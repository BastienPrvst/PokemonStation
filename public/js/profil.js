window.addEventListener("load", function () {
  let avatarId = avatar;

  let carouselItems = document.querySelectorAll(".carousel-item");
  for (let i = 0; i < carouselItems.length; i++) {
    let image = carouselItems[i].querySelector("img");
    if (image.getAttribute("data-avatar") === avatarId) {
      carouselItems[i].classList.add("active");
      break;
    }
  }
});

//Creation du système de changement d'avatar
document
  .querySelector(".select-character-button")
  .addEventListener("click", function () {
    let divToDelete = document.querySelector(".message-avatar");
    if (divToDelete) {
      divToDelete.remove();
    }
    let activeImage = document.querySelector(".carousel-item.active img");

    // Récupère l'ID de l'avatar correspondant à l'image active
    let avatarId = activeImage.getAttribute("data-avatar");
    let postData = new FormData();
    postData.append("avatarId", avatarId);

    let messageDiv = document.createElement("div");

    document.querySelector(".select-character-button").after(messageDiv);

    messageDiv.classList.add("message-avatar");

    fetch(profilPageApi, {
      method: "POST",
      body: postData,
    })
      .then((response) => response.json())

      .then((data) => {
        messageDiv.innerHTML = "<p>" + data.success + "</p>";
      })
      .catch((error) => {
        messageDiv.innerHTML = "<p>" + data.error + "</p>";
      });
  });
