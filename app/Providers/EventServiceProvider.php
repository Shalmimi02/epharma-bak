<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        \App\Models\User::observe(\App\Observers\UtilisateurObserver::class);
        \App\Models\Produit::observe(\App\Observers\ProduitObserver::class);
        \App\Models\CommandeProduit::observe(\App\Observers\CommandeProduitObserver::class);
        \App\Models\Fournisseur::observe(\App\Observers\FournisseurObserver::class);
        \App\Models\Mouvement::observe(\App\Observers\MouvementObserver::class);
        \App\Models\Commande::observe(\App\Observers\CommandeObserver::class);
        \App\Models\MouvMotif::observe(\App\Observers\MouvMotifObserver::class);
        \App\Models\ProdCategorie::observe(\App\Observers\ProdCategorieObserver::class);
        \App\Models\ProdClasseTherap::observe(\App\Observers\ProdClasseTherapObserver::class);
        \App\Models\ProdFamille::observe(\App\Observers\ProdFamilleObserver::class);
        \App\Models\ProdForme::observe(\App\Observers\ProdFormeObserver::class);
        \App\Models\ProdNature::observe(\App\Observers\ProdNatureObserver::class);
        \App\Models\ReservationProduit::observe(\App\Observers\ReservationProduitObserver::class);
        \App\Models\Reservation::observe(\App\Observers\ReservationObserver::class);
        \App\Models\Billetage::observe(\App\Observers\BilletageObserver::class);
        \App\Models\Remboursement::observe(\App\Observers\RemboursementObserver::class);
        \App\Models\Vente::observe(\App\Observers\VenteObserver::class);
        \App\Models\Client::observe(\App\Observers\ClientObserver::class);
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
