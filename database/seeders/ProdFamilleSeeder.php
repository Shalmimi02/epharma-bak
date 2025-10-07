<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ProdFamilleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $items = [
           "ACCESSOIRE MÉDICAUX TIPS",
            "ACCESSOIRES BB",
            "ACCESSOIRES DIVERS",
            "ACCESSOIRES MÉDICAUX",
            "BABY FOODS",
            "CHIMIQUE",
            "CHIMIQUES CONDITIONNÉS",
            "CONTACTOLOGIE",
            "COTONS + BANDES + COMPRESSES",
            "COUTELLERIE",
            "DENTIFRICES, APPAR. DENTAIRES",
            "DIÉTÉTIQUE",
            "FARINES",
            "HERBORISTERIE",
            "HYGIÈNE CORPORELLE",
            "HYGIÈNE DOMESTIQUE",
            "HYGIÈNE FÉMININE",
            "INCONTINENCE, ALÈZES",
            "LAITS INFANTILE",
            "MÉDICAMENTS HOPITAUX",
            "MÉDICAMENTS PUBLICS",
            "MÉDICAMENTS PUBLICS SOCIAUX",
            "MEDICAMENTS",
            "ORTHOPÉDIE NON TIPS",
            "PASTILLES MÉDICINALES",
            "PRODUITS VÉTÉRINAIRES",
            "RÉACTIFS NTIPS-TEST GROSSESSE",
            "RÉACTIFS TIPS",
            "SPARADRAPS MÉDICAUX",
            "VERRERIE, CONDITIONNEMENT",
            "Autre"
        ];

        foreach ($items as $item) {
            DB::table('prod_familles')->insert([
                'libelle' => strtoupper($item),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
