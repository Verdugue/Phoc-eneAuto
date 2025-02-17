# Phocéenne Auto - Gestion de Concession Automobile

## Présentation du Projet
Application web de gestion de concession automobile permettant de gérer les véhicules, clients, fournisseurs et transactions.

## Fonctionnalités Principales
- Gestion des véhicules (ajout, modification, suppression, photos)
- Gestion des clients et fournisseurs
- Suivi des transactions et génération de factures PDF
- Upload et gestion des documents
- Tableau de bord avec statistiques

## Installation Rapide

### Prérequis
- PHP 8.0+
- MySQL 5.7+
- Composer
- Serveur Web (XAMPP, WAMP, etc.)

### Installation

1. Cloner le projet
```bash
git clone https://github.com/Verdugue/Phoc-eneAuto.git
```

2. Installer les dépendances
```bash
composer install
or
pip install composer
```

3. Créer la base de données
- Créer une base de données nommée `autocar_manager`
- Importer le fichier `database/autocar_manager.sql`

4. Configuration
- Copier `config/database.example.php` vers `config/database.php`
- Modifier les informations de connexion dans `config/database.php` :
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'autocar_manager');
define('DB_USER', 'root'); // Votre utilisateur MySQL
define('DB_PASS', '');     // Votre mot de passe MySQL
```

5. Permissions
- Créer le dossier `uploads/` à la racine
- Donner les droits d'écriture au dossier `uploads/`

6. Lancer l'application
- Via XAMPP/WAMP : Placer le projet dans `htdocs/` ou `www/`
- Accéder à : `http://localhost/autocar-manager`

### Compte par défaut
```
Email : admin@admin.com
Mot de passe : admin123
```

## Structure des dossiers
```
autocar-manager/
├── assets/          # CSS, JS, images
├── config/          # Configuration
├── includes/        # Headers, footers
├── uploads/         # Fichiers uploadés
├── vehicles/        # Gestion véhicules
├── clients/         # Gestion clients
├── suppliers/       # Gestion fournisseurs
├── transactions/    # Gestion transactions
└── database/        # Fichiers SQL
```

## Dépendances
- Bootstrap 5.3 (CDN)
- Font Awesome 4.7 (CDN)
- TCPDF (via Composer)
- Chart.js (CDN)

## Base de données
Le fichier `database/autocar_manager.sql` contient :
- Structure complète de la base de données
- Données de démonstration
- Compte administrateur par défaut

## Développement

### Serveur de développement
```bash
php -S localhost:8000
```
Accéder à : `http://localhost:8000`

### Logs et débug
Les erreurs sont logguées dans :
- `logs/error.log`
- `logs/access.log`

## Problèmes courants

### Erreur de connexion à la base de données
- Vérifier les informations dans `config/database.php`
- Vérifier que MySQL est démarré
- Vérifier les droits de l'utilisateur MySQL

### Erreur d'upload
- Vérifier les droits du dossier `uploads/`
- Vérifier la taille maximale dans `php.ini`

## Contribution
1. Fork le projet
2. Créer une branche (`git checkout -b feature/nouvelle-fonctionnalite`)
3. Commit les changements (`git commit -m 'Ajout nouvelle fonctionnalité'`)
4. Push la branche (`git push origin feature/nouvelle-fonctionnalite`)
5. Créer une Pull Request

## Licence
Ce projet est sous licence MIT.

## Contact
Pour toute question ou problème :
- Créer une issue sur GitHub
- Email : votre.email@example.com
