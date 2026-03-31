<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard | Rafakijay Car Rental</title>
  <link rel="stylesheet" href="{{asset('assets/css/admindashboard.css')}}">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
</head>
<body>

  <!-- Background --> 
  <div class="background"></div>

  <div class="menu-container">
    <!-- Sidebar -->
    <aside class="sidebar">
      <img src="{{asset('assets/images/logocar.png.png')}}" alt="Logo" class="logo">

      <ul>
        <li class="active">
            <img src="{{ asset('assets/images/dashboard.png.png') }}" alt="">
            <a href="{{ url('admin/dashboard') }}" class="active">Dashboard</a>
        </li>
        <li>
            <img src="{{asset('assets/images/cars.png.png')}}" alt="">
            <a href="{{ url('vehicles') }}">Vehicles</a>
        </li>
        <li>
            <img src="{{asset('assets/images/annotation.png')}}" alt="">
            <a href="{{ url('rent') }}">Rent</a>
        </li>
        <li>
            <img src="{{asset('assets/images/cash.png')}}" alt="">
            <a href="{{ url('billing') }}">Billings</a>
        </li>
        <li>
            <img src="{{asset('assets/images/profile2.png')}}" alt="">
            <a href="{{ url('ownerslist') }}">Ownerslist</a>
        </li>
      </ul>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
      <header>
        <h1>Dashboard</h1>
      </header>

      <!-- MAP SECTION -->
      <section class="dashboard-layout">

  <!-- LEFT: MAP -->
  <div class="dashboard-map">
    <h2>Our Rental Shop Location</h2>
    <iframe
      src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d1007.2400853653274!2d125.49589573174583!3d9.787397809471626!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x330136bee35cc3ef%3A0x9b7f2d5a0bac57b5!2sNarciso%20Street%2C%20Surigao%2C%20Surigao%20del%20Norte!5e0!3m2!1sen!2sph!4v1765790499257!5m2!1sen!2sph"
      allowfullscreen
      loading="lazy">
    </iframe>
  </div>

  <!-- RIGHT: PIE CHARTS -->
  <div class="dashboard-cards">
    <div class="card">
      <div class="circle" style="--value:90;">
        <span>90%</span>
      </div>
      <p>Total Customers</p>
    </div>

    <div class="card">
      <div class="circle" style="--value:80;">
        <span>80%</span>
      </div>
      <p>Total Vehicle</p>
    </div>

    <div class="card">
      <div class="circle" style="--value:95;">
        <span>95%</span>
      </div>
      <p>Available Cars % Rented Cars</p>
    </div>
  </div>

</section>


    </main>
  </div>

</body>
</html>
