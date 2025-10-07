<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Vente;
use App\Models\Commande;
use Carbon\CarbonPeriod;
use App\Models\Inventaire;
use Illuminate\Http\Request;
use App\Models\CommandeProduit;
use App\Models\InventaireProduit;
use App\Models\ReservationProduit;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Resources\SalesHistoryResource;
use App\Http\Resources\PurchaseHistoryResource;
// use App\Http\Controllers\StatistiqueController;

class StatistiqueController extends Controller
{
    public function getLatestInventaireProduits()
    {
        // On part d'une requête sur Inventaire
        $query = Inventaire::where('statut', 'Terminé');
        // Application du filtre de période sur la date de création de l'inventaire (si des paramètres sont passés)
        $query = $this->periodFilter($query);
        // Récupérer le dernier inventaire selon le critère (par défaut, created_at)
        $dernierInventaire = $query->latest()->first();

        if (!$dernierInventaire) {
            return response()->json([
                'message' => 'Aucun inventaire trouvé.',
                'data' => []
            ], 200);
        }

        // On peut également appliquer le filtre sur les produits de l'inventaire si nécessaire
        $produitsQuery = $dernierInventaire->inventaire_produits()->select('produit_libelle', 'qte_reelle');
        // Ici, on applique periodFilter si vous souhaitez filtrer les produits de l'inventaire par leur date de création
        $produits = $this->periodFilter($produitsQuery)->get();

        return response()->json([
            'message' => 'Produits du dernier inventaire récupérés avec succès.',
            'data' => $produits
        ]);
    }

    public function getLatestInventaireData()
    {
        // Filtrer le dernier inventaire selon une période (optionnel)
        $query = Inventaire::query();
        $query = $this->periodFilter($query);
        $dernierInventaire = $query->latest('created_at')->first();

        if (!$dernierInventaire) {
            return response()->json(['message' => 'Aucun inventaire trouvé.'], 200);
        }

        $produitsQuery = InventaireProduit::where('inventaire_id', $dernierInventaire->id)
            ->select('produit_libelle', 'ecart');
        // Application du filtre sur InventaireProduit si nécessaire
        $produits = $this->periodFilter($produitsQuery)->get();

        return response()->json($produits);
    }

    public function getProduitsAchetes()
    {
        $produitsAchetes = CommandeProduit::whereHas('commande', function ($query) {
            $query->where('status', 'SUCCESS');
        })
        ->select('produit_libelle', \DB::raw('SUM(qte) as total_qte'))
        ->groupBy('produit_libelle');
         // Modification : Application du filtre periodFilter sur la requête
         $produitsAchetes = $this->periodFilter($produitsAchetes);

         $produitsAchetes = $produitsAchetes->get();

        return response()->json($produitsAchetes);
    }
    public function getMargeBenefice()
    {
        // Récupérer les commandeProduits liés à des commandes avec statut 'SUCCES'
        $commandeProduits = CommandeProduit::whereHas('commande', function ($query) {
            $query->where('status', 'SUCCESS');
        });
        // Modification : Application du filtre periodFilter sur la requête
        $commandeProduits = $this->periodFilter($commandeProduits);

        $commandeProduits = $commandeProduits->get();

        // Calculer la marge pour chaque produit
        $data = $commandeProduits->map(function ($item) {
            return [
                'produit_libelle' => $item->produit_libelle,
                'marge' => ($item->prix_de_vente - $item->prix_achat) * $item->qte,
            ];
        });

        return response()->json($data);
    }

