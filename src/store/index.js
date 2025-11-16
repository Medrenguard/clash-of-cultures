import Vue from 'vue'
import Vuex from 'vuex'
import layout from '@/store/layout'
import nodeList from '@/store/nodeList'
import mapTilesInRegion from '@/store/mapTilesInRegion'

Vue.use(Vuex)

export default new Vuex.Store({
  state: {
    currentCountGamers: 2,
    layoutByCount: {
      regionsCount: null,
      regionsForStart: []
    },
    // stageLoop: [
    //   'beforeStart',
    //   'start',
    //   'changeColor',
    //   'colorChanged',
    //   'changeFirstPlayer',
    //   'firstPlayerChanged',
    //   'readyToGame',
    //   'MOVING_waitingSelection', // этап после нажатия кнопки "Передвижение" для выбора юнитов
    //   'MOVING_selectingTile', // пехотное формирование выбрано и курсор наведен на другой тайл, до которого формирование может дойти
    //   'MOVING_selectingRegion', // флот выбран и курсор наведен на неизвестный регион, до которого флот может доплыть
    //   'MOVING_moveThenExploringManual', // этап, на котором юниты делают переход на регион и после этого запускается этап ручного выбора ориентации региона
    //   'MOVING_shipsExploringManualThenChange', // этап, на котором корабли выполняют разведку на регионе, на который потом смогут переместиться в обоих ориентациях и есть выбор тайла для размещения флота, запускается этап ручного выбора ориентации региона
    //   'MOVING_shipsMoveAfterExploring', // этап, на котором корабли выполнили разведку в регионе, возможны варианты размещения флота, запускается выбор тайла для размещения флота
    //   'MOVING_shipsExploringManualThenMoveStrict', // этап, на котором корабли выполняют разведку на регионе, на который потом смогут переместиться в обоих ориентациях и нет выбора тайла для размещения флота, запускается этап ручного выбора ориентации региона
    //   'MOVING_shipsExploringThenMoveStrict', // этап, на котором корабли выполняют разведку на регионе, на который потом смогут переместиться, для региона возможна только одна ориентация, нет выбора тайла для размещения флота, выполняется принудительная установка ориентации и подразделения
    //   'MOVING_shipsCantMoveAndTheyExploringManual', // этап, на котором корабли не могут сделать переход на регион из-за того, что в нём нет воды или она недосягаема, запускается этап ручного выбора ориентации региона
    //   'MOVING_exploringManual' // этап ручного выбора ориентации региона
    // ],
    stage: 'beforeStart',
    firstPlayer: 'player',
    whoActNow: 'player',
    opponents: { // для мультиплеера или большего кол-ва игроков правильнее будет переделать в массив, но для этого нужно будет переписать обращения к объекту
      player: {
        color: 'red',
        nation: undefined,
        achivements: [/** TODO **/]
      },
      AI: {
        color: 'blue',
        nation: undefined,
        achivements: [/** TODO **/]
      }
    },
    regionForManualOrientation: null,
    collectionPoint: { // локация(регион/тайл) выбранных в данный момент юнитов
      region: null,
      tile: null
    },
    shipExploringData: {
      // data: { // сырые данные о доступных тайлах для перемещения в новом регионе
      //   avers: массив объектов {region: 6, tile: 2} или пустой массив,
      //   revers: массив объектов {region: 6, tile: 2} или пустой массив
      // },
      // count: { // кол-во тайлов для возможного перемещения в новом регионе
      //   avers: Number
      //   revers: Number
      // },
      // have: { // признак наличия ограничения на каждой ориентации
      //   avers: Boolean,
      //   revers: Boolean
      // },
      // limitless: undefined || true || false, // признак, что ограничений нет
      // onlyPossibleOrientation: String || undefined, // Вытаскивает значение ориентации для первого false в have
      // regionNum: Number // номер разведываемого региона
    },
    regionItemsOnMap: [ // схема данных
      // {
      //   orientation: 'avers',
      //   region_type: 1
      // },
      // {
      //   orientation: 'avers',
      //   region_type: 0
      // }
    ]

  },
  getters: {
    PLAYER_COLORS (state) {
      const res = {}
      for (const key in state.opponents) {
        res[key] = state.opponents[key].color
      }
      return res
    },
    CITIES (state) {
      return state.layout.city.filter(city => city.destroyed === false)
    },
    BUILDINGS (state) {
      return state.layout.buildings.filter(building => building.destroyed === false)
    },
    LIVING_UNITS (state) {
      return state.layout.units.filter(unit => unit.alive === true)
    },
    GET_UNIT_BY_ID: (state) => (payload) => {
      return state.layout.units.find(unit => unit.id === payload)
    },
    SELECTED_UNITS (state) {
      return state.layout.units.filter(unit => unit.selected === true)
    },
    IS_SELECTED_FLEET (state, getters) {
      return getters.SELECTED_UNITS.findIndex(unit => unit.type === 'ship') > -1
    },
    GET_ORIENTED_REGION: (state) => (regionType, orientation) => {
      let region
      if (orientation !== 'revers') {
        region = state.mapTilesInRegion[regionType]
      } else {
        region = state.mapTilesInRegion[regionType].map(function (el) {
          let res
          if (el.translate === 'top') { res = 'bottom' }
          if (el.translate === 'bottom') { res = 'top' }
          if (el.translate === 'left') { res = 'right' }
          if (el.translate === 'right') { res = 'left' }
          return { type: el.type, translate: res }
        }).reverse()
      }
      return region
    },
    GET_TILE_TYPE: (state, getters) => (numReg, numTile, mockup) => { // принимает номер региона и тайла, выложенных на карту, возвращает тип тайла(местность)
      const regionOnMap = state.regionItemsOnMap[numReg - 1] // region_type; orientation
      // mockup - опциональный параметр, принимает номер и тип региона для подстановки для расчёта
      if (mockup?.numReg === numReg) { return getters.GET_ORIENTED_REGION(mockup.region_type, mockup.orientation)[numTile - 1].type }
      const res = getters.GET_ORIENTED_REGION(regionOnMap.region_type, regionOnMap.orientation)[numTile - 1].type
      return res
    },
    GET_UNITS_ON_TILE: (state, getters) => (numReg, numTile) => { // принимает номер региона и тайла, выложенных на карту, возвращает массив юнитов
      const res = getters.LIVING_UNITS.filter(unit => unit.region === numReg && unit.tile === numTile)
      return res
    },
    GET_NEAREST_NODES: (state) => (nodeNum) => { // принимает номер узла; отдаёт массив объектов, содержащих номера узлов
      return state.nodeList[state.currentCountGamers][nodeNum]
    },
    NODE_TO_TILE: (state) => (nodeNum) => { // принимает номер узла; отдаёт объект со свойствами region и tile
      const region = Number(String(nodeNum).slice(0, -1))
      const tile = Number(String(nodeNum).slice(-1))
      return { region: region, tile: tile }
    },
    TILE_TO_NODE: (state) => (numReg, numTile) => { // принимает номера региона и тайла; отдаёт номер узла
      return Number('' + numReg + numTile)
    },
    GET_NEAREST_TILES: (state, getters) => (numReg, numTile) => { // принимает номер региона и тайла; отдаёт массив объектов, содержащих номер региона и тайла; если пришёл null - отдаст пустой массив
      if (numReg === null || numTile === null) { return [] }
      const nodeNum = getters.TILE_TO_NODE(numReg, numTile)
      const nearestNodes = getters.GET_NEAREST_NODES(nodeNum)
      const res = nearestNodes.map(function (el) {
        return getters.NODE_TO_TILE(el)
      })
      return res
    },
    GET_WATER_AREA: (state, getters) => (numReg, numTile, mockup) => { // принимает номер региона, тайла и, опционально, подстановленный регион(TODO: и опцию "Навигация"); отдаёт массив объектов, содержащих номер региона и тайла; если пришёл null - отдаст пустой массив. TODO: должен учитывать врагов на пути
      if (numReg === null || numTile === null || getters.GET_TILE_TYPE(numReg, numTile) !== 'sea') { return [] }
      const waterAreaNodes = [getters.TILE_TO_NODE(numReg, numTile)]
      // рекурсивно добавляет соседние морские узлы в waterAreaNodes
      for (let i = 0; i < waterAreaNodes.length; i++) {
        const waterTile = waterAreaNodes[i]
        const nearestNodes = getters.GET_NEAREST_NODES(waterTile)
        nearestNodes.forEach(nearestNode => {
          const tile = getters.NODE_TO_TILE(nearestNode)
          if (getters.GET_TILE_TYPE(tile.region, tile.tile, mockup) === 'sea' && !waterAreaNodes.includes(nearestNode)) {
            waterAreaNodes.push(nearestNode)
          }
        })
      }
      // Преобразует данные узлов в данные тайлов
      const res = waterAreaNodes.map(function (waterAreaNode) {
        return getters.NODE_TO_TILE(waterAreaNode)
      })
      return res
    },
    GET_FOG_REGIONS_NEAR_WATER_AREA: (state, getters) => (numReg, numTile) => { // принимает номер региона, тайла(TODO: и опцию "Навигация"); отдаёт массив номеров регионов; если пришёл null - отдаст пустой массив.
      if (numReg === null || numTile === null || getters.GET_TILE_TYPE(numReg, numTile) !== 'sea') { return [] }
      const waterAreaNodes = getters.GET_WATER_AREA(numReg, numTile)
      const fogRegions = []
      for (let i = 0; i < waterAreaNodes.length; i++) {
        const waterNode = getters.TILE_TO_NODE(waterAreaNodes[i].region, waterAreaNodes[i].tile)
        const nearestNodes = getters.GET_NEAREST_NODES(waterNode)
        nearestNodes.forEach(nearestNode => {
          const tile = getters.NODE_TO_TILE(nearestNode)
          if (getters.GET_TILE_TYPE(tile.region, tile.tile) === 'fog' && !fogRegions.includes(tile.region)) {
            fogRegions.push(tile.region)
          }
        })
      }
      return fogRegions
    }
  },
  mutations: {
    updateRegionInfo (state, payload) {
      state.regionItemsOnMap.splice(payload.regionNum - 1, 1, payload.info)
    },
    updateLayoutByCount (state, payload) {
      state.layoutByCount = payload
    },
    updateStage (state, newValue) {
      state.stage = newValue
    },
    updateOpponentsColor (state, payload) {
      state.opponents.player.color = payload.player
      state.opponents.AI.color = payload.AI
    },
    updateFirstPlayer (state, newValue) {
      state.firstPlayer = newValue
    },
    reverseUnitSelection (state, unitId) {
      const u = state.layout.units.find(unit => unit.id === unitId)
      u.selected = !u.selected
    },
    updateCollectionPoint (state, newValue) {
      state.collectionPoint = newValue
    },
    unitMovement (state, payload) { // id юнита, регион и тайл; если точка назначения пустая - не перемещать юнит, завершить перемещение
      const unit = state.layout.units.find(unit => unit.id === payload.id)
      if (!!payload.region && !!payload.tile) {
        unit.region = payload.region
        unit.tile = payload.tile
      }
      unit.selected = false
      unit.canMove_onThisAction = false
      // тут добавить логику: при попадении в леса - canAttack = false, в горы - canMove_onThisRound = false
    },
    updateRegionForManualOrientation (state, newValue) {
      state.regionForManualOrientation = newValue
    },
    updateShipExploringData (state, newValue) {
      state.shipExploringData = newValue
    }
  },
  actions: {
    toggleUnitSelection (context, unitId) {
      context.commit('reverseUnitSelection', unitId)
      // если при выделении нет точки сбора - проставить её(будет корректно работать, если параметр selected у юнитов не будет сохраняться при перезагрузке)
      if (context.state.collectionPoint.region === null) {
        const unit = context.state.layout.units.find(unit => unit.id === unitId)
        context.commit('updateCollectionPoint', { region: unit.region, tile: unit.tile })
      }
    },
    formationMovement (context, target) {
      context.getters.SELECTED_UNITS.forEach(unit => {
        context.commit('unitMovement', { ...target, id: unit.id })
      })
    }
  },
  modules: {
    layout,
    nodeList,
    mapTilesInRegion
  }
})
