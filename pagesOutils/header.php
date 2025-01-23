<?php
// Connexion à la base de données
include "connDB.php" ;

// Vérifier si une recherche est effectuée
if (isset($_GET['query']) && !empty($_GET['query'])) {
    $query = htmlspecialchars($_GET['query']);
    $sql = "SELECT contentID, name, posterURL, contentType FROM film WHERE name LIKE :query LIMIT 10";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['query' => "%$query%"]);

    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Envoyer uniquement le JSON
    header('Content-Type: application/json');
    echo json_encode($results);
    exit;
}
?>


<!DOCTYPE html>
<html lang="fr">
    <head>
        <title>header</title>


    </head>
    <header>
        <nav class="header-nav">
            <a href="index.php"><img src="img/obLogo.png" alt="Logo OB"></a>
            <div class="search-container">
                <input type="text" id="searchInput" placeholder="Rechercher un film...">
                <div id="suggestions" class="suggestions-box"></div>
            </div>
            <a href="index.php">Accueil</a>
            <a href="communaute.php">Communauté</a>
            <a href="catalogue.php">Catalogue</a>
            <a href="aPropos.php">À propos</a>

            <!--A mettre dans le cas ou l'utilisateur n'est pas connecté-->
            <a href="connexion.php">Connexion</a>

            <!--A mettre seulement si l'utilisateur est connecté-->
            <a href="profile.php">Votre Profil</a>
        </nav>
    </header>
</html>

