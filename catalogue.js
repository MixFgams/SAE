document.querySelectorAll(".viewMoreCatalogue").forEach((button) => {
    let removerClass = button.getAttribute("param") ;
    console.log(removerClass) ;
    button.addEventListener("click", function(evt) {
        if (removerClass !== null) {
            document.querySelectorAll('.'+removerClass).forEach((image) => {
                image.classList.toggle('invisible') ;
            }) ;
            button.classList.toggle('invisible') ;
        }
    })
}) ;