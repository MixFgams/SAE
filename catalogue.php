<!DOCTYPE html>
<html>
<head>
    <link rel="icon" href="img/obLogo.png" type="image/x-icon">
    <link rel="stylesheet" href="catalogue.css">
    <link rel="stylesheet" href="style.css">

</head>
<body>
<?php
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
?>

<?php include 'pagesOutils/header.php'; ?>

<main>
    <section id="catalogue">
        <h2>Catalogue</h2>
        <div class="catalogue-container">
            <?php
            $sql = "
                SELECT contentID, name, type, description ,posterUrl
                FROM (
                    SELECT contentID, name, 'Film' AS type, description,posterUrl FROM film
                    UNION ALL
                    SELECT contentID, name, 'Série' AS type, description,posterUrl FROM series
                ) AS catalogue
                ORDER BY type, name;
                ";

            $stmt = $pdo->query($sql);

            if ($stmt->rowCount() > 0) {
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    // Créer un lien cliquable pour chaque élément du catalogue
                    echo '<a href="pageContenu.php?id=' . htmlspecialchars($row['contentID']) . '&type=' . htmlspecialchars($row['type']) . '" class="catalogue-item-link">';
                    echo '<div class="catalogue-item">';
                    echo '<div class="catalogue-item-image"><img src="' . htmlspecialchars($row['posterUrl']) . '" alt="' . htmlspecialchars($row['name']) . '"></div>';
                    echo '<div class="catalogue-item-info">';
                    echo '<div class="catalogue-item-title">' . htmlspecialchars($row['name']) . ' (' . htmlspecialchars($row['type']) . ')</div>';
                    echo '<div class="catalogue-item-description">' . htmlspecialchars($row['description']) . '</div>';
                    echo '</div>';
                    echo '</div>';
                    echo '</a>';
                }
            } else {
                echo "<p>Aucun élément trouvé dans le catalogue.</p>";
            }
            ?>
        </div>
    </section>
</main>

<?php include 'pagesOutils/footer.php'; ?>
<script src="./script.js"></script>
</body>
</html>
