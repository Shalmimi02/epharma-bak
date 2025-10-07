<?php

namespace App\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SyncDatabaseStock extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-database-stock';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        ini_set('memory_limit', '912M');

        \Artisan::call('produits:fix-libelle');

        $tables = ['rayons','mouv_motifs', 'prod_natures','fournisseurs',
        'prod_categories', 'prod_classe_theraps', 'prod_familles', 'prod_formes','produits', 'commandes', 'inventaires', 'mouvements',  
         'commande_produit','inventaire_produit'];
        
        DB::statement('SET FOREIGN_KEY_CHECKS=0;'); // Désactiver temporairement les contraintes
        
        foreach ($tables as $table) {
            $this->info("Synchronisation de la table {$table}...");
        
            try {
                // Vérifier si la table existe avant d'essayer de la synchroniser
                if (!Schema::connection('api-stock')->hasTable($table)) {
                    $this->warn("⚠️ La table {$table} n'existe pas. Passage à la suivante.");
                    continue;
                }
        
                // Supprimer les anciennes données sans affecter la structure
                DB::table($table)->delete();
                DB::statement("ALTER TABLE {$table} AUTO_INCREMENT = 1"); // Réinitialiser l'auto-incrément
        
                // Charger les données par morceaux pour éviter de surcharger la mémoire
                DB::connection('api-stock')->table($table)
                ->orderBy('id') // ⚠️ Assure-toi que 'id' existe dans toutes les tables
                ->chunk(1000, function ($rows) use ($table) {
                    DB::table($table)->insert(json_decode(json_encode($rows), true));
                });
        
                $this->info("✅ Table {$table} synchronisée avec succès !");
            } catch (\Exception $e) {
                $this->error("❌ Erreur avec la table {$table} : " . $e->getMessage());
                continue;
            }
        }
        
        DB::statement('SET FOREIGN_KEY_CHECKS=1;'); // Réactiver les contraintes
        $this->info("✅ Toutes les tables existantes ont été synchronisées.");
        

    }
}
