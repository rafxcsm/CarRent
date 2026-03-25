<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard | Rafakijay Car Rental</title>
  <link rel="stylesheet" href="{{asset(path: 'assets/css/user/dashboard.css')}}">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
</head>

<body>
  <div class="background"></div>

  <div class="menu-container">
    <!-- Sidebar -->
    <aside class="sidebar">
      <img src="{{asset(path: 'assets/images/logocar.png.png')}}" alt="Logo" class="logo">
       <ul>
        <li><img src="{{asset(path: 'assets/images/dashboard.png.png')}}" alt=""><a href=""class="active">Dashboard</a></li>
        <li><img src="{{asset(path: 'assets/images/user/images/profile2.png.png')}}" alt=""><a href="{{ url (path: 'profile')}}">Profile</a></li>
        <li><img src="{{asset(path: 'assets/images/cars.png.png')}}" alt=""><a href="{{ url (path: 'user-vehicles')}}">Vehicles</a></li>
        <li><img src="{{asset(path: 'assets/images/receipts.png.png')}}" alt=""><a href="{{ url (path: 'receipts')}}">Receipts</a></li>
      </ul>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
      <header>
        <h1>Dashboard</h1>
        <div class="icons">
          <img src="{{asset(path: 'assets/images/notification.png.png')}}" alt="Notifications">
          <img src="{{asset(path: 'assets/images/profile.png.png')}}" alt="Profile">
        </div>
      </header>

      <section class="dashboard-cards">
        <div class="card">
          <div class="circle" style="--value:90;"></div>
          <h3>90%</h3>
          <p>Total Customers</p>
        </div>

        <div class="card">
          <div class="circle" style="--value:80;"></div>
          <h3>80%</h3>
          <p>Daily Vehicles Summary</p>
        </div>

        <div class="card">
          <div class="circle" style="--value:95;"></div>
          <h3>95%</h3>
          <p>Daily Sales Summary</p>
        </div>
      </section>
    </main>
  </div>

  <!-- === NOTIFICATION POPUP === -->
  <div id="notificationPopup" class="notif-popup">
    <div class="notif-container">
      <header>
        <h2>Notifications</h2>
      </header>

      <section class="notification-panel">
        <div class="notification">
          <img src="images/notification.png.png" alt="Notification" class="notif-icon">
          <p>
            Dear Customer, you have successfully booked your transaction. 
            <a href="receipts.html">Click here to see your receipt.</a>
          </p>
        </div>
      </section>
    </div>
  </div>


  <!-- ✅ PROFILE INFORMATION & TRANSACTION HISTORY POPUP -->
  <div id="profileInfoPopup" class="profile-info-popup" 
    style="display:none; position:fixed; top:0; left:0; width:100%; height:100%;
    background:rgba(0,0,0,0.6); justify-content:center; align-items:center; z-index:9999;">
    
    <div class="popup-content" 
      style="background:#fff; padding:20px; border-radius:10px; width:400px; color:#000; max-height:80vh; overflow-y:auto;">
      
      <span id="closeProfileInfo" class="close-btn" 
        style="float:right; cursor:pointer; font-size:20px;">&times;</span>
      
      <h2 style="margin-top:0;"> User Profile</h2>

<button id="editProfileBtn" 
  style="margin:10px 0 20px; padding:8px 12px; background:#ffd700; color:black; border:none; border-radius:5px; cursor:pointer; font-weight:600;">
  ✏️ Edit Profile
</button>

<div id="userProfileDetails" style="margin-bottom:20px;">

        <p><strong>Name:</strong> <span id="profileName">Not set</span></p>
        <p><strong>Email:</strong> <span id="profileEmail">Not set</span></p>
        <p><strong>Phone:</strong> <span id="profilePhone">Not set</span></p>
        <p><strong>Address:</strong> <span id="profileAddress">Not set</span></p>
      </div>

      <!-- === EDIT PROFILE POPUP === -->
<div id="editProfilePopup" 
  style="display:none; position:fixed; top:0; left:0; width:100%; height:100%;
  background:rgba(0,0,0,0.6); justify-content:center; align-items:center; z-index:10000;">

  <div style="background:white; color:black; padding:20px; border-radius:10px; width:380px;">
    
    <span id="closeEditProfile" style="float:right; cursor:pointer; font-size:22px;">&times;</span>
    <h2>Edit Profile</h2>

    <form id="editProfileForm" style="margin-top:15px;">
      <label>Name</label>
      <input type="text" id="editName" style="width:100%; padding:8px; margin-bottom:10px;">

      <label>Email</label>
      <input type="email" id="editEmail" style="width:100%; padding:8px; margin-bottom:10px;">

      <label>Phone</label>
      <input type="text" id="editPhone" style="width:100%; padding:8px; margin-bottom:10px;">

      <label>Address</label>
      <input type="text" id="editAddress" style="width:100%; padding:8px; margin-bottom:20px;">

      <button type="submit" 
        style="width:100%; padding:10px; background:black; color:white; border:none; border-radius:5px; cursor:pointer;">
        💾 Save Changes
      </button>
    </form>
  </div>
