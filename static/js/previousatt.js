// color the fields according to the answer.
function coloreameatt(campos, respuesta) {
    for (let index = 0; index < campos.length; index++) {
        switch (respuesta.word[index]) {
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
}

// on pres modal button

function showattempts() {

    let xhr = new XMLHttpRequest();

    const queryString = window.location.search;
    const urlParams = new URLSearchParams(queryString);

    if (urlParams.get('id'))
        xhr.open("GET", "?action=showattempts&id=" + urlParams.get('id'));
    else {
        var idch = document.getElementById('idchall').innerHTML;
        xhr.open("GET", "?action=showattempts&id=" + idch);
    };

    xhr.onreadystatechange = () => {
        if (xhr.readyState == 4 && xhr.status == 200) {
            respuesta = JSON.parse(xhr.responseText);

        }
    }
    xhr.send()
    console.log(respuesta); //has aqui funciona
    console.log(respuesta[0]['solution']);


    let campos = document.getElementsByName("campo");
    let palabra = "";


    // if all the fields are fullfilled
    let xhr = new XMLHttpRequest();

    palabra = "";

    for (let index = 0; index < campos.length; index++) {
        palabra = palabra + campos[index].textContent;
    }

    const queryString = window.location.search;
    const urlParams = new URLSearchParams(queryString);


    if (urlParams.get('id'))
        xhr.open("GET", "?action=checkWord&palabra=" + palabra + "&id=" + urlParams.get('id'));
    else {
        var idch = document.getElementById('idchall').innerHTML;
        xhr.open("GET", "?action=checkWord&palabra=" + palabra + "&id=" + idch);
    }

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
                coloreame(campos, res);
            }
        }
    }

    xhr.send();


}