   /**
     * Retourne le chiffre d'affaires avec une granularité dynamique :
     * - Si l'intervalle couvre un jour, regroupe par heure (00:00 à 23:00)
     * - Si l'intervalle est inférieur à 31 jours, regroupe par jour
     * - Sinon, regroupe par mois
     */
    public function getChiffreAffaireMensuel(Request $request)
    {
        // Récupération des paramètres de période depuis la requête (GET)
        $periodFromInput = $request->input('period_from');
        $periodToInput   = $request->input('period_to');

        // Définition des dates de début et fin
        if ($periodFromInput && $periodToInput) {
            $start = Carbon::parse($periodFromInput . ' 00:00:00');
            $end   = Carbon::parse($periodToInput . ' 23:59:59');
        } else {
            // Par défaut : l'année en cours
            $start = Carbon::now()->startOfYear();
            $end   = Carbon::now()->endOfYear();
        }

        // Calcul de la différence en jours
        $diffInDays = $start->diffInDays($end);

        // Détermination de labelName selon l'intervalle de temps
        if ($diffInDays < 6) {
            $labelName = 'journalier';
        } elseif ($diffInDays < 28) {
            $labelName = 'hebdomadaire';
        } elseif ($diffInDays < 365) {
            $labelName = 'mensuel';
        } else {
            // Période annuelle
            if ($start->year == $end->year) {
                $labelName = 'annuel';
            } else {
                $labelName = 'annuel / ' . $start->year . '-' . $end->year;
            }
        }

        /*
        * Détermination de la granularité du regroupement et création d'une "timeline"
        * selon la période :
        * - 0 jour       : groupe par heure (00:00, 01:00, …, 23:00)
        * - < 31 jours   : groupe par jour (YYYY-MM-DD)
        * - >= 31 jours  : groupe par mois
        *    • Si la période est dans une seule année, la timeline utilisera les abréviations (Jan, Feb, …)
        *    • Sinon, la timeline sera générée mois par mois au format "M-Y" (ex. Feb-2025, Mar-2025, …)
        */
        if ($diffInDays === 0) {
            // Regroupement par heure
            $groupBy = DB::raw("DATE_FORMAT(created_at, '%H:00') as period");

            // Générer la timeline pour 24 heures
            $timeline = [];
            for ($h = 0; $h < 24; $h++) {
                $timeline[sprintf("%02d:00", $h)] = 0;
            }
            $xLabel = 'Heure';
        } elseif ($diffInDays < 31) {
            // Regroupement par jour
            $groupBy = DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d') as period");

            // Générer la timeline du premier au dernier jour de l'intervalle
            $timeline = [];
            $period = CarbonPeriod::create($start, $end);
            foreach ($period as $date) {
                $timeline[$date->format('Y-m-d')] = 0;
            }
            $xLabel = 'Date';
        } else {
            // Regroupement par mois
            if ($start->year == $end->year) {
                // Période dans une seule année
                $groupBy = DB::raw("MONTH(created_at) as period");
                $months = [
                    1  => 'Jan',
                    2  => 'Feb',
                    3  => 'Mar',
                    4  => 'Apr',
                    5  => 'May',
                    6  => 'Jun',
                    7  => 'Jul',
                    8  => 'Aug',
                    9  => 'Sep',
                    10 => 'Oct',
                    11 => 'Nov',
                    12 => 'Dec',
                ];
                $timeline = [];
                foreach ($months as $m => $monthName) {
                    $timeline[$monthName] = 0;
                }
                $xLabel = 'Mois';
            } else {
                // Période s'étendant sur plusieurs années : on regroupe par année-mois
                $groupBy = DB::raw("DATE_FORMAT(created_at, '%Y-%m') as period");
                $startTimeline = $start->copy()->startOfMonth();
                $endTimeline = $end->copy()->startOfMonth();
                $periodObj = CarbonPeriod::create($startTimeline, '1 month', $endTimeline);
                $timeline = [];
                foreach ($periodObj as $dt) {
                    $timeline[$dt->format('M-Y')] = 0;
                }
                $xLabel = 'Mois';
            }
        }

        // Construction de la requête de récupération des ventes
        $query = ReservationProduit::select(
                $groupBy,
                DB::raw('SUM(qte * prix_de_vente) as total')
            )
            ->where('is_sold', true)
            ->whereBetween('created_at', [$start, $end])
            ->groupBy('period')
            ->orderBy('period', 'asc');

        $result = $query->get();

        // Fusionner les résultats dans la timeline (les périodes sans vente resteront à 0)
        foreach ($result as $row) {
            if ($diffInDays >= 31) {
                if ($start->year == $end->year) {
                    $months = [
                        1  => 'Jan',
                        2  => 'Feb',
                        3  => 'Mar',
                        4  => 'Apr',
                        5  => 'May',
                        6  => 'Jun',
                        7  => 'Jul',
                        8  => 'Aug',
                        9  => 'Sep',
                        10 => 'Oct',
                        11 => 'Nov',
                        12 => 'Dec',
                    ];
                    $monthName = isset($months[$row->period]) ? $months[$row->period] : $row->period;
                    $timeline[$monthName] = $row->total;
                } else {
                    // Multi-années : $row->period est au format "YYYY-MM", on le reformate en "M-Y"
                    $dateObj = Carbon::createFromFormat('Y-m', $row->period);
                    $formatted = $dateObj->format('M-Y');
                    $timeline[$formatted] = $row->total;
                }
            } else {
                $timeline[$row->period] = $row->total;
            }
        }

        // Formatage de la réponse (tableau d'objets { period, total })
        $responseData = [];
        foreach ($timeline as $key => $value) {
            $responseData[] = [
                'period' => $key,
                'total'  => $value
            ];
        }

        return response()->json([
            'xLabel'    => $xLabel,  // Pour adapter le libellé de l'axe X côté front
            'data'      => $responseData,
            'labelName' => $labelName // Valeur de la période (annuel, mensuel, hebdomadaire ou journalier)
        ]);
    }




    public function getTop20ProduitsVendus()
    {
        // Requête pour récupérer les 20 produits les plus vendus
        $topProduits = ReservationProduit::select(
                'libelle', // Le nom du produit
                DB::raw('SUM(qte) as total_vendu') // Somme des quantités vendues
            )
            ->where('is_sold', true) // Ne prendre en compte que les produits vendus
            ->groupBy('libelle') // Grouper par libelle (nom du produit)
            ->orderBy('total_vendu', 'desc'); // Trier par quantité vendue (du plus au moins)


            $topProduits = $this->periodFilter($topProduits);


        return response()->json($topProduits->get());
    }

    public function getTop20ProduitsMoinsVendus()
    {
        // Requête pour récupérer les 20 produits les plus vendus
        $topProduits = ReservationProduit::select(
                'libelle', // Le nom du produit
                DB::raw('SUM(qte) as total_vendu') // Somme des quantités vendues
            )
            ->where('is_sold', true) // Ne prendre en compte que les produits vendus
            ->groupBy('libelle') // Grouper par libelle (nom du produit)
            ->orderBy('total_vendu', 'asc'); // Trier par quantité vendue (du plus au moins)

            $topProduits = $this->periodFilter($topProduits);

        return response()->json($topProduits->get());
    }

    public function getSoldProductsForLastMonth()
    {
        $dateLimite = Carbon::now()->subDays(30);

        $produitsQuery = ReservationProduit::where('is_sold', true)
            ->where('created_at', '>=', $dateLimite)
            ->select('libelle', 'prix_de_vente', DB::raw('SUM(qte) as total_qte'))
            ->groupBy('libelle', 'prix_de_vente');

        // Application optionnelle du filtre periodFilter (s'il y a des paramètres GET)
        $produitsQuery = $this->periodFilter($produitsQuery);
        $produits = $produitsQuery->get();

        if ($produits->isEmpty()) {
            return response()->json([
                'message' => 'Aucune donnée trouvée pour les produits vendus sur les 30 derniers jours.'
            ], 404);
        }

        return response()->json($produits);
    }

