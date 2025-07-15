import {clickSound, manageSound} from "./Sound.js";

manageSound();
clickSound();

let captureInProcess = false;
const pokeballButton = document.querySelectorAll(".capture-poke-button");

document
  .querySelector(".carousel-inner")
  .addEventListener("click", async function (event) {
    if (event.target && event.target.closest(".capture-poke-button")) {
      let button = event.target.closest(".capture-poke-button");

      if (!captureInProcess) {
        captureInProcess = true;

        let pokeballData = button.parentElement.getAttribute("data-ball");

        let postData = new FormData();

        postData.append("pokeballData", pokeballData);

        let carousel = document.querySelector(".carou-ball");

        carousel.classList.add("overflow-visible");

        //Si il y'a deja un pokemon, on l'enleve
        let currentPoke = document.querySelector(".displayed-pokemon");
        if (currentPoke) {
          currentPoke.remove();
        }
        let currentShiny = document.querySelector(".shining-effect");
        if (currentShiny) {
          currentShiny.remove();
        }
        let currentInfo = document.querySelector(".pokemon-captured-infos");
        if (currentInfo.classList.contains(".visi-one")) {
          currentInfo.innerHTML = "";
          currentInfo.classList.add(".visi-zero");
        }

        let currentNew = document.querySelector(".logo-new");
        if (currentNew) {
          currentNew.remove();
        }

        let currentPokeDiv = document.querySelector(".poke-capture-div");
        if (currentPokeDiv) {
          currentPokeDiv.remove();
        }

        //ON enleve une pokeball lancée

        let activeCarousel = document.querySelector(".carousel-item.active");
        let launchs = activeCarousel.querySelector(".launch-items").textContent;
        launchs = parseInt(launchs);

        if (launchs > 0) {
          activeCarousel.querySelector(".launch-items").textContent =
            launchs - 1;
        }

        let pokemonImage = document.createElement("img");
        let pokemonShining = document.createElement("img");
        let pokemonNewLogo = document.createElement("img");
        let pokeCoin = document.createElement("img");
        let pokemonDiv = document.createElement("div");
        pokemonImage.classList.add("displayed-pokemon");
        pokemonShining.classList.add("shining-effect");
        pokemonImage.alt = "";
        pokemonShining.alt = "";
        let pokemonGif;
        let pokemonShine;
        let pokemonIsNew;

        let pokemonIsCaptured = false;

        let getPokemonPromise = new Promise((resolve, reject) => {
          //Affichage du gif du pokémon
          fetch(capturedPageApi, {
            method: "POST",
            body: postData,
          })
            .then((response) => response.json())
            .then((data) => {
              //Vérification du nombre de lancers

              if (data.error != null) {
                document
                  .querySelector(".pokemon-captured-infos")
                  .classList.remove("visi-zero");
                document.querySelector(".pokemon-captured-infos").innerHTML =
                  data.error;
                captureInProcess = false;
                return;
              } else {
                pokemonIsCaptured = true;
                const rarity = data.captured_pokemon.rarity;
                //Si le pokemon est shiny on change la route du gif
                pokemonGif =
                  pokemonsGifDir +
                  "/" +
                  (data.captured_pokemon.shiny ? "shiny-" : "") +
                  data.captured_pokemon.nameEN +
                  ".gif";

                //Effets en fonction de la rareté
                if (data.captured_pokemon.shiny === true) {
                  pokemonShine = pokemonsShineDir + "/shiny-sparkle.gif";
                } else if (rarity === "TR") {
                  pokemonShine = pokemonsShineDir + "/sparkle.gif";
                } else if (rarity === "EX") {
                  pokemonShine = pokemonsShineDir + "/orange-sparkle.gif";
                } else if (rarity === "SR") {
                  pokemonShine = pokemonsShineDir + "/red-sparkle.gif";
                } else {
                  pokemonShine = pokemonsShineDir + "/invisible-sparkle.gif";
                }

                //Fonds en fonction des types
                document.querySelector(".view-pokemon").style.backgroundImage =
                  `url(/medias/images/fonds/${data.captured_pokemon.type}.png)`;

                document
                  .querySelector(".pokeball-animate")
                  .classList.remove("pokeball-animated");

                if (data.captured_pokemon.new === true) {
                  pokemonIsNew = true;
                }
              }

              //Resolve de la promesse
              resolve(data.captured_pokemon);
            });
        });

        let pokemon = await getPokemonPromise;

        const motionPathArray = {
          1: "M0 0C-895-382-119-691 5-421c35 62-5 91.3333-5 134",
          2: "M0 0C474-414-119-691 0-286",
          3: "M0 0C103.1319 33.5995 41.5327-143.7313-58.3325-63.4658-97.5319-18.6664-76 24-37.3328 64.3991-6 96 109.6651 38.2661 102.1985-28.9329 94.2653-94.2653 1.8666-128.3315 0-280",
        };

        let totalPaths = Object.keys(motionPathArray).length;
        let rand = Math.floor(Math.random() * totalPaths) + 1;
        let raRand = pokemon.rarityRandom;

        //Partie audio

        let rareSound = new Audio(newRare);
        let cry = new Audio(crySound + pokemon.nameEN + "-cry.mp3");
        if (localStorage.getItem("soundOn") === "true") {
          rareSound.volume = 0.025;
          cry.volume = 0.02;
        } else {
          rareSound.muted = true;
          cry.muted = true;
        }

        let animatePromise = new Promise((resolve, reject) => {
          (() => {
            let tl = gsap.timeline();
            let pathRandom = motionPathArray[rand];
            let rotationDegree = -2500;
            if (pathRandom.includes("0C-") || pathRandom.includes("0C0-")) {
              rotationDegree = 2500;
            }
            let initialPosition = {
              x: gsap.getProperty(".pokeball-animate", "x"),
              y: gsap.getProperty(".pokeball-animate", "y"),
              rotation: gsap.getProperty(".pokeball-animate", "rotation"),
            };

            let finalPosition = {};

            tl.to(".pokeball-animate", {
              duration: 0.4,
              rotation: rotationDegree,
              motionPath: pathRandom,
              ease: "power1.in",
            });

            tl.add(() => {
              finalPosition.x = gsap.getProperty(".pokeball-animate", "x");
              finalPosition.y = gsap.getProperty(".pokeball-animate", "y");
            }, ">");

            tl.to(
              ".pokeball-animate",
              {
                duration: 0.2,
                y: () => finalPosition.y - 100,
                ease: "power2.out",
              },
              ">",
            );

            tl.to(
              ".pokeball-animate",
              {
                y: () => finalPosition.y,
                ease: "bounce.out",
                duration: 0.3,
              },
              ">",
            );
            if (pokemon.shiny) {
              tl.to(
                ".pokeball-animate",
                {
                  filter: "hue-rotate(-90deg)",
                  duration: 0.5,
                },
                ">",
              );
              tl.to(
                ".pokeball-animate",
                {
                  filter: "hue-rotate(0deg)",
                  duration: 0.5,
                },
                ">",
              );
            }

            if (["EX", "UR", "SR"].includes(pokemon.rarity) || raRand >= 90) {
              {
                tl.to(
                  ".logo-what",
                  {
                    autoAlpha: 1,
                    duration: 0.6,
                  },
                  ">",
                );

                tl.to(
                  ".pokeball-animate",
                  {
                    duration: 0.4,
                    rotation: rotationDegree + 15,
                    ease: "back",
                    repeat: 1,
                    x: "+= 10",
                    yoyo: true,
                  },
                  ">",
                );
                tl.to(
                  ".logo-what",
                  {
                    autoAlpha: 0,
                    duration: 0.8,
                  },
                  ">",
                );
              }
            }

            if (["UR"].includes(pokemon.rarity)) {
              tl.to(
                ".logo-oh",
                {
                  autoAlpha: 1,
                  duration: 0.6,
                },
                ">",
              );

              tl.to(".pokeball-animate", {
                rotation: rotationDegree + 15,
                duration: 0.4,
                ease: "back",
                repeat: 1,
                x: "+= 10",
                yoyo: true,
              });
              tl.to(".logo-oh", {
                autoAlpha: 0,
                duration: 0.4,
                onComplete: function () {
                  rareSound.play();
                  gsap.set(".pokeball-animate", initialPosition);
                  resolve();
                },
              });
            } else {
              tl.to(
                ".pokeball-animate",
                {
                  onComplete: function () {
                    gsap.set(".pokeball-animate", initialPosition);
                    resolve();
                  },
                },
                ">",
              );
            }
          })();
        });

        await animatePromise;

        try {
          await cry.play();
        } catch (error) {
          console.warn(
            "Impossible de lire l'audio du pokemon " + pokemon.name + " " + cry,
            error,
          );
        }

        //Affichage des infos du pokemon libéré

        const rarityScale = {
          C: 1,
          PC: 3,
          R: 5,
          TR: 10,
          ME: 50,
          GMAX: 50,
          SR: 100,
          EX: 100,
          UR: 250,
        };

        let money = rarityScale[pokemon.rarity];
        if (pokemon.shiny) {
          money *= 10;
        }

        let totalCoins = money + pokemon.multiplyMoney;

        if (pokemon.new === false) {
          let actualCoin = document.querySelector(".coin-count").textContent;
          actualCoin = parseInt(actualCoin);
          document.querySelector(".coin-count").textContent =
            actualCoin + totalCoins;
        }

        currentInfo.classList.replace("visi-zero", "visi-one");

        currentInfo.innerHTML = `
        Vous avez libéré <span class="text-capitalize info-margin">${pokemon.name}</span>
        ${pokemon.shiny ? " Shiny" : ""}
        ${pokemon.new ? "" : ` ${pokemon.times_captured} fois !`}
        (${pokemon.rarity}) !
        ${!pokemon.new ? `<br>+${money}${pokemon.multiplyMoney > 0 ? ` (+ ${pokemon.multiplyMoney} bonus)` : ""}` : ""}
      `;

        if (pokemonIsCaptured) {
          pokemonImage.src = pokemonGif;
          pokemonShining.src = pokemonShine;
          pokemonNewLogo.src = newLogo;
          pokeCoin.src = coin;
          pokeCoin.classList.add("coin-width");
          pokemonDiv.classList.add("poke-capture-div");
          pokemonDiv.append(pokemonShining, pokemonImage);

          if (pokemonIsNew === true) {
            pokemonNewLogo.classList.add("logo-new");
            pokemonDiv.append(pokemonNewLogo);
          } else {
            currentInfo.append(pokeCoin);
          }

          document.querySelector(".view-pokemon").append(pokemonDiv);
        }
        let image = button.querySelector("img");
        image.style.bottom = "0px";
        image.style.rotate = "0deg";
        carousel.classList.remove("overflow-visible");

        setTimeout(() => {
          captureInProcess = false;
        });
      }
    }
  });

