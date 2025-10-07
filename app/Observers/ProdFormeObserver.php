<?php

namespace App\Observers;

use App\Models\ProdForme;

class ProdFormeObserver
{
    public function creating(ProdForme $prod_forme)
    {
        $prod_forme->libelle = strtoupper($prod_forme->libelle);
    }

    public function updating(ProdForme $prod_forme)
    {
        $prod_forme->libelle = strtoupper($prod_forme->libelle);
    }
}
