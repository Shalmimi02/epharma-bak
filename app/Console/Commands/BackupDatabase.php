<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Backup;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

class BackupDatabase extends Command
{
    protected $signature = 'backup:database';
    protected $description = 'Effectue une sauvegarde de la base de données et la stocke localement.';

    public function handle()
    {
        $filename = 'backup_' . now()->format('Y-m-d_H-i-s') . '.sql';
        $path = storage_path('app/backups/' . $filename);

        // Assurer que le dossier existe
        if (!is_dir(storage_path('app/backups'))) {
            mkdir(storage_path('app/backups'), 0777, true);
        }

        $process = new Process([
            'mysqldump',
            '--user=' . env('DB_USERNAME'),
            '--password=' . env('DB_PASSWORD'),
            '--host=' . env('DB_HOST'),
            env('DB_DATABASE'),
            '--result-file=' . $path
        ]);

        $process->run();

        if (!$process->isSuccessful()) {
            Log::error("Erreur de sauvegarde : " . $process->getErrorOutput());
            $this->error('Échec de la sauvegarde.');
            return 1;
        }

        if (!file_exists($path)) {
            Log::error("Erreur : fichier de sauvegarde introuvable.");
            $this->error('Le fichier de sauvegarde n’a pas été créé.');
            return 1;
        }

        // Enregistrer la sauvegarde en base de données
        Backup::create([
            'filename' => $filename,
            'path' => $path
        ]);

        $this->info('Sauvegarde réussie : ' . $filename);
        return 0;
    }
}
