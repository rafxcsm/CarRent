import axios from "axios";

const apiBaseUrl = import.meta.env.VITE_API_BASE_URL || "/api";
const apiOrigin = import.meta.env.VITE_API_ORIGIN || window.location.origin;

const api = axios.create({
  baseURL: apiBaseUrl,

  // ✅ REQUIRED FOR LARAVEL SANCTUM SESSION AUTH
  withCredentials: true,

  headers: {
    "Content-Type": "application/json",
    Accept: "application/json",
  },
});

/* ================= CSRF SUPPORT (SANCTUM) ================= */
// ✅ ADD THIS (IMPORTANT FOR SESSION LOGIN FLOW)
api.get("/sanctum/csrf-cookie", {
  baseURL: apiOrigin,
  withCredentials: true,
});

/* ================= REQUEST INTERCEPTOR ================= */
api.interceptors.request.use(
  (config) => {

    // 🔵 USER TOKEN (SESSION STORAGE NOW)
    const userToken = sessionStorage.getItem("token");

    // 🔴 ADMIN TOKENS
    const adminToken = sessionStorage.getItem("admin_token");
    const superAdminToken = sessionStorage.getItem("super_admin_token");

    if (config.url?.startsWith("/super-admin")) {
      if (superAdminToken) {
        config.headers.Authorization = `Bearer ${superAdminToken}`;
      }
    }
    // 🔴 RENTAL ADMIN ROUTES
    else if (config.url?.startsWith("/admin")) {

      if (adminToken) {
        config.headers.Authorization = `Bearer ${adminToken}`;
      }

    }

    // 🔵 USER ROUTES
    else {

      if (userToken) {
        config.headers.Authorization = `Bearer ${userToken}`;
      }

    }

    return config;
  },

  (error) => {
    return Promise.reject(error);
  }
);

/* ================= RESPONSE INTERCEPTOR ================= */
api.interceptors.response.use(

  (response) => response,

  (error) => {

    if (error.response?.status === 401) {

      if (window.location.pathname.startsWith("/super-admin")) {
        sessionStorage.removeItem("super_admin_token");
        sessionStorage.removeItem("super_admin");
        if (window.location.pathname !== "/super-admin/login") {
          window.location.href = "/super-admin/login";
        }
      } else if (window.location.pathname.startsWith("/admin")) {
        sessionStorage.removeItem("admin_token");
        sessionStorage.removeItem("admin");
        if (window.location.pathname !== "/admin/login") {
          window.location.href = "/admin/login";
        }
      } else if (`${window.location.pathname}${window.location.hash}`.includes("/user")) {
        sessionStorage.removeItem("token");
        sessionStorage.removeItem("user");
        const isUserLoginPage = window.location.pathname === "/user"
          || window.location.hash === "#/user";
        if (!isUserLoginPage) {
          if (window.location.hash.startsWith("#")) {
            window.location.hash = "#/user";
          } else {
            window.location.href = "/user";
          }
        }
      }
    }

    return Promise.reject(error);
  }
);

export default api;
