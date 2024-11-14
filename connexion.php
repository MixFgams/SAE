<!DOCTYPE html>
<html>
    <head>
        <link rel="icon" href="img/obLogo.png" type="image/x-icon">
        <meta charset="UTF-8">
        <link rel="stylesheet" href="style.css">
        <link rel="stylesheet" href="connexion.css">
        <script src="script.js"></script>
    </head>

    <body>
        <main id="connexion">
            <form method="post">

                <div id="connexion-form">
                    <label>Entrez vos identifiants :</label>
                    
                    <!-- Choix entre pseudo et email-->
                    <div>
                        <button type="button" onclick="connexionPseudo()">Pseudo</button>
                        <button type="button" onclick="connexionEmail()">Email</button>
                    </div>

                    <!-- entrée par pseudo -->
                    <input type="text" name="connexion-pseudo" id="connexion-pseudo" placeholder="Pseudo" minlength="4" required style="display: none;">

                    <!-- entrée par email -->
                    <input type="email" name="connexion-email" id="connexion-email" placeholder="Email" minlength="4" required style="display: none;">

                    <input type="password" name="connexion-mp" id="connexion-mp" placeholder="Mot de passe" minlength="8" maxlength="20" required>
                    <input type="submit" value="Connexion">

                    <button type="button" onclick="montrerInscription()">Première fois sur ce site ?</button>
                </div>

                <div id="inscription-form" style="display: none;">
                    <label>Entrez vos identifiants :</label>
                    <input type="text" name="inscription-pseudo" id="inscription-pseudo" placeholder="Pseudo" minlength="4" required>
                    <input type="email" name="inscription-mail" id="inscription-mail" placeholder="Email" minlength="4" required>
                    <input type="password" name="inscription-mp" id="inscription-mp" placeholder="Mot de passe" minlength="4" maxlength="20" required>
                    <input type="submit" value="Inscription">
                    <button type="button" onclick="montrerConnexion()">Vous avez déjà un compte ?</button>
                </div>

            </form>
            <script src="javaScriptExtra/connexion.js"></script>
        </main>
    </body>
</html>
