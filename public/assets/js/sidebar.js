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
