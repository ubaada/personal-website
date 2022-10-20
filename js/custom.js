
document.addEventListener('DOMContentLoaded', function() {
    M.AutoInit();
    adjust_image();
    checkHiddenAchors();
}, false);


window.addEventListener('hashchange', function() {
    checkHiddenAchors();
}, false);

window.onresize = adjust_image;



function adjust_image() {
    var im_elem = document.getElementById("my-picture");
    var text_elem = document.getElementById("about-text-side");
    var picpos_elem = document.getElementById("pic-pos");

    // check if page is in desktop size or mobile size. 
    // In mobile size screen the sides are stacked so same width
    if (im_elem.offsetWidth != text_elem.offsetWidth) {
        // Desktop mode.
        // check if gap is showing below image
        if (im_elem.offsetHeight < text_elem.offsetHeight) {
            //adjust image height
            im_elem.setAttribute("style", "width:auto;height:" + text_elem.offsetHeight + "px");
        }
    } else {
        // mobile mode. no need to adjust image height
        picpos_elem.outerText = "top";
    }
}


function checkHiddenAchors() {
    let id = window.location.hash;
    const popup = document.querySelector('#details-pop-up');
    if (id !== "" && popup.querySelector(id) !== null) { //check if anchor is child of hidden popup
        // .. it's a child
        showPopup(id);
    }
}




function showPopup(id) {
    const popup = document.querySelector('#details-pop-up');
    const contents = document.querySelector("#details-popup-contents");

    popup.classList.remove('hidden');
    document.getElementsByTagName("body")[0].style.overflow = "hidden";

    contents.style.animation = "popup-contents-in  normal .4s forwards"
    popup.style.animation = "popup-in .4s forwards";
    popup.querySelector(id).scrollIntoView();
}
function hidePopup(){
    const popup = document.querySelector('#details-pop-up');
    const contents = document.querySelector("#details-popup-contents");

    document.getElementsByTagName("body")[0].style.overflow = "visible";

    contents.style.animation = "popup-contents-out .5s forwards";
    popup.style.animation = "popup-out .5s forwards";
    
}