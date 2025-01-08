
//Js pour afficher les informations du mot de passe

//Pour afficher les infos du mdp quand on passe la souris dessus VERSION PC
document.querySelector('.fa-circle-info').addEventListener('mouseenter', function (){

    let info = document.querySelector('.password-infos');

    info.classList.add('active-info');

    info.classList.remove('inactive-info');

})

document.querySelector('.fa-circle-info').addEventListener('mouseleave', function (){

    let info = document.querySelector('.password-infos');

    info.classList.remove('active-info');

    info.classList.add('inactive-info');

})

//POUR TEL ET TABLETTES
document.querySelector('.fa-circle-info').addEventListener('click', function (){

    let info = document.querySelector('.password-infos');

    info.classList.add('active-info');

    info.classList.remove('inactive-info');

})
