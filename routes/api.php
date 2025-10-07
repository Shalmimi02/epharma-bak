<?php

use App\Models\Produit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\StatistiqueController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
use App\Http\Controllers\Api\QueryController;

Route::post('/query', [QueryController::class, 'handle'])->middleware('auth:sanctum');

Route::post('/premiere-connexion', [App\Http\Controllers\AuthController::class, 'index']);
Route::post('/login', [App\Http\Controllers\AuthController::class, 'authenticate']);
Route::post('/logout', [App\Http\Controllers\AuthController::class, 'logout']);
Route::get('/epg/list-versions', [App\Http\Controllers\AuthController::class, 'listVersions']);

Route::get('/produits/{code}/disponibilite', [App\Http\Controllers\ProduitController::class, 'verifierDisponibilite']);

// Endpoint de requête protégé
Route::post('/query', function (Request $request) {
    return response()->json(['data' => []]);
})->middleware('auth:sanctum');

// rechercher depuis le panel admin
Route::get('/recherche_produits', function (Request $request) {
    $libelle = $request->query('libelle');
    if (!$libelle) {
        return response()->json([]);
    }

    $produits = Produit::where('libelle', 'LIKE', "%$libelle%")->limit(10)->get(['cip', 'cip_deux', 'libelle', 'qte', 'prix_de_vente']);

    return response()->json($produits);
});

// Réservation depuis le panel admin
Route::post('/ttm/reservation', function (Request $request) {
    // $produit = Produit::where('cip', $request->cip)->first();

    // if ($produit && $produit->qte >= $request->qte) {
    //     $produit->qte -= $request->qte;
    //     $produit->save();
    //     return response()->json(['message' => 'Réservation confirmée']);
    // }

    // return response()->json(['error' => 'Stock insuffisant'], 400);

    return 'service indisponible';
});

