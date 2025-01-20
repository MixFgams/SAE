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
    die("ID du forum invalide. Forum ID reçu : " . $_POST['forumID']);
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

// Gestion du like
if (isset($_POST['like']) && isset($_POST['messageID'])) {
    $messageID = (int)$_POST['messageID'];
    $userID = $_SESSION['userID'] ?? 1; // Utilisateur par défaut ou connecté

    // Vérifier si l'utilisateur a déjà liké ce message
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM user_reactions WHERE userID = :userID AND messageID = :messageID AND reactionType = 'like'");
        $stmt->execute([':userID' => $userID, ':messageID' => $messageID]);
        $alreadyLiked = $stmt->fetchColumn() > 0;

        if (!$alreadyLiked) {
            // Insérer l'action du like dans la table user_reactions
            $stmt = $pdo->prepare("INSERT INTO user_reactions (userID, messageID, reactionType) VALUES (:userID, :messageID, 'like')");
            $stmt->execute([':userID' => $userID, ':messageID' => $messageID]);

            // Incrémenter le compteur de likes du message
            $stmt = $pdo->prepare("UPDATE comment SET likeCount = likeCount + 1 WHERE commentID = :messageID AND contentID = :forumID AND pk_ContentType = 'forum'");
            $stmt->execute([':messageID' => $messageID, ':forumID' => $forumID]);

            $likeConfirmation = "Vous avez liké ce message.";
        } else {
            $likeError = "Vous avez déjà liké ce message.";
        }
    } catch (PDOException $e) {
        $likeError = "Erreur lors du like : " . $e->getMessage();
    }
}

// Gestion du dislike
if (isset($_POST['dislike']) && isset($_POST['messageID'])) {
    $messageID = (int)$_POST['messageID'];
    $userID = $_SESSION['userID'] ?? 1; // Utilisateur par défaut ou connecté

    // Vérifier si l'utilisateur a déjà disliké ce message
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM user_reactions WHERE userID = :userID AND messageID = :messageID AND reactionType = 'dislike'");
        $stmt->execute([':userID' => $userID, ':messageID' => $messageID]);
        $alreadyDisliked = $stmt->fetchColumn() > 0;

        if (!$alreadyDisliked) {
            // Insérer l'action du dislike dans la table user_reactions
            $stmt = $pdo->prepare("INSERT INTO user_reactions (userID, messageID, reactionType) VALUES (:userID, :messageID, 'dislike')");
            $stmt->execute([':userID' => $userID, ':messageID' => $messageID]);

            // Incrémenter le compteur de dislikes du message
            $stmt = $pdo->prepare("UPDATE comment SET dislikeCount = dislikeCount + 1 WHERE commentID = :messageID AND contentID = :forumID AND pk_ContentType = 'forum'");
            $stmt->execute([':messageID' => $messageID, ':forumID' => $forumID]);

            $dislikeConfirmation = "Vous avez disliké ce message.";
        } else {
            $dislikeError = "Vous avez déjà disliké ce message.";
        }
    } catch (PDOException $e) {
        $dislikeError = "Erreur lors du dislike : " . $e->getMessage();
    }
}

// Récupération des messages après modification des compteurs
try {
    $stmt = $pdo->prepare("
        SELECT pk_userID, commetContent, likeCount, dislikeCount, commentID 
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
    <link rel="stylesheet" href="forum.css">

</head>
<body>

<?php include 'pagesOutils/header.php'; ?>

<main>
    <section id="forum-messages">
        <h2><?php echo htmlspecialchars($forum['forumTitle']); ?></h2>
        <h3>Description: <?php echo htmlspecialchars($forum['description']); ?></h3>

        <!-- Notification -->
        <?php if (isset($messageConfirmation)): ?>
            <p class="success"><?php echo $messageConfirmation; ?></p>
        <?php elseif (isset($messageErreur)): ?>
            <p class="error"><?php echo $messageErreur; ?></p>
        <?php elseif (isset($likeConfirmation)): ?>
            <p class="success"><?php echo $likeConfirmation; ?></p>
        <?php elseif (isset($likeError)): ?>
            <p class="error"><?php echo $likeError; ?></p>
        <?php elseif (isset($dislikeConfirmation)): ?>
            <p class="success"><?php echo $dislikeConfirmation; ?></p>
        <?php elseif (isset($dislikeError)): ?>
            <p class="error"><?php echo $dislikeError; ?></p>
        <?php endif; ?>

        <!-- Affichage des messages -->
        <div id="messages">
            <?php if ($messages): ?>
                <?php foreach ($messages as $message): ?>
                    <div class="message">
                        <strong>Utilisateur <?php echo htmlspecialchars($message['pk_userID']); ?></strong> :
                        <p><?php echo htmlspecialchars($message['commetContent']); ?></p>
                        <form method="post">
                            <input type="hidden" name="messageID" value="<?php echo $message['commentID']; ?>">
                            <input type="hidden" name="forumID" value="<?php echo $forumID; ?>">

                            <button type="submit" name="like">Liker</button>
                            <button type="submit" name="dislike">Disliker</button>
                        </form>
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
