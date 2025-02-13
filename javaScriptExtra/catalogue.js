document.querySelectorAll(".viewMoreCatalogue").forEach((button) => {
    let removerClass = button.getAttribute("param") ;
    button.addEventListener("click", function() {
        if (removerClass !== null) {
            document.querySelectorAll('.'+removerClass).forEach((image) => {
                image.classList.toggle('invisible') ;
            }) ;
            button.classList.toggle('invisible') ;
        }
    })
}) ;