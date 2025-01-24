<?php
session_start();

// Vérification si la variable de session 'idForum' existe
if (isset($_SESSION['idForum'])) {
    $forumID = $_SESSION['idForum'];
} else {
    echo "Forum non sélectionné. Veuillez revenir à la liste des forums.";
    exit;
}

if(isset($_SESSION['userID'])) {
    $userID = $_SESSION['userID'];
} else {
    $userID = 1;
}

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

// Récupération des informations du forum
function getForumInfo($pdo, $forumID) {
    $stmt = $pdo->prepare("SELECT title, subject.description, forumTitle 
                           FROM subject 
                           JOIN forum 
                           ON subject.pk_ForumID = forum.forumID 
                           WHERE pk_ForumID = :forumID");
    $stmt->execute([':forumID' => $forumID]);
    $forum = $stmt->fetch(PDO::FETCH_ASSOC);

    return $forum;
}


$forum = getForumInfo($pdo, $forumID);

// Ajout d'un nouveau message
function addMessage($pdo, $forumID, $userID, $message) {
    $stmt = $pdo->prepare("
        INSERT INTO comment (contentID, pk_userID, commetContent, pk_ContentType, likeCount, dislikeCount) 
        VALUES (:forumID, :userID, :message, 'forum', 0, 0)
    ");
    $stmt->execute([
        ':forumID' => $forumID,
        ':userID' => $userID,
        ':message' => trim($message),
    ]);
}

if (isset($_POST['envoyer']) && !empty($_POST['message'])) {
    $userID = $_SESSION['userID'] ?? 1; // Utilisateur par défaut
    addMessage($pdo, $forumID, $userID, $_POST['message']);
    $messageConfirmation = "Votre message a été ajouté avec succès !";
}

function handleReaction($pdo, $forumID, $userID, $messageID, $reactionType) {
    $oppositeReaction = $reactionType === 'like' ? 'dislike' : 'like';

    $stmt = $pdo->prepare("
        SELECT {$reactionType}Count, {$oppositeReaction}Count 
        FROM comment 
        WHERE commentID = :messageID
    ");
    $stmt->execute([':messageID' => $messageID]);
    $reactionCounts = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$reactionCounts) {
        return "Le commentaire n'existe pas.";
    }

    // Si l'utilisateur a déjà réagi (vérification avec les compteurs)
    if ($reactionCounts[$reactionType . 'Count'] > 0) {
        return "Vous avez déjà réagi à ce commentaire avec un {$reactionType}.";
    }

    if ($reactionCounts[$oppositeReaction . 'Count'] > 0) {
        // Supprimer l'opposée (réduction du compteur dans la base de données)
        $stmt = $pdo->prepare("
            UPDATE comment 
            SET {$oppositeReaction}Count = {$oppositeReaction}Count - 1 
            WHERE commentID = :messageID
        ");
        $stmt->execute([':messageID' => $messageID]);
    }

    $stmt = $pdo->prepare("
        UPDATE comment 
        SET {$reactionType}Count = {$reactionType}Count + 1 
        WHERE commentID = :messageID
    ");
    $stmt->execute([':messageID' => $messageID]);

    return "Votre réaction a été enregistrée.";
}

if (isset($_POST['like']) || isset($_POST['dislike'])) {
    $userID = $_SESSION['userID'] ?? 1; // Assurez-vous que la session de l'utilisateur est correctement gérée
    $messageID = (int)$_POST['messageID'];
    $reactionType = isset($_POST['like']) ? 'like' : 'dislike';
    $reactionMessage = handleReaction($pdo, $forumID, $userID, $messageID, $reactionType);
}


if (isset($_POST['like']) || isset($_POST['dislike'])) {
    $userID = $_SESSION['userID'] ?? 1;
    $messageID = (int)$_POST['messageID'];
    $reactionType = isset($_POST['like']) ? 'like' : 'dislike';
    $reactionMessage = handleReaction($pdo, $forumID, $userID, $messageID, $reactionType);
}

// Récupération des messages
function getMessages($pdo, $subjectID) {
    $stmt = $pdo->prepare("
        SELECT commentID, pk_userID, commetContent, likeCount, dislikeCount, username
        FROM comment 
        join user on comment.pk_UserID = user.userID
        WHERE contentID = :subjectID AND pk_ContentType = 'subject'
    ");
    $stmt->execute([':subjectID' => $subjectID]);
    return $stmt->fetchAll();
}

// Fonction pour récupérer la liste des sujets d'un forum
function getSubjectsByForum($pdo, $forumID) {
    $stmt = $pdo->prepare("
        SELECT 
            subjectID, 
            title, 
            description, 
            
            user.username, 
            pk_ForumID, 
            contentType 
        FROM 
            subject 
        JOIN 
            user on user.userID = subject.pk_UserID
        WHERE 
            pk_ForumID = :forumID
    ");
    $stmt->execute([':forumID' => $forumID]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$subjects = getSubjectsByForum($pdo, $forumID);


// Si le formulaire est soumis pour publier un article
if (isset($_POST['publish'])) {
    // Récupérer les données du formulaire
    $title = trim($_POST['title']);
    $description = trim($_POST['content']);

    if (!empty($title) && !empty($description)) {
        $sql = "INSERT INTO subject (title, description, pk_userID, pk_forumID)
        VALUES (:title, :content, :pk_userID, :forumID)";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':title' => $title,
            ':content' => $description,
            ':pk_userID' => $userID,
            ':forumID' => $forumID,
        ]);

        $messageConfirmation = "Votre article a été publié avec succès !";
    } else {
        $messageConfirmation = "Veuillez remplir tous les champs.";
    }
}



?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($forum['forumTitle']); ?></title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="forum.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
<?php include 'pagesOutils/header.php'; ?>

<main>

    <h1><?php
        if(!empty($forum)){
            echo htmlspecialchars($forum['forumTitle']);
    } ?>      </h1>
        <!-- Notifications -->
        <?php if (isset($messageConfirmation)) echo "<p class='success'>$messageConfirmation</p>"; ?>

    <?php

    // Calculer le numéro de la page actuelle (page 1 par défaut)
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = 5; // Nombre de résultats par page
    $offset = ($page - 1) * $limit; // Décalage de la pagination

    // Requête pour récupérer les sujets avec jointure correcte
    $query = $pdo->prepare("SELECT subjectID, title, description, pk_UserID, pk_ForumID, contentType, username
            FROM subject 
            JOIN user ON user.userID = subject.pk_UserID
            WHERE pk_ForumID = :forumID 
            LIMIT $limit OFFSET $offset");  // Insertion des valeurs de limit et offset directement

    // Utilisation du bindValue pour lier pk_ForumID uniquement
    $query->bindValue(':forumID', $forumID, PDO::PARAM_INT);

    // Exécution de la requête
    $query->execute();

    // Récupération des résultats
    $subjects = $query->fetchAll(PDO::FETCH_ASSOC);

    // Requête pour compter le nombre total d'éléments
    $query = $pdo->prepare("SELECT COUNT(*) FROM subject WHERE pk_ForumID = :forumID");
    $query->bindValue(':forumID', $forumID, PDO::PARAM_INT);
    $query->execute();
    $totalItems = $query->fetchColumn();



    $totalPages = ceil($totalItems / $limit);
        // Affichage des sujets
        if (!empty($subjects)): ?>
            <div id="subject">
                <h2>Liste des sujets</h2>
                <?php foreach ($subjects as $subject): ?>
                    <div class="subject">
                        <h3><?php echo htmlspecialchars($subject['title']); ?></h3>
                        <p>Nom d'utilisateur : <?php echo htmlspecialchars($subject['username']); ?></p>
                        <p><?php echo htmlspecialchars($subject['description']); ?></p>

                        <!-- Affichage des messages associés -->
                        <div id="messages">
                            <h4>Messages :</h4>
                            <?php
                            // Appel de la fonction pour récupérer les messages du sujet courant
                            $messages = getMessages($pdo, $subject['subjectID']);
                            if ($messages):
                                foreach ($messages as $message): ?>
                                    <div class="message">
                                        <strong>Utilisateur <?php echo htmlspecialchars($message['username']); ?></strong> :
                                        <p><?php echo htmlspecialchars($message['commetContent']); ?></p>
                                        <form method="post">
                                            <input type="hidden" name="messageID" value="<?php echo $message['commentID']; ?>">
                                            <input type="hidden" name="forumID" value="<?php echo htmlspecialchars($subject['pk_ForumID']); ?>">
                                            <button type="submit" name="like" class="like-button"><i class="fa fa-thumbs-up"></i> Like</button>
                                            <button type="submit" name="dislike" "dislike-button"> <i class="fa fa-thumbs-down"></i> Dislike</button>
                                        </form>
                                        <p>Likes : <?php echo $message['likeCount']; ?> | Dislikes : <?php echo $message['dislikeCount']; ?></p>
                                    </div>
                                <?php endforeach;
                            else: ?>
                                <p>Aucun message trouvé pour ce sujet.</p>
                            <?php endif; ?>
                        </div>
                        <!-- Formulaire pour poster un message -->
                        <div id="post-message">
                            <h4>Poster un message :</h4>
                            <form method="post" action="post_message.php">
                                <textarea name="messageContent" rows="2" placeholder="Écrivez votre commentaire ici..." required></textarea>
                                <input type="hidden" name="subjectID" value="<?php echo $subject['subjectID']; ?>">
                                <button type="submit">Envoyer</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p>Aucun sujet trouvé pour ce forum.</p>
        <?php endif; ?>

    <section>
        <h2>Ajouter votre article ou votre question</h2>
        <!-- Formulaire pour ajouter un article  -->
        <form method="post">
            <input type="hidden" name="forumID" value="<?php echo $forumID; ?>">
            <label for="title">Titre :</label>
            <input type="text" id="title" name="title" required>
            <label for="content">Contenu :</label>
            <textarea id="content" name="content" required></textarea>
            <button type="submit" name="publish">Publier</button>
        </form>

    </section>
    <!-- Pagination -->
    <ul class="pagination">
        <!-- Numéros de page -->
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <li>
                <a href="?page=<?php echo $i; ?>" class="<?php echo $i === $page ? 'active' : ''; ?>">
                    <?php echo $i; ?>
                </a>
            </li>
        <?php endfor; ?>

        <!-- Bouton Suivant -->
        <?php if ($page < $totalPages): ?>
            <li><a href="?page=<?php echo $page + 1; ?>">Suivant</a></li>
        <?php else: ?>
            <li><a class="disabled">Suivant</a></li>
        <?php endif; ?>

        <!-- Bouton Début -->
        <?php if ($page > 1): ?>
            <li><a href="?page=1">Début</a></li>
        <?php else: ?>
            <li><a class="disabled">Début</a></li>
        <?php endif; ?>

        <!-- Bouton Fin -->
        <?php if ($page < $totalPages): ?>
            <li><a href="?page=<?php echo $totalPages; ?>">Fin</a></li>
        <?php else: ?>
            <li><a class="disabled">Fin</a></li>
        <?php endif; ?>
    </ul>

</main>

<?php include 'pagesOutils/footer.php'; ?>
</body>
</html>
