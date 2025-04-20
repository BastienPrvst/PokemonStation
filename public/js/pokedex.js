import { clickSound, manageSound } from "./Sound.js";

manageSound();
clickSound();

let pokemonGif;
let pokemonImage = document.querySelector(".poke-gif");
let buttons = document.querySelectorAll(".pokemon-pokedex");
let currentPokeId = null;

let name = document.querySelector(".english-name").innerHTML;
const audio = new Audio(crySound + name + "-cry.mp3");

// Select on click a pokemon to display on pokedex
buttons.forEach(function (button) {
  button.addEventListener("click", function (event) {
    let pokemonId = event.target.getAttribute("data-pokemon");

    if (pokemonId === currentPokeId) return;

    currentPokeId = pokemonId;

    fetch(baseUrl + "pokedex-api/" + pokemonId, { method: "GET" })
      .then((response) => response.json())
      .then((data) => updatePokedex(data[0]))
      .catch((error) => console.log(error));
  });
});

// Select des générations
document.querySelector("#generations").addEventListener("change", (event) => {
  const apiPath = baseUrl + "generation-api/" + event.target.value;

  fetch(apiPath, { method: "GET" })
    .then((response) => response.json())
    .then((pokemons) => updatePokedexList(pokemons))
    .catch((error) => console.log(error));
});

