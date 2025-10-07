<?php

namespace App\Http\Controllers;
use App\Models\Rayon;
use App\Models\Produit;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Imports\ProduitsImport;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Resources\ProduitResource;
use Illuminate\Support\Facades\Validator;

class ProduitController extends Controller
{

    public function index() 
    {
        $produits = Produit::orderBy('libelle', 'ASC');
        
        if (isset($_GET['req_qte_min']) && is_numeric($_GET['req_qte_min'])) {
            $produits = $produits->where('qte', '>=', (float) $_GET['req_qte_min']);
        }

        if (isset($_GET['req_qte_max']) && is_numeric($_GET['req_qte_max'])) {
            $produits = $produits->where('qte', '<=', (float) $_GET['req_qte_max']);
        }
        
        // Filtrer les produits vendus par période
        if (isset($_GET['vendu']) && isset($_GET['from_period_debut']) && isset($_GET['from_period_fin'])) {
            try {
                // Formatter les dates pour inclure toute la journée
                $dateDebut = date('Y-m-d 00:00:00', strtotime($_GET['from_period_debut']));
                $dateFin = date('Y-m-d 23:59:59', strtotime($_GET['from_period_fin']));
                
                // Log pour debugging
                \Log::info('Filtrage des produits vendus - Période:', [
                    'debut' => $dateDebut, 
                    'fin' => $dateFin
                ]);
                
                // Récupérer les IDs des produits vendus dans la période spécifiée
                $produits_vendus = DB::table('reservation_produits')
                    ->where('is_sold', 1)
                    ->whereBetween('created_at', [$dateDebut, $dateFin]);
                
                \Log::info('SQL Query:', ['sql' => $produits_vendus->toSql(), 'bindings' => $produits_vendus->getBindings()]);
                
                $produits_vendus_ids = $produits_vendus->pluck('produit_id')->toArray();
                
                \Log::info('Produits vendus trouvés:', ['count' => count($produits_vendus_ids), 'ids' => $produits_vendus_ids]);
                
                // Si aucun produit vendu n'est trouvé, utiliser un tableau vide pour éviter les erreurs SQL
                if (empty($produits_vendus_ids)) {
                    $produits_vendus_ids = [-1]; // ID impossible pour forcer un résultat vide mais valide
                    \Log::warning('Aucun produit vendu trouvé pour cette période');
                }
                
                // Filtrer les produits qui ont été vendus
                $produits = $produits->whereIn('id', $produits_vendus_ids);
            } catch (\Exception $e) {
                // Log détaillé de l'erreur
                \Log::error('Erreur lors du filtrage par période des produits vendus: ' . $e->getMessage());
                \Log::error('Trace: ' . $e->getTraceAsString());
            }
        }

        if (isset($_GET['req_count'])) return $this->filterByColumn('produits', $produits)->count();

        //calcul des compteurs juste avant d'ajouter la pagination
        $compteurs = $this->calculerCoutTotal($this->filterByColumn('produits', $produits)->get());

        $filtered_response = $this->AsdecodefilterBy('produits', $produits);
        $resourceCollection = ProduitResource::collection($filtered_response);

        return $resourceCollection->additional([
            'extra_data' => [
                'incoherances' => isset($_GET['with_incoherances']) ? $this->checkInCoherences($produits->get()) : null,
                'compteurs' => $compteurs,
                'periode_vente' => isset($_GET['vendu']) && isset($_GET['from_period_debut']) && isset($_GET['from_period_fin']) ? [
                    'debut' => $_GET['from_period_debut'],
                    'fin' => $_GET['from_period_fin'],
                    'nombre_produits_vendus' => count($produits_vendus_ids ?? [])
                ] : null 
            ]
        ]);
    }

    public function checkInCoherences($produits) {
        // Initialiser un tableau pour stocker les ids
        $ids = [];

        foreach ($produits as $produit ) {
            if ($this->verifierCoherenceProduit($produit) == true) {
                // Ajouter uniquement l'id au tableau
                $ids[] = $produit->id;
            }
        }

        return [
            'nb' => count($ids),
            'ids' => $ids
        ];
    }

