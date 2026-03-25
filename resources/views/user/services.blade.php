<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Services | Rafakijay Car Rental</title>
  <link rel="stylesheet" href="{{asset(path: 'assets/css/user/services.css')}} " />
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
</head>
<body>
  <div class="background">
    <div class="overlay"></div>

    <!-- Header with Navigation -->
    <header>
      <nav>
        <ul>
          <li><a href="login">HOME</a></li>
          <li><a href="aboutus">ABOUT US</a></li>
          <li><a href=""class="active">SERVICES</a></li>
          <li><a href="contact">CONTACT</a></li>
        </ul>
      </nav>
    </header>

    <!-- Main Content -->
    <main>
      <div class="info-box">
        <h1>Our Services</h1>
        <ul>
          <li>Daily, weekly, and monthly car rentals</li>
          <li>Airport pickups and drop-offs</li>
          <li>Luxury and economy vehicle options</li>
          <li>Online booking and customer support</li>
          <li>Insurance and roadside assistance</li>
        </ul>
      </div>
    </main>
  </div>

  <script>
    const contactForm = document.getElementById('contactForm');
    contactForm.addEventListener('submit', (e) => {
      e.preventDefault();
      alert("Your message has been sent! We will contact you soon.");
      contactForm.reset();
    });
  </script>
</body>
</html>
