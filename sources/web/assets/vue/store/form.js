export default {
    namespaced: true,
    state: {
        currentEntite: null
    },
    getters: {
        currentEntite (state) {
            return state.currentEntite;
        }
    },
    mutations: {
        ['CHANGING_ENTITE'](state, payload) {
            state.currentEntite = payload.entite
        },
    },
    actions: {
        changeEntite({commit}, payload) {
            commit('CHANGING_ENTITE', payload);
        },
    },
}