// Select des formes alternatives
document.querySelectorAll(".alt-captured")?.forEach((el) => {
  el.addEventListener("click", () => {
    const apiPath = baseUrl + "pokedex-api/" + el.dataset.pokemonAltCaptured;

    fetch(apiPath, { method: "GET" })
      .then((response) => response.json())
      .then((data) => updatePokedex(data[0]))
      .catch((error) => console.log(error));
  });
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

const formatName = (name) => name.charAt(0).toUpperCase() + name.slice(1);

const updatePokedex = (pokemon) => {
  if (!pokemon.captured && !pokemon.shiny) {
    let altCaptured = pokemon.relatedPokemon.find(
      (poke) => poke.captured || poke.shiny,
    );
    altCaptured.relatedPokemon = [
      pokemon,
      ...pokemon.relatedPokemon.filter((p) => p.id !== altCaptured.id),
    ];

    pokemon = altCaptured;
  }

  let imgType = document.querySelector("#pokemonType");
  let imgType2 = document.querySelector("#pokemonType2");
  let imgGif = document.querySelector("#pokemonGif");
  let pathGif = imgGif?.src?.split("/") ?? undefined;
  let pathImgType = imgType?.src?.split("/") ?? undefined;
  let pathImgType2 = imgType2?.src?.split("/") ?? undefined;
  audio.src = crySound + pokemon.name_en + "-cry.mp3";
  pathGif.pop();
  pathImgType.pop();
  pathImgType2.pop();

  let newPathGif = pathGif.join("/") + `/${pokemon.name_en}.gif`;
  let newImgType = pathImgType.join("/") + `/${pokemon.type}.png`;
  let newImgType2 = pathImgType2.join("/") + `/${pokemon.type2}.png`;

  imgGif.src = newPathGif;
  imgType.src = newImgType;

  if (pokemon.type2) {
    imgType2.classList.remove("d-none");
    imgType2.src = newImgType2;
  } else {
    imgType2.classList.add("d-none");
  }

  document.querySelector("#pokedexMain").style.backgroundImage =
    `url(/medias/images/fonds/${pokemon.type}.png)`;
  document.querySelector("#pokemonName").innerHTML = formatName(pokemon.name);
  document.querySelector("#pokemonDescription").innerHTML = pokemon.description;

  let pokedexAltContainer = document.querySelector("#pokedexAltContainer");
  let pokedexAltCapturedTpl = document.querySelector("#pokedexAltCaptured");
  let pokedexAltNotCapturedTpl = document.querySelector(
    "#pokedexAltNotCaptured",
  );

  pokedexAltContainer.querySelectorAll(".alt").forEach((node) => node.remove());

  pokemon.relatedPokemon.map((p) => {
    let pokedexAlt = null;

    if (p.captured || p.shiny) {
      pokedexAlt =
        pokedexAltCapturedTpl.content.cloneNode(true).firstElementChild;
      let altImg = pokedexAlt.querySelector("img");
      let pathAltImg = altImg?.src?.split("/") ?? undefined;

      pathAltImg.pop();
      altImg.src = pathAltImg.join("/") + `/${p.name_en}.gif`;

      pokedexAlt.addEventListener("click", () => {
        fetch(baseUrl + "pokedex-api/" + p.pokeId, { method: "GET" })
          .then((response) => response.json())
          .then((data) => updatePokedex(data[0]))
          .catch((error) => console.log(error));
      });
    } else {
      pokedexAlt =
        pokedexAltNotCapturedTpl.content.cloneNode(true).firstElementChild;
    }

    pokedexAltContainer.appendChild(pokedexAlt);
  });
};

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
  pokemons.map((p) => {
    if (p.captured) pokedexCount++;
    if (p.shiny) shinyCount++;
    pokedexCount =
      pokedexCount + p.relatedPokemon.filter((pr) => pr.captured).length;
    shinyCount = shinyCount + p.relatedPokemon.filter((pr) => pr.shiny).length;
  });
  pokedexCounter.textContent = `${pokedexCount} sur ${pokemons.length}`;
  shinydexCounter.textContent = `${shinyCount} sur ${pokemons.length}`;

  pokemonsContainer.textContent = null;

  pokemons.forEach((pokemon) => {
    let pokemonContainer = null;
    let pokemonName = null;
    let pokemonFullName = "???";

    if (pokemon.captured || pokemon.altCaptured) {
      pokemonContainer =
        pokemonCapturedContainerTpl.content.cloneNode(true).firstElementChild;
      pokemonName = formatName(pokemon.name);
      pokemonFullName = `${pokemonName} #${pokemon.pokeId}`;
      pokemonContainer.addEventListener("click", () => {
        let pokemonId = pokemon.pokeId;

        if (pokemonId === currentPokeId) return;

        currentPokeId = pokemonId;

        fetch(baseUrl + "pokedex-api/" + pokemonId, { method: "GET" })
          .then((response) => response.json())
          .then((data) => updatePokedex(data[0]))
          .catch((error) => console.log(error));
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

let volumeButton = document.querySelector(".volume-dex");
volumeButton.onclick = function (e) {
  if (localStorage.getItem("soundOn") === "true") {
    audio.volume = 0.025;
    audio.play();
  } else {
    audio.muted = true;
  }
};

class PokedexAutoScroller {
  /**
   * @param {string} containerSelector  Sélecteur du conteneur à défiler
   * @param {string} leftBtnSelector    Sélecteur du bouton gauche
   * @param {string} rightBtnSelector   Sélecteur du bouton droit
   * @param {object} options
   *   - speed (number): vitesse initiale en px/s (défaut: 100)
   */
  constructor(
    containerSelector,
    leftBtnSelector,
    rightBtnSelector,
    options = {},
  ) {
    this.container = document.querySelector(containerSelector);
    this.leftBtn = document.querySelector(leftBtnSelector);
    this.rightBtn = document.querySelector(rightBtnSelector);
    this.speed = options.speed || 100; // px/s
    this.direction = 0; // -1 = gauche, +1 = droite
    this.isScrolling = false;
    this.lastTime = null;
    this.keyDown = false;

    this.leftArrow = this.container.querySelector(".arrow-left-dex");
    this.rightArrow = this.container.querySelector(".arrow-right-dex");

    this._bindEvents();
    // Mise à jour initiale de la visibilité des flèches
    setTimeout(() => this._updateArrows(), 50);
  }

  /** Démarre le défilement dans la direction donnée (-1 ou +1) */
  start(dir) {
    if (!this._hasOverflow()) return;
    this.direction = dir;
    this.isScrolling = true;
    requestAnimationFrame(this._loop.bind(this));
  }

  /** Arrête le défilement */
  stop() {
    this.isScrolling = false;
    this.lastTime = null;
  }

  /** Change la vitesse (en px/s) à la volée */
  setSpeed(newSpeed) {
    this.speed = newSpeed;
  }

  /** Boucle animée, indépendante du framerate */
  _loop(timestamp) {
    if (!this.isScrolling) return;
    if (this.lastTime !== null) {
      const deltaSec = (timestamp - this.lastTime) / 1000;
      this.container.scrollLeft += this.direction * this.speed * deltaSec;
      this._updateArrows();
    }
    this.lastTime = timestamp;
    requestAnimationFrame(this._loop.bind(this));
  }

  /** Affiche ou masque les flèches selon la position de scroll */
  _updateArrows() {
    const { scrollLeft, scrollWidth, clientWidth } = this.container;
    this.leftArrow.classList.toggle("d-none", scrollLeft <= 5);
    this.rightArrow.classList.toggle(
      "d-none",
      scrollLeft >= scrollWidth - clientWidth - 5,
    );
  }

  /** Vérifie si le conteneur déborde horizontalement */
  _hasOverflow() {
    return this.container.scrollWidth > this.container.clientWidth;
  }

  /** Attach des gestionnaires unifiés pointer + clavier */
  _bindEvents() {
    // Pointer (souris, tactile, stylet…)
    [
      { btn: this.leftBtn, dir: -1 },
      { btn: this.rightBtn, dir: +1 },
    ].forEach(({ btn, dir }) => {
      btn.addEventListener("pointerdown", (e) => {
        e.preventDefault();
        this.start(dir);
      });
    });
    window.addEventListener("pointerup", () => this.stop());
    window.addEventListener("pointercancel", () => this.stop());
    window.addEventListener("pointerleave", () => this.stop());

    // Clavier
    document.addEventListener("keydown", (e) => {
      if (this.keyDown) return;
      if (e.key === "ArrowLeft") {
        this.keyDown = true;
        this.start(-1);
      }
      if (e.key === "ArrowRight") {
        this.keyDown = true;
        this.start(+1);
      }
    });
    document.addEventListener("keyup", (e) => {
      if (e.key === "ArrowLeft" || e.key === "ArrowRight") {
        this.keyDown = false;
        this.stop();
      }
    });

    // Mise à jour des flèches si défilement manuel ou redimensionnement
    this.container.addEventListener("scroll", () => this._updateArrows());
    window.addEventListener("resize", () => this._updateArrows());
  }
}

// --- Initialisation au chargement de la page ---
document.addEventListener("DOMContentLoaded", () => {
  // Instanciez avec la vitesse souhaitée (px/s) :
  window.pokedexScroller = new PokedexAutoScroller(
    "#pokedexAltContainer",
    ".dpad-button.left",
    ".dpad-button.right",
    { speed: 400 },
  );
});
