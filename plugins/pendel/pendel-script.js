//For tiles
var pendelMouseIsOver = false;
// For page scroling by wheel
var pendelWheelCount = 0;
// true, if slider is in sliding mode
var pendelIsSliding = false;
// used while sliding with pageY position in px
var pendelSlidingPos;
// Range in px that can be used for scrolling, depends on KnobHeight
var pendelSlidingLength;
// max number of pages to be scrolled
var pendelMaxPages;
// Size of a paging step in y pixel
var pendelPageStepSizePx;
// Available height
var pendelContentHeight

// create function that applies width and height to slider
// that equals dimensions of window,
//
// feel free to modify values in case you need to reduce height of slider by height of the menu or sidebar
var resize = function () {
//    console.log("resize height=" + jQuery(window).height());

    jQuery('#pendel-content-box').css({
        width: jQuery(window).width(),
        height: jQuery(window).height()
    });
    pendelInitialize();
};
function pendelInitialize() {


    pendelContentHeight = window.innerHeight;
//    console.log("contentHeight=" + pendelContentHeight);
    jQuery("#pendel-v-slider").height(pendelContentHeight);
    pendelWidthScaling();
    pendelAdjustKnobHeight();
    pendelRefreshSlider();
}
/**
 * Returns true, if content is a pendel content. Use this for every global
 * access.
 * @returns {boolean} true, if pendel page
 */
function pendelIsPendel() {

    return !(jQuery("#pendel-content").length == 0);
}

/**
 * Lazy load of all thumb images.
 * @returns {undefined}
 */
function loadThumbs() {
//    console.log("loadThumbs");

    var max = 0;
    var count = 0;
    var progressBar = jQuery("#pendel-progress-bar");
    var infoLine = jQuery("#pendel-info-line");
    var msgLine = jQuery("#pendel-message-line");
    var msg = jQuery("#pendel-msg");
    msg.show();

    infoLine.hide();

    [].forEach.call(document.querySelectorAll('image[datahref]'), function (img) {
        max = max + 1;
    });
    msg.html("Lade " + count + " von " + max + "...");

    [].forEach.call(document.querySelectorAll('image[datahref]'), function (img) {
//        console.log("image=" + img.getAttribute("datahref"));

        img.addEventListener("load", function () {
//            console.log("item=" + img.getAttribute('href'));
            img.removeAttribute('datahref');
            count = count + 1;

            // Progress bar, fade out after loading the last tile
            if (count < max)
            {
                progressBar.width(msgLine.outerWidth() * count / max);
                progressBar.attr("class", "pendel-progress-bar-active");
                msg.html("Lade " + count + " von " + max + "...");

            } else {
                progressBar.width(msgLine.outerWidth());
                progressBar.attr("class", "pendel-progress-bar-done");
                msg.html("");
                msgLine.hide();
                infoLine.show();
//                infoLine.height(infoLine.height() + 5);
//                progressBar.height(1);
            }

        });
        img.setAttribute('href', img.getAttribute('datahref'));

    });
}

jQuery(document).ready(function () {
    if (!pendelIsPendel())
        return;
// Map hidden page title to subheader field
    jQuery("#pendel-page-title").html(jQuery(".entry-title").text());
    loadThumbs();
    // trigger function on each page resize
    jQuery(window).on('resize', resize);
    resize();
//    console.log("jQuery.ready()");

    // Remove browser slider


    pendelInitDivMouseOver();
    pendelInitSwipeVertical();
    //    consol.log("ready() init mouse.")
    jQuery(document).mousemove(function (event) {
        if (pendelIsSliding) {
            var py = event.pageY;
            var pos = py - pendelSlidingPos;
//            console.log("slidingPos=" + slidingPos + ", move y=" + event.pageY + ", newPos=" + pos);
            pendelMoveKnob(pos);
            pendelSlidingPos = py;
            //
            if (event.stopPropagation)
                event.stopPropagation();
            if (event.preventDefault)
                event.preventDefault();
        }
    }).mouseup(function () {
        if (!pendelIsSliding) {
            return;
        }
//        console.log("up");
        var wasDragging = pendelIsSliding;
        pendelIsSliding = false;
        if (!wasDragging) {
//            console.log("up");
        }
    });
    jQuery("#pendel-v-slider-knob")
            .mousedown(function (event) {
                pendelIsSliding = true;
                pendelSlidingPos = event.pageY;
//                console.log("down at y=" + event.pageY);
            });
});
function pendelMoveKnob(delta) {
//    console.log("moveKnob");
    var padding = parseInt(jQuery("#pendel-v-slider").css("paddingTop"));
    var offset = padding + delta;
//    console.log("moveKnob offset=" + offset);
    pendelUpdateSliderPos(offset);
    var actualPage = parseInt(jQuery("#pendel-actual-nr").text());
    newValue = pendelMaxPages - Math.round(offset / pendelPageStepSizePx);
//    console.log("newValue=" + newValue);
    pendelChangeActualPos(actualPage, newValue);
    if (newValue < 0)
        pendelSwitchPage("up");
    else
        pendelSwitchPage("down");
}

