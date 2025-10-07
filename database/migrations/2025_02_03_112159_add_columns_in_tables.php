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
        Schema::table('reservations', function (Blueprint $table) {
            $table->foreignId('client_id')->default(1)->constrained()->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('garde_id')->nullable()->constrained()->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('caisse_id')->nullable()->constrained()->onUpdate('cascade')->onDelete('cascade');
            $table->string('montant_avant_remise')->nullable()->after('montant');
            $table->string('credit_restant')->nullable();
            $table->string('avant_remise')->nullable(); 
            $table->string('printed_at')->nullable(); 
        });

        Schema::table('ventes', function (Blueprint $table) {
            $table->foreignId('reservation_id')->constrained()->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('caisse_id')->constrained()->onUpdate('cascade')->onDelete('cascade');
        });

        Schema::table('clients', function (Blueprint $table) {
            $table->foreignId('client_id')->nullable()->constrained()->onUpdate('cascade')->onDelete('cascade');

        });

        Schema::table('remboursements', function (Blueprint $table) {
            $table->string('venteId')->nullable();
        });

        Schema::table('gardes', function (Blueprint $table) {
            $table->string('statut')->default('Programmé');
        });

        // Copie des valeurs de la colonne montant vers montant_avant_remise
        DB::statement('UPDATE reservations SET montant_avant_remise = montant');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservations');
        Schema::dropIfExists('ventes');
        Schema::dropIfExists('clients');
    }
};
