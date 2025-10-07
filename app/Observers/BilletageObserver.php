<?php

namespace App\Observers;

use App\Models\Vente;
use App\Models\Billetage;

class BilletageObserver 
{
    public function creating(Billetage $billetage): void
    {
        $this->calculateTotalVente($billetage);
    }

    public function updating(Billetage $billetage): void
    {
        $this->calculateTotalVente($billetage);
    }

    protected function calculateTotalVente(Billetage $billetage)
    {
        // Récupérer les dates et la caisse du billetage
        $dateDebut =  date('Y-m-d H:i:s', strtotime($billetage->date_debut. ' '.$billetage->heure_debut));
        $dateFin =  date('Y-m-d H:i:s', strtotime($billetage->date_fin. ' '.$billetage->heure_fin));
        $caisseLibelle = $billetage->caisse_libelle;

        // Rechercher les ventes dans la période donnée et correspondant à la caisse
        $totalVente = Vente::where('caisse', $caisseLibelle)
            ->where('statut', 'Soldé') //on s'assure de prendre en compte uniquement les ventes validés
            ->whereBetween('created_at', [$dateDebut, $dateFin])
            ->sum('total'); // Calculer la somme des ventes validées

        // Mettre à jour le total des ventes dans le modèle Billetage
        $billetage->total_vente = $totalVente;

        // calculer l'écart
        $billetage->ecart = $billetage->total_billetage - $totalVente;
    }
}
