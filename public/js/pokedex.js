



// let pokedexNav = document.querySelector('.pokedex-nav');
//
// let buttonToDisplay = pokedexNav.querySelector('[data-current]');
//
// //Animation du bouton B
// const buttonB = document.querySelector(".gb-button-b");
//
// buttonB.addEventListener("click", animate);
//
// const pokeGifB = document.querySelector(".poke-gif");
//
//
// function animate() {
//     pokeGifB.classList.add("jump-animation", "scale");
// }
//
// pokeGifB.addEventListener("animationend", removeClass);
// function removeClass() {
//     pokeGifB.classList.remove("jump-animation", "scale");
// }
//
//
//
// // Animation du bouton A
// const buttonA = document.querySelector(".gb-button-a");
// const pokeGif = document.querySelector(".poke-gif");
//
// const originalSrc = pokeGif.src;
// const shinySrc = pokeShineDir;
//
// buttonA.addEventListener("click", function () {
//     if (pokeGif.src === originalSrc) {
//         pokeGif.src = shinySrc;
//     } else {
//         pokeGif.src = originalSrc;
//     }
// });
//
// // Animation du bouton Shiny sur mobile
// const shinyButton = document.querySelector(".shiny-button-mobile");
// const pokeShinyGif = document.querySelector(".poke-gif");
//
// const firstSrc = pokeGif.src;
// const secondSrc = pokeShineDir;
//
// shinyButton.addEventListener("click", function () {
//     if (pokeShinyGif.src === firstSrc) {
//         pokeShinyGif.src = secondSrc;
//     } else {
//         pokeShinyGif.src = firstSrc;
//     }
// });


// // Overlay du bouton start
// const startSelectButton = document.querySelector(".start");
// const overlay = document.querySelector(".overlay");
//
// startSelectButton.addEventListener("click", function () {
//     if (overlay.style.display === "flex") {
//         overlay.style.display = "none";
//     } else {
//         overlay.style.display = "flex";
//         overlay.style.flexDirection = "column";
//         overlay.style.justifyContent = "center";
//         overlay.style.alignItems = "center";
//     }
// });


//Gestion API

const typesBackgroundArray = {
    eau: 'sea-background',
    feu: 'fire-background',
    plante: 'forest2-background',
    insecte: 'cabane-background',
    poison: 'poison-background',
    vol: 'montagne-background',
    sol: 'desert-background',
    roche: 'cave-background',
    'ténèbres': 'cimetière-background',
    normal: 'forest2-background',
    combat: 'pourpre-background',
    psy: 'montagne-background',
    'fée': 'night-background',
    spectre: 'cimetière-background',
    dragon: 'dragon-background',
    acier: 'forest-background',
    electrik: 'pourpre-background',
    glace: 'neige-background',

}

const type1Element = {

    eau: 'Water.png',
    feu: 'Fire.png',
    plante: 'Grass.png',
    insecte: 'Bug.png',
    poison: 'Poison.png',
    vol: 'Flying.png',
    sol: 'Ground.png',
    roche: 'Rock.png',
    'ténèbres': 'Dark.png',
    normal: 'Normal.png',
    combat: 'Fighting.png',
    psy: 'Psychic.png',
    'fée': 'Fairy.png',
    spectre: 'Ghost.png',
    dragon: 'Dragon.png',
    acier: 'Steel.png',
    electrik: 'Electric.png',
    glace: 'Ice.png',



}



// window.addEventListener('load', function() {
//     // Récupère tous les éléments avec la classe '.pokemon-pokedex'
//     let buttons = document.querySelectorAll('.pokemon-pokedex:not(.not-captured)');
//
//     // Sélectionne le premier bouton
//     let firstButton = buttons[0];
//
//     // Active le premier bouton
//     firstButton.click();
// });

let pokemonGif;
let pokemonImage = document.querySelector('.poke-gif');

// Récupère tous les éléments avec la classe '.pokemon-pokedex'
let buttons = document.querySelectorAll('.pokemon-pokedex');

let displayInProgress = false;

let currentPokeId = null;

