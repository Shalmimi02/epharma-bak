<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

   public function up(): void
   {
      Schema::create('gardes', function (Blueprint $table) {
         $table->id();
         $table->string('numero')->nullable();
         $table->date('date_debut')->nullable();
         $table->date('date_fin')->nullable();
         $table->string('heure_debut')->nullable();
         $table->string('heure_fin')->nullable();
         $table->string('montant_taxe')->nullable();
         $table->string('total_taxe')->nullable();
         $table->boolean('is_active')->default(true);
         $table->timestamps();
         $table->softDeletes();
      });
   }


   public function down(): void
   {
      Schema::dropIfExists('gardes');
   }
};
