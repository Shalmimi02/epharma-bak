<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

   public function up(): void
   {
      Schema::create('clients', function (Blueprint $table) {
         $table->id();
         $table->string('libelle')->nullable();
         $table->string('total_amount')->default(0);
         $table->string('is_enabled')->nullable();
         $table->string('remise_percent')->default(0);
         $table->string('created_by')->nullable();
         $table->string('nom')->nullable();
         $table->string('code')->nullable();
         $table->string('email')->nullable();
         $table->string('telephone')->nullable();
         $table->string('ville')->nullable();
         $table->string('numero_cnss')->nullable();
         $table->string('numero_assurance')->nullable();
         $table->string('assurance')->nullable();
         $table->string('plafond_dette')->default(0);
         $table->string('current_dette')->default(0);
         $table->string('current_remboursement_amount')->default(0);
         $table->timestamps();
         $table->softDeletes();
      });
   }


   public function down(): void
   {
      Schema::dropIfExists('clients');
   }
};
