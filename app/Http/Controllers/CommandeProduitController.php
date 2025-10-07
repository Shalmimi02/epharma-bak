<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Rayon;
use App\Models\Produit;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\CommandeProduit;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\CommandeProduitResource;

class CommandeProduitController extends Controller
{

    public function index() 
    {
        $commande_produit = CommandeProduit::orderBy('produit_libelle', 'ASC');

        if (isset($_GET['from_period_debut'])) {
            $commande_produits = $commande_produits->where('created_at', '>=',$_GET['from_period_debut']);
        }

        if (isset($_GET['from_period_fin'])) {
            $commande_produits = $commande_produits->where('created_at', '<=',$_GET['from_period_fin']);
        }
    
        if (isset($_GET['show_diff_only']) && ($_GET['show_diff_only'] == 1 || ($_GET['show_diff_only'] == 'true')) ) {
            $commande_produit = $commande_produit->whereNotNull('total_ttc')->where('total_ttc', '!=', '0');
        }

        if (isset($_GET['column_sum'])) return $commande_produit->sum($_GET['column_sum']);

        if (isset($_GET['req_count'])) return $this->filterByColumn('commande_produit', $commande_produit)->count();

        $incoherances = isset($_GET['commande_id']) ? $this->checkInCoherences($commande_produit->where('commande_id', $_GET['commande_id'])->get()) : null;

        $filtered_response = $this->AsdecodefilterBy('commande_produit', $commande_produit);

        $resourceCollection = CommandeProduitResource::collection($filtered_response);

        return $resourceCollection->additional([
            'extra_data' => [
                'incoherances' => $incoherances,
                'compteurs' => isset($_GET['commande_id']) ? $this->calculerCoutTotal($_GET['commande_id']) : null
            ]
        ]);

    }

    public function calculerCoutTotal($id)
    {
        // Récupérer tous les produits
        $produits = CommandeProduit::where('commande_id', $id)->get();

        // Variables pour stocker les coûts totaux
        $coutTotalAchat = 0;
        $coutTotalVente = 0;
        $coutTotalCss = 0;
        $coutTotalTva = 0;
        $coutTotalHt = 0;

        // Parcourir chaque produit pour effectuer les calculs
        foreach ($produits as $produit) {
            // Convertir les prix d'achat et de vente en flottants, si non null
            $prixAchat = floatval($produit->prix_achat ?? 0);
            $prixVente = floatval($produit->prix_de_vente ?? 0);
            $totalCss = floatval($produit->total_css ?? 0);
            $totalTva = floatval($produit->total_tva ?? 0);
            $totalHt = floatval($produit->total_ht ?? 0);

            // Calculer le coût total pour chaque produit
            $coutTotalAchat += $prixAchat * $produit->qte;
            $coutTotalVente += $prixVente * $produit->qte;
            $coutTotalCss += $totalCss;
            $coutTotalTva += $totalTva;
            $coutTotalHt += $totalHt;
        }

        // Retourner les résultats
        return [
            'cout_total_achat' => $coutTotalAchat,
            'cout_total_vente' => $coutTotalVente,
            'cout_total_css' => $coutTotalCss,
            'cout_total_tva' => $coutTotalTva,
            'cout_total_ht' => $coutTotalHt,
        ];
    }

    public function store(Request $request) 
    {
        $validator = Validator::make(
           $request->all(),
           [
                'date_expiration' => ['date', 'after:today'],
               //'commande_id' => 'required',
               //'produit_id' => 'required',
               //'qte' => 'required',
               //'total_ht' => 'required',
               //'total_ttc' => 'required',
           ],
           $messages = [
               //'commande_id.required' => 'Le champ commande_id ne peut etre vide',
               //'produit_id.required' => 'Le champ produit_id ne peut etre vide',
               //'qte.required' => 'Le champ qte ne peut etre vide',
               //'total_ht.required' => 'Le champ total_ht ne peut etre vide',
               //'total_ttc.required' => 'Le champ total_ttc ne peut etre vide',
           ]
         );

        $commande_produit = CommandeProduit::latest();
        if ($commande_produit
        ->where('commande_id', $request->commande_id)
        ->where('produit_id', $request->produit_id)
        ->first()) {
           $messages = [ 'Cet enregistrement existe déjà' ];
           return $this->sendApiErrors($messages);
        }

        if ($validator->fails()) return $this->sendApiErrors($validator->errors()->all());


        $commande_produit = CommandeProduit::create($request->all());
        return $this->sendApiResponse($commande_produit, $commande_produit->produit_libelle.' ajouté à la commande', 201);
    }