    public function verifierCoherenceProduit(Produit $produit): bool
    {
        $epsilon = 0.01;

        $prixVente = $produit->prix_de_vente;
        $prixAchat = $produit->prix_achat;
        $coef = $produit->coef_conversion_de_prix_vente_achat;

        // Vérification des conditions
        if ($prixAchat == 0 || abs(($prixVente / $prixAchat) - $coef) < $epsilon) {
            return true;
        }
        
        if ($coef == 0 || abs(($prixAchat * $coef) - $prixVente) < $epsilon) {
            return true;
        }
        
        if ($coef == 0 || abs(($prixVente / $coef) - $prixAchat) < $epsilon) {
            return true;
        }

        if ($coef == 0 || $prixVente == 0 || $prixAchat == 0 ) {
            return true;
        }

        return false;
    }

    public function calculerCoutTotal($produits)
    {
        // Variables pour stocker les coûts totaux
        $coutTotalAchat = 0;
        $coutTotalVente = 0;

        // Parcourir chaque produit pour effectuer les calculs
        foreach ($produits as $produit) {
            // Convertir les prix d'achat et de vente en flottants, si non null
            $prixAchat = floatval($produit->prix_achat ?? 0);
            $prixVente = floatval($produit->prix_de_vente ?? 0);

            // Calculer le coût total pour chaque produit
            $coutTotalAchat += $prixAchat * $produit->qte;
            $coutTotalVente += $prixVente * $produit->qte;
        }

        // Retourner les résultats
        return [
            'cout_total_achat' => $coutTotalAchat,
            'cout_total_vente' => $coutTotalVente,
        ];
    }

    public function selected_by_id() 
    {
        if (isset($_GET['lines_selected']) && $_GET['lines_selected'] !== '')
        {
            $produits = Produit::latest();
        
            $lines_selected = Str::of($_GET['lines_selected'])->explode(',');
            foreach ($lines_selected as $line ) {
                $produits = $produits->orwhere('id', $line);
            }

            if (isset($_GET['req_count'])) return $this->filterByColumn('produits', $produits)->count();

            return ProduitResource::collection($this->AsdecodefilterBy('produits', $produits));
        }
        else abort(403);
    }

    public function filtered_by_keyword() 
    {
        $produits = Produit::latest();

        if (isset($_GET['keyword'])){
            $produits = $produits->orwhere('libelle', 'LIKE', '%'.$_GET['keyword'].'%');
        }

        return ProduitResource::collection($produits->get());
    }

    public function store(Request $request) 
    {
        $validator = Validator::make(
           $request->all(),
           [
               'libelle' => 'required|unique:produits',
               'cip' => [
                    'nullable',
                    'numeric',
                    'unique:produits,cip',
                    'regex:/^\d{7}$|^\d{13}$/',
                ],
                'cip_deux' => [
                    'nullable',
                    'numeric',
                    'unique:produits,cip_deux',
                    'regex:/^\d{7}$|^\d{13}$/',
                ],
                'qte' =>  'nullable|integer|min:0',

           ],
           $messages = [
               'libelle.required' => 'Le champ libelle ne peut etre vide',
               'libelle.unique' => 'Ce libelle existe déjà',
               'cip.required' => 'Le champ cip ne peut etre vide',
               'cip.regex' => 'Le champ CIP doit contenir soit 7 chiffres, soit 13 chiffres',
               'cip_deux.regex' => 'Le champ CIP 2 doit contenir soit 7 chiffres, soit 13 chiffres',
               'cip.unique' => 'Ce CIP existe déjà',
               'cip_deux.unique' => 'Ce CIP 2 existe déjà'
           ]
         );

        $produits = Produit::latest();
        if ($produits
        ->where('libelle', $request->libelle)
        ->where('cip', $request->cip)
        ->where('prix_achat', $request->prix_achat)
        ->where('coef_conversion_de_prix_vente_achat', $request->coef_conversion_de_prix_vente_achat)
        ->where('code', $request->code)
        ->where('description', $request->description)
        ->where('ean', $request->ean)
        ->where('dci', $request->dci)
        ->where('tva', $request->tva)
        ->where('css', $request->css)
        ->where('prix_de_vente', $request->prix_de_vente)
        ->where('posologie', $request->posologie)
        ->where('homologation', $request->homologation)
        ->where('forme', $request->forme)
        ->where('famille', $request->famille)
        ->where('nature', $request->nature)
        ->where('qte_min', $request->qte_min)
        ->where('qte_max', $request->qte_max)
        ->where('cip_deux', $request->cip_deux)
        ->where('fournisseurId', $request->fournisseurId)
        ->where('classe_therapeutique', $request->classe_therapeutique)
        ->where('categorie', $request->categorie)
        ->where('poids', $request->poids)
        ->where('longueur', $request->longueur)
        ->where('hauteur', $request->hauteur)
        ->where('code_table', $request->code_table)
        ->where('statut', $request->statut)
        ->where('code_fournisseur', $request->code_fournisseur)
        ->where('is_active', $request->is_active)
        ->where('photo', $request->photo)
        ->first()) {
           $messages = [ 'Cet enregistrement existe déjà' ];
           return $this->sendApiErrors($messages);
        }

        if ($validator->fails()) return $this->sendApiErrors($validator->errors()->all());

        //on s'assure que la quantité entré n'est jamais negative ou nulle
        $qte = intval($request->qte ?? 0);
        if ($qte < 0) {
            $messages = [ 'la quantité d\'un produit ne peut pas etre negatif ou nulle' ];
            return $this->sendApiErrors($messages);
        }

        $produit = Produit::create($request->all());
        return $this->sendApiResponse($produit, 'Produit ajouté', 201);
    }

