<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ProdClasseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $items = [
            "INFECT/PARASIT AMINOS ET APPAR",
            "INFECT/PARASIT ATB GLYCOPEPTID",
            "INFECT/PARASIT ANTIFONG SYSTEM",
            "INFECT/PARASIT ANILEPRE/TUBERC",
            "INFECT/PARASIT ANTIPARAS SYSTE",
            "CEPHALOSP/BETA-LACTAMINES",
            "ALLERGOLOGIE",
            "ANESTHESIE",
            "ANALGESIQUES MORPHINIQUES",
            "ANTALGIQUES NON OPIACES",
            "ANTALGIQUES OPIACES FAIBLES",
            "ANTALGIQUES OPIACES MIXTES",
            "AS ANTALGIQUES ET/OU ANTIPYRET",
            "AINS",
            "AIS VOIE INJECTABLE",
            "AIS VOIE ORALE",
            "ANTINEOPLASIQUE",
            "CARDIOLOGIE/ANTIARYTHMIQUES",
            "CARDIOLOGIE/ ANTIHYPERTENSEUR",
            "ANTIHYPOTENSEUR",
            "CARDIOPATHIE CONGENITALE",
            "SEDATIFS CARDIAQUES",
            "TTT INSUFFISANCE CARDIAQUE",
            "TTT INSUFFISANCE CORRONARIENNE",
            "TT PREVENTION CARDIOVASCULAIRE",
            "VASODILAT ANTI ISCHEMIQUES",
            "DERM/ANTISEPTIE DETERSION",
            "DERM/ANTIALOPECIQUES/ACNEIQUES",
            "DERM/ANTIBACTER ANTIHERPETIQUE",
            "DERM/ANTIFONGIQUES",
            "DERM/ANTIPARASIT_ANT PRURIGIN",
            "DERM/ANTIMITO SEBORRHE-SUDORAU",
            "DERM/DERMOCORTICOIDES",
            "DERM/ETHER-TROPHIQUES-KERATOLY",
            "DERM/DIVERS TTT",
            "ENDOCRIN/INHIB PROLACTINE",
            "ENDOCRI/AXE HYPOPH GONADIQUE",
            "ENDOCR/AXE HYPOPH THYROIDIEN",
            "G.E.H/ANTIACIDE-PANSEMENTS INT",
            "G.E.H/ANTIULCEREUX",
            "G.E.H/ANTIFONGIQUES BUT DIGEST",
            "G.E.H/TTT CONSTIPATION",
            "G.E.H/TTT DIARRHEE",
            "G.E.H/TTT CIRRHOSES HEPATITES",
            "G.E.H/TTT MALAD INFLAM INTESTI",
            "G.E.H/TTT NAUSEE VOMIS RGO",
            "G.E.H/TTT TROUBLE FCTNEL DIGES",
            "GYNECO/CONTRACEPTION",
            "GYNECO/ESTROEGENES",
            "GYNECO/OCYTOCIQUES LACTATION",
            "GYNECO/PROGESTATIFS",
            "GYNECO/TTT MENOPAUSE",
            "GYNECO/TTT STERILITE",
            "GYNECO/TTT DES VULVO VAGINITES",
            "GYNECO/UTERO RELAXANTS",
            "HEMOST/HEMATOP  ANTIHEMORRAGIQ",
            "HEMOST/HEMATOP ANTITHROMBIQUES",
            "PSEUDOEPHEDRINE",
            "PHENYL-EPHRINE",
            "Autre"
        ];

        foreach ($items as $item) {
            DB::table('prod_classe_theraps')->insert([
                'libelle' => strtoupper($item),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
