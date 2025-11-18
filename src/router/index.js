import Vue from 'vue'
import VueRouter from 'vue-router'
import mainView from '../views/mainView.vue'

Vue.use(VueRouter)

// TODO: подумать про разводку страницы создания комнаты и игры
const routes = [
  {
    path: '/',
    name: 'main',
    component: mainView
  },
  {
    path: '/game/:id',
    name: 'game',
    component: mainView
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

export default router