// Route::middleware('auth:sanctum')->group(function () {

    Route::post('/backup-database', [App\Http\Controllers\AuthController::class, 'backup']);
    Route::post('/backup/restore/{id}', [App\Http\Controllers\AuthController::class, 'restoreDatabase']);
    Route::get('/backups', function () {
        return \App\Models\Backup::orderBy('created_at', 'desc')->limit(5)->get();
    });

    Route::get('/pharmacie-info', [App\Http\Controllers\AuthController::class, 'getPharmacieInfo']);
    Route::post('/pharmacie-info', [App\Http\Controllers\AuthController::class, 'savePharmacieInfo']);
    Route::get('/pharmacie-sftp', [App\Http\Controllers\AuthController::class, 'getPharmacieSftp']);
    Route::post('/pharmacie-sftp', [App\Http\Controllers\AuthController::class, 'savePharmacieSftp']);

    Route::get('/notifications', [App\Http\Controllers\NotificationController::class, 'index']);
    Route::post('/notifications', [App\Http\Controllers\NotificationController::class, 'store']);
    Route::get('/notifications/{id}', [App\Http\Controllers\NotificationController::class, 'show']);
    Route::post('/notifications/{userId}/read-all', [App\Http\Controllers\NotificationController::class, 'readAll']);


    Route::get('/utilisateurs', [App\Http\Controllers\UtilisateurController::class, 'index']);
    Route::post('/utilisateurs', [App\Http\Controllers\UtilisateurController::class, 'store']);
    Route::get('/utilisateurs/{id}', [App\Http\Controllers\UtilisateurController::class, 'show']);
    Route::post('/utilisateurs/{id}/update', [App\Http\Controllers\UtilisateurController::class, 'update']);
    Route::post('/utilisateurs/{id}/reset-password', [App\Http\Controllers\UtilisateurController::class, 'reset_password']);
    Route::post('/utilisateurs/{id}/update-password', [App\Http\Controllers\UtilisateurController::class, 'update_password']);
    Route::post('/utilisateurs/{id}/destroy', [App\Http\Controllers\UtilisateurController::class, 'destroy']);
    Route::post('/utilisateurs/destroy-group', [App\Http\Controllers\UtilisateurController::class, 'destroy_group']);


    Route::get('/rayons', [App\Http\Controllers\RayonController::class, 'index']);
    Route::post('/rayons', [App\Http\Controllers\RayonController::class, 'store']);
    Route::get('/rayons/{id}', [App\Http\Controllers\RayonController::class, 'show']);
    Route::post('/rayons/{id}/update', [App\Http\Controllers\RayonController::class, 'update']);
    Route::post('/rayons/{id}/destroy', [App\Http\Controllers\RayonController::class, 'destroy']);
    Route::post('/rayons/destroy-group', [App\Http\Controllers\RayonController::class, 'destroy_group']);
    Route::get('/statistiques/rayons-inventaire/{id}', [App\Http\Controllers\RayonController::class, 'get_inventaire_rayon']);


    Route::get('/produits', [App\Http\Controllers\ProduitController::class, 'index']);
    Route::post('/produits-equivalents', [App\Http\Controllers\ProduitController::class, 'equivalent_filter']);
    Route::get('/produits/selected', [App\Http\Controllers\ProduitController::class, 'selected_by_id']);
    Route::get('/produits/filtered', [App\Http\Controllers\ProduitController::class, 'filtered_by_keyword']);
    Route::post('/produits', [App\Http\Controllers\ProduitController::class, 'store']);
    Route::get('/produits/{id}', [App\Http\Controllers\ProduitController::class, 'show']);
    Route::post('/produits/{id}/update', [App\Http\Controllers\ProduitController::class, 'update']);
    Route::post('/produits/{id}/destroy', [App\Http\Controllers\ProduitController::class, 'destroy']);
    Route::post('/produits/destroy-group', [App\Http\Controllers\ProduitController::class, 'destroy_group']);
    Route::post('/produits/import-excel', [App\Http\Controllers\ProduitController::class, 'import_excel']);
    Route::post('/produits/reduire-quantite', [App\Http\Controllers\ProduitController::class, 'reduireQuantite']);
    Route::get('/produits-calc-compteur', [App\Http\Controllers\ProduitController::class, 'calculerCoutTotal']);
    Route::post('/produits/comparer', [App\Http\Controllers\ProduitController::class, 'equivalent_filter']);



    Route::get('/commandes', [App\Http\Controllers\CommandeController::class, 'index']);
    Route::post('/commandes', [App\Http\Controllers\CommandeController::class, 'store']);
    Route::get('/commandes/{id}', [App\Http\Controllers\CommandeController::class, 'show']);
    Route::get('/commandes-du-produit/{id}', [App\Http\Controllers\CommandeController::class, 'get_commandes_of_product']);
    Route::post('/commandes/ajouter-produit/{id}', [App\Http\Controllers\CommandeController::class, 'add_product']);
    Route::post('/commandes/{id}/update', [App\Http\Controllers\CommandeController::class, 'update']);
    Route::post('/commandes/{id}/destroy', [App\Http\Controllers\CommandeController::class, 'destroy']);
    Route::post('/commandes/destroy-group', [App\Http\Controllers\CommandeController::class, 'destroy_group']);
    Route::get('/commandes-calc-compteur', [App\Http\Controllers\CommandeController::class, 'calculerCoutTotalCommande']);
    Route::get('/commandes-calc-compteur/{id}', [App\Http\Controllers\CommandeController::class, 'calculerCoutTotal']);
    Route::post('/commandes-calc-produits/{id}', [App\Http\Controllers\CommandeController::class, 'recalculerCout']);


    Route::get('/fournisseurs', [App\Http\Controllers\FournisseurController::class, 'index']);
    Route::post('/fournisseurs', [App\Http\Controllers\FournisseurController::class, 'store']);
    Route::get('/fournisseurs/{id}', [App\Http\Controllers\FournisseurController::class, 'show']);
    Route::post('/fournisseurs/{id}/update', [App\Http\Controllers\FournisseurController::class, 'update']);
    Route::post('/fournisseurs/{id}/destroy', [App\Http\Controllers\FournisseurController::class, 'destroy']);
    Route::post('/fournisseurs/destroy-group', [App\Http\Controllers\FournisseurController::class, 'destroy_group']);


    Route::get('/inventaires', [App\Http\Controllers\InventaireController::class, 'index']);
    Route::post('/inventaires', [App\Http\Controllers\InventaireController::class, 'store']);
    Route::get('/inventaires/{id}', [App\Http\Controllers\InventaireController::class, 'show']);
    Route::post('/inventaires/{id}/update', [App\Http\Controllers\InventaireController::class, 'update']);
    Route::post('/inventaires/{id}/destroy', [App\Http\Controllers\InventaireController::class, 'destroy']);
    Route::post('/inventaires/destroy-group', [App\Http\Controllers\InventaireController::class, 'destroy_group']);
    Route::get('/inventaires-calc-compteur/{id}', [App\Http\Controllers\InventaireController::class, 'calculerCoutTotal']);


    Route::get('/commande_produit', [App\Http\Controllers\CommandeProduitController::class, 'index']);
    Route::post('/commande_produit/import-excel', [App\Http\Controllers\CommandeProduitController::class, 'import_excel']);
    Route::get('/commande_produit/statistiques', [App\Http\Controllers\CommandeProduitController::class, 'stat']);
    Route::post('/commande_produit', [App\Http\Controllers\CommandeProduitController::class, 'store']);
    Route::get('/commande_produit/{id}', [App\Http\Controllers\CommandeProduitController::class, 'show']);
    Route::post('/commande_produit/{id}/update', [App\Http\Controllers\CommandeProduitController::class, 'update']);
    Route::post('/commande_produit/{id}/destroy', [App\Http\Controllers\CommandeProduitController::class, 'destroy']);
    Route::post('/commande_produit/destroy', [App\Http\Controllers\CommandeProduitController::class, 'destroy2']);


    Route::get('/inventaire_produit', [App\Http\Controllers\InventaireProduitController::class, 'index']);
    Route::post('/inventaire_produit', [App\Http\Controllers\InventaireProduitController::class, 'store']);
    Route::get('/inventaire_produit/{id}', [App\Http\Controllers\InventaireProduitController::class, 'show']);
    Route::post('/inventaire_produit/{id}/update', [App\Http\Controllers\InventaireProduitController::class, 'update']);
    Route::post('/inventaire_produit/{id}/destroy', [App\Http\Controllers\InventaireProduitController::class, 'destroy']);
    Route::post('/inventaire_produit/destroy-group', [App\Http\Controllers\InventaireProduitController::class, 'destroy_group']);


    Route::get('/mouvements', [App\Http\Controllers\MouvementController::class, 'index']);
    Route::post('/mouvements', [App\Http\Controllers\MouvementController::class, 'store']);
    Route::get('/mouvements/{id}', [App\Http\Controllers\MouvementController::class, 'show']);
    Route::post('/mouvements/{id}/update', [App\Http\Controllers\MouvementController::class, 'update']);
    Route::post('/mouvements/{id}/destroy', [App\Http\Controllers\MouvementController::class, 'destroy']);
    Route::post('/mouvements/destroy-group', [App\Http\Controllers\MouvementController::class, 'destroy_group']);


    Route::get('/prod_familles', [App\Http\Controllers\ProdFamilleController::class, 'index']);
    Route::post('/prod_familles', [App\Http\Controllers\ProdFamilleController::class, 'store']);
    Route::get('/prod_familles/{id}', [App\Http\Controllers\ProdFamilleController::class, 'show']);
    Route::post('/prod_familles/{id}/update', [App\Http\Controllers\ProdFamilleController::class, 'update']);
    Route::post('/prod_familles/{id}/destroy', [App\Http\Controllers\ProdFamilleController::class, 'destroy']);
    Route::post('/prod_familles/destroy-group', [App\Http\Controllers\ProdFamilleController::class, 'destroy_group']);


    Route::get('/prod_formes', [App\Http\Controllers\ProdFormeController::class, 'index']);
    Route::post('/prod_formes', [App\Http\Controllers\ProdFormeController::class, 'store']);
    Route::get('/prod_formes/{id}', [App\Http\Controllers\ProdFormeController::class, 'show']);
    Route::post('/prod_formes/{id}/update', [App\Http\Controllers\ProdFormeController::class, 'update']);
    Route::post('/prod_formes/{id}/destroy', [App\Http\Controllers\ProdFormeController::class, 'destroy']);
    Route::post('/prod_formes/destroy-group', [App\Http\Controllers\ProdFormeController::class, 'destroy_group']);


    Route::get('/prod_natures', [App\Http\Controllers\ProdNatureController::class, 'index']);
    Route::post('/prod_natures', [App\Http\Controllers\ProdNatureController::class, 'store']);
    Route::get('/prod_natures/{id}', [App\Http\Controllers\ProdNatureController::class, 'show']);
    Route::post('/prod_natures/{id}/update', [App\Http\Controllers\ProdNatureController::class, 'update']);
    Route::post('/prod_natures/{id}/destroy', [App\Http\Controllers\ProdNatureController::class, 'destroy']);
    Route::post('/prod_natures/destroy-group', [App\Http\Controllers\ProdNatureController::class, 'destroy_group']);


    Route::get('/prod_categories', [App\Http\Controllers\ProdCategorieController::class, 'index']);
    Route::post('/prod_categories', [App\Http\Controllers\ProdCategorieController::class, 'store']);
    Route::get('/prod_categories/{id}', [App\Http\Controllers\ProdCategorieController::class, 'show']);
    Route::post('/prod_categories/{id}/update', [App\Http\Controllers\ProdCategorieController::class, 'update']);
    Route::post('/prod_categories/{id}/destroy', [App\Http\Controllers\ProdCategorieController::class, 'destroy']);
    Route::post('/prod_categories/destroy-group', [App\Http\Controllers\ProdCategorieController::class, 'destroy_group']);


    Route::get('/mouv_motifs', [App\Http\Controllers\MouvMotifController::class, 'index']);
    Route::post('/mouv_motifs', [App\Http\Controllers\MouvMotifController::class, 'store']);
    Route::get('/mouv_motifs/{id}', [App\Http\Controllers\MouvMotifController::class, 'show']);
    Route::post('/mouv_motifs/{id}/update', [App\Http\Controllers\MouvMotifController::class, 'update']);
    Route::post('/mouv_motifs/{id}/destroy', [App\Http\Controllers\MouvMotifController::class, 'destroy']);
    Route::post('/mouv_motifs/destroy-group', [App\Http\Controllers\MouvMotifController::class, 'destroy_group']);


    Route::get('/prod_classe_theraps', [App\Http\Controllers\ProdClasseTherapController::class, 'index']);
    Route::post('/prod_classe_theraps', [App\Http\Controllers\ProdClasseTherapController::class, 'store']);
    Route::get('/prod_classe_theraps/{id}', [App\Http\Controllers\ProdClasseTherapController::class, 'show']);
    Route::post('/prod_classe_theraps/{id}/update', [App\Http\Controllers\ProdClasseTherapController::class, 'update']);
    Route::post('/prod_classe_theraps/{id}/destroy', [App\Http\Controllers\ProdClasseTherapController::class, 'destroy']);
    Route::post('/prod_classe_theraps/destroy-group', [App\Http\Controllers\ProdClasseTherapController::class, 'destroy_group']);


    Route::get('/clients', [App\Http\Controllers\ClientController::class, 'index']);
    Route::get('/clients/base', [App\Http\Controllers\ClientController::class, 'getClientBase']);
    Route::post('/clients', [App\Http\Controllers\ClientController::class, 'store']);
    Route::get('/clients/{id}', [App\Http\Controllers\ClientController::class, 'show']);
    Route::get('/clients/{id}/calc-compteur', [App\Http\Controllers\ClientController::class, 'clientStatistique']);
    Route::post('/clients/{id}/update', [App\Http\Controllers\ClientController::class, 'update']);
    Route::post('/clients/{id}/destroy', [App\Http\Controllers\ClientController::class, 'destroy']);
    Route::post('/clients/destroy-group', [App\Http\Controllers\ClientController::class, 'destroy_group']);
    Route::get('/clients-calc-compteur', [App\Http\Controllers\ClientController::class, 'calculerCoutTotal']);


    Route::get('/reservations', [App\Http\Controllers\ReservationController::class, 'index']);
    Route::get('/reservations/jour', [App\Http\Controllers\ReservationController::class, 'getReservationsToday']);
    Route::get('/reservations/jour/caisse', [App\Http\Controllers\ReservationController::class, 'getReservationsCaisseToday']);
    Route::post('/reservations', [App\Http\Controllers\ReservationController::class, 'store']);
    Route::post('/reservation/facture/{id}', [App\Http\Controllers\ReservationController::class, 'validate_invoice']);
    Route::get('/reservations/{id}', [App\Http\Controllers\ReservationController::class, 'show']);
    Route::post('/reservations/{id}/update', [App\Http\Controllers\ReservationController::class, 'update']);
    Route::post('/reservations/{id}/destroy', [App\Http\Controllers\ReservationController::class, 'destroy']);
    Route::post('/reservations/destroy-group', [App\Http\Controllers\ReservationController::class, 'destroy_group']);


    Route::get('/gardes', [App\Http\Controllers\GardeController::class, 'index']);
    Route::get('/gardes/actuelle', [App\Http\Controllers\GardeController::class, 'verifierGardeActuelle']);
    Route::post('/gardes', [App\Http\Controllers\GardeController::class, 'store']);
    Route::get('/gardes/{id}', [App\Http\Controllers\GardeController::class, 'show']);
    Route::get('/gardes/{id}/produits', [App\Http\Controllers\GardeController::class, 'getProduits']);
    Route::post('/gardes/{id}/update', [App\Http\Controllers\GardeController::class, 'update']);
    Route::post('/gardes/{id}/destroy', [App\Http\Controllers\GardeController::class, 'destroy']);
    Route::post('/gardes/{id}/desactiver', [App\Http\Controllers\GardeController::class, 'desactiver']);


    Route::get('/ventes', [App\Http\Controllers\VenteController::class, 'index']);
    Route::post('/ventes', [App\Http\Controllers\VenteController::class, 'store']);
    // Routes spécifiques d'abord
    Route::post('/ventes/{id}/update', [App\Http\Controllers\VenteController::class, 'update'])
        ->where('id', '[0-9]+');
    Route::post('/ventes/{id}/destroy', [App\Http\Controllers\VenteController::class, 'destroy'])
        ->where('id', '[0-9]+');
    Route::post('/ventes/{id}/cancel', [App\Http\Controllers\VenteController::class, 'cancel'])
        ->where('id', '[0-9]+');
    Route::post('/ventes/destroy-group', [App\Http\Controllers\VenteController::class, 'destroy_group']);

    // Ensuite la route générique (GET)
    Route::get('/ventes/{id}', [App\Http\Controllers\VenteController::class, 'show'])
        ->where('id', '[0-9]+');

    Route::get('/ventes-calc-compteur', [App\Http\Controllers\VenteController::class, 'calculerCoutTotal']);


    Route::get('/caisses', [App\Http\Controllers\CaisseController::class, 'index']);
    Route::post('/caisses', [App\Http\Controllers\CaisseController::class, 'store']);
    Route::get('/caisses/{id}', [App\Http\Controllers\CaisseController::class, 'show']);
    Route::post('/caisses/{id}/update', [App\Http\Controllers\CaisseController::class, 'update']);
    Route::post('/caisses/{id}/login', [App\Http\Controllers\CaisseController::class, 'login']);
    Route::post('/caisses/{id}/destroy', [App\Http\Controllers\CaisseController::class, 'destroy']);
    Route::post('/caisses/destroy-group', [App\Http\Controllers\CaisseController::class, 'destroy_group']);


    Route::get('/reservation_produits', [App\Http\Controllers\ReservationProduitController::class, 'index']);
    Route::get('/reservation_produits/vendus', [App\Http\Controllers\ReservationProduitController::class, 'reserv_sold']);
    Route::post('/reservation_produits', [App\Http\Controllers\ReservationProduitController::class, 'store']);
    Route::post('/reservation_produits/facture', [App\Http\Controllers\ReservationProduitController::class, 'storeInDevis']);
    Route::get('/reservation_produits/{id}', [App\Http\Controllers\ReservationProduitController::class, 'show']);
    Route::post('/reservation_produits/{id}/update', [App\Http\Controllers\ReservationProduitController::class, 'update']);
    Route::post('/reservation_produits/{id}/facture/update', [App\Http\Controllers\ReservationProduitController::class, 'updateInDevis']);
    Route::post('/reservation_produits/{id}/destroy', [App\Http\Controllers\ReservationProduitController::class, 'destroy']);
    Route::post('/reservation_produits/{id}/destroy2', [App\Http\Controllers\ReservationProduitController::class, 'destroy2']);
    Route::post('/reservation_produits/destroy-group', [App\Http\Controllers\ReservationProduitController::class, 'destroy_group']);


    Route::get('/factures', [App\Http\Controllers\FactureController::class, 'index']);
    Route::post('/factures', [App\Http\Controllers\FactureController::class, 'store']);
    Route::get('/factures/{id}', [App\Http\Controllers\FactureController::class, 'show']);
    Route::post('/factures/{id}/update', [App\Http\Controllers\FactureController::class, 'update']);
    Route::post('/factures/{id}/destroy', [App\Http\Controllers\FactureController::class, 'destroy']);
    Route::post('/factures/destroy-group', [App\Http\Controllers\FactureController::class, 'destroy_group']);


    Route::get('/billetages', [App\Http\Controllers\BilletageController::class, 'index']);
    Route::post('/billetages', [App\Http\Controllers\BilletageController::class, 'store']);
    Route::get('/billetages/{id}', [App\Http\Controllers\BilletageController::class, 'show']);
    Route::get('/billetages/{id}/produits', [App\Http\Controllers\BilletageController::class, 'getProduits']);
    Route::post('/billetages/{id}/update', [App\Http\Controllers\BilletageController::class, 'update']);
    Route::post('/billetages/{id}/destroy', [App\Http\Controllers\BilletageController::class, 'destroy']);
    Route::post('/billetages/destroy-group', [App\Http\Controllers\BilletageController::class, 'destroy_group']);

    Route::get('/remboursements', [App\Http\Controllers\RemboursementController::class, 'index']);
    Route::post('/remboursements', [App\Http\Controllers\RemboursementController::class, 'store']);
    Route::get('/remboursements/{id}', [App\Http\Controllers\RemboursementController::class, 'show']);
    Route::post('/remboursements/{id}/update', [App\Http\Controllers\RemboursementController::class, 'update']);
    Route::post('/remboursements/{id}/destroy', [App\Http\Controllers\RemboursementController::class, 'destroy']);
    Route::post('/remboursements/destroy-group', [App\Http\Controllers\RemboursementController::class, 'destroy_group']);

    Route::get('/statistiques/sold-products', [StatistiqueController::class, 'getSoldProductsForLastMonth']);
    Route::get('/statistiques/ventes-annulees', [StatistiqueController::class, 'getVentesSemaine']);
    Route::get('/statistiques/total-ventes-utilisateur', [StatistiqueController::class, 'getChiffreAffaireUtilisateur']);
    Route::get('/statistiques/panier-moyen', [StatistiqueController::class, 'panierMoyenParUtilisateur']);
    Route::get('/statistiques/clients-utilisateur', [StatistiqueController::class, 'ClientServisUtilisateur']);
    Route::get('/stat/chiffre-affaire', [StatistiqueController::class, 'getChiffreAffaireMensuel']);
    Route::get('/stat/top20-produits-vendus', [StatistiqueController::class, 'getTop20ProduitsVendus']);
    Route::get('/stat/top20-produits-moins-vendus', [StatistiqueController::class, 'getTop20ProduitsMoinsVendus']);
    Route::get('/statistiques/dernier-inventaire', [StatistiqueController::class, 'getLatestInventaireProduits']);
    Route::get('/statistiques/diff-dernier-inventaire', [StatistiqueController::class, 'getLatestInventaireData']);
    Route::get('/statistiques/produits-achetes', [StatistiqueController::class, 'getProduitsAchetes']);
    Route::get('/marge-benefice', [StatistiqueController::class, 'getMargeBenefice']);
    Route::get('produits/{id}/stats', [StatistiqueController::class, 'getProductStats']);
    Route::get('/sales-productivity/{product_id}', [StatistiqueController::class, 'getSalesProductivity']);
    Route::get('/sales-history/{productId}', [StatistiqueController::class, 'getSalesHistory']);
    Route::get('/purchase-history/{productId}', [StatistiqueController::class, 'getPurchaseHistory']);

     // Analyse des ventes
     Route::get('/statistiques/evolution-ventes', [StatistiqueController::class, 'getEvolutionVentes']);
     Route::get('/statistiques/ventes-par-categorie', [StatistiqueController::class, 'getVentesParCategorie']);

     // Gestion des stocks
     Route::get('/statistiques/valeur-stocks', [StatistiqueController::class, 'getValeurStocks']);
     Route::get('/statistiques/produits-obsoletes', [StatistiqueController::class, 'getProduitsObsoletes']);

     // Analyse financière
     Route::get('/statistiques/marge-brute-mensuelle', [StatistiqueController::class, 'getMargeBruteMensuelle']);
     Route::get('/statistiques/marge-nette', [StatistiqueController::class, 'getMargeNette']);

     // Performances des employés
     Route::get('/statistiques/ventes-par-employe', [StatistiqueController::class, 'getVentesParEmploye']);

     // Planification des réapprovisionnements
     Route::get('/statistiques/planification-reapprovisionnement', [StatistiqueController::class, 'getPlanificationReapprovisionnement']);
// });

