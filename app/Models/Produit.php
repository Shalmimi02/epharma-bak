<?php

namespace App\Models;

use App\Models\Mouvement;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Produit extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'libelle',
        'cip',
        'prix_achat',
        'coef_conversion_de_prix_vente_achat',
        'code',
        'qte',
        'qte_max',
        'qte_min',
        'description',
        'ean',
        'dci',
        'tva',
        'css',
        'prix_de_vente',
        'posologie',
        'homologation',
        'forme',
        'famille',
        'nature',
        'classe_therapeutique',
        'categorie',
        'poids',
        'longueur',
        'largeur',
        'rayon',
        'hauteur',
        'code_table',
        'statut',
        'code_fournisseur',
        'rayon_id',
        'qte_min',
        'qte_max',
        'cip_deux',
        'fournisseurId',
        'photo',
        'is_active'
    ];

    public function rayon()
    {
        return $this->belongsTo(Rayon::class); 
    }

    public function commandes()
    {
        return $this->belongsToMany(Commande::class); 
    }

    public function inventaires()
    {
        return $this->belongsToMany(Inventaire::class);
    }

    public function mouvements()
    {
        return $this->hasMany(Mouvement::class); 
    }
}
    


    
