document.addEventListener('DOMContentLoaded', function() {
    // Get all dropdown elements
    const dropdowns = document.querySelectorAll('.dropdown');
    
    // Add event listeners to each dropdown
    dropdowns.forEach(dropdown => {
        const menu = dropdown.querySelector('.dropdown-menu');
        let timeoutId;
        
        // Show dropdown on mouseover
        dropdown.addEventListener('mouseover', function() {
            clearTimeout(timeoutId);
            menu.style.display = 'block';
        });
        
        // Hide dropdown after a delay when mouse leaves
        dropdown.addEventListener('mouseleave', function() {
            timeoutId = setTimeout(function() {
                menu.style.display = 'none';
            }, 300); // 300ms delay gives user time to move to the dropdown
        });
        
        // Keep dropdown open when hovering over the menu itself
        menu.addEventListener('mouseover', function() {
            clearTimeout(timeoutId);
        });
        
        // Hide dropdown after delay when leaving the menu
        menu.addEventListener('mouseleave', function() {
            timeoutId = setTimeout(function() {
                menu.style.display = 'none';
            }, 300);
        });
    });
});