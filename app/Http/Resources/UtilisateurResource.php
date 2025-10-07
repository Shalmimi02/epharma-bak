<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UtilisateurResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return parent::toArray($request);
        // $datas = [
        //     'id' => $this->id,
        //     'name' => $this->name,
        //     'email' => $this->email,
        //     'password' => $this->password,
        //     'last_name' => $this->last_name,
        //     'first_name' => $this->first_name,
        //     'fullname' => $this->fullname,
        //     'role' => $this->role,
        //     'is_admin' => $this->is_admin,
        //     'is_active' => $this->is_active,
        //     'must_change_password' => $this->must_change_password,
        //     'last_connexion' => $this->last_connexion,
        //     'last_activity' => $this->last_activity,
        //     'created_by' => $this->created_by,
        //     'is_archive' => $this->is_archive,
        //     'is_enabled' => $this->is_enabled,
        //     'photo_url' => $this->photo_url,
        //     'adresse' => $this->adresse,
        //     'boite_postale' => $this->boite_postale,
        //     'ville' => $this->ville,
        //     'date_naissance' => $this->date_naissance,
        //     'lieu_naissance' => $this->lieu_naissance,
        //     'sexe' => $this->sexe,
        //     'nom_pere' => $this->nom_pere,
        //     'nom_mere' => $this->nom_mere,
        //     'numero_cni' => $this->numero_cni,
        //     'numero_permis_conduire' => $this->numero_permis_conduire,
        //     'type_permis_conduire' => $this->type_permis_conduire,
        //     'matricule_fonction_publique' => $this->matricule_fonction_publique,
        //     'numero_cnss' => $this->numero_cnss,
        //     'matricule_cnss' => $this->matricule_cnss,
        //     'numero_cnamgs' => $this->numero_cnamgs,
        //     'situation_familial' => $this->situation_familial,
        //     'nombre_enfant_charge' => $this->nombre_enfant_charge,
        //     'niveau_etude' => $this->niveau_etude,
        //     'dernier_diplome' => $this->dernier_diplome,
        //     'etablissement' => $this->etablissement,
        //     'profession_formation' => $this->profession_formation,
        //     'poste' => $this->poste,
        //     'mode_paiement_salaire' => $this->mode_paiement_salaire,
        //     'type_contrat' => $this->type_contrat,
        //     'date_embauche' => $this->date_embauche,
        //     'date_fin_contrat' => $this->date_fin_contrat,
        //     'email' => $this->email,
        //     'telephone' => $this->telephone,
        //     'contact_de_secours' => $this->contact_de_secours,
        // ];

        // if ($this->user->tokens->last() !== null) {
        //     $dernier_token = $this->user->tokens->last();
        //     if (isset($dernier_token)) {
        //         $derniere_connexion = date('d/m/Y à H:i:s', strtotime($dernier_token->last_used_at));
        //     }
        // } 
        // return $datas;

    }
}
