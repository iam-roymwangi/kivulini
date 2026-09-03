<?php

use App\Http\Controllers\Admin\EventController as AdminEventController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;

Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');

Route::get('/', [EventController::class, 'home'])->name('home');
Route::get('/events', [EventController::class, 'list'])->name('events.list');
Route::get('/events/{slug}', [EventController::class, 'show'])->name('events.show');
Route::get('/gallery', [EventController::class, 'galleryPage'])->name('events.gallery');
Route::get('/contact', [EventController::class, 'contactPage'])->name('contact');
Route::post('/contact', [ContactController::class, 'store'])->name('contact.store');

Route::post('/events/{event}/bookings', [BookingController::class, 'store'])->name('bookings.store');
Route::post('/bookings/{booking}/cancel', [BookingController::class, 'cancel'])->name('bookings.cancel');
Route::get('/bookings/{booking}/pass', [BookingController::class, 'downloadPass'])->name('bookings.pass');

use App\Http\Controllers\Admin\AdminGalleryController;
use App\Http\Controllers\DashboardController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');

    // Admin event management
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::resource('events', AdminEventController::class);
        Route::post('events/{event}/publish-gallery', [AdminEventController::class, 'publishGallery'])->name('events.publish-gallery');
        Route::post('events/{event}/unpublish-gallery', [AdminEventController::class, 'unpublishGallery'])->name('events.unpublish-gallery');

        // Admin gallery management
        Route::get('gallery', [AdminGalleryController::class, 'index'])->name('gallery.index');
        Route::post('gallery', [AdminGalleryController::class, 'store'])->name('gallery.store');
        Route::delete('gallery/{media}', [AdminGalleryController::class, 'destroy'])->name('gallery.destroy');
        Route::post('gallery/{media}/toggle-featured', [AdminGalleryController::class, 'toggleFeatured'])->name('gallery.toggle-featured');
    });
});

require __DIR__.'/settings.php';
