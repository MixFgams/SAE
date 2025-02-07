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

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['idUser'])) {
    $userID = 0;
} else {
    $userID = $_SESSION['idUser'];
}

$contentID = isset($_GET['id']) ? intval($_GET['id']) : -1;
$contentType = isset($_GET['type']) ? $_GET['type'] : "";

if ($contentID < 0 || empty($contentType)) {
    echo "<h1>Le contenu que vous cherchez n'existe pas</h1>";
    exit;
}



// Définir les noms de table dynamiquement
$watchedTable = ($contentType === 'film') ? 'filmwatched' : 'serieswatched';
$contentColumn = ($contentType === 'film') ? 'filmID' : 'pk_SeriesID';
$contentTable = ($contentType === 'film') ? 'film' : 'series';

// Ajouter à la liste "Déjà Vu" et incrémenter les vues
if ($userID > 0 && isset($_POST['dejaVu'])) {
    $stmt = $pdo->prepare("SELECT * FROM $watchedTable WHERE $contentColumn = :contentID AND pk_UserID = :userID");
    $stmt->execute([':contentID' => $contentID, ':userID' => $userID]);

    if ($stmt->rowCount() == 0) {
        $stmt = $pdo->prepare("INSERT INTO $watchedTable ($contentColumn, pk_UserID) VALUES (:contentID, :userID)");
        $stmt->execute([':contentID' => $contentID, ':userID' => $userID]);

        // Incrémenter le nombre de vues
        $stmt = $pdo->prepare("UPDATE $contentTable SET viewsCount = viewsCount + 1 WHERE contentID = :contentID");
        $stmt->execute([':contentID' => $contentID]);
    }
}

// Ajouter à une collection
if ($userID > 0 && isset($_POST['ajoutCollection']) && isset($_POST['collectionID'])) {
    $collectionID = intval($_POST['collectionID']);
    $stmt = $pdo->prepare("INSERT INTO contentcollection (contentID, pk_ContentType, pk_collectionID) VALUES (:contentID, :contentType, :collectionID)");
    $stmt->execute([':contentID' => $contentID, ':contentType' => $contentType, ':collectionID' => $collectionID]);
}

// Récupérer les collections de l'utilisateur
if ($userID > 0) {
    $stmt = $pdo->prepare("SELECT collectionID, name FROM collection WHERE pk_userID = :userID");
    $stmt->execute([':userID' => $userID]);
    $collections = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Récupérer les infos du contenu en fonction du type
$stmt = $pdo->prepare("SELECT c.contentID, c.name, c.description, c.releaseDate, c.runtime, c.posterUrl, c.contentType, c.viewsCount, p.name AS productionName 
                        FROM $contentTable c
                        LEFT JOIN productioncontentassociation pca ON c.contentID = pca.pk_ContentID AND pca.pk_ContentType = :contentType
                        LEFT JOIN production p ON pca.pk_ProductionID = p.productionID
                        WHERE c.contentID = :contentID");
$stmt->execute([':contentType' => $contentType, ':contentID' => $contentID]);
$content = $stmt->fetch(PDO::FETCH_ASSOC);

$viewCount = $content['viewsCount'];
$id = $content['contentID'];

if (!isset($_SESSION["vu$id"]) || $_SESSION["vu$id"] !== $userID) {
    $_SESSION["vu$id"] = $userID;
    $viewCount += 1;
    $contentType = $content['contentType'];
    $sql = "UPDATE $contentType SET viewsCount = $viewCount 
            WHERE contentID = $id";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();
}

// Vérifier si le contenu a déjà été marqué comme "Déjà Vu" par l'utilisateur
$isWatched = false;
if ($userID > 0) {
    $stmt = $pdo->prepare("SELECT * FROM $watchedTable WHERE $contentColumn = :contentID AND pk_UserID = :userID");
    $stmt->execute([':contentID' => $contentID, ':userID' => $userID]);
    $isWatched = $stmt->rowCount() > 0;
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
                <h3>Production</h3>
                <p><?= htmlspecialchars($content['productionName'] ?? 'Non disponible') ?></p>
                <h3>Durée</h3>
                <p><?= htmlspecialchars($content['runtime']) ?> minutes</p>
                <h3>Nombre de vues</h3>
                <p><?= htmlspecialchars($viewCount) ?></p>
                <h3>Description</h3>
                <p><?= htmlspecialchars($content['description']) ?></p>
            </div>
            <img src="<?= htmlspecialchars($content['posterUrl']) ?>" alt="Image du contenu" class="image-contenu">
        </div>
        <?php if ($userID > 0) { ?>
            <div id="description-grid">
                <form method="post">
                    <label>
                        <input type="checkbox" name="dejaVu" onchange="this.form.submit()" <?= $isWatched ? 'checked' : '' ?>> Déjà Vu
                    </label>
                </form>
                <form method="post">
                    <select name="collectionID">
                        <?php foreach ($collections as $collection): ?>
                            <option value="<?= $collection['collectionID'] ?>"> <?= htmlspecialchars($collection['name']) ?> </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" name="ajoutCollection">Ajouter à une collection</button>
                </form>
            </div>
        <?php } ?>
    </section>
</main>
</body>
</html>