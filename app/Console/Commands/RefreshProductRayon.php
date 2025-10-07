<?php

namespace App\Console\Commands;

use App\Models\CommandeProduit;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RefreshProductRayon extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'commande:refresh-product-rayon {commandeId}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Placer dans les bon rayons en fonction de la commande';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $commandeId = $this->argument('commandeId');

        foreach (CommandeProduit::where('commande_id', $commandeId)->get() as $commande_produit) {

            $produit = $commande_produit->produit;

            DB::table('produits')->where('id', $produit->id)->update([
                'rayon'  => $commande_produit->rayon
            ]);

            echo "Produit ".$produit->libelle." deplace"."\n";
        }
    }
}
