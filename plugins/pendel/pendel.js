var mouseIsOver = false;

jQuery(document).ready(function () {
    initDivMouseOver();
});

function initDivMouseOver() {
//    alert('initDivMouseOver ' + jQuery("#pendelActualNr").text());
    var div = jQuery("#svg-section");

    mouseIsOver = false;
    div.onmouseover = function () {
        mouseIsOver = true;
//        alert('onmouseover');
    };
    div.onmouseout = function () {
        mouseIsOver = false;
//        alert('onmouseout');
    };
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
    };

// When the user clicks anywhere outside of the modal, close it
    window.onclick = function (event) {
        if (event.target === modal) {
            closeViewer();
        }
    };
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
//    var msg = document.getElementById("msg");
    var msg = jQuery("#msg");
    var newNr = 0;

    // Check Precondition
    if (direction === 'down') {
        if (actualNr <= 1) {
            msg.html("geht nicht: actualNr=" + actualNr + " direction=" + direction);
            return;
        }
        newNr = actualNr - 1;
    } else if (direction === 'up') {
        if (actualNr >= canvasNr) {
            msg.html("geht nicht: actualNr=" + actualNr + " direction=" + direction);
            return;
        }
        newNr = actualNr + 1;
    } else {
        msg.html("Unknonwn direction=" + direction);
        return;
    }

    msg.html("geht: actualNr=" + actualNr + " newNr=" + newNr + " direction=" + direction);

    jQuery("#pendelActualNr").text(newNr);

    msg.html("Sende actualNr=" + actualNr + " newNr=" + newNr + " an " + pendel_vars.ajaxurl + " um " + +new Date());

    var data = {
        action: 'pendel_paging',
        actualNr: actualNr,
        nextNr: newNr
    };

// URL, type, data
    jQuery.post(pendel_vars.ajaxurl, data, function (response) {
        var res = jQuery.parseJSON(response);
//        alert('Got this from the server: actualNr=' + res.actualNr +
//                ", nextNr=" + res.nextNr);
        jQuery(msg.html('Got this from the server: actualNr=' + res.actualNr +
                ", nextNr=" + res.nextNr));

    });


}
