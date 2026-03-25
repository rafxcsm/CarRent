<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Vehicles | Rafakijay Car Rental</title>
  <link rel="stylesheet" href="{{asset('assets/css/vehicles.css')}}" />
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
</head>

<body>
  <div class="background"></div>
      <section class="content-section">
        <div class="search-bar">
          <input type="text" placeholder="Search...">
        </div>

     <table id="vehicleTable">
    <thead>
        <tr>
            <th>Reg No</th>
            <th>Brand</th>
            <th>Color</th>
            <th>Model</th>
            <th>Rate</th>
            <th>Status</th>
            <th>Created</th>
        </tr>
    </thead>

    <tbody>
        @foreach ($vehicles as $v)
            <tr data-id="{{ $v->id }}">
                <td>{{ $v->reg_no }}</td>
                <td>{{ $v->brand }}</td>
                <td>{{ $v->color }}</td>
                <td>{{ $v->model }}</td>
                <td>₱{{ number_format($v->rate) }}</td>
                <td>{{ $v->status }}</td>
                <td>{{ $v->created_at->format('m/d/Y') }}</td>
            </tr>
        @endforeach
    </tbody>
</table>


        <div class="action-buttons">
          <button id="addBtn">Add</button>
          <button id="deleteBtn">Delete</button>
          <button id="updateBtn">Update</button>
        </div>
      </section>
    </main>
  </div>
<!-- ADD / UPDATE VEHICLE MODAL -->
<div id="vehicleModal" class="modal">
    <div class="modal-content">
        <h2 id="modalTitle">Add New Vehicle</h2>

        <form id="vehicleForm" method="POST" action="{{ route('vehicles.store') }}" enctype="multipart/form-data">
            @csrf
            <input type="hidden" id="formMethod" name="_method" value="POST">

            <label>Reg. No:</label>
            <input type="text" id="regNo" name="reg_no" required>

            <label>Brand:</label>
            <input type="text" id="brand" name="brand" required>

            <label>Color:</label>
            <input type="text" id="color" name="color" required>

            <label>Model:</label>
            <input type="text" id="model" name="model" required>

            <label>Rental Rate:</label>
            <input type="number" id="rate" name="rate" required>

            <label>Status:</label>
            <select id="status" name="status" required>
                <option value="Available">Available</option>
                <option value="Rented">Rented</option>
            </select>

            <!-- NEW UPLOAD FIELD -->
            <label>Vehicle Photo:</label>
            <input type="file" id="image" name="image" accept="image/*">

            <div class="modal-buttons">
                <button type="submit">Save</button>
                <button type="button" class="closeBtn">Cancel</button>
            </div>
        </form>
    </div>
</div>

 <!-- DELETE VEHICLE MODAL -->
<div id="deleteModal" class="modal">
  <div class="modal-content small">
    <h3>Delete this vehicle?</h3>

    <div class="modal-buttons">
      <form id="deleteForm" method="POST">
        @csrf
        @method('DELETE')
        <button type="submit" style="background:red;color:white;">
          Delete
        </button>
      </form>

      <button class="closeBtn">Cancel</button>
    </div>
  </div>
</div>



 <!-- LOGOUT MODAL -->
<div id="logoutModal" class="modal">
  <div class="modal-content small">
    <img src="{{ asset('assets/images/profile.png.png') }}"
         style="width:80px;margin:0 auto;border-radius:50%;">

    <h3>Are you sure you want to log out?</h3>

    <div class="modal-buttons">
      <form method="POST" action="{{ route('admin.logout') }}">
        @csrf
        <button type="submit" class="danger">Log Out</button>
      </form>

      <button class="closeBtn">Cancel</button>
    </div>
  </div>
</div>


<script>
/* ========================
     VEHICLE TABLE SELECT
======================== */
let selectedRow = null;
document.querySelector("#vehicleTable tbody").addEventListener("click", e => {
  const row = e.target.closest("tr");
  if (!row) return;
  document.querySelectorAll("#vehicleTable tbody tr").forEach(r => r.classList.remove("selected"));
  row.classList.add("selected");
  selectedRow = row;
});

/* ========================
     LIVE SEARCH
======================== */
document.querySelector(".search-bar input").addEventListener("keyup", function () {
  const filter = this.value.toLowerCase();
  document.querySelectorAll("#vehicleTable tbody tr").forEach(row => {
    row.style.display = row.textContent.toLowerCase().includes(filter) ? "" : "none";
  });
});

/* ========================
     ADD VEHICLE
======================== */
const vehicleModal = document.getElementById("vehicleModal");
const deleteModal = document.getElementById("deleteModal");
const vehicleForm = document.getElementById("vehicleForm");
const modalTitle = document.getElementById("modalTitle");
const formMethod = document.getElementById("formMethod");


let editMode = false;

document.getElementById("addBtn").onclick = () => {
    editMode = false;
    modalTitle.textContent = "Add New Vehicle";

    vehicleForm.action = "/vehicles";
    formMethod.value = "POST";

    vehicleForm.reset();
    vehicleModal.style.display = "flex";
};


/* ========================
     UPDATE VEHICLE
======================== */
document.getElementById("updateBtn").onclick = () => {
    if (!selectedRow) return alert("⚠️ Select vehicle first");

    editMode = true;
    modalTitle.textContent = "Update Vehicle";

    let id = selectedRow.dataset.id;
    vehicleForm.action = `/vehicles/${id}`;
    formMethod.value = "PUT";

    const c = selectedRow.children;
    regNo.value = c[0].textContent;
    brand.value = c[1].textContent;
    color.value = c[2].textContent;
    model.value = c[3].textContent;
    rate.value = c[4].textContent.replace("₱", "");
    status.value = c[5].textContent;

    vehicleModal.style.display = "flex";
};


/* ========================
     SAVE VEHICLE
======================== */

/* ========================
     DELETE VEHICLE
======================== */
document.getElementById("deleteBtn").onclick = () => {
  if (!selectedRow) return alert("⚠️ Select a vehicle first");

  let id = selectedRow.dataset.id;
  document.getElementById("deleteForm").action = `/vehicles/${id}`;

  deleteModal.style.display = "flex";
};



const profileIcon = document.getElementById("profileIcon");
const logoutModal = document.getElementById("logoutModal");
const confirmLogout = document.getElementById("confirmLogout");

profileIcon.addEventListener("click", () => {
  logoutModal.style.display = "flex";
});


// Close all modals
document.querySelectorAll(".closeBtn").forEach(btn => {
  btn.addEventListener("click", () => {
    vehicleModal.style.display = "none";
    deleteModal.style.display = "none";
    logoutModal.style.display = "none";
  });
});

// Click outside to close
window.addEventListener("click", (e) => {
  if (e.target === vehicleModal) vehicleModal.style.display = "none";
  if (e.target === deleteModal) deleteModal.style.display = "none";
  if (e.target === logoutModal) logoutModal.style.display = "none";
});
</script> 

</body>
</html>
