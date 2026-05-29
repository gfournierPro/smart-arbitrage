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
        Schema::create('tariff_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->constrained()->cascadeOnDelete();
            $table->string('label');                 // "Heures creuses", "Effacement"
            $table->integer('price_cents');          // prix du kWh en centimes
            $table->time('starts_at')->nullable();   // début de plage horaire
            $table->time('ends_at')->nullable();
            $table->json('months')->nullable();      // [11,12,1,2] pour l'hiver
            $table->boolean('is_curtailment')->default(false); // jour d'effacement
            $table->integer('priority')->default(0); // résolution des chevauchements
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tariff_rates');
    }
};
