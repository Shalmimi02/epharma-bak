<?php

namespace App\Observers;

use App\Models\Fournisseur;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class FournisseurObserver
{
    public function created(Fournisseur $fournisseur): void
    {
        if(isset($fournisseur->libelle)){
            $code = Str::upper(str_replace(' ', '', $fournisseur->id.' '.$fournisseur->libelle));
            DB::table('fournisseurs')->where('id', $fournisseur->id)->update(['code' => $code]);
        }
    }

    public function updated(Fournisseur $fournisseur): void
    {
        if(empty($fournisseur->code)){
            $code = Str::upper(str_replace(' ', '', $fournisseur->id.' '.$fournisseur->libelle));
            DB::table('fournisseurs')->where('id', $fournisseur->id)->update(['code' => $code]);
        }
    }
}
