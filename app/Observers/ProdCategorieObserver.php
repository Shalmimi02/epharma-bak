<?php

namespace App\Observers;

use App\Models\ProdCategorie;

class ProdCategorieObserver
{
    public function creating(ProdCategorie $prod_categorie)
    {
        $prod_categorie->libelle = strtoupper($prod_categorie->libelle);
    }

    public function updating(ProdCategorie $prod_categorie)
    {
        $prod_categorie->libelle = strtoupper($prod_categorie->libelle);
    }
    
}