/*
 * To be called, everytime the browser window is rescaled.
 * Slider should be independent of browser scaling
 * @returns {undefined}
 */
function pendelWidthScaling() {
    var width = 10;
    jQuery("#pendel-v-slider").width(width / window.devicePixelRatio);
    jQuery("#pendel-v-slider-knob").width((width) / window.devicePixelRatio);
}

/**
 * pendelActualNr must be set before.
 * @returns {undefined}
 */
function pendelRefreshSlider() {
//    console.log("refreshSlider");


    var actualPage = jQuery("#pendel-actual-nr").text();
    // Nothing to scroll
    if (pendelMaxPages <= 1) {
        jQuery("#pendel-v-slider").hide();
        jQuery("#pendel-v-slider-knob").hide();
        return;
    }
//    console.log("slidingLength=" + pendelSlidingLength + " maxPages=" + pendelMaxPages + ", actualPage=" + actualPage);
    ;
    var offset = (pendelMaxPages - actualPage - 1) * pendelPageStepSizePx;
    pendelUpdateSliderPos(offset);
}

function pendelAdjustKnobHeight() {
    var hSlider = jQuery("#pendel-v-slider").height();
    console.log("hSlider=" + hSlider);
    console.log("vSlider width=" + jQuery("#pendel-v-slider").width());
    pendelMaxPages = jQuery("#pendel-nr").text();
    var length = pendelGetSlideLength(hSlider, pendelMaxPages);
    jQuery("#pendel-v-slider-knob").outerHeight(hSlider - length);
    pendelSlidingLength = pendelGetSlideLength(hSlider, pendelMaxPages);
    pendelPageStepSizePx = Math.round(pendelSlidingLength / (pendelMaxPages - 1));
//    console.log("hSlider=" + hSlider);
//    console.log("length=" + length);
//    console.log("slidingLength=" + slidingLength);
}
/**
 * Pages : values
 * 2        10 %
 * 3        20 %
 * 10       80 %
 * 100      95 %
 * 1000     98 %
 * @param {type} scrollPx pixel lenght
 * @param {type} pages values between [2..initity]
 * @returns {unresolved}
 */
function pendelGetSlideLength(scrollPx, pages) {
//    var f = Math.cos((1 / act) * (Math.PI / 2.0));
    var f = 1 - (1 / Math.log10(pages + 10));
    var x = f * scrollPx;
//    console.log("Len=" + Math.round(x));

    return Math.round(x);
}
function pendelChangeActualPos(oldValue, newValue) {
    var newAct = newValue;
    if (newValue > pendelMaxPages)
        newAct = pendelMaxPages;
    else if (newValue < 0)
        newAct = 0;
    if (oldValue == newAct)
        return;
//    console.log("newAct=" + newAct);
    jQuery("#pendel-actual-nr").text(newAct);
}
function pendelUpdateSliderPos(offset) {

//    console.log("updateSliderPos() offset=" + offset);
    var newOffset;
    // Prevent from expand slider
    if (offset < 0)
        newOffset = 0;
    else
        newOffset = Math.min(offset, pendelSlidingLength);
//    console.log("newOffset=" + newOffset);
    jQuery("#pendel-v-slider").css("paddingTop", newOffset);
}

