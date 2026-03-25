// Wait until the document is fully loaded
document.addEventListener("DOMContentLoaded", () => {
  const menuItems = document.querySelectorAll(".sidebar ul li");

  menuItems.forEach(item => {
    item.addEventListener("click", () => {
      const text = item.textContent.trim().toLowerCase();

      // Remove active class from all
      menuItems.forEach(li => li.classList.remove("active"));
      item.classList.add("active");

      // Navigate based on clicked item
      switch (text) {
        case "dashboard":
          window.location.href = "dashboard.html";
          break;
        case "profile":
          window.location.href = "profile.html";
          break;
        case "vehicles":
          window.location.href = "vehicles.html"; // (optional future page)
          break;
        case "receipts":
          window.location.href = "receipts.html"; // (optional future page)
          break;
        default:
          console.log("No linked page for:", text);
      }
    });
  });
});

