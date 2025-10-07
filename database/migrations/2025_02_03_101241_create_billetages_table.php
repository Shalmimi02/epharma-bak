<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

   public function up(): void
   {
      Schema::create('billetages', function (Blueprint $table) {
         $table->id();
         $table->string('caisse_libelle')->nullable();
         $table->string('ended_with')->nullable();
         $table->string('total_vente')->nullable();
         $table->string('total_billetage')->nullable();
         $table->string('ecart')->nullable();
         $table->date('date_debut')->nullable();
         $table->date('date_fin')->nullable();
         $table->time('heure_debut')->nullable();
         $table->time('heure_fin')->nullable();
         $table->string('statut')->default('En attente de validation');
         $table->string('cinq_franc')->nullable();
         $table->string('dix_franc')->nullable();
         $table->string('vingt_cinq_franc')->nullable();
         $table->string('cinquante_franc')->nullable();
         $table->string('cent_franc')->nullable();
         $table->string('cinq_cent_franc')->nullable();
         $table->string('mil_franc')->nullable();
         $table->string('deux_mil_franc')->nullable();
         $table->string('cinq_mil_franc')->nullable();
         $table->string('dix_mil_franc')->nullable();
         $table->text('description')->nullable();
         $table->string('created_by')->nullable();
         $table->string('commentaire_validation')->nullable();
         $table->string('est_valide')->nullable();
         $table->timestamps();
         $table->softDeletes();
      });
   }


   public function down(): void
   {
      Schema::dropIfExists('billetages');
   }
};
