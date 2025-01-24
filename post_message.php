<?php
// Connexion à la base de données

// Connexion à la base de données avec PDO
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ob";

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupérer les données du formulaire
    $messageContent = $_POST['messageContent'];
    $subjectID = $_POST['subjectID'];
    $userID = 1; // Remplace par l'ID de l'utilisateur connecté (via session, par exemple)

    // Préparer la requête d'insertion
    $stmt = $pdo->prepare("
        INSERT INTO comment (contentID, pk_userID, commetContent, likeCount, dislikeCount, pk_ContentType)
        VALUES (:contentID, :userID, :content, 0, 0, 'subject')
    ");

    // Exécuter la requête
    $stmt->execute([
        ':contentID' => $subjectID,
        ':userID' => $userID,
        ':content' => $messageContent,
    ]);

    // Rediriger vers la page précédente
    header("Location: forum.php");
    exit;
}
?>

