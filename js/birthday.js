document.addEventListener('DOMContentLoaded', function() {
    // DOM Elements
    const venuesContainer = document.getElementById('venues-container');
    const venueSearch = document.getElementById('venue-search');
    const capacityFilter = document.getElementById('capacity-filter');
    const dateFilter = document.getElementById('date-filter');
    const priceFilter = document.getElementById('price-filter');
    const moreFiltersBtn = document.getElementById('more-filters-btn');
    const moreFiltersPanel = document.getElementById('more-filters-panel');
    const applyFiltersBtn = document.getElementById('apply-filters-btn');
    const clearFiltersBtn = document.getElementById('clear-filters-btn');
    const venueCount = document.getElementById('venue-count');
    
    // Set today's date as the default for date picker
    const today = new Date();
    const formattedDate = today.toISOString().split('T')[0];
    dateFilter.value = formattedDate;
    
    // Toggle more filters panel
    moreFiltersBtn.addEventListener('click', function() {
        moreFiltersPanel.classList.toggle('show');
    });
    
    // Get all venues
    const venueCards = document.querySelectorAll('.venue-card');
    updateVenueCount(venueCards.length);
    
    // Apply filters function
    function applyFilters() {
        const searchTerm = venueSearch.value.toLowerCase();
        const capacityValue = capacityFilter.value ? parseInt(capacityFilter.value) : 0;
        const priceRange = priceFilter.value;
        
        // Get checked values from more filters
        const selectedTypes = getCheckedValues('filter-group:nth-child(1) input[type="checkbox"]');
        const selectedAmenities = getCheckedValues('filter-group:nth-child(2) input[type="checkbox"]');
        const selectedLocations = getCheckedValues('filter-group:nth-child(3) input[type="checkbox"]');
        
        let visibleCount = 0;
        
        // Loop through all venue cards and filter
        venueCards.forEach(card => {
            const venueTitle = card.querySelector('h3').textContent.toLowerCase();
            const venueLocation = card.querySelector('.venue-location').textContent.toLowerCase();
            const venuePrice = parseInt(card.dataset.price);
            const venueCapacity = parseInt(card.dataset.capacity);
            const venueType = card.dataset.type;
            const venueArea = card.dataset.area;
            
            // Check if venue matches search term
            const matchesSearch = searchTerm === '' || 
                                 venueTitle.includes(searchTerm) || 
                                 venueLocation.includes(searchTerm);
            
            // Check if venue matches capacity filter
            const matchesCapacity = capacityValue === 0 || venueCapacity >= capacityValue;
            
            // Check if venue matches price filter
            let matchesPrice = true;
            if (priceRange) {
                const [min, max] = priceRange.split('-');
                if (max === '+') {
                    matchesPrice = venuePrice >= parseInt(min);
                } else {
                    matchesPrice = venuePrice >= parseInt(min) && venuePrice <= parseInt(max);
                }
            }
            
            // Check if venue matches type filters
            const matchesType = selectedTypes.length === 0 || selectedTypes.includes(venueType);
            
            // Check if venue matches location filters
            const matchesLocation = selectedLocations.length === 0 || selectedLocations.includes(venueArea);
            
            // Determine if the venue should be visible
            const isVisible = matchesSearch && matchesCapacity && matchesPrice && matchesType && matchesLocation;
            
            // Show or hide the venue card
            card.style.display = isVisible ? 'block' : 'none';
            
            // Count visible venues
            if (isVisible) visibleCount++;
        });
        
        updateVenueCount(visibleCount);
        
        // Close the more filters panel
        moreFiltersPanel.classList.remove('show');
    }
    
    // Helper function to get checked values from checkboxes
    function getCheckedValues(selector) {
        const checkedBoxes = document.querySelectorAll(selector + ':checked');
        return Array.from(checkedBoxes).map(box => box.value);
    }
    
    // Update the venue count display
    function updateVenueCount(count) {
        venueCount.textContent = count;
    }
    
    // Clear all filters
    function clearFilters() {
        venueSearch.value = '';
        capacityFilter.value = '';
        priceFilter.value = '';
        
        // Uncheck all checkboxes
        document.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
            checkbox.checked = false;
        });
        
        // Reset date to today
        dateFilter.value = formattedDate;
        
        // Show all venues
        venueCards.forEach(card => {
            card.style.display = 'block';
        });
        
        updateVenueCount(venueCards.length);
    }
    
    // Event listeners for filter changes
    venueSearch.addEventListener('input', applyFilters);
    capacityFilter.addEventListener('change', applyFilters);
    priceFilter.addEventListener('change', applyFilters);
    dateFilter.addEventListener('change', applyFilters);
    applyFiltersBtn.addEventListener('click', applyFilters);
    clearFiltersBtn.addEventListener('click', clearFilters);
    
    // Toggle favorite button
    document.querySelectorAll('.favorite-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const icon = this.querySelector('i');
            if (icon.classList.contains('far')) {
                icon.classList.remove('far');
                icon.classList.add('fas');
                icon.style.color = '#e74c3c';
            } else {
                icon.classList.remove('fas');
                icon.classList.add('far');
                icon.style.color = '';
            }
        });
    });
});