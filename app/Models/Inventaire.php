<?php

namespace App\Models;

use Carbon\Carbon;
use App\Models\Produit;
use App\Models\InventaireProduit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Inventaire extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $fillable = [
        'numero',
        'type',
        'rayon',
        'created_by',
        'total_reel_cfa',
        'total_initial_cfa',
        'valeur_achat',
        'valeur_vente',
        'total_css',
        'is_closed',
        'closed_by',
        'closed_at',
        'ecart_only',
        'is_suspended',
        'suspended_by',
        'suspended_at',
        'statut'
    ];

    public function produits()
    {
        return $this->belongsToMany(Produit::class);
    }

    public function inventaire_produits()
    {
        return $this->hasMany(InventaireProduit::class);
    }
    
    // Boot method to hook into the creating event
    public static function boot()
    {
        parent::boot();

        // Hook into the creating event before the record is created
        static::creating(function ($inventaire) {
            $inventaire->numero = self::generateInventoryNumber();
        });
    }

    /**
     * Generate the inventory number following the format: YY-DDD-XXX.
     *
     * @return string
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


   