    public function show($id)
    {
        return new ProduitResource(Produit::find($id));
    }

    public function update(Request $request, $id) 
    {
        $validator = Validator::make(
           $request->all(),
           [
               'libelle' => 'required|unique:produits,libelle,'.$id,
               'cip' => [
                    'nullable',
                    'numeric',
                    'unique:produits,cip,'.$id,
                    'regex:/^\d{7}$|^\d{13}$/',
                ],
                'cip_deux' => [
                    'nullable',
                    'numeric',
                    'unique:produits,cip_deux,'.$id,
                    'regex:/^\d{7}$|^\d{13}$/',
                ],
                'qte' =>  'nullable|integer|min:0',
           ],
           $messages = [
               'libelle.required' => 'Le champ libelle ne peut etre vide',
               'libelle.unique' => 'Ce libelle existe déjà',
               'cip.required' => 'Le champ cip ne peut etre vide',
               'cip.regex' => 'Le champ CIP doit contenir soit 7 chiffres, soit 13 chiffres',
               'cip_deux.regex' => 'Le champ CIP 2 doit contenir soit 7 chiffres, soit 13 chiffres',
               'cip.unique' => 'Ce CIP existe déjà',
               'cip_deux.unique' => 'Ce CIP 2 existe déjà'
               
           ]
         );

        $produits = Produit::latest();
        if ($produits
        ->where('libelle', $request->libelle)
        ->where('cip', $request->cip)
        ->where('prix_achat', $request->prix_achat)
        ->where('coef_conversion_de_prix_vente_achat', $request->coef_conversion_de_prix_vente_achat)
        ->where('code', $request->code)
        ->where('description', $request->description)
        ->where('ean', $request->ean)
        ->where('dci', $request->dci)
        ->where('tva', $request->tva)
        ->where('css', $request->css)
        ->where('prix_de_vente', $request->prix_de_vente)
        ->where('posologie', $request->posologie)
        ->where('homologation', $request->homologation)
        ->where('forme', $request->forme)
        ->where('famille', $request->famille)
        ->where('nature', $request->nature)
        ->where('qte_min', $request->qte_min)
        ->where('qte_max', $request->qte_max)
        ->where('cip_deux', $request->cip_deux)
        ->where('fournisseurId', $request->fournisseurId)
        ->where('classe_therapeutique', $request->classe_therapeutique)
        ->where('categorie', $request->categorie)
        ->where('poids', $request->poids)
        ->where('longueur', $request->longueur)
        ->where('hauteur', $request->hauteur)
        ->where('code_table', $request->code_table)
        ->where('statut', $request->statut)
        ->where('code_fournisseur', $request->code_fournisseur)
        ->where('is_active', $request->is_active)
        ->where('photo', $request->photo)
        ->where('id','!=', $id)->first()) {
           $messages = [ 'Cet enregistrement existe déjà' ];
           return $this->sendApiErrors($messages);
        }

        if ($validator->fails()) return $this->sendApiErrors($validator->errors()->all());

        //on s'assure que la quantité entré n'est jamais negative ou nulle
        // $qte = intval($request->qte);
        // if ($qte < 0 || $qte == null) {
        //     $messages = [ 'la quantité d\'un produit ne peut pas etre negatif ou nulle' ];
        //     return $this->sendApiErrors($messages);
        // }

        $produit = Produit::find($id);
        $produit->update($request->all());
        return $this->sendApiResponse($produit, 'Produit modifié');
    }

