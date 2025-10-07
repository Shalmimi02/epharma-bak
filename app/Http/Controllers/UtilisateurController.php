<?php

namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\UtilisateurResource;

class UtilisateurController extends Controller
{

    public function index() 
    {
        $utilisateurs = User::latest();

        if (isset($_GET['req_count'])) return $this->filterByColumn('users', $utilisateurs)->count();

        return UtilisateurResource::collection($this->AsdecodefilterBy('users', $utilisateurs));
    }

    public function store(Request $request) 
    {
        $validator = Validator::make(
           $request->all(),
           [
               //'name' => 'required',
               //'password' => 'required',
               //'fullname' => 'required',
               //'role' => 'required',
               //'is_admin' => 'required',
               //'is_active' => 'required',
               //'must_change_password' => 'required',
               //'last_connexion' => 'required',
               //'last_activity' => 'required',
               //'created_by' => 'required',
               //'is_archive' => 'required',
               //'is_enabled' => 'required',
               //'photo_url' => 'required',
               //'adresse' => 'required',
               //'boite_postale' => 'required',
               //'ville' => 'required',
               //'date_naissance' => 'required',
               //'lieu_naissance' => 'required',
               //'sexe' => 'required',
               //'nom_pere' => 'required',
               //'nom_mere' => 'required',
               //'numero_cni' => 'required',
               //'numero_permis_conduire' => 'required',
               //'type_permis_conduire' => 'required',
               //'matricule_fonction_publique' => 'required',
               //'numero_cnss' => 'required',
               //'matricule_cnss' => 'required',
               //'numero_cnamgs' => 'required',
               //'situation_familial' => 'required',
               //'nombre_enfant_charge' => 'required',
               //'niveau_etude' => 'required',
               //'dernier_diplome' => 'required',
               //'etablissement' => 'required',
               //'profession_formation' => 'required',
               //'poste' => 'required',
               //'mode_paiement_salaire' => 'required',
               //'type_contrat' => 'required',
               //'date_embauche' => 'required',
               //'date_fin_contrat' => 'required',
               //'email' => 'required',
               //'telephone' => 'required',
               //'contact_de_secours' => 'required',
           ],
           $messages = [
               //'name.required' => 'Le champ name ne peut etre vide',
               //'password.required' => 'Le champ password ne peut etre vide',
               //'fullname.required' => 'Le champ fullname ne peut etre vide',
               //'role.required' => 'Le champ role ne peut etre vide',
               //'is_admin.required' => 'Le champ is_admin ne peut etre vide',
               //'is_active.required' => 'Le champ is_active ne peut etre vide',
               //'must_change_password.required' => 'Le champ must_change_password ne peut etre vide',
               //'last_connexion.required' => 'Le champ last_connexion ne peut etre vide',
               //'last_activity.required' => 'Le champ last_activity ne peut etre vide',
               //'created_by.required' => 'Le champ created_by ne peut etre vide',
               //'is_archive.required' => 'Le champ is_archive ne peut etre vide',
               //'is_enabled.required' => 'Le champ is_enabled ne peut etre vide',
               //'photo_url.required' => 'Le champ photo_url ne peut etre vide',
               //'adresse.required' => 'Le champ adresse ne peut etre vide',
               //'boite_postale.required' => 'Le champ boite_postale ne peut etre vide',
               //'ville.required' => 'Le champ ville ne peut etre vide',
               //'date_naissance.required' => 'Le champ date_naissance ne peut etre vide',
               //'lieu_naissance.required' => 'Le champ lieu_naissance ne peut etre vide',
               //'sexe.required' => 'Le champ sexe ne peut etre vide',
               //'nom_pere.required' => 'Le champ nom_pere ne peut etre vide',
               //'nom_mere.required' => 'Le champ nom_mere ne peut etre vide',
               //'numero_cni.required' => 'Le champ numero_cni ne peut etre vide',
               //'numero_permis_conduire.required' => 'Le champ numero_permis_conduire ne peut etre vide',
               //'type_permis_conduire.required' => 'Le champ type_permis_conduire ne peut etre vide',
               //'matricule_fonction_publique.required' => 'Le champ matricule_fonction_publique ne peut etre vide',
               //'numero_cnss.required' => 'Le champ numero_cnss ne peut etre vide',
               //'matricule_cnss.required' => 'Le champ matricule_cnss ne peut etre vide',
               //'numero_cnamgs.required' => 'Le champ numero_cnamgs ne peut etre vide',
               //'situation_familial.required' => 'Le champ situation_familial ne peut etre vide',
               //'nombre_enfant_charge.required' => 'Le champ nombre_enfant_charge ne peut etre vide',
               //'niveau_etude.required' => 'Le champ niveau_etude ne peut etre vide',
               //'dernier_diplome.required' => 'Le champ dernier_diplome ne peut etre vide',
               //'etablissement.required' => 'Le champ etablissement ne peut etre vide',
               //'profession_formation.required' => 'Le champ profession_formation ne peut etre vide',
               //'poste.required' => 'Le champ poste ne peut etre vide',
               //'mode_paiement_salaire.required' => 'Le champ mode_paiement_salaire ne peut etre vide',
               //'type_contrat.required' => 'Le champ type_contrat ne peut etre vide',
               //'date_embauche.required' => 'Le champ date_embauche ne peut etre vide',
               //'date_fin_contrat.required' => 'Le champ date_fin_contrat ne peut etre vide',
               //'email.required' => 'Le champ email ne peut etre vide',
               //'telephone.required' => 'Le champ telephone ne peut etre vide',
               //'contact_de_secours.required' => 'Le champ contact_de_secours ne peut etre vide',
           ]
         );

        $utilisateurs = User::latest();
        if ($utilisateurs
        ->where('name', $request->name)
        ->where('password', $request->password)
        ->where('fullname', $request->fullname)
        ->where('role', $request->role)
        ->where('is_admin', $request->is_admin)
        ->where('is_active', $request->is_active)
        ->where('must_change_password', $request->must_change_password)
        ->where('last_connexion', $request->last_connexion)
        ->where('last_activity', $request->last_activity)
        ->where('created_by', $request->created_by)
        ->where('is_archive', $request->is_archive)
        ->where('is_enabled', $request->is_enabled)
        ->where('photo_url', $request->photo_url)
        ->where('adresse', $request->adresse)
        ->where('boite_postale', $request->boite_postale)
        ->where('ville', $request->ville)
        ->where('date_naissance', $request->date_naissance)
        ->where('lieu_naissance', $request->lieu_naissance)
        ->where('sexe', $request->sexe)
        ->where('nom_pere', $request->nom_pere)
        ->where('nom_mere', $request->nom_mere)
        ->where('numero_cni', $request->numero_cni)
        ->where('numero_permis_conduire', $request->numero_permis_conduire)
        ->where('type_permis_conduire', $request->type_permis_conduire)
        ->where('matricule_fonction_publique', $request->matricule_fonction_publique)
        ->where('numero_cnss', $request->numero_cnss)
        ->where('matricule_cnss', $request->matricule_cnss)
        ->where('numero_cnamgs', $request->numero_cnamgs)
        ->where('situation_familial', $request->situation_familial)
        ->where('nombre_enfant_charge', $request->nombre_enfant_charge)
        ->where('niveau_etude', $request->niveau_etude)
        ->where('dernier_diplome', $request->dernier_diplome)
        ->where('etablissement', $request->etablissement)
        ->where('profession_formation', $request->profession_formation)
        ->where('poste', $request->poste)
        ->where('mode_paiement_salaire', $request->mode_paiement_salaire)
        ->where('type_contrat', $request->type_contrat)
        ->where('date_embauche', $request->date_embauche)
        ->where('date_fin_contrat', $request->date_fin_contrat)
        ->where('email', $request->email)
        ->where('telephone', $request->telephone)
        ->where('contact_de_secours', $request->contact_de_secours)
        ->first()) {
           $messages = [ 'Cet enregistrement existe déjà' ];
           return $this->sendApiErrors($messages);
        }

        if ($validator->fails()) return $this->sendApiErrors($validator->errors()->all());

        $utilisateur = User::create($request->all());

        $password = Str::random(8);
        $req = DB::table('users')->where('id', $utilisateur->id)->update([
            'password'  => Hash::make($password),
        ]);

        $identifiants= [
            'user'  => User::find($utilisateur->id),
            'password'  => $password,
        ];

        return $this->sendApiResponse($identifiants, 'Utilisateur ajouté', 201);
    }

