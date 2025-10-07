<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Billetage extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'caisse_libelle',
        'ended_with',
        'total_vente',
        'total_billetage',
        'ecart',
        'date_debut',
        'date_fin',
        'heure_debut',
        'heure_fin',
        'statut',
        'cinq_franc',
        'dix_franc',
        'vingt_cinq_franc',
        'cinquante_franc',
        'cent_franc',
        'cinq_cent_franc',
        'mil_franc',
        'deux_mil_franc',
        'cinq_mil_franc',
        'dix_mil_franc',
        'description',
        'created_by',
        'commentaire_validation',
        'est_valide',
    ];
}
