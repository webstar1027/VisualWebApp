export default {
  namespaced: true,
  state: {
      userSelectedId: null,
      disable: false,
      shareType: null,
      isModify: false
  },
  getters: {
    getUserSelectedId (state) {
      return state.userSelectedId;
    },
    getDisable (state) {
      return state.disable;
    },
    isModify (state) {
      return state.isModify;
    }
  },
  mutations: {
    SET_ENTITE_ID: (state, entiteID) => {
      state.userSelectedId = entiteID;
    },
    SET_DISABLE: (state, status) => {
      state.disable = status;
    },
    setUserType: (state, type) => {
      state.shareType = type;
    },
    setModify: (state, flag) => {
      state.isModify = flag;
    }
  },
  actions: {
    setEntiteId({ commit }, entiteID) {
      commit('SET_ENTITE_ID', entiteID);
    },
    setDisable({ commit }, status) {
      commit('SET_DISABLE', status);
    },
  }
}