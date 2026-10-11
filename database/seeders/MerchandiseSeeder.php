<?php

namespace Database\Seeders;

use App\Models\Merchandise;
use Illuminate\Database\Seeder;

class MerchandiseSeeder extends Seeder
{
    public function run(): void
    {
        Merchandise::factory()->count(5)->published()->create();
        Merchandise::factory()->count(2)->draft()->create();
    }
}
