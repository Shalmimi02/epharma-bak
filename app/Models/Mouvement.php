<?php

namespace App\Models;

use App\Models\Produit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Mouvement extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'produit_libelle',
        'motif',
        'type',
        'qte',
        'produit_id',
        'created_by',
    ];

    public function produit()
    {
        return $this->belongsTo(Produit::class); 
    }
}