function pendelInitDivMouseOver() {
//    alert('initDivMouseOver ' + jQuery("#pendelActualNr").text());
//
    var div = jQuery("#pendel-canvas");
    pendelMouseIsOver = false;
    div.onmouseover = function () {
        pendelMouseIsOver = true;
//        alert('onmouseover');
    };
    div.onmouseout = function () {
        pendelMouseIsOver = false;
//        alert('onmouseout');
    };
//    // disabel scrolling of mobile browser
//    window.addEventListener('touchmove', function (event) {
//        event.preventDefault();
//        return false;
//    }, false);

    window.addEventListener('wheel', function (e) {
        var direction = '';
        console.log(e.type);
        e.preventDefault();
        if (e.type == 'mousewheel') {
            if (e.originalEvent.wheelDelta > 0) {
                pendelWheelCount++;
            } else {
                pendelWheelCount--;
            }
        } else if (e.type == 'DOMMouseScroll') {
            if (e.detail > 0) {
                pendelWheelCount++;
            } else {
                pendelWheelCount--;
            }
        } else if (e.type == 'wheel') {
            if (e.deltaY < 0) {
                pendelWheelCount++;
            } else {
                pendelWheelCount--;
            }
        }

//       Reduce wheel threshold by incrementing both integer value
        if (pendelWheelCount >= 1) {
            direction = 'up';
        } else if (pendelWheelCount <= -1) {
            direction = 'down';
        }
        if (!(direction == '')) {
            pendelWheelCount = 0;
            pendelSwitchPage(direction);
            pendelRefreshSlider();
        }
    }, false);
}

// Y start pixel for swiping
var touchStart;
function pendelInitSwipeVertical() {

    var len = 50;
    window.addEventListener('touchstart', function (event) {
        if (event.targetTouches.length == 1) {
            var touch = event.targetTouches[0];
            touchStart = touch.pageY;
            console.log("touchStart=" + touchStart);
        }
    }, false);
    window.addEventListener('touchmove', function (event) {
        // If there's exactly one finger inside this element

        if (event.targetTouches.length == 1) {
            var touch = event.targetTouches[0];
//            console.log(touch.pageY);
            if ((touchStart - touch.pageY) >= len) {
//                console.log("down");
                touchStart = touch.pageY;
                pendelSwitchPage('down');
                pendelRefreshSlider();
            } else if ((touch.pageY - touchStart) >= len) {
//                console.log("up");
                touchStart = touch.pageY;
                pendelSwitchPage('up');
                pendelRefreshSlider();
            }
        }
    }, false);
}


/*
 * Show cliecked image in a viewer
 */
function pendelOnTileClicked(imageSrc, title, description, lat, lon) {

//    var modal = document.getElementById('pendelModal');
    var span = document.getElementById("pendel-close");
    var modal = document.getElementById('pendel-modal');
    var content = document.getElementById('pendel-modal-content');
    var img = document.getElementById('pendel-modal-image');
    document.getElementById("pendel-viewer-title").textContent = title + ' / ' + description;
    document.getElementById("pendel-viewer-subtitle").innerHTML = lat + ", " + lon +
            " <a href=\"http://www.openstreetmap.org/?mlat=" + lat + "&mlon=" + lon + "\" target = \"_blank\" >osm</a>";
    // Set the image to show
    img.src = imageSrc;
    // Prevent for showing previous image next time
//    setTimeout(function () {
    modal.className = 'pendel-modal pendel-modal-in'; // Whitespace!

//    }, 1);

//    content.className = 'pendel-modal-content pendel-modal-content-in';


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
        modal.className = 'pendel-modal pendel-modal-out'; // Whitespace!
        setTimeout(function () {
            img.src = '';
        }, 1000);
    }
}

/**
 * Updates the visibility of every tile in depenency of the actual page nr.
 * Precondition: PageNr == tileNr
 * @param {String} direction [up|down]
 * @returns {undefined}
 */
