<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Produit;
use App\Models\Commande;
use Illuminate\Http\Request;
use App\Models\CommandeProduit;
use Illuminate\Validation\Rule;
use App\Http\Resources\CommandeResource;
use Illuminate\Support\Facades\Validator;

class CommandeController extends Controller
{

    public function index()
    {
        $commandes = Commande::latest();

        if (isset($_GET['req_count'])) {
            return $this->filterByColumn('commandes', $commandes)->count();
        }

        if (isset($_GET['period_from'])) {
            $commandes = $commandes->where('created_at', '>=',date('Y-m-d', strtotime($_GET['period_from'])) . ' 00:00:00');
        }

        if (isset($_GET['period_to'])) {
            $commandes = $commandes->where('created_at', '<=',date('Y-m-d', strtotime($_GET['period_to'])) . ' 23:59:59');
        }
        // if (isset($_GET['column_sum'])) return $commandes->sum($_GET['column_sum']);

        //calcul des compteurs juste avant d'ajouter la pagination
        $compteurs = $this->calculerCoutTotalCommande($this->filterByColumn('commandes', $commandes)->get());
        
        //filtrer en ajoutant la pagination
        $filtered_response = $this->AsdecodefilterBy('commandes', $commandes);
        $resourceCollection = CommandeResource::collection($filtered_response);

        return $resourceCollection->additional([
            'extra_data' => [
                'compteurs' => $compteurs 
            ]
        ]);
    }

    public function calculerCoutTotalCommande($commandes)
    {
        // Variables pour stocker les coûts totaux
        $coutTotalAchat = 0;
        $coutTotalVente = 0;

        // Parcourir chaque produit pour effectuer les calculs
        foreach ($commandes as $commande) {
            // Convertir les prix d'achat et de vente en flottants, si non null
            $prixAchat = floatval($commande->total_achat ?? 0);
            $prixVente = floatval($commande->total_vente ?? 0);

            // Calculer le coût total pour chaque commande
            $coutTotalAchat += $prixAchat;
            $coutTotalVente += $prixVente;
        }

        // Retourner les résultats
        return [
            'cout_total_achat' => $coutTotalAchat,
            'cout_total_vente' => $coutTotalVente,
        ];
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

    public function get_commandes_of_product($id) {
        $commande_produits =  CommandeProduit::where('produit_id', $id)->get();

        $commandes = Commande::latest();

        foreach ($commande_produits as $commande_produit) {
            $commandes = $commandes->orwhere('id', $commande_produit->commande_id);
        }

        return CommandeResource::collection($this->AsdecodefilterBy('commandes', $commandes));
    }

    public function store(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'facture' => 'unique:commandes',
                'created_by' => 'required',
            ],
            $messages = [
                'facture.required' => 'Le champ facture ne peut etre vide',
                'created_by.required' => 'Le champ created_by ne peut etre vide',
            ]
        );

        if ($validator->fails()) return $this->sendApiErrors($validator->errors()->all());

