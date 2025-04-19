//Rotate de la card

let card = document.querySelector(".flip-card");
let elementToIgnore = card.querySelector(".modify-avatar");

card.onclick = function (event) {
  if (event.target === elementToIgnore) {
    return;
  }
  card.querySelector(".trainer-card").classList.toggle("rotate-b");
  card.querySelector(".back-card").classList.toggle("rotate-a");
};
