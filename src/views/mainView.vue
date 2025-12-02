<template>
  <!-- TODO: надо ограничить создание новых игроков, если игра уже начата, чтобы можно было спокойно завязывать построение поля на кол-во игроков -->
  <!-- Вычитать ниже по документобороту что делается, убрать лишнее с фронта -->
    <!-- Хранить в сессии еще раунд, эпоху, мб факт фазы состояния, очерёдность игрока -->
     <!-- Подумать, что хранить для каждого игрока, у него свой цикл должен быть -->
  <div class="main">
    <template v-if="$store.state.isStarted">
      <svg
        class="map"
        width="1300"
        viewBox="-43 0 55 69.5"
        version="1.1"
        id="map"
        xmlns="http://www.w3.org/2000/svg"
        xmlns:svg="http://www.w3.org/2000/svg"
        data-type-object="map"
        @click="clickSVG"
        @mouseover="mouseoverSVG">
          <region-item v-for="(region, i) in regionItemsOnMap" :region_info="region" :key="i+1" :numberRegion="i+1" :transform="giveTranslateAttr(i+1)"/>
      </svg>
      <the-help-block />
    </template>
    <joinModal v-else/>
  </div>
</template>

<script>
import regionItem from '@/components/onMap/regionItem.vue'
import TheHelpBlock from '@/components/TheHelpBlock.vue'
import joinModal from './joinModal.vue'
import { mapState, mapGetters } from 'vuex'

