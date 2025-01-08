
window.addEventListener('load', function() {
    // Code pour sélectionner l'image correspondante dans le carousel


    // Récupère l'ID de l'avatar de l'utilisateur depuis la base de données
    let avatarId = avatar;



// Sélectionne l'image correspondante dans le carousel
    let carouselItems = document.querySelectorAll('.carousel-item');

    //Parcours toutes les slides du carousel pour trouver celle qui correspond à l'id de l'avatar de l'utilisateur


    for (let i = 0; i < carouselItems.length; i++) {
        let image = carouselItems[i].querySelector('img');
        if (image.getAttribute('data-avatar') === avatarId) {
            carouselItems[i].classList.add('active');
            break;
        }
    }


});




//Creation du système de changement d'avatar


document.querySelector('.select-character-button').addEventListener("click", function (){

    let divToDelete = document.querySelector('.message-avatar');
    if (divToDelete){
        divToDelete.remove();
    }

    let activeImage = document.querySelector('.carousel-item.active img');

    // Récupère l'ID de l'avatar correspondant à l'image active
    let avatarId = activeImage.getAttribute('data-avatar');

    let postData = new FormData();

    postData.append('avatarId', avatarId);

    let messageDiv = document.createElement('div');

    document.querySelector('.select-character-button').after(messageDiv);

    messageDiv.classList.add('message-avatar')

    fetch(profilPageApi, {
        method: 'POST',
        body: postData,
    })
        .then((response) => response.json())


        .then(data => {
            console.log(data.avatarId);

            messageDiv.innerHTML = '<p>' + data.success + '</p>';



        })
        .catch(error => {

            messageDiv.innerHTML = '<p>' + data.error + '</p>';

            console.log(error);

        });



})