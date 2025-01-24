<?php
// Connexion à la base de données
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ob";

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// Vérifier si une recherche est effectuée
if (isset($_GET['query']) && !empty($_GET['query'])) {
    $query = htmlspecialchars($_GET['query']);
    $sql = "SELECT ContentID, name FROM film WHERE name LIKE :query LIMIT 10";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['query' => "%$query%"]);

    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Envoyer uniquement le JSON
    header('Content-Type: application/json');
    echo json_encode($results);
    exit; // Terminer immédiatement pour éviter tout ajout HTML
}


//gestion de la profondeur pour des liens correctes dans d'autre dossiers
$profendeur = 0;

if (isset($GLOBALS["profendeurDossier"])) {
    $profendeur = $GLOBALS["profendeurDossier"];
}

?>
<header>
    <nav class="header-nav">
        <a href="<?php echo str_repeat('../', $profendeur); ?>index.php">
            <img src="<?php echo str_repeat('../', $profendeur); ?>img/obLogo.png" alt="Logo OB">
        </a>
        <div class="search-container">
            <input type="text" id="searchInput" placeholder="Rechercher un film...">
            <div id="suggestions" class="suggestions-box"></div>
        </div>
        <a href="<?php echo str_repeat('../', $profendeur); ?>index.php">Accueil</a>
        <a href="<?php echo str_repeat('../', $profendeur); ?>communaute.php">Communauté</a>
        <a href="<?php echo str_repeat('../', $profendeur); ?>catalogue.php">Catalogue</a>
        <a href="<?php echo str_repeat('../', $profendeur); ?>aPropos.php">À propos</a>

        <?php
        if (isset($_SESSION["idUser"]) && $_SESSION["idUser"] != null) {
            echo '<a href="' . str_repeat('../', $profendeur) . 'connexion.php">Deconnexion</a>';
            // Lien vers le profil uniquement si l'utilisateur est connecté
            echo '<a href="' . str_repeat('../', $profendeur) . 'profile.php">Votre Profil</a>';
        } else {
            echo '<a href="' . str_repeat('../', $profendeur) . 'connexion.php">Connexion</a>';
        }
        ?>
    </nav>
</header>


