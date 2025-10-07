<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'last_name',
        'first_name',
        'fullname',
        'role',
        'is_admin',
        'is_active',
        'must_change_password',
        'last_connexion',
        'last_activity',
        'created_by',
        'is_archive',
        'is_enabled',
        'photo_url',
        'adresse',
        'boite_postale',
        'ville',
        'date_naissance',
        'lieu_naissance',
        'sexe',
        'nom_pere',
        'nom_mere',
        'numero_cni',
        'numero_permis_conduire',
        'type_permis_conduire',
        'matricule_fonction_publique',
        'numero_cnss',
        'matricule_cnss',
        'numero_cnamgs',
        'situation_familial',
        'nombre_enfant_charge',
        'niveau_etude',
        'dernier_diplome',
        'etablissement',
        'profession_formation',
        'poste',
        'mode_paiement_salaire',
        'type_contrat',
        'date_embauche',
        'date_fin_contrat',
        'email',
        'telephone',
        'contact_de_secours',
        'habilitations'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'habilitations' => 'json'
    ];
}
