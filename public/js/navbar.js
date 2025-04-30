
const prefixe = "read-";
let notifications = 0;

const ancienBadge = document.querySelector('.nav-link .notification');
if (ancienBadge) {
    ancienBadge.remove();
}

for (let i = 0; i < localStorage.length; i++) {
    const key = localStorage.key(i);

    if (key && key.startsWith(prefixe)) {
        const value = localStorage.getItem(key);
        if (value === "false") {
            notifications++;
        }
    }
}

if (notifications > 0) {
    let newSpan = document.createElement("span");
    newSpan.textContent = notifications.toString();
    newSpan.classList.add("notification");

    document.querySelector('.nav-link').append(newSpan);
}

