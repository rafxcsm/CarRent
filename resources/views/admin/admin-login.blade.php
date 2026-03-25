<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin Login | Rafakijay Car Rental</title>

  <link rel="stylesheet" href="{{asset('assets/css/adminlogin.css')}}" />
</head>

<body>
  <div class="background">
    <div class="overlay"></div>

    <header>
      <h2 class="logo">RAFAKIJAY CAR RENTAL <br> MANAGEMENT SYSTEM</h2>
    </header>

    <main>
      <div class="left-section">
        <p class="tagline">The Key to Hassle-Free Car Rentals</p>
        <p class="tagline">Admin Access Panel</p>

        <div class="login-box">
          <h3>Admin Login</h3>

          <form method="POST" action="{{ route('admin.login.submit') }}">
            @csrf

            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>

            <button type="submit" class="btn-link">Login</button>
          </form>

          @if ($errors->any())
            <div class="error">{{ $errors->first() }}</div>
          @endif

        </div>
      </div>
    </main>

  </div>
</body>
</html>
