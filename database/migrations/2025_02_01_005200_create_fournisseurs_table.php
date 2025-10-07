<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

   public function up(): void
   {
      Schema::create('fournisseurs', function (Blueprint $table) {
         $table->id();
         $table->string('libelle');
         $table->string('code')->unique()->nullable();
         $table->text('description')->nullable();
         $table->string('site')->nullable();
         $table->string('siteurl')->nullable();
         $table->string('rang')->nullable();
         $table->string('telephone')->nullable();
         $table->string('email')->nullable();
         $table->string('adresse')->nullable();
         $table->string('compte_bancaire')->nullable();
         $table->string('ville')->nullable();
         $table->string('pays')->nullable();
         $table->string('created_by')->nullable();
         $table->string('is_active')->nullable();
         $table->timestamps();
         $table->softDeletes();
      });
   }


   public function down(): void
   {
      Schema::dropIfExists('fournisseurs');
   }
};
