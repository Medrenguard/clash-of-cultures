<template>
  <!-- TODO: человеко-читаемые поля, запрет пустых значений, переадресация на комнату после создания, логика кнопок для нескольких игроков ("Присоединиться", если ты не создатель), взаимодействие до начала игры(счётчик игроков) -->
   <!-- На будущее: выбор цвета визуальный и немного украшательств -->
   <!-- На далёкое будущее: выведение особенностей выбираемой фракции -->
    <div>
        <label for="session_name">session_name:</label><br>
        <input type="text" v-model="session_name"><br>
        <label for="nickname">nickname:</label><br>
        <input type="text" id="nickname" v-model="nickname"><br>
        <label for="faction">faction:</label><br>
        <select v-model="faction_id">
          <option v-for="item in factions" :key="item.id" :value="item.id">
            {{ item.name }}
          </option>
        </select><br>
        <label for="color">color:</label><br>
        <select v-model="color_id">
          <option v-for="item in colors" :key="item.id" :value="item.id">
            {{ item.name }}
          </option>
        </select><br><br>

        <button @click="createRoom">Создать комнату</button>
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
      color_id: ''
    }
  },
  mounted () {
    this.getFreeFactions()
    this.getFreeColors()
  },
  methods: {
    async getFreeFactions () {
      try {
        const res = await fetch('/api/startGame/getFreeFactions')
        const data = await res.json()
        this.factions = data.result
      } catch (error) {
        console.error('Ошибка:', error)
      }
    },
    async getFreeColors () {
      try {
        const res = await fetch('/api/startGame/getFreeColors')
        const data = await res.json()
        this.colors = data.result
      } catch (error) {
        console.error('Ошибка:', error)
      }
    },
    async createRoom () {
      try {
        const sessionRes = await fetch('/api/startGame/CreateSession?session_name=' + this.session_name + '&nickname=' + this.nickname + '&faction_id=' + this.faction_id + '&color_id=' + this.color_id)
        const session = await sessionRes.json()
        console.log('Сессия:', session)
      } catch (error) {
        console.error('Ошибка:', error)
      }
    }
  }
}
</script>

<style lang="scss">
</style>
