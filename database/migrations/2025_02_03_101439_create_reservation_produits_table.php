<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

   public function up(): void
   {
      Schema::create('reservation_produits', function (Blueprint $table) {
         $table->id();
         $table->string('libelle');
         $table->string('qte')->default(1);
         $table->string('prix_de_vente')->default(0);
         $table->string('prix_achat')->default(0);
         $table->string('cout_total')->default(0);
         $table->string('cout_total_reel')->default(0);
         $table->json('produit')->nullable();
         $table->string('reservation_id')->nullable();
         $table->string('prise_en_charge')->default(0);
         $table->string('produit_id')->nullable();
         $table->string('total_ht')->default(0);
         $table->string('total_tva')->default(0);
         $table->string('total_css')->default(0);
         $table->string('total_prise_en_charge')->default(0);
         $table->string('total_garde')->default(0);
         $table->boolean('is_sold')->default(false);
         $table->foreignId('vente_id')->nullable()->constrained()->onUpdate('cascade')->onDelete('cascade');
         $table->timestamps();
      });
   }


   public function down(): void
   {
      Schema::dropIfExists('reservation_produits');
   }
};
