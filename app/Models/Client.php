<?php

namespace App\Models;

use App\Models\Reservation;
use App\Models\Remboursement;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Client extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'libelle',
        'total_amount',
        'remise_percent',
        'created_by',
        'nom',
        'email',
        'telephone',
        'ville',
        'code',
        'numero_cnss',
        'numero_assurance',
        'assurance',
        'plafond_dette',
        'current_dette',
        'current_remboursement_amount',
        'is_enabled',
        'client_id',
    ];

    public function reservations()
    {
        return $this->hasMany(Reservation::class); 
    }

    public function remboursements()
    {
        return $this->hasMany(Remboursement::class); 
    }
}

    

    
