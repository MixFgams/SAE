<?php 
// Connexion à la base de données
$DBservername = "localhost";
$DBusername = "root";
$DBpassword = "";
$DBdatabase = "OB";

$conn = new mysqli($DBservername, $DBusername, $DBpassword, $DBdatabase);

// Vérifier la connexion
if ($conn->connect_error) {
    die("Échec de la connexion : " . $conn->connect_error); // Arrête le script si la connexion échoue
}