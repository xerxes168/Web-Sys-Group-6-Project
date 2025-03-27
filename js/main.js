    jQuery(document).ready(function($) {
        'use strict';
        
        // Initialize Datepicker
        $('#datepicker').datepicker({
            format: 'dd M yyyy', // Format like 12 May 2024
            autoclose: true,
            startDate: 'today', // Prevent selecting past dates
            todayHighlight: true,
            zIndexOffset: 10000 // Ensure it appears above other elements
        });
    
        var owl = $("#owl-testimonial");
    
        owl.owlCarousel({
        pagination : true,
        paginationNumbers: false,
        autoPlay: 6000, //Set AutoPlay to 3 seconds
        items : 1, //10 items above 1000px browser width
        itemsDesktop : [1000,1], //5 items between 1000px and 901px
        itemsDesktopSmall : [900,1], // betweem 900px and 601px
        itemsTablet: [600,1], //2 items between 600 and 0
        itemsMobile : false // itemsMobile disabled - inherit from itemsTablet option
        });
            
        $('.recommendedgroup > div').hide();
        $('.recommendedgroup > div:first-of-type').show();
        $('.tabs a').click(function(e){
        e.preventDefault();
            var $this = $(this),
            tabgroup = '#'+$this.parents('.tabs').data('recommendedgroup'),
            others = $this.closest('li').siblings().children('a'),
            target = $this.attr('href');
        others.removeClass('active');
        $this.addClass('active');
        $(tabgroup).children('div').hide();
        $(target).show();
        });
    
        $('.weathergroup > div').hide();
        $('.weathergroup > div:first-of-type').show();
        $('.tabs a').click(function(e){
        e.preventDefault();
            var $this = $(this),
            tabgroup = '#'+$this.parents('.tabs').data('weathergroup'),
            others = $this.closest('li').siblings().children('a'),
            target = $this.attr('href');
        others.removeClass('active');
        $this.addClass('active');
        $(tabgroup).children('div').hide();
        $(target).show();
        });
    
        $('.tabgroup > div').hide();
        $('.tabgroup > div:first-of-type').show();
        $('.tabs a').click(function(e){
        e.preventDefault();
            var $this = $(this),
            tabgroup = '#'+$this.parents('.tabs').data('tabgroup'),
            others = $this.closest('li').siblings().children('a'),
            target = $this.attr('href');
        others.removeClass('active');
        $this.addClass('active');
        $(tabgroup).children('div').hide();
        $(target).show();
        });
    
        $(".pop-button").click(function () {
            $(".pop").fadeIn(300);
        });
    
        $(".pop > span").click(function () {
            $(".pop").fadeOut(300);
        });
    
        $(window).on("scroll", function() {
            if($(window).scrollTop() > 100) {
                $(".header").addClass("active");
            } else {
                //remove the background property so it comes transparent again (defined in your css)
            $(".header").removeClass("active");
            }
        });
    
        // Get the popup
        var popup = $("#reservation-popup");
        
        // Get all reserve buttons
        $(".white-button a").on("click", function(e) {
            e.preventDefault();
            
            // Get event details from the clicked row
            var eventRow = $(this).closest("ul");
            var eventTitle = eventRow.find("h4").text();
            var eventDate = eventRow.find(".date span").text();
            var eventTime = eventRow.find(".time span").text();
            var eventLocation = eventRow.find(".web span, .design span, .app span").text();
            
            // Set the event details in the popup
            $("#event-title").text(eventTitle);
            
            // Optional: Pre-fill the date field with the event date
            try {
            // Parse the date from the event row
            var parsedDate = new Date(eventDate);
            $("#reservation-date").datepicker('update', parsedDate);
            } catch(err) {
            console.log("Could not parse date:", err);
            }
            
            // Show the popup
            popup.css("display", "block");
            
            return false; // Additional safeguard
        });
        
        // Close the popup when clicking the close button
        $(".close-popup").on("click", function() {
            popup.css("display", "none");
        });
        
        // Close the popup when clicking outside the content
        $(window).on("click", function(event) {
            if ($(event.target).is(popup)) {
                popup.css("display", "none");
            }
        });
        
        // Handle the cancel button
        $("#cancel-reservation").on("click", function() {
            popup.css("display", "none");
        });
        
        // Handle the confirm button
        $("#confirm-reservation").on("click", function() {
            // Get all the form values
            var selectedDate = $("#reservation-date").val();
            var timeSlot = $("#timeslot-select").val();
            var guestCount = $("#guest-count").val();
            var specialRequests = $("#special-requests").val();
            var eventTitle = $("#event-title").text();
            
            // Here you would typically send this data to a server
            // For now, we'll just show a confirmation alert
            var confirmationMessage = `Reservation Confirmed!\n
            Event: ${eventTitle}
            Date: ${selectedDate}
            Time Slot: ${timeSlot}
            Guests: ${guestCount}
            Special Requests: ${specialRequests}`;
            
            alert(confirmationMessage);
            
            // Close the popup
            popup.css("display", "none");
        });


        // Add heart icons to each event item
        $('.event-item').each(function() {
            // Get event information for saving
            const eventTitle = $(this).find('h4').text();
            const eventLocation = $(this).find('.web span, .design span, .app span').text();
            const eventDate = $(this).find('.date span').text();
            
            // Create unique ID for the event based on its information
            const eventId = btoa(eventTitle + eventLocation + eventDate).replace(/=/g, '');
            
            // Add heart icon
            $(this).prepend(`<i class="fa fa-heart favorite-heart" data-event-id="${eventId}"></i>`);
            
            // Check if this event is already in favorites
            if (localStorage.getItem('favorite_' + eventId)) {
                $(this).find('.favorite-heart').addClass('active');
            }
        });
        
        // Handle click on heart icon
        $(document).on('click', '.favorite-heart', function(e) {
            e.stopPropagation(); // Prevent event bubbling
            
            const $heart = $(this);
            const eventId = $heart.data('event-id');
            
            // Toggle favorite status
            if ($heart.hasClass('active')) {
                // Remove from favorites
                $heart.removeClass('active');
                localStorage.removeItem('favorite_' + eventId);
            } else {
                // Add to favorites
                $heart.addClass('active');
                
                // Save to localStorage - just the ID is enough if no display is needed
                localStorage.setItem('favorite_' + eventId, 'true');
            }
        });
    });

        $(document).ready(function() 
        {
            // navigation click actions 
            $('.scroll-link').on('click', function(event){
                event.preventDefault();
                var sectionID = $(this).attr("data-id");
                scrollToID('#' + sectionID, 750);
            });
                // Navigation for How to Book
            $('.scroll-link').on('click', function(event){
                event.preventDefault();
                var sectionID = $(this).attr("reservation-section"); 
                $('html, body').animate({
                    scrollTop: $(sectionID).offset().top - 50 
                }, 750);
            });
            // scroll to top action
            $('.scroll-top').on('click', function(event) {
                event.preventDefault();
                $('html, body').animate({scrollTop:0}, 'slow');         
            });
            // mobile nav toggle
            $('#nav-toggle').on('click', function (event) {
                event.preventDefault();
                $('#main-nav').toggleClass("open");
            });
        });
        // scroll function
        function scrollToID(id, speed){
            var offSet = 0;
            var targetOffset = $(id).offset().top - offSet;
            var mainNav = $('#main-nav');
            $('html,body').animate({scrollTop:targetOffset}, speed);
            if (mainNav.hasClass("open")) {
                mainNav.css("height", "1px").removeClass("in").addClass("collapse");
                mainNav.removeClass("open");
            }
        }
        if (typeof console === "undefined") {
            console = {
                log: function() { }
            };
        }

        $('.scroll-link').on('click', function (event) {
            event.preventDefault();
            const target = $(this).attr("href");
            $('html, body').animate({
                scrollTop: $(target).offset().top - 50
            }, 750);
        });

        // Function to toggle the navigation menu visibility on mobile
        function toggleNavbar() {
        var navMenu = document.querySelector('.nav-menu-items');
        navMenu.classList.toggle('active');
        }

        $(document).ready(function() {
            // Hamburger toggle for mobile view
            $('.navbar-toggler').click(function() {
                $('.nav-menu-items').toggleClass('active');
            });
        });
      
        /************** Mixitup (Filter Projects) *********************/
        $('.projects-holder').mixitup({
            effects: ['fade','grayscale'],
            easing: 'snap',
            transitionSpeed: 400
        });
