<template>
  <g
    inkscape:groupmode="layer"
    class="city-container"
    inkscape:label="Поселение">
    <g v-if="buildingsInThisCity.length" class="platform-container">
      <platform-item v-for="(building, i) in buildingsInThisCity" :key="i+1" :buildingNumber="i+1" :buildingType="building.type" :colorClass="building.color"></platform-item>
    </g>
    <g class="city-item" :class="colorClass">
      <circle
        class="city__circle color-lighter"
        cx="11.166244"
        cy="11.335546"
        r="0.92122394"
        inkscape:label="Круг" />
      <path
        d="m 11.607078,11.260523 c -0.01506,0.01338 -0.0758,0.06713 -0.134868,0.119473 -0.05912,0.05229 -0.115732,0.102478 -0.125888,0.111457 -0.01015,0.0089 -0.02459,0.02176 -0.03208,0.02834 -0.0075,0.0066 -0.01475,0.0131 -0.01614,0.01445 l -0.0024,0.0024 0.01764,0.01978 c 0.0097,0.01091 0.01797,0.01971 0.01834,0.01971 4.38e-4,-5.6e-5 0.03224,-0.02786 0.07072,-0.06179 0.201954,-0.178114 0.234561,-0.206766 0.234936,-0.206661 2.18e-4,1.07e-4 0.02513,0.02209 0.05539,0.04885 0.03021,0.02678 0.09846,0.08719 0.151602,0.134282 0.05314,0.04709 0.09686,0.08563 0.09713,0.08563 2.57e-4,0 6.29e-4,-2.58e-4 8.17e-4,-5.32e-4 2.57e-4,-4.37e-4 5.99e-4,-3.77e-4 0.0011,5.4e-5 9.42e-4,8.17e-4 -5.36e-4,0.0024 0.01812,-0.01876 0.0083,-0.0092 0.01566,-0.01771 0.01667,-0.01876 l 0.0017,-0.0019 -0.04481,-0.0395 c -0.02463,-0.02177 -0.08253,-0.07287 -0.128714,-0.113547 -0.04613,-0.04073 -0.102585,-0.0905 -0.125461,-0.110653 l -0.04148,-0.03661 h -0.0024 -0.0024 z"
        class="city__homecap color-darker"
        inkscape:label="Крыша-2" />
      <path
        d="m 11.60061,11.395807 c -0.01946,0.01722 -0.07322,0.06484 -0.119473,0.105789 l -0.08403,0.07446 1.06e-4,0.153524 1.61e-4,0.153526 0.0013,0.0022 c 6.28e-4,0.0011 0.0022,0.003 0.0033,0.004 0.0044,0.0039 -0.0013,0.0036 0.08346,0.0036 h 0.07633 l 1.07e-4,-0.06805 c 1.59e-4,-0.06768 1.59e-4,-0.068 0.0013,-0.0704 0.0013,-0.0032 0.004,-0.006 0.0072,-0.0072 0.0024,-0.0012 0.0027,-0.0012 0.06543,-0.0013 0.0697,-1.61e-4 0.0667,-2.58e-4 0.07066,0.0032 0.0027,0.0024 0.0043,0.0051 0.0049,0.0087 4.36e-4,0.0022 6e-4,0.02427 6e-4,0.06906 v 0.06591 l 0.07933,-1.08e-4 0.07937,-1.59e-4 0.0016,-0.0015 c 8.79e-4,-8.8e-4 0.0024,-0.0033 0.0033,-0.0056 l 0.0017,-0.004 1.07e-4,-0.152884 1.07e-4,-0.152883 -0.119743,-0.105567 c -0.06788,-0.05987 -0.120222,-0.105619 -0.120809,-0.105619 -6.92e-4,-5.6e-5 -0.01319,0.01069 -0.03645,0.03127 z"
        class="city__housing color-darker"
        inkscape:label="Дом-2" />
      <path
        d="m 10.828906,10.881115 c -0.02397,0.02128 -0.120598,0.106805 -0.214565,0.190073 -0.09406,0.08317 -0.184123,0.163029 -0.200282,0.177319 -0.01614,0.01423 -0.03912,0.0346 -0.05104,0.04507 -0.01197,0.01056 -0.02347,0.02084 -0.02567,0.02297 l -0.0039,0.0038 0.02806,0.03147 c 0.01544,0.01735 0.02857,0.03137 0.02918,0.03137 6.94e-4,-8.8e-5 0.05128,-0.04431 0.112512,-0.09831 0.321296,-0.283367 0.373173,-0.328952 0.373769,-0.328783 3.46e-4,1.7e-4 0.03998,0.03513 0.08812,0.07773 0.04805,0.04261 0.156645,0.138709 0.241187,0.213631 0.08453,0.07492 0.154095,0.136244 0.154528,0.136244 4.1e-4,0 0.001,-4.09e-4 0.0013,-8.48e-4 4.09e-4,-6.94e-4 9.54e-4,-6.01e-4 0.0018,8.6e-5 0.0015,0.0013 -8.51e-4,0.0038 0.02883,-0.02985 0.01301,-0.01474 0.02492,-0.02816 0.02653,-0.02985 l 0.0027,-0.0031 -0.07128,-0.06284 c -0.03918,-0.03462 -0.131301,-0.115929 -0.204775,-0.180644 -0.07339,-0.06479 -0.163202,-0.143982 -0.199598,-0.176041 l -0.06601,-0.05825 h -0.0038 -0.0038 z"
        class="city__homecap color-darker"
        inkscape:label="Крыша-1"
        style="stroke-width:0.035" />
      <path
        d="m 10.818615,11.096341 c -0.03095,0.0274 -0.116499,0.103162 -0.190073,0.168302 l -0.133696,0.118469 1.69e-4,0.244246 2.56e-4,0.244248 0.0019,0.0036 c 10e-4,0.0019 0.0036,0.0049 0.0053,0.0065 0.0069,0.0063 -0.0021,0.0057 0.132758,0.0057 h 0.121448 l 1.71e-4,-0.108265 c 2.53e-4,-0.107671 2.53e-4,-0.108181 0.0021,-0.11199 0.0021,-0.0051 0.0064,-0.0093 0.01139,-0.01139 0.0038,-0.0018 0.0044,-0.0018 0.104099,-0.0021 0.110899,-2.56e-4 0.106129,-4.12e-4 0.112429,0.0051 0.0044,0.0039 0.0068,0.0081 0.0078,0.01387 6.94e-4,0.0036 9.54e-4,0.03861 9.54e-4,0.109879 v 0.104858 l 0.126201,-1.7e-4 0.126289,-2.54e-4 0.0026,-0.0024 c 0.0014,-0.0014 0.0038,-0.0053 0.0053,-0.0089 l 0.0027,-0.0065 1.68e-4,-0.243227 1.7e-4,-0.243226 -0.1905,-0.167955 c -0.108003,-0.09526 -0.191265,-0.168042 -0.1922,-0.168042 -0.0011,-8.7e-5 -0.021,0.017 -0.058,0.04975 z"
        class="city__housing color-darker"
        inkscape:label="Дом-1"
        style="stroke-width:0.035;" />
    </g>
    <mood-indicator :mood="cityInfo.mood"/>
  </g>
