<?php

namespace App\Http\Controllers;
use App\Models\Produit;
use Illuminate\Http\Request;
use App\Models\InventaireProduit;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\InventaireProduitResource;

class InventaireProduitController extends Controller
{

    public function index() 
    {
        $inventaire_produit = InventaireProduit::orderBy('produit_libelle', 'ASC');
        
        if (isset($_GET['show_diff_only']) && ($_GET['show_diff_only'] == 1 || ($_GET['show_diff_only'] == 'true')) ) {
            $inventaire_produit = $inventaire_produit->whereNotNull('ecart')->where('ecart', '!=', '0');
        }

        if (isset($_GET['req_count'])) return $this->filterByColumn('inventaire_produit', $inventaire_produit)->count();

        return InventaireProduitResource::collection($this->AsdecodefilterBy('inventaire_produit', $inventaire_produit));
    }

    public function store(Request $request) 
    {
        $validator = Validator::make(
           $request->all(),
           [
               //'inventaire_id' => 'required',
               //'produit_id' => 'required',
               //'qte' => 'required',
               //'qte_reelle' => 'required',
               //'ecart' => 'required',
           ],
           $messages = [
               //'inventaire_id.required' => 'Le champ inventaire_id ne peut etre vide',
               //'produit_id.required' => 'Le champ produit_id ne peut etre vide',
               //'qte.required' => 'Le champ qte ne peut etre vide',
               //'qte_reelle.required' => 'Le champ qte_reelle ne peut etre vide',
               //'ecart.required' => 'Le champ ecart ne peut etre vide',
           ]
         );

        $inventaire_produit = InventaireProduit::latest();
        if ($inventaire_produit
        ->where('inventaire_id', $request->inventaire_id)
        ->where('produit_id', $request->produit_id)
        ->where('qte', $request->qte)
        ->where('qte_reelle', $request->qte_reelle)
        ->where('ecart', $request->ecart)
        ->first()) {
           $messages = [ 'Cet enregistrement existe déjà' ];
           return $this->sendApiErrors($messages);
        }

        if ($validator->fails()) return $this->sendApiErrors($validator->errors()->all());

        $inventaire_produit = InventaireProduit::create($request->all());
        return $this->sendApiResponse($inventaire_produit, 'Inventaire_Produit ajouté', 201);
    }

    public function show($id)
    {
        return new InventaireProduitResource(InventaireProduit::find($id));
    }

    public function update(Request $request, $id) 
    {
        $validator = Validator::make(
           $request->all(),
           [
               //'inventaire_id' => 'required',
               //'produit_id' => 'required',
               //'qte' => 'required',
               //'qte_reelle' => 'required',
               //'ecart' => 'required',
           ],
           $messages = [
               //'inventaire_id.required' => 'Le champ inventaire_id ne peut etre vide',
               //'produit_id.required' => 'Le champ produit_id ne peut etre vide',
               //'qte.required' => 'Le champ qte ne peut etre vide',
               //'qte_reelle.required' => 'Le champ qte_reelle ne peut etre vide',
               //'ecart.required' => 'Le champ ecart ne peut etre vide',
           ]
         );

        $inventaire_produit = InventaireProduit::latest();
        if ($inventaire_produit
        ->where('inventaire_id', $request->inventaire_id)
        ->where('produit_id', $request->produit_id)
        ->where('qte', $request->qte)
        ->where('qte_reelle', $request->qte_reelle)
        ->where('ecart', $request->ecart)
        ->where('id','!=', $id)->first()) {
           $messages = [ 'Cet enregistrement existe déjà' ];
           return $this->sendApiErrors($messages);
        }

        if ($validator->fails()) return $this->sendApiErrors($validator->errors()->all());

        if ($request->qte_reelle == '' || intval($request->qte_reelle ) < 0) {
            $messages = [ 'Les quantité négatives ne sont pas autorisés' ];
            return $this->sendApiErrors($messages);
        }

        $inventaire_produit = InventaireProduit::find($id);
        $produit = Produit::find($inventaire_produit->produit_id);
        $ecart = intval($request->qte_reelle) - intval($inventaire_produit->qte);
        $qte_initiale= intval($produit->qte);

        
        $inventaire_produit->update([
            'qte_reelle' => $request->qte_reelle,
            'ecart' => $ecart,
            'qte_finale' => intval($ecart) + $qte_initiale,
            'qte_initiale' => $qte_initiale
        ]);
        return $this->sendApiResponse($inventaire_produit, 'Quantité à jour');
    }

    public function destroy($id) 
    {
        $inventaire_produit = InventaireProduit::find($id);
        $inventaire_produit->delete();

        return $this->sendApiResponse($inventaire_produit, 'Inventaire_Produit supprimé');
    }

    public function destroy_group(Request $request)
    {
        $key = 0;
        $nb_supprimes = 0;
        $messages= [];
        foreach ($request->selected_lines as $selected) {
            $inventaire_produit = InventaireProduit::find($selected);
            if (isset($inventaire_produit)) {
                if ($inventaire_produit->est_valide == 1) {
                    $messages[$key] = [
                        'severity' => 'warn',
                        'value' => 'Impossible de supprimer ID0'.$selected
                    ];
                    $key++;
                }
                else {
                    $inventaire_produit->delete();
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
