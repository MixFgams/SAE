<?php
// Gestion de la profondeur pour des liens corrects dans d'autres dossiers
$profendeur = 0;

if (isset($GLOBALS["profendeurDossier"])) {
    $profendeur = $GLOBALS["profendeurDossier"];
}
?>

<footer>
    <nav class="footer-nav">
        <a href="<?php echo str_repeat("../", $profendeur); ?>index.php">
            <img src="<?php echo str_repeat("../", $profendeur); ?>img/obLogo.png" alt="Logo OB">
        </a>
        <a href="mailto:FaroukMohamed.Bendeddouche@outlook.com">Contacter l'assistance</a>
        <a href="">FAQ</a>
        <a href="<?php echo str_repeat("../", $profendeur); ?>aPropos.php">Conditions et confidentialité</a>
        
        <?php
        if (isset($_SESSION["idUser"]) && $_SESSION["idUser"] != null) {
            echo '<a href="' . str_repeat("../", $profendeur) . 'connexion.php">Deconnexion</a>';
        } else {
            echo '<a href="' . str_repeat("../", $profendeur) . 'connexion.php">Connexion</a>';
        }
        ?>
    </nav>
    <p id="droits-footer">
        @OB 2024 - Tout contenu externe reste la propriété du propriétaire légitime
    </p>
</footer>
