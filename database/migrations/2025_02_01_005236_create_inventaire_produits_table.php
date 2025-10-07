<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

   public function up(): void
   {
      Schema::create('inventaire_produit', function (Blueprint $table) {
         $table->id();
         $table->foreignId('inventaire_id')->constrained()->onUpdate('cascade')->onDelete('cascade');
         $table->foreignId('produit_id')->constrained()->onUpdate('cascade')->onDelete('cascade');
         $table->string('qte')->nullable();
         $table->string('qte_reelle')->nullable();
         $table->string('qte_finale')->nullable();
         $table->string('qte_initiale')->nullable();
         $table->string('ecart')->nullable();
         $table->string('rayon_libelle')->nullable();
         $table->string('produit_libelle')->nullable();
         $table->string('produit_cip')->nullable();
         $table->timestamps();
         $table->softDeletes();
      });
   }


   public function down(): void
   {
      Schema::dropIfExists('inventaire_produits');
   }
};
