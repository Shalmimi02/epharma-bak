<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Reservation;
use App\Models\ReservationProduit;

class CalculateReservationTotals extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reservation:calculate-totals {reservationId}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculer les totaux pour une réservation donnée';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $reservationId = $this->argument('reservationId');
        $reservation = Reservation::find($reservationId);

        if (!$reservation) {
            $this->error('Réservation introuvable.');
            return 1;
        }

        $produits = $reservation->reservation_produits; // Relation hasMany avec ReservationProduit

        // Initialisation des totaux
        $totals = [
            'montant' => 0,
            'montant_avant_remise'  => 0,
            'total' => 0,
            'total_prise_en_charge' => 0,
            'total_tva' => 0,
            'total_css' => 0,
            'total_ht' => 0,
            'total_garde' => 0,
        ];

        // Calcul des totaux
        foreach ($produits as $produit) {
            $totals['montant'] += floatval($produit->cout_total);
            $totals['total'] += floatval($produit->cout_total_reel);
            $totals['total_prise_en_charge'] += floatval($produit->total_prise_en_charge);
            $totals['total_tva'] += floatval($produit->total_tva);
            $totals['total_css'] += floatval($produit->total_css);
            $totals['total_ht'] += floatval($produit->total_ht);
            $totals['total_garde'] += floatval($produit->total_garde);
        }

        $totals['montant_avant_remise'] = $totals['montant'];
        $totals['montant'] = $totals['montant'] - floatval($reservation->remise);

        // Mise à jour de la réservation
        $reservation->update($totals);

        $this->info('Les totaux pour la réservation #' . $reservationId . ' ont été calculés et mis à jour avec succès.');

        return 0;
    }
}
