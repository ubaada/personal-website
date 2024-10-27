// ===================================================
//             Script for light/dark mode
// ===================================================

// 1. Attach event listener to the color mode button
// 2. Load last time color preference or system preference
document.addEventListener('DOMContentLoaded', function() {
	// When color mode button pressed, store color preference
	// then load color preferenxe
    var ld_sel = document.querySelector('#lightdark-checkbox');
    ld_sel.addEventListener('change', colorCheckboxPressed);

    // Load color preference from last time or
    // Use matchMedia to check the system wide preference for light/dark mode
    startupColorPreference();
}, false);

// 1.
function colorCheckboxPressed(event) {
      localStorage.setItem("darkMode", this.checked);
      startupColorPreference();
}

// 2.
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
    // Invert color of images with data label "data-default-color"
    invertImageColors(isDark);
    // Update checkbox
    document.querySelector('#lightdark-checkbox').checked = isDark;
}

// Invert color of images with data label "data-default-color"
// data-default-color = [light|dark]
function invertImageColors(isDark) {
    // Get all images with the data label "data-default-color"
    var images = document.querySelectorAll('img[data-default-color]');
  
    // Iterate over all the images
    for (var i = 0; i < images.length; i++) {
      // Get the data label value for the current image
      var dataLabelValue = images[i].dataset.defaultColor;
  
      // If the data label value is "light" and isDark is true, invert the colors of the image
      if (dataLabelValue === 'light' && isDark) {
        let inversion = 1 - calc_luma(window.getComputedStyle(document.body).backgroundColor);
        images[i].style.filter = 'invert(' + inversion + ')';
      }
  
      // If the data label value is "dark" and isDark is true, leave the color of the image as is
      if (dataLabelValue === 'dark' && isDark) {
        images[i].style.filter = '';
      }
  
      // If the data label value is "light" and isDark is false, leave the color of the image as is
      if (dataLabelValue === 'light' && !isDark) {
        images[i].style.filter = '';
      }
  
      // If the data label value is "dark" and isDark is false, invert the colors of the image
      if (dataLabelValue === 'dark' && !isDark) {
        let inversion = 1 - calc_luma(window.getComputedStyle(document.body).backgroundColor);
        images[i].style.filter = 'invert(' + inversion + ')';
      }
    }
  }

  
  // Compute luma to match the inverted white and black colors
  function calc_luma(color) {
    let r, g, b;

    // rgb(r, g, b) format
    if (color.startsWith('rgb')) {
      let start = color.indexOf('(') + 1;
      let end = color.indexOf(')');
      let rgb = color.substring(start, end).split(',');
      r = parseInt(rgb[0]);
      g = parseInt(rgb[1]);
      b = parseInt(rgb[2]);
    }

    r /= 255;
    g /= 255;
    b /= 255;
  
    const luma = 0.299 * r + 0.587 * g + 0.114 * b;
  
    return luma;
  }
  