<!DOCTYPE html>
<html>
    <head>
        <link rel="icon" src="img/obLogo.png" type="image/x-icon">
        <link rel="stylesheet" href="style.css">
        <script src="script.js"></script>
    </head>

    <body>
        <main id="Connexion">
            <h1>Connectez-vous</h1>
            <form method="post">
                <label>Entrez vos identifiants :</label>
                <input type="text" name="pseudo" id="pseudo" placeholder="Pseudo" minlength="4" required>
                <input type="email" name="mail" id="mail" placeholder="Email" minlength="4" required> 
                <input type="password" name="pw" id="pw" placeholder="Mot de passe" minlength="4" maxlength="20" required>
                <input type="submit" value="Connexion" class="form-submit-button">
                
                <?php// if (!empty($feedback)): ?> <!-- Affiche le message de retour si présent -->
                    <!--<p><?php //echo $feedback; ?></p> -->
                <?//php endif; ?>
            </form>
        </main>
    </body>
</html>