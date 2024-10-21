<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="connexion.css">
    <title>Connexion</title>
</head>
<body>
    <main>
        <form name="connexionForm" class="connexionWrapper"method="POST">
            <h1>
                Connectez-vous à votre compte
            </h1>
            <input name="login" class="login" type="text" placeholder="Identifiant"/>
            <input name="password" class="password" type="password" placeholder="Mot de passe"/>
            <div class="connexionButton">
                <button name="connexionButton" type="submit">
                    Connexion
                </button>
                <a href="#">Nouvel utilisateur ? Cliquez ici !</a>
            </div>
        </form>
    </main>
    <?php include 'footer.php'?>
</body>
</html>