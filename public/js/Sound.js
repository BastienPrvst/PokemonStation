export function manageSound() {
  document.addEventListener("DOMContentLoaded", function () {
    if (localStorage.getItem("soundOn") === "false") {
      const soundOff = document.querySelector(".soundOff");
      const soundOn = document.querySelector(".soundOn");
      soundOff.classList.replace("type-none", "active");
      soundOn.classList.replace("active", "type-none");
    }
    if (localStorage.getItem("soundOff") === "") {
      localStorage.setItem("soundOff", "true");
    }
  });
}

export function clickSound() {
  const soundButton = document.querySelector(".volume");
  soundButton.addEventListener("click", () => {
    if (
      localStorage.getItem("soundOn") === "true" ||
      localStorage.getItem("soundOn") === ""
    ) {
      localStorage.setItem("soundOn", "false");
    } else {
      localStorage.setItem("soundOn", "true");
    }
    let childs = soundButton.children;
    for (const child of childs) {
      child.classList.toggle("active");
      child.classList.toggle("type-none");
    }
  });
}

