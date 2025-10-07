<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class MouvMotifSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $items = [
            "VENTE",
            "ECHANGE EN PLUS ",
            "AVARIE MAGASIN",
            "CASSE DANS MAGASIN",
            "COMMANDE NON LIVRE",
            "DECONDITIONNEMENT",
            "DEFAUT DE FABRICATION",
            "DIFFERENCE D'INVENTAIRE EN PLUS",
            "DIFFERENCE D'INVENTAIRE EN MOINS",
            "DONS ET LEGS",
            "ECHANGE EN MOINS",
            "ERREUR DE FACTURATION",
            "ERREUR DE LIVRAISON A SORTIR",
            "ERREUR DE LIVRAISON A ENTRER",
            "ERREUR SAISIE DE COMMANDE",
            "MANQUANT DANS LE MAGASIN",
            "MANQUANT A LA RECEPTION",
            "NON COMMANDE LIVRE",
            "PERIME",
            "PAYE NON LIVRE",
            "PRELEVEMENT PHARMACIEN",
            "PROMOTION",
            "RECONDITIONNEMENT",
            "REGLEMENT AVOIR PAYE NONLIVRE",
            "REGULATION STOCK",
            "REGUL. RENTREE STOCK",
            "REMBOURSEMENT PRDTS EMPRUNTES",
            "RENVOI AU FOURNISSEUR",
            "RETOUR DE PRDTS PRETES DANS STOCK",
            "SURPLUS DANS LE MAGASIN",
            "SOIN AU PERSONNEL",
            "TROP REÇU A LA RÉCEPTION",
            "TRANSF.STOCK AVEC DECONDITIONNEMENT",
            "UNITE GRATUITE",
            'ANNULATION VENTE',
            'NOUVELLE COMMANDE'
        ];

        foreach ($items as $item) {
            DB::table('mouv_motifs')->insert([
                'libelle' => strtoupper($item),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
