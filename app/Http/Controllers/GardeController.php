<?php

namespace App\Http\Controllers;
use Carbon\Carbon;
use App\Models\Garde;
use Illuminate\Http\Request;
use App\Models\ReservationProduit;
use App\Http\Resources\GardeResource;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\ReservationProduitResource;

class GardeController extends Controller
{

    public function index() 
    {
        $gardes = Garde::latest();

        if (isset($_GET['req_count'])) return $this->filterByColumn('gardes', $gardes)->count();

        return GardeResource::collection($this->AsdecodefilterBy('gardes', $gardes));
    }

    public function verifierGardeActuelle()
    {
        // Obtenir la date et l'heure actuelles
        $now = Carbon::now();

        // Rechercher une garde qui correspond à la période actuelle
        $gardeActuelle = Garde::where('date_debut', '<=', $now->toDateString())
            ->where('date_fin', '>=', $now->toDateString())
            ->where('heure_debut', '<=', $now->toTimeString())
            ->where('heure_fin', '>=', $now->toTimeString())
            ->where('is_active', 1)
            ->first();

        if ($gardeActuelle) {
            return new GardeResource($gardeActuelle);
        } else return null;
    }

    public function getProduits($id){
        // Trouver le garde par ID
        $garde = Garde::findOrFail($id);

        // Combiner dates et heures
        $startDateTime = Carbon::parse($garde->date_debut . ' ' . $garde->heure_debut);
        $endDateTime = Carbon::parse($garde->date_fin . ' ' . $garde->heure_fin);
    
        // Requête avec période exacte
        $reservation_produits = ReservationProduit::whereBetween('created_at', [$startDateTime, $endDateTime]);

        if (isset($_GET['page']) && isset($_GET['rows'])){
            return ReservationProduitResource::collection($reservation_produits->paginate($_GET['rows']));
        }

        return ReservationProduitResource::collection($reservation_produits->get());
    }

    public function store(Request $request) 
    {
        $validator = Validator::make(
           $request->all(),
           [
                'date_debut' => 'required|date',
                'date_fin' => 'required|date',
                'heure_debut' => 'required|date_format:H:i',
                'heure_fin' => 'required|date_format:H:i',
                'montant_taxe' => 'required|numeric',
           ],
           $messages = [
               'date_debut.required' => 'Le champ date_debut ne peut etre vide',
               'date_fin.required' => 'Le champ date_fin ne peut etre vide',
               'heure_debut.required' => 'Le champ heure_debut ne peut etre vide',
               'heure_fin.required' => 'Le champ heure_fin ne peut etre vide',
               'montant_taxe.required' => 'Le champ montant_taxe ne peut etre vide',
           ]
         );

        $gardes = Garde::latest();
        if ($gardes
        ->where('is_active', true)
        ->where('date_debut', $request->date_debut)
        ->where('date_fin', $request->date_fin)
        ->where('heure_debut', $request->heure_debut)
        ->where('heure_fin', $request->heure_fin)
        ->where('montant_taxe', $request->montant_taxe)
        ->first()) {
           $messages = [ 'Cet enregistrement existe déjà' ];
           return $this->sendApiErrors($messages);
        }

        // Vérification d'une garde existante dans la même période
        $gardeExistante = Garde::where(function($query) use ($request) {
            $query->where('is_active', true)
                    ->where('date_debut', '<=', $request->date_fin)
                    ->where('date_fin', '>=', $request->date_debut)
                    ->where(function($subQuery) use ($request) {
                      $subQuery->where(function($timeQuery) use ($request) {
                          $timeQuery->where('heure_debut', '<=', $request->heure_fin)
                                    ->where('heure_fin', '>=', $request->heure_debut);
                      });
                  });
        })->exists();

        if ($gardeExistante) {
            $messages = [ 'Une garde existe déjà dans la même période.' ];
            return $this->sendApiErrors($messages);
        }

        if ($validator->fails()) return $this->sendApiErrors($validator->errors()->all());

        $garde = Garde::create($request->all());
        return $this->sendApiResponse($garde, 'Garde ajouté', 201);
    }

    public function show($id)
    {
        return new GardeResource(Garde::find($id));
    }

    public function update(Request $request, $id) 
    {
        $validator = Validator::make(
           $request->all(),
           [
                'date_debut' => 'required|date',
                'date_fin' => 'required|date',
                'heure_debut' => 'required|date_format:H:i',
                'heure_fin' => 'required|date_format:H:i',
                'montant_taxe' => 'required|numeric',
            ],
            $messages = [
                'date_debut.required' => 'Le champ date_debut ne peut etre vide',
                'date_fin.required' => 'Le champ date_fin ne peut etre vide',
                'heure_debut.required' => 'Le champ heure_debut ne peut etre vide',
                'heure_fin.required' => 'Le champ heure_fin ne peut etre vide',
                'montant_taxe.required' => 'Le champ montant_taxe ne peut etre vide',
            ]
         );

        $gardes = Garde::latest();
        if ($gardes
        ->where('numero', $request->numero)
        ->where('date_debut', $request->date_debut)
        ->where('date_fin', $request->date_fin)
        ->where('heure_debut', $request->heure_debut)
        ->where('heure_fin', $request->heure_fin)
        ->where('montant_taxe', $request->montant_taxe)
        ->where('total_taxe', $request->total_taxe)
        ->where('id','!=', $id)->first()) {
           $messages = [ 'Cet enregistrement existe déjà' ];
           return $this->sendApiErrors($messages);
        }

        if ($validator->fails()) return $this->sendApiErrors($validator->errors()->all());

        $garde = Garde::find($id);
        $garde->update($request->all());
        return $this->sendApiResponse($garde, 'Garde modifié');
    }

    public function desactiver($id) 
    {
        $garde = Garde::find($id);
        $garde->update([
            'is_active' => false,
            'statut' => 'Désactivé à '. date('d/m/Y H:i', strtotime(now()))
        ]);
        return $this->sendApiResponse($garde, 'Garde desactivé');
    }

    public function destroy($id) 
    {
        $garde = Garde::find($id);
        $garde->delete();

        return $this->sendApiResponse($garde, 'Garde supprimé');
    }

}
