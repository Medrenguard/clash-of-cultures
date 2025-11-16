import { mapState, mapGetters } from 'vuex'
export default {
  methods: {
    exploring (numReg, numTile) { // надо добавить варианты морской, сухопутной и специальной(через карту действия) разведки
      const destination = { region: numReg, tile: numTile }
      const filler = { region_type: 0, orientation: 'avers' }

      // выбираем случайный регион из невыложенных
      const uniqRegionsOnMap = [...new Set(this.regionItemsOnMap.map(function (region) { return region.region_type }))]
      const lostTypesOfRegion = []
      // for (let i = 13; i <= 13; i++) { // тестить можно на 8, 13 и 15
      for (let i = 5; i <= 20; i++) {
        if (!uniqRegionsOnMap.includes(i)) { lostTypesOfRegion.push(i) }
      }
      filler.region_type = lostTypesOfRegion[Math.floor(Math.random() * lostTypesOfRegion.length)]

      // тут будет блок проверки на то, возможны ли 2 варианта ориентации региона
      let restrictionByUnit = false // ограничивает ли точка назначения в выборе ориентации региона. true - ограничивает
      let restrictionBySeaNear = false // если есть на разведываемом регионе морской тайл - ограничивает ли он в выборе ориентации региона из-за соседних морских тайлов. true - ограничивает
      let restrictionBySeaEnd = false // если есть на разведываемом регионе морской тайл - ограничивает ли он в выборе ориентации региона из-за прилегания к краю. true - ограничивает
      let onlyPossibleOrientationByUnit
      let onlyPossibleOrientationBySeaNear
      let onlyPossibleOrientationBySeaEnd
      const seaInRegion = {
        avers: this.GET_ORIENTED_REGION(filler.region_type, 'avers').filter(tile => tile.type === 'sea'),
        revers: this.GET_ORIENTED_REGION(filler.region_type, 'revers').filter(tile => tile.type === 'sea')
      }

      // ветвление на типы разведки
      if (this.SELECTED_UNITS.length) {
        if (!this.IS_SELECTED_FLEET) {
          // сухопутная разведка
          // проверка на restrictionByUnit
          const destinationTileTypeOfAvers = this.GET_ORIENTED_REGION(filler.region_type, 'avers')[destination.tile - 1].type
          const destinationTileTypeOfRevers = this.GET_ORIENTED_REGION(filler.region_type, 'revers')[destination.tile - 1].type
          restrictionByUnit = destinationTileTypeOfAvers === 'sea' || destinationTileTypeOfRevers === 'sea'
          if (destinationTileTypeOfAvers === 'sea') {
            onlyPossibleOrientationByUnit = 'revers'
          } else if (destinationTileTypeOfRevers === 'sea') {
            onlyPossibleOrientationByUnit = 'avers'
          }
        } else {
          // морская разведка
        }
      } else {
        // разведка с помощью карты действия "Разведчики"
      }

      if (seaInRegion.avers.length && !restrictionByUnit) {
        // проверка на restrictionBySeaNear
        const tileNumDictionary = { top: 1, left: 2, right: 3, bottom: 4 }
        const aversSeaFound = [] // массив булевых утверждений нахождения моря по соседству с морским тайлом, содержит кол-во элементов равное кол-ву морских тайлов в разведываемом регионе
        const reversSeaFound = [] // [true, false]
        // проверяем тип соседей морских тайлов: получаем через GET_NEAREST_TILES, проверяем через GET_TILE_TYPE
        seaInRegion.avers.forEach(tileWithSea => {
          const closestToTheSeaTiles = this.GET_NEAREST_TILES(numReg, tileNumDictionary[tileWithSea.translate])
          aversSeaFound.push(closestToTheSeaTiles.findIndex(closestTile => this.GET_TILE_TYPE(closestTile.region, closestTile.tile) === 'sea') > 0)
        })
        seaInRegion.revers.forEach(tileWithSea => {
          const closestToTheSeaTiles = this.GET_NEAREST_TILES(numReg, tileNumDictionary[tileWithSea.translate])
          reversSeaFound.push(closestToTheSeaTiles.findIndex(closestTile => this.GET_TILE_TYPE(closestTile.region, closestTile.tile) === 'sea') > 0)
        })

        if (aversSeaFound.includes(true) !== reversSeaFound.includes(true)) {
          // если только в одной ориентации региона у морских тайлов есть морские соседи - наложить ограничение
          restrictionBySeaNear = true
          onlyPossibleOrientationBySeaNear = aversSeaFound.includes(true) ? 'avers' : 'revers'
        } else if (!aversSeaFound.includes(true) && !reversSeaFound.includes(true)) {
          // проверка на restrictionBySeaEnd
          const aversEndFound = [] // массив булевых утверждений нахождения края карты возле морского тайла, содержит кол-во элементов равное кол-ву морских тайлов в разведываемом регионе
          const reversEndFound = [] // [false, true]
          seaInRegion.avers.forEach(tileWithSea => {
            // если closestToTheSeaTiles.length меньше 6 - значит тайл находится у края
            const closestToTheSeaTiles = this.GET_NEAREST_TILES(numReg, tileNumDictionary[tileWithSea.translate])
            aversEndFound.push(closestToTheSeaTiles.length < 6)
          })
          seaInRegion.revers.forEach(tileWithSea => {
            const closestToTheSeaTiles = this.GET_NEAREST_TILES(numReg, tileNumDictionary[tileWithSea.translate])
            reversEndFound.push(closestToTheSeaTiles.length < 6)
          })
          if (aversEndFound.includes(true) !== reversEndFound.includes(true)) {
            // если только в одной ориентации региона возле морских тайлов есть конец карты - наложить ограничение
            restrictionBySeaEnd = true
            onlyPossibleOrientationBySeaEnd = aversEndFound.includes(true) ? 'avers' : 'revers'
          }
        }
      }

      if (!this.IS_SELECTED_FLEET) {
        if (seaInRegion.avers.length) { // если выбрана пехота и в этом регионе есть море
          if (restrictionByUnit) { // если есть ограничение от местоположения пехоты
            filler.orientation = onlyPossibleOrientationByUnit
          } else if (restrictionBySeaNear) { // если есть ограничение от соседнего моря
            filler.orientation = onlyPossibleOrientationBySeaNear
          } else if (restrictionBySeaEnd) { // если есть ограничение от края карты
            filler.orientation = onlyPossibleOrientationBySeaEnd
          } else {
            // тут переключение на этап ручного выбора ориентации региона
            this.$store.commit('updateRegionForManualOrientation', destination.region)
            this.$store.commit('updateStage', 'MOVING_moveThenExploringManual')
          }
        } else { // если выбрана пехота и в этом регионе нет моря
          // тут переключение на этап ручного выбора ориентации региона
          this.$store.commit('updateRegionForManualOrientation', destination.region)
          this.$store.commit('updateStage', 'MOVING_moveThenExploringManual')
        }
      } else {
        if (seaInRegion.avers.length) { // если выбран флот и в этом регионе есть море
          // В регионе есть море, нужно проверить, возможно ли установить ориентацию региона так, чтобы море было рядом с акваторией, в которой находится корабль
          // Если возможны два таких варианта - дать выбор по установке ориентации. Затем, если в этом регионе 2 морских тайла - дать выбор, в какой поставить корабль, а если 1 - поставить в него принудительно.
          // Если возможен только один вариант - установить ограничение на выбор ориентации. Затем, если в этом регионе 2 морских тайла - дать выбор, в какой поставить корабль, а если 1 - поставить в него принудительно.
          // Если невозможен такой вариант - запустить разведку по стандартным правилам, флот вернуть на исходную позицию.

          const adjacentSeaTilesInNewRegionAvers = this.GET_WATER_AREA(this.collectionPoint.region, this.collectionPoint.tile, { numReg: numReg, region_type: filler.region_type, orientation: 'avers' }).filter(tile => tile.region === numReg)
          const adjacentSeaTilesInNewRegionRevers = this.GET_WATER_AREA(this.collectionPoint.region, this.collectionPoint.tile, { numReg: numReg, region_type: filler.region_type, orientation: 'revers' }).filter(tile => tile.region === numReg)
          // тут написать условия для предоставления возможности выбора ориентации
          const restrictionByWaterArea = { // true - есть ограничение
            data: { // сырые данные о доступных тайлах для перемещения в новом регионе
              avers: adjacentSeaTilesInNewRegionAvers,
              revers: adjacentSeaTilesInNewRegionRevers
            },
            count: { // кол-во тайлов для возможного перемещения в новом регионе
              avers: adjacentSeaTilesInNewRegionAvers.length,
              revers: adjacentSeaTilesInNewRegionRevers.length
            },
            have: { // признак наличия ограничения на каждой ориентации
              avers: !adjacentSeaTilesInNewRegionAvers.length,
              revers: !adjacentSeaTilesInNewRegionRevers.length
            },
            limitless: undefined, // признак, что ограничений нет
            onlyPossibleOrientation: undefined, // Вытаскивает значение ориентации для первого false в have
            regionNum: numReg
          }
          restrictionByWaterArea.limitless = Object.values(restrictionByWaterArea.have).filter(or => or === false).length === 2
          restrictionByWaterArea.onlyPossibleOrientation = Object.entries(restrictionByWaterArea.have).find(or => or[1] === false)?.[0]
          if (restrictionByWaterArea.limitless) {
            // предложить выбор ориентации тайла, записать данные о разведке
            this.$store.commit('updateRegionForManualOrientation', destination.region)
            this.$store.commit('updateShipExploringData', restrictionByWaterArea)
            // проверка на кол-во тайлов для возможного размещения флота; если больше 1 - дать выбор
            if (restrictionByWaterArea.count[restrictionByWaterArea.onlyPossibleOrientation] > 1) {
              // дать выбор
              this.$store.commit('updateStage', 'MOVING_shipsExploringManualThenChange')
            } else {
              // принудительная установка флота в единственное доступное место
              this.$store.commit('updateStage', 'MOVING_shipsExploringManualThenMoveStrict')
            }
          } else if (restrictionByWaterArea.onlyPossibleOrientation !== undefined) {
            // принудительно установить ориентацию, записать данные о разведке
            filler.orientation = restrictionByWaterArea.onlyPossibleOrientation
            this.$store.commit('updateShipExploringData', restrictionByWaterArea)
            // проверка на кол-во тайлов для возможного размещения флота; если больше 1 - дать выбор
            if (restrictionByWaterArea.count[restrictionByWaterArea.onlyPossibleOrientation] > 1) {
              // Переключает на этап выбора расположения флота
              this.$store.commit('updateStage', 'MOVING_shipsMoveAfterExploring')
            } else {
              // принудительная установка флота в единственное доступное место
              this.$store.commit('updateStage', 'MOVING_shipsExploringThenMoveStrict')
            }
          } else if (restrictionByWaterArea.onlyPossibleOrientation === undefined) {
            // море недостижимо для корабля - дать выбор ориентации региона, флот вернуть на исходную позицию.
            this.$store.commit('updateRegionForManualOrientation', destination.region)
            this.$store.commit('updateStage', 'MOVING_shipsCantMoveAndTheyExploringManual')
          }
        } else { // если выбран флот и в этом регионе нет моря
        // В регионе нет моря, - дать выбор ориентации региона, флот вернуть на исходную позицию.
          this.$store.commit('updateRegionForManualOrientation', destination.region)
          this.$store.commit('updateStage', 'MOVING_shipsCantMoveAndTheyExploringManual')
        }
      }

      this.$store.commit('updateRegionInfo', { regionNum: destination.region, info: filler })
    }
  },
  computed: {
    ...mapState([
      'regionItemsOnMap',
      'mapTilesInRegion'
    ]),
    ...mapGetters([
      'SELECTED_UNITS',
      'IS_SELECTED_FLEET',
      'GET_ORIENTED_REGION',
      'GET_NEAREST_TILES',
      'GET_TILE_TYPE',
      'GET_WATER_AREA'
    ])
  }
}
