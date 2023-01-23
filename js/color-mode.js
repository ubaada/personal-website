// Script for light/dark mode
document.addEventListener('DOMContentLoaded', function() {
	// When color mode button pressed, store color preference
	// then load color preferenxe
    var ld_sel = document.querySelector('#lightdark-checkbox');
    ld_sel.addEventListener('change', colorCheckboxPressed);

    // Load color preference from last time or
    // Use matchMedia to check the system wide preference for light/dark mode
    startupColorPreference();
}, false);


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