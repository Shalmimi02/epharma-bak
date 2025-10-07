# Déploiement sur Render

Ce guide vous explique comment déployer le backend Epharma sur Render.

## Prérequis

- Un compte GitHub (déjà fait ✅)
- Un compte Render (gratuit) : https://render.com

## Étape 1 : Créer un compte Render

1. Allez sur https://render.com
2. Cliquez sur "Get Started"
3. Connectez-vous avec votre compte GitHub

## Étape 2 : Créer une base de données PostgreSQL

1. Dans le dashboard Render, cliquez sur **"New +"**
2. Sélectionnez **"PostgreSQL"**
3. Configurez :
   - **Name** : `epharma-db`
   - **Database** : `epharma`
   - **User** : `epharma`
   - **Region** : Choisissez la région la plus proche (ex: Frankfurt pour l'Europe)
   - **Plan** : Sélectionnez **Free** (gratuit)
4. Cliquez sur **"Create Database"**
5. **Notez les informations de connexion** (vous en aurez besoin) :
   - Internal Database URL
   - Hostname
   - Port
   - Database
   - Username
   - Password

## Étape 3 : Créer le Web Service

1. Dans le dashboard, cliquez sur **"New +"**
2. Sélectionnez **"Web Service"**
3. Connectez votre dépôt GitHub :
   - Cliquez sur **"Connect a repository"**
   - Autorisez Render à accéder à GitHub
   - Sélectionnez le dépôt **"Shalmimi02/epharma-bak"**
4. Configurez le service :
   - **Name** : `epharma-backend`
   - **Region** : Même région que la base de données
   - **Branch** : `main`
   - **Root Directory** : Laissez vide
   - **Runtime** : Sélectionnez **"PHP"**
   - **Build Command** : `./build.sh`
   - **Start Command** : `php artisan serve --host=0.0.0.0 --port=$PORT`

## Étape 4 : Configurer les variables d'environnement

Dans la section **"Environment"**, ajoutez ces variables :

### Variables obligatoires :

```
APP_NAME=Epharma
APP_ENV=production
APP_DEBUG=false
APP_URL=https://epharma-backend.onrender.com
APP_TIMEZONE=Africa/Libreville
APP_TVA=0.18
APP_CSS=0.1

LOG_CHANNEL=stack
LOG_LEVEL=error

DB_CONNECTION=pgsql
DB_HOST=[Hostname de votre base PostgreSQL Render]
DB_PORT=[Port de votre base PostgreSQL Render]
DB_DATABASE=epharma
DB_USERNAME=epharma
DB_PASSWORD=[Password de votre base PostgreSQL Render]

CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync

MAIL_MAILER=smtp
MAIL_HOST=pl02.marcaria.net
MAIL_PORT=465
MAIL_USERNAME=info@epharma.ga
MAIL_PASSWORD=Epharma241
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS=info@epharma.ga
MAIL_FROM_NAME=Epharma
```

**Note importante** :
- Remplacez `[Hostname de votre base PostgreSQL Render]`, `[Port de votre base PostgreSQL Render]`, et `[Password de votre base PostgreSQL Render]` par les valeurs de votre base de données créée à l'étape 2
- Pour `APP_KEY`, Render le générera automatiquement lors du build

## Étape 5 : Configuration des bases de données multiples (optionnel)

Si vous avez besoin des bases comptes, stock, et ventes séparées :

1. Créez 3 bases PostgreSQL supplémentaires sur Render :
   - `epharma-comptes`
   - `epharma-stock`
   - `epharma-ventes`

2. Ajoutez ces variables d'environnement :

```
DBCOMPTES_DATABASE=epharma_comptes
DBCOMPTES_USERNAME=[username]
DBCOMPTES_PASSWORD=[password]

DBSTOCK_DATABASE=epharma_stock
DBSTOCK_USERNAME=[username]
DBSTOCK_PASSWORD=[password]

DBVENTES_DATABASE=epharma_ventes
DBVENTES_USERNAME=[username]
DBVENTES_PASSWORD=[password]
```

## Étape 6 : Déployer

1. Cliquez sur **"Create Web Service"**
2. Render va automatiquement :
   - Cloner votre dépôt GitHub
   - Installer les dépendances avec Composer
   - Exécuter le script build.sh
   - Lancer les migrations
   - Démarrer le serveur

3. Attendez que le déploiement se termine (3-5 minutes)
4. Vous verrez le statut passer à **"Live"**

## Étape 7 : Tester votre API

Votre API sera disponible à l'adresse :
```
https://epharma-backend.onrender.com
```

Testez avec :
```
https://epharma-backend.onrender.com/api/health
```

## Configuration CORS

Si vous avez un frontend, ajoutez l'URL de votre frontend dans `config/cors.php` :

```php
'allowed_origins' => [
    'https://votre-frontend.vercel.app',
    'http://localhost:3000', // Pour le développement local
],
```

## Mises à jour automatiques

Render redéploiera automatiquement votre application à chaque push sur la branche `main` de GitHub.

## Dépannage

### Problème : L'application ne démarre pas
- Vérifiez les logs dans le dashboard Render
- Assurez-vous que toutes les variables d'environnement sont définies

### Problème : Erreur de connexion à la base de données
- Vérifiez que les informations de connexion DB sont correctes
- Assurez-vous que la base de données est dans la même région que le service

### Problème : APP_KEY non défini
- Render devrait le générer automatiquement
- Sinon, générez-le localement avec `php artisan key:generate` et ajoutez-le manuellement

## Commandes utiles

Pour exécuter des commandes sur Render :
1. Allez dans votre service
2. Cliquez sur l'onglet **"Shell"**
3. Exécutez vos commandes artisan :
   ```bash
   php artisan migrate
   php artisan db:seed
   php artisan cache:clear
   ```

## Plan gratuit Render

Le plan gratuit de Render offre :
- 750 heures par mois
- L'application s'endort après 15 minutes d'inactivité
- Premier démarrage lent (jusqu'à 30 secondes)

Pour un service toujours actif, considérez un plan payant (~7$/mois).

## Support

En cas de problème, consultez :
- Documentation Render : https://render.com/docs
- Logs de votre service sur le dashboard Render