    public function destroy($id) 
    {
        $produit = Produit::find($id);
        $produit->delete();

        return $this->sendApiResponse($produit, 'Produit supprimé');
    }

    public function reduireQuantite(Request $request)
    {
        // Valider la requête pour s'assurer que produit_id est présent
        $validatedData = $request->validate([
            'produit_id' => 'required|integer|exists:produits,id',
        ]);

        // Récupérer le produit_id depuis la requête
        $produitId = $validatedData['produit_id'];

        // Chercher le produit dans la base de données
        $produit = Produit::find($produitId);

        if ($produit) {
            // Réduire la quantité de 1
            $produit->qte -= 1;

            // S'assurer que la quantité ne devient pas négative
            if ($produit->qte < 0) {
                $messages = [ 'La quantité du produit ne peut pas être négative.' ];
                return $this->sendApiErrors($messages);
            }

            // Sauvegarder les changements
            $produit->save();
            return $this->sendApiResponse($produit, 'Quantité du produit réduite avec succès.');
        }

        return $this->sendApiResponse($produit, 'Produit non trouvé');
    }

    public function import_excel(Request $request)
    {
        $produits = $request->input('produits');
        $lignesSauvegardes = [];
        $lignesIgnores = [];

        // Valider les données si nécessaire
        foreach ($produits as $medicament) {
            if (empty($medicament['libelle'])) {
                continue; // Passe à la ligne suivante si 'libelle' est vide
            }

            else if ($this->verifierProduitExistant($medicament['libelle'], $medicament['cip'], $medicament['cip_deux'])) {
                $lignesIgnores[] = $medicament['libelle'];
            }

            else {
                DB::table('produits')->insert([
                    'libelle'  => isset($medicament['libelle']) ? $medicament['libelle'] : null,
                    'cip'  => isset($medicament['cip']) ? $this->verifierCip($medicament['cip']) : null,
                    'cip_deux'  => isset($medicament['cip_deux']) ? $this->verifierCipDeux($medicament['cip_deux']) : null,
                    'prix_achat'  => isset($medicament['prix_achat']) ? $medicament['prix_achat'] : null,
                    'coef_conversion_de_prix_vente_achat'  => isset($medicament['coef_conversion_de_prix_vente_achat']) ? $medicament['coef_conversion_de_prix_vente_achat'] : null,
                    'code'  => isset($medicament['code']) ? $medicament['code'] : null,
                    'qte'  => isset($medicament['qte']) ? $medicament['qte'] : 0,
                    'is_active'  => isset($medicament['qte']) ? $this->verifierEtActiver($medicament['qte']) : false,
                    'qte_min'  => isset($medicament['qte_min']) ? $medicament['qte_min'] : 1,
                    'qte_max'  => isset($medicament['qte_max']) ? $medicament['qte_max'] : 5,
                    'description'  => isset($medicament['description']) ? $medicament['description'] : null,
                    'ean'  => isset($medicament['ean']) ? $medicament['ean'] : null,
                    'dci'  => isset($medicament['dci']) ? $medicament['dci'] : null,
                    'tva'  => isset($medicament['tva']) &&  Str::lower($medicament['tva']) == 'oui' ? true : false,
                    'css'  => isset($medicament['css']) &&  Str::lower($medicament['css']) == 'oui' ? true : false,
                    'prix_de_vente' => isset($medicament['prix_de_vente']) ? $medicament['prix_de_vente'] : null,
                    'posologie'  => isset($medicament['posologie']) ? $medicament['posologie'] : null,
                    'homologation'  => isset($medicament['homologation']) ? $medicament['homologation'] : null,
                    'forme'  => isset($medicament['forme']) ? $medicament['forme'] : null,
                    'famille'  => isset($medicament['famille']) ? $medicament['famille'] : null,
                    'nature'  => isset($medicament['nature']) ? $medicament['nature'] : null,
                    'classe_therapeutique'  => isset($medicament['classe_therapeutique']) ? $medicament['classe_therapeutique'] : null,
                    'categorie'  => isset($medicament['categorie']) ? $medicament['categorie'] : null,
                    'poids'  => isset($medicament['poids']) ? $medicament['poids'] : null,
                    'longueur'  => isset($medicament['longueur']) ? $medicament['longueur'] : null,
                    'hauteur'  => isset($medicament['hauteur']) ? $medicament['hauteur'] : null,
                    'largeur'  => isset($medicament['largeur']) ? $medicament['largeur'] : null,
                    'code_table'  => isset($medicament['code_table']) ? $medicament['code_table'] : null,
                    'statut'  => isset($medicament['statut']) ? $medicament['statut'] : null,
                    'code_fournisseur' =>isset($medicament['code_fournisseur']) ? $medicament['code_fournisseur'] : null,
                    'rayon' => isset($medicament['rayon']) ? $this->verifierOuCreerRayon($medicament['rayon'])->libelle : 'Default',
                    'rayon_id' => isset($medicament['rayon']) ? $this->verifierOuCreerRayon($medicament['rayon'], true)->id : 1,
                ]);

                $lignesSauvegardes[] = $medicament['libelle'];
            }
        }

        return $this->sendApiResponse([
            'importations' => $lignesSauvegardes,
            'nb_importations' => count($lignesSauvegardes),
            'importations_ignores' => $lignesIgnores,
            'nb_importations_ignores' => count($lignesIgnores),
        ], 'Importation de '.count($lignesSauvegardes).' produits terminés');
    }

