<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Produit;
use App\Models\Notification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Notifications\StockCritiqueProduit;

class CheckStockMini extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stock:check {produit_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Vérifie le stock d\'un produit et enregistre une notification si nécessaire';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $produitId = $this->argument('produit_id');

        // Récupérer le produit
        $produit = Produit::find($produitId);

        if (!$produit) {
            $this->error("Produit avec l'ID {$produitId} introuvable.");
            return 1;
        }

        // S'assurer que les valeurs sont des entiers
        $qte = (int) $produit->qte;
        $qteMin = (int) $produit->qte_min;

        // Vérifier les niveaux de stock
        if ($qte < 1) {
            $this->createStockNotification($produit, 'Le stock est terminé.');
        } elseif ($qte < $qteMin) {
            $this->createStockNotification($produit, 'Le stock est en dessous du seuil de sécurité.');
        } else {
            $this->info("Le stock du produit ID {$produitId} est suffisant.");
        }
    }

    private function createStockNotification(Produit $produit, string $message)
    {
        $data = [
            'libelle' => $produit->libelle,
            'description' => $message
        ];

        //recuperer la liste des utilisateurs 
        $users = User::all();

        if ($users) {
            foreach ($users as $user) {
                $user->notify(new StockCritiqueProduit($data));
            }
        }
        
        $this->info("Notification créée : {$message}");
    }
}
