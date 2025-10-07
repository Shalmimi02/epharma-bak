<?php

namespace App\Http\Controllers;
use Carbon\Carbon;
use App\Models\Billetage;
use Illuminate\Http\Request;
use App\Models\ReservationProduit;
use App\Http\Resources\BilletageResource;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\ReservationProduitResource;

class BilletageController extends Controller
{

    public function index() 
    {
        $billetages = Billetage::latest();

        if (isset($_GET['req_count'])) return $this->filterByColumn('billetages', $billetages)->count();

        return BilletageResource::collection($this->AsdecodefilterBy('billetages', $billetages));
    }

    public function getProduits($id){
        // Trouver le billetage par ID
        $billetage = Billetage::findOrFail($id);

        // Combiner dates et heures
        $startDateTime = Carbon::parse($billetage->date_debut . ' ' . $billetage->heure_debut);
        $endDateTime = Carbon::parse($billetage->date_fin . ' ' . $billetage->heure_fin);
    
        // Requête avec période exacte
        $reservation_produits = ReservationProduit::whereBetween('created_at', [$startDateTime, $endDateTime]);

        $produits = $this->AsdecodefilterBy('$reservation_produits', $reservation_produits);

        return ReservationProduitResource::collection($produits);
    }

    public function store(Request $request) 
    {
        $validator = Validator::make(
           $request->all(),
           [
                'caisse_libelle' => 'required|string',
                'total_billetage' => 'required|numeric',
                'ecart' => 'numeric',
                'date_debut' => 'required|date',
                'date_fin' => 'required|date|after_or_equal:date_debut',
                'heure_debut' => 'required',
                'heure_fin' => 'required',
               //'ended_with' => 'required',
               //'total_vente' => 'required',
               //'statut' => 'required',
               //'cinq_franc' => 'required',
               //'dix_franc' => 'required',
               //'vingt_cinq_franc' => 'required',
               //'cinquante_franc' => 'required',
               //'cent_franc' => 'required',
               //'cinq_cent_franc' => 'required',
               //'mil_franc' => 'required',
               //'deux_mil_franc' => 'required',
               //'cinq_mil_franc' => 'required',
               //'dix_mil_franc' => 'required',
               //'description' => 'required',
               //'created_by' => 'required',
               //'commentaire_validation' => 'required',
               //'est_valide' => 'required',
           ],
           $messages = [
               'caisse_libelle.required' => 'Le champ caisse_libelle ne peut etre vide',
               'ended_with.required' => 'Le champ ended_with ne peut etre vide',
               'total_vente.required' => 'Le champ total_vente ne peut etre vide',
               'total_billetage.required' => 'Le champ total_billetage ne peut etre vide',
               'ecart.required' => 'Le champ ecart ne peut etre vide',
               'date_debut.required' => 'Le champ date_debut ne peut etre vide',
               'date_fin.required' => 'Le champ date_fin ne peut etre vide',
               'statut.required' => 'Le champ statut ne peut etre vide',
               'cinq_franc.required' => 'Le champ cinq_franc ne peut etre vide',
               'dix_franc.required' => 'Le champ dix_franc ne peut etre vide',
               'vingt_cinq_franc.required' => 'Le champ vingt_cinq_franc ne peut etre vide',
               'cinquante_franc.required' => 'Le champ cinquante_franc ne peut etre vide',
               'cent_franc.required' => 'Le champ cent_franc ne peut etre vide',
               'cinq_cent_franc.required' => 'Le champ cinq_cent_franc ne peut etre vide',
               'mil_franc.required' => 'Le champ mil_franc ne peut etre vide',
               'deux_mil_franc.required' => 'Le champ deux_mil_franc ne peut etre vide',
               'cinq_mil_franc.required' => 'Le champ cinq_mil_franc ne peut etre vide',
               'dix_mil_franc.required' => 'Le champ dix_mil_franc ne peut etre vide',
               'description.required' => 'Le champ description ne peut etre vide',
               'created_by.required' => 'Le champ created_by ne peut etre vide',
               'commentaire_validation.required' => 'Le champ commentaire_validation ne peut etre vide',
               'est_valide.required' => 'Le champ est_valide ne peut etre vide',
           ]
         );

        $billetages = Billetage::latest();
        if ($billetages
        ->where('caisse_libelle', $request->caisse_libelle)
        ->where('ended_with', $request->ended_with)
        ->where('total_vente', $request->total_vente)
        ->where('total_billetage', $request->total_billetage)
        ->where('ecart', $request->ecart)
        ->where('date_debut', $request->date_debut)
        ->where('date_fin', $request->date_fin)
        ->where('heure_debut', $request->heure_debut)
        ->where('heure_fin', $request->heure_fin)
        ->where('statut', $request->statut)
        ->where('cinq_franc', $request->cinq_franc)
        ->where('dix_franc', $request->dix_franc)
        ->where('vingt_cinq_franc', $request->vingt_cinq_franc)
        ->where('cinquante_franc', $request->cinquante_franc)
        ->where('cent_franc', $request->cent_franc)
        ->where('cinq_cent_franc', $request->cinq_cent_franc)
        ->where('mil_franc', $request->mil_franc)
        ->where('deux_mil_franc', $request->deux_mil_franc)
        ->where('cinq_mil_franc', $request->cinq_mil_franc)
        ->where('dix_mil_franc', $request->dix_mil_franc)
        ->where('description', $request->description)
        ->where('created_by', $request->created_by)
        ->where('commentaire_validation', $request->commentaire_validation)
        ->where('est_valide', $request->est_valide)
        ->first()) {
           $messages = [ 'Cet enregistrement existe déjà' ];
           return $this->sendApiErrors($messages);
        }

        // Vérification que les périodes ne se chevauchent pas pour la même caisse
        $existingBilletage = Billetage::where('caisse_libelle', $request->caisse_libelle)
            ->where(function($query) use ($request) {
                $query->whereBetween('date_debut', [$request->date_debut, $request->date_fin])
                      ->orWhereBetween('date_fin', [$request->date_debut, $request->date_fin]);
            })
            ->where(function($query) use ($request) {
                $query->whereBetween('heure_debut', [$request->heure_debut, $request->heure_fin])
                      ->orWhereBetween('heure_fin', [$request->heure_debut, $request->heure_fin]);
            })
            ->exists();

        if ($existingBilletage) {
            $messages = [ 'Un billetage pour cette caisse existe déjà dans cette période.' ];
            return $this->sendApiErrors($messages);
        }

        if ($validator->fails()) return $this->sendApiErrors($validator->errors()->all());

        $billetage = Billetage::create($request->all());
        return $this->sendApiResponse($billetage, 'Billetage ajouté', 201);
    }