    public function getVentesAnnulees()
    {
        $ventesAnnulees = Vente::where('statut', 'Annulé')
            ->select(DB::raw('COUNT(*) as total_annule'));

        // Modification : Application du filtre periodFilter sur la requête
        $ventesAnnulees = $this->periodFilter($ventesAnnulees);

        $ventesAnnulees = $ventesAnnulees->first();

        return response()->json($ventesAnnulees);
    }
    public function getChiffreAffaireUtilisateur()
    {
        // Grouper par utilisateur et calculer le total des ventes
        $ventes = Vente::select('user', \DB::raw('SUM(total) as total_ventes'))
            ->where('statut', 'soldé') // Ignorer les ventes annulées
            ->groupBy('user');

            $ventes = $this->periodFilter($ventes);

            $ventes = $ventes->get();

        return response()->json($ventes);
    }

    public function panierMoyenParUtilisateur()
    {
        $resultats = DB::table('ventes')
            ->select('user', DB::raw('SUM(total) as total_ventes'), DB::raw('COUNT(id) as nombre_ventes'))
            ->where('statut', 'soldé')
            ->groupBy('user');


        // Modification : Application du filtre periodFilter sur la requête
        $resultats = $this->periodFilter($resultats);

        $resultats = $resultats->get();

        $panierMoyen = $resultats->map(function ($resultat) {
            return [
                'user' => $resultat->user,
                'panier_moyen' => $resultat->nombre_ventes > 0 ? $resultat->total_ventes / $resultat->nombre_ventes : 0
            ];
        });

        return response()->json($panierMoyen);
    }

    public function clientServisUtilisateur()
    {
        $ventes = DB::table('ventes')
            ->select('user', DB::raw('COUNT(*) as nombre_ventes'))
            ->where('statut', 'soldé')
            ->groupBy('user');

        $ventes = $this->periodFilter($ventes);

        $ventes = $ventes->get();

        return response()->json($ventes);
    }
    /**
     * Récupère les statistiques de vente d'un produit sur les 15 derniers jours
     * et calcule l'évolution par rapport aux 15 jours précédents.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $productId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProductStats(Request $request, $productId)
    {
        // Vérification de l'identifiant du produit
        if (!$productId) {
            return response()->json([
                'message' => "L'identifiant du produit (produit_id) est requis."
            ], 422);
        }

        // DEBUT MODIFICATION : Utilisation des filtres si fournis, sinon valeurs par défaut sur les 15 derniers jours
        if ($request->has('period_from') && $request->has('period_to')) {
            // Période actuelle définie par le filtre
            $actuelDebut = Carbon::parse($request->input('period_from') . ' 00:00:00');
            $actuelFin   = Carbon::parse($request->input('period_to') . ' 23:59:59');

            // Calcul de la durée de la période actuelle en jours
            $currentPeriodLength = $actuelDebut->diffInDays($actuelFin) + 1;

            // Définition de la période précédente : même durée immédiatement avant la période actuelle
            $precedentDebut = (clone $actuelDebut)->subDays($currentPeriodLength);
            $precedentFin   = (clone $actuelDebut)->subDay();
        } else {
            // Valeurs par défaut : les 15 derniers jours (de now()->subDays(14) à now())
            $actuelDebut = Carbon::now()->subDays(14)->startOfDay();
            $actuelFin   = Carbon::now()->endOfDay();
            // Période précédente : 15 jours précédents (de now()->subDays(29) à now()->subDays(15))
            $precedentDebut = Carbon::now()->subDays(29)->startOfDay();
            $precedentFin   = Carbon::now()->subDays(15)->endOfDay();
        }
        // FIN MODIFICATION

        // Récupération des statistiques pour la période actuelle
        $statsActuelles = ReservationProduit::where('is_sold', true)
            ->where('produit_id', $productId)
            ->whereBetween('created_at', [$actuelDebut, $actuelFin])
            ->select(
                DB::raw('COALESCE(SUM(qte * prix_de_vente), 0) as chiffre_affaire'),
                DB::raw('COALESCE(SUM(qte), 0) as quantite')
            )
            ->first();

        // Récupération des statistiques pour la période précédente
        $statsPrecedentes = ReservationProduit::where('is_sold', true)
            ->where('produit_id', $productId)
            ->whereBetween('created_at', [$precedentDebut, $precedentFin])
            ->select(
                DB::raw('COALESCE(SUM(qte * prix_de_vente), 0) as chiffre_affaire'),
                DB::raw('COALESCE(SUM(qte), 0) as quantite')
            )
            ->first();

        // Calcul des écarts
        $ecartChiffreAffaire = $statsActuelles->chiffre_affaire - $statsPrecedentes->chiffre_affaire;
        $ecartQuantite       = $statsActuelles->quantite - $statsPrecedentes->quantite;

        // Calcul des évolutions en pourcentage
        $pourcentageChiffreAffaire = $statsPrecedentes->chiffre_affaire > 0
            ? ($ecartChiffreAffaire / $statsPrecedentes->chiffre_affaire) * 100
            : null;
        $pourcentageQuantite = $statsPrecedentes->quantite > 0
            ? ($ecartQuantite / $statsPrecedentes->quantite) * 100
            : null;

        // Préparation de la réponse (j'ai ajouté les périodes pour vérification côté front)
        $responseData = [
            'actuel' => [
                'chiffre_affaire' => $statsActuelles->chiffre_affaire,
                'quantite_vendue' => $statsActuelles->quantite,
                'period' => [
                    'start' => $actuelDebut->toDateString(),
                    'end'   => $actuelFin->toDateString(),
                ],
            ],
            'precedent' => [
                'chiffre_affaire' => $statsPrecedentes->chiffre_affaire,
                'quantite_vendue' => $statsPrecedentes->quantite,
                'period' => [
                    'start' => $precedentDebut->toDateString(),
                    'end'   => $precedentFin->toDateString(),
                ],
            ],
            'evolution' => [
                'ecart_chiffre_affaire' => $ecartChiffreAffaire,
                'pourcentage_chiffre_affaire' => $pourcentageChiffreAffaire,
                'ecart_quantite_vendue' => $ecartQuantite,
                'pourcentage_quantite_vendue' => $pourcentageQuantite,
            ],
        ];

        return response()->json([
            'message' => 'Statistiques de productivité des ventes récupérées avec succès.',
            'data'    => $responseData,
        ]);
    }


    public function getSalesProductivity(Request $request, $product_id)
    {
        // On utilise le paramètre de route pour l'identifiant du produit
        $productId = $product_id;

        // Vérification de l'identifiant (normalement toujours défini via la route)
        if (!$productId) {
            return response()->json([
                'message' => "L'identifiant du produit (produit_id) est requis."
            ], 422);
        }

        // Récupération des paramètres de période depuis la requête
        // Si non fournis, on prend par défaut la semaine dernière (7 jours)
        $periodFromInput = $request->input('period_from');
        $periodToInput   = $request->input('period_to');

        if ($periodFromInput && $periodToInput) {
            $start = Carbon::parse($periodFromInput . ' 00:00:00');
            $end   = Carbon::parse($periodToInput . ' 23:59:59');
        } else {
            // Par défaut : les 7 derniers jours (7 jours inclus, soit de now()->subDays(6) à now())
            $start = Carbon::now()->subDays(6)->startOfDay();
            $end   = Carbon::now()->endOfDay();
        }

        // Calcul du nombre de jours dans la période pour déterminer la granularité du regroupement
        $diffInDays = $start->diffInDays($end);

        /*
        * Détermination de la granularité du regroupement :
        * - 0 jour   : regroupement par heure
        * - < 31 jours : regroupement par jour
        * - >= 31 jours : regroupement par mois
        */
        if ($diffInDays === 0) {
            // Regroupement par heure pour une journée unique
            $groupBy = DB::raw("DATE_FORMAT(reservation_produits.created_at, '%H:00') as period");
            $timeline = [];
            for ($h = 0; $h < 24; $h++) {
                $key = sprintf("%02d:00", $h);
                $timeline[$key] = [
                    'period'      => $key,
                    'sales_count' => 0,
                    'revenue'     => 0,
                ];
            }
            $xLabel = 'Heure';
        } elseif ($diffInDays < 31) {
            // Regroupement par jour
            $groupBy = DB::raw("DATE_FORMAT(reservation_produits.created_at, '%Y-%m-%d') as period");
            $timeline = [];
            // Création d'une timeline pour chaque jour de la période
            $periodRange = CarbonPeriod::create($start, $end);
            foreach ($periodRange as $date) {
                $key = $date->format('Y-m-d');
                $timeline[$key] = [
                    'period'      => $key,
                    'day'         => $date->translatedFormat('D'), // ex : Lun, Mar, etc.
                    'sales_count' => 0,
                    'revenue'     => 0,
                ];
            }
            $xLabel = 'Date';
        } else {
            // DEBUT MODIFICATION POUR REGROUPER PAR MOIS AVEC FORMATAGE APPROPRIÉ
            // On regroupe par mois en utilisant DATE_FORMAT avec '%Y-%m'
            $groupBy = DB::raw("DATE_FORMAT(reservation_produits.created_at, '%Y-%m') as period");
            $timeline = [];
            // Pour couvrir l'intégralité des mois, on ajuste le début et la fin
            $startMonth = $start->copy()->startOfMonth();
            $endMonth = $end->copy()->endOfMonth();
            $periodRange = CarbonPeriod::create($startMonth, '1 month', $endMonth);
            foreach ($periodRange as $date) {
                $key = $date->format('Y-m');
                $timeline[$key] = [
                    // Affiche par exemple "Fév 2025" en utilisant le format traduit
                    'period'      => $date->translatedFormat('M Y'),
                    'sales_count' => 0,
                    'revenue'     => 0,
                ];
            }
            $xLabel = 'Mois';
            // FIN MODIFICATION POUR REGROUPER PAR MOIS AVEC FORMATAGE APPROPRIÉ
        }

