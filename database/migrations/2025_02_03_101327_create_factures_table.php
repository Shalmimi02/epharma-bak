<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

   public function up(): void
   {
      Schema::create('factures', function (Blueprint $table) {
         $table->id();
         $table->string('numero')->nullable();
         $table->string('client');
         $table->string('created_by');
         $table->string('type')->nullable();
         $table->string('net_a_payer')->nullable();
         $table->foreignId('reservation_id')->constrained()->onUpdate('cascade')->onDelete('cascade');
         $table->boolean('est_valide')->default(false);
         $table->timestamps();
         $table->softDeletes();
      });
   }


   public function down(): void
   {
      Schema::dropIfExists('factures');
   }
};
