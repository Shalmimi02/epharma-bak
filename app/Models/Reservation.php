<?php

namespace App\Models;

use App\Models\Facture;
use App\Models\ReservationProduit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Reservation extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'code',
        'numero',
        'position',
        'remise',
        'avant_remise',
        'client',
        'caisse',
        'amount_reserved',
        'amount_gived',
        'nom_devis',
        'switch_caisse_at',
        'switch_finish_at',
        'switch_devis_at',
        'switch_dette_at',
        'status',
        'created_by',
        'nom_assure',
        'identifiant_assure',
        'numero_feuille_assure',
        'secteur_assure',
        'montant', //le montant que le client doit payer
        'total', //le montant total de la facture
        'total_ht',
        'total_tva',
        'total_css',
        'total_garde', //le cumul de la garde sur toutes les unités de produit
        'prise_en_charge', //montant pris en charge par l'assurance
        'total_prise_en_charge', // montant pris en charge par l'assurance
        'client_id',
        'garde_id',
        'caisse_id',
        'montant_taxe',
        'net_a_payer',// le montant que le client doit payer apres avoir retire la remise
        'credit_restant', // credit restant du client au moment de la validation de la reservation
        'printed_at', //stoquer la premiere fois qu'on imprime
        'closed_by'
    ];
    
    public function client()
    {
        return $this->belongsTo(Client::class); 
    }

    public function garde()
    {
        return $this->belongsTo(Garde::class); 
    }

    
    public function caisse()
    {
        return $this->belongsTo(Caisse::class); 
    }

    public function ventes()
    {
        return $this->hasMany(Vente::class); 
    }

    public function reservation_produits()
    {
        return $this->hasMany(ReservationProduit::class); 
    }

    public function facture()
    {
        return $this->hasOne(Facture::class); 
    }
}

