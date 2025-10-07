<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Caisse extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'libelle',
        'current_authorized_user',
        'pin',
        'is_open',
        'is_locked',
        'last_login',
        'statut',
        'created_by',
    ];

    public function reservations()
    {
        return $this->hasMany(Reservation::class); 
    }

    public function ventes()
    {
        return $this->hasMany(Vente::class); 
    }
}

    
