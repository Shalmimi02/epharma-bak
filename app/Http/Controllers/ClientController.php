<?php

namespace App\Http\Controllers;
use App\Models\Client;
use App\Models\Reservation;
use Illuminate\Http\Request;
use App\Models\ReservationProduit;
use App\Http\Resources\ClientResource;
use Illuminate\Support\Facades\Validator;

class ClientController extends Controller
{
    public function getClientBase() 
    {
        $clients = Client::where('client_id', null);

        if (isset($_GET['req_count'])) return $this->filterByColumn('clients', $clients)->count();

        return ClientResource::collection($this->AsdecodefilterBy('clients', $clients));
    }

    public function index() 
    {
        $clients = Client::latest();

        if (isset($_GET['req_count'])) return $this->filterByColumn('clients', $clients)->count();

        if (isset($_GET['column_sum'])) return $ventes->sum($_GET['column_sum']);

        return ClientResource::collection($this->AsdecodefilterBy('clients', $clients));
    }

    public function clientStatistique($id) {
        $client = Client::find($id);

        //liste des reservations du clients
        $reservations  = Reservation::where('client_id', $client->id)->get();

        $total_chiffre_affaire = 0;
        $total_nb_produit = 0;
        $total_nb_facture = count($reservations);
        $total_panier_moyen = 0;

        $ids = [];

        //calculs sur les reservations du client
        foreach ($reservations as $reservation) {
            $ca = floatval($reservation->montant ?? 0);
            $total_chiffre_affaire += $ca;

            // Ajouter l'ID à la liste des IDs
            $ids[] = $reservation->id;
        }

        //liste des produits vendus
        $reservations_produits = ReservationProduit::whereIn('reservation_id', $ids)->get();
        foreach ($reservations_produits as $produit) {
            $qte = floatval($produit->qte ?? 0);
            $total_nb_produit += $qte;
        }

        if ($total_nb_facture > 0) {
            $total_panier_moyen = ($total_chiffre_affaire / $total_nb_facture);
        }
        

        return [
            'total_chiffre_affaire' => $total_chiffre_affaire,
            'total_nb_produit' => $total_nb_produit,
            'total_nb_facture' => $total_nb_facture,
            'total_panier_moyen' => $total_panier_moyen,
        ];

    }

    public function calculerCoutTotal()
    {
        // Récupérer clients
        $cients = Client::all();

        // Variables pour stocker les coûts totaux
        $coutTotalDette = 0;
        $coutTotalCredit = 0;

        // Parcourir chaque client pour effectuer les calculs
        foreach ($cients as $client) {
            // Convertir les prix d'achat et de vente en flottants, si non null
            $dette = floatval($client->current_dette ?? 0);
            $credit = floatval($client->current_remboursement_amount ?? 0);

            // Calculer le coût total pour chaque client
            $coutTotalDette += $dette;
            $coutTotalCredit += $credit;
        }

        // Retourner les résultats
        return [
            'total_dette' => $coutTotalDette,
            'total_credit' => $coutTotalCredit,
        ];
    }

    public function store(Request $request) 
    {
        $validator = Validator::make(
           $request->all(),
           [
               'nom' => 'nullable|unique:clients,nom',
            //    'libelle' => 'nullable|unique:clients,libelle',
               //'total_amount' => 'required',
               //'is_enabled' => 'required',
               //'remise_percent' => 'required',
               //'created_by' => 'required',
               
           ],
           $messages = [
            //    'libelle.unique' => 'Ce libelle existe deja',
               'nom.unique' => 'Ce nom existe deja',
               //'total_amount.required' => 'Le champ total_amount ne peut etre vide',
               //'is_enabled.required' => 'Le champ is_enabled ne peut etre vide',
               //'remise_percent.required' => 'Le champ remise_percent ne peut etre vide',
               //'created_by.required' => 'Le champ created_by ne peut etre vide',
           ]
         );

        $clients = Client::latest();
        if ($clients
        ->where('libelle', $request->libelle)
        ->where('total_amount', $request->total_amount)
        ->where('is_enabled', $request->is_enabled)
        ->where('remise_percent', $request->remise_percent)
        ->where('created_by', $request->created_by)
        ->where( 'nom',$request->nom)
        ->where( 'code',$request->code)
        ->where('email',$request->email)
        ->where('telephone',$request->telephone)
        ->where('ville',$request->ville)
        ->where('numero_cnss',$request->numero_cnss)
        ->where('numero_assurance',$request->numero_assurance)
        ->where('assurance',$request->assurance)
        ->where('plafond_dette',$request->plafond_dette)
        ->where('current_dette',$request->current_dette)

        ->where('current_remboursement_amount', $request->current_remboursement_amount)
        ->first()) {
           $messages = [ 'Cet enregistrement existe déjà' ];
           return $this->sendApiErrors($messages);
        }

        if ($validator->fails()) return $this->sendApiErrors($validator->errors()->all());

        $client = Client::create($request->all());
        return $this->sendApiResponse($client, 'Client ajouté', 201);
    }

