<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Commande extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'numero',
        'facture',
        'description',
        'fournisseur',
        'fournisseur_libelle',
        'status',
        'created_by',
        'suspended_by',
        'ended_with',
        'total_achat',
        'total_vente',
        'total_ht',
        'total_tva',
        'total_css',
        'fournisseur_id',
    ];

    protected $casts = [
        'fournisseur' => 'json',
    ];

    public function fournisseur()
    {
        return $this->belongsTo(Fournisseur::class); 
    }

    public function produits()
    {
        return $this->belongsToMany(Produit::class); 
    }

    // Boot method to hook into the creating event
    public static function boot()
    {
        parent::boot();

        // Hook into the creating event before the record is created
        static::creating(function ($commande) {
            $commande->numero = self::generateInventoryNumber();
        });
    }
    /**
     * The "booted" method of the model.
     *
     * This method will automatically generate a 'numero' before creating a new Commande.
     */
    public static function generateInventoryNumber()
    {
        $currentDate = Carbon::now();
        $year = $currentDate->format('y'); // Last two digits of the current year
        $dayOfYear = $currentDate->format('z') + 1; // Day of the year (1-365/366)
        $dailyCount = self::getDailyInventoryCount(); // Unique ID for the day starting from 000

        // Format the number as: YY-DDD-XXX
        return sprintf('%s-%03d-%03d', $year, $dayOfYear, $dailyCount);
    }

    /**
     * Get the number of inventories created today to generate a unique daily ID.
     *
     * @return int
     */
    public static function getDailyInventoryCount()
    {
        $today = Carbon::now()->startOfDay();

        // Count inventories created since the start of today
        return self::where('created_at', '>=', $today)->count() + 1;
    }

}