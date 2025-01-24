<?php
    session_start();
?>


<!DOCTYPE html>
<html>
<head>
    <link rel="icon" href="img/obLogo.png" type="image/x-icon">

    <link rel="stylesheet" href="contenu.css">
</head>

<body>
<?php include 'pagesOutils/header.php'?>

<main>
    <section id="cadre-contenu">
        <div id="contenu">
            <div id="info-contenu">
                <?php
                // Connexion à la base de données
                $servername = "localhost";
                $username = "root";
                $password = "";
                $dbname = "ob";

                // Récupérer l'ID depuis l'URL
                $contentID = isset($_GET['id']) ? $_GET['id'] : 0;

                // Connexion à la base de données
                $conn = new mysqli($servername, $username, $password, $dbname);

                if ($conn->connect_error) {
                    die("Erreur de connexion : " . $conn->connect_error);
                }

                // Requête pour récupérer le contenu basé sur l'ID, et joindre les tables pour obtenir la production
                $sql = "
    SELECT 
        c.contentID, c.name, c.description, c.releaseDate, c.runtime, c.posterUrl, c.contentType, c.viewsCount,
        p.name AS productionName
    FROM (
        SELECT contentID, name, description, releaseDate, runtime, posterUrl, contentType, viewsCount 
        FROM film
        UNION ALL
        SELECT contentID, name, description, releaseDate, runtime, posterUrl, contentType, viewsCount 
        FROM series
    ) AS c
    LEFT JOIN productioncontentassociation pca ON c.contentID = pca.pk_ContentID AND c.contentType = pca.pk_ContentType
    LEFT JOIN production p ON pca.pk_ProductionID = p.productionID
    WHERE c.contentID = $contentID
";

                // Exécution de la requête
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    $row = $result->fetch_assoc();
                    // Affichage des données dynamiquement
                    echo '<h3>' . htmlspecialchars($row['name']) . '</h3>';
                    echo '<p id="titre">' . htmlspecialchars($row['name']) . '</p>';
                    echo '<h3>Type de Contenu</h3>';
                    echo '<p id="type-contenu">' . htmlspecialchars($row['contentType']) . '</p>';
                    echo '<h3>Date de Sortie</h3>';
                    echo '<p id="date-sortie">' . htmlspecialchars($row['releaseDate']) . '</p>';
                    echo '<h3>Production</h3>';
                    echo '<p id="production">' . htmlspecialchars($row['productionName']) . '</p>'; // Production récupérée ici
                    echo '<h3>Durée</h3>';
                    echo '<p id="runtime">' . htmlspecialchars($row['runtime']) . ' minutes</p>';
                    echo '<h3>Nombre de vues</h3>';
                    echo '<p id="views-count">' . htmlspecialchars($row['viewsCount']) . '</p>';
                } else {
                    echo "<p>Aucun contenu trouvé pour cet ID.</p>";
                }

                $conn->close();
                ?>

            </div>
            <img src="<?php echo htmlspecialchars($row['posterUrl']); ?>" alt="Image du contenu" class="image-contenu">
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
</main>

<script src="script.js"></script>
<script src="noter.js"></script>

<?php include 'pagesOutils/footer.php'?>
</body>
</html>
