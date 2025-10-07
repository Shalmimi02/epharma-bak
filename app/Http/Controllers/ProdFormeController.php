<?php

namespace App\Http\Controllers;
use App\Models\ProdForme;
use App\Http\Resources\ProdFormeResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProdFormeController extends Controller
{

    public function index() 
    {
        $prod_formes = ProdForme::latest();

        if (isset($_GET['req_count'])) return $this->filterByColumn('prod_formes', $prod_formes)->count();

        return ProdFormeResource::collection($this->AsdecodefilterBy('prod_formes', $prod_formes));
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

        $prod_formes = ProdForme::latest();
        if ($prod_formes
        ->where('libelle', $request->libelle)
        ->first()) {
           $messages = [ 'Cet enregistrement existe déjà' ];
           return $this->sendApiErrors($messages);
        }

        if ($validator->fails()) return $this->sendApiErrors($validator->errors()->all());

        $prod_forme = ProdForme::create($request->all());
        return $this->sendApiResponse($prod_forme, 'Prod_Forme ajouté', 201);
    }

    public function show($id)
    {
        return new ProdFormeResource(ProdForme::find($id));
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

        $prod_formes = ProdForme::latest();
        if ($prod_formes
        ->where('libelle', $request->libelle)
        ->where('id','!=', $id)->first()) {
           $messages = [ 'Cet enregistrement existe déjà' ];
           return $this->sendApiErrors($messages);
        }

        if ($validator->fails()) return $this->sendApiErrors($validator->errors()->all());

        $prod_forme = ProdForme::find($id);
        $prod_forme->update($request->all());
        return $this->sendApiResponse($prod_forme, 'Prod_Forme modifié', 201);
    }

    public function destroy($id) 
    {
        $prod_forme = ProdForme::find($id);
        $prod_forme->delete();

        return $this->sendApiResponse($prod_forme, 'Prod_Forme supprimé');
    }

    public function destroy_group(Request $request)
    {
        $key = 0;
        $nb_supprimes = 0;
        $messages= [];
        foreach ($request->selected_lines as $selected) {
            $prod_forme = ProdForme::find($selected);
            if (isset($prod_forme)) {
                if ($prod_forme->est_valide == 1) {
                    $messages[$key] = [
                        'severity' => 'warn',
                        'value' => 'Impossible de supprimer ID0'.$selected
                    ];
                    $key++;
                }
                else {
                    $prod_forme->delete();
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
