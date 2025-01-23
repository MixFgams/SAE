<?php
session_start();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forum de la Communauté</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="communaute.css">

</head>
<body>

<?php
// Inclusion du header
include 'pagesOutils/header.php';
include 'pagesOutils/connDB.php' ;

// Inclusion du header
include 'pagesOutils/header.php';

// Gestion des messages envoyés via le formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['message'])) {
    $message = htmlspecialchars($_POST['message']);
    $username = "Admin"; // Placeholder avant intégration du système d'authentification

    $sql = "INSERT INTO messages (username, content) VALUES (:username, :content)";
    $stmt = $pdo->prepare($sql);

    try {
        $stmt->execute([':username' => $username, ':content' => $message]);
        echo "<p>Message envoyé avec succès.</p>";
    } catch (PDOException $e) {
        echo "<p>Erreur lors de l'envoi du message : " . $e->getMessage() . "</p>";
    }
}
if (isset ($_POST['creerForum'])){

    echo '<div>
          <form method="post">
          <input type="text " name="forumName" placeholder="Nom du forum">
          <input type="text " name="forumDescription" placeholder="description">
          <input type="submit" name="creerMonForum" value="Creer">
          </div>
';

}
if (isset($_POST['creerMonForum']) && isset($_POST['forumName']) && isset($_POST['forumDescription'])) {
    // Sanitize input
    $forumName = htmlspecialchars($_POST['forumName']);
    $forumDescription = htmlspecialchars($_POST['forumDescription']);
    $creationDate = date('Y-m-d H:i:s'); // Date de création actuelle

    // Insertion du forum dans la base de données
    $sql = "INSERT INTO forum (forumTitle, description, creationDate, pk_ContentID, totalSubjectNumber, pk_ContentType) 
            VALUES (:forumTitle, :description, :creationDate, :pk_ContentID, :totalSubjectNumber, :pk_ContentType)";
    $stmt = $pdo->prepare($sql);

    try {
        $stmt->execute([
            ':forumTitle' => $forumName,
            ':description' => $forumDescription,
            ':creationDate' => $creationDate,
            ':pk_ContentID' => 1, // Valeur de pk_ContentID (si nécessaire)
            ':totalSubjectNumber' => 0, // Nombre de sujets initialement
            ':pk_ContentType' => 1 // Type de contenu (ajuster si nécessaire)
        ]);
        echo "<p>Le forum a été créé avec succès.</p>";
    } catch (PDOException $e) {
        echo "<p>Erreur lors de la création du forum : " . $e->getMessage() . "</p>";
    }
}

?>

<main>
    <form method="GET">
        <label for="barreRecherche">Recherchez un forum : </label>
        <input id="barreRecherche" name="barreRecherche" type="search">
        <h1>Liste des articles</h1>
        <?php
        if (!empty($_GET['barreRecherche'])) {
            $_isSearched = true;
            $sql = "SELECT forumID, forumTitle, description, creationDate
            FROM forum WHERE forumTitle LIKE :barreRecherche";
            $recherche = $_GET['barreRecherche'];
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['barreRecherche' => "%$recherche%"]);

            echo "<h2>Forums correspondant à : " . htmlspecialchars($_GET['barreRecherche']) . "</h2>";
            echo '<div class="forum-container">'; // Ajout du conteneur principal

            if ($stmt->rowCount() > 0) {
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    echo '<div class="Forum">'; // Utilisation de la même classe que pour les forums normaux
                    echo '<h3>' . htmlspecialchars($row['forumTitle']) . '</h3>';
                    echo '<p>' . htmlspecialchars($row['description']) . '</p>';
                    echo '<p>Date de création : ' . htmlspecialchars($row['creationDate']) . '</p>';
                    echo '<form method="post" action="forum.php">
                    <input type="hidden" name="forumID" value="' . htmlspecialchars($row['forumID']) . '">
                    <input type="submit" value="Rejoindre">
                  </form>';
                    echo '</div>';
                }
            } else {
                echo '<p>Aucun forum trouvé.</p>';
            }

            echo '</div>'; // Fermeture du conteneur principal
        }
        ?>
    </form>
    <section id="forum">
        <h2>Forum de Discussion</h2>
        <form method="POST">
            <input type="submit" name="creerForum" value="Créer mon forum">
        </form>
        <!-- Affichage des messages -->
        <div id="discussion">

            <?php
            // Gestion de la pagination
            $itemsPerPage = 10;
            $page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
            $offset = ($page - 1) * $itemsPerPage;

            try {
                // Requête pour récupérer les forums
                $sql = "SELECT forumID,forumTitle, description FROM forum LIMIT :limit OFFSET :offset";
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':limit', $itemsPerPage, PDO::PARAM_INT);
                $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
                $stmt->execute();

                $forums = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // Requête pour compter le nombre total de forums
                $totalItems = $pdo->query("SELECT COUNT(*) FROM forum")->fetchColumn();
                $totalPages = ceil($totalItems / $itemsPerPage);

                if (!empty($forums)) {
                    foreach ($forums as $forum) {
                        echo '<div class="Forum">';
                        echo '<h3>' . htmlspecialchars($forum['forumTitle']) . '</h3>';
                        echo '<p>' . htmlspecialchars($forum['description']) . '</p>';
                        echo '<form method="post" action="forum.php">
        <input type="hidden" name="forumID" value="' . $forum['forumID'] . '">
        <input type="submit" value="Rejoindre">
      </form>';


                        echo '</div>';
                    }
                } else {
                    echo "<p>Aucun forum pour le moment.</p>";
                }
            } catch (PDOException $e) {
                echo "<p>Erreur lors de la récupération des forums : " . $e->getMessage() . "</p>";
            }
            if (isset($_GET['acceder'])) {
                $_SESSION['idForum'] = $forum['forumID'];
                header('Location: forum.php');
            }
            ?>
        </div>


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

<?php
// Inclusion du footer
include 'pagesOutils/footer.php';
?>

<script src="communaute.js"></script>
<script src="script.js"></script>
</body>
</html>
