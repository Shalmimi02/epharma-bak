<?php

namespace App\Observers;

use App\Models\Produit;
use Illuminate\Support\Facades\DB;

class ProduitObserver
{
    public function created(Produit $produit): void
    {
        if ($produit->qte > 0 && $produit->is_active == false) {
            DB::table('produits')->where('id', $produit->id)->update([
                'is_active'  => true
            ]);
        }
    }

    public function updated(Produit $produit): void
    {
        if ($produit->qte > 0 && $produit->is_active == false) {
            DB::table('produits')->where('id', $produit->id)->update([
                'is_active'  => true
            ]);
        }
    }
}