    public function show($id)
    {
        return new UtilisateurResource(User::find($id));
    }

    public function update(Request $request, $id) 
    {
        $validator = Validator::make(
           $request->all(),
           [
               //'name' => 'required',
               //'password' => 'required',
               //'fullname' => 'required',
               //'role' => 'required',
               //'is_admin' => 'required',
               //'is_active' => 'required',
               //'must_change_password' => 'required',
               //'last_connexion' => 'required',
               //'last_activity' => 'required',
               //'created_by' => 'required',
               //'is_archive' => 'required',
               //'is_enabled' => 'required',
               //'photo_url' => 'required',
               //'adresse' => 'required',
               //'boite_postale' => 'required',
               //'ville' => 'required',
               //'date_naissance' => 'required',
               //'lieu_naissance' => 'required',
               //'sexe' => 'required',
               //'nom_pere' => 'required',
               //'nom_mere' => 'required',
               //'numero_cni' => 'required',
               //'numero_permis_conduire' => 'required',
               //'type_permis_conduire' => 'required',
               //'matricule_fonction_publique' => 'required',
               //'numero_cnss' => 'required',
               //'matricule_cnss' => 'required',
               //'numero_cnamgs' => 'required',
               //'situation_familial' => 'required',
               //'nombre_enfant_charge' => 'required',
               //'niveau_etude' => 'required',
               //'dernier_diplome' => 'required',
               //'etablissement' => 'required',
               //'profession_formation' => 'required',
               //'poste' => 'required',
               //'mode_paiement_salaire' => 'required',
               //'type_contrat' => 'required',
               //'date_embauche' => 'required',
               //'date_fin_contrat' => 'required',
               //'email' => 'required',
               //'telephone' => 'required',
               //'contact_de_secours' => 'required',
              // 'habilitations' => 'required|array', // Exemple de validation

           ],
           $messages = [
               //'name.required' => 'Le champ name ne peut etre vide',
               //'password.required' => 'Le champ password ne peut etre vide',
               //'fullname.required' => 'Le champ fullname ne peut etre vide',
               //'role.required' => 'Le champ role ne peut etre vide',
               //'is_admin.required' => 'Le champ is_admin ne peut etre vide',
               //'is_active.required' => 'Le champ is_active ne peut etre vide',
               //'must_change_password.required' => 'Le champ must_change_password ne peut etre vide',
               //'last_connexion.required' => 'Le champ last_connexion ne peut etre vide',
               //'last_activity.required' => 'Le champ last_activity ne peut etre vide',
               //'created_by.required' => 'Le champ created_by ne peut etre vide',
               //'is_archive.required' => 'Le champ is_archive ne peut etre vide',
               //'is_enabled.required' => 'Le champ is_enabled ne peut etre vide',
               //'photo_url.required' => 'Le champ photo_url ne peut etre vide',
               //'adresse.required' => 'Le champ adresse ne peut etre vide',
               //'boite_postale.required' => 'Le champ boite_postale ne peut etre vide',
               //'ville.required' => 'Le champ ville ne peut etre vide',
               //'date_naissance.required' => 'Le champ date_naissance ne peut etre vide',
               //'lieu_naissance.required' => 'Le champ lieu_naissance ne peut etre vide',
               //'sexe.required' => 'Le champ sexe ne peut etre vide',
               //'nom_pere.required' => 'Le champ nom_pere ne peut etre vide',
               //'nom_mere.required' => 'Le champ nom_mere ne peut etre vide',
               //'numero_cni.required' => 'Le champ numero_cni ne peut etre vide',
               //'numero_permis_conduire.required' => 'Le champ numero_permis_conduire ne peut etre vide',
               //'type_permis_conduire.required' => 'Le champ type_permis_conduire ne peut etre vide',
               //'matricule_fonction_publique.required' => 'Le champ matricule_fonction_publique ne peut etre vide',
               //'numero_cnss.required' => 'Le champ numero_cnss ne peut etre vide',
               //'matricule_cnss.required' => 'Le champ matricule_cnss ne peut etre vide',
               //'numero_cnamgs.required' => 'Le champ numero_cnamgs ne peut etre vide',
               //'situation_familial.required' => 'Le champ situation_familial ne peut etre vide',
               //'nombre_enfant_charge.required' => 'Le champ nombre_enfant_charge ne peut etre vide',
               //'niveau_etude.required' => 'Le champ niveau_etude ne peut etre vide',
               //'dernier_diplome.required' => 'Le champ dernier_diplome ne peut etre vide',
               //'etablissement.required' => 'Le champ etablissement ne peut etre vide',
               //'profession_formation.required' => 'Le champ profession_formation ne peut etre vide',
               //'poste.required' => 'Le champ poste ne peut etre vide',
               //'mode_paiement_salaire.required' => 'Le champ mode_paiement_salaire ne peut etre vide',
               //'type_contrat.required' => 'Le champ type_contrat ne peut etre vide',
               //'date_embauche.required' => 'Le champ date_embauche ne peut etre vide',
               //'date_fin_contrat.required' => 'Le champ date_fin_contrat ne peut etre vide',
               //'email.required' => 'Le champ email ne peut etre vide',
               //'telephone.required' => 'Le champ telephone ne peut etre vide',
               //'contact_de_secours.required' => 'Le champ contact_de_secours ne peut etre vide',
           ]
         );

        $utilisateurs = User::latest();
        if ($utilisateurs
        ->where('name', $request->name)
        ->where('password', $request->password)
        ->where('fullname', $request->fullname)
        ->where('role', $request->role)
        ->where('is_admin', $request->is_admin)
        ->where('is_active', $request->is_active)
        ->where('must_change_password', $request->must_change_password)
        ->where('last_connexion', $request->last_connexion)
        ->where('last_activity', $request->last_activity)
        ->where('created_by', $request->created_by)
        ->where('is_archive', $request->is_archive)
        ->where('is_enabled', $request->is_enabled)
        ->where('photo_url', $request->photo_url)
        ->where('adresse', $request->adresse)
        ->where('boite_postale', $request->boite_postale)
        ->where('ville', $request->ville)
        ->where('date_naissance', $request->date_naissance)
        ->where('lieu_naissance', $request->lieu_naissance)
        ->where('sexe', $request->sexe)
        ->where('nom_pere', $request->nom_pere)
        ->where('nom_mere', $request->nom_mere)
        ->where('numero_cni', $request->numero_cni)
        ->where('numero_permis_conduire', $request->numero_permis_conduire)
        ->where('type_permis_conduire', $request->type_permis_conduire)
        ->where('matricule_fonction_publique', $request->matricule_fonction_publique)
        ->where('numero_cnss', $request->numero_cnss)
        ->where('matricule_cnss', $request->matricule_cnss)
        ->where('numero_cnamgs', $request->numero_cnamgs)
        ->where('situation_familial', $request->situation_familial)
        ->where('nombre_enfant_charge', $request->nombre_enfant_charge)
        ->where('niveau_etude', $request->niveau_etude)
        ->where('dernier_diplome', $request->dernier_diplome)
        ->where('etablissement', $request->etablissement)
        ->where('profession_formation', $request->profession_formation)
        ->where('poste', $request->poste)
        ->where('mode_paiement_salaire', $request->mode_paiement_salaire)
        ->where('type_contrat', $request->type_contrat)
        ->where('date_embauche', $request->date_embauche)
        ->where('date_fin_contrat', $request->date_fin_contrat)
        ->where('email', $request->email)
        ->where('telephone', $request->telephone)
        ->where('contact_de_secours', $request->contact_de_secours)
        ->where('id','!=', $id)->first()) {
           $messages = [ 'Cet enregistrement existe déjà' ];
           return $this->sendApiErrors($messages);
        }

        if ($validator->fails()) return $this->sendApiErrors($validator->errors()->all());

        $utilisateur = User::find($id);
        $utilisateur->update($request->all());
        return $this->sendApiResponse($utilisateur, 'Utilisateur modifié', 201);
    }

