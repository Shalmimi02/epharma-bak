<?php

namespace App\Models;

use App\Models\Reservation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Facture extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'numero',
        'client',
        'created_by',
        'type',
        'reservation_id',
        'net_a_payer',
        'est_valide'
    ];

    public function reservation()
    {
        return $this->belongsTo(Reservation::class); 
    }
}
