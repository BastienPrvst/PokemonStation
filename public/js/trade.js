
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
            const el = document.querySelector('.not-1');
            if (el) {
                el.classList.add('d-none');
            }
            document.querySelector('.select-trade-1').classList.remove
            ('validate-pokemon');
            let image = document.querySelector('.choose-1');
            image.classList.remove('d-none');
            image.src = pokemon.img;
            image.dataset.id = pokemonId;
            socket.emit('changePokemon', pokemon);

        })
    })

});

socket.on('changeOtherPokemon', (pokemon) => {
    const el = document.querySelector('.not-2');
    if (el) {
        el.classList.add('d-none');
    }
    let imageToChange = document.querySelector('.choose-2');
    imageToChange.classList.remove('d-none');
    imageToChange.src = pokemon.img;
    imageToChange.dataset.id = pokemon.id;
    document.querySelector('.select-trade-2').classList.remove('validate-pokemon');
});

let validateButton = document.querySelector('.trade-v');

validateButton.addEventListener('click', (event) => {
    let pokemonId = document.querySelector('.choose-1').dataset.id;
    let formData = new FormData();
    formData.append('pokemonId', pokemonId);
    fetch(tradeUpdateUrl,{
        method: 'POST',
        body: formData,
    })
        .then((response) => response.json())
        .then((data) => {
            document.querySelector('.select-trade').classList.add
            ('validate-pokemon');
            let price = data.price;
            document.querySelector('.trade-price').textContent = data.price;
            socket.emit('validatePokemon', price);
        })
        .catch((error) => {
            console.log(error)
        })

})

socket.on('validatePokemonFromOther', (price) => {
    document.querySelector('.select-trade-2').classList.add('validate-pokemon');document.querySelector('.trade-price').textContent = price;
})


