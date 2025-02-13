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

$watchedTable = ($contentType === 'film') ? 'filmwatched' : 'serieswatched';
$contentColumn = ($contentType === 'film') ? 'filmID' : 'pk_SeriesID';
$contentTable = ($contentType === 'film') ? 'film' : 'series';

$isWatched = false;
if ($userID > 0) {
    $stmt = $pdo->prepare("SELECT * FROM $watchedTable WHERE $contentColumn = :contentID AND pk_UserID = :userID");
    $stmt->execute([':contentID' => $contentID, ':userID' => $userID]);
    $isWatched = $stmt->rowCount() > 0;
}

if ($userID > 0 && isset($_POST['toggleWatched'])) {
    if ($isWatched) {
        $stmt = $pdo->prepare("DELETE FROM $watchedTable WHERE $contentColumn = :contentID AND pk_UserID = :userID");
        $stmt->execute([':contentID' => $contentID, ':userID' => $userID]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO $watchedTable ($contentColumn, pk_UserID) VALUES (:contentID, :userID)");
        $stmt->execute([':contentID' => $contentID, ':userID' => $userID]);
    }
    header("Location: " . $_SERVER['PHP_SELF'] . "?id=$contentID&type=$contentType");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM $contentTable WHERE contentID = :contentID");
$stmt->execute([':contentID' => $contentID]);
$content = $stmt->fetch(PDO::FETCH_ASSOC);

$collections = [];
if ($userID > 0) {
    $stmt = $pdo->prepare("SELECT collectionID, name FROM collection WHERE pk_userID = :userID");
    $stmt->execute([':userID' => $userID]);
    $collections = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

if ($userID > 0 && isset($_POST['ajoutCollection']) && isset($_POST['collectionID'])) {
    $collectionID = intval($_POST['collectionID']);
    $stmt = $pdo->prepare("INSERT INTO contentcollection (contentID, pk_ContentType, pk_collectionID) VALUES (:contentID, :contentType, :collectionID)");
    $stmt->execute([':contentID' => $contentID, ':contentType' => $contentType, ':collectionID' => $collectionID]);
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
            <img src="<?= htmlspecialchars($content['posterUrl']) ?>" alt="Image du contenu" class="image-contenu">
        </div>
        <?php if ($userID > 0) { ?>
            <div id="description-grid">
                <form method="post">
                    <button type="submit" name="toggleWatched">
                        <?= $isWatched ? 'Supprimer de la liste des films/séries regardées' : 'Ajouter de la liste des films/séries regardées' ?>
                    </button>
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
