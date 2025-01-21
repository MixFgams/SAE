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

<?php include 'pagesOutils/header.php'; 

if(isset($_SESSION['userID'])) {
    $userID = $_SESSION['userID'];
} else {
    $userID = 1;
}?>

<main>
    <!-- Section Forums Populaires -->
    <section class="SectionIndex">
        <h2>Forums populaires</h2>
        <div class="forums-container">

            <?php
            $sql = "SELECT forumTitle
                    FROM forum 
                    ORDER BY totalSubjectNumber DESC
                    ";

            $stmt = $pdo->query($sql);

            if ($stmt->rowCount() > 0) {
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    echo '<div class="forum-item">';
                    echo '<h3> ' . htmlspecialchars($row['forumTitle']) . ' </h3>';

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
                $hasContent = false ;
                if ($userID >= 1) {
                    $hasContent = showRecommendedContents($pdo, $userID) ;
                }

                if (!$hasContent) {
                    echo '<p>Aucune recommandation trouvée.</p>';
                }
                ?>
            </div>
            <button class="scroll-button right" aria-label="Défiler à droite">▶</button>
        </div>
    </section>
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
            if (!$contentSet[$content['name']]) {
                $contentInserted = true ;
                $id = htmlspecialchars($content['contentID']) ;
                $name = htmlspecialchars($content['name']) ;
                $url = htmlspecialchars($content['posterURL']) ;
                $type = htmlspecialchars($content['contentType']) ;

                echo "<img src='$url' alt='$name'>" ;
                $contentSet[$content['name']] = true ;
            }
        }
    }

    return $contentInserted ;
}
