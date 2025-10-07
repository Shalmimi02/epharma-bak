<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Backup;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\Process\Exception\ProcessFailedException;

class AuthController extends Controller
{
    public function index()
    {
        return $this->sendApiResponse([], 'Connexion au serveur réussi !');
    }

    private $filePath = 'app/public/parametres.txt';
    private $sftpPath = 'app/public/sftp.txt';

    public function getPharmacieInfo()
    {
        if (File::exists(storage_path($this->filePath))) {
            $content = File::get(storage_path($this->filePath));
            $data = json_decode($content, true);

            return $this->sendApiResponse($data, 'Recuperation des information réussie');
        }

        return null;
    }

    public function savePharmacieInfo(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'nom_pharmacie' => 'required|string',
                'adresse' => 'required|string',
                'telephone1' => 'required',
                'email' => 'nullable|email',
                'logo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            ],
            $messages = [
                'nom_pharmacie.required' => 'Le champ libelle ne peut etre vide',
                'adresse.required' => 'Le champ description ne peut etre vide',
            ]
        );

        if ($validator->fails()) return $this->sendApiErrors($validator->errors()->all());

        $pharmacieInfos = $request->all();

        if ($request->file('logo')) {
            $path = $request->file('logo')->store('logos', 'public');
            $pharmacieInfos['logo'] = config('app.url').'/storage/'.$path;
        }
        else if ($request->logo_url){
            $pharmacieInfos['logo'] = $request->logo_url;
        }

        File::put(storage_path($this->filePath), json_encode($pharmacieInfos));

