let pokemonGif;
let pokemonImage = document.querySelector(".poke-gif");
let buttons = document.querySelectorAll(".pokemon-pokedex");
let currentPokeId = null;

// Attache l'événement 'click' à chaque élément de la liste
buttons.forEach(function (button) {
  button.addEventListener("click", function (event) {

    let pokemonId = event.target.getAttribute("data-pokemon");

    if (pokemonId === currentPokeId) return;

    currentPokeId = pokemonId;

    fetch(baseUrl + 'pokedex-api/' + pokemonId, {method: "GET"})
      .then(response => response.json())
      .then(data => updatePokedex(data[0]))
      .catch(error => console.log(error));

  });
});

// Select des générations
document.querySelector("#generations").addEventListener("change", (event) => {
  const apiPath = baseUrl + "generation-api/" + event.target.value;

  fetch(apiPath, {method: "GET"})
    .then(response => response.json())
    .then(pokemons => updatePokedexList(pokemons))
    .catch(error => console.log(error));

});

// Select des formes alternatives
document.querySelectorAll(".alt-captured")?.forEach(el => {
  el.addEventListener("click", () => {

    const apiPath = baseUrl + 'pokedex-api/' + el.dataset.pokemonAltCaptured;

    fetch(apiPath, {method: "GET"})
      .then(response => response.json())
      .then(data => updatePokedex(data[0]))
      .catch(error => console.log(error));
  })
});

// Search on all pokemon
let timeout = null;

document.querySelector("#pokedexSearch").addEventListener("input", (event) => {
  let apiPath = baseUrl + "search-api?search=" + event.target.value;

  clearTimeout(timeout);
  timeout = setTimeout(() => {
    if (event.target.value === "") {
      apiPath =
        baseUrl +
        "generation-api/" +
        document.querySelector("#generations").value;
    }

    fetch(apiPath, { method: "GET" })
      .then((response) => response.json())
      .then((pokemons) => updatePokedexList(pokemons))
      .catch((error) => console.log(error));
  }, 500);
});

// LOCAL FUNCTIONS

const formatName = name => name.charAt(0).toUpperCase() + name.slice(1);

const updatePokedex = pokemon => {

  if (!pokemon.captured && !pokemon.shiny) {
    let altCaptured = pokemon.relatedPokemon.find(poke => poke.captured || poke.shiny);
    altCaptured.relatedPokemon = [
      pokemon,
      ...pokemon.relatedPokemon.filter(p => p.id !== altCaptured.id)
    ];

    pokemon = altCaptured;
  }

  let imgType = document.querySelector("#pokemonType");
  let imgType2 = document.querySelector("#pokemonType2");
  let imgGif = document.querySelector("#pokemonGif");
  let pathGif = imgGif?.src?.split('/') ?? undefined;
  let pathImgType = imgType?.src?.split('/') ?? undefined;
  let pathImgType2 = imgType2?.src?.split('/') ?? undefined;
  pathGif.pop();
  pathImgType.pop();
  pathImgType2.pop();

  let newPathGif = pathGif.join('/') + `/${pokemon.name_en}.gif`;
  let newImgType = pathImgType.join('/') + `/${pokemon.type}.png`;
  let newImgType2 = pathImgType2.join('/') + `/${pokemon.type2}.png`;

  imgGif.src = newPathGif;
  imgType.src = newImgType;

  if (pokemon.type2) {
    imgType2.classList.remove('d-none');
    imgType2.src = newImgType2;
  } else {
    imgType2.classList.add('d-none');
  }

  document.querySelector("#pokedexMain").style.backgroundImage = `url(/medias/images/fonds/${pokemon.type}.png)`;;
  document.querySelector("#pokemonName").innerHTML = formatName(pokemon.name);
  document.querySelector("#pokemonDescription").innerHTML = pokemon.description;

  let pokedexAltContainer = document.querySelector('#pokedexAltContainer');
  let pokedexAltCapturedTpl = document.querySelector('#pokedexAltCaptured');
  let pokedexAltNotCapturedTpl = document.querySelector('#pokedexAltNotCaptured');

  pokedexAltContainer.textContent = null;

  pokemon.relatedPokemon.map(p => {

    let pokedexAlt = null;

    if (p.captured || p.shiny) {

      pokedexAlt = pokedexAltCapturedTpl.content.cloneNode(true).firstElementChild
      let altImg = pokedexAlt.querySelector('img');
      let pathAltImg = altImg?.src?.split('/') ?? undefined;

      pathAltImg.pop();
      altImg.src = pathAltImg.join('/') + `/${p.name_en}.gif`;

      pokedexAlt.addEventListener('click', () => {

        fetch(baseUrl + 'pokedex-api/' + p.pokeId, {method: "GET"})
          .then(response => response.json())
          .then(data => updatePokedex(data[0]))
          .catch(error => console.log(error));
      })
    } else {

      pokedexAlt = pokedexAltNotCapturedTpl.content.cloneNode(true).firstElementChild
    }

    pokedexAltContainer.appendChild(pokedexAlt);
  });

}

