import Vue from 'vue'
import Vuetify from 'vuetify'
import store from '~/store'
import router from '~/router'
import { i18n } from '~/plugins'
import App from '~/components/App'
import '~/components'
import VeHistogram from 'v-charts/lib/histogram.common'

Vue.component(VeHistogram.name, VeHistogram)
Vue.use(Vuetify, {
  theme: {
    "primary": "#3c8dbc",
    "secondary": "#5cb85c",
    "accent": "#82B1FF",
    "error": "#5bc0de",
    "info": "#00c0ef",
    "success": "#00a65a",
    "warning": "#f39c12"
  }
})

Vue.config.productionTip = false

new Vue({
  el: '#app',
  i18n,  
  store,
  router,
  ...App
})
