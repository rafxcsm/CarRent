<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>About Us | Rafakijay Car Rental</title>
  <link rel="stylesheet" href="{{asset(path: 'assets/css/user/about-us.css')}}" />
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
          <li><a href=""class="active">ABOUT US</a></li>
          <li><a href="services">SERVICES</a></li>
          <li><a href="contact">CONTACT</a></li>
        </ul>
      </nav>
    </header>

    <!-- Main Content -->
   <main>
      <div class="info-box">
        <h1>About Us</h1>
        <p>
          Rafakijay Car Rental has been providing hassle-free, reliable car rental services for years.
          Our mission is to deliver a seamless experience for our customers, from booking to drop-off.
        </p>
        <p>
          We pride ourselves on our professional service, wide vehicle selection, and competitive pricing.
        </p>
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
