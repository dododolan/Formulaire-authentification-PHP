# Formulaire de connexion

Création de formulaire de connexion en PHP avec une base MySQL, via PHPmyAdmin (XAMP).
Utilisation de PHP.


## Comment ca marche

Le tout tient dans un seul fichier `index.php`. On a trois boutons :

- **Reset** pour vider les champs
- **Valider** pour se connecter
- **Ajout Compte** pour creer un nouveau compte

Une fois connecte, on voit juste son nom et l'heure de connexion, avec un bouton pour se deconnecter.

## Mise en place

Deja il faut importer `setup.sql` dans MySQL, soit en ligne de commande :

```
mysql -u root -p < setup.sql
```

Soit via phpMyAdmin en passant par l'onglet Importer.

Ca va creer la base `identifiants` avec la table et un compte admin par defaut.

Ensuite on met `index.php` et `logo.png` dans le dossier du serveur web et on y accede via le navigateur.

Si les parametres MySQL sont differents (autre utilisateur, mot de passe, etc), il faut les modifier en haut du fichier `index.php`.

## Le logo

Le logo s'affiche en haut du formulaire. Il suffit de mettre une image `logo.png` dans le meme dossier que `index.php`. Si on veut changer le nom du fichier il faut modifier la balise img dans le code.

## Compte par defaut

- Identifiant : admin
- Mot de passe : Azerty1234

## Fichiers

- `index.php` : toute l'application
- `setup.sql` : creation de la base et du compte admin
- `logo.png` : le logo affiche sur la page
- `README.md` : ce fichier
