<?php

//parametres de profondeur
$_SESSION["profendeurDossier"] = 1;


?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="../style.css"> 
    <title>Refus d'accès</title>
</head>
<body>
    <?php include '../pagesOutils/header.php'?>

    <main>
        <section>
            <h1>Accès refusé</h1>
            <p>Vous n'avez pas les autorisations nécessaires pour accéder à cette page.</p>
        </section>
    </main>

    <?php include '../pagesOutils/footer.php'?>
</body>
</html>