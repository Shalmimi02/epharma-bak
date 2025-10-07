<?php

namespace App\Http\Controllers;
use DB;
use Illuminate\Http\Request;
use App\Models\ReservationProduit;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\ProduitVendusResource;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Http\Resources\ReservationProduitResource;

class ReservationProduitController extends Controller
{

    public function index() 
    {
        $reservation_produits = ReservationProduit::orderBy('libelle', 'ASC');

         if (isset($_GET['from_period_debut'])) {
            $reservation_produits = $reservation_produits->where('created_at', '>=',$_GET['from_period_debut']);
        }

        if (isset($_GET['from_period_fin'])) {
            $reservation_produits = $reservation_produits->where('created_at', '<=',$_GET['from_period_fin']);
        }

        if (isset($_GET['req_count'])) return $this->filterByColumn('reservation_produits', $reservation_produits)->count();

        return ReservationProduitResource::collection($this->AsdecodefilterBy('reservation_produits', $reservation_produits));
    }

    public function reserv_sold() 
    {

        // if (isset($_GET['req_contain'])) {
        //     $produits_vendus = ReservationProduit::where('libelle','like', '%'.$_GET['req_contain'].'%')->latest();
        // } else $produits_vendus = ReservationProduit::latest();

        // $produits_vendus = $produits_vendus->select('libelle','produit_id', 'produit',
        //     DB::raw('COUNT(*) as qte_vendus'))->whereBetween('created_at', [
        //     date('Y-m-d', strtotime($_GET['from_period_debut'])). ' 00:00:00', 
        //     date('Y-m-d', strtotime($_GET['from_period_fin'])). ' 23:59:59'
        // ])->where('is_sold', true)->groupBy('libelle','produit_id', 'produit');

        // Vérifiez si les paramètres nécessaires sont présents
        $reqContain = $_GET['req_contain'] ?? null;
        $fromPeriodDebut = $_GET['from_period_debut'] ?? null;
        $fromPeriodFin = $_GET['from_period_fin'] ?? null;

        if (!$fromPeriodDebut || !$fromPeriodFin) {
            // Gérer l'absence de dates : soit retour d'une erreur, soit des valeurs par défaut
            die('Les dates "from_period_debut" et "from_period_fin" sont obligatoires.');
        }

       
        // Construction de la requête de base
        $produitsVendusQuery = ReservationProduit::query();

        // Filtre par libellé si le paramètre est défini
        if (!empty($reqContain)) {
            $produitsVendusQuery->where('libelle', 'like', '%' . $reqContain . '%');
        }

        // Ajout des filtres pour les dates et la condition 'is_sold'
        $produitsVendus = $produitsVendusQuery
            ->select(
                'libelle',
                'produit_id',
                'produit',
                DB::raw('SUM(qte) as qte_vendus')
            )
            ->whereBetween('created_at', [
                date('Y-m-d', strtotime($fromPeriodDebut)) . ' 00:00:00',
                date('Y-m-d', strtotime($fromPeriodFin)) . ' 23:59:59',
            ])
            ->where('is_sold', true)
            ->groupBy('libelle', 'produit_id', 'produit');

        if (isset($_GET['page']) && isset($_GET['rows'])){
            return ProduitVendusResource::collection($produitsVendus->paginate($_GET['rows']));
        }

        return ProduitVendusResource::collection($produitsVendus->get());
    }
    