        // Construction de la requête pour récupérer les statistiques de ventes
        $sales = \App\Models\ReservationProduit::select(
                        $groupBy,
                        DB::raw("COALESCE(SUM(reservation_produits.qte), 0) as sales_count"),
                        DB::raw("COALESCE(SUM(reservation_produits.qte * reservation_produits.prix_de_vente), 0) as revenue")
                    )
                    ->where('is_sold', true)
                    ->where('produit_id', $productId)
                    ->whereBetween('created_at', [$start, $end])
                    ->groupBy('period')
                    ->orderBy('period', 'asc')
                    ->get();

        // Fusion des résultats dans la timeline (les intervalles sans ventes restent à 0)
        foreach ($sales as $row) {
            $key = $row->period;
            if (isset($timeline[$key])) {
                $timeline[$key]['sales_count'] = (int) $row->sales_count;
                $timeline[$key]['revenue']     = (float) $row->revenue;
            }
        }

        // Calcul des totaux sur la période
        $totalRevenue = array_sum(array_column($timeline, 'revenue'));
        $totalSales   = array_sum(array_column($timeline, 'sales_count'));
        $numberOfIntervals = count($timeline);

        // Calcul des moyennes par intervalle
        $averageRevenue = $numberOfIntervals > 0 ? $totalRevenue / $numberOfIntervals : 0;
        $averageSales   = $numberOfIntervals > 0 ? $totalSales / $numberOfIntervals : 0;

        // Préparation de la réponse
        $responseData = [
            'xLabel'   => $xLabel,
            'timeline' => array_values($timeline),
            'averages' => [
                'average_revenue'     => $averageRevenue,
                'average_sales_count' => $averageSales,
            ]
        ];

