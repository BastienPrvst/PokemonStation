let captureInProcess = false;
let elem = document.querySelector(".pokeball-animate");
function backToPlace() {
    elem.style.bottom = '0px';
    elem.style.rotate = '0deg';
}


const typesBackgroundArray = {
    eau: 'sea-background',
    feu: 'fire-background',
    plante: 'forest2-background',
    insecte: 'cabane-background',
    poison: 'poison-background',
    vol: 'montagne-background',
    sol: 'desert-background',
    roche: 'cave-background',
    'ténèbres' : 'cimetière-background',
    normal: 'forest2-background',
    combat: 'pourpre-background',
    psy: 'montagne-background',
    'fée': 'night-background',
    spectre: 'cimetière-background',
    dragon: 'dragon-background',
    acier: 'forest-background',
    electrik: 'pourpre-background',
    glace: 'neige-background',

};

let pokeballButton = document.querySelectorAll('.capture-poke-button');

pokeballButton.forEach(function (button){
    button.addEventListener("click", async function (event) {


        // document.querySelector('.multiple').after(launchCount);
        // launchCount.classList.add('count-pokeball');






        if (!captureInProcess) {

            captureInProcess = true;


            let pokeballData = button.getAttribute('data-ball');

            let postData = new FormData();

            postData.append('pokeballData', pokeballData);


            let carousel = document.querySelector('.carou-ball');

            carousel.classList.add('overflow-visible');


            //Si il y'a deja un pokemon, on l'enleve
            let currentPoke = document.querySelector('.displayed-pokemon');
            if (currentPoke) {
                currentPoke.remove();
            }
            let currentShiny = document.querySelector('.shining-effect');
            if (currentShiny) {
                currentShiny.remove();
            }
            let currentInfo = document.querySelector('.pokemon-captured-infos');
            if (currentInfo) {
                currentInfo.remove();
            }

            let currentNew = document.querySelector('.logo-new');
            if (currentNew) {
                currentNew.remove();
            }

            let currentPokeDiv = document.querySelector('.poke-capture-div')
            if (currentPokeDiv) {
                currentPokeDiv.remove();
            }



            //ON enleve une pokeball lancée


            let activeCarousel = document.querySelector('.carousel-item.active');


            let launchs = activeCarousel.querySelector('.launch-items').textContent;

            launchs = parseInt(launchs);



            if (launchs > 0) {

                activeCarousel.querySelector('.launch-items').textContent = (launchs - 1);

            }




            let pokemonImage = document.createElement('img');
            let pokemonShining = document.createElement('img');
            let pokemonInfo = document.createElement('p');
            let pokemonNewLogo = document.createElement('img');
            let pokeCoin = document.createElement('img');
            let pokemonDiv = document.createElement('div');
            pokemonImage.classList.add('displayed-pokemon');
            pokemonShining.classList.add('shining-effect');
            pokemonInfo.classList.add('pokemon-captured-infos');
            pokemonImage.alt = '';
            pokemonShining.alt = '';
            let pokemonGif;
            let pokemonShine;
            let pokemonIsNew;


            let animatePromise = new Promise((resolve, reject) => {

                (() => {
                    let pos = 0;
                    let angle = 0;
                    let id = setInterval(frame, 5);
                    clearInterval(id);
                    id = setInterval(frame, 10);

                    function frame() {

                        if (pos === 250) {

                            document.querySelector('.pokeball-animate').classList.add('pokeball-animated');

                            clearInterval(id);

                            resolve();

                        } else {

                            let image = button.querySelector('img');

                            pos += 5;
                            angle += 22;
                            image.style.bottom = pos + 'px';
                            image.style.rotate = angle + 'deg';
                        }
                    }
                })();

            });

            let pokemonIsCaptured = false;


            let getPokemonPromise = new Promise((resolve, reject) => {


                //Affichage du gif du pokémon
                fetch(capturedPageApi, {
                    method: 'POST',
                    body: postData,
                })
                    .then((response) => response.json())
                    .then((data) => {


                        //Vérification du nombre de lancers

                        if (data.error != null) {

                            pokemonInfo.innerHTML = data.error;

                        } else {

                            pokemonIsCaptured = true;


                            //Si le pokemon est shiny on change la route du gif
                            pokemonGif = pokemonsGifDir + '/' + ((data.captured_pokemon.shiny) ? 'shiny-' : '') + data.captured_pokemon.gif;


                            //Effets en fonction de la rareté
                            if (data.captured_pokemon.shiny === true) {
                                pokemonShine = pokemonsShineDir + '/shiny-sparkle.gif';
                            } else if (data.captured_pokemon.rarity === 'TR') {
                                pokemonShine = pokemonsShineDir + '/sparkle.gif';
                            } else if (data.captured_pokemon.rarity === 'EX') {
                                pokemonShine = pokemonsShineDir + '/orange-sparkle.gif';
                            } else if (data.captured_pokemon.rarity === 'SR') {
                                pokemonShine = pokemonsShineDir + '/red-sparkle.gif';
                            } else {
                                pokemonShine = pokemonsShineDir + '/invisible-sparkle.gif';
                            }

                            //Fonds en fonction des types


                            document.querySelector('.view-pokemon').classList.remove(window.pokemonBackground);

                            window.pokemonBackground = typesBackgroundArray[data.captured_pokemon.type];

                            document.querySelector('.view-pokemon').classList.add(typesBackgroundArray[data.captured_pokemon.type]);


                            pokemonInfo.innerHTML = '';

                            //Affichage des infos du pokemon libéré

                            const rarityScale = {
                                'C': 1,
                                'PC' : 3,
                                'R' : 5,
                                'TR' : 10,
                                'ME' : 25,
                                'SR' : 50,
                                'EX' : 50,
                                'UR' : 250,
                            };


                            pokemonInfo.innerHTML = 'Vous avez libéré <span class="text-capitalize">' + data.captured_pokemon.name + '</span>' + ((data.captured_pokemon.shiny) ? ' Shiny' : '') + ' (' + data.captured_pokemon.rarity + ') ! '  + ((data.captured_pokemon.new) ? '' : '+' + rarityScale[data.captured_pokemon.rarity]);

                            console.log(data.captured_pokemon.rarity, data.captured_pokemon.rarityRandom);

                            document.querySelector('.pokeball-animate').classList.remove('pokeball-animated');

                            if (data.captured_pokemon.new === true) {

                                pokemonIsNew = true;

                            }else{


                                //Comptage des pièces

                                const rarityScale = {
                                    'C': 1,
                                    'PC': 3,
                                    'R': 5,
                                    'TR': 10,
                                    'ME': 25,
                                    'SR': 50,
                                    'EX': 50,
                                    'UR': 250,
                                };


                                let actualCoin = document.querySelector('.coin-count').textContent;

                                actualCoin = parseInt(actualCoin);

                                document.querySelector('.coin-count').textContent = (actualCoin + rarityScale[data.captured_pokemon.rarity]);

                            }


                        }
                        //Resolve de la promesse
                        resolve();


                    })
                ;

            });

            await animatePromise;
            await getPokemonPromise;

            if (pokemonIsCaptured) {
                pokemonImage.src = pokemonGif;
                pokemonShining.src = pokemonShine;
                pokemonNewLogo.src = newLogo;
                pokeCoin.src = coin;

                pokeCoin.classList.add('coin-width');

                pokemonDiv.classList.add('poke-capture-div');

                pokemonDiv.append(pokemonShining, pokemonImage);

                if (pokemonIsNew === true) {

                    pokemonNewLogo.classList.add('logo-new');
                    pokemonDiv.append(pokemonNewLogo);

                }else{

                    pokemonInfo.append(pokeCoin);

                }


                document.querySelector('.view-pokemon').append(pokemonDiv);


            }

            document.querySelector('.description-poke-capture').append(pokemonInfo);

            let image = button.querySelector('img');

            image.style.bottom = '0px';
            image.style.rotate = '0deg';

            carousel.classList.remove('overflow-visible');

            setTimeout(() => {
                captureInProcess = false;
            }, 1000);

        }


    });

})
















