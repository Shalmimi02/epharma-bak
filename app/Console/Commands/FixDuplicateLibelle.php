<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixDuplicateLibelle extends Command
{
    protected $signature = 'produits:fix-libelle';
    protected $description = 'Ajoute (n) aux libellés dupliqués dans la table produits';

    public function handle()
    {
        $produits = DB::connection('api-stock')->table('produits')->select('id', 'libelle')->orderBy('libelle')->get();
        $libelleCounts = [];

        foreach ($produits as $produit) {
            $baseLibelle = preg_replace('/\(\d+\)$/', '', trim($produit->libelle));
            
            if (!isset($libelleCounts[$baseLibelle])) {
                $libelleCounts[$baseLibelle] = 0;
            }
            
            $libelleCounts[$baseLibelle]++;
            
            if ($libelleCounts[$baseLibelle] > 1) {
                $newLibelle = $baseLibelle . ' (' . $libelleCounts[$baseLibelle] . ')';
                DB::connection('api-stock')->table('produits')->where('id', $produit->id)->update(['libelle' => $newLibelle]);
                $this->info("Libelle mis à jour : {$produit->libelle} -> $newLibelle");
            }
        }

        $this->info('Mise à jour des libellés terminée.');
    }
}
