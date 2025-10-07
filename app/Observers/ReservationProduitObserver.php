<?php

namespace App\Observers;

use App\Models\Reservation;
use App\Models\ReservationProduit;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;

class ReservationProduitObserver
{

    public function created(ReservationProduit $reservation_produit): void
    {
        //on calcule les montants de la la reservation
        // Artisan::call('reservation:calculate-totals '.$reservation_produit->reservation_id);
    }

    public function updated(ReservationProduit $reservation_produit): void
    {
        //on calcule les montants de la la reservation
        // Artisan::call('reservation:calculate-totals '.$reservation_produit->reservation_id);
    }

    public function deleted(ReservationProduit $reservation_produit): void
    {
        //on calcule les montants de la la reservation
        // Artisan::call('reservation:calculate-totals '.$reservation_produit->reservation_id);
    }
}
