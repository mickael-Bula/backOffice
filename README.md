# Application de gestion de stock

## Quelques précisions préalables à l'utilisation

L'application utilise une base de données pour gérer les stocks et un système d'authentification pour en protéger l'accés.

Pour insérer un premier utilisateur dans la base, il suffit de lancer les fixtures.
Les identifiants par défaut sont :

```bash
mail : admin@admin.com
password : admin
```

## Procédure d'installation

L'application a été construite avec Symfony et Sass pour les styles. J'ai utilisé les services de Webpack Encore pour compiler Sass.

```sh
git clone git@github.com:mickael-Bula/backOffice.git
cd backOffice
composer install
npm install
npm run build
cp .env .env.local  # fichier de configuration de la BDD
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
php bin/console doctrine:fixtures:load
```

NOTE : l'installation de la BDD se fait avec l'user root par souci d'efficacité. Mais La bonne pratique impose de créer très vite un utilisateur dédié à cette base, que l'on renseignera ensuite dans le fichier .env.local.