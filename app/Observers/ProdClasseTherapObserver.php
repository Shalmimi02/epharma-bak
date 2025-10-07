<?php

namespace App\Observers;

use App\Models\ProdClasseTherap;

class ProdClasseTherapObserver
{
    public function creating(ProdClasseTherap $prod_classe_therap)
    {
        $prod_classe_therap->libelle = strtoupper($prod_classe_therap->libelle);
    }

    public function updating(ProdClasseTherap $prod_classe_therap)
    {
        $prod_classe_therap->libelle = strtoupper($prod_classe_therap->libelle);
    }

   
}
