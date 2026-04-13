# e-boutique
Application web de e-boutique  

## Installation et Configuration

Suivez ces étapes pour installer le projet sur votre environnement local.

### Prérequis
* **PHP** (version 8.1 ou supérieure)
* **Composer**
* **Symfony CLI** (recommandé)
* **Node.js & NPM** (pour les assets)
* Un serveur de base de données (MySQL/MariaDB)

### 1. Clonage du projet
```
git clone https://github.com/pnouhet/e-boutique.git
cd e-boutique
```
### 2. Installation des dépendances  
Installez les dépendances PHP avec Composer et les dépendances JavaScript avec NPM :
```
composer install
npm install
```
### 3. Configuration de l'environnement  
Copiez le fichier `.env` pour créer votre fichier de configuration locale :  
```
cp .env .env.local
```  
Ouvrez le fichier .env.local et modifiez la ligne DATABASE_URL avec vos identifiants de base de données :  
`DATABASE_URL="mysql://utilisateur:mot_de_passe@127.0.0.1:3306/nom_de_la_bdd?serverVersion=8.0"`  
### 4. Création de la base de données  
Exécutez les commandes suivantes pour créer la base de données et lancer les migrations :  
```
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```
### 5. Compilation des assets  
`npm run dev`  
### 6. Lancement du serveur  
`symfony server:start`
