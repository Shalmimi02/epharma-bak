<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ProdNatureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $items = [
            "ALIMENTS BÉBÉ",
            "ALIMENTS BÉBÉ SANS TVA",
            "ALIMENTS BÉBÉ LOCAL",
            "ALIMENTS BÉBÉ LOCAL TVA",
            "ANS LAIT",
            "ANS MEDICAMENTS",
            "ANS PARAPHARMACIE",
            "FRAIS GÉNÉRAUX",
            "LAIT",
            "LAIT LOCAL",
            "LAIT TVA",
            "MEDICAMENT PUBLIC IMPORTÉ",
            "MEDICAMENT PUBLIC IMPORTÉ",
            "MEDICAMENTS GLUCOSÉS, SOLUTÉS",
            "MEDICAMENTS HOPITAUX",
            "MÉDICAMENTS GÉNÉRIQUES",
            "PARAPHA (ALCOOL EN FÛT, FORMOL)",
            "PARAPHA (ALCOOL FÛT, FORMOL) TVA",
            "PARAPHARMACIE IMPORTÉE",
            "PARAPHARMACIE IMPORTÉE SANSS TVA",
            "PARAPHARMACIE IMPORTÉE SANSS TVA",
            "PRODUITS VÉTÉRINAIRES",
            "SPÉCIALE",
            "TRANCHE OU QUANTITÉ",
            "Autre"
        ];

        foreach ($items as $item) {
            DB::table('prod_natures')->insert([
                'libelle' => strtoupper($item),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
