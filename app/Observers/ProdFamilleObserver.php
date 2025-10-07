<?php

namespace App\Observers;

use App\Models\ProdFamille;

class ProdFamilleObserver
{
    public function creating(ProdFamille $prod_famille)
    {
        $prod_famille->libelle = strtoupper($prod_famille->libelle);
    }

    public function updating(ProdFamille $prod_famille)
    {
        $prod_famille->libelle = strtoupper($prod_famille->libelle);
    }
  
}
