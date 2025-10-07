<?php

namespace App\Http\Controllers;
use App\Models\Produit;
use App\Models\Mouvement;
use Illuminate\Http\Request;
use App\Http\Resources\MouvementResource;
use Illuminate\Support\Facades\Validator;

class MouvementController extends Controller
{

    public function index() 
    {
        $mouvements = Mouvement::latest();

        if (isset($_GET['req_count'])) return $this->filterByColumn('mouvements', $mouvements)->count();

        return MouvementResource::collection($this->AsdecodefilterBy('mouvements', $mouvements));
    }

    public function store(Request $request) 
    {
        $validator = Validator::make(
           $request->all(),
           [
               'motif' => 'required',
               'type' => 'required',
               'qte' => 'required',
           ],
           $messages = [
               'motif.required' => 'Le champ motif ne peut etre vide',
               'type.required' => 'Le champ type ne peut etre vide',
               'qte.required' => 'Le champ qte ne peut etre vide',
           ]
         );

        if ($validator->fails()) return $this->sendApiErrors($validator->errors()->all());

        if ($request->type == 'Sortie') {
            $produit = Produit::find($request->produit_id);
            if (intval($request->qte) > intval($produit->qte)) {
                 $messages = [ 'La quantité en stock est inferieur à celle du mouvement' ];
                 return $this->sendApiErrors($messages);
            }
         }

        $mouvement = Mouvement::create($request->all());
        return $this->sendApiResponse($mouvement, 'Mouvement ajouté', 201);
    }

    public function show($id)
    {
        return new MouvementResource(Mouvement::find($id));
    }

    public function update(Request $request, $id) 
    {
        $validator = Validator::make(
           $request->all(),
           [
               'motif' => 'required',
               'type' => 'required',
               'qte' => 'required',
               'produit_id' => 'required',
           ],
           $messages = [
               'motif.required' => 'Le champ motif ne peut etre vide',
               'type.required' => 'Le champ type ne peut etre vide',
               'qte.required' => 'Le champ qte ne peut etre vide',
               'produt_id.required' => 'Le champ qte ne peut etre vide',
           ]
         );

        if ($validator->fails()) return $this->sendApiErrors($validator->errors()->all());

        if ($request->type == 'Sortie') {
           $produit = Produit::find($request->produit_id);
           if (intval($request->qte) > intval($produit->qte)) {
                $messages = [ 'La quantité en stock est inferieur à celle du mouvement' ];
                return $this->sendApiErrors($messages);
           }
        }

        $mouvement = Mouvement::find($id);
        $mouvement->update($request->all());
        return $this->sendApiResponse($mouvement, 'Mouvement modifié', 201);
    }

    public function destroy($id) 
    {
        $mouvement = Mouvement::find($id);
        $mouvement->delete();

        return $this->sendApiResponse($mouvement, 'Mouvement supprimé');
    }

}