// Attache l'événement 'click' à chaque élément de la liste
    buttons.forEach(function(button) {
        button.addEventListener("click", function(event) {


            if(displayInProgress === false){




                displayInProgress = true;

                let pokemonId = event.target.getAttribute("data-pokemon");

                //Code pour eviter de recharger si on clique sur le meme bouton/pokémon

                if (pokemonId === currentPokeId){

                    displayInProgress = false;
                    return;

                }else{

                currentPokeId = pokemonId;

                let postData = new FormData();

                postData.append('pokemonId', pokemonId);



                    fetch(pokedexPageApi, {
                        method: 'POST',
                        body: postData,
                    })
                        .then((response) => response.json())


                        .then(data => {


                            //Changement des noms


                            //Français
                            document.querySelector('.firstName').innerHTML = '';

                            document.querySelector('.firstName').innerHTML = '<span class="text-capitalize">' + data.pokemonToDisplay.name + '</span>';

                            //Anglais

                            document.querySelector('.secondName').innerHTML = '';

                            document.querySelector('.secondName').innerHTML = '<span class="text-capitalize">' + data.pokemonToDisplay.nameEN + '</span>';

                            //ID

                            document.querySelector('.thirdName').innerHTML = '';

                            document.querySelector('.thirdName').innerHTML = '#' + data.pokemonToDisplay.pokeId;



                            //Changement du gif

                            pokemonGif = pokemonsGifDir + '/' + data.pokemonToDisplay.gif;

                            pokemonImage.src = pokemonGif;


                            //Changement de la description

                            document.querySelector('.description p').innerHTML = '';

                            document.querySelector('.description p').innerHTML = data.pokemonToDisplay.description;

                            //Changement du fond

                            document.querySelector('.fond').classList.remove(window.pokemonBackground);

                            window.pokemonBackground = typesBackgroundArray[data.pokemonToDisplay.type1];

                            document.querySelector('.fond').classList.add(typesBackgroundArray[data.pokemonToDisplay.type1]);


                            //Changement des types

                            let type1 = document.querySelector('.type1');

                            let type2 = document.querySelector('.type2');


                            //Type 1

                            type1.src = pokemonsTypeDir + type1Element[data.pokemonToDisplay.type1];


                            if(data.pokemonToDisplay.type2 != null){

                                type2.classList.remove('type-none');

                                type2.src = pokemonsTypeDir + type1Element[data.pokemonToDisplay.type2];

                            }else{

                                type2.classList.add('type-none');

                            }

                            //Bouton de shiny si l'utilisateur possède le shiny de ce pokémon

                            let mobileShinyButton = document.querySelector('.shiny-button-mobile');

                            mobileShinyButton.classList.add('type-none');

                            if(data.pokemonToDisplay.shiny === true){

                                mobileShinyButton.classList.remove('type-none');

                                mobileShinyButton.classList.add('type-on');

                            }else{

                                mobileShinyButton.classList.remove('type-on');

                                mobileShinyButton.classList.add('type-none');

                            }






                        })
                        .catch(error => {

                            console.log(error);

                        });




                        displayInProgress = false;

                }
                }
        });
    });









// Animation du bouton Shiny sur mobile
let shinyButton = document.querySelector(".shiny-button-mobile");

let pokeGif = document.querySelector('.poke-gif');




// Ajout de l'événement "click" du bouton shiny
shinyButton.addEventListener("click", function () {


    let currentSrc = pokeGif.src;

    let currentFileName = currentSrc.substring(currentSrc.lastIndexOf("/") + 1);

    if (currentSrc.includes('/shiny-')) {

        pokeGif.src = currentSrc.replace('/shiny-', '/').replace('shiny-', '');


    } else {

        pokeGif.src = currentSrc.replace(currentFileName, 'shiny-' + currentFileName);
    }
});







//Bouton d'affichage des générations

//Creation de la fonction d'affichage a adapter à chaque bouton

function toggleButtons(button, arrow, buttons) {
    let isButtonOpen = false;

    button.addEventListener("click", function () {
        isButtonOpen = !isButtonOpen;

        buttons.forEach(function (button) {
            button.classList.toggle('type-none', !isButtonOpen);
            button.classList.toggle('type-flex', isButtonOpen);
        });

        arrow.style.transform = isButtonOpen ? "rotate(90deg)" : "rotate(0deg)";
        arrow.classList.toggle('rotate-transition', isButtonOpen);
    });
}


//Premiere gen

let button1Gen = document.querySelector('.first-gen-button');
let arrow1Gen = document.querySelector('.first-gen-button .fa-solid');
let allGenOneButtons = document.querySelectorAll('.gen-1');

