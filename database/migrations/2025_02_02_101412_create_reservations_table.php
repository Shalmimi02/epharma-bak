<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

   public function up(): void
   {
      Schema::create('reservations', function (Blueprint $table) {
         $table->id();
         $table->integer('position')->nullable(); //position du client par jour
         $table->string('code')->nullable(); 
         $table->string('numero')->nullable();
         $table->string('remise')->nullable();
         $table->string('client')->default('COMPTANT');
         $table->string('caisse')->default('Default');
         $table->string('amount_reserved')->default(0);
         $table->string('amount_gived')->default(0);
         $table->string('switch_caisse_at')->nullable();
         $table->string('switch_finish_at')->nullable();
         $table->string('switch_devis_at')->nullable();
         $table->string('switch_dette_at')->nullable();
         $table->string('status')->default('Brouillon');
         $table->string('created_by')->nullable();
         $table->string('nom_assure')->nullable();
         $table->string('identifiant_assure')->nullable();
         $table->string('numero_feuille_assure')->nullable();
         $table->string('secteur_assure')->nullable();
         $table->string('total')->nullable();
         $table->string('montant')->nullable();
         $table->string('prise_en_charge')->nullable();
         $table->string('total_prise_en_charge')->nullable();
         $table->string('total_tva')->nullable();
         $table->string('total_css')->nullable();
         $table->string('total_ht')->nullable();
         $table->string('total_garde')->nullable();
         $table->string('montant_taxe')->nullable();
         $table->timestamps();
         $table->softDeletes();
      });
   }


   public function down(): void
   {
      Schema::dropIfExists('reservations');
   }
};
