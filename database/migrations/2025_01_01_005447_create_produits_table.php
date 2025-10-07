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
        Schema::create('produits', function (Blueprint $table) {
            $table->id();
            $table->string('libelle')->unique();
            $table->string('cip')->unique()->nullable();
            $table->string('cip_deux')->unique()->nullable();
            $table->string('prix_achat')->nullable();
            $table->string('coef_conversion_de_prix_vente_achat')->nullable();
            $table->string('code')->nullable();
            $table->integer('qte')->default(0);
            $table->text('description')->nullable();
            $table->string('ean')->nullable();
            $table->string('dci')->nullable();
            $table->boolean('tva')->default(false);
            $table->boolean('css')->default(false);
            $table->string('prix_de_vente')->nullable();
            $table->integer('qte_min')->default(1);
            $table->integer('qte_max')->default(5);
            $table->string('fournisseurId')->nullable();
            $table->string('posologie')->nullable();
            $table->string('homologation')->nullable();
            $table->string('forme')->nullable();
            $table->string('famille')->nullable();
            $table->string('nature')->nullable();
            $table->string('classe_therapeutique')->nullable();
            $table->string('categorie')->nullable();
            $table->string('poids')->nullable();
            $table->string('longueur')->nullable();
            $table->string('largeur')->nullable();
            $table->string('hauteur')->nullable();
            $table->string('code_table')->nullable();
            $table->string('rayon')->nullable();
            $table->string('statut')->nullable();
            $table->string('code_fournisseur')->nullable();
            $table->boolean('is_active')->default(false);
            $table->string('photo')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('produits');
    }
};