export default {
  name: 'mainView',
  components: {
    regionItem,
    TheHelpBlock,
    joinModal
  },
  data () {
    return {
      layoutByCountGamers: {
        2: {
          regionsCount: 10,
          regionsForStart: [1, 10]
        }
      },
      colors: { красный: 'red', голубой: 'blue', желтый: 'yellow', фиолетовый: 'purple' } // название на английском используется в классах для раскраски
    }
  },
  created () {
    if (this.stage === 'beforeStart') { this.$store.commit('updateStage', 'start') }
  },
  mounted () {
    // TODO: скрыто до выравнивания бэка
    // this.$store.commit('updateStage', 'MOVING_waitingSelection') отладочное
    // this.loadPlayers()
  },
  methods: {
    fillInfoAboutRegions () {
      for (let i = 1; i <= this.layoutByCount.regionsCount; i++) {
        // region_type: 0 - это регион под туманом
        const filler = { region_type: 0, orientation: 'avers' }
        // Заполнение стартовых тайлов на 2 игроков
        if (i === this.layoutByCount.regionsForStart[0]) { filler.region_type = 1 }
        if (i === this.layoutByCount.regionsForStart[1]) { filler.region_type = 1; filler.orientation = 'revers' }
        // отладочное
        // if (i === 8) { filler.region_type = 7; filler.orientation = 'avers' }
        // if (i === 7) { filler.region_type = 6; filler.orientation = 'revers' }
        // if (i === 5) { filler.region_type = 5; filler.orientation = 'revers' }
        // проверка сложного limitless
        // if (i === 4) { filler.region_type = 8; filler.orientation = 'avers' }
        // if (i === 5) { filler.region_type = 7; filler.orientation = 'avers' }
        // if (i === 6) { filler.region_type = 12; filler.orientation = 'avers' }
        // if (i === 8) { filler.region_type = 5; filler.orientation = 'avers' }
        // if (i === 9) { filler.region_type = 16; filler.orientation = 'avers' }

        this.$store.commit('updateRegionInfo', { regionNum: i, info: filler })
      }
    },
    suggestColorChoice () {
      // демо-выбор цвета
      const color = prompt(`Выберите цвет своих фигурок и впишите его в ответ: ${Object.keys(this.colors).join(', ')}`, Object.keys(this.colors)[0])
      if (this.colors[color] !== undefined) {
        const lostColors = { ...this.colors }
        delete lostColors[color]
        this.$store.commit('updateOpponentsColor', { player: this.colors[color], AI: Object.values(lostColors)[0] })
        this.$store.commit('updateStage', 'colorChanged')
      } else {
        this.suggestColorChoice()
      }
    },
    changeFirstPlayer () {
      const res = Math.floor(Math.random() * this.currentCountGamers) ? 'player' : 'AI'
      alert('Выбирается первый игрок, бросаются кубики... первым игроком становится ' + res + '.')
      this.$store.commit('updateFirstPlayer', res)
      this.$store.commit('updateStage', 'firstPlayerChanged')
    },
    clickSVG (event) {
      // использование .closest позволит убрать дублирующий слой на всех элементах
      // и убрать .selection-frame, убедившись, что все элементы, которые должны быть кликабельными, имеют fill не none
      const t = event.target.closest('[data-type-object]')
      if (t !== null) {
        const tileInfo = event.target.closest('.tile-wrap')?.querySelector('.selection-frame')
        // console.log(t)
        if (this.stage === 'MOVING_selectingTile' || this.stage === 'MOVING_moveThenExploringManual') {
          this.$store.dispatch('formationMovement', { region: Number(tileInfo.getAttribute('data-region')), tile: Number(tileInfo.getAttribute('data-tile')) })
          tileInfo.classList.remove('hover')
          if (this.stage === 'MOVING_selectingTile') {
            this.$store.commit('updateStage', 'MOVING_waitingSelection')
          } else {
            this.$store.commit('updateStage', 'MOVING_exploringManual')
          }
        } else if (this.stage === 'MOVING_shipsCantMoveAndTheyExploringManual') {
          this.$store.dispatch('formationMovement')
          this.$store.commit('updateStage', 'MOVING_exploringManual')
        } else if (this.stage === 'MOVING_shipsExploringThenMoveStrict') {
          // принудительное перемещение на единственный доступный для перемещения тайл
          this.$store.dispatch('formationMovement', { region: Number(tileInfo.getAttribute('data-region')), tile: this.shipExploringData.data[tileInfo.getAttribute('data-orientation')][0].tile })
          this.$store.commit('updateShipExploringData', {})
          this.$store.commit('updateStage', 'MOVING_waitingSelection')
        }
      }
    },
    mouseoverSVG (event) {
      const t = event.target.closest('[data-type-object]')
      // console.log(t.getAttribute('data-type-object'))
      if (t !== null) {
        if (t.getAttribute('data-type-object') === 'map') {
          if (this.stage === 'MOVING_selectingTile') {
            this.$store.commit('updateStage', 'MOVING_waitingSelection')
          }
        }
      }
    },
    giveTranslateAttr (numberRegion) {
      const res = this.calcTranslateAttr(numberRegion)
      return `translate(${res.toString()})`
    },
    calcTranslateAttr (numberRegion) {
      const res = []
      const shift = { x: 15, y: 8.66 }
      // описываем заполнение полей x
      if ((numberRegion - 1) % 3 === 0) { res[0] = 0 }
      if ((numberRegion - 2) % 3 === 0) { res[0] = 0 - shift.x }
      if ((numberRegion - 3) % 3 === 0) { res[0] = shift.x }
      // описываем заполнение полей y
      res[1] = Math.floor((1 + numberRegion) / 3) * shift.y
      if ((numberRegion - 1) % 3 !== 0) { res[1] += Math.floor((numberRegion - 2) / 3) * shift.y }
      if ((numberRegion - 1) % 3 === 0) { res[1] += Math.floor(numberRegion / 3) * shift.y }
      return res
    }
  },
  computed: {
    ...mapState([
      'currentCountGamers',
      'regionItemsOnMap',
      'layoutByCount',
      'stage',
      'opponents',
      'firstPlayer',
      'collectionPoint',
      'shipExploringData'
    ]),
    ...mapGetters([
      'GET_UNIT_BY_ID',
      'SELECTED_UNITS'
    ])
  },
  watch: {
    stage: function (newValue, oldValue) { // StageLoop
      console.log(oldValue + ' > ' + newValue)
      if (newValue === 'start') {
        // если цвета выбраны заранее - значит это тестовый режим, перепрыгиваем этап
        // TODO: скрыто для остановки документооборота, вероятно потом всё это будет перенесено на бэк
        // if (this.opponents.player.color === undefined) { this.$store.commit('updateStage', 'changeColor') } else { this.$store.commit('updateStage', 'colorChanged') }
      }
      if (newValue === 'changeColor') {
        this.suggestColorChoice()
      }
      if (newValue === 'colorChanged') {
        // если первый игрок выбран заранее - значит это тестовый режим, перепрыгиваем этап
        if (this.firstPlayer === undefined) { this.$store.commit('updateStage', 'changeFirstPlayer') } else { this.$store.commit('updateStage', 'firstPlayerChanged') }
      }
      if (newValue === 'changeFirstPlayer') {
        this.changeFirstPlayer()
      }
      if (newValue === 'firstPlayerChanged') { this.$store.commit('updateStage', 'readyToGame') }
      if (newValue === 'readyToGame') {
        // Хоть и добавлено сейчас, но потом частично или полностью тоже будет перенесено на бэк(в части расчётов)
        this.$store.commit('updateLayoutByCount', this.layoutByCountGamers[this.currentCountGamers])
        this.fillInfoAboutRegions()
      }
    },
    SELECTED_UNITS: function (newValue) {
      // если всё выделение снято - сбросить точку сбора
      if (!newValue.length) {
        this.$store.commit('updateCollectionPoint', { region: null, tile: null })
      }
    }
  }
}
</script>

