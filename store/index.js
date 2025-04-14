import Vue from 'vue'
import Vuex from 'vuex'
import user from './modules/user'
import rsoc from './modules/rsoc'
import keyword from './modules/keyword'
import report from './modules/report'

Vue.use(Vuex)

export default new Vuex.Store({
  modules: {
    user,
    rsoc,
    keyword,
    report
  },
  state: {
    sidebarCollapsed: false
  },
  mutations: {
    TOGGLE_SIDEBAR: state => {
      state.sidebarCollapsed = !state.sidebarCollapsed
    }
  },
  actions: {
    toggleSidebar({ commit }) {
      commit('TOGGLE_SIDEBAR')
    }
  },
  getters: {
    sidebarCollapsed: state => state.sidebarCollapsed
  }
})
