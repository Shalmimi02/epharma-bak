<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

   public function up(): void
   {
      Schema::create('inventaires', function (Blueprint $table) {
         $table->id();
         $table->string('numero')->nullable();
         $table->string('type')->nullable();
         $table->string('rayon')->nullable();
         $table->string('created_by')->nullable();
         $table->string('total_reel_cfa')->nullable();
         $table->string('total_initial_cfa')->nullable();
         $table->string('valeur_achat')->nullable();
         $table->string('valeur_vente')->nullable();
         $table->string('total_css')->nullable();
         $table->string('is_closed')->nullable();
         $table->string('closed_by')->nullable();
         $table->string('closed_at')->nullable();
         $table->string('ecart_only')->nullable();
         $table->string('is_suspended')->nullable();
         $table->string('suspended_by')->nullable();
         $table->string('suspended_at')->nullable();
         $table->string('statut')->default('En cours');
         $table->timestamps();
         $table->softDeletes();
      });
   }


   public function down(): void
   {
      Schema::dropIfExists('inventaires');
   }
};
