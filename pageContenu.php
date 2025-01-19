<!DOCTYPE html>
<html>
    <head>
        <link rel="icon" href="img/obLogo.png" type="image/x-icon">
        <link rel="stylesheet" href="style.css">

    </head>

    <body>
        <?php include "pagesOutils/header.php" ;
        include "pagesOutils/pdo.php" ;
        $contentId = $_GET["contentId"] ;
        $type = $_GET["type"] ;
        $content = getContent($pdo, $contentId, $type);
        if (!$content) {
        ?>
            <section>
                <h1>Contenu Inexistant</h1>
                La page que vous cherchez n'existe pas
            </section>
        <?php }
        else {
        ?>
        <main>
            <section id="cadre-contenu">
                <div id="contenu">
                    <div id="info-contenu">
                        <h3>Titre</h3>
                        <p id="titre"><?php echo $content["name"] ?></p>
                        
                        <h3>Type de Contenu</h3>
                        <p id="type-contenu"><?php echo $content["contentType"] ?></p>

                        <h3>Date de Sortie</h3>
                        <p id="date-sortie"><?php echo $content["releaseDate"] ?></p>

                        <h3>Auteur</h3>
                        <p id="auteur"><?php echo $content["director"] ?></p>

                        <h3>Nombre total de volumes</h3>
                        <p id="nb-volumes"></p>
                    </div>
                    <img src=<?php echo $content['posterUrl'] ;?> alt="chouine" class="image-contenu">
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
                        <p id="description"><?php echo $content["description"] ?></p>
                    </div>
                    <div id="noter">
                        <button>Noter</button>
                        <div class="invisible">
                        <button id="moins">-</button>
                        <p id="nbEtoile">0 etoiles</p>
                        <button id="plus">+</button>
                            <button id="envoie">Envoyer</button>
                        </div>
                    </div>
                </div>
                <h3>Commentaires</h3>
                <div id="Commentaires">
                    <div id="liste-commentaire">
                        <div class="blue-box">
                            <div class="commentaire-header">
                                <img src="./img/pdp.jpg"/ class="pp-commentaire">
                                <a href="">
                                    <p>xXYutaFanXx</p>
                                </a>
                            </div>
                            <p class="commentaire-text">
                                bonjour
                            </p>
                        </div>
                        <div class="blue-box">
                            <div class="commentaire-header">
                                <img src="./img/pdp.jpg"/ class="pp-commentaire">
                                <a href="">
                                    <p>AigriDeService</p>
                                </a>
                            </div>
                            <p class="commentaire-text">
                                Il s"agirait peut-être de savoir coder
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
        </main>
        <?php
        } ?>
        <script src="script.js"></script>
        <script src="noter.js"></script>
    
        <?php include "pagesOutils/footer.php"?>
    </body>
</html>

<?php
    function getContent(PDO $conn, int $contentID, string $type) {
        if ($type == 'film')
            $sql = "SELECT * FROM film 
                    WHERE contentID = ?" ;

        else if ($type == 'series')
            $sql = "SELECT * FROM series 
                    WHERE contentID = ?" ;

        else
            return null ;

        $stmt = $conn->prepare($sql) ;
        $stmt->bindParam(1, $contentID) ;
        $stmt->execute() ;
        
        if ($stmt->rowCount() > 0)
            return $stmt->fetch(PDO::FETCH_ASSOC) ;

        return null ;
    }
?>