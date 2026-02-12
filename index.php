<?php
session_start();

$hote = 'localhost';
$base = 'identifiants';
$utilisateur_bdd = 'root';
$mdp_bdd = '';

try {
    $pdo = new PDO("mysql:host=$hote;dbname=$base;charset=utf8mb4", $utilisateur_bdd, $mdp_bdd);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die('Impossible de se connecter a la base de donnees.');
}

$message = '';
$type_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

    if ($_POST['action'] === 'connexion') {
        $identifiant = trim($_POST['identifiant'] ?? '');
        $mot_de_passe = $_POST['mot_de_passe'] ?? '';

        if ($identifiant === '' || $mot_de_passe === '') {
            $message = 'Veuillez remplir tous les champs.';
            $type_message = 'erreur';
        } else {
            $req = $pdo->prepare('SELECT * FROM utilisateurs WHERE identifiant = ?');
            $req->execute([$identifiant]);
            $compte = $req->fetch(PDO::FETCH_ASSOC);

            if ($compte && password_verify($mot_de_passe, $compte['mot_de_passe'])) {
                $_SESSION['utilisateur'] = $compte['identifiant'];
                $_SESSION['heure_connexion'] = date('H:i');
                $message = 'Vous etes connecte.';
                $type_message = 'succes';
            } else {
                $message = 'Erreur. Recommencez.';
                $type_message = 'erreur';
            }
        }
    }

    if ($_POST['action'] === 'ajout_compte') {
        $identifiant = trim($_POST['nouveau_identifiant'] ?? '');
        $mot_de_passe = $_POST['nouveau_mdp'] ?? '';
        $confirmation = $_POST['confirmation_mdp'] ?? '';

        if ($identifiant === '' || $mot_de_passe === '' || $confirmation === '') {
            $message = 'Veuillez remplir tous les champs du formulaire de creation.';
            $type_message = 'erreur';
        } elseif (strlen($identifiant) < 3) {
            $message = 'L\'identifiant doit contenir au moins 3 caracteres.';
            $type_message = 'erreur';
        } elseif (strlen($mot_de_passe) < 4) {
            $message = 'Le mot de passe doit contenir au moins 4 caracteres.';
            $type_message = 'erreur';
        } elseif ($mot_de_passe !== $confirmation) {
            $message = 'Les mots de passe ne correspondent pas.';
            $type_message = 'erreur';
        } else {
            $req = $pdo->prepare('SELECT id FROM utilisateurs WHERE identifiant = ?');
            $req->execute([$identifiant]);
            if ($req->fetch()) {
                $message = 'Cet identifiant est deja utilise.';
                $type_message = 'erreur';
            } else {
                $hash = password_hash($mot_de_passe, PASSWORD_DEFAULT);
                $req = $pdo->prepare('INSERT INTO utilisateurs (identifiant, mot_de_passe) VALUES (?, ?)');
                $req->execute([$identifiant, $hash]);
                $message = 'Compte cree avec succes. Vous pouvez vous connecter.';
                $type_message = 'succes';
            }
        }
    }

    if ($_POST['action'] === 'deconnexion') {
        session_destroy();
        header('Location: index.php');
        exit;
    }
}

