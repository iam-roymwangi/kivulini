<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('merchandise', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->enum('type', ['t_shirt', 'hoodie', 'hat', 'other'])->default('t_shirt');
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->json('sizes');   // e.g. ["XS","S","M","L","XL","XXL"]
            $table->json('colors');  // e.g. ["Black","White","Amber"]
            $table->integer('stock')->default(0)->unsigned();
            $table->enum('status', ['draft', 'published'])->default('draft');
            $table->string('image_path')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('merchandise');
    }
};
