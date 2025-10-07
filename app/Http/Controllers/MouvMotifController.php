<?php

namespace App\Http\Controllers;
use App\Models\MouvMotif;
use App\Http\Resources\MouvMotifResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MouvMotifController extends Controller
{

    public function index() 
    {
        $mouv_motifs = MouvMotif::latest();

        if (isset($_GET['req_count'])) return $this->filterByColumn('mouv_motifs', $mouv_motifs)->count();

        return MouvMotifResource::collection($this->AsdecodefilterBy('mouv_motifs', $mouv_motifs));
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

        $mouv_motifs = MouvMotif::latest();
        if ($mouv_motifs
        ->where('libelle', $request->libelle)
        ->first()) {
           $messages = [ 'Cet enregistrement existe déjà' ];
           return $this->sendApiErrors($messages);
        }

        if ($validator->fails()) return $this->sendApiErrors($validator->errors()->all());

        $mouv_motif = MouvMotif::create($request->all());
        return $this->sendApiResponse($mouv_motif, 'Mouv_Motif ajouté', 201);
    }

    public function show($id)
    {
        return new MouvMotifResource(MouvMotif::find($id));
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

        $mouv_motifs = MouvMotif::latest();
        if ($mouv_motifs
        ->where('libelle', $request->libelle)
        ->where('id','!=', $id)->first()) {
           $messages = [ 'Cet enregistrement existe déjà' ];
           return $this->sendApiErrors($messages);
        }

        if ($validator->fails()) return $this->sendApiErrors($validator->errors()->all());

        $mouv_motif = MouvMotif::find($id);
        $mouv_motif->update($request->all());
        return $this->sendApiResponse($mouv_motif, 'Mouv_Motif modifié', 201);
    }

    public function destroy($id) 
    {
        $mouv_motif = MouvMotif::find($id);
        $mouv_motif->delete();

        return $this->sendApiResponse($mouv_motif, 'Mouv_Motif supprimé');
    }

    public function destroy_group(Request $request)
    {
        $key = 0;
        $nb_supprimes = 0;
        $messages= [];
        foreach ($request->selected_lines as $selected) {
            $mouv_motif = MouvMotif::find($selected);
            if (isset($mouv_motif)) {
                if ($mouv_motif->est_valide == 1) {
                    $messages[$key] = [
                        'severity' => 'warn',
                        'value' => 'Impossible de supprimer ID0'.$selected
                    ];
                    $key++;
                }
                else {
                    $mouv_motif->delete();
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
