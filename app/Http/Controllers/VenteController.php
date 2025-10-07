<?php

namespace App\Http\Controllers;
use App\Models\Vente;
use App\Models\Produit;
use Illuminate\Http\Request;
use App\Models\ReservationProduit;
use App\Http\Resources\VenteResource;
use Illuminate\Support\Facades\Validator;

class VenteController extends Controller
{

    public function index() 
    {
        $ventes = Vente::latest();

        if (isset($_GET['from_period_debut'])) {
            $ventes = $ventes->where('created_at', '>=',date('Y-m-d', strtotime($_GET['from_period_debut'])) . ' 00:00:00');
        }

        if (isset($_GET['from_period_fin'])) {
            $ventes = $ventes->where('created_at', '<=',date('Y-m-d', strtotime($_GET['from_period_fin'])) . ' 23:59:59');
        }

        if (isset($_GET['req_count'])) return $this->filterByColumn('ventes', $ventes)->count();

        if (isset($_GET['contain_product'])) {
            $produitId = $_GET['contain_product'];
            $ventes =  $ventes->whereHas('reservation_produits', function ($query) use ($produitId) {
                $query->where('produit_id', $produitId);
            });
            
        };

        if (isset($_GET['req_count'])) return $this->filterByColumn('ventes', $ventes)->count();

       
        return VenteResource::collection($this->AsdecodefilterBy('ventes', $ventes));
    }

    // public function getReservationsByProduit($id)
    // {
    //     // Récupérer les réservations avec un produit_id correspondant à l'id fourni
    //     $reservations = ReservationProduit::where('produit_id', $id)
    //         ->distinct('reservation_id')  // Sélectionne les réservations uniques en fonction de 'reservation_id'
    //         ->get(['reservation_id']);    // Récupérer uniquement le champ 'reservation_id'

    //     return $reservations;  // Retourner le résultat sous forme de JSON
    // }

    public function calculerCoutTotal()
    {
        // Récupérer tous les produits
        $ventes = Vente::where('statut', '!=', 'Annulé');
        if (isset($_GET['from_period_debut'])) {
            $ventes = $ventes->where('created_at', '>=',date('Y-m-d', strtotime($_GET['from_period_debut'])) . ' 00:00:00');
        }

        if (isset($_GET['from_period_fin'])) {
            $ventes = $ventes->where('created_at', '<=',date('Y-m-d', strtotime($_GET['from_period_fin'])) . ' 23:59:59');
        }

        // Variables pour stocker les coûts totaux
        $coutTotalTTC = 0;
        $coutTotalHT = 0;
        $coutTotalTVA = 0;
        $coutTotalCSS = 0;
        $coutTotalCA = 0;
        $coutTotalPC = 0;
        $margeTotal = 0;
        $panierMoyenTotal = 0;
        $coutAchatTotalHT = 0;

        // Parcourir chaque produit pour effectuer les calculs
        foreach ($ventes->get() as $vente) {
            // Convertir les prix  en flottants, si non null
            $ttc = floatval($vente->total_client ?? 0);
            $ht = floatval($vente->ht ?? 0);
            $tva = floatval($vente->tva ?? 0);
            $css = floatval($vente->css ?? 0);
            $ca = floatval($vente->total ?? 0);
            $pc = floatval($vente->total_prise_en_charge ?? 0);
            
            // Calculer le coût total pour chaque vente
            $coutTotalTTC += $ttc;
            $coutTotalHT += $ht;
            $coutTotalTVA += $tva;
            $coutTotalCSS += $css;
            $coutTotalCA += $ca;
            $coutTotalPC += $pc;

            $coutAchatTotalHT += $this->calcPrixAchatVente($vente->id);
        }


        // calculer la marge en faisant le PV HT - PA HT

        $margeTotal =  $coutTotalHT - $coutAchatTotalHT;

        if ($ventes->count() > 0) {
            $panierMoyenTotal = $coutTotalTTC / intval($ventes->count());
        }

        // Retourner les résultats
        return [
            'total_client' => $coutTotalTTC,
            'total_ht' => $coutTotalHT,
            'total_tva' => $coutTotalTVA,
            'total_css' => $coutTotalCSS,
            'total_ca' => $coutTotalCA,
            'total_pc' => $coutTotalPC,
            'marge' => $margeTotal,
            'panier_moyen' => $panierMoyenTotal,
        ];
    }