        $commande = Commande::create($request->all());
        return $this->sendApiResponse($commande, 'Nouvelle commande ajouté', 201);
    }

    public function show($id)
    {
        return new CommandeResource(Commande::find($id));
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make(
            $request->all(),
            [
                //'numero' => 'required',
                'facture' => ['nullable', Rule::unique('commandes')->ignore($id), 'alpha_num'],
                //'description' => 'required',
                //'fournisseur' => 'required',
                //'fournisseur_name' => 'required',
                //'status' => 'required',
                // 'created_by' => 'required',
                //'total_achat' => 'required',
            ],
            $messages = [
                //'numero.required' => 'Le champ numero ne peut etre vide',
                'facture.unique' => 'Ce numero de facture est déjà utilisé',
                //'description.required' => 'Le champ description ne peut etre vide',
                //'fournisseur.required' => 'Le champ fournisseur ne peut etre vide',
                //'fournisseur_name.required' => 'Le champ fournisseur_name ne peut etre vide',
                //'status.required' => 'Le champ status ne peut etre vide',
                //'created_by.required' => 'Le champ created_by ne peut etre vide',
                //'total_achat.required' => 'Le champ total_achat ne peut etre vide',
            ]
        );

        $commandes = Commande::latest();
        if ($commandes
            ->where('numero', $request->numero)
            ->where('facture', $request->facture)
            ->where('description', $request->description)
            ->where('fournisseur', $request->fournisseur)
            ->where('fournisseur_libelle', $request->fournisseur_libelle)
            ->where('status', $request->status)
            ->where('created_by', $request->created_by)
            ->where('total_achat', $request->total_achat)
            ->where('id', '!=', $id)->first()) {
            $messages = ['Cet enregistrement existe déjà'];
            return $this->sendApiErrors($messages);
        }

        if ($validator->fails()) {
            return $this->sendApiErrors($validator->errors()->all());
        }

        if ($request->status == 'SUCCESS') {
           //on compte le nombre de produit sans quantité rayon avant de terminer la commande
           $compteurRayon = 0;
           $compteurDateExpiration = 0;
           $compteurCoef = 0;
           $compteurLot = 0;
           $compteurIncoherance = 0;
           foreach (CommandeProduit::where('commande_id', $id)->get() as $commande_produit) {
                // if (!$commande_produit->date_expiration) {
                //     $compteurDateExpiration++;
                // }
                if ($this->verifierCoherence($commande_produit) == true) {
                    $compteurIncoherance++;
                }
                if (!$commande_produit->rayon || $commande_produit->rayon == '') {
                    $compteurRayon++;
                }
                
                // if (!$commande_produit->coef_conversion_de_prix_vente_achat) {
                //     $compteurCoef++;
                // }
                // if (!$commande_produit->lot || $commande_produit->lot == '') {
                //     $compteurLot++;
                // }
           }
           if ( $compteurIncoherance > 0) {
                $messages = [ 'Incoherances sur '.$compteurIncoherance .' produit(s) ' ];
                return $this->sendApiErrors($messages);
            }
           if ( $compteurRayon > 0) {
               $messages = [ 'Rayon manquant sur '.$compteurRayon .' produit(s) ' ];
               return $this->sendApiErrors($messages);
           }
           if ( $compteurDateExpiration > 0) {
                $messages = [ 'Date d\'expiration manquante sur '.$compteurDateExpiration .' produit(s) ' ];
                return $this->sendApiErrors($messages);
            }
            if ( $compteurCoef > 0) {
                $messages = [ 'Coefficient manquant sur '.$compteurCoef .' produit(s) ' ];
                return $this->sendApiErrors($messages);
            }
            if ( $compteurCoef > 0) {
                $messages = [ 'Lot manquant sur '.$compteurCoef .' produit(s) ' ];
                return $this->sendApiErrors($messages);
            }
            
        }

        $commande = Commande::find($id);
        $commande->update($request->all());
        return $this->sendApiResponse($commande, 'Commande mis à jour');
    }

    public function destroy($id)
    {
        $commande = Commande::find($id);
        $commande->delete();

        return $this->sendApiResponse($commande, 'Commande supprimé');
    }

    public function add_product($id)
    {
        // Rechercher une commande avec le statut "created" pour l'utilisateur donné
        $commande = Commande::where('status', 'CREATED')->first();

        // Si la commande n'existe pas, en créer une nouvelle
        if (!$commande) {
            $commande = new Commande();
            $commande->created_by = 'Testeur';
            $commande->status = 'CREATED';
            $commande->total_achat = 0;
            $commande->save();
        }

        $produit = Produit::find($id);

        if ($produit) {
            //on verifie si le produit n'est pas déja dans la commande
            if (CommandeProduit::where('commande_id', $commande->id)->where('produit_id', $produit->id)->first()) {
                $commande_produit = CommandeProduit::where('commande_id', $commande->id)->where('produit_id', $produit->id)->first();
                $commande_produit->update([
                    'qte' => intval($commande_produit->qte) + 1,
                    'qte_finale' => intval($commande_produit->qte_finale) + 1,
                ]);
            }
            else {
                // Ajouter le produit à la commande

                $initiale_qte = intval($produit->qte);
                CommandeProduit::create([
                    'commande_id' => $commande->id,
                    'produit_id' => $produit->id,
                    'qte' => 1,
                    'qte_initiale' => $initiale_qte,
                    'qte_finale' => $initiale_qte +1,
                    'rayon' => $produit->rayon,
                    'produit_libelle' => $produit->libelle,
                    'produit_cip' => $produit->cip,
                    'coef_conversion_de_prix_vente_achat'=> $produit->coef_conversion_de_prix_vente_achat,
                    'prix_de_vente'=> $produit->prix_de_vente,
                ]);
            }            

            // Optionnel: Retourner une réponse API avec la commande mise à jour
            return $this->sendApiResponse($commande, 'Produit ajouté à la commande N°'.$commande->numero);
        }
        
    }
    public function destroy_group(Request $request)
    {
        $key = 0;
        $nb_supprimes = 0;
        $messages = [];
        foreach ($request->selected_lines as $selected) {
            $commande = Commande::find($selected);
            if (isset($commande)) {
                if ($commande->est_valide == 1) {
                    $messages[$key] = [
                        'severity' => 'warn',
                        'value' => 'Impossible de supprimer ID0' . $selected,
                    ];
                    $key++;
                } else {
                    $commande->delete();
                    $nb_supprimes++;
                    $messages[$key] = [
                        'severity' => 'success',
                        'value' => $nb_supprimes . ' lignes ont été supprimé',
                    ];
                }
            }
        }
        return $this->sendApiResponse([], $messages);
    }

    // public function recalculerCout($id){
    //     foreach (CommandeProduit::where('commande_id', $id)->get() as $commande_produit) {
    //         if ($this->verifierCoherence($commande_produit) == true) {
    //             $calc = $this->recalculerPrix()
    //             $commande_produit->update([
    //                 'prix_achat' => $prixAchat,
    //                 'prix_de_vente' => $prixVente,
    //                 'coef_conversion_de_prix_vente_achat' => $coef,
    //             ]);
    //         }
            
    //     }

    //     return true;
    // }

    // public function recalculerPrix(string $field, $prixAchatVal, $prixVenteVal, $coefVal, $newValueVal)
    // {
    //     // Conversion des valeurs en float ou initialisation à 0 si conversion impossible
    //     $prixAchat = (float) $prixAchatVal ?: 0;
    //     $prixVente = (float) $prixVenteVal ?: 0;
    //     $coef = (float) $coefVal ?: 0;
    //     $newValue = (float) $newValueVal ?: 0;

    //     if ($field === 'prix_achat') {
    //         $prixAchat = $newValue;
    //         if ($prixVente > 0) {
    //             $coef = $prixAchat !== 0 ? $prixVente / $prixAchat : 0;
    //         } elseif ($coef > 0) {
    //             $prixVente = $prixAchat * $coef;
    //         }
    //     } elseif ($field === 'prix_de_vente') {
    //         $prixVente = $newValue;
    //         if ($prixAchat > 0) {
    //             $coef = $prixAchat !== 0 ? $prixVente / $prixAchat : 0;
    //         } elseif ($coef > 0) {
    //             $prixAchat = $coef !== 0 ? $prixVente / $coef : 0;
    //         }
    //     } elseif ($field === 'coef_conversion_de_prix_vente_achat') {
    //         $coef = $newValue;
    //         if ($prixAchat > 0) {
    //             $prixVente = $prixAchat * $coef;
    //         } elseif ($prixVente > 0) {
    //             $prixAchat = $coef !== 0 ? $prixVente / $coef : 0;
    //         }
    //     }

    //     return [
    //         'prixAchat' => $prixAchat,
    //         'prixVente' => $prixVente,
    //         'coef' => $coef,
    //     ];
    // }

}
