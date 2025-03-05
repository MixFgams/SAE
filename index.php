<?php
    session_start();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <link rel="icon" href="img/obLogo.png" type="image/x-icon">
    <link rel="stylesheet" href="refonte.css">
    <title>Ob, Your hobbies in one place</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
<?php
include 'pagesOutils/header.php';
include 'pagesOutils/connDB.php' ;


if(isset($_SESSION['idUser'])) {
    $userID = $_SESSION['idUser'];
} else {
    $userID = 0;
}?>

<main>
    <section id="accueilWelcome">
        <div id="slogan">
            <h2>Ob</h2>
            <h3>Your hobbies in one place</h3>
            <button class="bouttonOrange" id="decouvrir">Découvrir les contenus    <i class="fa-solid fa-arrow-right"></i></button>
        </div>
        <img src="img/mascotte.jpg">
    </section>
    <!-- Section Films Populaires -->
    <section class="SectionIndex">
        <h2>Films populaires</h2>
        <div class="scrollable-container">
            <button class="scroll-button left" aria-label="Défiler à gauche">◀</button>
            <div class="recommendations-scrollable scrollable-content">
                <?php
                $sqlFilmsPopulaires = "SELECT contentID, `name`, posterUrl, viewsCount
                                   FROM film
                                   ORDER BY viewsCount DESC
                                   LIMIT 20"; // Limite de 5 films

                $stmtFilms = $pdo->prepare($sqlFilmsPopulaires);
                $stmtFilms->execute();

                if ($stmtFilms->rowCount() > 0) {
                    while ($row = $stmtFilms->fetch(PDO::FETCH_ASSOC)) {
                        // On récupère l'ID et le type (film)
                        $id   = $row['contentID'];
                        $type = 'film';
                        $url  = htmlspecialchars($row['posterUrl']);
                        $name = htmlspecialchars($row['name']);

                        echo '<div class="recommendation-card">';
                        // On crée un lien vers pageContenu.php?id=...&type=film
                        echo '<a href="pageContenu.php?id=' . $id . '&type=' . $type . '">';
                        echo '<img src="' . $url . '" alt="' . $name . '">';
                        echo '</a>';
                        echo '</div>';
                    }
                } else {
                    echo '<p>Aucun film trouvé.</p>';
                }
                ?>
            </div>
            <button class="scroll-button right" aria-label="Défiler à droite">▶</button>
        </div>
    </section>

    <!-- Section Collections -->
    <section class="SectionIndexCollection">
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
            <button class="bouttonOrange" id="createCollection">+ Créer une collection</button>
        </a>
    </section>

    <!-- Section Séries Populaires -->
    <section class="SectionIndex">
        <h2>Séries populaires</h2>
        <div class="scrollable-container">
            <button class="scroll-button left" aria-label="Défiler à gauche">◀</button>
            <div class="recommendations-scrollable scrollable-content">
                <?php
                $sqlSeriesPopulaires = "SELECT contentID, `name`, posterUrl, viewsCount
                                    FROM series
                                    ORDER BY viewsCount DESC
                                    LIMIT 20"; // Limite de 5 séries

                $stmtSeries = $pdo->prepare($sqlSeriesPopulaires);
                $stmtSeries->execute();

                if ($stmtSeries->rowCount() > 0) {
                    while ($row = $stmtSeries->fetch(PDO::FETCH_ASSOC)) {
                        // On récupère l'ID et le type (series)
                        $id   = $row['contentID'];
                        $type = 'series';
                        $url  = htmlspecialchars($row['posterUrl']);
                        $name = htmlspecialchars($row['name']);

                        echo '<div class="recommendation-card">';
                        // On crée un lien vers pageContenu.php?id=...&type=series
                        echo '<a href="pageContenu.php?id=' . $id . '&type=' . $type . '">';
                        echo '<img src="' . $url . '" alt="' . $name . '">';
                        echo '</a>';
                        echo '</div>';
                    }
                } else {
                    echo '<p>Aucune série trouvée.</p>';
                }
                ?>
            </div>
            <button class="scroll-button right" aria-label="Défiler à droite">▶</button>
        </div>
    </section>


    <!-- Section Forums Populaires -->
    <section class="SectionIndexForum">
        <h2>Forums populaires</h2>
        <div class="forums-container">
            <?php
            $sql = "SELECT *
                    FROM
                    (
                        (SELECT forumTitle, f.description, nbComment, posterUrl
                        FROM forum f
                        JOIN film on film.contentID = f.pk_ContentID
                        JOIN (SELECT contentID as forumID, COUNT(commentID) as nbComment 
                            FROM comment 
                            WHERE pk_ContentType = 'forum'
                            GROUP BY contentID) c
                        ON f.forumID = c.forumID 
                        ORDER BY nbComment DESC)
                        UNION
                        (SELECT forumTitle, f.description, 0 as nbComment, posterUrl
                        FROM forum f
                        JOIN film on film.contentID = f.pk_ContentID
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
                    echo '<img src="' . htmlspecialchars($row['posterUrl']) .'">';
                    echo '<div class="forum-description">';
                    echo '<h2>' . htmlspecialchars($row['forumTitle']) . '</h2>';
                    echo '<p>Description : ' . htmlspecialchars($row['description']) . '</p>'; // Description (optionnel si présent)
                    echo '</div>';
                    echo '</div>';
                }
            } else {
                echo '<p>Aucun forum trouvé.</p>';
            }
            ?>
        </div>
        <button class="bouttonOrange" id="showMoreBtn">Voir plus +</button>
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
                echo '<div class="recommendation-card">';
                echo '<a href="pageContenu.php?id=' . $id . '&type=' . $type . '">';
                echo "<img id='id=$id&type=$type' src='$url' alt='$name'>" ;
                $contentSet[$content['name']] = true ;
                echo '</a>';
                echo '</div>';
            }
        }
    }

    return $contentInserted ;
}
