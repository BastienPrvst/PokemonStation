
const socket = io.connect("http://localhost:4000");

socket.on("connect", () => {

    console.log("Connecté avec l'ID :", socket.id);

    socket.emit('joinRoom', 'tradeRoom')

    let myButtons = document.querySelectorAll('.my-pokemon-trade');

    //Selectionner un poké

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
            document.querySelector('.trade-c').classList.add('d-none');
            socket.emit('changePokemon', pokemon);

        })
    })

});

//Affichage autre utilisateur

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


//Confirmation poké

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
            document.querySelector('.trade-c').classList.remove('d-none');
            socket.emit('validatePokemon', price);
            document.querySelector('.ready-2').classList.add('d-none');
        })
        .catch((error) => {
            console.log(error)
        })

})

//Confirmation pour un autre utilisateur

socket.on('validatePokemonFromOther', (price) => {
    document.querySelector('.select-trade-2').classList.add('validate-pokemon');document.querySelector('.trade-price').textContent = price;
    document.querySelector('.ready').classList.add('d-none');
})

//Validation poké

let validateTrade = document.querySelector('.trade-c');
validateTrade.addEventListener('click', (event) => {
    fetch(tradeValidateUrl, {
        method: 'POST',
    })
    .then((response) => response.json())
    .then((data) => {
        console.log(data.error)
        socket.emit('confirmedPokemon')
        document.querySelector('.ready').classList.remove('d-none');
    })
    .catch((error) => {
        console.log(error)
    })
})

//Validation pour autre utilisateur

socket.on('confirmedPokemonFromOther', () => {
    document.querySelector('.ready-2').classList.remove('d-none');
})


//Signe d'interet
document.querySelectorAll('.other-trade').forEach(element => {
    element.addEventListener('click', (event) => {
        let id = parseInt(element.dataset.id);
        socket.emit('interested', id)

        document.querySelectorAll('.other-trade .poketrade').forEach(element => {
            element.classList.remove('interestedPokemon');
        })

        element.querySelector('.poketrade').classList.add('interestedPokemon');
    })

})


//Signe d'interet pour autre utilisateur
socket.on('interestedPokemonFromOther', (id) => {
    let target = document.querySelector(`.my-pokemon-trade[data-id="${id}"]`);
    document.querySelectorAll('.my-pokemon-trade .poketrade').forEach(element => {
        element.classList.remove('interestedPokemon');
    });

    if (target) {
        let child = target.querySelector('.poketrade');
        if (child) {
            child.classList.add('interestedPokemon');
        }
    }})


//Partie filtre

function filterTrade(filtersB, classToFilter) {
    let activeFilters = [];
    let pokemonToFilter = document.querySelectorAll(classToFilter);

    filtersB.forEach(button => {
        button.addEventListener('click', (event) => {
            event.target.classList.toggle('checked-filter');
            let filterValue = event.target.textContent;

            if (event.target.classList.contains('checked-filter')) {
                if (!activeFilters.includes(filterValue)) {
                    activeFilters.push(filterValue);
                }
            } else {
                activeFilters = activeFilters.filter(f => f !== filterValue);
            }

            pokemonToFilter.forEach((poke) =>{
                poke.classList.add('hidden');
            })

            if (activeFilters.length === 1 && activeFilters.includes('Shiny')){
                document.querySelectorAll(`${classToFilter}[data-shiny="1"]`).forEach((item) => {
                    item.classList.remove('hidden');
                })
            } else if (activeFilters.length === 1 && activeFilters.includes('Possédé')){
                document.querySelectorAll(`${classToFilter}[data-possessed="1"]`).forEach((item) => {
                    item.classList.remove('hidden');
                })

            }  else if (activeFilters.length === 1 && activeFilters.includes('Non-possédé')){
                document.querySelectorAll(`${classToFilter}[data-possessed="0"]`).forEach((item) => {
                    item.classList.remove('hidden');
                })

            } else if (activeFilters.length > 0) {
                activeFilters.forEach(filter => {

                    if (
                        filter === "Shiny" ||
                        filter === "Possédé" ||
                        filter === "Non-possédé")
                        return;
                    let filtersButtons;
                    if (activeFilters.includes('Shiny')){
                        filtersButtons = document.querySelectorAll(`${classToFilter}[data-filter="${filter}"][data-shiny="1"]`);

                    } else if( activeFilters.includes('Possédé')) {
                        filtersButtons = document.querySelectorAll(`${classToFilter}[data-filter="${filter}"][data-possessed="1"]`);
                    } else if( activeFilters.includes('Non-possédé')) {
                        filtersButtons = document.querySelectorAll(`${classToFilter}[data-filter="${filter}"][data-possessed="0"]`);
                    } else {
                        filtersButtons = document.querySelectorAll(`${classToFilter}[data-filter="${filter}"]`);
                    }

                    filtersButtons.forEach(button => {
                        button.classList.remove('hidden');
                    });
                });
                filtersButtons.forEach(button => {
                    button.classList.remove('hidden');
                })
            }else{
                pokemonToFilter.forEach(button => {
                    button.classList.remove('hidden');
                });
            }
        })
    })
}

let filtersButtons = document.querySelectorAll('.filters button');
let targetClass = '.my-pokemon-trade';

filterTrade(filtersButtons, targetClass);

let filterButtons2 = document.querySelectorAll('.filters-2 button');
let allOtherPokemonsTrade = '.other-trade';

filterTrade(filterButtons2, allOtherPokemonsTrade);



