<?php
session_start();
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php
include 'pagesOutils/header.php';
include 'pagesOutils/connDB.php';

// Vérification de l'ID utilisateur
if (!isset($_SESSION['idUser'])) {
    $userID = 0;
} else {
    $userID = $_SESSION['idUser'];
}

// Récupération des paramètres GET
$contentID   = isset($_GET['id'])   ? intval($_GET['id'])   : -1;
$contentType = isset($_GET['type']) ? $_GET['type']         : "";

// Vérification de la validité
if ($contentID < 0 || empty($contentType)) {
    echo "<h1>Le contenu que vous cherchez n'existe pas</h1>";
    exit;
}

// Détermination des tables et colonnes selon le type
$watchedTable  = ($contentType === 'film') ? 'filmwatched' : 'serieswatched';
$contentColumn = ($contentType === 'film') ? 'filmID'     : 'pk_SeriesID';
$contentTable  = ($contentType === 'film') ? 'film'       : 'series';

// Vérifier si l'utilisateur a déjà regardé ce contenu
$isWatched = false;
if ($userID > 0) {
    $stmt = $pdo->prepare("SELECT *
                           FROM $watchedTable
                           WHERE $contentColumn = :contentID
                             AND pk_UserID = :userID");
    $stmt->execute([
        ':contentID' => $contentID,
        ':userID'    => $userID
    ]);
    $isWatched = ($stmt->rowCount() > 0);
}

// Gestion du bouton "Ajouter / Supprimer de la liste des films/séries regardées"
if ($userID > 0 && isset($_POST['toggleWatched'])) {
    if ($isWatched) {
        // Supprimer l'entrée de la table watched
        $stmt = $pdo->prepare("DELETE FROM $watchedTable
                               WHERE $contentColumn = :contentID
                                 AND pk_UserID = :userID");
        $stmt->execute([
            ':contentID' => $contentID,
            ':userID'    => $userID
        ]);

        // Décrémenter le viewsCount
        $stmt = $pdo->prepare("UPDATE $contentTable
                               SET viewsCount = viewsCount - 1
                               WHERE contentID = :contentID");
        $stmt->execute([':contentID' => $contentID]);
    } else {
        // Ajouter l'entrée dans la table watched
        $stmt = $pdo->prepare("INSERT INTO $watchedTable ($contentColumn, pk_UserID)
                               VALUES (:contentID, :userID)");
        $stmt->execute([
            ':contentID' => $contentID,
            ':userID'    => $userID
        ]);

        // Incrémenter le viewsCount
        $stmt = $pdo->prepare("UPDATE $contentTable
                               SET viewsCount = viewsCount + 1
                               WHERE contentID = :contentID");
        $stmt->execute([':contentID' => $contentID]);
    }

    // Rafraîchir la page pour mettre à jour l'affichage
    header("Location: " . $_SERVER['PHP_SELF'] . "?id=$contentID&type=$contentType");
    exit;
}

// Récupération des infos du contenu
$stmt = $pdo->prepare("SELECT *
                       FROM $contentTable
                       WHERE contentID = :contentID");
$stmt->execute([':contentID' => $contentID]);
$content = $stmt->fetch(PDO::FETCH_ASSOC);

// Récupération des collections de l'utilisateur
$collections = [];
if ($userID > 0) {
    $stmt = $pdo->prepare("SELECT collectionID, name
                           FROM collection
                           WHERE pk_userID = :userID");
    $stmt->execute([':userID' => $userID]);
    $collections = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Gestion de l'ajout à une collection
if ($userID > 0 && isset($_POST['ajoutCollection']) && isset($_POST['collectionID'])) {
    $collectionID = intval($_POST['collectionID']);
    $stmt = $pdo->prepare("INSERT INTO contentcollection (contentID, pk_ContentType, pk_collectionID)
                           VALUES (:contentID, :contentType, :collectionID)");
    $stmt->execute([
        ':contentID'   => $contentID,
        ':contentType' => $contentType,
        ':collectionID'=> $collectionID
    ]);

    // On rafraîchit la page pour mettre à jour l'affichage
    header("Location: " . $_SERVER['PHP_SELF'] . "?id=$contentID&type=$contentType");
    exit;
}
?>
<main>
    <section id="cadre-contenu">
        <div id="contenu">
            <div id="info-contenu">
                <h3><?= htmlspecialchars($content['name']) ?></h3>
                <h3>Type de Contenu</h3>
                <p><?= htmlspecialchars($content['contentType']) ?></p>
                <h3>Date de Sortie</h3>
                <p><?= htmlspecialchars($content['releaseDate']) ?></p>
                <h3>Durée</h3>
                <p><?= htmlspecialchars($content['runtime']) ?> minutes</p>
                <h3>Nombre de vues</h3>
                <p><?= htmlspecialchars($content['viewsCount']) ?></p>
                <h3>Description</h3>
                <p><?= htmlspecialchars($content['description']) ?></p>
            </div>
            <img src="<?= htmlspecialchars($content['posterUrl']) ?>"
                 alt="Image du contenu"
                 class="image-contenu">
        </div>

        <?php if ($userID > 0) { ?>
            <div id="description-grid">
                <form method="post">
                    <button type="submit" name="toggleWatched">
                        <?= $isWatched
                            ? 'Supprimer de la liste des films/séries regardées'
                            : 'Ajouter de la liste des films/séries regardées'
                        ?>
                    </button>
                </form>
                <form method="post">
                    <select name="collectionID">
                        <?php foreach ($collections as $collection): ?>
                            <option value="<?= $collection['collectionID'] ?>">
                                <?= htmlspecialchars($collection['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" name="ajoutCollection">
                        Ajouter à une collection
                    </button>
                </form>
            </div>
        <?php } ?>
    </section>
</main>

<!-- SECTION ACTEURS EN BAS DE PAGE -->
<section class="SectionIndex">
    <h2>Acteurs</h2>
    <div class="scrollable-container">
        <button class="scroll-button left" aria-label="Défiler à gauche">◀</button>
        <div class="recommendations-scrollable scrollable-content" >
            <?php
            // Récupération des acteurs pour ce contenu
            // (On ne touche pas la requête)
            $sqlActors = "
                SELECT c.characterdbID, c.name, c.imageUrl
                FROM charactercontentassociation cca
                JOIN `characterdb` c ON cca.pk_CharacterID = c.characterdbID
                WHERE cca.pk_ContentID = :contentID
                  AND cca.characterType = 'actor'
            ";
            $stmtActors = $pdo->prepare($sqlActors);
            $stmtActors->execute([':contentID' => $contentID]);

            if ($stmtActors->rowCount() > 0) {
                while ($actor = $stmtActors->fetch(PDO::FETCH_ASSOC)) {
                    // Classes similaires à la page index
                    echo '<div class="recommendation-card">';

                    // Vérifier si imageUrl n'est pas vide ou null
                    if (!empty($actor['imageUrl'])) {
                        // Affichage de l'image
                        echo '<img src="' . htmlspecialchars($actor['imageUrl']) . '" '
                            . 'alt="' . htmlspecialchars($actor['name']) . '">';
                    }
                    // Afficher systématiquement le nom
                    echo '<p>' . htmlspecialchars($actor['name']) . '</p>';
                    echo '</div>';
                }
            } else {
                echo '<p>Aucun acteur renseigné pour ce contenu.</p>';
            }
            ?>
        </div>
        <button class="scroll-button right" aria-label="Défiler à droite">▶</button>
    </div>
</section>
<script src="script.js"></script>
</body>
</html>
