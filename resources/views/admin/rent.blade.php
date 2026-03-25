<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">

<title>Rent | Rafakijay Car Rental</title>

<link rel="stylesheet" href="{{ asset('assets/css/rentturn.css') }}">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">

<style>
.receipt-link{
  color:#007bff;
  cursor:pointer;
  text-decoration:underline;
}

#receiptModal{
  display:none;
  position:fixed;
  top:0;
  left:0;
  width:100%;
  height:100%;
  background:rgba(0,0,0,0.6);
  justify-content:center;
  align-items:center;
  z-index:9999;
}

#receiptModal img{
  max-width:500px;
  max-height:500px;
  border-radius:10px;
  background:#fff;
  padding:10px;
}

.closeReceipt{
  position:absolute;
  top:30px;
  right:40px;
  font-size:30px;
  color:white;
  cursor:pointer;
}

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
<header>
<h1>Rent</h1>
</header>

<section class="content-section">

<table id="rentalTable">
<thead>
<tr>
<th>ID</th>
<th>Customer</th>
<th>Vehicle</th>
<th>Payment Receipt</th>
<th>Status</th>
<th>Action</th>
</tr>
</thead>

<tbody>
@foreach($rentals as $rental)
<tr data-id="{{ $rental->id }}">
<td>{{ $rental->id }}</td>

<td>
{{ optional($rental->customer)->name ?? optional($rental->customer)->full_name ?? 'N/A' }}
</td>

<td>
{{ optional($rental->vehicle)->brand }},
{{ optional($rental->vehicle)->model }},
{{ optional($rental->vehicle)->color }}
</td>

<td>
@if($rental->proof_file)
<span
class="receipt-link"
data-img="{{ asset('uploads/proofs/'.$rental->proof_file) }}"
>
{{ $rental->proof_file }}
</span>
@else
N/A
@endif
</td>

<td class="status-cell">
  <span class="status-badge status-{{ $rental->status }}">
    {{ ucfirst($rental->status) }}
  </span>
</td>

<td>
@if($rental->status === 'pending')
  <button class="approveBtn">Approve</button>
  <button class="denyBtn">Deny</button>
@else
  <span style="color:#777;font-weight:600;">Processed</span>
@endif
</td>

</tr>
@endforeach
</tbody>
</table>

</section>
</main>
</div>

<div id="receiptModal">
<span class="closeReceipt">&times;</span>
<img id="receiptImage">
</div>

<script>
const csrf = document.querySelector('meta[name="csrf-token"]').content;

function bindActionButtons() {
  document.querySelectorAll(".approveBtn").forEach(btn => {
    btn.onclick = function () {
      const row = this.closest("tr");
      const id = row.dataset.id;

      fetch(`/rentals/${id}/approve`, {
        method: "POST",
        headers: {
          "X-CSRF-TOKEN": csrf,
          "Accept": "application/json"
        }
      })
      .then(res => res.json())
      .then(data => {
        if (!data.success) {
          alert(data.message || "Failed to approve rental.");
          return;
        }

        row.querySelector(".status-cell").innerHTML = `
          <span class="status-badge status-approved">Approved</span>
        `;
        this.parentElement.innerHTML = `<span style="color:#777;font-weight:600;">Processed</span>`;
      })
      .catch(() => {
        alert("Something went wrong while approving.");
      });
    };
  });

  document.querySelectorAll(".denyBtn").forEach(btn => {
    btn.onclick = function () {
      const row = this.closest("tr");
      const id = row.dataset.id;

      if (!confirm("Deny this rental?")) return;

      fetch(`/rentals/${id}/deny`, {
        method: "POST",
        headers: {
          "X-CSRF-TOKEN": csrf,
          "Accept": "application/json"
        }
      })
      .then(res => res.json())
      .then(data => {
        if (!data.success) {
          alert(data.message || "Failed to deny rental.");
          return;
        }

        row.querySelector(".status-cell").innerHTML = `
          <span class="status-badge status-denied">Denied</span>
        `;
        this.parentElement.innerHTML = `<span style="color:#777;font-weight:600;">Processed</span>`;
      })
      .catch(() => {
        alert("Something went wrong while denying.");
      });
    };
  });
}

const receiptModal = document.getElementById("receiptModal");
const receiptImage = document.getElementById("receiptImage");

document.querySelectorAll(".receipt-link").forEach(link => {
  link.addEventListener("click", function () {
    receiptImage.src = this.dataset.img;
    receiptModal.style.display = "flex";
  });
});

document.querySelector(".closeReceipt").onclick = () => {
  receiptModal.style.display = "none";
};

window.onclick = (e) => {
  if (e.target === receiptModal) {
    receiptModal.style.display = "none";
  }
};

bindActionButtons();
</script>

</body>
</html>