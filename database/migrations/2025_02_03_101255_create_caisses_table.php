<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

   public function up(): void
   {
      Schema::create('caisses', function (Blueprint $table) {
         $table->id();
         $table->string('libelle')->unique();
         $table->string('current_authorized_user')->nullable();
         $table->string('pin');
         $table->string('is_open')->default(false);
         $table->string('is_locked')->nullable();
         $table->string('last_login')->nullable();
         $table->string('statut')->default('Fermer');
         $table->string('created_by')->nullable();
         $table->timestamps();
         $table->softDeletes();
      });
   }


   public function down(): void
   {
      Schema::dropIfExists('caisses');
   }
};