        return $this->sendApiResponse([], 'Informations mis à jour');
    }

    public function getPharmacieSftp()
    {
        if (File::exists(storage_path($this->sftpPath))) {
            $content = File::get(storage_path($this->sftpPath));
            $data = json_decode($content, true);

            return $this->sendApiResponse($data, 'Recuperation des informations de connexion ftp réussie');
        }

        return null;
    }

    public function savePharmacieSftp(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'sftp_host' => 'required',
                'sftp_username' => 'required',
                'sftp_password' => 'required',
                'sftp_port' => 'nullable',
            ],
            $messages = [
                'sftp_username.required' => 'Le champ username ne peut etre vide',
                'sftp_password.required' => 'Le champ password ne peut etre vide',
                'sftp_port.required' => 'Le champ host ne peut etre vide',
            ]
        );

        if ($validator->fails()) return $this->sendApiErrors($validator->errors()->all());

        $pharmacieInfos = $request->all();

        File::put(storage_path($this->sftpPath), json_encode($pharmacieInfos));

        return $this->sendApiResponse([], 'Informations mis à jour');
    }
    
    public function authenticate(Request $request)
    {
        $credentials = $request->validate([
            'name' => ['required'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {

            $user = Auth::user();
            $user->update([
                'last_connexion' => now(),
            ]);

            $user_datas = [
                "id" => $user->id,
                "photo" => $user->photo_url,
                "name" => $user->name,
                "fullname" => $user->fullname,
                "email" => $user->email,
                "role" => $user->role,
                "must_change_password" => $user->must_change_password,
                "adresse" => $user->adresse,
                "boite_postale" => $user->boite_postale,
                "ville" => $user->ville,
                "date_naissance" => $user->date_naissance,
                "sexe" => $user->sexe,
                "poste" => $user->poste,
                "telephone" => $user->telephone,
                "is_enabled" => $user->is_enabled,
                "is_admin" => $user->is_admin,
                "habilitations" => $user->habilitations,
                "token" => $user->createToken("asdecode_token_" . Str::slug($user->name . ' ' . now(), '_'))->plainTextToken,
            ];

            if ($user->email_verified_at != null) {
                $user_datas['verifiedAccount'] = true;
            }

            return $this->sendApiResponse($user_datas, 'Bienvenue');
        }

        return $this->sendApiErrors(['Indentifiants incorrects.']);
    }

    public function logout()
    {
        if (isset(auth('sanctum')->user()->id)) {
            $user = User::find(auth('sanctum')->user()->id);

            if ($user->tokens()->delete()) {
                return $this->sendApiResponse([], 'Bye Bye ! Revenez vite');
            } else $this->sendApiErrors(['Erreur réseau, veuillez reessayer.']);
        }

        return $this->sendApiResponse([], 'Bye Bye ! Revenez vite');
    }

    public function backup()
    {
        try {
            // 📌 Définition du nom du fichier
            $filename = 'backup-' . date('Y-m-d_H-i-s') . '.sql';
            $path = storage_path("app/backups/$filename");

            // Assure-toi que le dossier de sauvegarde existe
            if (!is_dir(storage_path('app/backups'))) {
                mkdir(storage_path('app/backups'), 0777, true);
            }

            // 📌 Exécution de la commande mysqldump
            $process = new Process([
                'mysqldump',
                '--user=' . env('DB_USERNAME'),
                '--password=' . env('DB_PASSWORD'),
                '--host=' . env('DB_HOST'),
                env('DB_DATABASE'),
                '--result-file=' . $path
            ]);

            $process->mustRun();

            // Vérifie si la sauvegarde a bien été créée
            if (!file_exists($path)) {
                return $this->sendApiErrors(['Erreur : le fichier de sauvegarde n’a pas été créé.']);
            }

            // 📌 Enregistrer la sauvegarde dans la base de données
            $backup = Backup::create([
                'filename' => $filename,
                'path' => $path
            ]);

            // 📌 Transfert via SFTP
            $this->transferToRemoteServer($filename);

            return $this->sendApiResponse($filename, 'Sauvegarde réussie');
        } catch (ProcessFailedException $exception) {
            Log::error('Erreur de sauvegarde : ' . $exception->getMessage());
            return $this->sendApiErrors(['Erreur lors de la sauvegarde.']);
        }
    }

    private function transferToRemoteServer($filename)
    {
        // 📌 Configuration SFTP
        $sftpDisk = Storage::disk('sftp');

        // 📌 Transfert du fichier
        $localPath = storage_path("app/backups/$filename");
        $remotePath = "backups/$filename";

        if ($sftpDisk->put($remotePath, file_get_contents($localPath))) {
            Log::info("Transfert SFTP réussi : $remotePath");
        } else {
            Log::error("Échec du transfert SFTP : $remotePath");
        }
    }

    public function restoreDatabase($id) {
        $backup = Backup::find($id);
    
        if (!$backup || !file_exists($backup->path)) {
            return $this->sendApiErrors(['Fichier de sauvegarde introuvable.']);
        }
    
        $process = new Process([
            'mysql',
            '--user=' . env('DB_USERNAME'),
            '--password=' . env('DB_PASSWORD'),
            '--host=' . env('DB_HOST'),
            env('DB_DATABASE'),
            '-e',
            'source ' . $backup->path
        ]);
    
        $process->run();
    
        if (!$process->isSuccessful()) {
            Log::error("Erreur de restauration : " . $process->getErrorOutput());
            return $this->sendApiErrors(['Échec de la restauration. '.$process->getErrorOutput()]);
        }
    
        return $this->sendApiResponse($backup, 'Sauvegarde réussie');
    }
    public function listVersions()
{
    // Dossier à lister
    $dir = 'C:\\laragon\\www\\epg-epharma\\release';

    // Vérifie si le dossier existe
    if (!file_exists($dir)) {
        return response()->json([
            'status' => 'error',
            'message' => 'Le dossier n\'existe pas.'
        ], 404);
    }

    // Récupère tous les fichiers
    $files = scandir($dir);
    $zipFiles = [];

    // Regex pour extraire la version : update-3.3.2.zip => version: 3.3.2
    $pattern = '/-(\d+\.\d+\.\d+)\.zip$/';

    foreach ($files as $file) {
        // On ne garde que les .zip
        if (\Illuminate\Support\Str::endsWith($file, '.zip')) {
            $version = '0.0.0'; // valeur par défaut

            // Vérifie si le nom contient une version
            if (preg_match($pattern, $file, $matches)) {
                $version = $matches[1]; // ex. 3.3.2
            }

            // Récupère la date de modification du fichier
            $filePath = $dir . DIRECTORY_SEPARATOR . $file;
            $mtime = filemtime($filePath);
            $date = date('Y-m-d H:i:s', $mtime);

            $zipFiles[] = [
                'name'    => $file,
                'version' => $version,
                'date'    => $date
            ];
        }
    }

    // On filtre pour ne garder que les fichiers dont la date commence par la date du jour
    $aujourdhui = date('Y-m-d'); // ex: 2023-03-18
    $zipFilesToday = array_filter($zipFiles, function ($zip) use ($aujourdhui) {
        // On compare uniquement la partie 'YYYY-MM-DD' de la date
        return substr($zip['date'], 0, 10) === $aujourdhui;
    });

    // Trier par version décroissante
    usort($zipFilesToday, function ($a, $b) {
        return version_compare($b['version'], $a['version']);
    });

    // Retourne uniquement le dernier ZIP d'aujourd'hui
    if (!empty($zipFilesToday)) {
        return response()->json([
            'status' => 'success',
            'data'   => $zipFilesToday[0] // On prend le plus récent (après tri)
        ]);
    }

    return response()->json([
        'status' => 'error',
        'message' => 'Aucun fichier ZIP trouvé pour aujourd\'hui.'
    ], 404);
}

    
}
