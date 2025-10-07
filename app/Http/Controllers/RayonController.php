<?php

namespace App\Http\Controllers;
use App\Models\Rayon;
use Illuminate\Http\Request;
use App\Models\InventaireProduit;
use App\Http\Resources\RayonResource;
use Illuminate\Support\Facades\Validator;

class RayonController extends Controller
{
    public function index() 
    {
        $rayons = Rayon::latest();

        if (isset($_GET['req_count'])) return $this->filterByColumn('rayons', $rayons)->count();

        return RayonResource::collection($this->AsdecodefilterBy('rayons', $rayons));
    }

    public function store(Request $request) 
    {
        $validator = Validator::make(
           $request->all(),
           [
               'libelle' => 'required|unique:rayons,libelle',
            //    'created_by' => 'required',
           ],
           $messages = [
               'libelle.required' => 'Le champ libelle ne peut etre vide',
            //    'created_by.required' => 'Le champ created_by ne peut etre vide',
           ]
         );

        $rayons = Rayon::latest();
        if ($rayons
        ->where('libelle', $request->libelle)
        ->where('description', $request->description)
        ->where('created_by', $request->created_by)
        ->first()) {
           $messages = [ 'Cet enregistrement existe déjà' ];
           return $this->sendApiErrors($messages);
        }

        if ($validator->fails()) return $this->sendApiErrors($validator->errors()->all());

        $rayon = Rayon::create($request->all());
        return $this->sendApiResponse($rayon, 'Rayon ajouté', 201);
    }

    public function show($id)
    {
        return new RayonResource(Rayon::find($id));
    }

    public function update(Request $request, $id) 
    {
        $validator = Validator::make(
           $request->all(),
           [
               'libelle' => 'required|unique:rayons,libelle,'.$id,
            //    'created_by' => 'required',
           ],
           $messages = [
               'libelle.required' => 'Le champ libelle ne peut etre vide',
            //    'created_by.required' => 'Le champ created_by ne peut etre vide',
           ]
         );

        $rayons = Rayon::latest();
        if ($rayons
        ->where('libelle', $request->libelle)
        ->where('description', $request->description)
        ->where('created_by', $request->created_by)
        ->where('id','!=', $id)->first()) {
           $messages = [ 'Cet enregistrement existe déjà' ];
           return $this->sendApiErrors($messages);
        }

        if ($validator->fails()) return $this->sendApiErrors($validator->errors()->all());

        $rayon = Rayon::find($id);
        $rayon->update($request->all());
        return $this->sendApiResponse($rayon, 'Rayon modifié', 201);
    }

    public function destroy($id) 
    {
        $rayon = Rayon::find($id);
        $rayon->delete();

        return $this->sendApiResponse($rayon, 'Rayon supprimé');
    }

    public function destroy_group(Request $request)
    {
        $key = 0;
        $nb_supprimes = 0;
        $messages= [];
        foreach ($request->selected_lines as $selected) {
            $rayon = Rayon::find($selected);
            if (isset($rayon)) {
                if ($rayon->est_valide == 1) {
                    $messages[$key] = [
                        'severity' => 'warn',
                        'value' => 'Impossible de supprimer ID0'.$selected
                    ];
                    $key++;
                }
                else {
                    $rayon->delete();
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

    public function get_inventaire_rayon($id)
    {
        return InventaireProduit::where('inventaire_id', $id)->distinct()->pluck('rayon_libelle');
    }
}
