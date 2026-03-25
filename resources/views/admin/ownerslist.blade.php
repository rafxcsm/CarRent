<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Owner's List | Rafakijay Car Rental</title>
  <link rel="stylesheet" href="{{ asset('assets/css/ownerslist.css') }}">
  <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
  <!-- Background -->
  <div class="background"></div>

    <!-- Main Content -->
    <main class="main-content">
      <header>
        <h1>Owners List</h1>
        <div class="icons">
          <img src="{{ asset('assets/images/profile.png.png') }}" alt="Profile">
        </div>
      </header>

      <section class="content-section">
          <div class="search-bar">
  <input type="text" id="searchInput" placeholder="Search...">
</div>
        <!-- Owners Table -->
        <table>
          <thead>
            <tr>
              <th>ID</th>
              <th>Full Name</th>
              <th>Gender</th>
              <th>Birthdate</th>
              <th>Contact No.</th>
              <th>Address</th>
              <th>License No.</th>
              <th>Status</th>
              <th>Date Created</th>
            </tr>
          </thead>
          <tbody>
            @foreach($owners as $owner)
            <tr data-id="{{ $owner->id }}">
              <td>{{ $owner->id }}</td>
              <td>{{ $owner->full_name }}</td>
              <td>{{ $owner->gender }}</td>
              <td>{{ $owner->birthdate }}</td>
              <td>{{ $owner->contact_no }}</td>
              <td>{{ $owner->address }}</td>
              <td>{{ $owner->license_no }}</td>
              <td>{{ $owner->status }}</td>
              <td>{{ $owner->created_at->format('m/d/Y') }}</td>
            </tr>
            @endforeach
          </tbody>
        </table>

        <div class="action-buttons">
          <button id="deleteBtn">Delete</button>
        </div>

        <form id="deleteForm" method="POST" style="display:none;">
          @csrf
          @method('DELETE')
        </form>
      </section>
    </main>
 
  <!-- Delete Modal -->
  <div id="deleteModal" class="modal">
    <div class="modal-content small">
      <h3>Are you sure you want to delete this owner?</h3>
      <div class="modal-buttons">
        <button id="confirmDelete">Yes</button>
        <button class="closeBtn">Cancel</button>
      </div>
    </div>
  </div>

  <!-- Logout Modal -->
  <div id="logoutModal" class="modal">
    <div class="modal-content small">
      <img src="{{ asset('assets/images/profile.png.png') }}" style="width:80px; margin:0 auto; border-radius:50%;">
      <h3>Are you sure you want to log out?</h3>
      <div class="modal-buttons">
        <button id="confirmLogout">Log Out</button>
        <button class="closeBtn">Cancel</button>
      </div>
    </div>
  </div>

  <script>
    let selectedRow = null;

    // Select row
    document.querySelectorAll("table tbody tr").forEach(row => {
        row.addEventListener("click", () => {
            document.querySelectorAll("table tbody tr").forEach(r => r.classList.remove("selected-row"));
            row.classList.add("selected-row");
            selectedRow = row;
        });
    });

    // Delete button
    document.getElementById("deleteBtn").addEventListener("click", () => {
        if (!selectedRow) { alert("Please select an owner to delete."); return; }

        if (confirm("Are you sure you want to delete this owner?")) {
            let ownerId = selectedRow.dataset.id;
            let form = document.getElementById("deleteForm");
            form.action = `/owners/${ownerId}`;
            form.submit();
        }
    });


/* SEARCH (OWNERS LIST FIXED) */
document.getElementById("searchInput").addEventListener("keyup", function () {
  const filter = this.value.toLowerCase();

  document.querySelectorAll("table tbody tr").forEach(row => {
    row.style.display = row.textContent.toLowerCase().includes(filter)
      ? ""
      : "none";
  });
});
</script>

  </script>

  <script src="{{ asset('js/sidebar.js') }}"></script>
</body>
</html>
