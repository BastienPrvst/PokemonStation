
const socket = io.connect("http://localhost:4000");

socket.on("connect", () => {

    console.log("Connecté avec l'ID :", socket.id);

    socket.emit('joinRoom', 'tradeRoom')

    let myButtons = document.querySelectorAll('.my-pokemon-trade');

    myButtons.forEach((button) => {
        button.addEventListener('click', (event) => {

            let pokemon = {};
            let pokemonId = button.getAttribute('data-id');
            pokemon.id = parseInt(pokemonId);
            pokemon.img = button.firstElementChild.children[1].src;
            document.querySelector('.not-1').classList.add('d-none');
            let image = document.querySelector('.choose-1');
            image.classList.remove('d-none');
            image.src = pokemon.img;
            image.dataset.id = pokemonId;
            socket.emit('changePokemon', pokemon);

        })
    })

});

socket.on('changeOtherPokemon', (pokemon) => {
    document.querySelector('.not-2').classList.add('d-none');
    let imageToChange = document.querySelector('.choose-2');
    imageToChange.classList.remove('d-none');
    imageToChange.src = pokemon.img;
    imageToChange.dataset.id = pokemon.id;
    console.log(pokemon)
});

let validateButton = document.querySelector('.trade-v');

validateButton.addEventListener('click', (event) => {
    let pokemonId = document.querySelector('.choose-1').dataset.id;
    let postData = new FormData();
    let updateUrl = fetch(window.updateUrl)
    postData.append('pokemonId', pokemonId);

    fetch(updateUrl,{
        method: 'POST',
        body: postData,
    })
        .then((response) => response.json())
        .then((data) => {
            console.log(data)
        })
        .catch((error) => {
        })
})


