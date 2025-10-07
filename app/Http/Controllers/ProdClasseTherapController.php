<?php

namespace App\Http\Controllers;
use App\Models\ProdClasseTherap;
use App\Http\Resources\ProdClasseTherapResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProdClasseTherapController extends Controller
{

    public function index() 
    {
        $prod_classe_theraps = ProdClasseTherap::latest();

        if (isset($_GET['req_count'])) return $this->filterByColumn('prod_classe_theraps', $prod_classe_theraps)->count();

        return ProdClasseTherapResource::collection($this->AsdecodefilterBy('prod_classe_theraps', $prod_classe_theraps));
    }

    public function store(Request $request) 
    {
        $validator = Validator::make(
           $request->all(),
           [
               //'libelle' => 'required',
           ],
           $messages = [
               //'libelle.required' => 'Le champ libelle ne peut etre vide',
           ]
         );

        $prod_classe_theraps = ProdClasseTherap::latest();
        if ($prod_classe_theraps
        ->where('libelle', $request->libelle)
        ->first()) {
           $messages = [ 'Cet enregistrement existe déjà' ];
           return $this->sendApiErrors($messages);
        }

        if ($validator->fails()) return $this->sendApiErrors($validator->errors()->all());

        $prod_classe_therap = ProdClasseTherap::create($request->all());
        return $this->sendApiResponse($prod_classe_therap, 'Prod_Classe_Therap ajouté', 201);
    }

    public function show($id)
    {
        return new ProdClasseTherapResource(ProdClasseTherap::find($id));
    }

    public function update(Request $request, $id) 
    {
        $validator = Validator::make(
           $request->all(),
           [
               //'libelle' => 'required',
           ],
           $messages = [
               //'libelle.required' => 'Le champ libelle ne peut etre vide',
           ]
         );

        $prod_classe_theraps = ProdClasseTherap::latest();
        if ($prod_classe_theraps
        ->where('libelle', $request->libelle)
        ->where('id','!=', $id)->first()) {
           $messages = [ 'Cet enregistrement existe déjà' ];
           return $this->sendApiErrors($messages);
        }

        if ($validator->fails()) return $this->sendApiErrors($validator->errors()->all());

        $prod_classe_therap = ProdClasseTherap::find($id);
        $prod_classe_therap->update($request->all());
        return $this->sendApiResponse($prod_classe_therap, 'Prod_Classe_Therap modifié', 201);
    }

    public function destroy($id) 
    {
        $prod_classe_therap = ProdClasseTherap::find($id);
        $prod_classe_therap->delete();

        return $this->sendApiResponse($prod_classe_therap, 'Prod_Classe_Therap supprimé');
    }

    public function destroy_group(Request $request)
    {
        $key = 0;
        $nb_supprimes = 0;
        $messages= [];
        foreach ($request->selected_lines as $selected) {
            $prod_classe_therap = ProdClasseTherap::find($selected);
            if (isset($prod_classe_therap)) {
                if ($prod_classe_therap->est_valide == 1) {
                    $messages[$key] = [
                        'severity' => 'warn',
                        'value' => 'Impossible de supprimer ID0'.$selected
                    ];
                    $key++;
                }
                else {
                    $prod_classe_therap->delete();
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
