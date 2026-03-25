import { createRouter, createWebHistory } from 'vue-router'

import AdminVehicles from './Admin/vehicles.vue'
import AdminDashboard from './Admin/dashboard.vue'
import LoginPage from './Admin/login.vue'
import AdminRent from './Admin/rent.vue'
import AdminOwnerslist from './Admin/ownerslist.vue'
import AdminLayout from './Layouts/AdminLayout.vue'

import UserLayout from './Layouts/UserLayout.vue'
import UserLogin from './User/login.vue'
import UserSignUp from './User/signup.vue'
import UserVehicles from './User/vehicles.vue'
import UserReceipts from './User/receipts.vue'

const routes = [
  {
    path: '/admin/login',
    name: 'admin-login',
    component: LoginPage,
  },
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

    ],
  },

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
      }
    ],
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

export default router