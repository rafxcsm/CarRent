import { createRouter, createWebHistory } from 'vue-router'

/* ================= ADMIN ================= */
import AdminVehicles from './Admin/vehicles.vue'
import AdminDashboard from './Admin/dashboard.vue'
import LoginPage from './Admin/login.vue'
import AdminRent from './Admin/rent.vue'
import AdminOwnerslist from './Admin/ownerslist.vue'
import AdminBusiness from './Admin/business.vue'
import AdminStaff from './Admin/staff.vue'
import AdminLayout from './Layouts/AdminLayout.vue'
import SuperAdminLogin from './SuperAdmin/login.vue'
import SuperAdminDashboard from './SuperAdmin/dashboard.vue'
import SuperAdminClients from './SuperAdmin/clients.vue'

/* ================= USER ================= */
import UserLayout from './Layouts/UserLayout.vue'
import UserLogin from './User/login.vue'
import UserSignUp from './User/signup.vue'
import UserVehicles from './User/vehicles.vue'
import UserReceipts from './User/receipts.vue'

/* ================= ROUTES ================= */
const routes = [

  /* PLATFORM SUPER ADMIN */
  {
    path: '/super-admin/login',
    name: 'super-admin.login',
    component: SuperAdminLogin,
  },
  {
    path: '/super-admin/dashboard',
    name: 'super-admin.dashboard',
    component: SuperAdminDashboard,
  },
  {
    path: '/super-admin/clients',
    name: 'super-admin.clients',
    component: SuperAdminClients,
  },

  /* 🔴 ADMIN LOGIN */
  {
    path: '/admin/login',
    name: 'admin-login',
    component: LoginPage,
  },

  /* 🔴 ADMIN PANEL */
  {
    path: '/admin',
    component: AdminLayout,
    children: [
      {
        path: 'dashboard',
        name: 'admin.dashboard',
        component: AdminDashboard,
      },
      {
        path: 'vehicles',
        name: 'admin.vehicles',
        component: AdminVehicles,
      },
      {
        path: 'rent',
        name: 'admin.rent',
        component: AdminRent,
      },
      {
        path: 'ownerslist',
        name: 'admin.ownerslist',
        component: AdminOwnerslist,
      },
      {
        path: 'business',
        name: 'admin.business',
        component: AdminBusiness,
      },
      {
        path: 'staff',
        name: 'admin.staff',
        component: AdminStaff,
      },
    ],
  },

  /* 🔵 USER SYSTEM */
  {
    path: '/user',
    component: UserLayout,
    children: [
      {
        path: '',
        name: 'user.login',
        component: UserLogin,
      },
      {
        path: 'signup',
        name: 'user.signup',
        component: UserSignUp,
      },
      {
        path: 'vehicles',
        name: 'user.vehicles',
        component: UserVehicles,
      },
      {
        path: 'receipts',
        name: 'user.receipts',
        component: UserReceipts,
      },
    ],
  },
]

/* ================= ROUTER ================= */
const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior() {
    return { top: 0, left: 0 }
  },
})

/* ================= AUTH GUARD ================= */
router.beforeEach((to, from, next) => {

  // ✅ USER TOKEN
  const userToken = sessionStorage.getItem('token')

  // ✅ ADMIN TOKEN (SESSION ONLY)
  const adminToken = sessionStorage.getItem('admin_token')
  const superAdminToken = sessionStorage.getItem('super_admin_token')
  let adminSession = null
  try { adminSession = JSON.parse(sessionStorage.getItem('admin') || 'null') } catch { adminSession = null }

  if (['super-admin.dashboard', 'super-admin.clients'].includes(to.name) && !superAdminToken) {
    return next('/super-admin/login')
  }

  if (to.name === 'super-admin.login' && superAdminToken) {
    return next('/super-admin/dashboard')
  }

  /* ================= ADMIN ROUTES ================= */

  const adminProtectedRoutes = [
    'admin.dashboard',
    'admin.vehicles',
    'admin.rent',
    'admin.ownerslist',
    'admin.business',
    'admin.staff',
  ]

  // 🚫 BLOCK ADMIN PAGES IF NOT LOGGED IN
  if (adminProtectedRoutes.includes(to.name) && !adminToken) {
    return next('/admin/login')
  }

  if (to.name === 'admin-login' && adminToken) {
    return next('/admin/dashboard')
  }

  const adminOnlyRoutes = ['admin.vehicles', 'admin.ownerslist', 'admin.business', 'admin.staff']
  if (adminOnlyRoutes.includes(to.name) && adminSession?.role === 'staff') {
    return next('/admin/dashboard')
  }

  /* ================= USER ROUTES ================= */

  const userProtectedRoutes = [
    'user.vehicles',
    'user.receipts',
  ]

  // 🚫 BLOCK USER PAGES IF NOT LOGGED IN
  if (userProtectedRoutes.includes(to.name) && !userToken) {
    return next('/user')
  }

  // ✅ ALLOW ACCESS
  next()
})

export default router
