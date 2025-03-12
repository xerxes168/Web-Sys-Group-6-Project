// sit_validator.js

// Function to validate the login form
function validateLoginForm() {
    const form = document.querySelector('form[action="login.php"]');
    if (!form) return; // Exit if form not found

    form.addEventListener('submit', function (event) {
        event.preventDefault(); // Prevent form submission until validation passes

        // Get input values
        const username = document.getElementById('username').value.trim();
        const password = document.getElementById('password').value.trim();

        // Clear previous error messages
        clearErrors();

        // Validation flags
        let isValid = true;

        // Username/Email validation
        if (username === '') {
            showError('username', 'Username or Email is required.');
            isValid = false;
        } else if (!isValidEmail(username) && username.length < 3) {
            showError('username', 'Enter a valid email or username (min 3 characters).');
            isValid = false;
        }

        // Password validation
        if (password === '') {
            showError('password', 'Password is required.');
            isValid = false;
        } else if (password.length < 6) {
            showError('password', 'Password must be at least 6 characters.');
            isValid = false;
        }

        // Submit form if valid
        if (isValid) {
            form.submit();
        }
    });
}

// Function to validate the register form
function validateRegisterForm() {
    const form = document.querySelector('form[action="register.php"]');
    if (!form) return; // Exit if form not found

    form.addEventListener('submit', function (event) {
        event.preventDefault(); // Prevent form submission until validation passes

        // Get input values
        const username = document.getElementById('username').value.trim();
        const email = document.getElementById('email').value.trim();
        const password = document.getElementById('password').value.trim();
        const confirmPassword = document.getElementById('confirm-password').value.trim();

        // Clear previous error messages
        clearErrors();

        // Validation flags
        let isValid = true;

        // Username validation
        if (username === '') {
            showError('username', 'Username is required.');
            isValid = false;
        } else if (username.length < 3) {
            showError('username', 'Username must be at least 3 characters.');
            isValid = false;
        }

        // Email validation
        if (email === '') {
            showError('email', 'Email is required.');
            isValid = false;
        } else if (!isValidEmail(email)) {
            showError('email', 'Enter a valid email address.');
            isValid = false;
        }

        // Password validation
        if (password === '') {
            showError('password', 'Password is required.');
            isValid = false;
        } else if (password.length < 6) {
            showError('password', 'Password must be at least 6 characters.');
            isValid = false;
        }

        // Confirm Password validation
        if (confirmPassword === '') {
            showError('confirm-password', 'Please confirm your password.');
            isValid = false;
        } else if (password !== confirmPassword) {
            showError('confirm-password', 'Passwords do not match.');
            isValid = false;
        }

        // Submit form if valid
        if (isValid) {
            form.submit();
        }
    });
}

// Helper function to check if email is valid
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

// Helper function to show error messages
function showError(inputId, message) {
    const input = document.getElementById(inputId);
    const errorDiv = document.createElement('div');
    errorDiv.className = 'error-message';
    errorDiv.style.color = 'red';
    errorDiv.style.fontSize = '12px';
    errorDiv.style.marginTop = '5px';
    errorDiv.textContent = message;
    input.parentNode.appendChild(errorDiv);
    input.style.borderColor = 'red';
}

// Helper function to clear previous error messages
function clearErrors() {
    const errors = document.querySelectorAll('.error-message');
    errors.forEach(error => error.remove());
    const inputs = document.querySelectorAll('input');
    inputs.forEach(input => input.style.borderColor = '');
}

// Initialize validation based on page
document.addEventListener('DOMContentLoaded', function () {
    if (document.querySelector('form[action="login.php"]')) {
        validateLoginForm();
    } else if (document.querySelector('form[action="register.php"]')) {
        validateRegisterForm();
    }
});