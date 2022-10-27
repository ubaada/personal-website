
document.addEventListener('DOMContentLoaded', function() {
    M.AutoInit();
    adjust_image();
    checkHiddenAnchors();

    var ld_sel = document.querySelector('#lightdark-checkbox');
    ld_sel.addEventListener('change', colorCheckboxPressed);


    // Load color preference from last time or
    // Use matchMedia to check the system wide preference for light/dark mode
    startupColorPreference();

	// set how much about text div moves on Z axis to match the bevel
	// And Add animation
	setAboutTextInset();
}, false);


window.addEventListener('hashchange', function() {
    checkHiddenAnchors();
}, false);

window.onresize = adjust_image;

function adjust_image() {
    var im_elem = document.getElementById("my-picture");
    var text_elem = document.getElementById("about-text-side");

    // check if page is in desktop size or mobile size. 
    // In mobile size screen the sides are stacked so same width
    if (window.matchMedia("(min-width: 600px)").matches) {
        // Desktop mode.
        // check if gap is showing below image
        if (im_elem.offsetHeight < text_elem.offsetHeight) {
            //adjust image height
            im_elem.setAttribute("style", "width:auto;height:" + text_elem.offsetHeight + "px");
        }
		//recalc aboutext inset
		setAboutTextInset();
    } else {
        // mobile mode. no need to adjust image height
        im_elem.setAttribute("style", "");
    }
	console.log("______");
}


function checkHiddenAnchors() {
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


function colorCheckboxPressed(event) {
      console.log(this.checked);
      localStorage.setItem("darkMode", this.checked);
      startupColorPreference();
}

// Load color preference from last time if saved else
// Use matchMedia to check the system wide preference for light/dark mode
function startupColorPreference() {
    
    isManualDarkModeSet = (localStorage.getItem("darkMode") !== null);
    if (isManualDarkModeSet) {
        // if dark mode preference manually set by user use that
        var prefersDark = localStorage.getItem("darkMode");
        changeColorMode(prefersDark === 'true'); //value is stored as string in localStorage
    } else {
        // Use system of preference
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)');
        changeColorMode(prefersDark)
    }
}

function changeColorMode(isDark) {
    document.body.classList.toggle('dark', isDark);
    document.querySelectorAll('#sm-bar a').forEach((smitem) => {
      smitem.classList.toggle('dark', isDark);
    });
        
    document.querySelector('#lightdark-checkbox').checked = isDark;
}

// How much about text div moves on Z axis to match the bevel
// And Add animation
function setAboutTextInset() {
	var body = document.querySelector('body');
	var bevelElem = document.getElementById("bevel-strip");
	var aboutTextElem = document.getElementById("about-text-side");
	
	var bevelElemComp = getComputedStyle(bevelElem);
	var bevelWidth = parseFloat(bevelElemComp.width);
	var bevelAngle = parseFloat(bevelElemComp.getPropertyValue('--bevel-rotation'));
	
	var calcInset = bevelWidth * Math.cos(bevelAngle);
	
	body.style.setProperty('--about-text-inset', calcInset + 'px');
	
	//Begin animation
	bevelElem.classList.add("anim");
	aboutTextElem.classList.add("anim");
	
}

