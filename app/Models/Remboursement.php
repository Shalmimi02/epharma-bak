<?php

namespace App\Models;

use App\Models\Client;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Remboursement extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'montant',
        'reste_a_payer',
        'created_by',
        'client_id',
        'venteId',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class); 
    }
}
