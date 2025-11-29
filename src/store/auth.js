import Vue from 'vue'
import Vuex from 'vuex'

Vue.use(Vuex)

export default new Vuex.Store({
  state: {
    username: null,
    isAuthenticated: false,
    authChecked: false
  },
  mutations: {
    setUser (state, username) {
      state.username = username
      state.isAuthenticated = !!username
    },
    clearUser (state) {
      state.username = null
      state.isAuthenticated = false
    }
  },
  actions: {
    async checkAuthStatus ({ commit }) {
      try {
        const response = await fetch('/api/auth/checkAuthStatus', {
          credentials: 'include'
        })
        const data = await response.json()
        this.authChecked = true

        if (data.result && data.result.authenticated) {
          commit('setUser', data.result.username)
        } else {
          commit('clearUser')
        }
      } catch (error) {
        console.error('Auth check failed:', error)
        commit('clearUser')
        throw error
      }
    },
    async logout ({ commit }) {
      try {
        await fetch('/api/auth/logout', {
          method: 'POST',
          credentials: 'include'
        })
        commit('clearUser')
      } catch (error) {
        console.error('Logout failed:', error)
        throw error
      }
    }
  }
//   getters: {
//     currentUser: state => state.user,
//     isAuthenticated: state => state.isAuthenticated
//   }
})
