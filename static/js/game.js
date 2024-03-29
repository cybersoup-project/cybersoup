const chlid = document.getElementById("idchl").value;
// https://animate.style/
const animateCSS = (element, animation, prefix = 'animate__') =>
    // We create a Promise and return it
    new Promise((resolve, reject) => {
        const animationName = `${prefix}${animation}`;
        const node = document.querySelector(element);

        node.classList.add(`${prefix}animated`, animationName);

        // When the animation ends, we clean the classes and resolve the Promise
        function handleAnimationEnd(event) {
            event.stopPropagation();
            node.classList.remove(`${prefix}animated`, animationName);
            resolve('Animation ended');
        }

        node.addEventListener('animationend', handleAnimationEnd, { once: true });
    });

// triggered on page load and after every attempt.
heartfn = () => {

    let health = document.getElementById("health");
    let heart = document.createElement("i");
    heart.classList = "bi bi-heart-fill";

    let xhr = new XMLHttpRequest();

    //const chlid = document.getElementById("idchl").value;
    /* window.location.hostname + window.location.pathname + */

    xhr.open("GET",  window.location.protocol + "//" + window.location.host + "?action=getHealth&id=" + chlid);

    xhr.onreadystatechange = () => {
        if (xhr.readyState == 4 && xhr.status == 200) {

            respuesta = JSON.parse(xhr.responseText);
            health.innerHTML = "";

            if (respuesta.status == "not logged in") {
                health.textContent = "Please log in to play.";
            } else {
                if (respuesta.health > 0) {
                    for (let index = 0; index < respuesta.health; index++) {
                        health.appendChild(heart.cloneNode(true));
                        console.log("heart");
                    }
                } else {
                    health.textContent = "Out of lives!";
                    animateCSS('#health', 'shakeX');
                }
            }
        }
    }

    xhr.send();

}

// show the keyboard
const Keyboard = window.SimpleKeyboard.default;

let options = {
    onKeyPress: button => onKeyPress(button),
    mergeDisplay: true,
    layoutName: "default",
    physicalKeyboardHighlight: true,
    physicalKeyboardHighlightPress: true,
    layout: {
        default: [
            "Q W E R T Y U I O P {backspace}",
            "A S D F G H J K L {ent}",
            "Z X C V B N M"
        ]
    },
    display: {
        "{numbers}": "123",
        "{ent}": "enter",
        "{escape}": "esc ⎋",
        "{tab}": "tab ⇥",
        "{backspace}": "⌫",
        "{capslock}": "caps lock ⇪",
        "{shift}": "⇧",
        "{controlleft}": "ctrl ⌃",
        "{controlright}": "ctrl ⌃",
        "{altleft}": "alt ⌥",
        "{altright}": "alt ⌥",
        "{metaleft}": "cmd ⌘",
        "{metaright}": "cmd ⌘",
        "{abc}": "ABC"
    },
  };

let keyboard = new Keyboard({
    ...options
});

/* Not the best fix to make lowercase work, but the clock is ticking */
let lowercasekeyboard = new Keyboard('.simple-keyboard-lowercase' , {
    ...options,
    layout: {
        default: [
            "q w e r t y u i o p",
            "a s d f g h j k l",
            "z x c v b n m"
        ]
    }
});

// triggered on win
function confettifn(respuesta) {
    function randomInRange(min, max) {
        return Math.random() * (max - min) + min;
    }

    if (respuesta.length != 0) {
        Swal.fire({
            icon: 'success',
            title: 'Congratulations!',
            text: 'You have succeded at this challenge!'
        })
    }

    confetti({
        angle: randomInRange(55, 125),
        spread: randomInRange(50, 70),
        particleCount: randomInRange(50, 100),
        origin: { y: 0.6 }
    });

    setTimeout(() => {
        confetti({
            angle: randomInRange(55, 125),
            spread: randomInRange(50, 70),
            particleCount: randomInRange(50, 100),
            origin: { y: 0.6 }
        });
    }, 500);

}

