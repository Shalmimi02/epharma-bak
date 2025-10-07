<?php

namespace App\Http\Controllers;
use App\Models\Produit;
use App\Models\Inventaire;
use Illuminate\Http\Request;
use App\Models\InventaireProduit;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\InventaireResource;

class InventaireController extends Controller
{

    public function index() 
    {
        $inventaires = Inventaire::latest();
       
        if (isset($_GET['req_count'])) return $this->filterByColumn('inventaires', $inventaires)->count();

        if (isset($_GET['column_sum'])) return $inventaires->sum($_GET['column_sum']);

        if (isset($_GET['show_diff_only'])) $inventaires = $inventaires->where('ecart', '!=', '0');
        
        return InventaireResource::collection($this->AsdecodefilterBy('inventaires', $inventaires));
    }

    public function calculerCoutTotal($id)
    {
        // Récupérer tous les produits
        $produits = InventaireProduit::where('inventaire_id', $id)->get();

        // Variables pour stocker les coûts totaux
        $coutTotalAchat = 0;
        $coutTotalVente = 0;
        $coutTotalAchatReel = 0;
        $coutTotalVenteReel = 0;

        // Parcourir chaque produit pour effectuer les calculs
        foreach ($produits as $inventaire_produit) {
            // Convertir les prix d'achat et de vente en flottants, si non null
            $prixAchat = floatval($inventaire_produit->produit->prix_achat ?? 0);
            $prixVente = floatval($inventaire_produit->produit->prix_de_vente ?? 0);

            // Calculer le coût total pour chaque produit
            $coutTotalAchat += $prixAchat * intval($inventaire_produit->qte_initiale);
            $coutTotalVente += $prixVente * intval($inventaire_produit->qte_initiale);
            $coutTotalAchatReel += $prixAchat * intval($inventaire_produit->qte_reelle);
            $coutTotalVenteReel += $prixVente * intval($inventaire_produit->qte_reelle);
        }

        // Retourner les résultats
        return [
            'cout_total_achat' => $coutTotalAchat,
            'cout_total_vente' => $coutTotalVente,
            'cout_total_achat_reel' => $coutTotalAchatReel,
            'cout_total_vente_reel' => $coutTotalVenteReel,
            'diff_total_achat' => $coutTotalAchatReel - $coutTotalAchat,
            'diff_total_vente' => $coutTotalVenteReel - $coutTotalVente,
        ];
    }

