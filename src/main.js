import Vue from 'vue'
import App from './App.vue'
import router from './router'
import store from './store'
import authStore from './store/auth'

Vue.config.productionTip = false

new Vue({
  router,
  beforeCreate () {
    Vue.prototype.$authStore = authStore
  },
  store,
  render: h => h(App)
}).$mount('#app')
