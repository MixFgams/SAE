<?php 
session_start() ;
include "pageOutils/connexions.php" ;
?>

<!DOCTYPE html>
<html>
    <head>
        <link rel="icon" src="img/obLogo.png" type="image/x-icon">
        <link rel="stylesheet" href="style.css">
    </head>

    <body id="catalogue">
        <?php include 'pagesOutils/header.php'?>
        <?php if (!isset($_POST['sort']) and isset($_SESSION['sort']))
            $_POST['sort'] = $_SESSION['sort'] ;?>
        <section>
            <form method="GET" action="" id="searchBarCatalogue">
                <input type="text" name="searchBarCatalogue" placeholder="Rechercher un article">
                <button type="submit">Rechercher</button>
            </form>
        </section>
        <main>
            <section id="sortFilter">
                <h3>Tris</h3>
                <form id="radio-list" method="POST">
                    <div>
                        <input type="radio" name="sort" value="type" onchange="this.form.submit()"
                            <?php if (!isset($_POST['sort']) or strcmp($_POST['sort'], "type") == 0)
                                echo "checked" ;?> />
                        <label>Type de Contenu</label>
                    </div>
                    <div>
                        <input type="radio" name="sort" value="alphabet" onchange="this.form.submit()"
                            <?php if (strcmp($_POST['sort'], "alphabet") == 0)
                                    echo "checked" ;?> />
                        <label>Alphabétique</label>
                    </div>
                    <div>
                        <input type="radio" name="sort" value="genre" onchange="this.form.submit()"
                            <?php if (strcmp($_POST['sort'], "genre") == 0)
                                echo "checked" ;?> />
                        <label>Genre</label>
                    </div>
                </form>
            </section>
            <section class="catalogueDisplay">
                <?php 
                switch($_POST['sort']) {
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
        <?php if (isset($_POST['sort'])) {
            $_SESSION['sort'] = $_POST['sort'] ;
        }?>
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
                showContentDiv($id, $contentName, $imgURL, $row['contentType']) ;
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
                showContentDiv($id, $contentName, $imgURL, $row['contentType']) ;

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
            showContentDiv($id, $contentName, $imgURL, $content['contentType']) ;

        }
        echo "</div></div>" ;
    }

    function showContentByGenre(PDO $conn) {

        if (!empty($_GET['searchBarCatalogue'])) {
            $searchQuery = htmlspecialchars($_GET['searchBarCatalogue']); // Protection contre XSS
            echo "<h1 class='section-title'>Résultats pour : $searchQuery</h1>";
        }
        else {
            $searchQuery = "" ;
        }
        
        $sql = "SELECT * FROM (SELECT *, ROW_NUMBER() OVER(PARTITION BY pk_ContentID) AS rownum
            FROM genecontentassociation) as a
            WHERE rownum = 1
            ORDER BY pk_GenreID ASC ;";
        
        $stmt = $conn->prepare($sql) ;
        $stmt->execute() ;

        $genreQuery = "SELECT * FROM genre WHERE genreID = ?" ;
        $hasContent = false ;
        if ($stmt->rowCount() > 0) {
            $genreStmt = $conn->prepare($genreQuery) ;

            $previousGenre = null ;
            $currentGenre = null ;

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $type = $row['pk_ContentType'] ;
                $id = $row['pk_ContentID'] ;
                
                $content = getContentIfMatchesName($conn, $id, $searchQuery, $type) ;
                if ($content) {
                    $hasContent = true ;
                    $genreId = $row['pk_GenreID'] ;
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
                    showContentDiv($id, $contentName, $imgURL, $content['contentType']) ;
                }
            }
        }

        if (!$hasContent)
            echo "<p class='no-results'>Aucun résultat trouvé pour : $searchQuery. Essayez une autre recherche.</p>";
    }

    function getContentByID(PDO $conn, int $id, string $type) {

        if (strcmp($type, "film") == 0)
            $query = "SELECT * FROM film WHERE contentID = ?" ;
        else if (strcmp($type, "series") == 0)
            $query = "SELECT * FROM series WHERE contentID = ?" ;

        $stmt = $conn->prepare($query) ;
        $stmt->bindParam(1, $id) ;
        $stmt->execute() ;
        return $stmt->fetch(PDO::FETCH_ASSOC) ;
    }

    function getContentIfMatchesName(PDO $conn, int $id, string $name, string $type) {
        if (strcmp($name, "") == 0)
            return getContentByID($conn, $id, $type) ;

        if (strcmp($type, "film") == 0)
            $query = "SELECT * FROM film WHERE contentID = ? AND `name` LIKE CONCAT('%', LOWER(?), '%')" ;
        else if (strcmp($type, "series") == 0)
            $query = "SELECT * FROM series WHERE contentID = ? AND `name` LIKE CONCAT('%', LOWER(?), '%')" ;
        $stmt = $conn->prepare($query) ;
        $stmt->bindParam(1, $id) ;
        $stmt->bindParam(2, $name) ;
        $stmt->execute() ;

        return $stmt->fetch(PDO::FETCH_ASSOC) ;
    }

    function showContentDiv(int $id, string $contentName, string $imgURL, string $type) {
        echo "<div class=catalogueContent>
                            <a href=pageContenu.php?id=$id&type=$type>
                                <h3>$contentName</h3>
                                <img src=$imgURL class=\"catalogueImages content-image\" alt=\"Image Catalogue\">
                            </a>
                        </div>
                    " ;
    }
?>
