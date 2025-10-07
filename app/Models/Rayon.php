<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rayon extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'libelle',
        'description',
        'created_by',
    ];

    public function produits()
    {
        return $this->hasMany(Produit::class); 
    }

}

   
