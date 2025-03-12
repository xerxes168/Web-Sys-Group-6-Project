<?php
session_start();
$booking_id = $_GET['booking_id'] ?? null;
if (!$booking_id || !isset($_SESSION['bookings'][$booking_id])) {
    die("Booking not found.");
}
$booking = $_SESSION['bookings'][$booking_id];
$total_amount = $booking['total_amount'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Payment Page</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Minimal styling (optional) -->
  <link rel="stylesheet" href="css/bootstrap.min.css">
  <style>
    .payment-page {
      margin: 50px auto;
      max-width: 600px;
      padding: 20px;
      background: #fff;
      border-radius: 6px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    .payment-page h2 {
      text-align: center;
      margin-bottom: 20px;
    }
    /* Hide default tooltip arrow in some browsers */
    ::-webkit-validation-bubble-arrow,
    ::-webkit-validation-bubble-arrow-clipper {
      display: none;
    }
  </style>
</head>
<body>
<div class="container payment-page">
  <h2>Payment Information</h2>
  <p style="text-align: center;">Total Amount Due: $<?php echo number_format($total_amount, 2); ?></p>
  <form action="process_payment.php" method="POST">
    <input type="hidden" name="booking_id" value="<?php echo htmlspecialchars($booking_id); ?>">

    <!-- Cardholder Name: just required, no pattern -->
    <div class="form-group">
      <label for="cardholder-name">Cardholder Name</label>
      <input 
        type="text"
        class="form-control"
        id="cardholder-name"
        name="cardholder-name"
        placeholder="John Doe"
        required
      >
    </div>

    <!-- Card Number: Exactly 16 digits, custom message -->
    <div class="form-group">
      <label for="card-number">Card Number</label>
      <input 
        type="text"
        class="form-control"
        id="card-number"
        name="card-number"
        placeholder="1234567812345678"
        required
        pattern="^\d{16}$"
        maxlength="16"
        oninvalid="this.setCustomValidity('Please input exactly 16 digits');"
        oninput="this.setCustomValidity('');"
      >
    </div>

    <!-- Email: default HTML5 validation -->
    <div class="form-group">
      <label for="email">Email</label>
      <input 
        type="email"
        class="form-control"
        id="email"
        name="email"
        placeholder="john@example.com"
        required
      >
    </div>

    <!-- Expiry Date: auto-inserts slash, default prompt if invalid -->
    <div class="form-row">
      <div class="form-group col-md-6">
        <label for="expiry-date">Expiry Date (MM/YY)</label>
        <input 
          type="text"
          class="form-control"
          id="expiry-date"
          name="expiry-date"
          placeholder="MM/YY"
          required
          pattern="(0[1-9]|1[0-2])/\d{2}"
          maxlength="5"
        >
      </div>

      <!-- CVV: 3 digits, default prompt if invalid -->
      <div class="form-group col-md-6">
        <label for="cvv">CVV</label>
        <input 
          type="text"
          class="form-control"
          id="cvv"
          name="cvv"
          placeholder="123"
          required
          pattern="^\d{3}$"
          maxlength="3"
        >
      </div>
    </div>

    <button type="submit" class="btn btn-primary">Pay Now</button>
  </form>
</div>
<script src="js/vendor/jquery.min.js"></script>
<script src="js/vendor/bootstrap.min.js"></script>
<script>
  // Restrict card number & CVV to digits only
  document.getElementById('card-number').addEventListener('input', function() {
    this.value = this.value.replace(/\D/g, '');
  });
  document.getElementById('cvv').addEventListener('input', function() {
    this.value = this.value.replace(/\D/g, '');
  });
  // Auto-insert slash for expiry date
  document.getElementById('expiry-date').addEventListener('input', function() {
    let digits = this.value.replace(/\D/g, '');
    if (digits.length > 2) {
      digits = digits.slice(0,2) + '/' + digits.slice(2,4);
    } else if (digits.length === 2 && !this.value.includes('/')) {
      digits = digits + '/';
    }
    this.value = digits;
  });
</script>
</body>
</html>
