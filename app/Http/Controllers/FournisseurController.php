<?php

namespace App\Http\Controllers;
use App\Models\Fournisseur;
use App\Http\Resources\FournisseurResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FournisseurController extends Controller
{

    public function index() 
    {
        $fournisseurs = Fournisseur::latest();

        if (isset($_GET['req_count'])) return $this->filterByColumn('fournisseurs', $fournisseurs)->count();

        return FournisseurResource::collection($this->AsdecodefilterBy('fournisseurs', $fournisseurs));
    }

    public function store(Request $request) 
    {
        $validator = Validator::make(
           $request->all(),
           [
               'libelle' => 'required|unique:fournisseurs,libelle',
               //'description' => 'required',
               //'site' => 'required',
               //'siteurl' => 'required',
               //'rang' => 'required',
               //'telephone' => 'required',
               //'email' => 'required',
               //'adresse' => 'required',
               //'compte_bancaire' => 'required',
               //'ville' => 'required',
               //'pays' => 'required',
               //'created_by' => 'required',
           ],
           $messages = [
               'libelle.required' => 'Le champ libelle ne peut etre vide',
               'libelle.unique' => 'Ce fournisseur existe déjà',
               //'description.required' => 'Le champ description ne peut etre vide',
               //'site.required' => 'Le champ site ne peut etre vide',
               //'siteurl.required' => 'Le champ siteurl ne peut etre vide',
               //'rang.required' => 'Le champ rang ne peut etre vide',
               //'telephone.required' => 'Le champ telephone ne peut etre vide',
               //'email.required' => 'Le champ email ne peut etre vide',
               //'adresse.required' => 'Le champ adresse ne peut etre vide',
               //'compte_bancaire.required' => 'Le champ compte_bancaire ne peut etre vide',
               //'ville.required' => 'Le champ ville ne peut etre vide',
               //'pays.required' => 'Le champ pays ne peut etre vide',
               //'created_by.required' => 'Le champ created_by ne peut etre vide',
           ]
         );

        $fournisseurs = Fournisseur::latest();
        if ($fournisseurs
        ->where('libelle', $request->libelle)
        ->where('description', $request->description)
        ->where('site', $request->site)
        ->where('siteurl', $request->siteurl)
        ->where('rang', $request->rang)
        ->where('telephone', $request->telephone)
        ->where('email', $request->email)
        ->where('adresse', $request->adresse)
        ->where('compte_bancaire', $request->compte_bancaire)
        ->where('ville', $request->ville)
        ->where('pays', $request->pays)
        ->where('created_by', $request->created_by)
        ->first()) {
           $messages = [ 'Cet enregistrement existe déjà' ];
           return $this->sendApiErrors($messages);
        }

        if ($validator->fails()) return $this->sendApiErrors($validator->errors()->all());

        $fournisseur = Fournisseur::create($request->all());
        return $this->sendApiResponse($fournisseur, 'Fournisseur ajouté', 201);
    }

    public function show($id)
    {
        return new FournisseurResource(Fournisseur::find($id));
    }

    public function update(Request $request, $id) 
    {
        $validator = Validator::make(
           $request->all(),
           [
               'libelle' => 'required|unique:fournisseurs,libelle,'.$id,
               //'description' => 'required',
               //'site' => 'required',
               //'siteurl' => 'required',
               //'rang' => 'required',
               //'telephone' => 'required',
               //'email' => 'required',
               //'adresse' => 'required',
               //'compte_bancaire' => 'required',
               //'ville' => 'required',
               //'pays' => 'required',
               //'created_by' => 'required',
           ],
           $messages = [
               'libelle.required' => 'Le champ libelle ne peut etre vide',
               'libelle.unique' => 'Ce fournisseur existe déjà',
               //'description.required' => 'Le champ description ne peut etre vide',
               //'site.required' => 'Le champ site ne peut etre vide',
               //'siteurl.required' => 'Le champ siteurl ne peut etre vide',
               //'rang.required' => 'Le champ rang ne peut etre vide',
               //'telephone.required' => 'Le champ telephone ne peut etre vide',
               //'email.required' => 'Le champ email ne peut etre vide',
               //'adresse.required' => 'Le champ adresse ne peut etre vide',
               //'compte_bancaire.required' => 'Le champ compte_bancaire ne peut etre vide',
               //'ville.required' => 'Le champ ville ne peut etre vide',
               //'pays.required' => 'Le champ pays ne peut etre vide',
               //'created_by.required' => 'Le champ created_by ne peut etre vide',
           ]
         );

        $fournisseurs = Fournisseur::latest();
        if ($fournisseurs
        ->where('libelle', $request->libelle)
        ->where('description', $request->description)
        ->where('site', $request->site)
        ->where('siteurl', $request->siteurl)
        ->where('rang', $request->rang)
        ->where('telephone', $request->telephone)
        ->where('email', $request->email)
        ->where('adresse', $request->adresse)
        ->where('compte_bancaire', $request->compte_bancaire)
        ->where('ville', $request->ville)
        ->where('pays', $request->pays)
        ->where('created_by', $request->created_by)
        ->where('id','!=', $id)->first()) {
           $messages = [ 'Cet enregistrement existe déjà' ];
           return $this->sendApiErrors($messages);
        }

        if ($validator->fails()) return $this->sendApiErrors($validator->errors()->all());

        $fournisseur = Fournisseur::find($id);
        $fournisseur->update($request->all());
        return $this->sendApiResponse($fournisseur, 'Fournisseur modifié', 201);
    }

    public function destroy($id) 
    {
        $fournisseur = Fournisseur::find($id);
        $fournisseur->delete();

        return $this->sendApiResponse($fournisseur, 'Fournisseur supprimé');
    }

    public function destroy_group(Request $request)
    {
        $key = 0;
        $nb_supprimes = 0;
        $messages= [];
        foreach ($request->selected_lines as $selected) {
            $fournisseur = Fournisseur::find($selected);
            if (isset($fournisseur)) {
                if ($fournisseur->est_valide == 1) {
                    $messages[$key] = [
                        'severity' => 'warn',
                        'value' => 'Impossible de supprimer ID0'.$selected
                    ];
                    $key++;
                }
                else {
                    $fournisseur->delete();
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
