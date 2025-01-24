<?php
session_start();
include 'pagesOutils/header.php';
include 'pagesOutils/connDB.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['idUser'])) {
    echo "<p style='color:red;'>Veuillez vous connecter pour utiliser cette fonctionnalité.</p>";
    exit;
}

$userID = $_SESSION['idUser'];
$contentID = isset($_GET['id']) ? intval($_GET['id']) : -1;
$contentType = isset($_GET['type']) ? $_GET['type'] : "";

if ($contentID < 0 || empty($contentType)) {
    echo "<h1>Le contenu que vous cherchez n'existe pas</h1>";
    exit;
}

// Définition des tables selon le type de contenu
$watchedTable = ($contentType === 'film') ? 'filmwatched' : 'serieswatched';
$contentColumn = ($contentType === 'film') ? 'filmID' : 'pk_SeriesID';
$contentTable = ($contentType === 'film') ? 'film' : 'series';

// Vérifier si le contenu est déjà vu par l'utilisateur
$stmt = $pdo->prepare("SELECT * FROM $watchedTable WHERE $contentColumn = :contentID AND pk_UserID = :userID");
$stmt->execute([':contentID' => $contentID, ':userID' => $userID]);
$isWatched = ($stmt->rowCount() > 0);

// **Gestion du marquage "Déjà Vu"**
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggleVu'])) {
    if ($isWatched) {
        // Retirer le contenu de la liste "Déjà Vu"
        $stmt = $pdo->prepare("DELETE FROM $watchedTable WHERE $contentColumn = :contentID AND pk_UserID = :userID");
        $stmt->execute([':contentID' => $contentID, ':userID' => $userID]);

        if ($stmt->rowCount() > 0) {
            $stmt = $pdo->prepare("UPDATE $contentTable SET viewsCount = GREATEST(viewsCount - 1, 0) WHERE contentID = :contentID");
            $stmt->execute([':contentID' => $contentID]);
        }
    } else {
        // Ajouter à la liste "Déjà Vu"
        $stmt = $pdo->prepare("INSERT INTO $watchedTable ($contentColumn, pk_UserID) VALUES (:contentID, :userID)");
        $stmt->execute([':contentID' => $contentID, ':userID' => $userID]);

        $stmt = $pdo->prepare("UPDATE $contentTable SET viewsCount = viewsCount + 1 WHERE contentID = :contentID");
        $stmt->execute([':contentID' => $contentID]);
    }

    // Recharger l'état de la case après modification
    $stmt = $pdo->prepare("SELECT * FROM $watchedTable WHERE $contentColumn = :contentID AND pk_UserID = :userID");
    $stmt->execute([':contentID' => $contentID, ':userID' => $userID]);
    $isWatched = ($stmt->rowCount() > 0);

    // Redirection pour éviter la double soumission du formulaire
    header("Location: pageContenu.php?id=$contentID&type=$contentType");
    exit;
}

// Ajouter à une collection
if (isset($_POST['ajoutCollection']) && isset($_POST['collectionID'])) {
    $collectionID = intval($_POST['collectionID']);
    $stmt = $pdo->prepare("INSERT INTO contentcollection (contentID, pk_ContentType, pk_collectionID) VALUES (:contentID, :contentType, :collectionID)");
    $stmt->execute([':contentID' => $contentID, ':contentType' => $contentType, ':collectionID' => $collectionID]);
}

// Récupérer les collections de l'utilisateur
$stmt = $pdo->prepare("SELECT collectionID, name FROM collection WHERE pk_userID = :userID");
$stmt->execute([':userID' => $userID]);
$collections = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les infos du contenu
$stmt = $pdo->prepare("SELECT c.contentID, c.name, c.description, c.releaseDate, c.runtime, c.posterUrl, c.contentType, c.viewsCount, p.name AS productionName 
                        FROM $contentTable c
                        LEFT JOIN productioncontentassociation pca ON c.contentID = pca.pk_ContentID AND pca.pk_ContentType = :contentType
                        LEFT JOIN production p ON pca.pk_ProductionID = p.productionID
                        WHERE c.contentID = :contentID");
$stmt->execute([':contentType' => $contentType, ':contentID' => $contentID]);
$content = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="contenu.css">
</head>
<body>
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
                <p><?= htmlspecialchars($content['viewsCount']) ?></p>
                <h3>Description</h3>
                <p><?= htmlspecialchars($content['description']) ?></p>
            </div>
            <img src="<?= htmlspecialchars($content['posterUrl']) ?>" alt="Image du contenu" class="image-contenu">
        </div>
        <div id="description-grid">
            <form method="post">
                <label>
                    <input type="checkbox" name="toggleVu" onchange="this.form.submit()" <?= $isWatched ? 'checked' : '' ?>> Déjà Vu
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
    </section>
</main>
</body>
</html>