    public function store(Request $request) 
    {
        $validator = Validator::make(
           $request->all(),
           [
               //'libelle' => 'required',
               //'qte' => 'required',
               //'prix_de_vente' => 'required',
               //'cout_total' => 'required',
               //'produit' => 'required',
               //'reservation_id' => 'required',
           ],
           $messages = [
               //'libelle.required' => 'Le champ libelle ne peut etre vide',
               //'qte.required' => 'Le champ qte ne peut etre vide',
               //'prix_de_vente.required' => 'Le champ prix_de_vente ne peut etre vide',
               //'cout_total.required' => 'Le champ cout_total ne peut etre vide',
               //'produit.required' => 'Le champ produit ne peut etre vide',
               //'reservation_id.required' => 'Le champ reservation_id ne peut etre vide',
           ]
        );

        $reservation_produits = ReservationProduit::latest();
        if ($reservation_produits
        ->where('libelle', $request->libelle)
        ->where('produit', $request->produit)
        ->where('reservation_id', $request->reservation_id)
        ->first()) {
           $messages = [ 'Cet enregistrement existe déjà' ];
           return $this->sendApiErrors($messages);
        }

        if ($validator->fails()) return $this->sendApiErrors($validator->errors()->all());

        if (isset($request->qte)) {
            $request_qte = intval($request->qte);
            $produit_qte = intval($request->produit_qte);

            if ($request_qte <= 0){
                $messages = [ 'Impossible d\'avoir une quantité 0 ou inferieur' ];
                return $this->sendApiErrors($messages);
            }

            if ($request_qte > $produit_qte){
                $messages = [ 'Impossible d\'avoir une quantité superieur au stock' ];
                return $this->sendApiErrors($messages);
            }
        }

        if (isset($request->prise_en_charge)) {
            $prise_en_charge = floatval($request->prise_en_charge);

            if ($prise_en_charge > 100){
                $messages = [ 'Impossible de depasser le taux de 100%' ];
                return $this->sendApiErrors($messages);
            }
        }

        $reservation_produit = ReservationProduit::create($request->all());
        return $this->sendApiResponse($reservation_produit, $reservation_produit->libelle.' ajouté à la réservation', 201);
    }

    public function storeInDevis(Request $request)
    {
        $validator = Validator::make(
           $request->all(),
           [
               //'libelle' => 'required',
               //'qte' => 'required',
               //'prix_de_vente' => 'required',
               //'cout_total' => 'required',
               //'produit' => 'required',
               //'reservation_id' => 'required',
           ],
           $messages = [
               //'libelle.required' => 'Le champ libelle ne peut etre vide',
               //'qte.required' => 'Le champ qte ne peut etre vide',
               //'prix_de_vente.required' => 'Le champ prix_de_vente ne peut etre vide',
               //'cout_total.required' => 'Le champ cout_total ne peut etre vide',
               //'produit.required' => 'Le champ produit ne peut etre vide',
               //'reservation_id.required' => 'Le champ reservation_id ne peut etre vide',
           ]
        );

        $reservation_produits = ReservationProduit::latest();
        if ($reservation_produits
        ->where('libelle', $request->libelle)
        ->where('produit', $request->produit)
        ->where('reservation_id', $request->reservation_id)
        ->first()) {
           $messages = [ 'Cet enregistrement existe déjà' ];
           return $this->sendApiErrors($messages);
        }

        if ($validator->fails()) return $this->sendApiErrors($validator->errors()->all());

        if (isset($request->prise_en_charge)) {
            $prise_en_charge = floatval($request->prise_en_charge);

            if ($prise_en_charge > 100){
                $messages = [ 'Impossible de depasser le taux de 100%' ];
                return $this->sendApiErrors($messages);
            }
        }

        $reservation_produit = ReservationProduit::create($request->all());
        return $this->sendApiResponse($reservation_produit, $reservation_produit->libelle.' ajouté à la réservation', 201);
    }

    public function show($id)
    {
        return new ReservationProduitResource(ReservationProduit::find($id));
    }

