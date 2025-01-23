<!DOCTYPE html>
<html lang="fr">
<head>
    <link rel="icon" href="img/obLogo.png" type="image/x-icon">
    <link rel="stylesheet" href="style.css">
    <title>Ob, Your hobbys in one place</title>
</head>
<body>
<?php
session_start() ;
include 'pagesOutils/header.php';
include 'pagesOutils/connDB.php' ;


if(isset($_SESSION['idUser'])) {
    $userID = $_SESSION['idUser'];
} else {
    $userID = 0;
}?>

<main>
    <!-- Section Forums Populaires -->
    <section class="SectionIndex">
        <h2>Forums populaires</h2>
        <div class="forums-container">
            <?php
            $sql = "SELECT *
                    FROM
                    (
                        (SELECT forumTitle, `description`, nbComment
                        FROM forum f
                        JOIN (SELECT contentID as forumID, COUNT(commentID) as nbComment 
                            FROM comment 
                            WHERE pk_ContentType = 'forum'
                            GROUP BY contentID) c
                        ON f.forumID = c.forumID 
                        ORDER BY nbComment DESC)
                        UNION
                        (SELECT forumTitle, `description`, 0 as nbComment
                        FROM forum f
                        WHERE forumID 
                        NOT IN (SELECT contentID as nbComment 
                            FROM comment 
                            WHERE pk_ContentType = 'forum'
                            GROUP BY contentID))
                    ) as res
                    LIMIT 5;" ; // Ajouter une limite pour éviter un affichage trop long ;

            $stmt = $pdo->prepare($sql);
            $stmt->execute() ;

            if ($stmt->rowCount() > 0) {
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    echo '<div class="forum-item">';
                    echo '<img src="img/afficheFilm.jpg" alt="Image du forum">'; // Ajouter une image par défaut
                    echo '<div class="forum-description">';
                    echo '<h3>' . htmlspecialchars($row['forumTitle']) . '</h3>';
                    echo '<p>Nombre de commentaires : ' . htmlspecialchars($row['nbComment']) . '</p>'; // Ajouter les sujets
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
        <a href="collection.php">
            <h2>Collections</h2>
        </a>
        <div class="scrollable-container">
            <button class="scroll-button left" aria-label="Défiler à gauche">◀</button>
            <div id="ListeCollection" class="scrollable-content">
                <?php
                $sql = "SELECT collectionID, `name` FROM collection
                        WHERE pk_userID = $userID
                        ORDER BY collectionID ASC";
                $stmt = $pdo->prepare($sql);
                $stmt->execute() ;

                if ($stmt->rowCount() > 0) {
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        $id = $row['collectionID'] ;
                        echo "<div id='id=$id' class='Collection'><h3>" . htmlspecialchars($row['name']) . "</h3></div>";
                    }
                } else {
                    echo '<p>Aucune collection trouvée.</p>';
                }
                ?>
            </div>
            <button class="scroll-button right" aria-label="Défiler à droite">▶</button>
        </div>
        <a href="collection.php">
            <button id="createCollection">+ Créer une collection</button>
        </a>
    </section>

    <!-- Section Recommandations -->
    <section class="SectionIndex">
        <h2>Recommandations personnalisées</h2>
        <div class="scrollable-container">
            <button class="scroll-button left" aria-label="Défiler à gauche">◀</button>
            <div class="recommendations-scrollable scrollable-content">
                <?php
                $hasContent = false ;
                if ($userID >= 1) {
                    $hasContent = showRecommendedContents($pdo, $userID) ;
                }

                if (!$hasContent) {
                    echo '<p>Aucune recommandations trouvées.</p>';
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

<?php
function getFavoriteGenres(PDO $conn, int $userID) {
    // Requête récupérant le nombre de contenus
    $sql = "SELECT pk_GenreID, genreName, COUNT(g.pk_ContentID) as nb from genecontentassociation g
            JOIN (SELECT pk_UserID, f.filmID as contentID, 'film' as contentType FROM filmwatched f
                UNION
                SELECT pk_UserID, s.pk_SeriesID as contentID, 'series' as contentType FROM serieswatched s) c 
        ON c.contentID = g.pk_ContentID AND c.pk_UserID = ? AND g.pk_ContentType = c.contentType
        JOIN genre ge ON g.pk_GenreID = ge.genreID
        GROUP BY g.pk_GenreID
        ORDER BY nb DESC, genreName 
        LIMIT 5;" ;

    $stmt = $conn->prepare($sql) ;
    $stmt->bindParam(1, $userID) ;
    $stmt->execute() ;
    $genres = $stmt->fetchAll(PDO::FETCH_ASSOC) ;
    return $genres ;
}

function showRecommendedContents(PDO $conn, int $userID) {
    $genres = getFavoriteGenres($conn, $userID) ;
    $contentInserted = false ;
    $sql = "SELECT contentID, `name`, posterURL, contentType FROM 
            (SELECT contentID, `name`, posterURL, contentType from series
            UNION
            SELECT contentID, `name`, posterURL, contentType from film) u
        JOIN genecontentassociation g
        ON u.contentID = g.pk_ContentID AND u.contentType = g.pk_ContentType 
        WHERE g.pk_GenreID = ? AND u.contentID NOT IN
            (SELECT pk_SeriesID as contentID from serieswatched
            WHERE pk_userID = $userID
            UNION
            SELECT filmID as contentID from filmwatched
            WHERE pk_userID = $userID) 
        ORDER BY RAND()
        LIMIT 3;" ;
    
    $stmt = $conn->prepare($sql) ;
    $contentSet = [] ;
    foreach ($genres as $genre) {
        $stmt->bindParam(1, $genre['pk_GenreID']) ;
        $stmt->execute() ;
        $contents = $stmt->fetchAll(PDO::FETCH_ASSOC) ;
        foreach ($contents as $content) {
            if (!isset($contentSet[$content['name']]) or !$contentSet[$content['name']]) {
                $contentInserted = true ;
                $id = htmlspecialchars($content['contentID']) ;
                $name = htmlspecialchars($content['name']) ;
                $url = htmlspecialchars($content['posterURL']) ;
                $type = htmlspecialchars($content['contentType']) ;

                echo "<img id='id=$id&type=$type' src='$url' alt='$name'>" ;
                $contentSet[$content['name']] = true ;
            }
        }
    }

    return $contentInserted ;
}
