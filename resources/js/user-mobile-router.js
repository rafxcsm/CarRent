import { createRouter, createWebHashHistory } from 'vue-router'
import UserLayout from './Layouts/UserLayout.vue'
import UserLogin from './User/login.vue'
import UserSignUp from './User/signup.vue'
import UserVehicles from './User/vehicles.vue'
import UserReceipts from './User/receipts.vue'

const router = createRouter({
  history: createWebHashHistory(),
  routes: [
    { path: '/', redirect: '/user' },
    {
      path: '/user',
      component: UserLayout,
      children: [
        { path: '', name: 'user.login', component: UserLogin },
        { path: 'signup', name: 'user.signup', component: UserSignUp },
        { path: 'vehicles', name: 'user.vehicles', component: UserVehicles },
        { path: 'receipts', name: 'user.receipts', component: UserReceipts },
      ],
    },
    { path: '/:pathMatch(.*)*', redirect: '/user' },
  ],
})

router.beforeEach((to) => {
  const protectedUserRoutes = ['user.vehicles', 'user.receipts']
  if (protectedUserRoutes.includes(to.name) && !sessionStorage.getItem('token')) {
    return '/user'
  }
})

export default router
