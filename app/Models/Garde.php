<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Garde extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'numero',
        'date_debut',
        'date_fin',
        'heure_debut',
        'heure_fin',
        'montant_taxe',
        'total_taxe',
        'statut',
        'is_active'
    ];

    public function reservations()
    {
        return $this->hasMany(Reservation::class); 
    }
}

    
