<?php

use App\Models\Merchandise;
use App\Models\MerchandiseImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

// ── Public index ──────────────────────────────────────────────────────────────

test('public merchandise index returns only published items', function () {
    Merchandise::factory()->published()->count(3)->create();
    Merchandise::factory()->draft()->count(2)->create();

    $this->withoutVite()->get('/merchandise')
        ->assertStatus(200)
        ->assertInertia(fn ($page) => $page
            ->component('merchandise/Index')
            ->has('merchandise.data', 3)
        );
});

test('public merchandise show returns 404 for draft items', function () {
    $item = Merchandise::factory()->draft()->create();

    $this->get("/merchandise/{$item->id}")->assertStatus(404);
});

test('public merchandise show returns published item with images', function () {
    $item = Merchandise::factory()->published()->create();

    $this->withoutVite()->get("/merchandise/{$item->id}")
        ->assertStatus(200)
        ->assertInertia(fn ($page) => $page
            ->component('merchandise/Show')
            ->has('merchandise')
        );
});

// ── Admin index ───────────────────────────────────────────────────────────────

test('admin merchandise index is accessible to authenticated users', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->withoutVite()
        ->get('/admin/merchandise')
        ->assertStatus(200)
        ->assertInertia(fn ($page) => $page->component('admin/merchandise/Index'));
});

test('admin merchandise index requires authentication', function () {
    $this->get('/admin/merchandise')->assertRedirect('/login');
});

// ── Admin store ───────────────────────────────────────────────────────────────

test('admin can create merchandise item without images', function () {
    Storage::fake('public');
    $user = User::factory()->create();

    $this->actingAs($user)->post('/admin/merchandise', [
        'name' => 'Kivulini T-Shirt',
        'type' => 't_shirt',
        'description' => 'A great shirt.',
        'price' => '1500',
        'sizes' => ['S', 'M', 'L'],
        'colors' => ['Black', 'White'],
        'stock' => 50,
        'status' => 'published',
    ])->assertRedirect('/admin/merchandise');

    $this->assertDatabaseHas('merchandise', ['name' => 'Kivulini T-Shirt', 'status' => 'published']);
});

test('admin can create merchandise with multiple images', function () {
    Storage::fake('public');
    $user = User::factory()->create();

    $this->actingAs($user)->post('/admin/merchandise', [
        'name' => 'Multi-Image Hoodie',
        'type' => 'hoodie',
        'price' => '2500',
        'sizes' => ['M', 'L'],
        'colors' => ['Black'],
        'stock' => 10,
        'status' => 'published',
        'images' => [
            UploadedFile::fake()->image('front.jpg'),
            UploadedFile::fake()->image('back.jpg'),
        ],
    ]);

    $item = Merchandise::where('name', 'Multi-Image Hoodie')->first();
    expect($item)->not->toBeNull();

    $images = MerchandiseImage::where('merchandise_id', $item->id)->get();
    expect($images)->toHaveCount(2);

    $cover = $images->firstWhere('is_cover', true);
    expect($cover)->not->toBeNull();
    expect($cover->sort_order)->toBe(0);
});

test('admin store validates required fields', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post('/admin/merchandise', [])
        ->assertSessionHasErrors(['name', 'type', 'price', 'sizes', 'colors', 'stock', 'status']);
});

// ── Admin update ──────────────────────────────────────────────────────────────

test('admin can update merchandise item details', function () {
    $user = User::factory()->create();
    $item = Merchandise::factory()->published()->create(['name' => 'Old Name']);

    $this->actingAs($user)->put("/admin/merchandise/{$item->id}", [
        'name' => 'Updated Name',
        'type' => $item->type,
        'price' => $item->price,
        'sizes' => $item->sizes,
        'colors' => $item->colors,
        'stock' => $item->stock,
        'status' => $item->status,
    ]);

    expect($item->fresh()->name)->toBe('Updated Name');
});

test('admin can delete a specific image when updating', function () {
    Storage::fake('public');
    $user = User::factory()->create();
    $item = Merchandise::factory()->published()->create();

    $storedPath = 'merchandise/'.$item->id.'/test.webp';
    Storage::disk('public')->put($storedPath, 'fake-content');

    $image = MerchandiseImage::create([
        'merchandise_id' => $item->id,
        'file_path' => $storedPath,
        'is_cover' => true,
        'sort_order' => 0,
    ]);

    $this->actingAs($user)->put("/admin/merchandise/{$item->id}", [
        'name' => $item->name,
        'type' => $item->type,
        'price' => $item->price,
        'sizes' => $item->sizes,
        'colors' => $item->colors,
        'stock' => $item->stock,
        'status' => $item->status,
        'delete_image_ids' => [$image->id],
    ]);

    $this->assertDatabaseMissing('merchandise_images', ['id' => $image->id]);
    Storage::disk('public')->assertMissing($storedPath);
});

test('admin can set cover image when updating', function () {
    Storage::fake('public');
    $user = User::factory()->create();
    $item = Merchandise::factory()->published()->create();

    $img1 = MerchandiseImage::create([
        'merchandise_id' => $item->id,
        'file_path' => 'merchandise/'.$item->id.'/img1.webp',
        'is_cover' => true,
        'sort_order' => 0,
    ]);

    $img2 = MerchandiseImage::create([
        'merchandise_id' => $item->id,
        'file_path' => 'merchandise/'.$item->id.'/img2.webp',
        'is_cover' => false,
        'sort_order' => 1,
    ]);

    $this->actingAs($user)->put("/admin/merchandise/{$item->id}", [
        'name' => $item->name,
        'type' => $item->type,
        'price' => $item->price,
        'sizes' => $item->sizes,
        'colors' => $item->colors,
        'stock' => $item->stock,
        'status' => $item->status,
        'cover_image_id' => $img2->id,
    ]);

    expect($img1->fresh()->is_cover)->toBeFalse();
    expect($img2->fresh()->is_cover)->toBeTrue();
});

test('admin can add new images when updating', function () {
    Storage::fake('public');
    $user = User::factory()->create();
    $item = Merchandise::factory()->published()->create();

    $this->actingAs($user)->put("/admin/merchandise/{$item->id}", [
        'name' => $item->name,
        'type' => $item->type,
        'price' => $item->price,
        'sizes' => $item->sizes,
        'colors' => $item->colors,
        'stock' => $item->stock,
        'status' => $item->status,
        'images' => [UploadedFile::fake()->image('new.jpg')],
    ]);

    expect(MerchandiseImage::where('merchandise_id', $item->id)->count())->toBe(1);
});

// ── Admin destroy ─────────────────────────────────────────────────────────────

test('admin can soft delete merchandise item', function () {
    $user = User::factory()->create();
    $item = Merchandise::factory()->published()->create();

    $this->actingAs($user)->delete("/admin/merchandise/{$item->id}");

    $this->assertSoftDeleted('merchandise', ['id' => $item->id]);
});
