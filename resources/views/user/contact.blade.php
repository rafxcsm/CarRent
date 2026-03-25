<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Contact Us | Rafakijay Car Rental</title>
  <link rel="stylesheet" href="{{asset(path: 'assets/css/user/contact.css')}}" />
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
          <li><a href="services">SERVICES</a></li>
          <li><a href=""class="active">CONTACT</a></li>
        </ul>
      </nav>
    </header>

    <!-- Main Content -->
    <main>
      <div class="info-box">
        <h1>Contact Us</h1>
        <p>Email: <a href="mailto:support@rafakijay.com">support@rafakijay.com</a></p>
        <p>Phone: +63 912 345 6789</p>
        <p>Address: 123 Main Street, Cebu City, Philippines</p>

        <h3>Send us a message</h3>
        <form id="contactForm">
          <input type="text" placeholder="Your Name" required>
          <input type="email" placeholder="Your Email" required>
          <textarea placeholder="Your Message" required></textarea>
          <button type="submit">Send</button>
        </form>
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
