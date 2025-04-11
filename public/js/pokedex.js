//Gestion API

const typesBackgroundArray = {
  eau: "sea-background",
  feu: "fire-background",
  plante: "forest2-background",
  insecte: "cabane-background",
  poison: "poison-background",
  vol: "montagne-background",
  sol: "desert-background",
  roche: "cave-background",
  ténèbres: "cimetière-background",
  normal: "forest2-background",
  combat: "pourpre-background",
  psy: "montagne-background",
  fée: "night-background",
  spectre: "cimetière-background",
  dragon: "dragon-background",
  acier: "forest-background",
  electrik: "pourpre-background",
  glace: "neige-background",
};

const type1Element = {
  eau: "Water.png",
  feu: "Fire.png",
  plante: "Grass.png",
  insecte: "Bug.png",
  poison: "Poison.png",
  vol: "Flying.png",
  sol: "Ground.png",
  roche: "Rock.png",
  ténèbres: "Dark.png",
  normal: "Normal.png",
  combat: "Fighting.png",
  psy: "Psychic.png",
  fée: "Fairy.png",
  spectre: "Ghost.png",
  dragon: "Dragon.png",
  acier: "Steel.png",
  electrik: "Electric.png",
  glace: "Ice.png",
};

let pokemonGif;
let pokemonImage = document.querySelector(".poke-gif");

// Récupère tous les éléments avec la classe '.pokemon-pokedex'
let buttons = document.querySelectorAll(".pokemon-pokedex");

let displayInProgress = false;

let currentPokeId = null;

// Attache l'événement 'click' à chaque élément de la liste
buttons.forEach(function (button) {
  button.addEventListener("click", function (event) {
    if (displayInProgress === false) {
      displayInProgress = true;

      let pokemonId = event.target.getAttribute("data-pokemon");

      //Code pour eviter de recharger si on clique sur le meme bouton/pokémon
      if (pokemonId === currentPokeId) {
        displayInProgress = false;
        return;
      } else {
        currentPokeId = pokemonId;

        let postData = new FormData();

        postData.append("pokemonId", pokemonId);

        fetch(pokedexPageApi, {
          method: "POST",
          body: postData,
        })
          .then((response) => response.json())
          .then((data) => {
            //Changement des noms
            //Français
            document.querySelector(".firstName").innerHTML = "";
            document.querySelector(".firstName").innerHTML =
              '<span class="text-capitalize">' +
              data.pokemonToDisplay.name +
              "</span>";
            //Anglais
            document.querySelector(".secondName").innerHTML = "";
            document.querySelector(".secondName").innerHTML =
              '<span class="text-capitalize">' +
              data.pokemonToDisplay.nameEN +
              "</span>";
            //ID
            document.querySelector(".thirdName").innerHTML = "";
            document.querySelector(".thirdName").innerHTML =
              "#" + data.pokemonToDisplay.pokeId;

            //Changement du gif
            pokemonGif =
              pokemonsGifDir + "/" + data.pokemonToDisplay.nameEN + ".gif";
            pokemonImage.src = pokemonGif;
            //Changement de la description
            document.querySelector(".description p").innerHTML = "";
            document.querySelector(".description p").innerHTML =
              data.pokemonToDisplay.description;
            //Changement du fond
            document
              .querySelector(".fond")
              .classList.remove(window.pokemonBackground);
            window.pokemonBackground =
              typesBackgroundArray[data.pokemonToDisplay.type1];
            document
              .querySelector(".fond")
              .classList.add(typesBackgroundArray[data.pokemonToDisplay.type1]);

            //Changement des types
            let type1 = document.querySelector(".type1");
            let type2 = document.querySelector(".type2");

            //Type 1
            type1.src =
              pokemonsTypeDir + type1Element[data.pokemonToDisplay.type1];

            if (data.pokemonToDisplay.type2 != null) {
              type2.classList.remove("type-none");

              type2.src =
                pokemonsTypeDir + type1Element[data.pokemonToDisplay.type2];
            } else {
              type2.classList.add("type-none");
            }

            //Bouton de shiny si l'utilisateur possède le shiny de ce pokémon
            let mobileShinyButton = document.querySelector(
              ".shiny-button-mobile",
            );
            mobileShinyButton.classList.add("type-none");

            if (data.pokemonToDisplay.shiny === true) {
              mobileShinyButton.classList.remove("type-none");
              mobileShinyButton.classList.add("type-on");
            } else {
              mobileShinyButton.classList.remove("type-on");
              mobileShinyButton.classList.add("type-none");
            }
          })
          .catch((error) => {
            console.log(error);
          });

        displayInProgress = false;
      }
    }
  });
});

