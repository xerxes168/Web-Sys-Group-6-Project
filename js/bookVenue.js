// Add this JavaScript code to the bottom of your book_venue.php file
// or to a separate JS file that's included in the page
document.addEventListener('DOMContentLoaded', function() {
    // Get the date input field
    const dateInput = document.getElementById('event_date');
    
    // Make the entire input field clickable by triggering a click on the calendar icon
    dateInput.addEventListener('click', function(e) {
        // This simulates clicking on the calendar icon
        e.preventDefault();
        dateInput.showPicker();
    });
    
    // Set minimum date to today
    const today = new Date().toISOString().split('T')[0];
    dateInput.min = today;
});