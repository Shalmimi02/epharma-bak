<?php

namespace App\Observers;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class UtilisateurObserver
{
    public function created(User $utilisateur): void
    {
        $req = DB::table('users')->where('id', $utilisateur->id);
        $req->update([
            'name'  => Str::slug($utilisateur->fullname.$utilisateur->id, ''),
        ]);
    }

    public function updated(User $utilisateur): void
    {
        //
    }

    public function deleted(User $utilisateur): void
    {
        //
    }

    public function restored(User $utilisateur): void
    {
        //
    }

    public function forceDeleted(User $utilisateur): void
    {
        //
    }
}
