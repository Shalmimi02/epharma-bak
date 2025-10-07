<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

   public function up(): void
   {
      Schema::create('remboursements', function (Blueprint $table) {
         $table->id();
         $table->string('montant')->nullable();
         $table->string('reste_a_payer')->nullable();
         $table->string('created_by')->nullable();
         $table->string('client_id')->nullable();
         $table->timestamps();
         $table->softDeletes();
      });
   }


   public function down(): void
   {
      Schema::dropIfExists('remboursements');
   }
};
