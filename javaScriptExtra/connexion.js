// Affiche le formulaire de connexion
function montrerConnexion() {
    document.getElementById("connexion-form").style.display = "block";
    document.getElementById("inscription-form").style.display = "none";
}

// Affiche le formulaire d'inscription
function montrerInscription() {
    document.getElementById("inscription-form").style.display = "block";
    document.getElementById("connexion-form").style.display = "none";
}

// Affiche les champs pour se connecter avec un pseudo
function connexionPseudo() {
    document.getElementById("connexion-email").style.display = "none";
    document.getElementById("connexion-pseudo").style.display = "block";
}

// Affiche les champs pour se connecter avec un email
function connexionEmail() {
    document.getElementById("connexion-pseudo").style.display = "none";
    document.getElementById("connexion-email").style.display = "block";
}

// Initialiser l'affichage au chargement
window.onload = function() {
    montrerConnexion(); // Afficher le formulaire de connexion
    connexionEmail(); // Afficher le champ email par défaut
};
