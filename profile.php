<!DOCTYPE html>
<html>
    <head>
        <link rel="icon" href="img/obLogo.png   " type="image/x-icon">
        <link rel="stylesheet" href="style.css">
    </head>
    <?php
    session_start();
    include 'pagesOutils/connDB.php' ;

    if(isset($_SESSION['idUser'])) {
        $userID = $_SESSION['idUser'];
    } else {
        $userID = 0;
    }
    ?>

    <body>
        <?php include 'pagesOutils/header.php'?>


        <?php
        $uploadDir = __DIR__ . "/uploads/";
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['uploadPhoto'])) {
            if (isset($_FILES['profilePicture']) && $_FILES['profilePicture']['error'] == 0) {
                $fileTmpPath = $_FILES['profilePicture']['tmp_name'];
                $fileName = $_FILES['profilePicture']['name'];
                $fileSize = $_FILES['profilePicture']['size'];
                $fileType = $_FILES['profilePicture']['type'];
                $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

                $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];

                if (in_array($fileExt, $allowedExtensions)) {
                    $newFileName = "profile_" . $userID . "." . $fileExt;
                    $uploadDir = "uploads/";
                    $destPath = $uploadDir . $newFileName;

                    // Déplace le fichier vers le dossier uploads/
                    if (move_uploaded_file($fileTmpPath, $destPath)) {
                        // Mise à jour de la base de données
                        $stmt = $pdo->prepare("UPDATE user SET profilePicture = :profilePicture WHERE userID = :userID");
                        $stmt->execute([':profilePicture' => $destPath, ':userID' => $userID]);

                        echo "<p style='color:green;'>Photo de profil mise à jour avec succès !</p>";
                    } else {
                        echo "<p style='color:red;'>Erreur lors du déplacement du fichier.</p>";
                    }
                } else {
                    echo "<p style='color:red;'>Seuls les fichiers JPG, JPEG, PNG et GIF sont autorisés.</p>";
                }
            } else {
                echo "<p style='color:red;'>Aucun fichier valide n'a été uploadé.</p>";
            }
        }
        ?>

        <main>
            <?php
                try {
                // Connexion déjà établie dans $pdo
                // Préparer la requête pour récupérer les informations de l'utilisateur
                $stmt = $pdo->prepare("
                SELECT username, profilePicture, registrationDate, userType
                FROM user
                WHERE userID = :userID
                ");
                $stmt->execute([':userID' => $userID]);

                // Récupérer les données de l'utilisateur
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($user) {
                // Définir les variables nécessaires
                $username = htmlspecialchars($user['username']);
                $profilePicture = $user['profilePicture'] ?: 'img/pdp.jpg'; // Image par défaut si aucune n'est définie
                $registrationDate = date('d/m/Y', strtotime($user['registrationDate']));
                $userType = htmlspecialchars($user['userType']);

                // Simuler un "niveau" utilisateur (par exemple, basé sur `userType`)
                $niveau = ($userType === 'admin') ? 'Admin' : 'Utilisateur standard';

                // Barre de progression simulée (exemple, en pourcentage)
                $progressPercent = ($userType === 'admin') ? 100 : 50;
                } else {
                echo "Utilisateur non trouvé.";
                exit;
                }
                } catch (PDOException $e) {
                echo "Erreur : " . $e->getMessage();
                exit;
                }
            ?>
            <div id="user">
                <section>
                    <img src="<?= htmlspecialchars($profilePicture) ?>" alt="Photo de l'utilisateur">
                    <div id="detail-user">
                        <h3><?= $username ?></h3>
                        <p>Inscrit depuis le : <?= $registrationDate ?></p>
                        <p>Niveau : <?= $niveau ?></p>
                        <div id="progress-container">
                            <div id="progress-bar" style="width: <?= $progressPercent ?>%;"></div>
                        </div>
                        <form method="post" enctype="multipart/form-data">
                            <input type="file" name="profilePicture" accept="image/*" required>
                            <input type="submit" name="uploadPhoto" value="Changer la photo">
                        </form>

                    </div>
                </section>
            </div>

            <section class="SectionIndex">
                    <h2>Collections suivies</h2>
                    <div class="scrollable-container">
                        <button class="scroll-button left" aria-label="Défiler à gauche">◀</button>
                        <div id="ListeCollection" class="scrollable-content">
                            <?php
                                $stmt = $pdo->prepare("SELECT name
                                    FROM collection
                                    WHERE pk_userID = :userID;
                                    ");
                                $stmt->execute([':userID' => $userID]);

                                if ($stmt->rowCount() > 0) {
                                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                        echo '<div class="Collection"><h3>' . htmlspecialchars($row['name']) . '</h3></div>';
                                    }
                                } else {
                                    echo '<p>Aucune collection trouvée.</p>';
                                }
                            ?>
                        </div>
                        <button class="scroll-button right" aria-label="Défiler à droite">▶</button>
                    </div>
            </section>

            <section class="SectionIndex">
                <h2>Film regardés</h2>
                <div class="scrollable-container">
                    <button class="scroll-button left" aria-label="Défiler à gauche">◀</button>
                    <div class="recommendations-scrollable scrollable-content">
                        <?php
                        $sql = "
                                SELECT film.contentID, film.posterUrl 
                                FROM film
                                JOIN filmwatched ON film.contentID = filmwatched.filmID
                                JOIN user ON user.userID = filmwatched.pk_UserID
                                WHERE user.userID = :userID
                                LIMIT 10
                            ";
                            $stmt = $pdo->prepare($sql);
                            $stmt->execute([':userID' => $userID]);

                            if ($stmt->rowCount() > 0) {
                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                echo '<div class="recommendation-card" data-id="' . htmlspecialchars($row['contentID']) . '">';
                                echo '<img src="' . htmlspecialchars($row['posterUrl']) . '" alt="' .  '">';
                                echo '</div>';
                            }
                            } else {
                            echo '<p>Aucuns films trouvés.</p>';
                            }
                        ?>
                    </div>
                    <button class="scroll-button right" aria-label="Défiler à droite">▶</button>
                </div>
            </section>

            <section class="SectionIndex">
                <h2>Série regardées</h2>
                <div class="scrollable-container">
                    <button class="scroll-button left" aria-label="Défiler à gauche">◀</button>
                    <div class="recommendations-scrollable scrollable-content">
                        <?php

                        $stmt = $pdo->prepare("
                            SELECT series.content, series.releaseDate
                            FROM series
                            JOIN serieswatched ON serieswatched.pk_SeriesID = series.contentID
                            JOIN user ON user.userID = serieswatched.pk_UserID
                            WHERE user.userID = :userID
                            ORDER BY series.releaseDate DESC
                            LIMIT 20
                        ");

                        if ($stmt->rowCount() > 0) {
                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                echo '<img src="img/afficheFilm.jpg" alt="' . htmlspecialchars($row['name']) . '">';
                            }
                        } else {
                            echo '<p>Aucunes séries trouvées.</p>';
                        }
                        ?>
                    </div>
                    <button class="scroll-button right" aria-label="Défiler à droite">▶</button>
                </div>
            </section>

            <section class="SectionIndex">
                <h2>Livres</h2>
                <div class="scrollable-container">
                    <button class="scroll-button left" aria-label="Défiler à gauche">◀</button>
                    <div class="recommendations-scrollable scrollable-content">
                        <img src="img/naruto.jpg" alt="Image de la recommandation">
                        <img src="img/naruto.jpg" alt="Image de la recommandation">
                        <img src="img/naruto.jpg" alt="Image de la recommandation">
                        <img src="img/naruto.jpg" alt="Image de la recommandation">
                        <img src="img/naruto.jpg" alt="Image de la recommandation">
                        <img src="img/naruto.jpg" alt="Image de la recommandation">
                    </div>
                    <button class="scroll-button right" aria-label="Défiler à droite">▶</button>
                </div>
            </section>
        </main>
    
        <?php include 'pagesOutils/footer.php'?>
        <script src="script.js"></script>
    </body>
</html>
