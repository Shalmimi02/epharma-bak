<?php

namespace App\Http\Controllers;
use Carbon\Carbon;
use App\Models\Vente;
use App\Models\Client;
use App\Models\Facture;
use App\Models\Produit;
use App\Models\Reservation;
use Illuminate\Http\Request;
use App\Models\ReservationProduit;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\ReservationResource;

class ReservationController extends Controller
{

    public function index() 
    {
        $reservations = Reservation::latest();

        if (isset($_GET['req_count'])) return $this->filterByColumn('reservations', $reservations)->count();

        return ReservationResource::collection($this->AsdecodefilterBy('reservations', $reservations));
    }

    public function getReservationsToday() 
    {
        $today = Carbon::now()->format('Y-m-d');

        $reservations = Reservation::whereDate('created_at', $today)->orderBy('position', 'desc');

        if (isset($_GET['req_count'])) return $this->filterByColumn('reservations', $reservations)->count();

        return ReservationResource::collection($this->AsdecodefilterBy('reservations', $reservations));
    }

    public function getReservationsCaisseToday() 
    {
        $today = Carbon::now()->format('Y-m-d');

        $reservations = Reservation::whereDate('created_at', $today)->orderBy('position', 'desc')->where('status','!=','Annule')->where('status','!=','Devis');

        if (isset($_GET['req_count'])) return $this->filterByColumn('reservations', $reservations)->count();

        return ReservationResource::collection($this->AsdecodefilterBy('reservations', $reservations));
    }

    public function store(Request $request) 
    {
        // Obtenir la date actuelle sans l'heure (juste AAAA-MM-JJ)
        $today = Carbon::now()->format('Y-m-d');
        
        // Récupérer le dernier numéro de position pour les réservations créées aujourd'hui
        $lastReservation = Reservation::whereDate('created_at', $today)
            ->orderBy('position', 'desc')
            ->first();
        
        // Si une réservation existe déjà pour aujourd'hui, incrémenter le numéro de position
        if ($lastReservation) {
            $request->merge([
                'position'  => $lastReservation->position + 1,
                'prise_en_charge'  => 0
            ]);
        } else {
            // Sinon, initialiser à 1 pour la première réservation du jour
            $request->merge([
                'position'  => 1,
                'prise_en_charge'  => 0
            ]);
        }
        
        $reservation = Reservation::create($request->all());
        return $this->sendApiResponse($reservation, 'Reservation ajouté', 201);
    }

    public function show($id)
    {
        return new ReservationResource(Reservation::find($id));
    }

