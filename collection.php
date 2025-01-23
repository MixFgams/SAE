<?php 
session_start() ;
include 'pagesOutils/connDB.php' ;

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Collection</title>
</head>
<body>
    <?php include("pagesOutils/header.php") ;
    if (isset($_SESSION['idUser'])) {
        $userID = $_SESSION['idUser'] ;
    } else {
        $userID = 0 ;
    } ?>
    <main>
        <?php
        if (!isset($_GET['collectionID'])) {
            showGeneralPage($pdo, $userID) ;
        }
        else {
            $collectionID = intval($_GET['collectionID']) ;
            $collectionNameQuery = "SELECT `name` FROM `collection`
                                    WHERE collectionID = ? AND pk_userID = ?" ;
    
            $stmtName = $pdo->prepare($collectionNameQuery) ;
            $stmtName->bindParam(1, $collectionID) ;
            $stmtName->bindParam(2, $userID) ;
            $stmtName->execute() ;

            $collectionName = $stmtName->fetch(PDO::FETCH_COLUMN) ;
            if ($collectionName) {
                showCollection($pdo, $collectionID, $collectionName) ;
            }
        }
        ?>
    </main>
    <script src="script.js"></script>
</body>
</html>

<?php 
function showGeneralPage(PDO $conn, int $userID) {

    $sql = "SELECT collectionID, `name` from collection c
    WHERE pk_userID = ? ;" ;
    $stmt = $conn->prepare($sql) ;
    $stmt->bindParam(1, $userID) ;
    $stmt->execute() ;

    $contentQuery = "SELECT u.contentID, u.posterUrl, u.`name`, u.contentType FROM
            (SELECT contentID, posterUrl, `name`, contentType FROM film
            UNION
            SELECT contentID, posterUrl, `name`, contentType FROM series) u
        JOIN contentcollection cc ON u.contentID = cc.contentID AND u.contentType = cc.pk_ContentType
        WHERE cc.pk_collectionID = ? ;" ;
    
    $stmtContent = $conn->prepare($contentQuery) ;
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $collectionName = $row['name'] ; 
        $collectionID = $row['collectionID'] ;

        $stmtContent->bindParam(1, $collectionID) ;
        $stmtContent->execute() ;
        $result = $stmtContent->fetchAll(PDO::FETCH_ASSOC) ;

        echo "<section class='SectionIndex'>
                    <a href='collection.php?collectionID=$collectionID'><h2>$collectionName</h2></a>
                    <div class='scrollable-container'>
                        <button class='scroll-button left' aria-label='Défiler à gauche'>◀</button>
                        <div class='scrollable-content recommendations-scrollable'>" ;
                        
        foreach ($result as $content) {
            $id = $content['contentID'] ;
            $posterUrl = $content['posterUrl'] ;
            $contentName = $content['name'] ;
            $type = $content['contentType'] ;
            echo "<img id='id=$id&type=$type' src='$posterUrl' alt='$contentName'>" ;
        }

        echo "</div>
                        <button class='scroll-button right' aria-label='Défiler à droite'>▶</button>
                    </div>
                </section>";
    }
    echo "<button id='createCollection'>+ Créer une collection</button>" ;
}

function showCollection(PDO $conn, int $collectionID, string $collectionName) {
    echo "<section><h2>$collectionName</h2>" ;
    echo "<div class='catalogueSection'>" ;

    $contentQuery = "SELECT u.contentID, u.posterUrl, u.`name`, u.contentType FROM
            (SELECT contentID, posterUrl, `name`, contentType FROM film
            UNION
            SELECT contentID, posterUrl, `name`, contentType FROM series) u
        JOIN contentcollection cc ON u.contentID = cc.contentID AND u.contentType = cc.pk_ContentType
        WHERE cc.pk_collectionID = ? ;" ;

    $stmtContent = $conn->prepare($contentQuery) ;
    $stmtContent->bindParam(1, $collectionID) ;
    $stmtContent->execute() ;
    
    while ($row = $stmtContent->fetch(PDO::FETCH_ASSOC)) {
        $id = $row['contentID'];
        $contentName = $row['name'] ;
        $imgURL = $row['posterUrl'] ;
        $type = $row['contentType'] ;
        echo "<div class=catalogueContent>
                        <a href=pageContenu.php?id=$id&type=$type>
                            <h3>$contentName</h3>
                            <img src=$imgURL class=\"catalogueImages content-image\" alt=\"$contentName\">
                        </a>
                    </div>
                " ;

    }
    echo "</section>" ;
}
?>