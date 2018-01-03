window.onload = initDivMouseOver;
var mouseIsOver = false;
function initDivMouseOver() {
    alert('initDivMouseOver ' + jQuery("#pendelActualNr").textContent);

    var div = document.getElementById("svg-section");

    mouseIsOver = false;
    div.onmouseover = function () {
        mouseIsOver = true;
//        alert('onmouseover');
    };
    div.onmouseout = function () {
        mouseIsOver = false;
//        alert('onmouseout');
    }
    window.addEventListener('wheel', function (e) {
        var direction = '';
        if (event.wheelDelta > 0) {
            direction = 'up';
        } else {
            direction = 'down';
        }
        switchCanvas(parseInt(document.getElementById("pendelActualNr").textContent),
                parseInt(document.getElementById("pendelNr").textContent),
                direction);

    }, false);
}



function pendelOnWheel() {

}
/*
 * Show cliecked image in a viewer
 */
function onTileClicked(imageSrc, title, description, lat, lon) {

//    var modal = document.getElementById('pendelModal');
    var span = document.getElementsByClassName("close")[0];
    var modal = document.getElementById('pendelModal');
    var content = document.getElementById('pendelModalContent');
    var img = document.getElementById('pendelModalImg');

    document.getElementById("viewer-title").textContent = title + ' / ' + description;
//    document.getElementById("viewer-subtitle").textContent = lat + ', ' + lon;

//    document.getElementById("viewer-subtitle").innerHTML = lat +
//            "<a href=\"http://www.openstreetmap.org/?mlat=" + lat + "&mlon=" + lon + ">OSM</a>"


    document.getElementById("viewer-subtitle").innerHTML = lat + ", " + lon +
            " <a href=\"http://www.openstreetmap.org/?mlat=" + lat + "&mlon=" + lon + "\" target = \"_blank\" >osm</a>";
    // Set the image to show
    img.src = imageSrc;


    // Prevent for showing previous image next time
//    setTimeout(function () {
    modal.className = 'modal modal-in'; // Whitespace!
//    }, 1);

    content.className = 'modal-content modal-content-in';


// When the user clicks on <span> (x), close the modal
    span.onclick = function () {
        closeViewer();
    }

// When the user clicks anywhere outside of the modal, close it
    window.onclick = function (event) {
        if (event.target == modal) {
            closeViewer();
        }
    }
    function closeViewer() {
        modal.className = 'modal modal-out'; // Whitespace!
        setTimeout(function () {
            img.src = '';
        }, 1000);

    }
}


var didScroll = false;
/*http://joshbroton.com/hooking-up-to-the-window-onscroll-event-without-killing-your-performance/
 *
 */
function pendelOnScrolled() {
    didScroll = true;
}

setInterval(function () {
    if (didScroll) {
        didScroll = false;
//        console.log('You scrolled');
        alert(document.getElementById("pendelNr").textContent);
        switchCanvas(document.getElementById("msg").textContent - 1);
    }
}, 100);

/**
 * Call backend and get tiles for next canvas
 * @param {Number} actualNr
 * @param {Number} canvasNr max.
 * @param {String} direction [up|down]
 * @returns {undefined}
 */
function switchCanvas(actualNr, canvasNr, direction) {
    var msg = document.getElementById("msg");
    var newNr = 0;

    // Check Precondition
    if (direction == 'down') {
        if (actualNr <= 1) {
            msg.innerHTML = "geht nicht: actualNr=" + actualNr + " direction=" + direction;
            return;
        }
        newNr = actualNr - 1;
    } else if (direction == 'up') {
        if (actualNr >= canvasNr) {
            msg.innerHTML = "geht nicht: actualNr=" + actualNr + " direction=" + direction;
            return;
        }
        newNr = actualNr + 1;
    } else {
        msg.innerHTML = "Unknonwn direction=" + direction;
        return;
    }

    msg.innerHTML = "geht: actualNr=" + actualNr + " newNr=" + newNr + " direction=" + direction;

    document.getElementById("pendelActualNr").textContent = newNr;

    msg.innerHTML = "Sende actualNr=" + actualNr + " newNr=" + newNr + " um " + +new Date();
    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            msg.innerHTML = this.responseText;
        }
    };
    xmlhttp.open("GET", "get_tiles.php?nextNr=" + nr, true);
    xmlhttp.send();
}