        return response()->json([
            'message' => 'Statistiques de productivité des ventes récupérées avec succès.',
            'data'    => $responseData,
        ]);
    }



    public function getSalesHistory(Request $request, $productId)
    {
        // Vérification de l'identifiant du produit
        if (!$productId) {
            return response()->json([
                'message' => "L'identifiant du produit est requis."
            ], 422);
        }

        // Construction de la requête en sélectionnant explicitement les colonnes souhaitées
        $query = \DB::table('reservation_produits')
            ->join('ventes', 'reservation_produits.reservation_id', '=', 'ventes.reservation_id')
            ->where('reservation_produits.produit_id', $productId)
            ->select([
                'ventes.date_reservation as created_at', // Unique colonne pour le filtrage
                'ventes.date_reservation as sale_date',
                'ventes.client as client_name',
                'reservation_produits.qte as quantity_sold',
                \DB::raw('(reservation_produits.qte * reservation_produits.prix_de_vente) as total_amount'),
                'ventes.statut as sale_status'
            ])
            ->orderBy('ventes.date_reservation', 'desc');

        // Application du filtre de période (la fonction periodFilter applique whereBetween sur "created_at")
        $query = $this->periodFilter($query, 'ventes.date_reservation');



        if (isset($_GET['page']) && isset($_GET['rows'])){
            return SalesHistoryResource::collection($query->paginate($_GET['rows']));
        }

        return SalesHistoryResource::collection($query->get());
    }




   /**
     * Récupère l'historique des achats d'un produit.
     *
     * Colonnes renvoyées :
     * - purchase_date         : Date d'achat (commandes.created_at)
     * - supplier_name         : Nom du fournisseur (commandes.fournisseur_libelle)
     * - quantity_purchased    : Quantité achetée (commande_produit.qte)
     * - total_purchase_amount : Montant total d'achat (commande_produit.total_achat)
     * - purchase_status       : Statut de la commande (commandes.status)
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $productId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPurchaseHistory(Request $request, $productId)
    {
        // Vérification de l'identifiant du produit
        if (!$productId) {
            return response()->json([
                'message' => "L'identifiant du produit est requis."
            ], 422);
        }

        // Construction de la requête de base avec la jointure et sélection explicite
        $query = DB::table('commande_produit')
            ->join('commandes', 'commande_produit.commande_id', '=', 'commandes.id')
            ->where('commande_produit.produit_id', $productId)
            ->select([
                'commandes.created_at as created_at', // Unique colonne pour le filtrage
                'commandes.created_at as purchase_date',
                'commandes.fournisseur_libelle as supplier_name',
                'commande_produit.qte as quantity_purchased',
                DB::raw('commande_produit.total_achat as total_purchase_amount'),
                'commandes.status as purchase_status'
            ])
            ->orderBy('commandes.created_at', 'desc');

        // Application du filtre de période via periodFilter (qui s'applique sur "created_at")
        $query = $this->periodFilter($query, 'commandes.created_at');

        if (isset($_GET['page']) && isset($_GET['rows'])){
            return PurchaseHistoryResource::collection($query->paginate($_GET['rows']));
        }

        return PurchaseHistoryResource::collection($query->get());
    }






    public function getVentesSemaine(Request $request)
    {
        $query = Vente::query();

        // Appliquer le filtre de période si défini
        $query = $this->periodFilter($query, 'created_at');

        // Gestion des dates
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();

        // Vérifier si period_from et period_to existent ET ne sont pas vides
        if ($request->has('period_from') && !empty($request->period_from)) {
            $startOfWeek = Carbon::parse($request->period_from)->startOfDay();
        }

        if ($request->has('period_to') && !empty($request->period_to)) {
            $endOfWeek = Carbon::parse($request->period_to)->endOfDay();
        }

        // Appliquer le filtre de la période
        $query->whereBetween('created_at', [$startOfWeek, $endOfWeek]);

        // Exclure les ventes annulées
        $query->where('isannule', '!=', 1);

        // Charger toutes les informations de chaque vente + les relations nécessaires
        $ventes = $query->get();

        return response()->json([
            'data' => $ventes,
            'period' => [
                'from' => $startOfWeek->toDateTimeString(),
                'to'   => $endOfWeek->toDateTimeString()
            ]
        ]);
    }






    public function periodFilter($req, $col = 'created_at')
    {
        $periodFrom = (isset($_GET['period_from']) && $_GET['period_from'] != '') ? date('Y-m-d', strtotime($_GET['period_from'])). ' 00:00:00' : null;
        $periodTo = (isset($_GET['period_to']) && $_GET['period_to'] != '') ? date('Y-m-d', strtotime($_GET['period_to'])). ' 23:59:59' : null;
        $limit = (isset($_GET['top_limit']) && $_GET['top_limit'] != '') ? $_GET['top_limit'] : null;

        if ($periodFrom && $periodTo) {
            $req = $req->whereBetween($col, [$periodFrom, $periodTo]);
        }

        else if ($periodFrom ) {
            $req = $req->whereBetween($col, '>=', $periodFrom);
        }

        else if ($periodTo ) {
            $req = $req->whereBetween($col, '<=', $periodTo);
        }

        if ($limit) {
            $req = $req->limit($limit);
        }

        return $req;
    }

    /**
     * Analyse des ventes - Évolution des ventes
     * Comparaison des ventes avec le mois précédent et le même mois l'année dernière
     */
    public function getEvolutionVentes(Request $request)
    {
        // Période actuelle (par défaut : mois en cours)
        $periodFromInput = $request->input('period_from');
        $periodToInput = $request->input('period_to');

        if ($periodFromInput && $periodToInput) {
            $actuelDebut = Carbon::parse($periodFromInput . ' 00:00:00');
            $actuelFin = Carbon::parse($periodToInput . ' 23:59:59');
        } else {
            $actuelDebut = Carbon::now()->startOfMonth();
            $actuelFin = Carbon::now()->endOfMonth();
        }

        // Calcul de la durée de la période actuelle
        $periodLength = $actuelDebut->diffInDays($actuelFin) + 1;

        // Période précédente (même durée juste avant)
        $precedentDebut = (clone $actuelDebut)->subDays($periodLength);
        $precedentFin = (clone $actuelDebut)->subDay();

        // Même période l'année dernière
        $anneePrecedenteDebut = (clone $actuelDebut)->subYear();
        $anneePrecedenteFin = (clone $actuelFin)->subYear();

        // Requête pour la période actuelle
        $ventesActuelles = ReservationProduit::where('is_sold', true)
            ->whereBetween('created_at', [$actuelDebut, $actuelFin])
            ->select(
                DB::raw('COALESCE(SUM(qte * prix_de_vente), 0) as chiffre_affaire'),
                DB::raw('COALESCE(SUM(qte), 0) as quantite'),
                DB::raw('COALESCE(COUNT(DISTINCT reservation_id), 0) as nombre_ventes')
            )
            ->first();

        // Requête pour la période précédente
        $ventesPrecedentes = ReservationProduit::where('is_sold', true)
            ->whereBetween('created_at', [$precedentDebut, $precedentFin])
            ->select(
                DB::raw('COALESCE(SUM(qte * prix_de_vente), 0) as chiffre_affaire'),
                DB::raw('COALESCE(SUM(qte), 0) as quantite'),
                DB::raw('COALESCE(COUNT(DISTINCT reservation_id), 0) as nombre_ventes')
            )
            ->first();

        // Requête pour la même période l'année dernière
        $ventesAnneePrecedente = ReservationProduit::where('is_sold', true)
            ->whereBetween('created_at', [$anneePrecedenteDebut, $anneePrecedenteFin])
            ->select(
                DB::raw('COALESCE(SUM(qte * prix_de_vente), 0) as chiffre_affaire'),
                DB::raw('COALESCE(SUM(qte), 0) as quantite'),
                DB::raw('COALESCE(COUNT(DISTINCT reservation_id), 0) as nombre_ventes')
            )
            ->first();

        // Calcul des évolutions
        $evolutionPeriodePrecedente = [
            'chiffre_affaire' => $ventesPrecedentes->chiffre_affaire > 0
                ? (($ventesActuelles->chiffre_affaire - $ventesPrecedentes->chiffre_affaire) / $ventesPrecedentes->chiffre_affaire) * 100
                : null,
            'quantite' => $ventesPrecedentes->quantite > 0
                ? (($ventesActuelles->quantite - $ventesPrecedentes->quantite) / $ventesPrecedentes->quantite) * 100
                : null,
            'nombre_ventes' => $ventesPrecedentes->nombre_ventes > 0
                ? (($ventesActuelles->nombre_ventes - $ventesPrecedentes->nombre_ventes) / $ventesPrecedentes->nombre_ventes) * 100
                : null,
        ];

        $evolutionAnneePrecedente = [
            'chiffre_affaire' => $ventesAnneePrecedente->chiffre_affaire > 0
                ? (($ventesActuelles->chiffre_affaire - $ventesAnneePrecedente->chiffre_affaire) / $ventesAnneePrecedente->chiffre_affaire) * 100
                : null,
            'quantite' => $ventesAnneePrecedente->quantite > 0
                ? (($ventesActuelles->quantite - $ventesAnneePrecedente->quantite) / $ventesAnneePrecedente->quantite) * 100
                : null,
            'nombre_ventes' => $ventesAnneePrecedente->nombre_ventes > 0
                ? (($ventesActuelles->nombre_ventes - $ventesAnneePrecedente->nombre_ventes) / $ventesAnneePrecedente->nombre_ventes) * 100
                : null,
        ];

        return response()->json([
            'message' => 'Évolution des ventes récupérée avec succès.',
            'data' => [
                'periode_actuelle' => [
                    'chiffre_affaire' => $ventesActuelles->chiffre_affaire,
                    'quantite' => $ventesActuelles->quantite,
                    'nombre_ventes' => $ventesActuelles->nombre_ventes,
                    'periode' => [
                        'debut' => $actuelDebut->toDateString(),
                        'fin' => $actuelFin->toDateString(),
                    ]
                ],
                'periode_precedente' => [
                    'chiffre_affaire' => $ventesPrecedentes->chiffre_affaire,
                    'quantite' => $ventesPrecedentes->quantite,
                    'nombre_ventes' => $ventesPrecedentes->nombre_ventes,
                    'periode' => [
                        'debut' => $precedentDebut->toDateString(),
                        'fin' => $precedentFin->toDateString(),
                    ]
                ],
                'annee_precedente' => [
                    'chiffre_affaire' => $ventesAnneePrecedente->chiffre_affaire,
                    'quantite' => $ventesAnneePrecedente->quantite,
                    'nombre_ventes' => $ventesAnneePrecedente->nombre_ventes,
                    'periode' => [
                        'debut' => $anneePrecedenteDebut->toDateString(),
                        'fin' => $anneePrecedenteFin->toDateString(),
                    ]
                ],
                'evolution_periode_precedente' => $evolutionPeriodePrecedente,
                'evolution_annee_precedente' => $evolutionAnneePrecedente,
            ]
        ]);
    }

    /**
     * Ventes par catégorie de produit
     * Répartition des ventes par type de produit
     */
    public function getVentesParCategorie()
    {
        $ventesParCategorie = ReservationProduit::join('produits', 'reservation_produits.produit_id', '=', 'produits.id')
            ->where('reservation_produits.is_sold', true)
            ->select(
                'produits.categorie',
                DB::raw('SUM(reservation_produits.qte) as quantite_vendue'),
                DB::raw('SUM(reservation_produits.qte * reservation_produits.prix_de_vente) as chiffre_affaire'),
                DB::raw('COUNT(DISTINCT reservation_produits.reservation_id) as nombre_ventes')
            )
            ->groupBy('produits.categorie');

        $ventesParCategorie = $this->periodFilter($ventesParCategorie);

        return response()->json([
            'message' => 'Ventes par catégorie récupérées avec succès.',
            'data' => $ventesParCategorie->get()
        ]);
    }

    /**
     * Valeur des stocks
     * Valeur totale des stocks à la fin du mois
     */
    public function getValeurStocks()
    {
        $valeurStocks = \App\Models\Produit::select(
                'categorie',
                'rayon',
                DB::raw('SUM(qte * prix_achat) as valeur_stock_achat'),
                DB::raw('SUM(qte * prix_de_vente) as valeur_stock_vente'),
                DB::raw('SUM(qte) as quantite_totale'),
                DB::raw('COUNT(*) as nombre_produits')
            )
            ->where('qte', '>', 0)
            ->where('is_active', 1)
            ->groupBy('categorie', 'rayon')
            ->get();

        $valeurTotale = [
            'valeur_totale_achat' => $valeurStocks->sum('valeur_stock_achat'),
            'valeur_totale_vente' => $valeurStocks->sum('valeur_stock_vente'),
            'quantite_totale' => $valeurStocks->sum('quantite_totale'),
            'nombre_produits_total' => $valeurStocks->sum('nombre_produits')
        ];

        return response()->json([
            'message' => 'Valeur des stocks récupérée avec succès.',
            'data' => [
                'detail_par_categorie' => $valeurStocks,
                'resume_global' => $valeurTotale
            ]
        ]);
    }

    /**
     * Produits obsolètes ou expirés
     * Identification des produits qui doivent être retirés du stock
     */
    public function getProduitsObsoletes()
    {
        $dateAujourdhui = Carbon::now();
        $dateDansTroisMois = Carbon::now()->addMonths(3);

        // Produits expirés ou bientôt expirés (basé sur CommandeProduit qui a date_expiration)
        $produitsExpires = DB::table('commande_produit')
            ->join('produits', 'commande_produit.produit_id', '=', 'produits.id')
            ->whereNotNull('commande_produit.date_expiration')
            ->where('commande_produit.date_expiration', '<=', $dateDansTroisMois)
            ->select(
                'produits.libelle',
                'produits.cip',
                'commande_produit.date_expiration',
                'commande_produit.lot',
                'commande_produit.qte',
                'produits.prix_de_vente',
                DB::raw('CASE
                    WHEN commande_produit.date_expiration <= "' . $dateAujourdhui->toDateString() . '" THEN "Expiré"
                    WHEN commande_produit.date_expiration <= "' . Carbon::now()->addMonth()->toDateString() . '" THEN "Expire dans 1 mois"
                    ELSE "Expire dans 3 mois"
                END as statut_expiration'),
                DB::raw('(commande_produit.qte * produits.prix_de_vente) as valeur_risque')
            )
            ->orderBy('commande_produit.date_expiration', 'asc')
            ->get();

        // Produits avec stock critique (stock inférieur à qte_min)
        $produitsStockCritique = \App\Models\Produit::where('qte', '<=', DB::raw('qte_min'))
            ->where('qte_min', '>', 0)
            ->select(
                'libelle',
                'cip',
                'qte',
                'qte_min',
                'qte_max',
                'prix_de_vente',
                'categorie',
                'rayon'
            )
            ->get();

        return response()->json([
            'message' => 'Produits obsolètes identifiés avec succès.',
            'data' => [
                'produits_expires_ou_bientot_expires' => $produitsExpires,
                'produits_stock_critique' => $produitsStockCritique,
                'resume' => [
                    'nombre_produits_expires' => $produitsExpires->where('statut_expiration', 'Expiré')->count(),
                    'nombre_produits_bientot_expires' => $produitsExpires->where('statut_expiration', '!=', 'Expiré')->count(),
                    'nombre_produits_stock_critique' => $produitsStockCritique->count(),
                    'valeur_totale_risque_expiration' => $produitsExpires->sum('valeur_risque')
                ]
            ]
        ]);
    }

    /**
     * Marge brute mensuelle
     * Total des marges réalisées sur les ventes
     */
    public function getMargeBruteMensuelle()
    {
        $margesBrutes = ReservationProduit::where('is_sold', true)
            ->select(
                DB::raw('SUM(qte * (prix_de_vente - prix_achat)) as marge_brute_totale'),
                DB::raw('SUM(qte * prix_de_vente) as chiffre_affaire_total'),
                DB::raw('SUM(qte * prix_achat) as cout_total'),
                DB::raw('SUM(qte) as quantite_totale')
            );

        $margesBrutes = $this->periodFilter($margesBrutes);
        $resultat = $margesBrutes->first();

        $tauxMarge = $resultat->chiffre_affaire_total > 0
            ? ($resultat->marge_brute_totale / $resultat->chiffre_affaire_total) * 100
            : 0;

        return response()->json([
            'message' => 'Marge brute mensuelle récupérée avec succès.',
            'data' => [
                'marge_brute_totale' => $resultat->marge_brute_totale,
                'chiffre_affaire_total' => $resultat->chiffre_affaire_total,
                'cout_total' => $resultat->cout_total,
                'taux_marge_pourcentage' => $tauxMarge,
                'quantite_totale' => $resultat->quantite_totale
            ]
        ]);
    }

    /**
     * Marge nette
     * Résultat après déduction des dépenses mensuelles
     * Note: Cette méthode peut être étendue pour inclure d'autres dépenses
     */
    public function getMargeNette()
    {
        // Calcul de la marge brute
        $margesBrutes = ReservationProduit::where('is_sold', true)
            ->select(
                DB::raw('SUM(qte * (prix_de_vente - prix_achat)) as marge_brute_totale'),
                DB::raw('SUM(qte * prix_de_vente) as chiffre_affaire_total')
            );

        $margesBrutes = $this->periodFilter($margesBrutes);
        $resultatMargeBrute = $margesBrutes->first();

        // Ici vous pouvez ajouter d'autres dépenses (salaires, loyer, etc.)
        // Pour l'instant, nous considérons uniquement les coûts des produits
        $autresdepenses = 0; // À personnaliser selon vos besoins

        $margeNette = $resultatMargeBrute->marge_brute_totale - $autresdepenses;
        $tauxMargeNette = $resultatMargeBrute->chiffre_affaire_total > 0
            ? ($margeNette / $resultatMargeBrute->chiffre_affaire_total) * 100
            : 0;

        return response()->json([
            'message' => 'Marge nette récupérée avec succès.',
            'data' => [
                'marge_brute' => $resultatMargeBrute->marge_brute_totale,
                'autres_depenses' => $autresdepenses,
                'marge_nette' => $margeNette,
                'chiffre_affaire_total' => $resultatMargeBrute->chiffre_affaire_total,
                'taux_marge_nette_pourcentage' => $tauxMargeNette
            ]
        ]);
    }

    /**
     * Ventes par employé
     * Analyse des performances de chaque employé
     */
    public function getVentesParEmploye()
    {
        $ventesParEmploye = Vente::where('statut', 'soldé')
            ->select(
                'user as employe',
                DB::raw('COUNT(*) as nombre_ventes'),
                DB::raw('SUM(total) as chiffre_affaire_total'),
                DB::raw('AVG(total) as panier_moyen'),
                DB::raw('SUM(CASE WHEN isannule = 1 THEN 1 ELSE 0 END) as ventes_annulees')
            )
            ->groupBy('user');

        $ventesParEmploye = $this->periodFilter($ventesParEmploye);
        $resultats = $ventesParEmploye->get();

        // Calcul des totaux généraux
        $totaux = [
            'nombre_total_ventes' => $resultats->sum('nombre_ventes'),
            'chiffre_affaire_global' => $resultats->sum('chiffre_affaire_total'),
            'panier_moyen_global' => $resultats->avg('panier_moyen'),
            'total_ventes_annulees' => $resultats->sum('ventes_annulees')
        ];

        return response()->json([
            'message' => 'Performances des employés récupérées avec succès.',
            'data' => [
                'performances_individuelles' => $resultats,
                'totaux_generaux' => $totaux
            ]
        ]);
    }

    /**
     * Planification des réapprovisionnements
     * Liste des produits à commander en fonction des ventes et des niveaux de stock
     */
    public function getPlanificationReapprovisionnement()
    {
        // Produits nécessitant un réapprovisionnement (stock <= qte_min)
        $produitsAReapprovisionner = \App\Models\Produit::where('qte', '<=', DB::raw('qte_min'))
            ->where('qte_min', '>', 0)
            ->where('is_active', 1)
            ->select(
                'id',
                'libelle',
                'cip',
                'qte as stock_actuel',
                'qte_min as stock_minimum',
                'qte_max as stock_maximum',
                DB::raw('(qte_max - qte) as quantite_a_commander'),
                'prix_achat',
                DB::raw('((qte_max - qte) * prix_achat) as cout_reapprovisionnement'),
                'categorie',
                'rayon',
                'code_fournisseur'
            )
            ->get();

        // Calcul des ventes moyennes pour estimer les besoins
        $dateDebut = Carbon::now()->subDays(30);
        $ventesRecentes = ReservationProduit::where('is_sold', true)
            ->where('created_at', '>=', $dateDebut)
            ->select(
                'produit_id',
                DB::raw('SUM(qte) as quantite_vendue_30j'),
                DB::raw('AVG(qte) as vente_moyenne_par_transaction')
            )
            ->groupBy('produit_id')
            ->get()
            ->keyBy('produit_id');

        // Enrichir les données avec les informations de ventes
        $produitsAvecVentes = $produitsAReapprovisionner->map(function ($produit) use ($ventesRecentes) {
            $venteProduit = $ventesRecentes->get($produit->id);

            return [
                'produit' => $produit,
                'ventes_30_derniers_jours' => $venteProduit ? $venteProduit->quantite_vendue_30j : 0,
                'vente_moyenne_par_transaction' => $venteProduit ? $venteProduit->vente_moyenne_par_transaction : 0,
                'estimation_jours_stock' => $venteProduit && $venteProduit->quantite_vendue_30j > 0
                    ? ($produit->stock_actuel / ($venteProduit->quantite_vendue_30j / 30))
                    : null,
                'priorite' => $produit->stock_actuel == 0 ? 'Critique' :
                    ($produit->stock_actuel <= ($produit->stock_minimum * 0.5) ? 'Haute' : 'Normale')
            ];
        });

        // Calcul des totaux
        $coutTotalReapprovisionnement = $produitsAReapprovisionner->sum('cout_reapprovisionnement');
        $nombreProduitseCritiques = $produitsAvecVentes->where('priorite', 'Critique')->count();
        $nombreProduitsHautePriorite = $produitsAvecVentes->where('priorite', 'Haute')->count();

        return response()->json([
            'message' => 'Planification des réapprovisionnements générée avec succès.',
            'data' => [
                'produits_a_reapprovisionner' => $produitsAvecVentes,
                'resume' => [
                    'nombre_total_produits' => $produitsAReapprovisionner->count(),
                    'nombre_produits_critiques' => $nombreProduitseCritiques,
                    'nombre_produits_haute_priorite' => $nombreProduitsHautePriorite,
                    'cout_total_reapprovisionnement' => $coutTotalReapprovisionnement
                ]
            ]
        ]);
    }
}

