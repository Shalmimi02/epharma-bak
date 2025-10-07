<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

   public function up(): void
   {
      Schema::create('commandes', function (Blueprint $table) {
         $table->id();
         $table->string('numero')->nullable();
         $table->string('facture')->nullable()->unique();
         $table->text('description')->nullable();
         $table->json('fournisseur')->nullable();
         $table->string('fournisseur_libelle')->nullable();
         $table->string('status')->nullable();
         $table->string('created_by')->nullable();
         $table->string('suspended_by')->nullable();
         $table->string('ended_with')->nullable();
         $table->string('total_achat')->nullable();
         $table->string('total_vente')->nullable();
         $table->string('total_ht')->nullable();
         $table->string('total_tva')->nullable();
         $table->string('total_css')->nullable();
         $table->timestamps();
         $table->softDeletes();
      });
   }


   public function down(): void
   {
      Schema::dropIfExists('commandes');
   }
};
