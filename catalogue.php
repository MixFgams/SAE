<?php
$host = 'localhost';
$dbname = 'ob';
$username = 'root';
$password = ''; // Par défaut pour XAMPP/WAMP

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html>
    <head>
        <link rel="icon" src="img/obLogo.png" type="image/x-icon">
        <link rel="stylesheet" href="style.css">
    </head>

    <body id="catalogue">
        <?php include 'pagesOutils/header.php'?>
        <section>
            <form method="GET" action="" id="searchBarCatalogue">
                <input type="text" name="searchBarCatalogue" placeholder="Rechercher un article">
                <button type="submit">Rechercher</button>
            </form>
        </section>
        <main>
            <section id="sortFilter">
                <h3>Tris</h3>
                <form id="radio-list">
                    <div>
                        <input type="radio" name="sort" value="type" onchange="this.form.submit()"
                            <?php if (!isset($_GET['sort']) or strcmp($_GET['sort'], "type") == 0)
                                echo "checked" ;?> />
                        <label>Type de Contenu</label>
                    </div>
                    <div>
                        <input type="radio" name="sort" value="alphabet" onchange="this.form.submit()"
                            <?php if (strcmp($_GET['sort'], "alphabet") == 0)
                                    echo "checked" ;?> />
                        <label>Alphabétique</label>
                    </div>
                    <div>
                        <input type="radio" name="sort" value="genre" onchange="this.form.submit()"
                            <?php if (strcmp($_GET['sort'], "genre") == 0)
                                echo "checked" ;?> />
                        <label>Genre</label>
                    </div>
                </form>
            </section>
            <section class="catalogueDisplay">
                <?php 
                switch($_GET['sort']) {
                    case "type":
                        showContentsByType($pdo) ; 
                        break ;
                    case "alphabet":
                        showContentsByCharacter($pdo) ;
                        break ;
                    case "genre":
                        showContentByGenre($pdo) ;
                        break ;
                    default:
                        showContentsByType($pdo) ; 
                    }?>
            </section>
        </main>
        <?php include 'pagesOutils/footer.php'?>
        <script src="script.js"></script>
    </body>
</html>

