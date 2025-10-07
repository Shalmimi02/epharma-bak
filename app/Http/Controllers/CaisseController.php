<?php

namespace App\Http\Controllers;
use App\Models\Caisse;
use Illuminate\Http\Request;
use App\Http\Resources\CaisseResource;
use Illuminate\Support\Facades\Validator;

class CaisseController extends Controller
{

    public function index() 
    {
        $caisses = Caisse::latest();

        if (isset($_GET['req_count'])) return $this->filterByColumn('caisses', $caisses)->count();

        return CaisseResource::collection($this->AsdecodefilterBy('caisses', $caisses));
    }

    public function store(Request $request) 
    {
        $validator = Validator::make(
           $request->all(),
           [
               'libelle' => 'required|unique:caisses,libelle',
               'pin' => 'required',
               'created_by' => 'required',
           ],
           $messages = [
               'libelle.required' => 'Le champ libelle ne peut etre vide',
               'unique.required' => 'Cette caisse existe d\'ejà',
               'pin.required' => 'Le champ pin ne peut etre vide',
               'created_by.required' => 'Le champ created_by ne peut etre vide',
               'libelle.unique' => 'Ce libelle existe déjà'
           ]
         );

        $caisses = Caisse::latest();
        if ($caisses
        ->where('libelle', $request->libelle)
        ->where('current_authorized_user', $request->current_authorized_user)
        ->where('pin', $request->pin)
        ->where('is_open', $request->is_open)
        ->where('is_locked', $request->is_locked)
        ->where('last_login', $request->last_login)
        ->where('created_by', $request->create_by)
        ->first()) {
           $messages = [ 'Cet enregistrement existe déjà' ];
           return $this->sendApiErrors($messages);
        }

        if ($validator->fails()) return $this->sendApiErrors($validator->errors()->all());

        $caisse = Caisse::create($request->all());
        return $this->sendApiResponse($caisse, 'Caisse ajoutée', 201);
    }

    public function show($id)
    {
        return new CaisseResource(Caisse::find($id));
    }

    public function update(Request $request, $id) 
    {
        $validator = Validator::make(
           $request->all(),
           [
            'libelle' => 'required|unique:caisses,libelle,'.$id,
            'pin' => 'required',
            // 'created_by' => 'required',
            ],
            $messages = [
                'libelle.required' => 'Le champ libelle ne peut etre vide',
                'unique.required' => 'Cette caisse existe d\'ejà',
                'pin.required' => 'Le champ pin ne peut etre vide',
                'created_by.required' => 'Le champ created_by ne peut etre vide',
                'libelle.unique' => 'Ce libelle existe déjà'
            ]
         );

        $caisses = Caisse::latest();
        if ($caisses
        ->where('libelle', $request->libelle)
        ->where('current_authorized_user', $request->current_authorized_user)
        ->where('pin', $request->pin)
        ->where('is_open', $request->is_open)
        ->where('is_locked', $request->is_locked)
        ->where('last_login', $request->last_login)
        ->where('created_by', $request->create_by)
        ->where('id','!=', $id)->first()) {
           $messages = [ 'Cet enregistrement existe déjà' ];
           return $this->sendApiErrors($messages);
        }

        if ($validator->fails()) return $this->sendApiErrors($validator->errors()->all());

        $caisse = Caisse::find($id);
        $caisse->update($request->all());
        return $this->sendApiResponse($caisse, 'Caisse modifiée', 201);
    }

    public function destroy($id) 
    {
        $caisse = Caisse::find($id);
        $caisse->delete();

        return $this->sendApiResponse($caisse, 'Caisse supprimée');
    }

    public function destroy_group(Request $request)
    {
        $key = 0;
        $nb_supprimes = 0;
        $messages= [];
        foreach ($request->selected_lines as $selected) {
            $caisse = Caisse::find($selected);
            if (isset($caisse)) {
                if ($caisse->est_valide == 1) {
                    $messages[$key] = [
                        'severity' => 'warn',
                        'value' => 'Impossible de supprimer ID0'.$selected
                    ];
                    $key++;
                }
                else {
                    $caisse->delete();
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

    public function login(Request $request, $id)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'fullname' => 'required',
                'pin' => 'required',
            ],
            $messages = [
                'fullname.required' => 'Le champ libelle ne peut etre vide',
                'pin.required' => 'Le champ pin ne peut etre vide',
            ]
          );

        if ($validator->fails()) return $this->sendApiErrors($validator->errors()->all());

        if (Caisse::where(['pin' => $request->pin, 'id' => $id])->first()) {

            $caisse = Caisse::find($id);
            $caisse->update([
                'current_authorized_user' => $request->fullname,
                'last_login' => now(),
            ]);

            return $this->sendApiResponse($caisse, 'Bienvenue');
        }

        return $this->sendApiErrors(['PIN incorrect.']);
    }
}
