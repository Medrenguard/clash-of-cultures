<template>
 <!-- TODO: автообновление статуса комнаты, попапы с ошибками -->
   <!-- На будущее: выбор цвета визуальный и украшательства: иконки фракций, точки статуса готовности -->
   <!-- На будущее: формочка визуально красивая -->
   <!-- На далёкое будущее: выведение особенностей выбираемой фракции -->
   <!-- На далёкое будущее: динамическое обновление доступных цветов и фракций -->
    <div>
        <label for="session_name">Название игры:</label><br>
        <input v-if="!iAmInRoom" type="text" v-model="session_name" id="session_name">
        <div v-else> {{ session_name }} </div>
        <br>
        <label for="faction">Фракция:</label><br>
        <select v-model="faction_id" :disabled="meAsPlayer" >
          <option v-for="item in factions" :key="item.id" :value="item.id">
            {{ item.name }}
          </option>
        </select><br>
        <label>Цвет на поле:</label><br>
        <select v-model="color_id" :disabled="meAsPlayer">
          <option v-for="item in colors" :key="item.id" :value="item.id">
            {{ item.name }}
          </option>
        </select><br><br>
        <div v-if="iAmInRoom">
          <div>Требования к началу игры:
            <span v-if="requirementsArray.length === 0"> выполнены!</span>
            <template v-else>
              <div v-for="item in requirementsArray" :key="item">- {{ item }}</div>
            </template>
          </div>
          <br>
          Уже на поле({{ session_players.length }}/ 4):
          <div v-for="player in session_players" :key="player.id">
            {{ player.name }} - {{ player.faction_name }} - {{ player.color_code }}. Готовность - {{ player.ready_for_start }}
          </div>
        </div><br>

        <button v-if="!iAmInRoom" @click="createRoom" :disabled="cantJoinToRoom">Создать комнату</button>
        <template v-else>
          <button v-if="!meAsPlayer" @click="JoinToRoom" :disabled="cantJoinToRoom">Присоединиться</button>
          <button v-else @click="setReadyPlayer" :disabled="i_am_ready || loading">Подтвердить готовность</button>
          <br><br>
          <button v-if="iAmCreator" @click="startGame" :disabled="requirementsArray.length > 0">Начать игру</button>
          <div v-if="this.redirect_url">
            Ссылка для приглашения друзей =>
          <button @click="copyToClipboardUrl" style="height: 1.5rem">📋 скопировать</button>
        </div>
        </template>
    </div>
</template>

<script>
export default {
  name: 'joinModal',
  data () {
    return {
      session_name: '',
      session_players: [],
      factions: [],
      faction_id: '',
      colors: [],
      color_id: '',
      redirect_url: '',
      i_am_ready: false,
      creator_username: '',
      loading: false
    }
  },
  async mounted () {
    if (this.iAmInRoom) {
      // TODO: перенести этот вызов на роут, чтобы делать переадресацию с несуществующей комнаты сразу, а не после задержки
      await this.getSessionInfo()
      this.redirect_url = window.location.origin + '/game/' + this.$route.params.id
    }
    this.getFreeFactions()
    this.getFreeColors()
  },
  computed: {
    cantJoinToRoom () {
      return !(this.session_name && this.faction_id && this.color_id) || this.loading
    },
    iAmInRoom () {
      return this.$route.name === 'game'
    },
    meAsPlayer () {
      return this.session_players.find((el) => el.name === this.$authStore.state.username)
    },
    iAmCreator () {
      return this.meAsPlayer?.name === this.creator_username
    },
    requirementsArray () {
      const res = []
      const readyPlayersCount = this.session_players.filter((el) => el.ready_for_start).length
      if (this.session_players.length < 2) res.push('Необходимо хотя бы 2 игрока')
      if (readyPlayersCount < this.session_players.length) res.push('Все игроки в лобби должны быть готовы')
      if (this.session_players.length > 4) res.push('Комната перегружена. Обратитесь к администратору')
      return res
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
        if (data.error && data.error.message === 'Игра не найдена') {
          window.location.href = window.location.origin
        } else {
          this.session_name = data.result.name
          this.session_players = data.result.players
          this.creator_username = data.result.creator_username
          if (this.meAsPlayer) {
            this.faction_id = this.meAsPlayer.faction_id
            this.color_id = this.meAsPlayer.color_id
            this.i_am_ready = this.meAsPlayer.ready_for_start
          }
          if (data.result.is_started) {
            this.$store.commit('updateIsStarted', true)
          }
        }
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
        this.loading = true
        const res = await fetch('/api/startGame/CreateSession?session_name=' + this.session_name + '&faction_id=' + this.faction_id + '&color_id=' + this.color_id)
        const data = await res.json()
        window.location.href = '/game/' + data.result.session_id
      } catch (error) {
        console.error('Ошибка:', error)
        this.loading = false
      }
    },
    async JoinToRoom () {
      // TODO: возвращает id игрока, но пока никак не обрабатывается. Возможно возвращать id не нужно
      try {
        this.loading = true
        await fetch('/api/startGame/createSessionPlayer?session_id=' + this.$route.params.id + '&faction_id=' + this.faction_id + '&color_id=' + this.color_id)
        await this.getSessionInfo()
        this.getFreeFactions()
        this.getFreeColors()
        this.loading = false
      } catch (error) {
        console.error('Ошибка:', error)
      }
    },
    async setReadyPlayer () {
      try {
        this.loading = true
        await fetch('/api/startGame/setReadyPlayer?session_id=' + this.$route.params.id)
        await this.getSessionInfo()
        this.loading = false
      } catch (error) {
        console.error('Ошибка:', error)
      }
    },
    async startGame () {
      try {
        this.loading = true
        await this.getSessionInfo()
        if (this.requirementsArray.length === 0) {
          await fetch('/api/startGame/startGame?session_id=' + this.$route.params.id)
          await this.getSessionInfo()
        }
        this.loading = false
      } catch (error) {
        console.error('Ошибка:', error)
      }
    }
  }
}
</script>

<style lang="scss">
</style>
