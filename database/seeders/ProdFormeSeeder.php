<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProdFormeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $items = [
            'COMPRIMES, GELULES, CAPSULES',
            'AMPOULES INJECTABLES, PERF.',
            'AMPOULES BUVABLES',
            'SACHETS, GRANULES, PAQUETS',
            'GOUTTES BUV, SOLUTES BUV, SUSP',
            'SIROPS, GELEES',
            'COLLYRE, PDE OPHT, CONTACTOLOG',
            'POMMADES, CREMES, ONGUENTS',
            'DISPOSITIFS TRANSDERMIQUES',
            'SUPPOSITOIRES, GELS RECTAUX',
            'USAGES EXTERNES',
            'ORL, GTTES NAS, AURIC, COLLUT',
            'GYNECOLOGIE OVULES CPR GYNECO',
            'ACCESSOIRES ET PANSEMENTS',
            'HYGIENE BUCCALE DENTAIRE',
            'HYGIENE CORPORELLE',
            'HYGIENE DOMESTIQUE',
            'DIETETIQUE ADULTE',
            'DIET INFANTILE, LAITS, FARINES',
            'VETERINAIRE',
            'HERBORISTERIE, TISANES',
            'HOMEOPATHIE',
            'REACTIFS, PRODUITS RADIOLOGIE',
            'PRODUITS CHIMIQUES ET VERRERIE',
            'PASTILLAGE',
            'DIVERS',
            'PERMANGANATE DE POTASSIUM',
            'Autre'
        ];

        foreach ($items as $item) {
            DB::table('prod_formes')->insert([
                'libelle' => strtoupper($item),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
