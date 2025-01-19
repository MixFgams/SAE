<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forum de la Communauté</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<?php
// Inclusion du header
include 'pagesOutils/header.php';

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
?>

<main>
    <section id="forum">
        <h2>Forum de Discussion</h2>

        <!-- Affichage des messages -->
        <div id="discussion">
            <?php
            // Gestion de la pagination
            $itemsPerPage = 10;
            $page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
            $offset = ($page - 1) * $itemsPerPage;
            $totalPages = 0;

            try {
                // Requête pour récupérer les forums
                $sql = "SELECT forumTitle, description FROM forum LIMIT :limit OFFSET :offset";
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
                        echo '<h3>' . htmlspecialchars($forum['title']) . '</h3>';
                        echo '<p>' . htmlspecialchars($forum['description']) . '</p>';
                        echo '</div>';
                    }
                } else {
                    echo "<p>Aucun message pour le moment.</p>";
                }
            } catch (PDOException $e) {
                echo "<p>Erreur lors de la récupération des forums : " . $e->getMessage() . "</p>";
            }
            ?>
        </div>

        <!-- Formulaire pour ajouter un message -->
        <form method="post">
            <input name="message" type="text" placeholder="Écrivez votre message ici" required>
            <input type="submit" value="Envoyer">
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

<?php
// Inclusion du footer
include 'pagesOutils/footer.php';
?>

<script src="communaute.js"></script>
</body>
</html>
