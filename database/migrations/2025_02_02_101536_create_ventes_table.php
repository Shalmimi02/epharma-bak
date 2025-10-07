<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

   public function up(): void
   {
      Schema::create('ventes', function (Blueprint $table) {
         $table->id();
         $table->string('clientId')->nullable();
         $table->string('position')->nullable();
         $table->string('caisse')->nullable();
         $table->string('client')->nullable();
         $table->string('total_client')->default(0);
         $table->timestamp('date_reservation');
         $table->string('user')->nullable();
         $table->string('total')->default(0);
         $table->string('tva')->default(0);
         $table->string('css')->default(0);
         $table->string('garde')->nullable();
         $table->string('ht')->default(0);
         $table->string('total_garde')->default(0);
         $table->boolean('isannule')->default(false);
         $table->string('nom_assure')->nullable();
         $table->string('identifiant_assure')->nullable();
         $table->string('numero_feuille_assure')->nullable();
         $table->string('secteur_assure')->nullable();
         $table->string('montant_recu')->nullable();
         $table->string('total_prise_en_charge')->nullable();
         $table->string('statut')->nullable();
         $table->timestamps();
         $table->softDeletes();
      });
   }


   public function down(): void
   {
      Schema::dropIfExists('ventes');
   }
};
