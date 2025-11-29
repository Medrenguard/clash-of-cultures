<template>
  <div id="app">
    <!-- TODO: доделать юзер-бар, сделать его скрываевым, оформить красиво, вынести в отдельный компонент -->
     <!-- TODO: сделать везде по проекту нормальные уведомления об ошибках во всплывашках -->
    <template v-if="$authStore.state.isAuthenticated">
      <div class="user-bar">
        <div><b>{{ $authStore.state.username }}</b></div>
        <button @click="logout" class="logout-btn">Выйти</button>
      </div>
    </template>
    <router-view/>
  </div>
</template>

<script>
export default {
  name: 'App',
  methods: {
    async logout () {
      try {
        await this.$authStore.dispatch('logout')
        if (this.$route.path !== '/auth') {
          this.$router.push('/auth')
        }
      } catch (error) {
        console.error('Logout error:', error)
      }
    }
  }
}
</script>

<style lang="scss">
body {
  margin: 0;
}
.user-bar {
  width: auto;
  position: absolute;
  right: 0;
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  padding: 5px 16px;
  gap: 2px;
}
</style>
