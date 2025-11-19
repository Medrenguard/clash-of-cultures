<template>
  <!-- TODO: сделать кнопку присоединения к игре для гостя(и функцию на бэке), кнопку готовности для гостя(и функцию простановки её), кнопку начала игры(и функцию на бэке) -->
   <!-- Счётчик игроков, их имена, статус готовности, фракция -->
   <!-- На будущее: выбор цвета визуальный и немного украшательств -->
   <!-- На далёкое будущее: выведение особенностей выбираемой фракции -->
    <div>
        <label for="session_name">Название игры:</label><br>
        <input v-if="!iAmInRoom" type="text" v-model="session_name" id="session_name">
        <div v-else> {{ session_name }} </div>
        <br>
        <label for="nickname">Никнейм:</label><br>
        <input type="text" id="nickname" v-model="nickname"><br>
        <label for="faction">Фракция:</label><br>
        <select v-model="faction_id">
          <option v-for="item in factions" :key="item.id" :value="item.id">
            {{ item.name }}
          </option>
        </select><br>
        <label>Цвет на поле:</label><br>
        <select v-model="color_id">
          <option v-for="item in colors" :key="item.id" :value="item.id">
            {{ item.name }}
          </option>
        </select><br><br>

        <button @click="createRoom" :disabled="cantCreateRoom">Создать комнату</button>
        <div v-if="this.redirect_url">
          Ссылка для приглашения друзей =>
          <button @click="copyToClipboardUrl" style="height: 1.5rem">📋 скопировать</button>
        </div>

    </div>
</template>

<script>
export default {
  name: 'joinModal',
  data () {
    return {
      session_name: '',
      nickname: '',
      factions: [],
      faction_id: '',
      colors: [],
      color_id: '',
      redirect_url: ''
    }
  },
  mounted () {
    if (this.iAmInRoom) {
      this.getSession()
    }
    this.getFreeFactions()
    this.getFreeColors()
  },
  computed: {
    cantCreateRoom () {
      return !(this.session_name && this.nickname && this.faction_id && this.color_id)
    },
    iAmInRoom () {
      return this.$route.name === 'game'
    }
  },
  methods: {
    copyToClipboardUrl () {
      navigator.clipboard.writeText(this.redirect_url)
    },
    async getSession () {
      try {
        const res = await fetch('/api/startGame/getSession?session_id=' + this.$route.params.id)
        const data = await res.json()
        this.session_name = data.result.name
      } catch (error) {
        console.error('Ошибка:', error)
      }
    },
    async getFreeFactions () {
      try {
        const params = this.iAmInRoom ? `?session_id=${this.$route.params.id}` : ''
        const res = await fetch('/api/startGame/getFreeFactions' + params)
        const data = await res.json()
        this.factions = data.result
      } catch (error) {
        console.error('Ошибка:', error)
      }
    },
    async getFreeColors () {
      try {
        const params = this.iAmInRoom ? `?session_id=${this.$route.params.id}` : ''
        const res = await fetch('/api/startGame/getFreeColors' + params)
        const data = await res.json()
        this.colors = data.result
      } catch (error) {
        console.error('Ошибка:', error)
      }
    },
    async createRoom () {
      try {
        const res = await fetch('/api/startGame/CreateSession?session_name=' + this.session_name + '&nickname=' + this.nickname + '&faction_id=' + this.faction_id + '&color_id=' + this.color_id)
        const data = await res.json()
        this.redirect_url = window.location.origin + data.result.redirect_url
      } catch (error) {
        console.error('Ошибка:', error)
      }
    }
  }
}
</script>

<style lang="scss">
</style>