$connecte = isset($_SESSION['utilisateur']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: #000;
            color: #888;
            font-family: Arial, Helvetica, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .conteneur {
            width: 380px;
            padding: 20px;
        }

        .carte {
            background: #0a0a0a;
            border: 1px solid #1a1a1a;
            border-radius: 8px;
            padding: 35px 30px;
        }

        .titre {
            color: #aaa;
            font-size: 18px;
            font-weight: 600;
            text-align: center;
            margin-bottom: 24px;
        }

        .logo {
            text-align: center;
            margin-bottom: 20px;
        }

        .logo img {
            max-width: 120px;
            height: auto;
        }

        .champ {
            margin-bottom: 14px;
        }

        .champ label {
            display: block;
            font-size: 12px;
            color: #666;
            margin-bottom: 5px;
        }

        .champ input {
            width: 100%;
            padding: 9px 12px;
            background: #000;
            border: 1px solid #222;
            border-radius: 4px;
            color: #aaa;
            font-family: inherit;
            font-size: 13px;
            outline: none;
        }

        .champ input:focus {
            border-color: #444;
        }

        .champ input::placeholder {
            color: #333;
        }

        .boutons {
            display: flex;
            gap: 8px;
            margin-top: 18px;
        }

        .btn {
            padding: 9px 14px;
            border-radius: 4px;
            font-family: inherit;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            background: #1a1a1a;
            border: 1px solid #2a2a2a;
            color: #888;
        }

        .btn:hover {
            background: #222;
            border-color: #333;
            color: #aaa;
        }

        .btn-valider {
            flex: 1;
        }

        .msg {
            padding: 9px 12px;
            border-radius: 4px;
            font-size: 12px;
            text-align: center;
            margin-bottom: 16px;
        }

        .msg-erreur {
            background: #1a0000;
            border: 1px solid #330000;
            color: #aa3333;
        }

        .msg-succes {
            background: #001a0a;
            border: 1px solid #003315;
            color: #33aa55;
        }

        .panneau-ajout {
            display: none;
            margin-top: 18px;
            padding-top: 18px;
            border-top: 1px solid #1a1a1a;
        }

        .panneau-ajout.visible {
            display: block;
        }

        .panneau-ajout .sous-titre {
            color: #777;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 14px;
            text-align: center;
        }

        .btn-creer {
            width: 100%;
            padding: 9px;
            background: #1a1a1a;
            border: 1px solid #2a2a2a;
            border-radius: 4px;
            color: #888;
            font-family: inherit;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 10px;
        }

        .btn-creer:hover {
            background: #222;
            border-color: #333;
            color: #aaa;
        }

        .btn-annuler {
            width: 100%;
            margin-top: 8px;
            padding: 8px;
            background: transparent;
            border: 1px solid #1a1a1a;
            border-radius: 4px;
            color: #555;
            font-family: inherit;
            font-size: 12px;
            cursor: pointer;
        }

        .btn-annuler:hover {
            border-color: #333;
            color: #888;
        }

        .info-connexion {
            text-align: center;
            color: #aaa;
            font-size: 15px;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .info-detail {
            text-align: center;
            color: #555;
            font-size: 12px;
            margin-bottom: 22px;
        }

        .btn-deconnexion {
            display: block;
            margin: 0 auto;
            padding: 9px 20px;
            background: #1a0000;
            border: 1px solid #330000;
            border-radius: 4px;
            color: #aa3333;
            font-family: inherit;
            font-size: 12px;
            font-weight: 500;
            cursor: pointer;
        }

        .btn-deconnexion:hover {
            background: #220000;
            border-color: #440000;
        }

        .pied {
            text-align: center;
            color: #333;
            font-size: 11px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="conteneur">
        <?php if ($connecte): ?>
            <div class="carte">
                <div class="msg msg-succes">Vous etes connecte.</div>
                <div class="info-connexion"><?php echo htmlspecialchars($_SESSION['utilisateur']); ?></div>
                <div class="info-detail"><?php echo $_SESSION['heure_connexion']; ?></div>
                <form method="post">
                    <input type="hidden" name="action" value="deconnexion">
                    <button type="submit" class="btn-deconnexion">Deconnexion</button>
                </form>
            </div>
        <?php else: ?>
            <div class="carte">
                <div class="logo"><img src="logo.png" alt="Logo"></div>
                <div class="titre">Connexion</div>

                <?php if ($message !== ''): ?>
                    <div class="msg <?php echo $type_message === 'erreur' ? 'msg-erreur' : 'msg-succes'; ?>">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>

                <form method="post" id="formulaire">
                    <input type="hidden" name="action" value="connexion">
                    <div class="champ">
                        <label for="identifiant">Identifiant</label>
                        <input type="text" id="identifiant" name="identifiant" placeholder="Identifiant" required>
                    </div>
                    <div class="champ">
                        <label for="mot_de_passe">Mot de passe</label>
                        <input type="password" id="mot_de_passe" name="mot_de_passe" placeholder="Mot de passe" required>
                    </div>
                    <div class="boutons">
                        <button type="button" class="btn" onclick="document.getElementById('formulaire').reset();">Reset</button>
                        <button type="submit" class="btn btn-valider">Valider</button>
                        <button type="button" class="btn" onclick="document.getElementById('panneauAjout').classList.toggle('visible');">Ajout Compte</button>
                    </div>
                </form>

                <div id="panneauAjout" class="panneau-ajout">
                    <div class="sous-titre">Nouveau compte</div>
                    <form method="post" id="formulaireAjout">
                        <input type="hidden" name="action" value="ajout_compte">
                        <div class="champ">
                            <label for="nouveau_identifiant">Identifiant</label>
                            <input type="text" id="nouveau_identifiant" name="nouveau_identifiant" placeholder="Identifiant" required>
                        </div>
                        <div class="champ">
                            <label for="nouveau_mdp">Mot de passe</label>
                            <input type="password" id="nouveau_mdp" name="nouveau_mdp" placeholder="Mot de passe" required>
                        </div>
                        <div class="champ">
                            <label for="confirmation_mdp">Confirmation</label>
                            <input type="password" id="confirmation_mdp" name="confirmation_mdp" placeholder="Confirmation" required>
                        </div>
                        <button type="submit" class="btn-creer">Creer le compte</button>
                        <button type="button" class="btn-annuler" onclick="document.getElementById('panneauAjout').classList.remove('visible');">Annuler</button>
                    </form>
                </div>
            </div>
        <?php endif; ?>

        <div class="pied">2026 Lucas CORTADA</div>
    </div>
</body>
</html>