<?php 
session_start() ;
include 'pagesOutils/connDB.php' ;
?>

<!DOCTYPE html>
<html>
    <head>
        <link rel="icon" src="img/obLogo.png" type="image/x-icon">
        <link rel="stylesheet" href="refonte.css">
    </head>

    <body id="catalogue">
        <?php include 'pagesOutils/header.php'?>
        <?php if (!isset($_POST['sort']) and isset($_SESSION['sort']))
                $_POST['sort'] = $_SESSION['sort'] ;
            else if (!isset($_SESSION['sort'])) {
                $_POST['sort'] = "type" ;
            } ?>  
        <section>
            <form method="GET" action="" id="searchBarCatalogue">
                <div class="search">
                    <label for="searchBarCatalogue" >Rechercher un contenu :</label>
                    <input type="text" id="barreRecherche" name="searchBarCatalogue" class="search__input" placeholder="Type your text">
                    <button class="search__button">
                        <svg class="search__icon" aria-hidden="true" viewBox="0 0 24 24">
                            <g>
                                <path d="M21.53 20.47l-3.66-3.66C19.195 15.24 20 13.214 20 11c0-4.97-4.03-9-9-9s-9 4.03-9 9 4.03 9 9 9c2.215 0 4.24-.804 5.808-2.13l3.66 3.66c.147.146.34.22.53.22s.385-.073.53-.22c.295-.293.295-.767.002-1.06zM3.5 11c0-4.135 3.365-7.5 7.5-7.5s7.5 3.365 7.5 7.5-3.365 7.5-7.5 7.5-7.5-3.365-7.5-7.5z"></path>
                            </g>
                        </svg>
                    </button>
                </div>
            </form>
        </section>
        <main id="pageCatalogue">
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
                if (isset($_POST['sort'])) {
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
                    }
                } 

                else {
                    showContentsByType($pdo) ; 
                }?>
            </section>
        </main>
        <?php if (isset($_POST['sort'])) {
            $_SESSION['sort'] = $_POST['sort'] ;
        }?>
        <?php include 'pagesOutils/footer.php'?>
        <script src="catalogue.js"></script>
        <script src="script.js"></script>
    </body>
</html>

