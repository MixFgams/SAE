document.querySelectorAll('a').forEach(lien=>{
    lien.addEventListener('mouseover',()=>{
        lien.style.color='red';
    });
    lien.addEventListener('mouseout',()=>{
        lien.style.color='';
    })
    }
)

document.querySelectorAll('img').forEach(img=>{
    img.addEventListener('click',()=>{
        window.location.href='profile.php';
    })
});



