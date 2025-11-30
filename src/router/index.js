import Vue from 'vue'
import VueRouter from 'vue-router'
import authStore from '@/store/auth'
import mainView from '../views/mainView.vue'
import authView from '../views/authView.vue'

Vue.use(VueRouter)

const routes = [
  {
    path: '/auth',
    name: 'auth',
    component: authView,
    meta: { requiresAuth: true }
  },
  {
    path: '/',
    name: 'main',
    component: mainView,
    meta: { requiresAuth: true }
  },
  {
    path: '/game/:id',
    name: 'game',
    component: mainView,
    meta: { requiresAuth: true }
  },
  {
    path: '/about',
    name: 'about',
    // route level code-splitting
    // this generates a separate chunk (about.[hash].js) for this route
    // which is lazy-loaded when the route is visited.
    component: () => import(/* webpackChunkName: "about" */ '../views/AboutView.vue')
  }
]

const router = new VueRouter({
  mode: 'history',
  routes
})

// Глобальный хук для проверки авторизации
router.beforeEach(async (to, from, next) => {
  if (to.meta.requiresAuth && !authStore.state.authChecked) {
    await authStore.dispatch('checkAuthStatus')
  }
  if (to.name !== 'auth' && to.meta.requiresAuth && !authStore.state.isAuthenticated) {
    next('/auth')
  } else if (to.name === 'auth' && authStore.state.isAuthenticated) {
    next('/')
  } else {
    next()
  }
})

export default router