    public function destroy($id) 
    {
        $utilisateur = User::find($id);
        $utilisateur->delete();

        return $this->sendApiResponse($utilisateur, 'Utilisateur supprimé');
    }

    public function destroy_group(Request $request)
    {
        $key = 0;
        $nb_supprimes = 0;
        $messages= [];
        foreach ($request->selected_lines as $selected) {
            $utilisateur = User::find($selected);
            if (isset($utilisateur)) {
                if ($utilisateur->est_valide == 1) {
                    $messages[$key] = [
                        'severity' => 'warn',
                        'value' => 'Impossible de supprimer ID0'.$selected
                    ];
                    $key++;
                }
                else {
                    $utilisateur->delete();
                    $nb_supprimes++;
                    $messages[$key] = [
                        'severity' => 'success',
                        'value' => $nb_supprimes.' lignes ont été supprimé'
                    ];
                }
            }
        }
        return $this->sendApiResponse([], $messages);
    }

    public function reset_password($id)
    {
        $utilisateur = User::find($id);

        $password = Str::random(8);
        $req = DB::table('users')->where('id', $utilisateur->id)->update([
            'password'  => Hash::make($password),
            'must_change_password' => true
        ]);

        $identifiants= [
            'user'  => User::find($utilisateur->id),
            'password'  => $password,
        ];

        return $this->sendApiResponse($identifiants, 'Mot de passe reinitialisé');
    }

    public function update_password(Request $request, $id)
    {
        $validator = Validator::make($request->all(),
        [
            'password' => 'required|min:4',
            'password_confirm' => 'required',
        ], $messages = [
            'password.required' => 'Le champ nouveau mot de passe ne peut etre vide',
            'password.min' => 'Le mot de passe doit avoir 4 caracteres minimum',
            'password_confirm.required' => 'Le champ confirmation du mot de passe ne peut etre vide'
        ]);

        if ($validator->fails()) {
            return $this->sendApiErrors($validator->errors()->all());
        }

        $utilisateur = User::find($id);

        if ($request->password === $request->password_confirm) {
            $utilisateur->password = Hash::make($request->password);
            $utilisateur->must_change_password = false;
            $utilisateur->update();

            return $this->sendApiResponse($utilisateur, 'Mot de passe enregistré');
        }
        else return  $this->sendApiErrors(['Les mots de passe ne correspondent pas.']);

    }

}
