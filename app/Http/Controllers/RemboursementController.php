<?php

namespace App\Http\Controllers;
use App\Models\Vente;
use App\Models\Client;
use Illuminate\Http\Request;
use App\Models\Remboursement;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\RemboursementResource;

class RemboursementController extends Controller
{

    public function index() 
    {
        $remboursements = Remboursement::latest();

        if (isset($_GET['req_count'])) return $this->filterByColumn('remboursements', $remboursements)->count();

        return RemboursementResource::collection($this->AsdecodefilterBy('remboursements', $remboursements));
    }

    public function store(Request $request) 
    {
        $validator = Validator::make(
           $request->all(),
           [
               'montant' => 'required',
               'created_by' => 'required',
               'client_id' => 'required',
               'venteId' => 'required',
           ],
           $messages = [
               'montant.required' => 'Le champ montant ne peut etre vide',
               'created_by.required' => 'Le champ created_by ne peut etre vide',
               'client_id.required' => 'Le champ client_id ne peut etre vide',
               'venteId.required' => 'Le champ venteId ne peut etre vide',
           ]
         );


         //on verifie le montant de la vente selectionné
         $vente = Vente::find($request->venteId);
         if ($vente) {
             $montant_a_payer = floatval($vente->total_prise_en_charge);
             $montant_versement = floatval($request->montant);
             if ($montant_versement < $montant_a_payer) {
                 $messages = [ 'Ce montant est inferieur au montant à payer' ];
                 return $this->sendApiErrors($messages);
             }
         }
         else {
             $messages = [ 'Choisissez une vente d\'abord' ];
             return $this->sendApiErrors($messages);
         }

        // $client = Client::find($request->client_id);
        // if ($client) {
        //     $reste_a_payer = floatval($client->current_dette);
        //     $montant_versement = floatval($request->montant);
        //     if ($montant_versement > $reste_a_payer) {
        //         $messages = [ 'Ce montant dépasse le reste à payer' ];
        //         return $this->sendApiErrors($messages);
        //     }
        // }
        // else {
        //     $messages = [ 'Choisissez un client d\'abord' ];
        //     return $this->sendApiErrors($messages);
        // }

        
        

        if ($validator->fails()) return $this->sendApiErrors($validator->errors()->all());

        $remboursement = Remboursement::create($request->all());

        DB::table('ventes')->where('id', $vente->id)->update([
            'statut' => 'Soldé'
        ]);
    

        return $this->sendApiResponse($remboursement, 'Remboursement ajouté', 201);
    }

    public function show($id)
    {
        return new RemboursementResource(Remboursement::find($id));
    }

    // public function update(Request $request, $id) 
    // {
    //     $validator = Validator::make(
    //        $request->all(),
    //        [
    //            //'montant' => 'required',
    //            //'reste_a_payer' => 'required',
    //            //'created_by' => 'required',
    //            //'client_id' => 'required',
    //        ],
    //        $messages = [
    //            //'montant.required' => 'Le champ montant ne peut etre vide',
    //            //'reste_a_payer.required' => 'Le champ reste_a_payer ne peut etre vide',
    //            //'created_by.required' => 'Le champ created_by ne peut etre vide',
    //            //'client_id.required' => 'Le champ client_id ne peut etre vide',
    //        ]
    //      );

    //     $remboursements = Remboursement::latest();
    //     if ($remboursements
    //     ->where('montant', $request->montant)
    //     ->where('reste_a_payer', $request->reste_a_payer)
    //     ->where('created_by', $request->created_by)
    //     ->where('client_id', $request->client_id)
    //     ->where('id','!=', $id)->first()) {
    //        $messages = [ 'Cet enregistrement existe déjà' ];
    //        return $this->sendApiErrors($messages);
    //     }

    //     if ($validator->fails()) return $this->sendApiErrors($validator->errors()->all());

    //     $remboursement = Remboursement::find($id);
    //     $remboursement->update($request->all());
    //     return $this->sendApiResponse($remboursement, 'Remboursement modifié', 201);
    // }

    public function destroy($id) 
    {
        $remboursement = Remboursement::find($id);
        $remboursement->delete();

        return $this->sendApiResponse($remboursement, 'Remboursement supprimé');
    }

   

}
