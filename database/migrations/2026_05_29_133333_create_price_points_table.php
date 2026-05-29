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
        Schema::create('price_points', function (Blueprint $table) {
            $table->id();
            $table->string('country_code', 2);
            $table->timestamp('starts_at');          // créneau (souvent horaire)
            $table->integer('price_cents');          // peut être négatif
            $table->string('currency', 3)->default('EUR');
            $table->timestamps();
            $table->index(['country_code', 'starts_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('price_points');
    }
};
