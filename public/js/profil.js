//Creation du système de changement d'avatar

let modal = document.getElementById("myModal");

let btn = document.getElementById("myBtn");

let span = document.getElementsByClassName("close")[0];

btn.onclick = function () {
  modal.style.display = "block";
};

span.onclick = function () {
  modal.style.display = "none";
};

window.onclick = function (event) {
  if (event.target === modal) {
    modal.style.display = "none";
  }
};

window.onkeydown = function (e) {
  if (e.key === "Escape") {
    modal.style.display = "none";
  }
};

const ImageSelector = document.querySelectorAll(".modal-solo");

ImageSelector.forEach((el) => {
  el.addEventListener("click", function () {
    let divToDelete = document.querySelector(".message-avatar");
    if (divToDelete) {
      divToDelete.remove();
    }
    let choosenImage = el.querySelector("img");
    let activeImage = document.querySelector(".skibibop");

    // Récupère l'ID de l'avatar correspondant à l'image active
    let avatarId = choosenImage.getAttribute("data-avatar");
    let postData = new FormData();
    postData.append("avatarId", avatarId);

    let messageDiv = document.createElement("div");

    messageDiv.classList.add("message-avatar");

    fetch(profilPageApi, {
      method: "POST",
      body: postData,
    })
      .then((response) => response.json())

      .then((data) => {
        messageDiv.innerHTML = "<p>" + data.success + "</p>";
        activeImage.src = "../medias/images/trainers/" + avatarId + ".gif";
        document.getElementById("myModal").style.display = "none";
      })
      .catch((error) => {
        console.log(error);
      });
  });
});

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