//Code pour le SHOP


//Fonctions


function add(number){

    document.querySelector('.plus-' + number).addEventListener("click", function (){

        let $i = document.querySelector('.quantity-'+ number);

        $i.innerHTML = parseInt($i.innerHTML) + 1;

        let totalShop = document.querySelector('.total_shop');

        let price = document.querySelector('.price-'+ number);

        totalShop.innerHTML = parseInt(totalShop.innerHTML) + parseInt(price.innerHTML);


    })


}
function unset(number){

    document.querySelector('.minus-' + number).addEventListener("click", function (){

        let $i = document.querySelector('.quantity-'+ number);

        $i.innerHTML = parseInt($i.innerHTML);

        if ($i.innerHTML>0){

            $i.innerHTML = parseInt($i.innerHTML)-1;

            let totalShop = document.querySelector('.total_shop');

            let price = document.querySelector('.price-'+ number);

            totalShop.innerHTML = parseInt(totalShop.innerHTML) - parseInt(price.innerHTML);

        }
    })


}


add(1);
add(2);
add(3);


unset(1);
unset(2);
unset(3);


//Utilisation AJAX

let shopInProcess = false;

let buyButton = document.querySelector('.buy-it');

buyButton.addEventListener("click", function(){


    if (!shopInProcess){

        shopInProcess = true

        let shopToDelete = document.querySelector('.shopNotice');

        if (shopToDelete){
            shopToDelete.remove();
        }


        let quantityNumbers = document.querySelectorAll('.quantity');

        let quantityArray = [];


        quantityNumbers.forEach(function (quantityNumber){

            quantityArray.push(parseInt(quantityNumber.innerHTML));

        })


        let shopInfo = document.createElement('p');

        let postData = new FormData();

        postData.append('quantityArray', quantityArray);

        fetch(capturedShopApi, {
            method: 'POST',
            body: postData,
        })
            .then((response) => response.json())


            .then((data) => {


                if (data.error != null) {

                    shopInfo.innerHTML = data.error;

                    console.log(data.bug);

                }else{

                    let quantity = document.querySelectorAll('.quantity');

                    quantity.forEach(function (quan) {

                        quan.textContent = '0';

                    });

                    let totalPrice = document.querySelector('.total_shop');

                    totalPrice.textContent = '0';

                    //Ajout HyperBall

                    let hyperBall = document.querySelector('.launch-2').textContent;



                    hyperBall = parseInt(hyperBall);


                    let newHyperBall = parseInt(data.kart[0]);

                    document.querySelector('.launch-2').textContent = (hyperBall + newHyperBall);


                    //Ajout ShinyBall

                    let shinyBall = document.querySelector('.launch-3').textContent;

                    shinyBall = parseInt(shinyBall);


                    let newShinyBall = parseInt(data.kart[1]);

                    document.querySelector('.launch-3').textContent = (shinyBall + newShinyBall);


                    //Ajout MasterBall

                    let masterBall = document.querySelector('.launch-4').textContent;


                    masterBall = parseInt(masterBall);


                    let newMasterBall = parseInt(data.kart[2]);


                    document.querySelector('.launch-4').textContent = (masterBall + newMasterBall);


                    // let activeCarousel = document.querySelector('.carousel-item.active');
                    //
                    //
                    // let launchs = activeCarousel.querySelector('.launch-items').textContent;
                    //
                    // launchs = parseInt(launchs);
                    //
                    //
                    //
                    // if (launchs > 0) {
                    //
                    //     activeCarousel.querySelector('.launch-items').textContent = (launchs - 1);
                    //
                    // }




                    //On enlève les sous du compteur de l'utilisateur


                    let userWallet = document.querySelector('.coin-count').textContent;

                    userWallet = parseInt(userWallet);

                    let kartPrice = parseInt(data.kartPrice);

                    document.querySelector('.coin-count').textContent = (userWallet - kartPrice);



                    //Message de succès

                    shopInfo.textContent = data.success;


                }


                let pokemonShop = document.querySelector('.shop');

                pokemonShop.append(shopInfo);

                shopInfo.classList.add('shopNotice');



                setTimeout(() => {
                    shopInProcess = false;
                }, 1000);


            })





    }








})