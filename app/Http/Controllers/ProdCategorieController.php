<?php

namespace App\Http\Controllers;
use App\Models\ProdCategorie;
use App\Http\Resources\ProdCategorieResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProdCategorieController extends Controller
{

    public function index() 
    {
        $prod_categories = ProdCategorie::latest();

        if (isset($_GET['req_count'])) return $this->filterByColumn('prod_categories', $prod_categories)->count();

        return ProdCategorieResource::collection($this->AsdecodefilterBy('prod_categories', $prod_categories));
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

        $prod_categories = ProdCategorie::latest();
        if ($prod_categories
        ->where('libelle', $request->libelle)
        ->first()) {
           $messages = [ 'Cet enregistrement existe déjà' ];
           return $this->sendApiErrors($messages);
        }

        if ($validator->fails()) return $this->sendApiErrors($validator->errors()->all());

        $prod_categorie = ProdCategorie::create($request->all());
        return $this->sendApiResponse($prod_categorie, 'Prod_Categorie ajouté', 201);
    }

    public function show($id)
    {
        return new ProdCategorieResource(ProdCategorie::find($id));
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

        $prod_categories = ProdCategorie::latest();
        if ($prod_categories
        ->where('libelle', $request->libelle)
        ->where('id','!=', $id)->first()) {
           $messages = [ 'Cet enregistrement existe déjà' ];
           return $this->sendApiErrors($messages);
        }

        if ($validator->fails()) return $this->sendApiErrors($validator->errors()->all());

        $prod_categorie = ProdCategorie::find($id);
        $prod_categorie->update($request->all());
        return $this->sendApiResponse($prod_categorie, 'Prod_Categorie modifié', 201);
    }

    public function destroy($id) 
    {
        $prod_categorie = ProdCategorie::find($id);
        $prod_categorie->delete();

        return $this->sendApiResponse($prod_categorie, 'Prod_Categorie supprimé');
    }

    public function destroy_group(Request $request)
    {
        $key = 0;
        $nb_supprimes = 0;
        $messages= [];
        foreach ($request->selected_lines as $selected) {
            $prod_categorie = ProdCategorie::find($selected);
            if (isset($prod_categorie)) {
                if ($prod_categorie->est_valide == 1) {
                    $messages[$key] = [
                        'severity' => 'warn',
                        'value' => 'Impossible de supprimer ID0'.$selected
                    ];
                    $key++;
                }
                else {
                    $prod_categorie->delete();
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
