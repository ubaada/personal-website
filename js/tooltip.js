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
  element.addEventListener('mouseenter', event => {
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
    tooltipDiv.style.top = (event.clientY + 15) + 'px';
    tooltipDiv.style.left = (event.clientX) + 'px';
  });
  
  // Destroy all tooltips when cursor leaves this element
  element.addEventListener('mouseleave', event => {
    const tooltipDiv = document.querySelector('.tooltip');
    if (tooltipDiv) {
      tooltipDiv.remove();
    }
  });
});

