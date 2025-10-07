<?php

namespace App\Http\Controllers;
use App\Models\Facture;
use App\Http\Resources\FactureResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FactureController extends Controller
{

    public function index() 
    {
        $factures = Facture::latest();

        if (isset($_GET['req_count'])) return $this->filterByColumn('factures', $factures)->count();

        return FactureResource::collection($this->AsdecodefilterBy('factures', $factures));
    }

    public function store(Request $request) 
    {
        $validator = Validator::make(
           $request->all(),
           [
               //'numero' => 'required',
               //'date' => 'required',
               //'client' => 'required',
               //'utilisateur' => 'required',
               'reservation_id' => 'required',
               //'type' => 'required',
           ],
           $messages = [
               //'numero.required' => 'Le champ numero ne peut etre vide',
               //'date.required' => 'Le champ date ne peut etre vide',
               //'client.required' => 'Le champ client ne peut etre vide',
               //'utilisateur.required' => 'Le champ utilisateur ne peut etre vide',
               'reservation_id.required' => 'Le champ reservation_id ne peut etre vide',
               //'type.required' => 'Le champ type ne peut etre vide',
           ]
         );

        $factures = Facture::latest();
        if ($factures
        ->where('numero', $request->numero)
        ->where('client', $request->client)
        ->where('created_by', $request->created_by)
        ->where('reservation_id', $request->reservation_id)
        ->where('type', $request->type)
        ->first()) {
           $messages = [ 'Cet enregistrement existe déjà' ];
           return $this->sendApiErrors($messages);
        }

        if ($validator->fails()) return $this->sendApiErrors($validator->errors()->all());

        $facture = Facture::create($request->all());
        return $this->sendApiResponse($facture, 'Facture ajouté', 201);
    }

    public function show($id)
    {
        return new FactureResource(Facture::find($id));
    }

    public function update(Request $request, $id) 
    {
        $validator = Validator::make(
           $request->all(),
           [
               //'numero' => 'required',
               //'date' => 'required',
               //'client' => 'required',
               //'utilisateur' => 'required',
               //'action' => 'required',
               //'type' => 'required',
           ],
           $messages = [
               //'numero.required' => 'Le champ numero ne peut etre vide',
               //'date.required' => 'Le champ date ne peut etre vide',
               //'client.required' => 'Le champ client ne peut etre vide',
               //'utilisateur.required' => 'Le champ utilisateur ne peut etre vide',
               //'action.required' => 'Le champ action ne peut etre vide',
               //'type.required' => 'Le champ type ne peut etre vide',
           ]
         );

        $factures = Facture::latest();
        if ($factures
        ->where('numero', $request->numero)
        ->where('client', $request->client)
        ->where('created_by', $request->created_by)
        ->where('reservation_id', $request->reservation_id)
        ->where('type', $request->type)
        ->where('id','!=', $id)->first()) {
           $messages = [ 'Cet enregistrement existe déjà' ];
           return $this->sendApiErrors($messages);
        }

        if ($validator->fails()) return $this->sendApiErrors($validator->errors()->all());

        $facture = Facture::find($id);
        $facture->update($request->all());
        return $this->sendApiResponse($facture, 'Facture modifié', 201);
    }

    public function destroy($id) 
    {
        $facture = Facture::find($id);
        $facture->delete();

        return $this->sendApiResponse($facture, 'Facture supprimé');
    }

    public function destroy_group(Request $request)
    {
        $key = 0;
        $nb_supprimes = 0;
        $messages= [];
        foreach ($request->selected_lines as $selected) {
            $facture = Facture::find($selected);
            if (isset($facture)) {
                if ($facture->est_valide == 1) {
                    $messages[$key] = [
                        'severity' => 'warn',
                        'value' => 'Impossible de supprimer ID0'.$selected
                    ];
                    $key++;
                }
                else {
                    $facture->delete();
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