<?php
    function getAllContents(PDO $conn) {
        $res = [] ;
        $sql = "SELECT contentID, `name`, posterUrl, contentType FROM film" ;

        $stmt = $conn->prepare($sql) ;
        $stmt->execute() ;
        
        if ($stmt->rowCount() > 0)
            $res['film'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $sql = "SELECT contentID, `name`, posterUrl, contentType FROM series" ; 
        $stmt = $conn->prepare($sql) ;
        $stmt->execute() ;
        
        if ($stmt->rowCount() > 0)
            $res['series'] = $stmt->fetchAll(PDO::FETCH_ASSOC) ;

        return $res ;
    }

    function getAllContentsSorted(PDO $conn) {
        $sql = "SELECT * FROM
            (
            SELECT contentID, `name`, posterUrl, contentType FROM film
            UNION
            SELECT contentID, `name`, posterUrl, contentType FROM series
            ) as res
        ORDER BY `name` ASC ;" ;

        $stmt = $conn->prepare($sql) ;
        $stmt->execute() ;

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ;
    }

    function getContentsLike(PDO $conn, string $name) {
        if ($name === "")
            return getAllContents($conn) ;

        $res = [] ;
        $sql = "SELECT contentID, `name`, posterUrl, contentType FROM film
                WHERE LOWER(`name`) LIKE CONCAT('%', LOWER(?), '%')
                ORDER BY `name` ASC" ;

        $stmt = $conn->prepare($sql) ;
        $stmt->bindParam(1, $name) ;
        $stmt->execute() ;
        
        if ($stmt->rowCount() > 0)
            $res['film'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $sql = "SELECT contentID, `name`, posterUrl, contentType FROM series
                WHERE LOWER(`name`) LIKE CONCAT('%', LOWER(?), '%')
                ORDER BY `name` ASC" ; 
               
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

        $sql = "SELECT * FROM
            (
            SELECT contentID, `name`, posterUrl, contentType FROM film
            UNION
            SELECT contentID, `name`, posterUrl, contentType FROM series
            ) as res
        WHERE LOWER(`name`) LIKE CONCAT('%', LOWER(?), '%')
        ORDER BY `name` ASC ;" ;

        $stmt = $conn->prepare($sql) ;
        $stmt->bindParam(1, $name) ;
        $stmt->execute() ;

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ;
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
            echo "<h2>Films</h2>";
            echo "<div class='catalogueSection'>" ;
            $contentCount = 0 ;
            foreach($contents['film'] as $row) {
                $id = $row['contentID'];
                $contentName = $row['name'] ;
                $imgURL = $row['posterUrl'] ;
                $contentCount += 1 ;
                showContentDiv($id, $contentName, $imgURL, $row['contentType'], "film", $contentCount, 8) ;
            }
            if ($contentCount > 8)
                echo "<div id='divVoirTout'>";
                echo "<button param='series' id='voirTout' class='bouttonOrange'>Voir Tout</button>" ;
                echo "</div>";
            echo "</div>" ;
        } 

        // Affichage des résultats pour les séries
        if (isset($contents['series'])) {
            $hasContent = True ;
            echo "<h2>Séries</h2>" ;
            echo "<div class='catalogueSection'>" ;
            $contentCount = 0 ;
            foreach($contents['series'] as $row) {
                $id = $row['contentID'];
                $contentName = $row['name'] ;
                $imgURL = $row['posterUrl'] ;
                $contentCount += 1 ;
                showContentDiv($id, $contentName, $imgURL, $row['contentType'], "series", $contentCount, 8) ;

            }
            if ($contentCount > 6)
                echo "<div id='divVoirTout'>";
                echo "<button param='series' id='voirTout' class='bouttonOrange'>Voir Tout</button>" ;
                echo "</div>";
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
        $i = 0 ;
        $previousLetter = null ;
        $currentLetter = null ;

        if (!$contents) 
            echo "<p class='no-results'>Aucun résultat trouvé pour : $searchQuery. Essayez une autre recherche.</p>";

        else {
            $contentCount = 0 ;
            foreach ($contents as $content) {
                
                $currentLetter = strtoupper(mb_substr($content['name'], 0, 1));
    
                if ($currentLetter != $previousLetter) {
                    if ($contentCount > 4) {
                        echo "<button param='series' id='voirTout' class='bouttonOrange'>Voir Tout</button>" ;
                    }

                    if ($previousLetter != null) {
                        echo "</div>" ;
                    }
                    
                    $contentCount = 0 ;
                    echo "<div>" ;
                    echo "<h2>" . strtoupper($currentLetter) . "</h2>" ;
                    echo "<div class='catalogueSection'>" ;
                }
                
                $contentCount += 1 ;
                $previousLetter = $currentLetter ;
                $id = $content['contentID'];
                $contentName = $content['name'] ;
                $imgURL = $content['posterUrl'] ;
                showContentDiv($id, $contentName, $imgURL, $content['contentType'], $currentLetter, $contentCount, 4) ;
    
            }

            if ($contentCount > 4)
                echo "<button param='series' id='voirTout' class='bouttonOrange'>Voir Tout</button>" ;
            echo "</div></div>" ;
        }
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

            $contentCount = 0 ;
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $type = $row['pk_ContentType'] ;
                $id = $row['pk_ContentID'] ;
                
                $content = getContentByIDIfNameMatches($conn, $id, $type, $searchQuery) ;
                if ($content) {
                    $hasContent = true ;
                    $genreId = $row['pk_GenreID'] ;
                    $genreStmt->bindParam(1, $genreId) ;
                    $genreStmt->execute() ;

                    $currentGenre = $genreStmt->fetch(PDO::FETCH_ASSOC)['genreName'] ;
                    if ($currentGenre != $previousGenre) {
                        if ($contentCount > 4) {
                            echo "<button param='series' id='voirTout' class='bouttonOrange'>Voir Tout</button>" ;
                        }
                        
                        if ($previousGenre != null) {
                            echo "</div>" ;
                        }

                        $contentCount = 0 ;
                        echo "<div>" ;
                        echo "<h2>$currentGenre</h2>" ;
                        echo "<div class='catalogueSection'>" ;
                    }
                    
                    $contentCount += 1 ;
                    $previousGenre = $currentGenre ;
                    $contentName = $content['name'] ;
                    $imgURL = $content['posterUrl'] ;
                    showContentDiv($id, $contentName, $imgURL, $content['contentType'], $currentGenre, $contentCount, 4) ;
                }
            }
        }

        if (!$hasContent)
            echo "<p class='no-results'>Aucun résultat trouvé pour : $searchQuery. Essayez une autre recherche.</p>";
        else {
            if ($contentCount > 4)
                echo "<button param='series' id='voirTout' class='bouttonOrange'>Voir Tout</button>" ;
            echo "</div>" ;
        }
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

    function getContentByIDIfNameMatches(PDO $conn, int $id, string $type, string $name) {
        if ($name === "")
            return getContentByID($conn, $id, $type) ;
        if (strcmp($type, "film") == 0)
            $query = "SELECT * FROM film WHERE contentID = ? AND LOWER(`name`) LIKE CONCAT('%', LOWER(?), '%')" ;
        else if (strcmp($type, "series") == 0)
            $query = "SELECT * FROM series WHERE contentID = ? AND LOWER(`name`) LIKE CONCAT('%', LOWER(?), '%')" ;

        $stmt = $conn->prepare($query) ;
        $stmt->bindParam(1, $id) ;
        $stmt->bindParam(2, $name) ;
        $stmt->execute() ;
        return $stmt->fetch(PDO::FETCH_ASSOC) ;
    }

    function showContentDiv(int $id, string $contentName, string $imgURL, string $type, string $divClass, int $contentCount, int $contentLimit) {
        if ($contentCount <= $contentLimit)
            echo "<div class=catalogueContent>" ;
        else
            echo "<div class='catalogueContent $divClass invisible'>" ;

        echo "<a href=pageContenu.php?id=$id&type=$type>" ;
        echo "<h3>$contentName</h3>" ;
        echo "<img src=$imgURL class=\"catalogueImages content-image\" alt=\"Image Catalogue\">" ;
        echo "</a>" ;
        echo "</div>" ;
    }
?>