    public function store(Request $request) 
    {
        $validator = Validator::make(
           $request->all(),
           [
               //'numero' => 'required',
               'type' => 'required',
               //'rayon' => 'required',
               //'created_by' => 'required',
               //'total_reel_cfa' => 'required',
               //'total_initial_cfa' => 'required',
               //'valeur_achat' => 'required',
               //'valeur_vente' => 'required',
               //'total_css' => 'required',
               //'is_closed' => 'required',
               //'closed_by' => 'required',
               //'closed_at' => 'required',
               //'ecart_only' => 'required',
               //'is_suspended' => 'required',
               //'suspended_by' => 'required',
               //'suspended_at' => 'required',
           ],
           $messages = [
               //'numero.required' => 'Le champ numero ne peut etre vide',
               'type.required' => 'Le champ type ne peut etre vide',
               //'rayon.required' => 'Le champ rayon ne peut etre vide',
               //'created_by.required' => 'Le champ created_by ne peut etre vide',
               //'total_reel_cfa.required' => 'Le champ total_reel_cfa ne peut etre vide',
               //'total_initial_cfa.required' => 'Le champ total_initial_cfa ne peut etre vide',
               //'valeur_achat.required' => 'Le champ valeur_achat ne peut etre vide',
               //'valeur_vente.required' => 'Le champ valeur_vente ne peut etre vide',
               //'total_css.required' => 'Le champ total_css ne peut etre vide',
               //'is_closed.required' => 'Le champ is_closed ne peut etre vide',
               //'closed_by.required' => 'Le champ closed_by ne peut etre vide',
               //'closed_at.required' => 'Le champ closed_at ne peut etre vide',
               //'ecart_only.required' => 'Le champ ecart_only ne peut etre vide',
               //'is_suspended.required' => 'Le champ is_suspended ne peut etre vide',
               //'suspended_by.required' => 'Le champ suspended_by ne peut etre vide',
               //'suspended_at.required' => 'Le champ suspended_at ne peut etre vide',
           ]
         );

        $inventaires = Inventaire::latest();
        // if ($inventaires
        // ->where('numero', $request->numero)
        // ->where('type', $request->type)
        // ->where('rayon', $request->rayon)
        // ->where('created_by', $request->created_by)
        // ->where('total_reel_cfa', $request->total_reel_cfa)
        // ->where('total_initial_cfa', $request->total_initial_cfa)
        // ->where('valeur_achat', $request->valeur_achat)
        // ->where('valeur_vente', $request->valeur_vente)
        // ->where('total_css', $request->total_css)
        // ->where('is_closed', $request->is_closed)
        // ->where('closed_by', $request->closed_by)
        // ->where('closed_at', $request->closed_at)
        // ->where('ecart_only', $request->ecart_only)
        // ->where('is_suspended', $request->is_suspended)
        // ->where('suspended_by', $request->suspended_by)
        // ->where('suspended_at', $request->suspended_at)
        // ->first()) {
        //    $messages = [ 'Cet enregistrement existe déjà' ];
        //    return $this->sendApiErrors($messages);
        // }

        // if ($validator->fails()) return $this->sendApiErrors($validator->errors()->all());


        // Créer l'inventaire, le numéro sera généré automatiquement par le modèle
        $inventaire = Inventaire::create($request->all());
        if ( $request->type =='Partiel'){
            foreach (Produit::where('rayon', $request->rayon)->where('is_active', 1)->get() as $produit) {
                InventaireProduit::create([
                    'inventaire_id' => $inventaire->id,
                    'produit_id' => $produit->id,
                    'qte' => $produit->qte,
                    'rayon_libelle' => $produit->rayon,
                    'produit_libelle' => $produit->libelle,
                    'produit_cip' => $produit->cip,
                ]);
            }
        }
        else {
           foreach (Produit::where('is_active', 1)->latest()->get() as $produit) {
                InventaireProduit::create([
                    'inventaire_id' => $inventaire->id,
                    'produit_id' => $produit->id,
                    'qte' => $produit->qte,
                    'rayon_libelle' => $produit->rayon ? $produit->rayon : 'Default',
                    'produit_libelle' => $produit->libelle,
                    'produit_cip' => $produit->cip,
                ]);
            }
        }

        return $this->sendApiResponse($inventaire, 'Inventaire ajouté', 201);
       
    }

    public function show($id)
    {
        return new InventaireResource(Inventaire::find($id));
    }

