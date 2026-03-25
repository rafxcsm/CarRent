<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Rafakijay Car Rental Management System</title>

  <!-- CSS -->
  <link rel="stylesheet" href="{{ asset('assets/css/user/login.css') }}">

  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
</head>

<body>
  <div class="background">
    <div class="overlay"></div>

    <main>
      <div class="left-section">
        <h2 class="logo-login">RAFAKIJAY CAR RENTAL <br> MANAGEMENT SYSTEM</h2>
        <p class="tagline">The Key to Hassle-Free Car Rentals</p>

        <div class="login-box">
          <h3>Log in to your account</h3>

        <form action="{{ route('login.submit') }}" method="POST">
    @csrf
    <input type="email" name="email" placeholder="Email" required>
    <input type="password" name="password" placeholder="Password" required>
    <button type="submit" class="btn-link">Login</button>
</form>



          <p class="signup-text">Don’t have an account? <a href="{{ route('signup.form') }}" id="signupLink">Sign up</a></p>
        </div>
      </div>
    </main>
  </div>

  <script src="js/login.js"></script>
</body>
</html>