//Code pour le SHOP

//Fonctions

function add(number) {
  document
    .querySelector(".plus-" + number)
    .addEventListener("click", function () {
      let i = document.querySelector(".quantity-" + number);
      i.innerHTML = parseInt(i.innerHTML) + 1;
      let totalShop = document.querySelector(".total_shop");
      let price = document.querySelector(".price-" + number);
      totalShop.innerHTML =
        parseInt(totalShop.innerHTML) + parseInt(price.innerHTML);
    });
}

function unset(number) {
  document
    .querySelector(".minus-" + number)
    .addEventListener("click", function () {
      let i = document.querySelector(".quantity-" + number);
      i.innerHTML = parseInt(i.innerHTML);

      if (i.innerHTML > 0) {
        i.innerHTML = parseInt(i.innerHTML) - 1;
        let totalShop = document.querySelector(".total_shop");
        let price = document.querySelector(".price-" + number);
        totalShop.innerHTML =
          parseInt(totalShop.innerHTML) - parseInt(price.innerHTML);
      }
    });
}

let i = 1;
document.querySelectorAll(".minus").forEach(function () {
  add(i);
  unset(i);
  i++;
});

//Utilisation AJAX

let shopInProcess = false;

let buyButton = document.querySelector(".buy-it");

buyButton.addEventListener("click", function () {
  if (!shopInProcess) {
    shopInProcess = true;

    let shopToDelete = document.querySelector(".shopNotice");
    if (shopToDelete) {
      shopToDelete.remove();
    }

    let globalArray = [];

    let allItems = document.querySelectorAll(".shop-item");
    allItems.forEach(function (item) {
      let quantity = parseInt(item.querySelector(".quantity").innerHTML);
      if (quantity > 0) {
        let itemArray = {
          id: parseInt(item.querySelector(".item-id").innerHTML),
          quantity: quantity,
        };
        globalArray.push(itemArray);
      }
    });

    let shopInfo = document.createElement("p");

    fetch(capturedShopApi, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({ globalArray }),
    })
      .then((response) => response.json())

      .then((data) => {
        if (data.error != null) {
          shopInfo.innerHTML = data.error;
        } else {
          let quantity = document.querySelectorAll(".quantity");
          quantity.forEach(function (quan) {
            quan.textContent = "0";
          });
          let totalPrice = document.querySelector(".total_shop");
          totalPrice.textContent = "0";

          //On enlève les sous du compteur de l'utilisateur
          let userWallet = document.querySelector(".coin-count").textContent;
          userWallet = parseInt(userWallet);
          let kartPrice = parseInt(data.kartPrice);
          document.querySelector(".coin-count").textContent =
            userWallet - kartPrice;
          //Message de succès

          let shopSuccesSound = new Audio(buyItemSound);
          localStorage.getItem("soundOn") === "true"
            ? (shopSuccesSound.volume = 0.025)
            : (shopSuccesSound.muted = true);

          shopSuccesSound.play();
          shopInfo.textContent = data.success;

          //Ajout des boutons ou incrementations
          for (let entity of data.array) {
            let carouselItem = document.querySelectorAll(".carou-not-base");
            let alreadyExist = false;
            carouselItem.forEach(function (pokeItem) {
              if (
                parseInt(pokeItem.dataset.ball) === parseInt(entity.item.id)
              ) {
                alreadyExist = true;
              }
            });

            if (alreadyExist === true) {
              let divToChange = document.querySelector(
                `.carou-not-base[data-ball='${entity.item.id}']`,
              );

              let base = parseInt(divToChange.lastElementChild.innerHTML);
              divToChange.lastElementChild.textContent =
                base + parseInt(entity.quantity);
            } else {
              let newCarou = document
                .querySelector(".carousel-item")
                .cloneNode(true);

              newCarou.dataset.ball = entity.item.id;
              newCarou.classList.add("carou-not-base");
              newCarou.classList.add(`ball-${entity.item.id}`);
              newCarou.classList.remove(`active`);
              let button = newCarou.querySelector(".capture-poke-button img");
              button.src = "/medias/images/balls/" + entity.item.image;
              const launchDiv = newCarou.querySelector(".launch-items");
              launchDiv.textContent = entity.quantity;
              document.querySelector(".carousel-inner").append(newCarou);
              newCarou.addEventListener("click", () => {});
            }
          }
        }

        let pokemonShop = document.querySelector(".shop");
        pokemonShop.append(shopInfo);
        shopInfo.classList.add("shopNotice");
        setTimeout(() => {
          shopInProcess = false;
        }, 1000);
      });
  }
});
