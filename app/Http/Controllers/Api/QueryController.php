<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Models\Produit;
use App\Models\Vente;
use App\Models\ReservationProduit;

class QueryController extends Controller
{
    public function handle(Request $request)
    {
        $validated = $request->validate([
            'kind' => 'required|string|in:stock,last_sale,price',
            'params' => 'nullable|array',
        ]);

        $kind = $validated['kind'];
        $params = $validated['params'] ?? [];

        switch ($kind) {
            case 'stock':
                return $this->stock($params);
            case 'last_sale':
                return $this->lastSale($params);
            case 'price':
                return $this->price($params);
            default:
                return response()->json(['data' => []]);
        }
    }

    protected function stock(array $params)
    {
        $name = Str::lower($params['name'] ?? '');
        if (!$name) return response()->json(['data' => []]);

        // Eloquent: produits(libelle, qte, updated_at)
        $row = Produit::query()
            ->select(['libelle', 'qte', 'updated_at'])
            ->whereRaw('LOWER(libelle) LIKE ?', ['%' . $name . '%'])
            ->orderByDesc('updated_at')
            ->first();

        $data = [];
        if ($row) {
            $data[] = [
                'nom' => $row->libelle,
                'quantite' => $row->qte,
                'date_mise_a_jour' => $row->updated_at,
            ];
        }

        return response()->json(['data' => $data]);
    }

    protected function lastSale(array $params)
    {
        $seller = Str::lower($params['seller'] ?? '');
        if (!$seller) return response()->json(['data' => []]);

        // Eloquent: trouver la dernière vente du vendeur puis son dernier produit vendu
        $vente = Vente::query()
            ->whereRaw('LOWER(user) LIKE ?', ['%' . $seller . '%'])
            ->orderByDesc('created_at')
            ->first();

        if (!$vente) return response()->json(['data' => []]);

        $rp = ReservationProduit::query()
            ->where('vente_id', $vente->id)
            ->orderByDesc('id')
            ->first();

        if (!$rp) return response()->json(['data' => []]);

        $dateVente = $vente->date_reservation ?: $vente->created_at;
        $data = [[
            'date_vente' => $dateVente,
            'produit' => $rp->libelle,
            'quantite' => $rp->qte,
            'prix_unitaire' => $rp->prix_de_vente,
            'montant_total' => $rp->cout_total,
        ]];

        return response()->json(['data' => $data]);
    }

    protected function price(array $params)
    {
        $name = Str::lower($params['name'] ?? '');
        if (!$name) return response()->json(['data' => []]);

        // Eloquent: produits(libelle, prix_de_vente, updated_at)
        $row = Produit::query()
            ->select(['libelle', 'prix_de_vente', 'updated_at'])
            ->whereRaw('LOWER(libelle) LIKE ?', ['%' . $name . '%'])
            ->orderByDesc('updated_at')
            ->first();

        $data = [];
        if ($row) {
            $data[] = [
                'nom' => $row->libelle,
                'prix_vente' => $row->prix_de_vente,
                'date_mise_a_jour' => $row->updated_at,
            ];
        }

        return response()->json(['data' => $data]);
    }
}