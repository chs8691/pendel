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
//        alert('1');
    }
}, 100);