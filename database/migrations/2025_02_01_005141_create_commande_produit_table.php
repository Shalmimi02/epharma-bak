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
        Schema::create('commande_produit', function (Blueprint $table) {
            $table->id();
            $table->foreignId('commande_id')->constrained()->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('produit_id')->constrained()->onUpdate('cascade')->onDelete('cascade');
            $table->integer('qte')->nullable();
            $table->string('total_tva')->nullable();
            $table->string('total_css')->nullable();
            $table->string('total_ttc')->nullable();
            $table->string('total_ht')->nullable();
            $table->string('produit_libelle')->nullable();
            $table->string('produit_cip')->nullable();
            $table->string('lot')->nullable();
            $table->string('rayon')->nullable();
            $table->string('rayonId')->nullable();
            $table->string('date_expiration')->nullable();
            $table->string('prix_achat')->nullable();
            $table->string('total_achat')->nullable();
            $table->string('coef_conversion_de_prix_vente_achat')->nullable();
            $table->string('prix_de_vente')->nullable();
            $table->integer('qte_initiale')->nullable();
            $table->integer('qte_finale')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commande_produit');
    }
};