const updatePokedexList = (pokemons) => {
  const pokemonsContainer = document.querySelector("#pokemonsContainer");
  const pokemonCapturedContainerTpl = document.querySelector(
    "#pokemonCapturedTpl",
  );
  const pokemonNotCapturedContainerTpl = document.querySelector(
    "#pokemonNotCapturedTpl",
  );
  const shinyImgTpl = document.querySelector("#shinyImgTpl");

  const pokedexCounter = document.querySelector("#pokedexCounter");
  const shinydexCounter = document.querySelector("#shinydexCounter");

  let pokedexCount = 0;
  let shinyCount = 0;
  pokemons.map(p => {
    if (p.captured) pokedexCount++;
    if (p.shiny) shinyCount++;
    pokedexCount = pokedexCount + p.relatedPokemon.filter(pr => pr.captured).length;
    shinyCount = shinyCount + p.relatedPokemon.filter(pr => pr.shiny).length;
  })
  pokedexCounter.textContent = `${pokedexCount} sur ${pokemons.length}`;
  shinydexCounter.textContent = `${shinyCount} sur ${pokemons.length}`;

  pokemonsContainer.textContent = null;

  pokemons.forEach((pokemon) => {
    let pokemonContainer = null;
    let pokemonName = null;
    let pokemonFullName = "???";

    if (pokemon.captured || pokemon.shiny) {
      pokemonContainer =
        pokemonCapturedContainerTpl.content.cloneNode(true).firstElementChild;
      pokemonName =
        pokemon.name?.charAt(0).toUpperCase() + pokemon.name?.slice(1);
    if (pokemon.captured || pokemon.altCaptured) {
      pokemonContainer = pokemonCapturedContainerTpl.content.cloneNode(true).firstElementChild;
      pokemonName = formatName(pokemon.name);
      pokemonFullName = `${pokemonName} #${pokemon.pokeId}`;
      pokemonContainer.addEventListener('click', () => {

      let pokemonId = pokemon.pokeId;

      if (pokemonId === currentPokeId) return;

        currentPokeId = pokemonId;

        fetch(baseUrl + 'pokedex-api/' + pokemonId, {method: "GET"})
          .then(response => response.json())
          .then(data => updatePokedex(data[0]))
          .catch(error => console.log(error));

      });
    } else {
      pokemonContainer =
        pokemonNotCapturedContainerTpl.content.cloneNode(
          true,
        ).firstElementChild;
    }

    pokemonContainer.textContent = pokemonFullName;
    pokemonsContainer.appendChild(pokemonContainer);

    if (pokemon.shiny) {
      let shinyImg = shinyImgTpl.content.cloneNode(true).firstElementChild;
      pokemonContainer.appendChild(shinyImg);
    }
  });
};
