<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile | Rafakijay Car Rental</title>

    <link rel="stylesheet" href="{{ asset('assets/css/user/profile.css') }}">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">

</head>
<body>

<div class="background"></div>

<div class="menu-container">

    <!-- SIDEBAR -->
    <aside class="sidebar">
        <img src="{{ asset('assets/images/logocar.png.png') }}" class="logo">
        <ul>
            <li>
                <img src="{{ asset('assets/images/cars.png.png') }}">
                <a href="{{ route('user.vehicles') }}">Vehicles</a>
            </li>
            <li class="active">
                <img src="{{ asset('assets/images/user/images/profile2.png.png') }}">
                <a href="{{ route('user.profile') }}" class="active">Profile</a>
            </li>
            <li>
                <img src="{{ asset('assets/images/receipts.png.png') }}">
                <a href="{{ route('user.receipts') }}">Receipts</a>
            </li>
        </ul>
    </aside>

    <!-- MAIN -->
    <main class="main-content">
        <header>
            <h1>Profile</h1>

            <!-- PROFILE ICON -->
            <div class="icons">
                <img src="{{ asset('assets/images/user/images/profile.png.png') }}"
                     id="profileIcon"
                     alt="Profile">
            </div>
        </header>

        <!-- PROFILE FORM -->
        <section class="profile-form">
            <div class="profile-layout">

                <form id="profileForm" method="POST" action="{{ route('profile.storeAndBook') }}">
                    @csrf

                    <div class="form-columns">

                        <div class="column">
                            <input type="text" name="first_name" placeholder="First Name" required>
                            <input type="text" name="middle_name" placeholder="Middle Name">
                            <input type="text" name="last_name" placeholder="Last Name" required>

                            <select name="gender" required>
                                <option value="">Select Gender</option>
                                <option value="M">Male</option>
                                <option value="F">Female</option>
                            </select>
                        </div>

                        <div class="column">
                            <input type="date" name="birthdate" required>
                            <input type="text" name="address" placeholder="Address" required>
                            <input type="text" name="contact" placeholder="Contact" required>
                            <input type="text" name="license_number" placeholder="License Number">
                        </div>

                    </div>

                    @if($vehicle)
                        <input type="hidden" name="vehicle_id" value="{{ $vehicle->id }}">
                    @endif

                    <input type="hidden" name="pickup_date" id="pickup_date">
                    <input type="hidden" name="pickup_time" id="pickup_time">
                    <input type="hidden" name="rental_type" id="rental_type">

                    <button type="button" class="add-btn" id="openBooking">
                        Add Booking Details
                    </button>
                </form>

                @if($vehicle)
                    <div class="vehicle-summary">
                        <h3>Booking Vehicle</h3>
                        <img src="{{ asset('uploads/vehicles/'.$vehicle->image) }}" class="vehicle-img">
                        <p><strong>{{ $vehicle->brand }} {{ $vehicle->model }}</strong></p>
                        <p>Rate: ₱{{ number_format($vehicle->rate, 2) }} / day</p>
                    </div>
                @endif

            </div>
        </section>
    </main>
</div>

<!-- BOOKING POPUP -->
<div class="popup" id="bookingPopup">
    <div class="popup-box">
        <span class="close-btn" id="closeBooking">&times;</span>
        <h3>Booking Details</h3>

        <input type="date" id="b_date">
        <input type="time" id="b_time">

        <select id="b_type">
            <option value="">Select Rental Type</option>
            <option value="Whole Day">Whole Day</option>
            <option value="Half Day">Half Day</option>
        </select>

        <button class="add-btn" id="confirmBooking">Book</button>
    </div>
</div>

<!-- SUCCESS POPUP -->
<div class="popup" id="successPopup">
    <div class="popup-box">
        <h3>Booking Successful</h3>
        <p>Your booking has been saved.</p>
        <button class="add-btn" id="successOk">OK</button>
    </div>
</div>

<!-- PROFILE MODAL -->
<div id="profileModal" class="modal">
    <div class="modal-content">

        <img src="{{ asset('assets/images/user/images/profile.png.png') }}"
             style="width:80px;border-radius:50%;margin-bottom:10px;">

        <h3>User Profile</h3>

        <p><strong>Name:</strong> {{ auth()->user()->name }}</p>
        <p><strong>Email:</strong> {{ auth()->user()->email }}</p>
        <p><strong>Contact:</strong> {{ auth()->user()->contact }}</p>
        <p><strong>Address:</strong> {{ auth()->user()->address }}</p>

        <div class="modal-buttons">
            <a href="{{ route('user.profile') }}" class="btn">Edit Profile</a>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn danger">Log Out</button>
            </form>

            <button class="cancelBtn">Cancel</button>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {

    /* PROFILE MODAL */
    const profileIcon = document.getElementById("profileIcon");
    const profileModal = document.getElementById("profileModal");
    const cancelBtns = document.querySelectorAll(".cancelBtn");

    profileIcon.addEventListener("click", () => {
        profileModal.style.display = "flex";
    });

    cancelBtns.forEach(btn => {
        btn.addEventListener("click", () => {
            profileModal.style.display = "none";
        });
    });

    /* BOOKING LOGIC */
    const openBtn = document.getElementById("openBooking");
    const bookingPopup = document.getElementById("bookingPopup");
    const closeBookingBtn = document.getElementById("closeBooking");
    const confirmBookingBtn = document.getElementById("confirmBooking");
    const successPopup = document.getElementById("successPopup");
    const successOkBtn = document.getElementById("successOk");
    const profileForm = document.getElementById("profileForm");

    openBtn.onclick = () => {
        if (!profileForm.checkValidity()) {
            alert("Please complete the profile form.");
            return;
        }
        bookingPopup.style.display = "flex";
    };

    closeBookingBtn.onclick = () => {
        bookingPopup.style.display = "none";
    };

    confirmBookingBtn.onclick = () => {
        const date = b_date.value;
        const time = b_time.value;
        const type = b_type.value;

        if (!date || !time || !type) {
            alert("Please complete booking details.");
            return;
        }

        pickup_date.value = date;
        pickup_time.value = time;
        rental_type.value = type;

        const receipt = {
            type: "Car Rental Booking",
            date: date,
            time: time,
            duration: type,
            car_name: "{{ $vehicle ? $vehicle->brand.' '.$vehicle->model : 'N/A' }}",
            car_rate: "{{ $vehicle ? number_format($vehicle->rate, 2) : '0.00' }}",
            user: profileForm.first_name.value + " " + profileForm.last_name.value,
            gender: profileForm.gender.value,
            birthdate: profileForm.birthdate.value,
            address: profileForm.address.value,
            contact: profileForm.contact.value,
            license: profileForm.license_number.value
        };

        localStorage.setItem("latest_receipt", JSON.stringify(receipt));
        profileForm.submit();
    };

    @if(session('success'))
        successPopup.style.display = "flex";
    @endif

    successOkBtn.onclick = () => {
        window.location.href = "{{ route('user.receipts') }}";
    };

});
</script>

</body>
</html>