<?php
    function getAllContents(PDO $conn) {
        $sql = "SELECT * FROM film" ;

        $stmt = $conn->prepare($sql) ;
        $stmt->execute() ;
        $res = [] ;
        
        if ($stmt->rowCount() > 0)
            $res['film'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $sql = "SELECT * FROM series" ; 
        $stmt = $conn->prepare($sql) ;
        $stmt->execute() ;
        
        if ($stmt->rowCount() > 0)
            $res['series'] = $stmt->fetchAll(PDO::FETCH_ASSOC) ;

        return $res ;
    }

    function getAllContentsSorted(PDO $conn) {
        $sql = "SELECT * FROM film
                ORDER BY `name` ASC" ;

        $stmt = $conn->prepare($sql) ;
        $stmt->execute() ;
        $res = [] ;
        
        if ($stmt->rowCount() > 0)
            $res['film'] = $stmt->fetchAll(PDO::FETCH_BOTH);

        $sql = "SELECT * FROM series
                ORDER BY `name` ASC" ;
        
        $stmt = $conn->prepare($sql) ;
        $stmt->execute() ;
        
        if ($stmt->rowCount() > 0)
            $res['series'] = $stmt->fetchAll(PDO::FETCH_BOTH) ;

        return $res ;
    }

    function getContentsLike(PDO $conn, string $name) {
        if ($name == "")
            return getAllContents($conn) ;

        $sql = "SELECT * FROM film 
                WHERE LOWER(`name`) LIKE CONCAT('%', LOWER(?), '%')" ;
        $stmt = $conn->prepare($sql) ;
        $stmt->bindParam(1, $name) ;
        $stmt->execute() ;
        $res = [] ;
        
        if ($stmt->rowCount() > 0)
            $res['film'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $sql = "SELECT * FROM series 
                WHERE LOWER(`name`) LIKE CONCAT('%', LOWER(?), '%')" ;
        $stmt = $conn->prepare($sql) ;
        $stmt->bindParam(1, $name) ;
        $stmt->execute() ;
        
        if ($stmt->rowCount() > 0)
            $res['series'] = $stmt->fetchAll(PDO::FETCH_ASSOC) ;

        return $res ;
    }

    function getContentsSortedLike(PDO $conn, string $name) {
        if ($name == "")
            return getAllContentsSorted($conn) ;

        $sql = "SELECT * FROM film 
                WHERE LOWER(`name`) LIKE CONCAT('%', LOWER(?), '%')
                ORDER BY `name` ASC" ;
        $stmt = $conn->prepare($sql) ;
        $stmt->bindParam(1, $name) ;
        $stmt->execute() ;
        $res = [] ;
        
        if ($stmt->rowCount() > 0)
            $res['film'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $sql = "SELECT * FROM series 
                WHERE LOWER(`name`) LIKE CONCAT('%', LOWER(?), '%')
                ORDER BY `name` ASC" ;
        $stmt = $conn->prepare($sql) ;
        $stmt->bindParam(1, $name) ;
        $stmt->execute() ;
        
        if ($stmt->rowCount() > 0)
            $res['series'] = $stmt->fetchAll(PDO::FETCH_ASSOC) ;

        return $res ;
    }

    function showContentsByType(PDO $conn) {
        if (!empty($_GET['searchBarCatalogue'])) {
            $searchQuery = htmlspecialchars($_GET['searchBarCatalogue']); // Protection contre XSS
            echo "<h1 class='section-title'>Résultats pour : $searchQuery</h1>";
        }
        else {
            $searchQuery = "" ;
        }

        $contents = getContentsLike($conn, $searchQuery) ;
        $hasContent = false ;
        // Affichage des résultats pour les films
        if (isset($contents['film'])) {
            $hasContent = True ;
            echo "<h2>Films</h2>" ;
            echo "<div class='catalogueSection'>" ;
            foreach($contents['film'] as $row) {
                $id = $row['contentID'];
                $contentName = $row['name'] ;
                $imgURL = $row['posterUrl'] ;
                echo "<div class=catalogueContent>
                        <a href=pageContenu.php?id=$id&type=film>
                            <h3>$contentName</h3>
                            <img src=$imgURL class=\"catalogueImages content-image\" alt=\"Image Catalogue\">
                        </a>
                    </div>
                " ;
            }
            echo "</div>" ;
        } 

        // Affichage des résultats pour les séries
        if (isset($contents['series'])) {
            $hasContent = True ;
            echo "<h2>Séries</h2>" ;
            echo "<div class='catalogueSection'>" ;
            foreach($contents['series'] as $row) {
                $id = $row['contentID'];
                $contentName = $row['name'] ;
                $imgURL = $row['posterUrl'] ;
                echo "<div class=catalogueContent>
                        <a href=pageContenu.php?id=$id&type=series>
                            <h3>$contentName</h3>
                            <img src=$imgURL class=\"catalogueImages content-image\" alt=\"Image Catalogue\">
                        </a>
                    </div>
                " ;
            }
            echo "</div>" ;
        } 
        
        if (!$hasContent) {
            echo "<p class='no-results'>Aucun résultat trouvé pour : $searchQuery. Essayez une autre recherche.</p>";
        }
    }

    function showContentsByCharacter(PDO $conn) {

        if (!empty($_GET['searchBarCatalogue'])) {
            $searchQuery = htmlspecialchars($_GET['searchBarCatalogue']); // Protection contre XSS
            echo "<h1 class='section-title'>Résultats pour : $searchQuery</h1>";
        }
        else {
            $searchQuery = "" ;
        }

        $contents = getContentsSortedLike($conn, $searchQuery) ;
        $i = 0 ; $j = 0 ;
        $previousLetter = null ;
        $currentLetter = null ;

        $filmCount = isset($contents['film']) ? sizeof($contents['film']) : 0 ;
        $seriesCount = isset($contents['series']) ? sizeof($contents['series']) : 0 ;

        if ($filmCount == 0 and $seriesCount == 0) 
         echo "<p class='no-results'>Aucun résultat trouvé pour : $searchQuery. Essayez une autre recherche.</p>";

        while ($i <  $filmCount or $j < $seriesCount) {
            if ($i < $filmCount)
                $film = $contents['film'][$i] ;
            if ($j < $seriesCount)
                $serie = $contents['series'][$j] ;

            if ($j >= $seriesCount or ($i < $filmCount and 
                    strcmp(strtolower($film['name']), strtolower($serie['name'])) <= 0)) {
                $content = $film ;
                $i += 1 ;
            } 
            else {
                $content = $serie ;
                $j += 1 ;
            }
            $currentLetter = mb_substr($content['name'], 0, 1);

            if ($currentLetter != $previousLetter) {
                if ($previousLetter != null) {
                    echo "</div>" ;
                }
                echo "<div>" ;
                echo "<h2>" . strtoupper($currentLetter) . "</h2>" ;
                echo "<div class='catalogueSection'>" ;
            }

            $previousLetter = $currentLetter ;
            $id = $content['contentID'];
            $contentName = $content['name'] ;
            $imgURL = $content['posterUrl'] ;
            echo "<div class=catalogueContent>
                    <a href=pageContenu.php?id=$id&type=series>
                        <h3>$contentName</h3>
                        <img src=$imgURL class=\"catalogueImages content-image\" alt=\"Image Catalogue\">
                    </a>
                </div>
            " ;
        }
        echo "</div></div>" ;
    }

    function showContentByGenre(PDO $conn) {
        $sql = "SELECT * FROM
            (SELECT *, ROW_NUMBER() OVER(PARTITION BY pk_ContentID) AS rownum
            FROM genecontentassociation) as a
            WHERE rownum = 1
            ORDER BY pk_GenreID ASC";
        
        $stmt = $conn->prepare($sql) ;
        $stmt->execute() ;

        $filmQuery = "SELECT * FROM film WHERE contentID = ?" ;
        $serieQuery = "SELECT * FROM series WHERE contentID = ?" ;
        $genreQuery = "SELECT * FROM genre WHERE genreID = ?" ;
        
        if ($stmt->rowCount() > 0) {
            $filmStmt = $conn->prepare($filmQuery) ;
            $seriesStmt = $conn->prepare($serieQuery) ;
            $genreStmt = $conn->prepare($genreQuery) ;

            $previousGenre = null ;
            $currentGenre = null ;
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $type = $row['pk_ContentType'] ;
                $id = $row['pk_ContentID'] ;
                $genreId = $row['pk_GenreID'] ;
                if (strcmp($type, "film") == 0) {
                    $contentStmt = $filmStmt ;
                } 
                else if (strcmp($type, "series") == 0) {
                    $contentStmt = $seriesStmt ;
                }

                $contentStmt->bindParam(1, $id) ;
                $contentStmt->execute() ;
                $content = $contentStmt->fetch() ;
                
                if ($content) {
                    $genreStmt->bindParam(1, $genreId) ;
                    $genreStmt->execute() ;
                    $currentGenre = $genreStmt->fetch(PDO::FETCH_ASSOC)['genreName'] ;
                    if ($currentGenre != $previousGenre) {
                        if ($previousGenre != null) {
                            echo "</div>" ;
                        }
                        echo "<div>" ;
                        echo "<h2>$currentGenre</h2>" ;
                        echo "<div class='catalogueSection'>" ;
                    }
        
                    $previousGenre = $currentGenre ;
                    $contentName = $content['name'] ;
                    $imgURL = $content['posterUrl'] ;
                    echo "<div class=catalogueContent>
                            <a href=pageContenu.php?id=$id&type=series>
                                <h3>$contentName</h3>
                                <img src=$imgURL class=\"catalogueImages content-image\" alt=\"Image Catalogue\">
                            </a>
                        </div>
                    " ;
                }
            }
        }
        
    }
?>
