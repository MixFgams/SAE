//-----------------------------------------------------------------------------------------------------------------------------------//
//-----------------------------------------------------------------------------------------------------------------------------------//
document.addEventListener("DOMContentLoaded", function () {
    initShowMoreButton();
    initScrollButtons();
});
//-----------------------------------------------------------------------------------------------------------------------------------//


//-----------------------------------------------------------------------------------------------------------------------------------//
//-----------------------------------------------------------------------------------------------------------------------------------//
function initShowMoreButton() {
    try {
        const showMoreBtn = document.getElementById("showMoreBtn");
        const extraForums = document.querySelectorAll(".forums-container .forum-item:nth-child(n+3)");

        showMoreBtn.addEventListener("click", function () {
            toggleShowMore(showMoreBtn, extraForums);
        });
    } catch (error){
        console.log();
    }
}
//-----------------------------------------------------------------------------------------------------------------------------------//


//-----------------------------------------------------------------------------------------------------------------------------------//
//-----------------------------------------------------------------------------------------------------------------------------------//
function toggleShowMore(button, forums) {
    if (button.innerText === "Voir plus +") {
        forums.forEach(forum => forum.style.display = "flex");
        button.innerText = "Voir moins -";
    } else {
        forums.forEach(forum => forum.style.display = "none");
        button.innerText = "Voir plus +";
    }
}
//-----------------------------------------------------------------------------------------------------------------------------------//


//-----------------------------------------------------------------------------------------------------------------------------------//
//-----------------------------------------------------------------------------------------------------------------------------------//
function initScrollButtons() {
    // Sélection de tous les conteneurs défilables
    const containers = document.querySelectorAll('.scrollable-container');

    containers.forEach(container => {
        // Récupère les boutons et le conteneur défilable associés
        const leftButton = container.querySelector('.scroll-button.left');
        const rightButton = container.querySelector('.scroll-button.right');
        const scrollableContent = container.querySelector('.scrollable-content');

        if (!leftButton || !rightButton || !scrollableContent) {
            console.warn('Boutons ou contenu défilable manquants dans un conteneur.');
            return;
        }

        console.log("Conteneur : ", container);
        console.log("Bouton gauche : ", leftButton);
        console.log("Bouton droit : ", rightButton);
        console.log("Contenu défilable : ", scrollableContent);


        // Définit la quantité de défilement
        const scrollAmount = 300;

        // Événements pour les boutons
        leftButton.addEventListener("click", function () {
            scrollableContent.scrollBy({ left: -scrollAmount, behavior: 'smooth' });
        });

        rightButton.addEventListener("click", function () {
            scrollableContent.scrollBy({ left: scrollAmount, behavior: 'smooth' });
        });
    });
}
//-----------------------------------------------------------------------------------------------------------------------------------//


//-----------------------------------------------------------------------------------------------------------------------------------//
//-----------------------------------------------------------------------------------------------------------------------------------//
//-----------------------------------------------------------------------------------------------------------------------------------//


//-----------------------------------------------------------------------------------------------------------------------------------//
//-----------------------------------------------------------------------------------------------------------------------------------//
document.querySelector('#createCollection').addEventListener('click', () => {
    // Crée un nouvel élément <h1>
    console.log("click");
    const form = document.createElement();
    const collectionContainer = document.querySelector('#ListeCollection');
    collectionContainer.appendChild(form);
});
//-----------------------------------------------------------------------------------------------------------------------------------//


//-----------------------------------------------------------------------------------------------------------------------------------//
//-------------------- Méthode pour rediriger vers la page d'un contenu lors d'un click dessus ---------------------------------------//
//-----------------------------------------------------------------------------------------------------------------------------------//
const parentsAfficheRedirection = document.querySelectorAll('.recommendations-scrollable.scrollable-content');
parentsAfficheRedirection.forEach(parent => {
    const enfants = parent.children;
    for (let enfant of enfants) {
        enfant.addEventListener('click', () => {
            // Redirection vers la page cible
            const lien = 'pageContenu.php';
            console.log(lien);
            console.log(window.location.href);
            window.location.href = lien;
        });
    }
});
//-----------------------------------------------------------------------------------------------------------------------------------//



//-----------------------------------------------------------------------------------------------------------------------------------//
//-------------------- Méthode pour afficher dynamiquement la barre de recherche              ---------------------------------------//
//-----------------------------------------------------------------------------------------------------------------------------------//
const searchInput = document.getElementById("searchInput");
const suggestionsBox = document.getElementById("suggestions");

searchInput.addEventListener("input", () => {
    const query = searchInput.value.trim();

    if (query.length > 0) {
        fetch(`pagesOutils/header.php?query=${encodeURIComponent(query)}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error("Erreur réseau");
                }
                return response.json();
            })
            .then(data => {
                suggestionsBox.innerHTML = ""; // Vider les suggestions précédentes

                if (data.length > 0) {
                    data.forEach(item => {
                        // Conteneur pour chaque suggestion
                        const suggestion = document.createElement("div");
                        suggestion.classList.add("suggestion-item");

                        // Image
                        const img = document.createElement("img");
                        img.src = 'img/afficheFilm.jpg';
                        img.alt = item.name;
                        img.classList.add("suggestion-image");

                        // Titre
                        const text = document.createElement("span");
                        text.textContent = item.name;
                        text.classList.add("suggestion-text");

                        // Ajout d'un événement de clic
                        suggestion.addEventListener("click", () => {
                            window.location.href = `catalogue.php?id=${item.ContentID}`;
                        });

                        // Ajout de l'image et du texte au conteneur
                        suggestion.appendChild(img);
                        suggestion.appendChild(text);

                        // Ajout de la suggestion au conteneur de la boîte
                        suggestionsBox.appendChild(suggestion);
                    });
                    suggestionsBox.style.display = "block"; // Afficher la boîte
                } else {
                    suggestionsBox.innerHTML = "<div class='suggestion-item'>Aucun résultat</div>";
                    suggestionsBox.style.display = "none";
                }

            })
            .catch(error => console.error("Erreur lors de la recherche :", error));
    } else {
        suggestionsBox.style.display = "none"; // Masquer la boîte si aucune requête
    }
});

// Fermer la boîte si on clique ailleurs
document.addEventListener("click", (event) => {
    if (!event.target.closest(".search-container")) {
        suggestionsBox.style.display = "none";
    }
});



//-----------------------------------------------------------------------------------------------------------------------------------//