    public function update(Request $request, $id) 
    {
        $validator = Validator::make(
           $request->all(),
           [
               //'numero' => 'required',
               //'type' => 'required',
               //'rayon' => 'required',
               //'created_by' => 'required',
               //'total_reel_cfa' => 'required',
               //'total_initial_cfa' => 'required',
               //'valeur_achat' => 'required',
               //'valeur_vente' => 'required',
               //'total_css' => 'required',
               //'is_closed' => 'required',
               //'closed_by' => 'required',
               //'closed_at' => 'required',
               //'ecart_only' => 'required',
               //'is_suspended' => 'required',
               //'suspended_by' => 'required',
               //'suspended_at' => 'required',
           ],
           $messages = [
               //'numero.required' => 'Le champ numero ne peut etre vide',
               //'type.required' => 'Le champ type ne peut etre vide',
               //'rayon.required' => 'Le champ rayon ne peut etre vide',
               //'created_by.required' => 'Le champ created_by ne peut etre vide',
               //'total_reel_cfa.required' => 'Le champ total_reel_cfa ne peut etre vide',
               //'total_initial_cfa.required' => 'Le champ total_initial_cfa ne peut etre vide',
               //'valeur_achat.required' => 'Le champ valeur_achat ne peut etre vide',
               //'valeur_vente.required' => 'Le champ valeur_vente ne peut etre vide',
               //'total_css.required' => 'Le champ total_css ne peut etre vide',
               //'is_closed.required' => 'Le champ is_closed ne peut etre vide',
               //'closed_by.required' => 'Le champ closed_by ne peut etre vide',
               //'closed_at.required' => 'Le champ closed_at ne peut etre vide',
               //'ecart_only.required' => 'Le champ ecart_only ne peut etre vide',
               //'is_suspended.required' => 'Le champ is_suspended ne peut etre vide',
               //'suspended_by.required' => 'Le champ suspended_by ne peut etre vide',
               //'suspended_at.required' => 'Le champ suspended_at ne peut etre vide',
           ]
         );

        $inventaires = Inventaire::latest();
        if ($inventaires
        ->where('numero', $request->numero)
        ->where('type', $request->type)
        ->where('rayon', $request->rayon)
        ->where('created_by', $request->created_by)
        ->where('total_reel_cfa', $request->total_reel_cfa)
        ->where('total_initial_cfa', $request->total_initial_cfa)
        ->where('valeur_achat', $request->valeur_achat)
        ->where('valeur_vente', $request->valeur_vente)
        ->where('total_css', $request->total_css)
        ->where('is_closed', $request->is_closed)
        ->where('closed_by', $request->closed_by)
        ->where('closed_at', $request->closed_at)
        ->where('ecart_only', $request->ecart_only)
        ->where('is_suspended', $request->is_suspended)
        ->where('suspended_by', $request->suspended_by)
        ->where('suspended_at', $request->suspended_at)
        ->where('id','!=', $id)->first()) {
           $messages = [ 'Cet enregistrement existe déjà' ];
           return $this->sendApiErrors($messages);
        }

        if ($validator->fails()) return $this->sendApiErrors($validator->errors()->all());

        
        if($request->statut == 'Terminé'){
            //on compte le nombre de produit sans quantité réeele avant de terminer l'inventaire
            $compteur = 0;
            $lines = '';
            foreach (InventaireProduit::where('inventaire_id', $id)->get() as $inventaire_produit) {
                // $qte_reelle = intval($inventaire_produit->qte_reelle);
                $qte = $inventaire_produit->qte_reelle;
                $qteNumerique = (float) $qte;

                if (is_null($qte) || $qte === '') {
                    $lines = $lines . $inventaire_produit->produit_libelle.';';
                    $compteur++;
                }
                else if (!is_numeric($qte) || $qteNumerique < 0){
                    $lines = $lines . $inventaire_produit->produit_libelle.';';
                    $compteur++;
                }
            }
            if ( $compteur > 0) {
                $messages = [ 'Quantité reelle manquante sur '.$compteur .' produit(s) :'.$lines ];
                return $this->sendApiErrors($messages);
            }

            //si tout est correcte on modifie les quantités des produits en stock
            foreach (InventaireProduit::where('inventaire_id', $id)->get() as $inventaire_produit) {

                DB::table('produits')->where('id', $inventaire_produit->produit_id)->update([
                    'qte' => $inventaire_produit->qte_reelle
                ]);
            }
        }
        

        $inventaire = Inventaire::find($id);
        $inventaire->update($request->all());
        return $this->sendApiResponse($inventaire, 'Inventaire mis à jour', 201);
    }

    public function destroy($id) 
    {
        $inventaire = Inventaire::find($id);
        $inventaire->delete();

        return $this->sendApiResponse($inventaire, 'Inventaire supprimé');
    }

}
