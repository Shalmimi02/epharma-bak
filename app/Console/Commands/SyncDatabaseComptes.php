<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncDatabaseComptes extends Command
{
    protected $signature = 'app:sync-database-comptes';
    protected $description = 'Synchronise les données de la base de données 2 vers la base de données 1';

    public function handle()
    {
        $tables = ['users'];

        foreach ($tables as $table) {
            $this->info("Synchronisation de la table {$table}...");

            // Récupérer les données depuis la base de données 2
            $data = DB::connection('api-comptes')->table($table)->get();

            // Insérer dans la base de données 1
            DB::table($table)->truncate(); // Supprime les anciennes données
            DB::table($table)->insert(json_decode(json_encode($data), true));

            $this->info("Table {$table} synchronisée avec succès !");
        }

        $this->info("Toutes les tables ont été synchronisées.");
    }
}
