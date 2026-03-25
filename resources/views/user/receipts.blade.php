<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Receipts | Rafakijay Car Rental</title>

  <link rel="stylesheet" href="{{ asset('assets/css/user/receipts.css') }}">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">

  <style>
    .status-badge {
      display: inline-block;
      padding: 6px 12px;
      border-radius: 999px;
      font-size: 12px;
      font-weight: 600;
      text-transform: uppercase;
    }

    .status-pending {
      background: #fff3cd;
      color: #856404;
    }

    .status-approved {
      background: #d4edda;
      color: #155724;
    }

    .status-denied {
      background: #f8d7da;
      color: #721c24;
    }
  </style>
</head>

<body>
<div class="background"></div>


  <main class="main-content">
    <header style="display:flex;justify-content:space-between;align-items:center;">
      <h1>Receipts</h1>
    </header>

    @if(session('success'))
      <div style="margin-bottom:15px;padding:12px;border-radius:8px;background:#e8f7e8;color:#1f7a1f;">
        {{ session('success') }}
      </div>
    @endif

    <section class="receipts-container">
      <div id="receiptsList">
        @forelse($rentals as $rental)
          <div class="receipt-card">
            <h3>Receipt No: R-{{ str_pad($rental->id, 5, '0', STR_PAD_LEFT) }}</h3>

            <div class="receipt-details">
              <h4>Booking Information</h4>
              <p><strong>Date:</strong> {{ \Carbon\Carbon::parse($rental->rental_start)->format('Y-m-d') }}</p>
              <p><strong>Time:</strong> {{ \Carbon\Carbon::parse($rental->rental_start)->format('H:i') }}</p>
              <p>
                <strong>Status:</strong>
                <span class="status-badge status-{{ $rental->status }}">
                  {{ ucfirst($rental->status) }}
                </span>
              </p>

              <hr>

              <h4>Vehicle Details</h4>
              <p><strong>Car:</strong> {{ optional($rental->vehicle)->brand }} {{ optional($rental->vehicle)->model }}</p>
              <p><strong>Rate:</strong> ₱{{ number_format(optional($rental->vehicle)->rate ?? 0, 2) }} / day</p>

              <hr>

              <h4>Payment Receipt</h4>
              <p><strong>File:</strong> {{ $rental->proof_file ?? 'N/A' }}</p>
            </div>
          </div>
        @empty
          <p class="no-receipts">No receipts found.</p>
        @endforelse
      </div>
    </section>
  </main>

</div>

</body>
</html>