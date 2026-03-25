<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Billings | Rafakijay Car Rental</title>
  <link rel="stylesheet" href="{{ asset('assets/css/billings.css') }}">
</head>
<body>

<!-- Background -->
<div class="background"></div>

<div class="menu-container">
  <!-- Sidebar -->
  <aside class="sidebar">
    <img src="{{ asset('assets/images/logocar.png.png') }}" class="logo">

    <ul>
      <li><img src="{{ asset('assets/images/dashboard.png.png') }}"><a href="{{ url('admin/dashboard') }}">Dashboard</a></li>
      <li><img src="{{ asset('assets/images/cars.png.png') }}"><a href="{{ url('vehicles') }}">Vehicles</a></li>
      <li><img src="{{ asset('assets/images/annotation.png') }}"><a href="{{ url('rent') }}">Rent</a></li>
      <li class="active"><img src="{{ asset('assets/images/cash.png') }}"><a class="active">Billings</a></li>
      <li><img src="{{ asset('assets/images/profile2.png') }}"><a href="{{ url('ownerslist') }}">Ownerslist</a></li>
    </ul>
  </aside>

  <!-- Main Content -->
  <main class="main-content">
    <header>
      <h1>Billings</h1>
    </header>

    <section class="content-section">

      <!-- Search -->
      <div class="search-bar">
        <input type="text" placeholder="Search..." id="searchInput">
      </div>

      <!-- Table -->
      <table id="billingTable">
        <thead>
          <tr>
            <th>ID</th>
            <th>Customer</th>
            <th>Vehicle</th>
            <th>Total Amount</th>
            <th>Bill Date</th>
          </tr>
        </thead>
        <tbody>
          @foreach($billings as $billing)
          <tr data-id="{{ $billing->id }}">
            <td>{{ $billing->id }}</td>
            <td>{{ $billing->customer }}</td>
            <td>{{ $billing->vehicle }}</td>
            <td>{{ number_format($billing->total_amount, 2) }}</td>
            <td>{{ date('m/d/Y', strtotime($billing->bill_date)) }}</td>
          </tr>
          @endforeach
        </tbody>
      </table>

      <!-- Action -->
      <div class="action-buttons">
        <button id="deleteBtn">Delete</button>
      </div>

      <!-- Hidden delete form -->
      <form id="deleteForm" method="POST" style="display:none;">
        @csrf
        @method('DELETE')
      </form>

    </section>
  </main>
</div>

<script>
let selectedRow = null;

/* ROW SELECTION (SAME AS OWNERSLIST) */
document.querySelectorAll("#billingTable tbody tr").forEach(row => {
  row.addEventListener("click", () => {
    document.querySelectorAll("#billingTable tbody tr")
      .forEach(r => r.classList.remove("selected-row"));

    row.classList.add("selected-row");
    selectedRow = row;
  });
});

/* DELETE (SAME AS OWNERSLIST) */
document.getElementById("deleteBtn").addEventListener("click", () => {
  if (!selectedRow) {
    alert("Please select a billing to delete.");
    return;
  }

  if (confirm("Are you sure you want to delete this billing?")) {
    const id = selectedRow.dataset.id;
    const form = document.getElementById("deleteForm");
    form.action = `/billing/${id}`;
    form.submit();
  }
});

/* SEARCH */
document.getElementById("searchInput").addEventListener("keyup", function () {
  const filter = this.value.toLowerCase();
  document.querySelectorAll("#billingTable tbody tr").forEach(row => {
    row.style.display = row.textContent.toLowerCase().includes(filter) ? "" : "none";
  });
});
</script>

</body>
</html>