    public function update(Request $request, $id) 
    {
        $validator = Validator::make(
           $request->all(),
           [
               //'code' => 'required',
               //'numero' => 'required',
               //'client' => 'required',
               //'caisse' => 'required',j
               //'amount_reserved' => 'required',
               //'amount_gived' => 'required',
               //'nom_devis' => 'required',
               //'switch_caisse_at' => 'required',
               //'switch_finish_at' => 'required',
               //'switch_devis_at' => 'required',
               //'switch_dette_at' => 'required',
               //'status' => 'required',
               //'create_by' => 'required',
               //'nom_assure' => 'required',
               //'identifiant_assure' => 'required',
               //'numero_feuille_assure' => 'required',
               //'secteur_assure' => 'required',
           ],
           $messages = [
               //'code.required' => 'Le champ code ne peut etre vide',
               //'numero.required' => 'Le champ numero ne peut etre vide',
               //'client.required' => 'Le champ client ne peut etre vide',
               //'caisse.required' => 'Le champ caisse ne peut etre vide',
               //'amount_reserved.required' => 'Le champ amount_reserved ne peut etre vide',
               //'amount_gived.required' => 'Le champ amount_gived ne peut etre vide',
               //'nom_devis.required' => 'Le champ nom_devis ne peut etre vide',
               //'switch_caisse_at.required' => 'Le champ switch_caisse_at ne peut etre vide',
               //'switch_finish_at.required' => 'Le champ switch_finish_at ne peut etre vide',
               //'switch_devis_at.required' => 'Le champ switch_devis_at ne peut etre vide',
               //'switch_dette_at.required' => 'Le champ switch_dette_at ne peut etre vide',
               //'status.required' => 'Le champ status ne peut etre vide',
               //'create_by.required' => 'Le champ create_by ne peut etre vide',
               //'nom_assure.required' => 'Le champ nom_assure ne peut etre vide',
               //'identifiant_assure.required' => 'Le champ identifiant_assure ne peut etre vide',
               //'numero_feuille_assure.required' => 'Le champ numero_feuille_assure ne peut etre vide',
               //'secteur_assure.required' => 'Le champ secteur_assure ne peut etre vide',
           ]
         );

        // $reservations = Reservation::latest();
        // if ($reservations
        // ->where('code', $request->code)
        // ->where('numero', $request->numero)
        // ->where('client', $request->client)
        // ->where('caisse', $request->caisse)
        // ->where('amount_reserved', $request->amount_reserved)
        // ->where('amount_gived', $request->amount_gived)
        // ->where('nom_devis', $request->nom_devis)
        // ->where('switch_caisse_at', $request->switch_caisse_at)
        // ->where('switch_finish_at', $request->switch_finish_at)
        // ->where('switch_devis_at', $request->switch_devis_at)
        // ->where('switch_dette_at', $request->switch_dette_at)
        // ->where('status', $request->status)
        // ->where('create_by', $request->create_by)
        // ->where('nom_assure', $request->nom_assure)
        // ->where('identifiant_assure', $request->identifiant_assure)
        // ->where('numero_feuille_assure', $request->numero_feuille_assure)
        // ->where('secteur_assure', $request->secteur_assure)
        // ->where('id','!=', $id)->first()) {
        //    $messages = [ 'Cet enregistrement existe déjà' ];
        //    return $this->sendApiErrors($messages);
        // }

        // if ($validator->fails()) return $this->sendApiErrors($validator->errors()->all());

        $reservation = Reservation::find($id);


        //verifier si la position existe deja
        $today = Carbon::now()->format('Y-m-d');
        if(Vente::whereDate('date_reservation', $today)
            ->where('position', $reservation->position)
            ->first())
        {
            $messages = [ 'Cette reservation a déjà été vendu, veuillez creer une nouvelle' ];
            return $this->sendApiErrors($messages);
        }

        //si tout est correct on effectue les modifications
        $reservation->update($request->all());

        //sauvegarder les produits de la reservation
        if ($request->reservation_produits) {
            foreach ($request->reservation_produits as $reservation_produit) {
                $produit = $reservation_produit['produit'];
                if (isset($request->qte)) {
                    $request_qte = intval($reservation_produit['qte']);
                    $produit_qte = intval($produit->qte);
        
                    if ($request_qte <= 0){
                        $messages = [ 'Impossible d\'avoir une quantité 0 ou inferieur du produit '.$produit->libelle ];
                        return $this->sendApiErrors($messages);
                    }
        
                    if ($request_qte > $produit_qte){
                        $messages = [ 'Impossible d\'avoir une quantité superieur au stock du produit '.$produit->libelle ];
                        return $this->sendApiErrors($messages);
                    }
                }
        
                if (isset($reservation_produit['prise_en_charge'])) {
                    $prise_en_charge = floatval($reservation_produit['prise_en_charge']);
        
                    if ($prise_en_charge > 100){
                        $messages = [ 'Impossible de depasser le taux de 100% sur le produit '.$produit->libelle ];
                        return $this->sendApiErrors($messages);
                    }
                }
            }

            //vider dans la base avant de mettre a jour
            $produitIds = collect($request->reservation_produits)->pluck('produit_id')->toArray();

            // Supprimer les produits qui ne sont plus dans la requête
            ReservationProduit::where('reservation_id', $id)
                ->whereNotIn('produit_id', $produitIds)
                ->delete();

            //si aucuns produit n'a d'erreur on sauvegarde les produits
            foreach ($request->reservation_produits as $reservation_produit){
                ReservationProduit::updateOrCreate(
                    [
                        'reservation_id' =>  $reservation_produit['reservation_id'],
                        'produit_id' =>  $reservation_produit['produit_id']
                    ],
                    $reservation_produit
                );
            }
        }

        //on met à jour le devis
        if (isset($request->factureId)) {
            $facture = Facture::find($request->factureId);
            $facture->update([
                'net_a_payer' =>  $reservation->montant
            ]);
        }
        
        if ($request->switch_finish_at) {

            //mettre a jour le nom du caissier
            $reservation->update([
                'closed_by' => $request->switch_finish_by
            ]);
            
            $total_tva = ReservationProduit::where('reservation_id', $reservation->id)->sum('total_tva');
            $total_css = ReservationProduit::where('reservation_id', $reservation->id)->sum('total_css');
            $total_ht = ReservationProduit::where('reservation_id', $reservation->id)->sum('total_ht');
            $total_prise_en_charge = ReservationProduit::where('reservation_id', $reservation->id)->sum('total_prise_en_charge');
            $total_garde = ReservationProduit::where('reservation_id', $reservation->id)->sum('total_garde');
            $total_vente = ReservationProduit::where('reservation_id', $reservation->id)->sum('cout_total_reel');

            $vente = [
                'position' => $reservation->position,
                'caisse' => $reservation->caisse,
                'client' => $reservation->client,
                'clientId' => $reservation->client_id,
                'date_reservation' => $reservation->created_at,
                'user' => $request->switch_finish_by,
                'total' => $total_vente,
                'total_client' => $reservation->montant,
                'tva' => $total_tva,
                'css' => $total_css,
                'ht' => $total_ht,
                'total_prise_en_charge' => $total_prise_en_charge,
                'garde' => $reservation->garde_id? $reservation->garde_id : 0,
                'total_garde' => $request->$total_garde? $request->$total_garde : 0,
                'nom_assure' => $reservation->nom_assure,
                'identifiant_assure' => $reservation->identifiant_assure,
                'numero_feuille_assure' => $reservation->numero_feuille_assure,
                'secteur_assure' => $reservation->secteur_assure,
                'montant_recu' => $reservation->amount_gived,
                'reservation_id' => $reservation->id,
                'caisse_id' => $reservation->caisse_id
            ];

            if (floatval($total_prise_en_charge) > 0) {
                $vente['statut'] = 'Impayé';
                $client = Client::find($reservation->client_id);
                $client->update([
                    'current_dette' => floatval($client->current_dette) + floatval($total_prise_en_charge),
                    'current_remboursement_amount' => floatval($reservation->credit_restant)
                ]);
            } else {
                $vente['statut'] = 'Soldé';
            } 

            $nouvelleVente = Vente::create($vente);

            //on marque vendu tout les produits reservés
            foreach (ReservationProduit::where('reservation_id', $reservation->id)->get() as $reservation_produit) {
                DB::table('reservation_produits')->where('id', $reservation_produit->id)->update([
                    'is_sold' => true,
                    'statut' => 'Vendu',
                    'vente_id' => $nouvelleVente->id,
                ]);

                $this->sortieDeStock($reservation_produit->produit_id, $reservation_produit->qte, $vente['user'] );
            }
        }
        return $this->sendApiResponse($reservation, 'Reservation mis à jour');
    }

