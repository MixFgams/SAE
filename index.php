<!DOCTYPE html>
<html lang="fr">
<head>
    <link rel="icon" href="img/obLogo.png" type="image/x-icon">
    <link rel="stylesheet" href="style.css">
    <title>Ob, Your hobbys in one place</title>
</head>
<body>
<?php
session_start();
if(isset($_SESSION['userID'])) {
    $userID = $_SESSION['userID'];
} else {
    $userID = 1;
}


// Connexion à la base de données avec PDO
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
?>

<?php include 'pagesOutils/header.php'; ?>

<main>
    <!-- Section Forums Populaires -->
    <section class="SectionIndex">
        <h2>Forums populaires</h2>
        <div class="forums-container">
            <?php
            $sql = "SELECT forumTitle, forum.description, totalSubjectNumber, posterUrl
                FROM forum 
                join film on film.contentID = forum.pk_ContentID
                ORDER BY totalSubjectNumber DESC 
                LIMIT 5"; // Ajouter une limite pour éviter un affichage trop long

            $stmt = $pdo->query($sql);

            if ($stmt->rowCount() > 0) {
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    echo '<div class="forum-item">';
                    echo '<img src="' . htmlspecialchars($row['posterUrl']) .'">';
                    echo '<div class="forum-description">';
                    echo '<h3>' . htmlspecialchars($row['forumTitle']) . '</h3>';
                    echo '<p>Nombre de sujets : ' . htmlspecialchars($row['totalSubjectNumber']) . '</p>'; // Ajouter les sujets
                    echo '<p>Description : ' . htmlspecialchars($row['description']) . '</p>'; // Description (optionnel si présent)
                    echo '</div>';
                    echo '</div>';
                }
            } else {
                echo '<p>Aucun forum trouvé.</p>';
            }
            ?>
        </div>
        <button id="showMoreBtn">Voir plus +</button>
    </section>

    <!-- Section Collections -->
    <section class="SectionIndex">
        <h2>Collections</h2>
        <div class="scrollable-container">
            <button class="scroll-button left" aria-label="Défiler à gauche">◀</button>
            <div id="ListeCollection" class="scrollable-content">
                <?php
                $sql = "SELECT name FROM collection ORDER BY collectionID ASC";
                $stmt = $pdo->query($sql);

                if ($stmt->rowCount() > 0) {
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        echo '<div class="Collection"><h3>' . htmlspecialchars($row['name']) . '</h3></div>';
                    }
                } else {
                    echo '<p>Aucune collection trouvée.</p>';
                }
                ?>
            </div>
            <button class="scroll-button right" aria-label="Défiler à droite">▶</button>
        </div>
        <button id="createCollection">+ Créer une collection</button>
    </section>

    <!-- Section Recommandations -->
    <section class="SectionIndex">
        <h2>Recommandations personnalisées</h2>
        <div class="scrollable-container">
            <button class="scroll-button left" aria-label="Défiler à gauche">◀</button>
            <div class="recommendations-scrollable scrollable-content">
                <?php
                $sql = "SELECT DISTINCT f.contentID, f.name, f.posterUrl
            FROM film f 
            LIMIT 7";
                $stmt = $pdo->query($sql);
                if ($stmt->rowCount() > 0) {
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        // Utiliser pk_ContentType comme data-id dynamique
                        echo '<div class="recommendation-card" data-id="' . htmlspecialchars($row['contentID']) . '">';
                        echo '<img src="' . htmlspecialchars($row['posterUrl']) . '" alt="' . htmlspecialchars($row['name']) . '">';
                        echo '<div class="eye-icon">';
                        echo '<img src="img/eye-icon.png" alt="Voir">';
                        echo '</div>';
                        echo '</div>';
                    }
                } else {
                    echo '<p>Aucune recommandation trouvée.</p>';
                }
                ?>
            </div>

            <button class="scroll-button right" aria-label="Défiler à droite">▶</button>
        </div>
    </section>

    <?php

    // Vérifiez si un filmId est passé dans l'URL
    if (isset($_GET['filmId'])) {
        $filmId = intval($_GET['filmId']);

        // Vérifier si l'entrée existe déjà dans la base
        try {
            // Vérification de l'existence de la ligne
            $stmt = $pdo->prepare('SELECT COUNT(*) FROM filmwatched WHERE pk_UserID = :user_id AND filmID = :film_id');
            $stmt->execute([
                ':user_id' => $userID,
                ':film_id' => $filmId
            ]);
            $exists = $stmt->fetchColumn();
            if ($exists > 0) {
                echo '';
            } else {
                // Si l'entrée n'existe pas, on l'ajoute
                $stmt = $pdo->prepare('INSERT INTO filmwatched VALUES (:film_id,:user_id)');
                $stmt->execute([
                    ':film_id' => $filmId,
                    ':user_id' => $userID
                ]);
                echo 'Film ajouté avec succès !';
            }
        } catch (PDOException $e) {
            echo 'Erreur : ' . $e->getMessage();
        }
    }
    ?>
</main>

<?php
// Inclusion du fichier footer
include 'pagesOutils/footer.php';
?>
<script src="script.js">
</script>

</body>
</html>
