<template>
  <!-- TODO: сделать кнопку готовности для гостя(и функцию простановки её), кнопку начала игры(и функцию на бэке), автообновление статуса комнаты, блокировка старта игры, если не готово хотя бы 2 участника -->
   <!-- Сделать блокировку никнейма/фракции/цвета, когда всё выбрано(временно, или возможно обновлять свой выбор) -->
   <!-- На будущее: выбор цвета визуальный и украшательства: иконки фракций, точки статуса готовности -->
   <!-- На будущее: формочка визуально красивая -->
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
        <!-- TODO: пока прячется в комнате. Вообще надо здесь и оставить и делать переадресацию на комнату после связывания юзера через куки -->
        <div v-if="iAmInRoom">
          Уже на поле({{ session_players.length }}/ 4):
          <div v-for="player in session_players" :key="player.id">
            {{ player.name }} - {{ player.faction_name }} - {{ player.color_code }}. Готовность - {{ player.ready_for_start }}
          </div>
        </div><br><br>

        <button v-if="!iAmInRoom" @click="createRoom" :disabled="cantJoinToRoom">Создать комнату</button>
        <button v-else @click="JoinToRoom" :disabled="cantJoinToRoom">Присоединиться</button>
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
      session_players: [],
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
      this.redirect_url = window.location.origin + this.$route.params.id
      this.getSessionInfo()
    }
    this.getFreeFactions()
    this.getFreeColors()
  },
  computed: {
    cantJoinToRoom () {
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
    async getSessionInfo () {
      try {
        const res = await fetch('/api/startGame/getSessionInfo?session_id=' + this.$route.params.id)
        const data = await res.json()
        this.session_name = data.result.name
        this.session_players = data.result.players
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
    },
    async JoinToRoom () {
      // TODO: возвращает id игрока, но пока никак не обрабатывается. Нужно где-то генерировать ключ или использовать сам этот id для того, чтобы игра узнавала тебя
      try {
        // const res = await fetch('/api/startGame/createSessionPlayer?session_id=' + this.$route.params.id + '&nickname=' + this.nickname + '&faction_id=' + this.faction_id + '&color_id=' + this.color_id)
        await fetch('/api/startGame/createSessionPlayer?session_id=' + this.$route.params.id + '&nickname=' + this.nickname + '&faction_id=' + this.faction_id + '&color_id=' + this.color_id)
        // const data = await res.json()
      } catch (error) {
        console.error('Ошибка:', error)
      }
    }
  }
}
</script>

<style lang="scss">
</style>