    public function verifierEtActiver($qte)
    {
        // Vérifier si la quantité est un nombre et si elle est supérieure à zéro
        if (is_numeric($qte) && $qte > 0) {
            // Si les conditions sont remplies, on met is_active à true
            $is_active = true;
        } else {
            // Sinon, on ne change pas is_active
            $is_active = false;
        }

        // Retourner la valeur de is_active
        return $is_active;
    }

    public function verifierCip($cip)
    {
        if (strlen($cip) !== 7 && strlen($cip) !== 13) {
            return null; // Retourne null si la longueur est incorrecte
        }
        // Vérifier si le cip existe dans la table produits
        $produit = Produit::where('cip', $cip)->first();
        // Si le produit est trouvé, retourner null, sinon retourner le produit ou un message
        return $produit ? null : $cip;
    }

    public function verifierCipDeux($cip)
    {
        if (strlen($cip) !== 7 && strlen($cip) !== 13) {
            return null; // Retourne null si la longueur est incorrecte
        }
        // Vérifier si le cip existe dans la table produits
        $produit = Produit::where('cip_deux', $cip)->first();
        // Si le produit est trouvé, retourner null, sinon retourner le produit ou un message
        return $produit ? null : $cip;
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

    public function verifierProduitExistant($libelle, $cip = null, $cip2 = null){
        $produit = Produit::where('libelle', $libelle);

        if ($cip) {
            $produit =  $produit->orwhere('cip', $cip);
        }

        if ($cip2) {
            $produit =  $produit->where('cip_deux', $cip2);
        }

        if ($produit->first()) {
            return true;
        } return false;
    }

    public function equivalent_filter(Request $request) {
         // Valider la requête pour s'assurer que les CIPs sont fournis
         $validated = $request->validate([
            'cips' => 'required|array', // Les CIPs doivent être un tableau
            'cips.*' => 'string',      // Chaque CIP doit être une chaîne de caractères
        ]);

        $cips = $validated['cips'];

        // Rechercher les produits actifs dans la base de données correspondant aux CIPs
        $produitsCorrespondants = Produit::where(function ($query) use ($cips) {
            $query->whereIn('cip', $cips)
                  ->orWhereIn('cip_deux', $cips);
        })->get();

        // Retourner les produits correspondants en réponse JSON
        return response()->json($produitsCorrespondants, 200);
    }

    public function verifierDisponibilite($code) {
        $produit = Produit::where('cip', $code)->orwhere('cip_deux', $code)->first();
        return response()->json(['qte' => $produit ? $produit->qte : 0]);
    }

}
