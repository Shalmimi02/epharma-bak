<?php

namespace App\Http\Controllers;
use App\Models\ProdNature;
use App\Http\Resources\ProdNatureResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProdNatureController extends Controller
{

    public function index() 
    {
        $prod_natures = ProdNature::latest();

        if (isset($_GET['req_count'])) return $this->filterByColumn('prod_natures', $prod_natures)->count();

        return ProdNatureResource::collection($this->AsdecodefilterBy('prod_natures', $prod_natures));
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

        $prod_natures = ProdNature::latest();
        if ($prod_natures
        ->where('libelle', $request->libelle)
        ->first()) {
           $messages = [ 'Cet enregistrement existe déjà' ];
           return $this->sendApiErrors($messages);
        }

        if ($validator->fails()) return $this->sendApiErrors($validator->errors()->all());

        $prod_nature = ProdNature::create($request->all());
        return $this->sendApiResponse($prod_nature, 'Prod_Nature ajouté', 201);
    }

    public function show($id)
    {
        return new ProdNatureResource(ProdNature::find($id));
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

        $prod_natures = ProdNature::latest();
        if ($prod_natures
        ->where('libelle', $request->libelle)
        ->where('id','!=', $id)->first()) {
           $messages = [ 'Cet enregistrement existe déjà' ];
           return $this->sendApiErrors($messages);
        }

        if ($validator->fails()) return $this->sendApiErrors($validator->errors()->all());

        $prod_nature = ProdNature::find($id);
        $prod_nature->update($request->all());
        return $this->sendApiResponse($prod_nature, 'Prod_Nature modifié', 201);
    }

    public function destroy($id) 
    {
        $prod_nature = ProdNature::find($id);
        $prod_nature->delete();

        return $this->sendApiResponse($prod_nature, 'Prod_Nature supprimé');
    }

    public function destroy_group(Request $request)
    {
        $key = 0;
        $nb_supprimes = 0;
        $messages= [];
        foreach ($request->selected_lines as $selected) {
            $prod_nature = ProdNature::find($selected);
            if (isset($prod_nature)) {
                if ($prod_nature->est_valide == 1) {
                    $messages[$key] = [
                        'severity' => 'warn',
                        'value' => 'Impossible de supprimer ID0'.$selected
                    ];
                    $key++;
                }
                else {
                    $prod_nature->delete();
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