    public function show($id)
    {
        return new CommandeProduitResource(CommandeProduit::find($id));
    }

    public function update(Request $request, $id) 
    {
        $validator = Validator::make(
           $request->all(),
           [
            'date_expiration' => ['date'],
            //    'rayon' => 'required',
            //    'commande_id' => 'required',
               //'produit_id' => 'required',
               //'qte' => 'required',
               //'total_ht' => 'required',
               //'total_ttc' => 'required',
           ],
           $messages = [
                // 'rayon.required' => 'Le champ rayon ne peut etre vide',
               //'commande_id.required' => 'Le champ commande_id ne peut etre vide',
               //'produit_id.required' => 'Le champ produit_id ne peut etre vide',
               //'qte.required' => 'Le champ qte ne peut etre vide',
               //'total_ht.required' => 'Le champ total_ht ne peut etre vide',
               //'total_ttc.required' => 'Le champ total_ttc ne peut etre vide',
           ]
         );

        $commande_produit = CommandeProduit::latest();
        if ($commande_produit
        ->where('commande_id', $request->commande_id)
        ->where('produit_id', $request->produit_id)
        ->where('id','!=', $id)->first()) {
           $messages = [ 'Cet enregistrement existe déjà' ];
           return $this->sendApiErrors($messages);
        }

        if ($validator->fails()) return $this->sendApiErrors($validator->errors()->all());

        if (isset($request->qte) && ($request->qte == 0)) {
            $messages = [ 'Impossible de commander la quantité 0' ];
            return $this->sendApiErrors($messages);
        }

        $commande_produit = CommandeProduit::find($id);
        $commande_produit->update($request->all());
        return $this->sendApiResponse($commande_produit, 'Produit mis à jour', 201);
    }

    public function destroy($id) 
    {
        $commande_produit = CommandeProduit::find($id);
        $commande_produit->delete();

        return $this->sendApiResponse($commande_produit, $commande_produit->produit_libelle.' retiré');
    }

    public function destroy2(Request $request) 
    {
        $commande_produit = CommandeProduit::where('commande_id', $request->commande_id)->where('produit_id', $request->produit_id)->first();
        $commande_produit->delete();

        return $this->sendApiResponse($commande_produit, $commande_produit->produit_libelle.' retiré');
    }

    public function checkInCoherences($produits) {
        // Initialiser un tableau pour stocker les ids
        $ids = [];

        foreach ($produits as $produit ) {
            if ($this->verifierCoherence($produit) == true) {
                // Ajouter uniquement l'id au tableau
                $ids[] = $produit->id;
            }
        }

        return [
            'nb' => count($ids),
            'ids' => $ids
        ];
    }

