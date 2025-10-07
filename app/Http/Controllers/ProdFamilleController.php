<?php

namespace App\Http\Controllers;
use App\Models\ProdFamille;
use App\Http\Resources\ProdFamilleResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProdFamilleController extends Controller
{

    public function index() 
    {
        $prod_familles = ProdFamille::latest();

        if (isset($_GET['req_count'])) return $this->filterByColumn('prod_familles', $prod_familles)->count();

        return ProdFamilleResource::collection($this->AsdecodefilterBy('prod_familles', $prod_familles));
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

        $prod_familles = ProdFamille::latest();
        if ($prod_familles
        ->where('libelle', $request->libelle)
        ->first()) {
           $messages = [ 'Cet enregistrement existe déjà' ];
           return $this->sendApiErrors($messages);
        }

        if ($validator->fails()) return $this->sendApiErrors($validator->errors()->all());

        $prod_famille = ProdFamille::create($request->all());
        return $this->sendApiResponse($prod_famille, 'Prod_Famille ajouté', 201);
    }

    public function show($id)
    {
        return new ProdFamilleResource(ProdFamille::find($id));
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

        $prod_familles = ProdFamille::latest();
        if ($prod_familles
        ->where('libelle', $request->libelle)
        ->where('id','!=', $id)->first()) {
           $messages = [ 'Cet enregistrement existe déjà' ];
           return $this->sendApiErrors($messages);
        }

        if ($validator->fails()) return $this->sendApiErrors($validator->errors()->all());

        $prod_famille = ProdFamille::find($id);
        $prod_famille->update($request->all());
        return $this->sendApiResponse($prod_famille, 'Prod_Famille modifié', 201);
    }

    public function destroy($id) 
    {
        $prod_famille = ProdFamille::find($id);
        $prod_famille->delete();

        return $this->sendApiResponse($prod_famille, 'Prod_Famille supprimé');
    }

    public function destroy_group(Request $request)
    {
        $key = 0;
        $nb_supprimes = 0;
        $messages= [];
        foreach ($request->selected_lines as $selected) {
            $prod_famille = ProdFamille::find($selected);
            if (isset($prod_famille)) {
                if ($prod_famille->est_valide == 1) {
                    $messages[$key] = [
                        'severity' => 'warn',
                        'value' => 'Impossible de supprimer ID0'.$selected
                    ];
                    $key++;
                }
                else {
                    $prod_famille->delete();
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