    public function calcPrixAchatVente($id)
    {
        $vente = Vente::find($id);
        $coutAchatTotal = 0;
        //recuperer les produits de la vente
        foreach (ReservationProduit::where('vente_id', $id)->get() as $reserv_produit) {
            $prix_achat = floatval($reserv_produit->prix_achat ?? 0);
            $qte = intval($reserv_produit->qte ?? 0);
            $hasTVA = intval($reserv_produit->produit['tva'] ?? 0);
            $hasCSS = intval($reserv_produit->produit['css'] ?? 0);
            $totalAchat = $prix_achat * $qte;

            if ($hasTVA > 0) {
                $totalTVA = $totalAchat * floatval(env('APP_TVA'));
                $totalAchat = $totalAchat - $totalTVA;
            }

            if ($hasCSS > 0) {
                $totalCSS = $totalAchat * floatval(env('APP_CSS'));
                $totalAchat = $totalAchat - $totalCSS;
            }

            $coutAchatTotal += $totalAchat;
        }

        return $coutAchatTotal;
    }

    public function store(Request $request) 
    {
        $validator = Validator::make(
           $request->all(),
           [
               //'caisse' => 'required',
               //'client' => 'required',
               //'reservation' => 'required',
               //'user' => 'required',
               //'total' => 'required',
               //'tva' => 'required',
               //'css' => 'required',
               //'garde' => 'required',
               //'ht' => 'required',
               //'total_garde' => 'required',
               //'isannule' => 'required',
               //'nom_assure' => 'required',
               //'identifiant_assure' => 'required',
               //'numero_feuille_assure' => 'required',
               //'secteur_assure' => 'required',
               //'montant_recu' => 'required',
           ],
           $messages = [
               //'caisse.required' => 'Le champ caisse ne peut etre vide',
               //'client.required' => 'Le champ client ne peut etre vide',
               //'reservation.required' => 'Le champ reservation ne peut etre vide',
               //'user.required' => 'Le champ user ne peut etre vide',
               //'total.required' => 'Le champ total ne peut etre vide',
               //'tva.required' => 'Le champ tva ne peut etre vide',
               //'css.required' => 'Le champ css ne peut etre vide',
               //'garde.required' => 'Le champ garde ne peut etre vide',
               //'ht.required' => 'Le champ ht ne peut etre vide',
               //'total_garde.required' => 'Le champ total_garde ne peut etre vide',
               //'isannule.required' => 'Le champ isannule ne peut etre vide',
               //'nom_assure.required' => 'Le champ nom_assure ne peut etre vide',
               //'identifiant_assure.required' => 'Le champ identifiant_assure ne peut etre vide',
               //'numero_feuille_assure.required' => 'Le champ numero_feuille_assure ne peut etre vide',
               //'secteur_assure.required' => 'Le champ secteur_assure ne peut etre vide',
               //'montant_recu.required' => 'Le champ montant_recu ne peut etre vide',
           ]
         );

        $ventes = Vente::latest();
        if ($ventes
        ->where('caisse', $request->caisse)
        ->where('client', $request->client)
        ->where('reservation', $request->reservation)
        ->where('user', $request->user)
        ->where('total', $request->total)
        ->where('tva', $request->tva)
        ->where('css', $request->css)
        ->where('garde', $request->garde)
        ->where('ht', $request->ht)
        ->where('total_garde', $request->total_garde)
        ->where('isannule', $request->isannule)
        ->where('nom_assure', $request->nom_assure)
        ->where('identifiant_assure', $request->identifiant_assure)
        ->where('numero_feuille_assure', $request->numero_feuille_assure)
        ->where('secteur_assure', $request->secteur_assure)
        ->where('montant_recu', $request->montant_recu)
        ->where('statut', $request->statut)
        ->first()) {
           $messages = [ 'Cet enregistrement existe déjà' ];
           return $this->sendApiErrors($messages);
        }

        if ($validator->fails()) return $this->sendApiErrors($validator->errors()->all());

        $vente = Vente::create($request->all());
        return $this->sendApiResponse($vente, 'Vente ajouté', 201);
    }

