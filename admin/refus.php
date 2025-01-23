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
    <meta http-equiv="refresh" content="5;url=../index.php">
</head>
<body>
    <?php include '../pagesOutils/header.php'?>

    <main>
        <section>
            <h1>Accès refusé</h1>
            <p>Vous n'avez pas les autorisations nécessaires pour accéder à cette page.</p>
            <p>Vous serez redirigé vers la page d'accueil dans 5 secondes...</p>
        </section>
    </main>

    <?php include '../pagesOutils/footer.php'?>
</body>
</html>