</template>

<script>
import moodIndicator from '@/components/onMap/onTile/city/moodIndicator.vue'
import platformItem from '@/components/onMap/onTile/city/buildings/platformItem.vue'
import { mapGetters } from 'vuex'

export default {
  name: 'cityItem',
  components: {
    moodIndicator,
    platformItem
  },
  props: {
    cityInfo: {
      id: Number,
      mood: String,
      owner: String
    },
    colorClass: String
  },
  mounted () {
  },
  computed: {
    ...mapGetters([
      'BUILDINGS',
      'PLAYER_COLORS'
    ]),
    buildingsInThisCity () {
      const res = []
      for (let i = 0; i < this.BUILDINGS.length; i++) {
        if (this.BUILDINGS[i].cityId === this.cityInfo.id) {
          res.push({ id: this.BUILDINGS[i].id, type: this.BUILDINGS[i].type, owner: this.BUILDINGS[i].owner, color: this.PLAYER_COLORS[this.BUILDINGS[i].owner] + 'Color' })
        }
      }
      return res
    }
  }
}
</script>

<style scoped lang="scss">
.city__circle {
  fill:#000000;
  stroke-width:0.1;
  stroke-dasharray:none;
  stroke-opacity:1;
  paint-order:stroke markers fill;
}
.city__homecap {
  display:inline;
  fill:#000000;
  fill-opacity:1;
  stroke:#959595;
  stroke-width:0.0219997;
  stroke-linejoin:round;
  stroke-dasharray:none;
  stroke-opacity:1;
}

.city__housing {
  display:inline;
  fill:#000000;
  fill-opacity:1;
  stroke:#959595;
  stroke-width:0.0219997;
  stroke-linecap:butt;
  stroke-linejoin:round;
  stroke-dasharray:none;
  stroke-opacity:1;
  paint-order:normal;
}

</style>
