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
  
document.addEventListener('DOMContentLoaded', initPasswordToggles);

// Client-side check to ensure new password differs from current password before form submission
document.getElementById('change_password_form').addEventListener('submit', function(e) {
    const currentPassword = document.getElementById('current_password').value;
    const newPassword = document.getElementById('new_password').value;
    if (currentPassword === newPassword) {
        e.preventDefault();
        alert("New password must be different from the current password.");
    }
});

// Password Strength Indicator
document.getElementById('new_password').addEventListener('input', function() {
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
  
// Preview profile picture when a file is selected
function previewProfilePicture(event) {
    const previewContainer = document.getElementById('profile_preview');
    previewContainer.innerHTML = ""; // Clear previous preview
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const img = document.createElement('img');
            img.src = e.target.result;
            img.alt = "Profile Picture Preview";
            previewContainer.appendChild(img);
        }
        reader.readAsDataURL(file);
    }
}
