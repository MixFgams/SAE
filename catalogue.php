<!DOCTYPE html>
<html>
<head>
    <link rel="icon" src="img/obLogo.png" type="image/x-icon">
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
            // faudra faire une page pdo pour eviter de refaire a chaque fois
            $servername = "localhost";
            $username = "root";
            $password = "";
            $dbname = "ob";

            $conn = new mysqli($servername, $username, $password, $dbname);

            if ($conn->connect_error) {
                die("Erreur de connexion : " . $conn->connect_error);
            }

            // Requête pour récupérer tous les livres, films et séries
            $sql = "
                SELECT contentID, name, type, description 
                FROM (
                    SELECT contentID, name, 'Film' AS type, description FROM film
                    UNION ALL
                    SELECT contentID, name, 'Série' AS type, description FROM series
                ) AS catalogue
                ORDER BY type, name;
                ";

            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo '<div class="catalogue-item">';
                    echo '<h3>' . htmlspecialchars($row['name']) . ' (' . htmlspecialchars($row['type']) . ')</h3>';
                    echo '<p>' . htmlspecialchars($row['description']) . '</p>';
                    echo '</div>';
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
