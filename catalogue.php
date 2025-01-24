<?php
    session_start();
?>
<!DOCTYPE html>
<html>
<head>
    <link rel="icon" href="img/obLogo.png" type="image/x-icon">
    <link rel="stylesheet" href="catalogue.css">
    <link rel="stylesheet" href="style.css">
    <script src="script.js"></script>
</head>
<body>
<?php include 'pagesOutils/header.php'; ?>

<main>
    <section id="catalogue">
        <h2>Catalogue</h2>
        <div class="catalogue-container">
            <?php
            $servername = "localhost";
            $username = "root";
            $password = "";
            $dbname = "ob";

            $conn = new mysqli($servername, $username, $password, $dbname);

            if ($conn->connect_error) {
                die("Erreur de connexion : " . $conn->connect_error);
            }

            $sql = "
                SELECT contentID, name, type, description ,posterUrl
                FROM (
                    SELECT contentID, name, 'Film' AS type, description,posterUrl FROM film
                    UNION ALL
                    SELECT contentID, name, 'Série' AS type, description,posterUrl FROM series
                ) AS catalogue
                ORDER BY type, name;
                ";

            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
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

            $conn->close();
            ?>
        </div>
    </section>
</main>

<?php include 'pagesOutils/footer.php'; ?>
</body>
</html>