// color the fields according to the answer.
function coloreame(campos, respuesta, status, confetti) {
    console.log(respuesta);
    for (let index = 0; index < campos.length; index++) {
        switch (respuesta[index]) {
            case ("ok" || respuesta.status == "success"):
                campos[index].style.backgroundColor = "#0f0";
                campos[index].style.setProperty('border-color', '#0c0', 'important');
                break;
            case "exists":
                campos[index].style.backgroundColor = "#ffc342";
                campos[index].style.setProperty('border-color', '#ffae00', 'important');
                break;
            case "null":
                campos[index].style.backgroundColor = "#f00";
                campos[index].style.setProperty('border-color', '#c00', 'important');
                break;
        }
    }

    if (confetti) {
        if (status == "success") {
            confettifn(respuesta);
        }
    }
}

// on keyboard keypress
function onKeyPress(button) {
    let campos = document.getElementsByName("campo");
    let palabra = "";

    if (button == "{ent}" && campos[campos.length - 1].textContent != "") {
        // if all the fields are fullfilled
        let xhr = new XMLHttpRequest();

        palabra = "";

        for (let index = 0; index < campos.length; index++) {
            palabra = palabra + campos[index].textContent;
        }

        //const chlid = document.getElementById("idchl").value;
        //const chlid = urlParams.get('id')

        xhr.open("GET", window.location.protocol + "//" + window.location.host + "?action=checkWord&palabra=" + palabra + "&id=" + chlid);

        xhr.onreadystatechange = () => {
            if (xhr.readyState == 4 && xhr.status == 200) {
                console.log(xhr.responseText);
                res = JSON.parse(xhr.responseText);
                if (res.status == "not logged in") {
                    // if the user is not logged in, show a message.
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'You must be logged in to do that!',
                        footer: 'Please&nbsp;<a href="index.php?action=login">log in</a>&nbsp;or&nbsp;<a href="index.php?action=register">register</a>'
                    })
                } else {
                    // else, color the fields or/and show modals
                    coloreame(campos, res.word, res.status, true);
                }
            }
        }

        xhr.send();
        heartfn();
    }

    if (button == "{backspace}") {
        for (let i = 0; i < campos.length - 1; i++) {
            var lastfield = campos[i];
            var newfield = campos[i + 1];
            if (newfield.textContent == "") {
                lastfield.textContent = "";
            }

            if (newfield == campos[campos.length - 1]) {
                newfield.textContent = "";
            }

        }
    } else if (button != "{ent}") {
        for (let i = 0; i < campos.length; i++) {
            /* palabra = palabra + campos[i].textContent; */
            /* console.log(typeof button); */

            if (campos[i].textContent.length == 0) {
                campos[i].textContent = button.toUpperCase();
                break;
            }
        }
    }
    console.log("Button pressed", button);
}

window.onload = heartfn;

// -------------------- modal functions

let showmodal = document.getElementById("attempts");
let attemptsrender = document.getElementById("attemptsrender");

showmodal.addEventListener("click", () => {
    let xhr = new XMLHttpRequest();

    xhr.onreadystatechange = function() {
        if (this.status == 200 && this.readyState == 4) {
            attemptsrender.innerHTML = "";
            let res = JSON.parse(this.responseText);
            let past = res.past;
            let words = res.word;

            console.log(past);
            console.log(words);

            for (let i = 0; i < past.length; i++) {
                let div = document.createElement("div");
                div.classList = "d-flex justify-content-center my-2";
                for (let j = 0; j < words[i].length; j++) {
                    let template = `<div class="mx-1">
            <p class="fs-3 square border border-3 border-secondary" name="resolved_${i}">${past[i][j]}</p>
        </div>`;
                    div.innerHTML += template;
                }

                attemptsrender.appendChild(div);
                coloreame(document.getElementsByName("resolved_" + i), words[i], null, false);
            }
        }
    }

    xhr.open("GET", window.location.protocol + "//" + window.location.host + "?action=showAttempts&id=" + chlid)

    xhr.send();
})

// -------------------- reset button

let reset = document.getElementById("reset");

reset.addEventListener("click", () => {
    let campos = document.getElementsByName("campo");
    for (let i = 0; i < campos.length; i++) {
        campos[i].textContent = "";
        campos[i].style.backgroundColor = "#F8F9FA";
        campos[i].style.setProperty('border-color', '#0c0');

    }
})