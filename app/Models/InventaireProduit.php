<?php

namespace App\Models;

use App\Models\Produit;
use App\Models\Inventaire;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InventaireProduit extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'inventaire_produit';

    protected $fillable = [
        'inventaire_id',
        'produit_id',
        'qte',
        'qte_reelle',
        'qte_finale',
        'qte_initiale',
        'ecart',
        'rayon_libelle',
        'produit_libelle',
        'produit_cip'
    ];

    public function inventaire()
    {
        return $this->belongsTo(Inventaire::class);
    }
        public function produit()
    {
        return $this->belongsTo(Produit::class);
    }
}
