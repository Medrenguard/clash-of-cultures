<template>
    <div>
        <label for="username">Имя пользователя:</label><br>
        <input type="text" v-model="form.username" id="username" @input="clearFieldError('username')">
        <span style="color: red"> {{ errorForm.username }}</span>
        <br>
        <label for="password">Пароль:</label><br>
        <input type="password" v-model="form.password" id="password" @input="clearFieldError('password')">
        <span style="color: red"> {{ errorForm.password }}</span>
        <br><br>
        <button @click="handleLogin" style="height: 1.5rem" :disabled="loading">Войти</button>
        <div style="color: red"> {{ errorForm.auth }}</div>
        <div  v-if="loading"> Загрузка... </div>
        <div style="color: green" v-if="authSuccessful"> Вход успешен!<br>Сейчас будет выполнена переадресация... </div>
    </div>
</template>

<script>
export default {
  name: 'authView',
  data () {
    return {
      form: {
        username: '',
        password: ''
      },
      errorForm: {
        username: '',
        password: '',
        auth: ''
      },
      loading: false,
      authSuccessful: false
    }
  },
  mounted () {
  },
  computed: {
  },
  watch: {
  },
  methods: {
    validateForm () {
      let isValid = true

      // Очищаем предыдущие ошибки
      this.errorForm.username = ''
      this.errorForm.password = ''
      // Проверка username
      if (!this.form.username.trim()) {
        this.errorForm.username = 'Имя пользователя обязательно'
        isValid = false
      }
      // Проверка password
      if (!this.form.password) {
        this.errorForm.password = 'Пароль обязателен'
        isValid = false
      }

      return isValid
    },
    clearFieldError (fieldName) {
      this.errorForm[fieldName] = ''
      this.errorForm.auth = ''
    },
    async handleLogin () {
      try {
        if (!this.validateForm()) {
          return
        }
        this.loading = true
        this.authSuccessful = false
        this.errorForm.auth = ''
        const data = await this.$authStore.dispatch('handleLogin', this.form)
        if (data.error) {
          this.errorForm.auth = data.error.message || 'Ошибка сервера'
          return
        }
        if (this.$authStore.state.username) {
          this.authSuccessful = true
          setTimeout(() => {
            this.$router.push('/')
          }, 3000)
        } else {
          this.errorForm.auth = 'Неверное имя пользователя или пароль'
        }
      } catch (error) {
        console.error('Ошибка:', error)
        this.errorForm.auth = 'Ошибка соединения c сервером'
      } finally {
        this.loading = false
      }
    }
  }
}
</script>

<style lang="scss">
</style>