</div>


      <h3>🧾 Transaction History</h3>
      <div id="transactionHistory" style="background:#f7f7f7; padding:10px; border-radius:5px;">
        <p>No transactions yet.</p>
      </div>

      <button id="logoutBtn" 
        style="margin-top:20px; width:100%; padding:10px; background:black; color:white; border:none; border-radius:5px; cursor:pointer;">
        🚪 Log Out
      </button>
    </div>
  </div>


  <!-- === JAVASCRIPT (ALWAYS AT THE BOTTOM) === -->
  <script src="js/dashboard.js"></script>

  <script>
    // === Notification Popup ===
    const notifBtn = document.querySelector('img[alt="Notifications"]');
    const notifPopup = document.getElementById('notificationPopup');
    const profilePopup = document.getElementById('profilePopup'); // (not used, but kept for compatibility)

    notifBtn.addEventListener('click', (e) => {
      e.stopPropagation();
      notifPopup.style.display = notifPopup.style.display === 'flex' ? 'none' : 'flex';
      if (profilePopup) profilePopup.style.display = 'none';
    });

    window.addEventListener('click', (e) => {
      if (e.target === notifPopup) notifPopup.style.display = 'none';
    });

    // === PROFILE INFO MODAL LOGIC ===
    const profileInfoPopup = document.getElementById('profileInfoPopup');
    const closeProfileInfo = document.getElementById('closeProfileInfo');
    const logoutBtn = document.getElementById('logoutBtn');

    // When clicking the profile icon (top right)
    document.querySelector('img[alt="Profile"]').addEventListener('click', (e) => {
      e.stopPropagation();
      loadProfileInfo();
      loadTransactionHistory();
      profileInfoPopup.style.display = 'flex';
    });

    // Close the popup
    closeProfileInfo.addEventListener('click', () => {
      profileInfoPopup.style.display = 'none';
    });
    window.addEventListener('click', (e) => {
      if (e.target === profileInfoPopup) profileInfoPopup.style.display = 'none';
    });

    // Load stored user profile data
    function loadProfileInfo() {
      const profile = JSON.parse(localStorage.getItem('userProfile')) || {};
      document.getElementById('profileName').textContent = profile.name || "Not set";
      document.getElementById('profileEmail').textContent = profile.email || "Not set";
      document.getElementById('profilePhone').textContent = profile.phone || "Not set";
      document.getElementById('profileAddress').textContent = profile.address || "Not set";
    }

    // Load transaction history (bookings and rentals)
    function loadTransactionHistory() {
      const receipts = JSON.parse(localStorage.getItem('receipts')) || [];
      const container = document.getElementById('transactionHistory');
      container.innerHTML = "";

      if (receipts.length === 0) {
        container.innerHTML = "<p>No transactions yet.</p>";
        return;
      }

      receipts.forEach(r => {
        const div = document.createElement('div');
        div.style.borderBottom = "1px solid #ccc";
        div.style.padding = "5px 0";
        div.innerHTML = `
          <p><strong>${r.type}</strong> - ${r.car}</p>
          <p>Date: ${r.date} | Time: ${r.time}</p>
          <p><em>User: ${r.user}</em></p>
        `;
        container.appendChild(div);
      });
    }

    // Logout button
    logoutBtn.addEventListener('click', () => {
      if (confirm("Are you sure you want to log out?")) {
        localStorage.clear();
        alert("You have been logged out.");
        window.location.href = "login";
      }
    });

    // === EDIT PROFILE MODAL ===
const editProfileBtn = document.getElementById("editProfileBtn");
const editProfilePopup = document.getElementById("editProfilePopup");
const closeEditProfile = document.getElementById("closeEditProfile");
const editForm = document.getElementById("editProfileForm");

// Open edit profile modal
editProfileBtn.addEventListener("click", () => {
  const profile = JSON.parse(localStorage.getItem('userProfile')) || {};

  document.getElementById("editName").value = profile.name || "";
  document.getElementById("editEmail").value = profile.email || "";
  document.getElementById("editPhone").value = profile.phone || "";
  document.getElementById("editAddress").value = profile.address || "";

  editProfilePopup.style.display = "flex";
});

// Close popup
closeEditProfile.addEventListener("click", () => {
  editProfilePopup.style.display = "none";
});

window.addEventListener("click", (e) => {
  if (e.target === editProfilePopup) editProfilePopup.style.display = "none";
});

// Save updated profile
editForm.addEventListener("submit", (e) => {
  e.preventDefault();

  const updatedProfile = {
    name: document.getElementById("editName").value,
    email: document.getElementById("editEmail").value,
    phone: document.getElementById("editPhone").value,
    address: document.getElementById("editAddress").value
  };

  localStorage.setItem("userProfile", JSON.stringify(updatedProfile));

  alert("Profile updated successfully!");

  loadProfileInfo(); // refresh display
  editProfilePopup.style.display = "none";
});


  </script>
</body>
</html>