function pendelSwitchPage(direction) {
//    console.log("switchPage()");
    var msg = jQuery("#pendel-msg");
    jQuery("#pendel-id").hide();
    // Only for debugging
//    msg.hide();
    var actualNr = parseInt(jQuery("#pendel-actual-nr").text());
    var canvasNr = parseInt(jQuery("#pendel-nr").text());
    var pendelId = jQuery("#pendel-id").text();
    //
    var nextNr = 0;
    // Check Precondition
    if (direction === 'down') {
        if (actualNr < 1) {
//            msg.html("geht nicht: actualNr=" + actualNr + " direction=" + direction);
            return;
        }
        nextNr = actualNr - 1;
    } else if (direction === 'up') {
        if (actualNr >= canvasNr) {
//            msg.html("geht nicht: actualNr=" + actualNr + " direction=" + direction);
            return;
        }
        nextNr = actualNr + 1;
    } else {
//        msg.html("Unknonwn direction=" + direction);
        return;
    }

    jQuery("#pendel-actual-nr").text(nextNr);
    // For all actual visible elements
    jQuery('.pendel-svg-tile').each(function (i, obj) {
        var elm = jQuery(obj);
        if (idFromImageId(elm.attr('id')) > nextNr) {
            elm.attr("class", "pendel-svg-tile-hidden");
        }
    });
    // for all actual hidden elements
    jQuery('.pendel-svg-tile-hidden').each(function (i, obj) {
        var elm = jQuery(obj);
        if (idFromImageId(elm.attr('id')) <= nextNr) {
            elm.attr("class", "pendel-svg-tile");
        }
    });
}
/**
 * Backwards function for the corresponding php function toImageId
 * @param String $idString
 * @return Integer
 */
function idFromImageId(idString) {
    return idString.match(/\d+/)[0];
}
/**
 * Call backend and get tiles for next canvas
 * @param {String} direction [up|down]
 * @returns {undefined}
 */
function pendelSwitchPageOld(direction) {
//    console.log("switchPage()");
    var msg = jQuery("#pendel-msg");
    jQuery("#pendel-id").hide();
    // Only for debugging
    msg.hide();
    var actualNr = parseInt(jQuery("#pendel-actual-nr").text());
    var canvasNr = parseInt(jQuery("#pendel-nr").text());
    var pendelId = jQuery("#pendel-id").text();
    //
    var nextNr = 0;
    // Check Precondition
    if (direction === 'down') {
        if (actualNr < 1) {
            msg.html("geht nicht: actualNr=" + actualNr + " direction=" + direction);
            return;
        }
        nextNr = actualNr - 1;
    } else if (direction === 'up') {
        if (actualNr >= canvasNr) {
            msg.html("geht nicht: actualNr=" + actualNr + " direction=" + direction);
            return;
        }
        nextNr = actualNr + 1;
    } else {
        msg.html("Unknonwn direction=" + direction);
        return;
    }

    msg.html("geht: actualNr=" + actualNr + " newNr=" + nextNr + " direction=" + direction);
    jQuery("#pendel-actual-nr").text(nextNr);
    msg.html("Sende actualNr=" + actualNr + " newNr=" + nextNr + " an " +
            pendel_vars.ajaxurl + " pendelId=" + pendelId + " um " + +new Date());
    var data = {
        'action': 'pendel_paging',
        'pendelId': pendelId,
        'nextNr': nextNr
    };
//    console.log("supress ajax call");
//    return;

    // URL, type, data
    jQuery.post(pendel_vars.ajaxurl, data, function (response) {
        var res = jQuery.parseJSON(response);
//        attribute = "visibility";
        jQuery(msg.html("Response received"));
//        alert('Got this from the server: actualNr=' + res.actualNr +
//                ", nextNr=" + res.nextNr);
        jQuery.each(res, function (index, element) {
            // expecting image name and visability boolean
            // e.g. img123.jpg="true"
            var values = element.split("=");
//            console.log("Response index=" + index + " value=" + values[0]);
            id = "#" + index;
            if (values[0] === "true") {
//                visibility = "visible";
                newclass = "pendel-svg-tile";
            } else {
//                visibility = "hidden";
                newclass = "pendel-svg-tile-hidden";
            }

            jQuery(id).attr("class", newclass);
        });
    });
}
