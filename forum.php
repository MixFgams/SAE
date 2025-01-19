<?php
session_start();

// Connexion à la base de données
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

// Vérification de l'ID du forum
if (!isset($_POST['forumID']) || !is_numeric($_POST['forumID'])) {
    die("ID du forum invalide.");
}

$forumID = (int)$_POST['forumID'];

// Récupération des informations du forum
try {
    $stmt = $pdo->prepare("SELECT forumTitle, description FROM forum WHERE forumID = :forumID");
    $stmt->execute([':forumID' => $forumID]);
    $forum = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$forum) {
        die("Forum non trouvé.");
    }
} catch (PDOException $e) {
    die("Erreur lors de la récupération du forum : " . $e->getMessage());
}

// Gestion de l'insertion d'un nouveau message
if (isset($_POST['envoyer']) && !empty($_POST['message'])) {
    $message = trim($_POST['message']);
    try {
        $stmt = $pdo->prepare("
            INSERT INTO comment (contentID, pk_userID, commetContent, pk_ContentType, likeCount, dislikeCount) 
            VALUES (:forumID, :userID, :message, 'forum', 0, 0)
        ");
        $stmt->execute([
            ':forumID' => $forumID,
            ':userID' => $_SESSION['userID'] ?? 1, // Utilisateur par défaut ou connecté
            ':message' => $message,
        ]);
        $messageConfirmation = "Votre message a été ajouté avec succès !";
    } catch (PDOException $e) {
        $messageErreur = "Erreur lors de l'insertion du message : " . $e->getMessage();
    }
}

// Récupération des messages du forum
try {
    $stmt = $pdo->prepare("
        SELECT pk_userID, commetContent, likeCount, dislikeCount 
        FROM comment 
        WHERE contentID = :forumID AND pk_ContentType = 'forum'
    ");
    $stmt->execute([':forumID' => $forumID]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erreur lors de la récupération des messages : " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages du Forum</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<?php include 'pagesOutils/header.php'; ?>

<main>
    <section id="forum-messages">
        <h2><?php echo htmlspecialchars($forum['forumTitle']); ?></h2>
        <p><?php echo htmlspecialchars($forum['description']); ?></p>

        <!-- Notification -->
        <?php if (isset($messageConfirmation)): ?>
            <p class="success"><?php echo $messageConfirmation; ?></p>
        <?php elseif (isset($messageErreur)): ?>
            <p class="error"><?php echo $messageErreur; ?></p>
        <?php endif; ?>

        <!-- Affichage des messages -->
        <div id="messages">
            <?php if ($messages): ?>
                <?php foreach ($messages as $message): ?>
                    <div class="message">
                        <strong>Utilisateur <?php echo htmlspecialchars($message['pk_userID']); ?></strong> :
                        <p><?php echo htmlspecialchars($message['commetContent']); ?></p>
                        <p>Likes : <?php echo htmlspecialchars($message['likeCount']); ?></p>
                        <p>Dislikes : <?php echo htmlspecialchars($message['dislikeCount']); ?></p>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Aucun message dans ce forum.</p>
            <?php endif; ?>
        </div>

        <!-- Formulaire pour ajouter un message -->
        <form method="post">
            <input type="hidden" name="forumID" value="<?php echo $forumID; ?>">
            <textarea name="message" placeholder="Écrivez votre message ici" required></textarea>
            <input type="submit" name="envoyer" value="Envoyer">
        </form>

        <a href="communaute.php" class="btn-retour">Retour à la communauté</a>
    </section>
</main>

<?php include 'pagesOutils/footer.php'; ?>

<script src="forum.js"></script>
</body>
</html>