toggleButtons(button1Gen, arrow1Gen, allGenOneButtons);


//Deuxieme gen


let button2Gen = document.querySelector('.second-gen-button');
let arrow2Gen = document.querySelector('.second-gen-button .fa-solid');
let allGenTwoButtons = document.querySelectorAll('.gen-2');

toggleButtons(button2Gen, arrow2Gen, allGenTwoButtons);

//Troisième gen


let button3Gen = document.querySelector('.third-gen-button');
let arrow3Gen = document.querySelector('.third-gen-button .fa-solid');
let allGenThreeButtons = document.querySelectorAll('.gen-3');

toggleButtons(button3Gen, arrow3Gen, allGenThreeButtons);



//Quatrieme gen


let button4Gen = document.querySelector('.fourth-gen-button');
let arrow4Gen = document.querySelector('.fourth-gen-button .fa-solid');
let allGenFourButtons = document.querySelectorAll('.gen-4');

toggleButtons(button4Gen, arrow4Gen, allGenFourButtons);


//Cinquieme gen


let button5Gen = document.querySelector('.fifth-gen-button');
let arrow5Gen = document.querySelector('.fifth-gen-button .fa-solid');
let allGenFiveButtons = document.querySelectorAll('.gen-5');

toggleButtons(button5Gen, arrow5Gen, allGenFiveButtons);


//Sixieme gen


let button6Gen = document.querySelector('.six-gen-button');
let arrow6Gen = document.querySelector('.six-gen-button .fa-solid');
let allGenSixButtons = document.querySelectorAll('.gen-6');

toggleButtons(button6Gen, arrow6Gen, allGenSixButtons);


//Septième gen


let button7Gen = document.querySelector('.seven-gen-button');
let arrow7Gen = document.querySelector('.seven-gen-button .fa-solid');
let allGenSevenButtons = document.querySelectorAll('.gen-7');

toggleButtons(button7Gen, arrow7Gen, allGenSevenButtons);



//Huitième gen

let button8Gen = document.querySelector('.eight-gen-button');
let arrow8Gen = document.querySelector('.eight-gen-button .fa-solid');
let allGenEightButtons = document.querySelectorAll('.gen-8');

toggleButtons(button8Gen, arrow8Gen, allGenEightButtons);



//Forme reg


let buttonALO = document.querySelector('.ALO-button');
let arrowALO = document.querySelector('.ALO-button .fa-solid');
let allALOButtons = document.querySelectorAll('.ALO');

toggleButtons(buttonALO, arrowALO, allALOButtons);





//Megas Evolutions


let buttonME = document.querySelector('.ME-button');
let arrowME = document.querySelector('.ME-button .fa-solid');
let allMEButtons = document.querySelectorAll('.ME');

toggleButtons(buttonME, arrowME, allMEButtons);


//Secret rares


let buttonSR = document.querySelector('.SR-button');
let arrowSR = document.querySelector('.SR-button .fa-solid');
let allSRButtons = document.querySelectorAll('.SR');

toggleButtons(buttonSR, arrowSR, allSRButtons);


//Ultra rares


let buttonUR = document.querySelector('.UR-button');
let arrowUR = document.querySelector('.UR-button .fa-solid');
let allURButtons = document.querySelectorAll('.UR');


toggleButtons(buttonUR, arrowUR, allURButtons);


//Comptage des boutons actifs pour chaque gen


//Gen 1


//Completion
const activeButtonsG1 = document.querySelectorAll(".captured.gen-1");
const activeCountG1 = document.getElementById("activeCountG1");

activeCountG1.textContent = activeButtonsG1.length;

//Shiny

const activeShinyG1 = document.querySelectorAll(".gen-1-shiny");
const activeCountShinyG1 = document.getElementById("shinyG1");

activeCountShinyG1.textContent = activeShinyG1.length;



//Gen 2


//Completion
const activeButtonsG2 = document.querySelectorAll(".captured.gen-2");
const activeCountG2 = document.getElementById("activeCountG2");

activeCountG2.textContent = activeButtonsG2.length;

//Shiny

const activeShinyG2 = document.querySelectorAll(".gen-2-shiny");
const activeCountShinyG2 = document.getElementById("shinyG2");

activeCountShinyG2.textContent = activeShinyG2.length;


//Gen 3