    public function update(Request $request, $id) 
    {
        $validator = Validator::make(
           $request->all(),
           [
               //'libelle' => 'required',
               //'qte' => 'required',
               //'prix_de_vente' => 'required',
               //'cout_total' => 'required',
               //'produit' => 'required',
               //'reservation_id' => 'required',
           ],
           $messages = [
               //'libelle.required' => 'Le champ libelle ne peut etre vide',
               //'qte.required' => 'Le champ qte ne peut etre vide',
               //'prix_de_vente.required' => 'Le champ prix_de_vente ne peut etre vide',
               //'cout_total.required' => 'Le champ cout_total ne peut etre vide',
               //'produit.required' => 'Le champ produit ne peut etre vide',
               //'reservation_id.required' => 'Le champ reservation_id ne peut etre vide',
           ]
         );

        $reservation_produits = ReservationProduit::latest();
        if ($reservation_produits
        ->where('libelle', $request->libelle)
        ->where('produit', $request->produit)
        ->where('reservation_id', $request->reservation_id)
        ->where('id','!=', $id)->first()) {
           $messages = [ 'Cet enregistrement existe déjà' ];
           return $this->sendApiErrors($messages);
        }

        if ($validator->fails()) return $this->sendApiErrors($validator->errors()->all());

        
        if (isset($request->qte)) {
            $request_qte = intval($request->qte);
            $produit_qte = intval($request->produit_qte);

            if ($request_qte <= 0){
                $messages = [ 'Impossible d\'avoir une quantité 0 ou inferieur' ];
                return $this->sendApiErrors($messages);
            }

            if ($request_qte > $produit_qte){
                $messages = [ 'Impossible d\'avoir une quantité superieur au stock' ];
                return $this->sendApiErrors($messages);
            }
        }

        if (isset($request->prise_en_charge)) {
            $prise_en_charge = floatval($request->prise_en_charge);

            if ($prise_en_charge > 100){
                $messages = [ 'Impossible de depasser le taux de 100%' ];
                return $this->sendApiErrors($messages);
            }
            if ($prise_en_charge < 0){
                $messages = [ 'Impossible de descendre le taux à 0% ou inferieur' ];
                return $this->sendApiErrors($messages);
            }
        }

        $reservation_produit = ReservationProduit::find($id);
        $reservation_produit->update($request->all());
        return $this->sendApiResponse($reservation_produit, $reservation_produit->libelle. ' mis à jour', 201);
    }

    public function updateInDevis(Request $request, $id) 
    {
        $validator = Validator::make(
           $request->all(),
           [
               //'libelle' => 'required',
               //'qte' => 'required',
               //'prix_de_vente' => 'required',
               //'cout_total' => 'required',
               //'produit' => 'required',
               //'reservation_id' => 'required',
           ],
           $messages = [
               //'libelle.required' => 'Le champ libelle ne peut etre vide',
               //'qte.required' => 'Le champ qte ne peut etre vide',
               //'prix_de_vente.required' => 'Le champ prix_de_vente ne peut etre vide',
               //'cout_total.required' => 'Le champ cout_total ne peut etre vide',
               //'produit.required' => 'Le champ produit ne peut etre vide',
               //'reservation_id.required' => 'Le champ reservation_id ne peut etre vide',
           ]
         );

        $reservation_produits = ReservationProduit::latest();
        if ($reservation_produits
        ->where('libelle', $request->libelle)
        ->where('produit', $request->produit)
        ->where('reservation_id', $request->reservation_id)
        ->where('id','!=', $id)->first()) {
           $messages = [ 'Cet enregistrement existe déjà' ];
           return $this->sendApiErrors($messages);
        }

        if ($validator->fails()) return $this->sendApiErrors($validator->errors()->all());


        if (isset($request->prise_en_charge)) {
            $prise_en_charge = floatval($request->prise_en_charge);

            if ($prise_en_charge > 100){
                $messages = [ 'Impossible de depasser le taux de 100%' ];
                return $this->sendApiErrors($messages);
            }
            if ($prise_en_charge < 0){
                $messages = [ 'Impossible de descendre le taux à 0% ou inferieur' ];
                return $this->sendApiErrors($messages);
            }
        }

        $reservation_produit = ReservationProduit::find($id);
        $reservation_produit->update($request->all());
        return $this->sendApiResponse($reservation_produit, $reservation_produit->libelle. ' mis à jour', 201);
    }
    

    public function destroy($id) 
    {
        $reservation_produit = ReservationProduit::find($id);
        $libelle = $reservation_produit->libelle;
        $reservation_produit->delete();

        return $this->sendApiResponse([], $libelle.'  la réservation supprimé');
    }

    public function destroy2(Request $request, $id) 
    {
        $reservation_produit = ReservationProduit::where('produit_id',$id)->where('reservation_id',$request->reservation_id)->first();
        $libelle = $reservation_produit->libelle;
        $reservation_produit->delete();

        return $this->sendApiResponse([], $libelle.'  la réservation supprimé');
    }

}