    public function import_excel(Request $request)
    {
        $produits = $request->input('produits');
        $lignesSauvegardes = [];
        $lignesIgnores = [];

        if (!$request->commande_id) {
            $messages = [ 'Impossible d\'importer sans selectionner de commande' ];
            return $this->sendApiErrors($messages);
        }

        $lignesIgnore = []; //sauvegarder les lignes a ignorer

        // Valider les données si nécessaire
        foreach ($produits as $medicament) {
            if (empty($medicament['produit_cip'])) {
                continue; // Passe à la ligne suivante si 'produit_cip' est vide
            }

            if (isset($medicament['qte']) && intval($medicament['qte']) < 0) {
                $messages = [ 'Impossible d\'ajouter une quantité negative' ];
                return $this->sendApiErrors($messages);
            }

            $produit = $this->verifierCip($medicament['produit_cip'], $request->commande_id);

            if (!$produit) {
                $lignesIgnores[] = $medicament['produit_cip'];
                continue;
            } else $lignesSauvegardes[] = $medicament['produit_cip'];
            
            $tva = 0;
            $css = 0;
            $total_tva = 0;
            $total_css = 0;
            $total_ttc = 0;
            $total_ht = 0;
            $total_achat = 0;
            $prix_achat = 0;
            $prix_de_vente = 0;
            $qte = 0;
            $qte_initiale = 0;
            $qte_finale = 0;
            $coef_conversion_de_prix_vente_achat = 1;

            if(isset($produit->tva) &&  Str::lower($produit->tva) == 'oui') $tva = $request->tva;
            if(isset($produit->css) &&  Str::lower($produit->css) == 'oui') $css = $request->css;
            if(isset($medicament['prix_achat'])) {
                $prix_achat = floatval($medicament['prix_achat']);
                $total_achat = floatval($medicament['prix_achat']);
            } else $prix_achat = $produit->prix_achat;
            if(isset($medicament['prix_de_vente'])) {
                $prix_de_vente = floatval($medicament['prix_de_vente']);
                $total_ht = floatval($medicament['prix_de_vente']);
            } else $prix_de_vente = $produit->prix_de_vente;
            if(isset($medicament['qte'])) {
                $qte = intval($medicament['qte']);
                $total_ht = $prix_de_vente * $qte;
                $total_achat = $prix_achat * $qte;
            } else $qte = 1;
            if(isset($produit->qte) && intval($produit->qte) > 0) {
                $qte_initiale = intval($produit->qte);
                $qte_finale = intval($produit->qte) + $qte;
            } else {
                $qte_initiale = 0;
                $qte_finale = 0 + $qte;
            };
            if($prix_de_vente && $prix_achat) {
                $coef_conversion_de_prix_vente_achat = round($prix_de_vente / $prix_achat, 2);
            } else $coef_conversion_de_prix_vente_achat = $produit->coef_conversion_de_prix_vente_achat;
            if($tva != 0){
                $total_tva = round($prix_de_vente * $tva, 2);
                $total_ht = round($total_ht - $total_tva, 2);
            }
            if($css != 0){
                $total_css = round($prix_de_vente * $css, 2);
                $total_ht = round($total_ht - $total_css, 2);
            }


            CommandeProduit::create([
                'commande_id'  => $request->commande_id,
                'produit_id'  => intval($produit->id),
                'produit_libelle'  => $produit->libelle,
                'produit_cip'  => $produit->cip,
                'prix_achat'  => $prix_achat,
                'prix_de_vente'  => $prix_de_vente,
                'rayon' => isset($medicament['rayon']) ? $this->verifierOuCreerRayon($medicament['rayon'])->libelle : 'Default',
                'rayonId' => isset($medicament['rayon']) ? $this->verifierOuCreerRayon($medicament['rayon'], true)->id : 1,
                'qte'  => $qte,
                'lot'  => isset($medicament['lot']) ? $medicament['lot'] : null,
                'date_expiration'  => isset($medicament['date_expiration']) ? $this->convertToDate($medicament['date_expiration']) : null,
                'qte_initiale'  => $qte_initiale,
                'qte_finale'  => $qte_finale,
                'coef_conversion_de_prix_vente_achat'  => $coef_conversion_de_prix_vente_achat,
                'total_tva' => $total_tva,
                'total_css' => $total_css,
                'total_ttc' => $total_ht + $total_tva + $total_css,
                'total_ht' => $total_ht,
                'total_achat' => $total_achat,
            ]);
        }

        return $this->sendApiResponse([
            'importations' => $lignesSauvegardes,
            'nb_importations' => count($lignesSauvegardes),
            'importations_ignores' => $lignesIgnores,
            'nb_importations_ignores' => count($lignesIgnores),
        ], 'Importation de '.count($lignesSauvegardes). ' lignes terminé');
    }

    public function convertToDate($dateExpiration)
    {

        $formats = ['d/m/Y', 'Y-m-d']; // Liste des formats acceptés

        foreach ($formats as $format) {
            try {
                return Carbon::createFromFormat($format, $dateExpiration);
            } catch (\Exception $e) {
                // Continuer à tester les formats suivants
            }
        }

        // Si aucun format ne correspond
        return null;
    }

    public function verifierCip($cip, $commande_id)
    {
        if (strlen($cip) !== 7 && strlen($cip) !== 13) {
            return null; // Retourne null si la longueur est incorrecte
        }
        // Vérifier si le cip existe dans la table produits
        $produit = Produit::where('cip', $cip)->orwhere('cip_deux', $cip)->first();

        //verifier si le produit n'est pas deja dans la commande
        if($produit && CommandeProduit::where('commande_id', intval($commande_id))->where('produit_id', $produit->id)->first()){
            return null;
        }

        // Si le produit est trouvé, retourner le produit 
        return $produit ? $produit : null;
    }

    public function verifierOuCreerRayon($libelle_rayon){
        // Vérifier si le rayon existe déjà dans la table rayons
        $rayon = Rayon::where('libelle', $libelle_rayon)->first();

        // Si le rayon existe, on retourne son libellé
        if ($rayon) {
            return $rayon;
        }

        // Si le rayon n'existe pas, on le crée
        $nouveau_rayon = Rayon::create([
            'libelle' => $libelle_rayon
        ]);

        // Retourner le libellé du nouveau rayon créé
        return $nouveau_rayon;
    }

}
