<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Birthday Venues - GatherSpot</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <!-- Navbar would go here -->
    
    <!-- Search and Filter Bar -->
    <div class="search-filter-container">
        <div class="container">
            <div class="search-row">
                <div class="search-box">
                    <input type="text" id="venue-search" placeholder="Search venues...">
                    <button type="button"><i class="fas fa-search"></i></button>
                </div>
                
                <div class="filter-options">
                    <div class="filter-dropdown">
                        <select id="capacity-filter">
                            <option value="">Guest Capacity</option>
                            <option value="10">Up to 10 people</option>
                            <option value="20">Up to 20 people</option>
                            <option value="50">Up to 50 people</option>
                            <option value="100">Up to 100 people</option>
                            <option value="200">More than 100 people</option>
                        </select>
                    </div>
                    
                    <div class="filter-dropdown">
                        <input type="date" id="date-filter">
                    </div>
                    
                    <div class="filter-dropdown">
                        <select id="price-filter">
                            <option value="">Price Range</option>
                            <option value="0-50">$0 - $50 /hr</option>
                            <option value="50-100">$50 - $100 /hr</option>
                            <option value="100-200">$100 - $200 /hr</option>
                            <option value="200+">$200+ /hr</option>
                        </select>
                    </div>
                    
                    <button type="button" id="more-filters-btn">
                        <i class="fas fa-sliders-h"></i> More Filters
                    </button>
                </div>
            </div>
            
            <!-- More Filters Panel (hidden by default) -->
            <div class="more-filters-panel" id="more-filters-panel">
                <div class="filter-row">
                    <div class="filter-group">
                        <h4>Venue Type</h4>
                        <label><input type="checkbox" value="function-room"> Function Room</label>
                        <label><input type="checkbox" value="restaurant"> Restaurant</label>
                        <label><input type="checkbox" value="activity-space"> Activity Space</label>
                        <label><input type="checkbox" value="outdoor"> Outdoor</label>
                    </div>
                    
                    <div class="filter-group">
                        <h4>Amenities</h4>
                        <label><input type="checkbox" value="catering"> Catering Available</label>
                        <label><input type="checkbox" value="decoration"> Decoration Service</label>
                        <label><input type="checkbox" value="sound-system"> Sound System</label>
                        <label><input type="checkbox" value="party-games"> Party Games</label>
                    </div>
                    
                    <div class="filter-group">
                        <h4>Location</h4>
                        <label><input type="checkbox" value="central"> Central</label>
                        <label><input type="checkbox" value="north"> North</label>
                        <label><input type="checkbox" value="south"> South</label>
                        <label><input type="checkbox" value="east"> East</label>
                        <label><input type="checkbox" value="west"> West</label>
                    </div>
                </div>
                
                <div class="filter-actions">
                    <button type="button" id="clear-filters-btn">Clear All</button>
                    <button type="button" id="apply-filters-btn">Apply Filters</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Venues List -->
    <section class="venues-section">
        <div class="container">
            <h1>Birthday Venues</h1>
            <p class="results-count"><span id="venue-count">12</span> venues available for your birthday celebration</p>
            
            <div class="venues-grid" id="venues-container">
                <!-- Venue Card 1 -->
                <div class="venue-card" data-price="64" data-capacity="35" data-type="function-room" data-area="central">
                    <div class="venue-image">
                        <img src="../img/birthday-venue1.jpg" alt="Function Room Venue">
                        <button class="favorite-btn"><i class="far fa-heart"></i></button>
                        <span class="price-tag">from $64/hour</span>
                    </div>
                    <div class="venue-details">
                        <h3>Function Room in an Event Venue</h3>
                        <p class="venue-location"><i class="fas fa-map-marker-alt"></i> Little India</p>
                        <div class="venue-features">
                            <span><i class="fas fa-user-friends"></i> 35 guests</span>
                            <span><i class="fas fa-th-large"></i> 30 sqm</span>
                        </div>
                        <div class="venue-rating">
                            <span class="stars">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star-half-alt"></i>
                            </span>
                            <span class="rating-count">(305)</span>
                        </div>
                        <div class="tag-container">
                            <span class="tag">Private space</span>
                            <span class="tag supervenue">Supervenue</span>
                        </div>
                    </div>
                </div>
                
                <!-- Venue Card 2 -->
                <div class="venue-card" data-price="72" data-capacity="50" data-type="activity-space" data-area="east">
                    <div class="venue-image">
                        <img src="../img/birthday-venue2.jpg" alt="Level Two Space">
                        <button class="favorite-btn"><i class="far fa-heart"></i></button>
                        <span class="price-tag">from $72/hour</span>
                    </div>
                    <div class="venue-details">
                        <h3>Level Two Space</h3>
                        <p class="venue-location"><i class="fas fa-map-marker-alt"></i> Jalan Besar MRT Station</p>
                        <div class="venue-features">
                            <span><i class="fas fa-user-friends"></i> 50 guests</span>
                            <span><i class="fas fa-th-large"></i> 50 sqm</span>
                        </div>
                        <div class="venue-rating">
                            <span class="stars">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                            </span>
                            <span class="rating-count">(90)</span>
                        </div>
                        <div class="tag-container">
                            <span class="tag">Private space</span>
                            <span class="tag supervenue">Supervenue</span>
                        </div>
                    </div>
                </div>
                
                <!-- Add more venue cards here with different attributes for filtering -->
                
                <!-- Additional venue cards would be added here -->
            </div>
            
            <!-- Pagination -->
            <div class="pagination">
                <button class="prev-page" disabled><i class="fas fa-chevron-left"></i> Previous</button>
                <div class="page-numbers">
                    <button class="active">1</button>
                    <button>2</button>
                    <button>3</button>
                    <span>...</span>
                    <button>8</button>
                </div>
                <button class="next-page">Next <i class="fas fa-chevron-right"></i></button>
            </div>
        </div>
    </section>
    
    <!-- Footer would go here -->
    
    <script src="../js/birthday-venues.js"></script>
</body>
</html>