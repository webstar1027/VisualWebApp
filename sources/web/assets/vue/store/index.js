import Vue from 'vue';
import Vuex from 'vuex';
import SecurityModule from './security';
import ChoixEntiteModule from './choixEntite';

Vue.use(Vuex);

export default new Vuex.Store({
    modules: {
        security: SecurityModule,
        choixEntite: ChoixEntiteModule
    },
});