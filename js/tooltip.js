// =====================================
//        Shows Tooltip
// =====================================
// Queries all the elements with "data-tooltip" attribute. 
// For each element assign a on-hover function. When the element is hovered 
// the browser  instantiates a div on the location of the cursor with the
// text containing the value of "data-tooltip".

const elementsWithTooltip = document.querySelectorAll('[data-tooltip]');

// Insert hover event on element containing the data-tooltip attr
elementsWithTooltip.forEach(element => {
  // Show tooltip when cursor hovers over this element
  element.addEventListener('mouseenter', showTooltip);

  // Destroy all tooltips when cursor leaves this element
  element.addEventListener('mouseleave', closeTooltip);

});

/**
 * Tooltip hover
 */
function showTooltip(event) {
    // Tooltip container
    const tooltipDiv = document.createElement('div');
    tooltipDiv.classList.add('tooltip');
    tooltipDiv.style.position = "fixed";

    // Tooltip text, revealed via animation
    const blinker_text = document.createElement('div');
    blinker_text.classList.add('text');
    const tooltipText = event.target.getAttribute('data-tooltip');
    blinker_text.textContent = "> " + tooltipText;

    // Add txt to container, and container to the document
    tooltipDiv.appendChild(blinker_text);
    document.body.appendChild(tooltipDiv);

    // Set tooltip location to cursor's 
    tooltipLocation = calculateTooltipLocation(event, tooltipDiv);
    tooltipDiv.style.left = tooltipLocation.x + 'px';
    tooltipDiv.style.top = tooltipLocation.y + 'px';
}
/*
 * Calculates the location of the tooltip 
 */
function calculateTooltipLocation(event, tooltipDiv) {
  const tooltipWidth = tooltipDiv.offsetWidth;
  const tooltipHeight = tooltipDiv.offsetHeight;
  const buttonRect = event.target.getBoundingClientRect();
  const cursorHeight = 15;
  const paddingY = 3;
  const paddingX = 10; // dealing with the width of the scrollbar
  const windowWidth = window.innerWidth;

  // ideal postion: 
  // Y: on top of button
  // X: middle of tooltip aligns with middle of button
  var tooltipY = buttonRect.y - tooltipHeight - paddingY;
  if (tooltipY < 0) {
    // if tooltip is outside the top of the page
    // change tooltip Y to show it on the bottom side
    tooltipY = event.clientY + cursorHeight + paddingY;
  }
  var tooltipX = buttonRect.x + buttonRect.width / 2 - tooltipWidth / 2
  if (tooltipX < 0) {
    // left side of screen is hiding it.
    // pin X to left most side of the screen.
    tooltipX = 0;
  } else if (tooltipX + tooltipWidth > windowWidth) {
    // right side overflowing into right of screen
    // pin X to right most side of the screen
    tooltipX = windowWidth - tooltipWidth - paddingX;
    console.log(tooltipX);
  }
  // returns X and Y of the tooltip
  return {x: tooltipX, y: tooltipY}
}

/**
 * Close tooltip
 */
function closeTooltip() {
    const tooltipDiv = document.querySelector('.tooltip');
    if (tooltipDiv) {
      tooltipDiv.remove();
    }
}