    public function show($id)
    {
        return new BilletageResource(Billetage::find($id));
    }

    public function update(Request $request, $id) 
    {
        $validator = Validator::make(
           $request->all(),
           [
               //'caisse_libelle' => 'required',
               //'ended_with' => 'required',
               //'total_vente' => 'required',
               //'total_billetage' => 'required',
               //'ecart' => 'required',
               //'date_debut' => 'required',
               //'date_fin' => 'required',
               //'statut' => 'required',
               //'cinq_franc' => 'required',
               //'dix_franc' => 'required',
               //'vingt_cinq_franc' => 'required',
               //'cinquante_franc' => 'required',
               //'cent_franc' => 'required',
               //'cinq_cent_franc' => 'required',
               //'mil_franc' => 'required',
               //'deux_mil_franc' => 'required',
               //'cinq_mil_franc' => 'required',
               //'dix_mil_franc' => 'required',
               //'description' => 'required',
               //'created_by' => 'required',
               //'commentaire_validation' => 'required',
               //'est_valide' => 'required',
           ],
           $messages = [
               //'caisse_libelle.required' => 'Le champ caisse_libelle ne peut etre vide',
               //'ended_with.required' => 'Le champ ended_with ne peut etre vide',
               //'total_vente.required' => 'Le champ total_vente ne peut etre vide',
               //'total_billetage.required' => 'Le champ total_billetage ne peut etre vide',
               //'ecart.required' => 'Le champ ecart ne peut etre vide',
               //'date_debut.required' => 'Le champ date_debut ne peut etre vide',
               //'date_fin.required' => 'Le champ date_fin ne peut etre vide',
               //'statut.required' => 'Le champ statut ne peut etre vide',
               //'cinq_franc.required' => 'Le champ cinq_franc ne peut etre vide',
               //'dix_franc.required' => 'Le champ dix_franc ne peut etre vide',
               //'vingt_cinq_franc.required' => 'Le champ vingt_cinq_franc ne peut etre vide',
               //'cinquante_franc.required' => 'Le champ cinquante_franc ne peut etre vide',
               //'cent_franc.required' => 'Le champ cent_franc ne peut etre vide',
               //'cinq_cent_franc.required' => 'Le champ cinq_cent_franc ne peut etre vide',
               //'mil_franc.required' => 'Le champ mil_franc ne peut etre vide',
               //'deux_mil_franc.required' => 'Le champ deux_mil_franc ne peut etre vide',
               //'cinq_mil_franc.required' => 'Le champ cinq_mil_franc ne peut etre vide',
               //'dix_mil_franc.required' => 'Le champ dix_mil_franc ne peut etre vide',
               //'description.required' => 'Le champ description ne peut etre vide',
               //'created_by.required' => 'Le champ created_by ne peut etre vide',
               //'commentaire_validation.required' => 'Le champ commentaire_validation ne peut etre vide',
               //'est_valide.required' => 'Le champ est_valide ne peut etre vide',
           ]
         );

        $billetages = Billetage::latest();
        if ($billetages
        ->where('caisse_libelle', $request->caisse_libelle)
        ->where('ended_with', $request->ended_with)
        ->where('total_vente', $request->total_vente)
        ->where('total_billetage', $request->total_billetage)
        ->where('ecart', $request->ecart)
        ->where('date_debut', $request->date_debut)
        ->where('date_fin', $request->date_fin)
        ->where('heure_debut', $request->heure_debut)
        ->where('heure_fin', $request->heure_fin)
        ->where('statut', $request->statut)
        ->where('cinq_franc', $request->cinq_franc)
        ->where('dix_franc', $request->dix_franc)
        ->where('vingt_cinq_franc', $request->vingt_cinq_franc)
        ->where('cinquante_franc', $request->cinquante_franc)
        ->where('cent_franc', $request->cent_franc)
        ->where('cinq_cent_franc', $request->cinq_cent_franc)
        ->where('mil_franc', $request->mil_franc)
        ->where('deux_mil_franc', $request->deux_mil_franc)
        ->where('cinq_mil_franc', $request->cinq_mil_franc)
        ->where('dix_mil_franc', $request->dix_mil_franc)
        ->where('description', $request->description)
        ->where('created_by', $request->created_by)
        ->where('commentaire_validation', $request->commentaire_validation)
        ->where('est_valide', $request->est_valide)
        ->where('id','!=', $id)->first()) {
           $messages = [ 'Cet enregistrement existe déjà' ];
           return $this->sendApiErrors($messages);
        }

        if ($validator->fails()) return $this->sendApiErrors($validator->errors()->all());

        $billetage = Billetage::find($id);
        $billetage->update($request->all());
        return $this->sendApiResponse($billetage, 'Billetage modifié', 201);
    }

    public function destroy($id) 
    {
        $billetage = Billetage::find($id);
        $billetage->delete();

        return $this->sendApiResponse($billetage, 'Billetage supprimé');
    }

    public function destroy_group(Request $request)
    {
        $key = 0;
        $nb_supprimes = 0;
        $messages= [];
        foreach ($request->selected_lines as $selected) {
            $billetage = Billetage::find($selected);
            if (isset($billetage)) {
                if ($billetage->est_valide == 1) {
                    $messages[$key] = [
                        'severity' => 'warn',
                        'value' => 'Impossible de supprimer ID0'.$selected
                    ];
                    $key++;
                }
                else {
                    $billetage->delete();
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
