<?php

namespace App\Models;

use App\Models\Vente;
use App\Models\Reservation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ReservationProduit extends Model
{
    use HasFactory;
    protected $fillable = [
        'libelle',
        'qte',
        'prix_de_vente',
        'prix_achat',
        'cout_total',
        'cout_total_reel',
        'produit',
        'prise_en_charge',
        'reservation_id',
        'produit_id',
        'total_ht',
        'total_tva',
        'total_css',
        'total_garde',
        'total_prise_en_charge',
        'is_sold',
        'vente_id',
        'statut'
    ];

    protected $casts = [
        'produit' => 'json',
    ];

    public function reservation()
    {
        return $this->belongsTo(Reservation::class); 
    }

    public function vente()
    {
        return $this->belongsTo(Vente::class); 
    }
}
