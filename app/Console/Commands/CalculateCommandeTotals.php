<?php

namespace App\Console\Commands;

use App\Models\Commande;
use App\Models\CommandeProduit;
use Illuminate\Console\Command;

class CalculateCommandeTotals extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'commande:calculate-totals {commandeId}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculer les totaux pour une commande donnée';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $commandeId = $this->argument('commandeId');
        $commande = Commande::find($commandeId);

        if (!$commande) {
            $this->error('Commande introuvable.');
            return 1;
        }

        // $produits = $commande->produits; // Relation hasMany avec commandeProduit

        // Initialisation des totaux
        $totals = [
            'total_achat' => 0,
            'total_vente' => 0,
            'total_tva' => 0,
            'total_css' => 0,
            'total_ht' => 0,
        ];

        // Calcul des totaux
        foreach (CommandeProduit::where('commande_id', $commande->id)->get() as $produit) {
            $totals['total_achat'] += floatval($produit->total_achat);
            $totals['total_vente'] += floatval($produit->total_ttc);
            $totals['total_tva'] += floatval($produit->total_tva);
            $totals['total_css'] += floatval($produit->total_css);
            $totals['total_ht'] += floatval($produit->total_ht);
        }

        // Mise à jour de la commande
        $commande->update($totals);

        $this->info('Les totaux pour la commande #' . $commandeId . ' ont été calculés et mis à jour avec succès.');

        return 0;
    }
}
