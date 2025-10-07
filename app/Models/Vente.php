<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vente extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'caisse',
        'position',
        'client',
        'total_client',
        'clientId',
        'date_reservation',
        'user',
        'total',
        'tva',
        'css',
        'garde',
        'ht',
        'total_garde',
        'isannule',
        'nom_assure',
        'identifiant_assure',
        'numero_feuille_assure',
        'secteur_assure',
        'montant_recu',
        'reservation_id',
        'caisse_id',
        'statut',
        'total_prise_en_charge',
        'is_sold',
        'export_at'
    ];

    public function reservation()
    {
        return $this->belongsTo(Reservation::class); 
    }

    
    public function caisse()
    {
        return $this->belongsTo(Caisse::class); 
    }

    
    public function reservation_produits()
    {
        return $this->hasMany(ReservationProduit::class); 
    }

}

    
    
