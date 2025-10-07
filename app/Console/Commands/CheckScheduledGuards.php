<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Garde;
use App\Models\ReservationProduit;
use Carbon\Carbon;

class CheckScheduledGuards extends Command
{
    protected $signature = 'gardes:check-status';
    protected $description = 'Vérifie et met à jour les gardes terminées, en calculant les taxes et le total de la garde';

    public function handle()
    {
        $now = Carbon::now();

        $gardes = Garde::where('statut', 'Programmé')
            ->whereRaw('CONCAT(date_fin, " ", heure_fin) <= ?', [$now])
            ->get();

        foreach ($gardes as $garde) {
            $dateDebut = date('Y-m-d H:i:s', strtotime($garde->date_debut . ' ' . $garde->heure_debut));
            $dateFin = date('Y-m-d H:i:s', strtotime($garde->date_fin . ' ' . $garde->heure_fin));

            $totaltaxe = ReservationProduit::where('is_sold', true)
                ->whereBetween('created_at', [$dateDebut, $dateFin])
                ->sum('qte');

            $catotal = ReservationProduit::where('is_sold', true)
                ->whereBetween('created_at', [$dateDebut, $dateFin])
                ->sum('cout_total');

            // Mettre à jour la garde
            $garde->update([
                'statut' => 'Terminé',
                'total_taxe' => $totaltaxe ?? 0,
                'total_garde' => $catotal ?? 0,
            ]);
        }

        $this->info(count($gardes) . " gardes mises à jour.");
    }
}
