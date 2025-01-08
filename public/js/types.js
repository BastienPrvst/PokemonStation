const typeButtons = document.querySelectorAll('.type-title');

typeButtons.forEach(button => {
    button.addEventListener('click', function () {
        const typeDescription = this.nextElementSibling;
        const caret = this.querySelector('.right-caret');

        typeDescription.classList.toggle('show');
        caret.classList.toggle('fa-caret-down');
        caret.classList.toggle('fa-caret-right');
    });
});