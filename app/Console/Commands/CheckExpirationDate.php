<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Produit;
use App\Models\Commande;
use App\Mail\ProduitExpire;
use App\Models\CommandeProduit;
use Illuminate\Console\Command;
use App\Models\StockNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Notifications\StockCritiqueProduit;

class CheckExpirationDate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-expiration-date';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Commande pour verifier les produits qui expirent';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Date actuelle
        $currentDate = Carbon::now();

        // Seuil des 30 jours
        $thresholdDate = $currentDate->addDays(30);

        // Récupérer les produits dont la date d'expiration est inférieure à 30 jours
        $expiringProducts = CommandeProduit::whereDate('date_expiration', '<', $thresholdDate)
        ->orderBy('date_expiration', 'asc') // Optionnel, pour les trier par date d'expiration
        ->get();

        if ($expiringProducts->isEmpty()){
            $this->info('Aucun produit proche de l\'expiration.');
            return;
        }

        // Afficher la liste des produits qui expirent
        foreach ($expiringProducts as $produit) {
            $this->info($produit->produit_libelle.' '.$produit->date_expiration);
        }

        //recuperer la liste des utilisateurs qui ont une adresse email
        $users = User::whereNotNull('email')->get();


        //informations contenu dans l'alerte
        $alerteData = [
            'libelle' => $produit->libelle,
            'description' => 'Produits en voie d\'expiration -30 jours restant',
            'produits' => $expiringProducts
        ];

        if ($users) {
            foreach ($users as $user) {
                $user->notify(new StockCritiqueProduit($alerteData));

                Mail::to($user->email)->queue(new ProduitExpire($expiringProducts));
            }
        }
    }
}
