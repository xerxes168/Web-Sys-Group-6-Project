// Function to initialize password toggle buttons
function initPasswordToggles() {
  const toggleButtons = document.querySelectorAll('.toggle-password');

  toggleButtons.forEach((button) => {
      button.addEventListener('click', function () {
          // Find the associated input via a data attribute
          const inputId = this.getAttribute('data-target');
          const input = document.getElementById(inputId);
          const icon = this.querySelector('i');

          if (input) {
              if (input.type === 'password') {
                  input.type = 'text';
                  icon.classList.remove('fa-eye');
                  icon.classList.add('fa-eye-slash');
              } else {
                  input.type = 'password';
                  icon.classList.remove('fa-eye-slash');
                  icon.classList.add('fa-eye');
              }
          }
      });
  });
}

// Initialize JavaScript on DOM load
document.addEventListener('DOMContentLoaded', function () {
  console.log("Profile JS loaded");

  // Initialize password toggle functionality
  initPasswordToggles();

  // Handle profile picture upload via AJAX
  $('#profile_picture_form').on('submit', function (e) {
      e.preventDefault(); // Prevent default form submission
      console.log("AJAX form submit intercepted");

      var formData = new FormData(this);

      $.ajax({
          url: 'upload_profile_pic.php',
          type: 'POST',
          data: formData,
          contentType: false, // Important for file uploads
          processData: false, // Important for file uploads
          success: function (response) {
              console.log("Server Response:", response);

              var parts = response.split('|');
              console.log("Parts:", parts);

              if (parts.length === 2) {
                  var message = parts[0].trim(); // Trim whitespace
                  var newFilePath = parts[1].trim(); // Trim whitespace

                  console.log("Message:", message);
                  console.log("New File Path:", newFilePath);

                  // Display only the success message
                  $('#upload_status').html(message);

                  // Update the profile picture source
                  $('.profile-picture').attr('src', newFilePath + '?' + new Date().getTime());
                  console.log("Updated Profile Picture Src:", $('.profile-picture').attr('src'));
              } else {
                  console.error("Invalid Server Response:", response);
                  $('#upload_status').html("Unexpected server response.");
              }
          },
          error: function (jqXHR, textStatus, errorThrown) {
              $('#upload_status').html("Upload failed: " + textStatus);
              console.error("AJAX Error:", textStatus, errorThrown);
          }
      });
  });

  // Client-side check to ensure new password differs from current password before form submission
  document.getElementById('change_password_form').addEventListener('submit', function (e) {
      const currentPassword = document.getElementById('current_password').value;
      const newPassword = document.getElementById('new_password').value;

      if (currentPassword === newPassword) {
          e.preventDefault();
          alert("New password must be different from the current password.");
      }
  });

  // Password Strength Indicator
  document.getElementById('new_password').addEventListener('input', function () {
      const password = this.value;
      const strengthDiv = document.getElementById('password_strength');
      let strength = '';
      let strengthClass = '';

      if (password.length < 6) {
          strength = 'Too Short';
          strengthClass = 'weak';
      } else if (password.match(/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{6,}$/)) {
          strength = 'Strong';
          strengthClass = 'strong';
      } else {
          strength = 'Medium';
          strengthClass = 'medium';
      }

      strengthDiv.textContent = strength;
      strengthDiv.className = 'password-strength ' + strengthClass;
  });
});