<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        \App\Models\Rayon::create([
            'libelle' => 'Default',
        ]);

        \App\Models\Caisse::create([
            'libelle' => 'Default',
            'pin'=> '1234',
            'statut'=> 'Ouvert'
        ]);

        DB::table('clients')->insert([
            'id' => 1,
            'libelle' => 'COMPTANT',
            'remise_percent' => 0,
            'created_by' => 'Epharma',
        ]);
        
        DB::table('users')->insert([
            'name' => 'superadmin',
            'is_admin' => true,
            'last_name' => 'YDS',
            'first_name' => 'Pharmacie',
            'fullname' => 'Pharmacie YDS',
            'adresse' => 'Oloumi, Rue justinho',
            'email' => 'it@yamslogistics.com',
            'sexe' => 'masculin',
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
            'email_verified_at' => now(),
            'must_change_password' => false,
            'remember_token' => Str::random(10),
        ]);

        $this->call([
            MouvMotifSeeder::class,
            ProdCategorieSeeder::class,
            ProdClasseSeeder::class,
            ProdFamilleSeeder::class,
            ProdFormeSeeder::class,
            ProdNatureSeeder::class,
        ]);
    }
}
