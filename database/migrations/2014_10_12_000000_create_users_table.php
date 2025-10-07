<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('email')->unique()->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password')->nullable();
            $table->string('last_name');
            $table->string('first_name')->nullable();
            $table->string('fullname');
            $table->string('role')->nullable();
            $table->boolean('is_admin')->default(false);
            $table->boolean('is_active')->default(true);
            $table->boolean('must_change_password')->default(true);
            $table->string('last_connexion')->nullable();
            $table->string('last_activity')->nullable();
            $table->string('created_by')->nullable();
            $table->string('is_archive')->nullable();
            $table->string('is_enabled')->nullable();
            $table->string('photo_url')->nullable();
            $table->string('adresse')->nullable();
            $table->string('boite_postale')->nullable();
            $table->string('ville')->nullable();
            $table->date('date_naissance')->nullable();
            $table->string('lieu_naissance')->nullable();
            $table->string('sexe')->nullable();
            $table->string('nom_pere')->nullable();
            $table->string('nom_mere')->nullable();
            $table->string('numero_cni')->nullable();
            $table->string('numero_permis_conduire')->nullable();
            $table->string('type_permis_conduire')->nullable();
            $table->string('matricule_fonction_publique')->nullable();
            $table->string('numero_cnss')->nullable();
            $table->string('matricule_cnss')->nullable();
            $table->string('numero_cnamgs')->nullable();
            $table->string('situation_familial')->nullable();
            $table->string('nombre_enfant_charge')->nullable();
            $table->string('niveau_etude')->nullable();
            $table->string('dernier_diplome')->nullable();
            $table->string('etablissement')->nullable();
            $table->string('profession_formation')->nullable();
            $table->string('poste')->nullable();
            $table->string('mode_paiement_salaire')->nullable();
            $table->string('type_contrat')->nullable();
            $table->date('date_embauche')->nullable();
            $table->date('date_fin_contrat')->nullable();
            $table->string('telephone')->nullable();
            $table->string('contact_de_secours')->nullable();
            $table->json('habilitations')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
