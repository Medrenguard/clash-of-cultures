<template>
  <!-- TODO: добавить общий лоадер при создании комнаты, присоединении к ней и подтверждения готовности -->
 <!-- TODO: сделать функцию на бэке простановки готовности для гостя, кнопку начала игры для хоста(и функцию на бэке), автообновление статуса комнаты, блокировка старта игры, если не готово хотя бы 2 участника, ограничение присоединения к игре по достижению 4 игроков-->
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
        <!-- TODO: пока прячется в комнате. Вообще надо здесь и оставить и делать переадресацию на комнату после связывания юзера через куки -->
        <div v-if="iAmInRoom">
          Уже на поле({{ session_players.length }}/ 4):
          <div v-for="player in session_players" :key="player.id">
            {{ player.name }} - {{ player.faction_name }} - {{ player.color_code }}. Готовность - {{ player.ready_for_start }}
          </div>
        </div><br><br>

        <button v-if="!iAmInRoom" @click="createRoom" :disabled="cantJoinToRoom">Создать комнату</button>
        <button v-else-if="iAmInRoom && !meAsPlayer" @click="JoinToRoom" :disabled="cantJoinToRoom">Присоединиться</button>
        <button v-else @click="iAmReady">Подтвердить готовность</button>
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
      factions: [],
      faction_id: '',
      colors: [],
      color_id: '',
      redirect_url: ''
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
      return !(this.session_name && this.faction_id && this.color_id)
    },
    iAmInRoom () {
      return this.$route.name === 'game'
    },
    meAsPlayer () {
      return this.session_players.find((el) => el.name === this.$authStore.state.username)
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
        if (data.error && data.error.message === 'game_not_found') {
          window.location.href = window.location.origin
        } else {
          this.session_name = data.result.name
          this.session_players = data.result.players
          if (this.meAsPlayer) {
            this.faction_id = this.meAsPlayer.faction_id
            this.color_id = this.meAsPlayer.color_id
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
        const res = await fetch('/api/startGame/CreateSession?session_name=' + this.session_name + '&faction_id=' + this.faction_id + '&color_id=' + this.color_id)
        const data = await res.json()
        window.location.href = '/game/' + data.result.session_id
      } catch (error) {
        console.error('Ошибка:', error)
      }
    },
    async JoinToRoom () {
      // TODO: возвращает id игрока, но пока никак не обрабатывается. Возможно возвращать id не нужно
      try {
        // const res = await fetch('/api/startGame/createSessionPlayer?session_id=' + this.$route.params.id + '&faction_id=' + this.faction_id + '&color_id=' + this.color_id)
        await fetch('/api/startGame/createSessionPlayer?session_id=' + this.$route.params.id + '&faction_id=' + this.faction_id + '&color_id=' + this.color_id)
        this.getSessionInfo()
        // const data = await res.json()
      } catch (error) {
        console.error('Ошибка:', error)
      }
    },
    iAmReady () {
      // тут вызов бэка, проставляющий мне галочку готовности
    }
  }
}
</script>

<style lang="scss">
</style>
