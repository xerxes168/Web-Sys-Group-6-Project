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
    
        /************** Mixitup (Filter Projects) *********************/
        $('.projects-holder').mixitup({
            effects: ['fade','grayscale'],
            easing: 'snap',
            transitionSpeed: 400
        });
    });