<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

   public function up(): void
   {
      Schema::create('mouvements', function (Blueprint $table) {
         $table->id();
         $table->string('produit_libelle');
         $table->string('motif');
         $table->string('type');
         $table->string('qte');
         $table->string('produit_id');
         $table->string('created_by');
         $table->timestamps();
         $table->softDeletes();
      });
   }


   public function down(): void
   {
      Schema::dropIfExists('mouvements');
   }
};
