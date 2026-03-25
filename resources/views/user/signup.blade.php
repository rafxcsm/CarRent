<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Sign Up | Rafakijay Car Rental</title>

    <link rel="stylesheet" href="{{asset(path: 'assets/css/user/signup.css')}}" >

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="background">
        <div class="overlay"></div>

        <header>
            <nav>
                <ul>
                    <li><a href="{{ route('login.form') }}">LOGIN</a></li>
                    <li><a href="signup" class="active">SIGN UP</a></li>
                </ul>
            </nav>
        </header>

        <main>
            <div class="left-section">
                <h2 class="logo-signup">RAFAKIJAY CAR RENTAL <br> MANAGEMENT SYSTEM</h2>
                <p class="tagline-signup">Join us today for hassle-free rentals!</p>

                <div class="signup-box">
                    <h3>Create your account</h3>
                   <form action="{{ route('signup.store') }}" method="POST">
    @csrf

    <input type="text" name="name" placeholder="Full Name" required>
    <input type="email" name="email" placeholder="Email" required>
    <input type="text" name="contact" placeholder="Phone Number" required>
    <input type="text" name="address" placeholder="Address" required>
    <input type="password" name="password" placeholder="Password" required>
    <input type="password" name="password_confirmation" placeholder="Confirm Password" required>

    <button type="submit">Sign Up</button>
</form>

              @if(session('success'))
    <div id="successModal" class="modal">
        <div class="modal-content">
            <h3>Success</h3>
            <p>{{ session('success') }}</p>
            <button id="okBtn">OK</button>
        </div>
    </div>
@endif

@if(session('success'))
<script>
    document.getElementById('successModal').style.display = 'flex';

    document.getElementById('okBtn').addEventListener('click', function () {
        window.location.href = "{{ route('login.form') }}";
    });
</script>
@endif

        </main>
    </div>
</head>
</body>