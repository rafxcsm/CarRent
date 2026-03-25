<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Vehicles | Rafakijay Car Rental</title>

    <link rel="stylesheet" href="{{ asset('assets/css/user/vehicles.css') }}" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">

    <style>
        .background { pointer-events: none !important; }
        header { position: relative !important; z-index: 10 !important; }
        .icons { position: relative !important; z-index: 99999 !important; }
        #profileIcon {
            cursor: pointer !important;
            pointer-events: auto !important;
            position: relative !important;
            z-index: 99999 !important;
        }

        .view-btn {
            background-color: #ffd700;
            color: #000;
            border: none;
            padding: 10px 16px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            font-family: 'Poppins', sans-serif;
        }

        .view-btn:hover { opacity: 0.9; }

        .car-details-popup,
        .booking-modal,
        .processing-modal {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.65);
            justify-content: center;
            align-items: center;
            z-index: 999999;
            padding: 20px;
        }

        .car-details-container,
        .booking-modal-content,
        .processing-content {
            background: #fff;
            width: 100%;
            max-width: 520px;
            border-radius: 14px;
            padding: 24px;
            position: relative;
            text-align: center;
            box-shadow: 0 10px 35px rgba(0, 0, 0, 0.25);
            max-height: 90vh;
            overflow-y: auto;
        }

        .close-btn {
            position: absolute;
            top: 12px;
            right: 18px;
            font-size: 28px;
            cursor: pointer;
            font-weight: bold;
            color: #333;
        }

        #carImage {
            width: 100%;
            max-height: 240px;
            object-fit: cover;
            border-radius: 10px;
            margin-bottom: 15px;
        }

        #carName { margin: 10px 0 5px; font-size: 24px; }

        #carPrice {
            font-size: 20px;
            font-weight: 600;
            color: #222;
            margin-bottom: 18px;
        }

        .car-info-box {
            text-align: left;
            background: #f8f8f8;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 18px;
        }

        .car-info-box h3 { margin: 0 0 12px; font-size: 18px; }
        #carFeatures { margin: 0; padding-left: 18px; }
        #carFeatures li { margin-bottom: 8px; font-size: 14px; color: #333; }

        .action-buttons {
            display: flex;
            gap: 10px;
            justify-content: center;
            margin-bottom: 16px;
            flex-wrap: wrap;
        }

        #bookNowBtn,
        .confirm-btn,
        .ok-btn {
            min-width: 140px;
            background-color: #ffd700;
            color: #000;
            border: none;
            border-radius: 6px;
            padding: 10px 16px;
            cursor: pointer;
            font-weight: 600;
            font-family: 'Poppins', sans-serif;
        }

        .booking-fields {
            text-align: left;
            background: #f4f4f4;
            border-radius: 10px;
            padding: 15px;
            margin-top: 10px;
        }

        .booking-fields h4 {
            margin: 0 0 12px;
            font-size: 17px;
            text-align: center;
        }

        .booking-fields label {
            display: block;
            margin: 10px 0 6px;
            font-weight: 600;
            font-size: 14px;
        }

        .booking-fields input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-family: 'Poppins', sans-serif;
            margin-bottom: 10px;
            box-sizing: border-box;
        }

        .confirm-btn { width: 100%; margin-top: 8px; }
        .ok-btn { margin-top: 15px; }

        .unavailable { opacity: 0.8; }
        .blurred { filter: blur(2px); }

        .processing-content h3 { margin: 0 0 10px; font-size: 22px; }
        .processing-content p { margin: 0; font-size: 15px; color: #555; }

        @media (max-width: 600px) {
            .car-details-container,
            .booking-modal-content {
                padding: 18px;
            }

            .action-buttons { flex-direction: column; }

            #bookNowBtn,
            .confirm-btn,
            .ok-btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>

<div class="background"></div>

<div class="menu-container">
    <aside class="sidebar">
        <img src="{{ asset('assets/images/user/images/logocar.png.png') }}" class="logo" alt="Logo">
        <ul>
            <li class="active">
                <img src="{{ asset('assets/images/user/images/cars.png.png') }}" alt="Vehicles Icon">
                <a href="{{ route('user.vehicles') }}" class="active">Vehicles</a>
            </li>
            <li>
                <img src="{{ asset('assets/images/user/images/profile2.png.png') }}" alt="Profile Icon">
                <a href="{{ route('user.profile') }}">Profile</a>
            </li>
            <li>
                <img src="{{ asset('assets/images/receipts.png.png') }}" alt="Receipts Icon">
                <a href="{{ route('user.receipts') }}">Receipts</a>
            </li>
        </ul>
    </aside>

    <main class="main-content">
        <header>
            <h1>Vehicles</h1>
            <div class="icons">
                <img src="{{ asset('assets/images/user/images/profile.png.png') }}" id="profileIcon" alt="Profile">
            </div>
        </header>

        <section class="vehicles-grid">
            @foreach($vehicles as $vehicle)
                <div
                    class="vehicle-card {{ $vehicle->status !== 'Available' ? 'unavailable' : '' }}"
                    data-car-id="{{ $vehicle->id }}"
                    data-brand="{{ $vehicle->brand }}"
                    data-model="{{ $vehicle->model }}"
                    data-rate="{{ number_format($vehicle->rate, 2) }}"
                    data-seats="{{ $vehicle->seats ?? 5 }}"
                    data-transmission="{{ $vehicle->transmission ?? 'Automatic' }}"
                    data-fuel="{{ $vehicle->fuel ?? 'Gasoline' }}"
                    data-status="{{ $vehicle->status }}"
                    data-image="{{ $vehicle->image ? asset('uploads/vehicles/'.$vehicle->image).'?v='.time() : asset('assets/images/default-car.png') }}"
                >
                    <img
                        src="{{ $vehicle->image ? asset('uploads/vehicles/'.$vehicle->image).'?v='.time() : asset('assets/images/default-car.png') }}"
                        class="{{ $vehicle->status !== 'Available' ? 'blurred' : '' }}"
                        alt="Vehicle"
                    >

                    <h2>{{ $vehicle->brand }} {{ $vehicle->model }}</h2>

                    <p class="price">
                        ₱{{ number_format($vehicle->rate, 2) }} <span>per day</span>
                    </p>

                    @if($vehicle->status === 'Available')
                        <button class="view-btn" type="button">See More</button>
                    @else
                        <button class="unavailable-btn" type="button" disabled>Unavailable</button>
                    @endif
                </div>
            @endforeach
        </section>
    </main>
</div>

<div id="carDetailsPopup" class="car-details-popup">
    <div class="car-details-container">
        <span id="closeCarDetails" class="close-btn">&times;</span>

        <img id="carImage" src="" alt="Vehicle Image">
        <h2 id="carName"></h2>
        <p class="price" id="carPrice"></p>

        <div class="car-info-box">
            <h3>Car Details</h3>
            <ul id="carFeatures"></ul>
        </div>

        <div class="action-buttons">
            <button id="bookNowBtn" type="button">Book Now</button>
        </div>
    </div>
</div>

<div id="bookingModal" class="booking-modal">
    <div class="booking-modal-content">
        <span id="closeBookingModal" class="close-btn">&times;</span>

        <form id="bookNowForm" action="{{ route('rentals.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="vehicle_id" id="bookVehicleId">

            <div class="booking-fields">
                <h4>Book Vehicle</h4>

                <label for="bookDate">Select Date</label>
                <input type="date" id="bookDate" name="date" required>

                <label for="bookTime">Select Time</label>
                <input type="time" id="bookTime" name="time" required>

                <label for="bookProof">Upload File or Image</label>
                <input
                    type="file"
                    id="bookProof"
                    name="proof_file"
                    accept="image/*,.pdf,.doc,.docx"
                    required
                >

                <button id="confirmBookBtn" class="confirm-btn" type="submit">
                    Confirm Booking
                </button>
            </div>
        </form>
    </div>
</div>

<div id="processingModal" class="processing-modal">
    <div class="processing-content" id="processingContent">
        <h3>Your transaction is being processed.</h3>
        <p>Please wait...</p>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const carDetailsPopup = document.getElementById("carDetailsPopup");
    const closeCarDetails = document.getElementById("closeCarDetails");
    const carImage = document.getElementById("carImage");
    const carName = document.getElementById("carName");
    const carPrice = document.getElementById("carPrice");
    const carFeatures = document.getElementById("carFeatures");
    const bookNowBtn = document.getElementById("bookNowBtn");

    const bookingModal = document.getElementById("bookingModal");
    const closeBookingModal = document.getElementById("closeBookingModal");
    const bookNowForm = document.getElementById("bookNowForm");
    const processingModal = document.getElementById("processingModal");
    const processingContent = document.getElementById("processingContent");

    const bookVehicleId = document.getElementById("bookVehicleId");
    const bookDate = document.getElementById("bookDate");
    const bookTime = document.getElementById("bookTime");
    const bookProof = document.getElementById("bookProof");

    let selectedVehicleId = null;

    document.querySelectorAll(".vehicle-card .view-btn").forEach(btn => {
        btn.addEventListener("click", () => {
            const card = btn.closest(".vehicle-card");
            if (!card) return;

            selectedVehicleId = card.dataset.carId || "";
            const brand = card.dataset.brand || "";
            const model = card.dataset.model || "";
            const rate = card.dataset.rate || "0.00";
            const seats = card.dataset.seats || "5";
            const transmission = card.dataset.transmission || "Automatic";
            const fuel = card.dataset.fuel || "Gasoline";
            const status = card.dataset.status || "Available";
            const image = card.dataset.image || "";

            carImage.src = image;
            carName.textContent = `${brand} ${model}`.trim();
            carPrice.innerHTML = "₱" + rate + ' <span>per day</span>';

            carFeatures.innerHTML = `
                <li><strong>Brand:</strong> ${brand}</li>
                <li><strong>Model:</strong> ${model}</li>
                <li><strong>Seats:</strong> ${seats}</li>
                <li><strong>Transmission:</strong> ${transmission}</li>
                <li><strong>Fuel:</strong> ${fuel}</li>
                <li><strong>Status:</strong> ${status}</li>
            `;

            carDetailsPopup.style.display = "flex";
        });
    });

    closeCarDetails.addEventListener("click", () => {
        carDetailsPopup.style.display = "none";
    });

    closeBookingModal.addEventListener("click", () => {
        bookingModal.style.display = "none";
    });

    bookNowBtn.addEventListener("click", () => {
        if (!selectedVehicleId) return;

        bookVehicleId.value = selectedVehicleId;
        bookDate.value = "";
        bookTime.value = "";
        bookProof.value = "";

        carDetailsPopup.style.display = "none";
        bookingModal.style.display = "flex";
    });

    window.addEventListener("click", (e) => {
        if (e.target === carDetailsPopup) {
            carDetailsPopup.style.display = "none";
        }

        if (e.target === bookingModal) {
            bookingModal.style.display = "none";
        }
    });

    bookNowForm.addEventListener("submit", (e) => {
        if (!bookDate.value || !bookTime.value || !bookProof.value) {
            e.preventDefault();
            alert("Please select booking date, time, and upload a file or image.");
            return;
        }

        e.preventDefault();

        bookingModal.style.display = "none";
        processingModal.style.display = "flex";

        processingContent.innerHTML = `
            <h3>Your transaction is being processed.</h3>
            <p>Please wait...</p>
        `;

        setTimeout(() => {
            processingContent.innerHTML = `
                <h3>Your transaction is being processed.</h3>
                <p>Please wait...</p>
                <button id="okBtn" class="ok-btn" type="button">OK</button>
            `;

            document.getElementById("okBtn").addEventListener("click", () => {
                bookNowForm.submit();
            });
        }, 1500);
    });
});
</script>

</body>
</html>