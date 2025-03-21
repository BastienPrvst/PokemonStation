
let searchs = document.querySelectorAll('[data-search]');

searchs.forEach(input => {

    let timeout = null;
    const altInput = input.cloneNode(true);
    altInput.type = 'hidden';

    const div = input.parentElement;
    const submit = document.createElement('button');
    submit.type = 'submit';
    submit.innerText = input.dataset.searchLabel ?? 'Rechercher';
    submit.classList.add('search-button');
    submit.disabled = true;
    div.classList.add('d-flex');
    div.classList.add('position-relative');
    div.appendChild(submit);

    input.addEventListener('input', (event) => {

        const route = event.target.dataset.route;
        const search = event.target.value;

        submit.disabled = true;
        submit.classList.add('loading');

        clearTimeout(timeout); // avoid spam on each input but add delay
        timeout = setTimeout(() => {

            fetch(route + '?search=' + search, {method: 'GET'})
                .then((response) => response.json())
                .then(data => {
                    if (data) {
                        altInput.value = data;

                        input.parentElement.appendChild(altInput);
                        input.name = input.name + 'alt';

                        submit.disabled = false;
                        submit.classList.remove('loading');
                    }
                })
                .catch(error => {
                    console.log(error);
                });
        }, 1000);
    });

    input.addEventListener('submit', (event) => event.preventDefault())
});