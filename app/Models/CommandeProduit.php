<?php

namespace App\Models;
use App\Models\Produit;
use App\Models\Commande;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CommandeProduit extends Model
{

    use HasFactory;

    protected $table = 'commande_produit';

    protected $fillable = [
        'commande_id',
        'produit_id',
        'qte',
        'qte_initiale',
        'qte_finale',
        'total_tva',
        'total_css',
        'total_ttc',
        'total_ht',
        'produit_libelle',
        'produit_cip',
        'lot',
        'rayon',
        'rayonId',
        'date_expiration',
        'prix_achat',
        'total_achat',
        'coef_conversion_de_prix_vente_achat',
        'prix_de_vente'
    ];

    public function commande()
    {
        return $this->belongsTo(Commande::class);
    }
    
    public function produit()
    {
        return $this->belongsTo(Produit::class);
    }
}
