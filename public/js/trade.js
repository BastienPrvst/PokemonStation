
let buttons = document.querySelectorAll('.my-pokemon-trade');

buttons.forEach(button => {
    button.addEventListener('click', (e) => {
        const id = button.dataset.id;
        console.log(id);
    })
})