    public function show($id)
    {
        return new VenteResource(Vente::find($id));
    }

    public function update(Request $request, $id) 
    {
        $validator = Validator::make(
           $request->all(),
           [
               //'caisse' => 'required',
               //'client' => 'required',
               //'reservation' => 'required',
               //'user' => 'required',
               //'total' => 'required',
               //'tva' => 'required',
               //'css' => 'required',
               //'garde' => 'required',
               //'ht' => 'required',
               //'total_garde' => 'required',
               //'isannule' => 'required',
               //'nom_assure' => 'required',
               //'identifiant_assure' => 'required',
               //'numero_feuille_assure' => 'required',
               //'secteur_assure' => 'required',
               //'montant_recu' => 'required',
           ],
           $messages = [
               //'caisse.required' => 'Le champ caisse ne peut etre vide',
               //'client.required' => 'Le champ client ne peut etre vide',
               //'reservation.required' => 'Le champ reservation ne peut etre vide',
               //'user.required' => 'Le champ user ne peut etre vide',
               //'total.required' => 'Le champ total ne peut etre vide',
               //'tva.required' => 'Le champ tva ne peut etre vide',
               //'css.required' => 'Le champ css ne peut etre vide',
               //'garde.required' => 'Le champ garde ne peut etre vide',
               //'ht.required' => 'Le champ ht ne peut etre vide',
               //'total_garde.required' => 'Le champ total_garde ne peut etre vide',
               //'isannule.required' => 'Le champ isannule ne peut etre vide',
               //'nom_assure.required' => 'Le champ nom_assure ne peut etre vide',
               //'identifiant_assure.required' => 'Le champ identifiant_assure ne peut etre vide',
               //'numero_feuille_assure.required' => 'Le champ numero_feuille_assure ne peut etre vide',
               //'secteur_assure.required' => 'Le champ secteur_assure ne peut etre vide',
               //'montant_recu.required' => 'Le champ montant_recu ne peut etre vide',
           ]
         );

        $ventes = Vente::latest();
        if ($ventes
        ->where('caisse', $request->caisse)
        ->where('client', $request->client)
        // ->where('reservation', $request->reservation)
        ->where('user', $request->user)
        ->where('total', $request->total)
        ->where('tva', $request->tva)
        ->where('css', $request->css)
        ->where('garde', $request->garde)
        ->where('ht', $request->ht)
        ->where('total_garde', $request->total_garde)
        ->where('isannule', $request->isannule)
        ->where('nom_assure', $request->nom_assure)
        ->where('identifiant_assure', $request->identifiant_assure)
        ->where('numero_feuille_assure', $request->numero_feuille_assure)
        ->where('secteur_assure', $request->secteur_assure)
        ->where('montant_recu', $request->montant_recu)
        ->where('statut', $request->statut)
        ->where('id','!=', $id)->first()) {
           $messages = [ 'Cet enregistrement existe déjà' ];
           return $this->sendApiErrors($messages);
        }

        if ($validator->fails()) return $this->sendApiErrors($validator->errors()->all());

        $vente = Vente::find($id);
        $vente->update($request->all());
        return $this->sendApiResponse($vente, 'Vente modifié', 201);
    }

    public function destroy($id) 
    {
        $vente = Vente::find($id);
        $vente->delete();

        return $this->sendApiResponse($vente, 'Vente supprimé');
    }

    public function cancel($id)
    {
        $vente = Vente::find($id);

        //on verifie si on a pas deja annulé
        if ($vente->statut == 'Annulé') {
            return $this->sendApiResponse($vente, 'Vente déjà annulé');
        }
        
        //sion on annule 
        $vente->update([
            'statut' => 'Annulé'
        ]);

        //annuler les produits de cette vente
        $produitsAnnul = ReservationProduit::where('vente_id', $id)->get();
        foreach ($produitsAnnul as $reserv_produit) {
            $reserv_produit->update([
                'statut' => 'Annulé',
                'is_sold' => false
            ]);

            //remettre le produit en stock apres annulation
            $prod = Produit::find($reserv_produit->produit_id);
            $prod->update([
                'qte' => intval($prod->qte) + intval($reserv_produit->qte),
            ]);
        }

        return $this->sendApiResponse($vente, 'Vente annulé');
    }

}
