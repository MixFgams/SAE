<?php
    session_start();
    include "pagesOutils/connDB.php";
    
    $_SESSION["idUser"] = null;
    $_SESSION["userType"] = null;

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Récupération de l'action
        $action = $_POST['typeForm'] ?? null;
    
        if ($action == "connexion") {
            // Traitement pour la connexion
            $pseudo = $_POST['pseudo-c'] ?? null;
            $email = $_POST['email-c'] ?? null;
            $motDePasse = $_POST['mp-c'] ?? null;
    
            if ($pseudo != null) {
                $stmt = $pdo->prepare("SELECT userID, userType, password FROM user WHERE username = :username");
    
                $stmt->execute([
                    ":username" => $pseudo
                ]);
    
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
                if ($result != null && password_verify($motDePasse, $result['password'])) {
                    // Mot de passe vérifié
                    $_SESSION["userType"] = $result["userType"];
                    $_SESSION["idUser"] = $result["userID"];
    
                    header("Location: index.php");
                    exit();
                }
    
                $error = "Identifiant ou mot de passe incorrect";
            } elseif ($email != null) {
                $stmt = $pdo->prepare("SELECT userID, userType, password FROM user WHERE email = :email");
    
                $stmt->execute([
                    ":email" => $email
                ]);
    
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
                if ($result != null && password_verify($motDePasse, $result['password'])) {
                    // Mot de passe vérifié
                    $_SESSION["userType"] = $result["userType"];
                    $_SESSION["idUser"] = $result["userID"];
    
                    header("Location: index.php");
                    exit();
                }
    
                $error = "Identifiant ou mot de passe incorrect";
            } else {
                $error = "Veuillez insérer un identifiant ou un email";
            }
    
        } elseif ($action == "inscription") {
            // Traitement pour l'inscription
            $pseudo = $_POST['pseudo-i'] ?? null;
            $email = $_POST['mail-i'] ?? null;
            $motDePasse = $_POST['mp-i'] ?? null;
    
            // Vérification si le pseudo ou l'email existe déjà
            $stmt = $pdo->prepare("SELECT userID FROM user WHERE username = :username");
            $stmt->execute([":username" => $pseudo]);
            $resultP = $stmt->fetch(PDO::FETCH_ASSOC);
    
            $stmt = $pdo->prepare("SELECT userID FROM user WHERE email = :email");
            $stmt->execute([":email" => $email]);
            $resultE = $stmt->fetch(PDO::FETCH_ASSOC);
    
            if ($resultE == null && $resultP == null) {
                if ($pseudo && $email && $motDePasse) {
                    // Hachage du mot de passe
                    $motDePasseHache = password_hash($motDePasse, PASSWORD_DEFAULT);
    
                    // Insertion dans la base de données
                    $stmt = $pdo->prepare("INSERT INTO user(username, email, password) VALUES (:username, :email, :password)");
                    $stmt->execute([
                        ":username" => $pseudo,
                        ":email" => $email,
                        ":password" => $motDePasseHache
                    ]);
    
                    $_SESSION["idUser"] = $pdo->lastInsertId();
                    $_SESSION["userType"] = "user";
    
                    header("Location: index.php");
                    exit();
                } else {
                    $error = "Erreur : données manquantes pour l'inscription.";
                }
            } else {
                $error = "Identifiant ou email déjà pris";
            }
        } else {
            $error = "Erreur : action non reconnue.";
        }
    }

?>

<!DOCTYPE html>
<html>
    <head>
        <link rel="icon" href="img/obLogo.png" type="image/x-icon">
        <meta charset="UTF-8">
        <title>LogIn - OB</title>
        <link rel="stylesheet" href="style.css">
        <link rel="stylesheet" href="cssExtraPages/connexion.css">
    </head>

    <body id="connexion-body">
        <?php include "pagesOutils/header.php"?>

        <main id="connexion-main">
            <h1>Connexion</h1>
            <section>
                <form method="post" action="">

                    <div id="connexion-form">
                        
                        <!-- Choix entre pseudo et email-->
                        <div>

                            <button type="button" onclick="connexionPseudo()" class="connexion-selection-button">Pseudo</button>
                            <button type="button" onclick="connexionEmail()">Email</button>

                        </div>

                        <!-- entrée par pseudo -->
                        <input type="text" name="pseudo-c" id="connexion-pseudo" placeholder="Pseudo">

                        <!-- entrée par email -->
                        <input type="email" name="email-c" id="connexion-email" placeholder="Email">

                        
                        <input type="password" name="mp-c" id="connexion-mp" placeholder="Mot de passe">
                        <input type="submit" name ="typeForm" value="connexion">

                        <button type="button" onclick="montrerInscription()">Première fois sur ce site ?</button>
                    </div>

                    <div id="inscription-form">
                        
                        <input type="text" name="pseudo-i" id="inscription-pseudo" placeholder="Pseudo">
                        <input type="email" name="mail-i" id="inscription-mail" placeholder="Email">
                        <input type="password" name="mp-i" id="inscription-mp" placeholder="Mot de passe">
                        
                        <input type="submit" name ="typeForm" value="inscription">

                        <button type="button" onclick="montrerConnexion()">Vous avez déjà un compte ?</button>
                    </div>

                </form>
                <?php if (!empty($error)): ?> <!-- Affiche le message de retour si présent -->
                    <p><?php echo $error; ?></p>
                <?php endif; ?>
                <script src="javaScriptExtra/connexion.js"></script>
            </main>
        </section>
        <?php include "pagesOutils/footer.php"?>

        <script src="script.js"></script>
    </body>
</html>
