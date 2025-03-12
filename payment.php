<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Payment Page - GatherSpot</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="css/bootstrap.min.css">
  <link rel="stylesheet" href="css/templatemo-style.css">
  <style>
    .payment-page {
      margin: 50px auto;
      max-width: 600px;
      padding: 20px;
      background: #fff;
      border-radius: 8px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    .payment-page h2 {
      margin-bottom: 30px;
    }
  </style>
</head>
<body>
  <div class="navbar">
    <div class="nav-menu">
      <div class="nav-logo">GatherSpot</div>
      <ul class="nav-menu-items">
        <li class="nav-menu-item"><a href="index.html">Home</a></li>
        <li class="nav-menu-item"><a href="about.html">About Us</a></li>
        <li class="nav-menu-item"><a href="events.html">Events</a></li>
        <li class="nav-menu-item"><a href="contact.html">Contact Us</a></li>
      </ul>
    </div>
    <div class="nav-actions">
      <div class="nav-login">Log In</div>
      <button class="nav-button">Get Started</button>
    </div>
  </div>
  <div class="container payment-page">
    <h2 class="text-center">Payment Information</h2>
    <form id="payment-form" novalidate>
      <div class="form-group">
        <label for="cardholder-name">Cardholder Name</label>
        <input type="text" class="form-control" id="cardholder-name" placeholder="Enter name on card" required>
      </div>
      <div class="form-group">
        <label for="card-number">Card Number</label>
        <input type="text" class="form-control" id="card-number" placeholder="16-digit card number" pattern="\d{16}" maxlength="16" title="Please enter exactly 16 digits" required>
      </div>
      <div class="form-row">
        <div class="form-group col-md-6">
          <label for="expiry-date">Expiry Date (MM/YY)</label>
          <input type="text" class="form-control" id="expiry-date" placeholder="MM/YY" pattern="(0[1-9]|1[0-2])\/\d{2}" maxlength="5" title="Enter a valid expiry date in MM/YY format" required>
        </div>
        <div class="form-group col-md-6">
          <label for="cvv">CVV</label>
          <input type="text" class="form-control" id="cvv" placeholder="3-digit CVV" pattern="\d{3}" maxlength="3" title="Please enter exactly 3 digits" required>
        </div>
      </div>
      <div class="form-group">
        <label for="billing-zip">Billing ZIP Code</label>
        <input type="text" class="form-control" id="billing-zip" placeholder="Enter ZIP code" required>
      </div>
      <button type="submit" class="btn btn-primary btn-block">Pay Now</button>
    </form>
  </div>
  <footer>
    <div class="container text-center">
      <p>&copy; 2025 GatherSpot. All rights reserved.</p>
    </div>
  </footer>
  <script src="js/vendor/jquery.min.js"></script>
  <script src="js/vendor/bootstrap.min.js"></script>
  <script>
    document.getElementById('card-number').addEventListener('input', function() {
      this.value = this.value.replace(/\D/g, '');
    });
    document.getElementById('cvv').addEventListener('input', function() {
      this.value = this.value.replace(/\D/g, '');
    });
    document.getElementById('expiry-date').addEventListener('input', function() {
      let digits = this.value.replace(/\D/g, '');
      if (digits.length > 2) {
        digits = digits.slice(0,2) + '/' + digits.slice(2,4);
      } else if (digits.length === 2 && !this.value.includes('/')) {
        digits = digits + '/';
      }
      this.value = digits;
    });
    function validateExpiryDate() {
      var expInput = document.getElementById('expiry-date');
      var value = expInput.value;
      var regex = /^(0[1-9]|1[0-2])\/\d{2}$/;
      if (!regex.test(value)) {
        expInput.setCustomValidity("Enter a valid expiry date in MM/YY format.");
        return false;
      }
      var parts = value.split('/');
      var month = parseInt(parts[0], 10);
      var year = parseInt(parts[1], 10) + 2000;
      var now = new Date();
      var expiry = new Date(year, month, 0, 23, 59, 59, 999);
      if (expiry < now) {
        expInput.setCustomValidity("The expiry date is expired.");
        return false;
      }
      expInput.setCustomValidity("");
      return true;
    }
    document.getElementById('expiry-date').addEventListener('blur', validateExpiryDate);
    document.getElementById('payment-form').addEventListener('submit', function(e) {
      validateExpiryDate();
      if (!this.checkValidity()) {
        e.preventDefault();
        e.stopPropagation();
        this.classList.add('was-validated');
      } else {
        e.preventDefault();
        var paymentSuccessful = Math.random() < 0.5;
        if (paymentSuccessful) {
          alert("Payment processed successfully!");
        } else {
          alert("Payment failed. Please check your payment details and try again.");
        }
      }
    });
  </script>
</body>
</html>
