// js/login.js

document.addEventListener("DOMContentLoaded", () => {
  const loginForm = document.querySelector(".login-box form");

  loginForm.addEventListener("submit", (event) => {
    event.preventDefault(); // Stop default form submission

    // Get the form values
    const email = loginForm.querySelector('input[type="email"]').value.trim();
    const password = loginForm.querySelector('input[type="password"]').value;

    // Simple validation check
    if (email === "" || password === "") {
      alert("Please enter both email and password.");
      return;
    }

    // ✅ Check against registered users in localStorage
    const users = JSON.parse(localStorage.getItem("users")) || [];
    const user = users.find(u => u.email === email && u.password === password);

    if (user) {
      alert(`Welcome back, ${user.name}!`);
      window.location.href = "dashboard.html"; // Redirect to your dashboard
    } else {
      alert("Invalid email or password. Please try again.");
    }
  });

  // Optional: handle "Sign up" link if you add one
  const signupLink = document.getElementById("signupLink");
  if (signupLink) {
    signupLink.addEventListener("click", (event) => {
      event.preventDefault();
      window.location.href = "signup.html";
    });
  }
});
