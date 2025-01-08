window.addEventListener('load', () => {
    const element = document.querySelector('.sacha');
    element.classList.add('fade-up');
});

window.addEventListener('load', () => {
    const element = document.querySelector('.project');
    element.classList.add('fade-up');
    addFadeUpWhenVisible();
});