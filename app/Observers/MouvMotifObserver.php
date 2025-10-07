<?php

namespace App\Observers;

use App\Models\MouvMotif;

class MouvMotifObserver 
{
    public function creating(MouvMotif $mouv_motif)
    {
        $mouv_motif->libelle = strtoupper($mouv_motif->libelle);
    }

    public function updating(MouvMotif $mouv_motif)
    {
        $mouv_motif->libelle = strtoupper($mouv_motif->libelle);
    }

}
