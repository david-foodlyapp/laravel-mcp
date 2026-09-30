<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('restaurants', function (Blueprint $table) {
            $table->id();
            $table->string('external_slug')->unique();
            $table->string('name');
            $table->text('address')->nullable();
            $table->text('logo_url')->nullable();
            $table->text('image_url')->nullable();
            $table->decimal('price_per_person', 10, 2)->nullable();
            $table->decimal('discount_rate', 5, 2)->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->boolean('reservations_enabled')->default(false);
            $table->json('working_hours')->nullable();
            $table->string('currency', 3)->nullable();
            $table->boolean('is_open')->default(false);
            $table->unsignedInteger('rank')->nullable();
            $table->unsignedInteger('sort_order')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();

            $table->index(['is_open', 'currency']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('restaurants');
    }
};
