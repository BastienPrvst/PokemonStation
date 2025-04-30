

// Set the date we're counting down to
const now = new Date();
const firstDayNextMonth = new Date(now.getFullYear(), now.getMonth() + 1, 1, 0, 0, 0);

let countDownDate = new Date(firstDayNextMonth);

// Update the count down every 1 second
let x = setInterval(function() {

    // Get today's date and time
    let nowTime = new Date().getTime();

    // Find the distance between now and the count down date
    let distance = countDownDate - nowTime;

    // Time calculations for days, hours, minutes and seconds
    let days = Math.floor(distance / (1000 * 60 * 60 * 24));
    let hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
    let minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
    let seconds = Math.floor((distance % (1000 * 60)) / 1000);

    // Display the result in the element with id="demo"
    document.querySelectorAll(".countdown").forEach(function (el) {
        el.innerHTML = "Rafraichissement : " + days + "j " + hours + "h " + minutes + "m " + seconds + "s ";
    });

    // If the count down is finished, write some text
    if (distance < 0) {
        countDownDate = new Date(countDownDate.getFullYear(), countDownDate.getMonth() + 1, 1, 0, 0, 0);
    }
}, 1000);


//Partie lecture des news

const numbersToKeep = [];

document.querySelectorAll('.read-button').forEach(function (el) {
    let number = el.dataset.read;
    let img = el.querySelector('img');
    let local = localStorage.getItem(`read-${number}`);
    numbersToKeep.push(number);

    if (local === 'true'){
        img.src = readUrl;
    }

    if (!local){
        localStorage.setItem(`read-${number}`, 'false');
    }

    el.addEventListener('click', function (e) {
        if (img.src.endsWith('unread.png')) {
            img.src = readUrl;
            localStorage.setItem(`read-${number}`, 'true')
        }
    })

})

const keysToDelete = [];

for (let i = 0; i < localStorage.length; i++) {
    const key = localStorage.key(i);
    if (key.startsWith('read-')) {
        const keyNumber = key.split('read-')[1];
        if (!numbersToKeep.includes(keyNumber)) {
            keysToDelete.push(key); // on ne supprime pas encore
        }
    }
}

keysToDelete.forEach(key => localStorage.removeItem(key));