    public function destroy($id) 
    {
        $reservation = Reservation::find($id);
        $reservation->delete();

        return $this->sendApiResponse($reservation, 'Reservation supprimé');
    }

    public function validate_invoice(Request $request, $id) 
    {
        // Obtenir la date actuelle sans l'heure (juste AAAA-MM-JJ)
        $today = Carbon::now()->format('Y-m-d');

        // Récupérer le dernier numéro de position pour les réservations créées aujourd'hui
        $lastReservation = Reservation::whereDate('created_at', $today)
            ->orderBy('position', 'desc')
            ->first();
        
        // Si une réservation existe déjà pour aujourd'hui, incrémenter le numéro de position
        $position = 1;

        if ($lastReservation) {
            $position  = $lastReservation->position + 1;
        } 

        //on copie la reservation de la facture
        $reservToCopy = Reservation::find($id);
        $new_reservation = Reservation::create([
            'position' => $position,
            'code' => $reservToCopy->code,
            'numero' => $reservToCopy->numero,
            'client' => $reservToCopy->client,
            'caisse' => $reservToCopy->caisse,
            'amount_reserved' => $reservToCopy->amount_reserved,
            'created_by' => $request->user,
            'nom_assure' => $reservToCopy->nom_assure,
            'identifiant_assure' => $reservToCopy->identifiant_assure,
            'numero_feuille_assure' => $reservToCopy->numero_feuille_assure,
            'secteur_assure' => $reservToCopy->secteur_assure,
            'montant' => $reservToCopy->montant,
            'prise_en_charge' => $reservToCopy->prise_en_charge,
            'client_id' => $reservToCopy->client_id,
            'garde_id' => $reservToCopy->garde_id,
            'caisse_id' => $reservToCopy->caisse_id,
        ]);
        //on copie les reservation_produits de la reservation
        foreach (ReservationProduit::where('reservation_id', $id)->get() as $value) {
            DB::table('reservation_produits')->insert([
                'libelle' => $value->libelle,
                'qte' => $value->qte,
                'prix_de_vente' => $value->prix_de_vente,
                'cout_total' => $value->cout_total,
                'produit' => json_encode($value->produit),
                'prise_en_charge' => $value->prise_en_charge,
                'reservation_id' => $new_reservation->id,
                'produit_id' => $value->produit_id,
                'total_ht' => $value->total_ht,
                'total_tva' => $value->total_tva,
                'total_css' => $value->total_css,
                'total_prise_en_charge' => $value->total_prise_en_charge
            ]);
        }

        //on desactive les actions future sur le devis
        $facture = Facture::find($request->factureId);
        $facture->update([
            'est_valide' => true
        ]);

        $reservation = Reservation::find($new_reservation->id);

        return $this->sendApiResponse($reservation, 'Nouvelle reservation ajouté (numero '.$reservation->position.')', 200);
    }

    private function sortieDeStock($produitId, $qte, $created_by){
        $produit = DB::table('produits')->where('id', $produitId)->first();

        DB::table('produits')->where('id', $produitId)->update([
            'qte' => intval($produit->qte) - intval($qte)
        ]);

        DB::table('mouvements')->insert([
            'produit_libelle' => $produit->libelle,
            'motif' => 'VENTE',
            'type' => 'Sortie',
            'qte' => intval($qte),
            'produit_id' => $produit->id,
            'created_by' => $created_by,
            'created_at' => now()
        ]);

        //ALERTER si la qte est critique
        Artisan::call('stock:check '.$produitId);

    }
    
}