//Completion
const activeButtonsG3 = document.querySelectorAll(".captured.gen-3");
const activeCountG3 = document.getElementById("activeCountG3");

activeCountG3.textContent = activeButtonsG3.length;

//Shiny

const activeShinyG3 = document.querySelectorAll(".gen-3-shiny");
const activeCountShinyG3 = document.getElementById("shinyG3");

activeCountShinyG3.textContent = activeShinyG3.length;


//Gen 4


//Completion
const activeButtonsG4 = document.querySelectorAll(".captured.gen-4");
const activeCountG4 = document.getElementById("activeCountG4");

activeCountG4.textContent = activeButtonsG4.length;

//Shiny

const activeShinyG4 = document.querySelectorAll(".gen-4-shiny");
const activeCountShinyG4 = document.getElementById("shinyG4");

activeCountShinyG4.textContent = activeShinyG4.length;


//Gen 5


//Completion
const activeButtonsG5 = document.querySelectorAll(".captured.gen-5");
const activeCountG5 = document.getElementById("activeCountG5");

activeCountG5.textContent = activeButtonsG5.length;

//Shiny

const activeShinyG5 = document.querySelectorAll(".gen-5-shiny");
const activeCountShinyG5 = document.getElementById("shinyG5");

activeCountShinyG5.textContent = activeShinyG5.length;


//Gen 6


//Completion
const activeButtonsG6 = document.querySelectorAll(".captured.gen-6");
const activeCountG6 = document.getElementById("activeCountG6");

activeCountG6.textContent = activeButtonsG6.length;

//Shiny

const activeShinyG6 = document.querySelectorAll(".gen-6-shiny");
const activeCountShinyG6 = document.getElementById("shinyG6");

activeCountShinyG6.textContent = activeShinyG6.length;


//Gen 7


//Completion
const activeButtonsG7 = document.querySelectorAll(".captured.gen-7");
const activeCountG7 = document.getElementById("activeCountG7");

activeCountG7.textContent = activeButtonsG7.length;

//Shiny

const activeShinyG7 = document.querySelectorAll(".gen-7-shiny");
const activeCountShinyG7 = document.getElementById("shinyG7");

activeCountShinyG7.textContent = activeShinyG7.length;



//Gen 7


//Completion
const activeButtonsG8 = document.querySelectorAll(".captured.gen-8");
const activeCountG8 = document.getElementById("activeCountG8");

activeCountG8.textContent = activeButtonsG8.length;

//Shiny

const activeShinyG8 = document.querySelectorAll(".gen-8-shiny");
const activeCountShinyG8 = document.getElementById("shinyG8");

activeCountShinyG8.textContent = activeShinyG8.length;


//Formes ALOLA


//Completion
const activeButtonsALO = document.querySelectorAll(".captured.ALO");
const activeCountALO = document.getElementById("activeCountALO");

activeCountALO.textContent = activeButtonsALO.length;

//Shiny

const activeShinyALO = document.querySelectorAll(".ALO-shiny");
const activeCountShinyALO = document.getElementById("shinyALO");

activeCountShinyALO.textContent = activeShinyALO.length;


//ME


//Completion
const activeButtonsME = document.querySelectorAll(".captured.gen-ME");
const activeCountME = document.getElementById("activeCountME");

activeCountME.textContent = activeButtonsME.length;

//Shiny

const activeShinyME = document.querySelectorAll(".gen-ME-shiny");
const activeCountShinyME = document.getElementById("shinyME");

activeCountShinyME.textContent = activeShinyME.length;



//SR


//Completion
const activeButtonsSR = document.querySelectorAll(".captured.gen-SR");
const activeCountSR = document.getElementById("activeCountSR");

activeCountSR.textContent = activeButtonsSR.length;

//Shiny

const activeShinySR = document.querySelectorAll(".gen-SR-shiny");
const activeCountShinySR = document.getElementById("shinySR");

activeCountShinySR.textContent = activeShinySR.length;


//UR


//Completion
const activeButtonsUR = document.querySelectorAll(".captured.gen-UR");
const activeCountUR = document.getElementById("activeCountUR");

activeCountUR.textContent = activeButtonsUR.length;

//Shiny

const activeShinyUR = document.querySelectorAll(".gen-UR-shiny");
const activeCountShinyUR = document.getElementById("shinyUR");

activeCountShinyUR.textContent = activeShinyUR.length;