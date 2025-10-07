<?php

namespace App\Observers;

use Carbon\Carbon;
use App\Models\Garde;
use App\Models\Client;
use App\Models\Reservation;
use Illuminate\Support\Facades\DB;

class ReservationObserver
{
    // public function creating(Reservation $reservation): void
    // {
    //     // Obtenir la date et l'heure actuelles
    //     $now = Carbon::now();

    //     // Rechercher une garde qui correspond à la période actuelle
    //     $gardeActuelle = Garde::where('date_debut', '<=', $now->toDateString())
    //         ->where('date_fin', '>=', $now->toDateString())
    //         ->where('heure_debut', '<=', $now->toTimeString())
    //         ->where('heure_fin', '>=', $now->toTimeString())
    //         ->where('is_active', 1)
    //         ->first();

    //     if ($gardeActuelle) $reservation->montant_taxe = $gardeActuelle->montant_taxe;
    // }

    public function created(Reservation $reservation): void
    {
        //  // Obtenir la date actuelle sans l'heure (juste AAAA-MM-JJ)
        //  $today = Carbon::now()->format('Y-m-d');
        
        //  // Récupérer le dernier numéro de position pour les réservations créées aujourd'hui
        //  $lastReservation = Reservation::whereDate('created_at', $today)
        //      ->orderBy('position', 'desc')
        //      ->first();
         
        //  // Si une réservation existe déjà pour aujourd'hui, incrémenter le numéro de position
        //  if ($lastReservation) {
        //      DB::table('reservations')->where('id', $reservation->id)->update([
        //         'position'  => $lastReservation->position + 1,
        //         'prise_en_charge'  => 0
        //     ]);
        //  } else {
        //      // Sinon, initialiser à 1 pour la première réservation du jour
        //      DB::table('reservations')->where('id', $reservation->id)->update([
        //         'position'  => 1,
        //         'prise_en_charge'  => 0
        //     ]);
        //  }
    }

    public function updated(Reservation $reservation): void
    {
        $taux = 0;
        $client = Client::find($reservation->client_id);
        if($client && (floatval($client->remise_percent) > 0)){
            $taux = floatval($client->remise_percent);
        }
        
        DB::table('reservations')->where('id', $reservation->id)->update([
            'prise_en_charge'  => $taux
        ]);
    }

    public function deleted(Reservation $reservation): void
    {
        
    }

    public function restored(Reservation $reservation): void
    {
       
    }

    public function forceDeleted(Reservation $reservation): void
    {
       
    }
}
