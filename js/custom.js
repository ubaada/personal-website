
document.addEventListener('DOMContentLoaded', function() {
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
	
	//email-icon on click event
	//var em_sel = document.querySelector('#email-icon');
    //em_sel.addEventListener('click', emailShow);
	
	//hide-email form
	//var canc_em_sel = document.querySelector('#cancel-email-btn');
    //canc_em_sel.addEventListener('click', emailHide);
	
}, false);


window.addEventListener('hashchange', function() {
    checkHiddenAnchors();
}, false);

window.addEventListener("resize", function() {
  adjust_image();
  setAboutTextInset();
});

// When phone is flipped into a different orientation
window.addEventListener("deviceorientation", function() {
  adjust_image();
  setAboutTextInset();
});
function adjust_image() {
	var im_elem = document.getElementById("my-picture");
	var im_cont = document.getElementById("about-img-side");
	//----------------wrapper for unstretched size-\/
    var text_elem = document.getElementById("about-text-side"); 
	
	var im_cont_width = im_cont.offsetWidth;
	var text_height = text_elem.offsetHeight;
	// to calculate img height if its set to width 100%
	var im_ratio = im_elem.offsetWidth / im_elem.offsetHeight;
	var h_if_full_width = im_cont_width / im_ratio;
	
    // check if page is in desktop size or mobile size. 
    // In mobile size screen the sides are stacked so same width
    if (window.matchMedia("(min-width: 600px)").matches) {
		// 2 image expanding scenarios:
		//	i. Width: 100%, height: auto (when about image is longer, default)
		//	ii.Width: auto, height: abouttxt height (when abttxt is longer, here)
		if (h_if_full_width > text_height) {
			// revert to default (to fill the width)
			im_elem.setAttribute("style", "width:100%;height:auto");
			
			// console.log("width:100% fh:" + h_if_full_width + " th:" + text_height + " ratio:" + im_ratio);
		} else {
			//adjust image height to match text box
            im_elem.setAttribute("style", "width:auto;height:" + parseInt(text_height) + "px");
			
			// console.log("height:match fh:" + h_if_full_width + " th:" + text_height + " ratio:" + im_ratio);
		}
	} else {
        // mobile mode. no need to adjust image height
        im_elem.setAttribute("style", "");
	}	
		
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

    // Only calculate in non-mobile device.
	// Otherwise the inset is undefined.
    if (window.matchMedia("(min-width: 600px)").matches) {
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
}


function emailShow() {
	var er_sel = document.querySelector('#email-panel');
	er_sel.classList.toggle('hidden');
	er_sel.classList.toggle('show');
}

function emailHide() {
	
	var er_sel = document.querySelector('#email-panel');
	er_sel.classList.toggle('hidden');
}
