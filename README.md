# SciLink-Back

Ceci est une application Symfony qui nécessite une configuration initiale pour fonctionner correctement.

## Exigences

- PHP 8.1 ou supérieur
- Symfony 6.3
- Les autres dépendances sont répertoriées dans le fichier `composer.json`

## Configuration

1. Clonez le dépôt et naviguez jusqu'au répertoire du projet.

2. Installez les dépendances en exécutant `composer install`.

3. Créez une copie du fichier `.env` et nommez-la `.env.local`. Modifiez ce fichier pour ajouter vos paramètres de configuration locaux.

4. Créez la base de données et exécutez les migrations en utilisant les commandes `php bin/console doctrine:database:create` et `php bin/console doctrine:migrations:migrate`.

5. Téléchargez le fichier xls depuis [ici](https://www.data.gouv.fr/fr/datasets/structures-de-recherche-publiques-actives/) et placez-le dans le répertoire `data`.

6. Exécutez la commande `php bin/console app:init-database` pour initialiser la base de données avec les données du fichier xls. Vous pouvez ajouter l'option `no-cache` à cette commande si vous souhaitez l'exécuter sans cache. C'est plus léger mais beaucoup plus lent.

Après ces étapes, votre application devrait être prête à fonctionner.

## Exécution de l'application

Pour démarrer l'application, utilisez la commande `symfony server:start`.

## Déploiement

1. Assurez-vous que votre serveur répond aux [exigences](https://symfony.com/doc/current/setup.html#technical-requirements) pour exécuter des applications Symfony.

2. Configurez votre serveur pour pointer vers le répertoire `public/` du projet.

3. Installez les dépendances sur le serveur en exécutant `composer install --no-dev --optimize-autoloader`.

4. Définissez la variable `APP_ENV` dans votre fichier `.env.local` sur `prod`.

5. Effacez et préchauffez le cache Symfony en exécutant `php bin/console cache:clear --no-warmup` et `php bin/console cache:warmup`.

6. Créez la base de données et exécutez les migrations en utilisant les commandes `php bin/console doctrine:database:create` et `php bin/console doctrine:migrations:migrate`.

7. Suivez les étapes de configuration ci-dessus pour initialiser la base de données avec les données du fichier xls.
