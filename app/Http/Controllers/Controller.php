<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use App\Models\CommandeProduit;
use Illuminate\Support\Facades\Schema;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    public function sendApiResponse($datas, $message = '', $code = 200)
    {
    	$response = [
            'success' => true,
            'data'   => $datas,
            'message' => $message,
        ];

        return response()->json($response, $code);
    }

    public function sendApiErrors($errors, $code = 200)
    {
    	$response = [
            'success' => false,
            'errors'  => $errors,
        ];

        return response()->json($response, $code);
    }

    public function AsdecodefilterBy ($table, $req)
    {
        // Récupérer les paramètres de l'URL via $_GET
        $searchKeyword = (isset($_GET['search_by_keyword']) && $_GET['search_by_keyword'] != '') ? $_GET['search_by_keyword'] : null;
        $columns = (isset($_GET['columns']) && $_GET['columns'] != '') ? Str::of($_GET['columns'])->explode(',') : [];
        $sortBy = (isset($_GET['sort_key']) && $_GET['sort_key'] != '') ? $_GET['sort_key'] : null;
        $sortDirection = (isset($_GET['sort_order']) && $_GET['sort_order'] != '') ? $_GET['sort_order'] : 'ASC'; // 'asc' par défaut
        $periodFrom = (isset($_GET['period_from']) && $_GET['period_from'] != '') ? date('Y-m-d', strtotime($_GET['period_from'])). ' 00:00:00' : null;
        $periodTo = (isset($_GET['period_to']) && $_GET['period_to'] != '') ? date('Y-m-d', strtotime($_GET['period_to'])). ' 23:59:59' : null;
        $perPage = (isset($_GET['rows']) && $_GET['rows'] != '') ? $_GET['rows'] : null;
        $currentPage = (isset($_GET['page']) && $_GET['page'] != '') ? $_GET['page'] : null;

        //si vous precisez la periode, le debut et / ou la fin
        if ($periodFrom && $periodTo) {
            $req = $req->whereBetween('created_at', [$periodFrom, $periodTo]);
        }

        else if ($periodFrom ) {
            $req = $req->whereBetween('created_at', '>=', $periodFrom);
        }

        else if ($periodTo ) {
            $req = $req->whereBetween('created_at', '<=', $periodTo);
        }

        // si vous filtrez a partir d'une colonne specifique
        $req = $this->filterByColumn($table, $req);

        //si une recherche par mot cles est active
        if ($searchKeyword && $columns)
        {
            $search = $_GET['search_by_keyword'];

            $req->where(function ($q) use ($search,$columns,$table ) {
                foreach ($columns as $column ) {
                    if (Schema::hasColumn($table, $column)) $q->orWhere($column, 'like', "%$search%");
                }
            });
        }

        //si vous utilisez le trie par colonne
        if ($sortDirection && $sortBy) {
            $req = $req->orderBy($sortBy, $sortDirection);
        }
        
        // si la pagination est active dans l'url sinon renvoyer sans pagination
        if ($perPage && $currentPage){
            return $req->paginate($perPage);
        } else return $req->get();

    }

    public function filterByColumn ($table, $req)
    {
        foreach ($_GET as $key => $value) {
            if (($value != null) && (Schema::hasColumn($table, $key)) )
            {
                $valArray = [];
                $not_equal = null;
                $valArray = explode('/', str_replace(['[', ']'], '', $value));

                if (strpos($value, '!') === 0) {
                    $not_equal = str_replace(['!', '[', ']'], '', $value);
                }

                else if (!empty($valArray)) {
                    $req->whereIn($key, $valArray);
                }

                else if ($not_equal) {
                    $req->where($key, '!=', $not_equal);
                }

                else $req = $req->where($key, $value);
            }
        }

        return  $req;
    }


    public function verifierCoherence(CommandeProduit $commandeProduit): bool
    {
        $epsilon = 0;

        $prixVente = floatval($commandeProduit->prix_de_vente);
        $prixAchat = floatval($commandeProduit->prix_achat);
        $coef = floatval($commandeProduit->coef_conversion_de_prix_vente_achat);

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
}
