<?php
session_start();
include 'pagesOutils/connDB.php';

if (!isset($_SESSION['idUser'])) {
    header("Location: connexion.php");
    exit;
}

$userID = $_SESSION['idUser'];
$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['collectionName'])) {
        $collectionName = htmlspecialchars($_POST['collectionName']);
        
        $presentQuery = "SELECT `name`, pk_userID FROM collection WHERE `name` = ? AND pk_userID = ?" ;
        $stmt = $pdo->prepare($presentQuery) ;
        $stmt->bindParam(1, $collectionName) ;
        $stmt->bindParam(2, $userID) ;
        $stmt->execute() ;
        
        if ($stmt->fetch(PDO::FETCH_ASSOC)) {
            $message = "Erreur, cette collection existe déjà" ;
        } else {
            $insertQuery = "INSERT INTO collection (`name`, pk_userID) VALUES (?, ?)";
            $stmt = $pdo->prepare($insertQuery);
            $stmt->bindParam(1, $collectionName);
            $stmt->bindParam(2, $userID);
            
            if ($stmt->execute()) {
                $message = "Collection créée avec succès.";
            } else {
                $message = "Erreur lors de la création de la collection.";
            }
        }

    } else {
        $message = "Veuillez entrer un nom pour la collection.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Créer une collection</title>
</head>
<body>
    <?php include("pagesOutils/header.php"); ?>
    <main>
        <h1>Créer une nouvelle collection</h1>
        <form method="POST" action="">
            <label for="collectionName">Nom de la collection :</label>
            <input type="text" id="collectionName" name="collectionName" required>
            <button type="submit">Créer</button>
        </form>
        <p><?php echo $message; ?></p>
        <a href="collection.php">Retour aux collections</a>
    </main>
</body>
</html>
