<?php

namespace App\Observers;

use App\Models\ProdNature;

class ProdNatureObserver
{
    public function creating(ProdNature $prod_nature)
    {
        $prod_nature->libelle = strtoupper($prod_nature->libelle);
    }

    public function updating(ProdNature $prod_nature)
    {
        $prod_nature->libelle = strtoupper($prod_nature->libelle);
    }
}