// Animation du bouton Shiny sur mobile
// let shinyButton = document.querySelector(".shiny-button-mobile");
// let pokeGif = document.querySelector(".poke-gif");
//
// // Ajout de l'événement "click" du bouton shiny
// shinyButton.addEventListener("click", function () {
//   let currentSrc = pokeGif.src;
//   let currentFileName = currentSrc.substring(currentSrc.lastIndexOf("/") + 1);
//
//   if (currentSrc.includes("/shiny-")) {
//     pokeGif.src = currentSrc.replace("/shiny-", "/").replace("shiny-", "");
//   } else {
//     pokeGif.src = currentSrc.replace(
//       currentFileName,
//       "shiny-" + currentFileName,
//     );
//   }
// });

// Select des générations
document.querySelector("#generations").addEventListener("change", (event) => {

  const apiPath = baseUrl + 'generation-api/' + event.target.value;

  fetch(apiPath, {method: "GET"})
    .then(response => response.json())
    .then(pokemons => updatePokedexList(pokemons))
    .catch(error => console.log(error));

});

// Search on all pokemon
let timeout = null;

document.querySelector('#pokedexSearch').addEventListener('input', (event) => {

  let apiPath = baseUrl + 'search-api?search=' + event.target.value;

  clearTimeout(timeout);
  timeout = setTimeout(() => {

    if (event.target.value === '') {
      apiPath = baseUrl + 'generation-api/' + document.querySelector("#generations").value;
    }

    fetch(apiPath, {method: "GET"})
      .then(response => response.json())
      .then(pokemons => updatePokedexList(pokemons))
      .catch(error => console.log(error));

  }, 500);
})

const updatePokedexList = (pokemons) => {
  const pokemonsContainer = document.querySelector("#pokemonsContainer");
  const pokemonCapturedContainerTpl = document.querySelector("#pokemonCapturedTpl");
  const pokemonNotCapturedContainerTpl = document.querySelector("#pokemonNotCapturedTpl");
  const shinyImgTpl = document.querySelector('#shinyImgTpl');

  const pokedexCounter = document.querySelector('#pokedexCounter');
  const shinydexCounter = document.querySelector('#shinydexCounter');

  const pokedexCount = pokemons.filter((p) => p.captured).length;
  const shinyCount = pokemons.filter((p) => p.shiny).length;
  pokedexCounter.textContent = `${pokedexCount} sur ${pokemons.length}`;
  shinydexCounter.textContent = `${shinyCount} sur ${pokemons.length}`;

  pokemonsContainer.textContent = null

  pokemons.forEach(pokemon => {

    let pokemonContainer = null;
    let pokemonName = null;
    let pokemonFullName = '???';

    if (pokemon.captured || pokemon.shiny) {
      pokemonContainer = pokemonCapturedContainerTpl.content.cloneNode(true).firstElementChild;
      pokemonName = pokemon.name?.charAt(0).toUpperCase() + pokemon.name?.slice(1);
      pokemonFullName = `${pokemonName} #${pokemon.pokeId}`;
    } else {
      pokemonContainer = pokemonNotCapturedContainerTpl.content.cloneNode(true).firstElementChild;
    }

    pokemonContainer.textContent = pokemonFullName;
    pokemonsContainer.appendChild(pokemonContainer);

    if (pokemon.shiny) {
      let shinyImg = shinyImgTpl.content.cloneNode(true).firstElementChild;
      pokemonContainer.appendChild(shinyImg);
    }
  })
}