<style lang="scss">
.redColor {
  .color-main {
    fill: #ff8080;
    stroke: #660000;
    &.borderless-color {
      stroke: #ff8080;
    }
    &.border-color-special {
      fill: #660000;
    }
  }
  .color-lighter {
    fill: #ffd2d2;
    stroke: #959595;
    &.borderless-color {
      stroke: #ffd2d2;
    }
    &.border-color-special {
      fill: #959595;
    }
  }
  .color-darker {
    fill: #aa1515;
    stroke: #959595;
    &.borderless-color {
      stroke: #aa1515;
    }
    &.border-color-special {
      fill: #959595;
    }
  }
  &.selected .pentahedron {
    filter: drop-shadow(0px 0px 1px #ffd034);
    stroke: #000000 !important
  }
  &.selected .pictogram {
    fill: #ffd034
  }
  &.disabled .pentahedron {
    fill: #cc7373;
  }
  &.disabled .pictogram {
    fill: #ebc7c7;
  }
}
.blueColor {
  .color-main {
    fill: #80e5ff;
    stroke: #008496;
    &.borderless-color {
      stroke: #80e5ff;
    }
    &.border-color-special {
      fill: #008496;
    }
  }
  .color-lighter {
    fill: #d2f6ff;
    stroke: #959595;
    &.borderless-color {
      stroke: #d2f6ff;
    }
    &.border-color-special {
      stroke: #959595;
    }
  }
  .color-darker {
    fill: #158caa;
    stroke: #959595;
    &.borderless-color {
      stroke: #158caa;
    }
    &.border-color-special {
      stroke: #959595;
    }
  }
  &.selected .pentahedron {
    filter: drop-shadow(0px 0px 1px #f3bf00);
    stroke: #000000 !important
  }
  &.selected .pictogram {
    fill: #f3bf00
  }
  &.disabled .pentahedron {
    fill: #73bacc;
  }
  &.disabled .pictogram {
    fill: #f9f9f999;
  }
}
.yellowColor {
  .color-main {
    fill: #fff280;
    stroke: #969300;
    &.borderless-color {
      stroke: #fff280;
    }
    &.border-color-special {
      fill: #969300;
    }
  }
  .color-lighter {
    fill: #fffcd2;
    stroke: #959595;
    &.borderless-color {
      stroke: #fffcd2;
    }
    &.border-color-special {
      stroke: #959595;
    }
  }
  .color-darker {
    fill: #dfc800;
    stroke: #959595;
    &.borderless-color {
      stroke: #dfc800;
    }
    &.border-color-special {
      stroke: #959595;
    }
  }
  &.selected .pentahedron {
    filter: drop-shadow(0px 0px 1px #000000);
    stroke: #000000 !important
  }
  &.selected .pictogram {
    fill: #000000
  }
  &.disabled .pentahedron {
    fill: #ecde66;
  }
  &.disabled .pictogram {
    fill: #f7f2c2;
  }
}
.purpleColor {
  .color-main {
    fill: #ff80ff;
    stroke: #96008e;
    &.borderless-color {
      stroke: #ff80ff;
    }
    &.border-color-special {
      fill: #96008e;
    }
  }
  .color-lighter {
    fill: #fed2ff;
    stroke: #959595;
    &.borderless-color {
      stroke: #fed2ff;
    }
    &.border-color-special {
      stroke: #959595;
    }
  }
  .color-darker {
    fill: #9115aa;
    stroke: #959595;
    &.borderless-color {
      stroke: #9115aa;
    }
    &.border-color-special {
      stroke: #959595;
    }
  }
  &.selected .pentahedron {
    filter: drop-shadow(0px 0px 1px #ffd034);
    stroke: #000000 !important
  }
  &.selected .pictogram {
    fill: #ffd034
  }
  &.disabled .pentahedron {
    fill: #bd73cc;
  }
  &.disabled .pictogram {
    fill: #e1c3e7;
  }
}
.selection-frame {
  fill: black !important; // нужно для правильной работы слоя для выбора обьекта: делает заполнение однозначно не none
  fill-opacity: 0 !important; // делает заполнение прозрачным
  &.hover {
    cursor: pointer;
    &.tile-item {
    fill: rgba(255, 196, 0, 0.329) !important;
    fill-opacity: 1 !important;
    .underFog & {
      stroke: #363636;
      stroke-width: 0.01;
    }
    }
  }
}
</style>

<style scoped lang="scss">
.map {
  margin: 0 auto;
  display: block;
}
</style>
