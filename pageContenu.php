<!DOCTYPE html>
<html>
<head>
    <link rel="icon" href="img/obLogo.png" type="image/x-icon">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="contenu.css">
</head>

<body>
<?php include 'pagesOutils/header.php'?>
<?php include 'pageOutils/connDB.php'?>

<main>
    <?php 
    // Récupérer l'ID et le type depuis l'URL
    $contentID = isset($_GET['id']) ? intval($_GET['id']) : -1 ;
    $contentType = isset($_GET['type']) ? $_GET['type'] : "" ;
    if ($contentID >= 0 and strcmp($contentType, "") != 0) {
        // Requête pour récupérer le contenu basé sur l'ID
        $doQuery = true ;
        if (strcmp($contentType, "film") == 0)
            $sql = "SELECT *
                    FROM film
                    WHERE contentID = ?";
        else if (strcmp($contentType, "series") == 0) 
            $sql = "SELECT *
            FROM series
            WHERE contentID = ?";
        else
            $doQuery = false ;
        
        if ($doQuery) {
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(1, $contentID) ;
            $stmt->execute() ;
        }

        $hasContent = ($doQuery and $stmt->rowCount() > 0) ;
    } else {
        $hasContent = false ;
    }

    if ($hasContent) {
    ?>
    <section id="cadre-contenu">
        <div id="contenu">
            <div id="info-contenu">
                <?php
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                // Affichage des données dynamiquement
                echo '<h3>' . htmlspecialchars($row['name']) . '</h3>';
                echo '<p id="titre">' . htmlspecialchars($row['name']) . '</p>';
                echo '<h3>Type de Contenu</h3>';
                echo '<p id="type-contenu">' . htmlspecialchars($row['contentType']) . '</p>';
                echo '<h3>Date de Sortie</h3>';
                echo '<p id="date-sortie">' . htmlspecialchars($row['releaseDate']) . '</p>';
                echo '<h3>Auteur</h3>';
                echo '<p id="auteur">Non spécifié</p>';  // Auteur n'est pas dans la base de données, donc ajout de "Non spécifié"
                echo '<h3>Durée</h3>';
                echo '<p id="runtime">' . htmlspecialchars($row['runtime']) . ' minutes</p>';
                echo '<h3>Nombre de vues</h3>';
                echo '<p id="views-count">' . htmlspecialchars($row['viewsCount']) . '</p>';
                ?>
            </div>
            <img src=<?php echo htmlspecialchars($row['posterUrl']);?> alt="Image du contenu" class="image-contenu">
        </div>
        <div id="description-grid">
            <div class="blue-box" id="collection">
                <label id="checkbox-grid">
                    <div class="checkmark">
                        <input type="checkbox" id="deja-vu">
                        <div class="checkmark-inside"></div>
                    </div>
                    <p>Déjà Vu</p>
                </label>
                <p>Ajouter à une collection</p>
                <button type="button" id="ajouter-collection">
                    AJOUTER
                </button>
            </div>
            <div id="description-div">
                <h3>Description</h3>
                <p id="description"><?php echo htmlspecialchars($row['description']); ?></p>
            </div>
            <div id="noter">
                <button>Noter</button>
                <div class="invisible">
                    <button id="moins">-</button>
                    <p id="nbEtoile">0 étoiles</p>
                    <button id="plus">+</button>
                    <button id="envoie">Envoyer</button>
                </div>
            </div>
        </div>
        <h3>Commentaires</h3>
        <div id="Commentaires">
            <div id="liste-commentaire">
                <!-- Affichage des commentaires (vous pouvez ajouter la requête pour récupérer les commentaires si nécessaire) -->
                <div class="blue-box">
                    <div class="commentaire-header">
                        <img src="./img/pdp.jpg" class="pp-commentaire">
                        <a href="">
                            <p>xXYutaFanXx</p>
                        </a>
                    </div>
                    <p class="commentaire-text">
                        Bonjour
                    </p>
                </div>
                <div class="blue-box">
                    <div class="commentaire-header">
                        <img src="./img/pdp.jpg" class="pp-commentaire">
                        <a href="">
                            <p>AigriDeService</p>
                        </a>
                    </div>
                    <p class="commentaire-text">
                        Il s'agirait peut-être de savoir coder
                    </p>
                </div>
            </div>
        </div>
        <a href="" class="texte-page-contenue">Voir Plus +</a>
        </div>
    </section>

    <section id="volumes">
        <h2>Volumes</h2>
    </section>
    <?php 
    } else {
        echo "<h1>Le contenu que vous cherchez n'existe pas</h1>" ;
    } ?>
</main>

<script src="script.js"></script>
<script src="noter.js"></script>

<?php include 'pagesOutils/footer.php'?>
</body>
</html>