    public function show($id)
    {
        return new ClientResource(Client::find($id));
    }

    public function update(Request $request, $id) 
    {
        $validator = Validator::make(
           $request->all(),
           [
               'nom' => 'nullable|unique:clients,nom,'.$id,
            //    'libelle' => 'nullable|unique:clients,libelle,'.$id,
               //'total_amount' => 'required',
               //'is_enabled' => 'required',
               //'remise_percent' => 'required',
               //'created_by' => 'required',
           ],
           $messages = [
            //    'libelle.unique' => 'Ce libelle existe deja',
               'nom.unique' => 'Ce nom existe deja',
               //'total_amount.required' => 'Le champ total_amount ne peut etre vide',
               //'is_enabled.required' => 'Le champ is_enabled ne peut etre vide',
               //'remise_percent.required' => 'Le champ remise_percent ne peut etre vide',
               //'created_by.required' => 'Le champ created_by ne peut etre vide',
           ]
         );

        $clients = Client::latest();
        if ($clients
        ->where('libelle', $request->libelle)
        ->where('total_amount', $request->total_amount)
        ->where('is_enabled', $request->is_enabled)
        ->where('remise_percent', $request->remise_percent)
        ->where('created_by', $request->created_by)
        ->where( 'nom',$request->nom,)
        ->where( 'code',$request->code)
        ->where('email',$request->email)
        ->where('telephone',$request->telephone)
        ->where('ville',$request->ville)
        ->where('numero_cnss',$request->numero_cnss)
        ->where('numero_assurance',$request->numero_assurance)
        ->where('assurance',$request->assurance)
        ->where('plafond_dette',$request->plafond_dette)
        ->where('current_dette',$request->ccurrent_dette)
        ->where('current_remboursement_amount', $request->current_remboursement_amount)

        ->where('id','!=', $id)->first()) {
           $messages = [ 'Cet enregistrement existe déjà' ];
           return $this->sendApiErrors($messages);
        }

        if ($validator->fails()) return $this->sendApiErrors($validator->errors()->all());

        $client = Client::find($id);
        $client->update($request->all());
        return $this->sendApiResponse($client, 'Client modifié', 201);
    }

    public function destroy($id) 
    {
        $client = Client::find($id);
        $client->delete();

        return $this->sendApiResponse($client, 'Client supprimé');
    }

    public function destroy_group(Request $request)
    {
        $key = 0;
        $nb_supprimes = 0;
        $messages= [];
        foreach ($request->selected_lines as $selected) {
            $client = Client::find($selected);
            if (isset($client)) {
                if ($client->est_valide == 1) {
                    $messages[$key] = [
                        'severity' => 'warn',
                        'value' => 'Impossible de supprimer ID0'.$selected
                    ];
                    $key++;
                }
                else {
                    $client->delete();
                    $nb_supprimes++;
                    $messages[$key] = [
                        'severity' => 'success',
                        'value' => $nb_supprimes.' lignes ont été supprimé'
                    ];
                }
            }
        }
        return $this->sendApiResponse([], $messages);
    }

}
