import _ from 'lodash';
import SecurityAPI from '../api/security';

export default {
    namespaced: true,
    state: {
        isLoading: false,
        error: null,
        isAuthenticated: false,
        id: null,
        nom: null,
        prenom: null,
        email: null,
        fonction: null,
        telephone: null,
        estAdministrateurCentral: false,
        estAdministrateurSimulation: false,
        droits: [],
        roles: [],
        isReferent: false
    },
    getters: {
        isLoading (state) {
            return state.isLoading;
        },
        hasError (state) {
            return state.error !== null;
        },
        error (state) {
            return state.error;
        },
        isAuthenticated (state) {
            return state.isAuthenticated;
        },
        id (state) {
            return state.id;
        },
        nom (state) {
            return state.nom;
        },
        prenom (state) {
            return state.prenom;
        },
        email (state) {
            return state.email;
        },
        fonction (state) {
            return state.fonction;
        },
        telephone (state) {
            return state.telephone;
        },
        estAdministrateurCentral (state) {
            return state.estAdministrateurCentral;
        },
        estAdministrateurSimulation (state) {
            return state.estAdministrateurSimulation;
        },
        hasDroit (state) {
            return (droit, entiteID) => {
                if (state.estAdministrateurCentral) {
                    return true;
                }
                if (_.isNil(state.droits[entiteID])) {
                    return false;
                }
                return state.droits[entiteID].indexOf(droit) !== -1;
            };
        },
        roles (state) {
            return state.roles;
        },
        hasRole (state) {
            return (role, entiteID) => {
                if (state.estAdministrateurCentral) {
                    return true;
                }
                if (_.isNil(state.roles[entiteID])) {
                    return false;
                }
                return state.roles[entiteID].includes(role);
            };
        },
        isReferentEntite (state) {
            let isReferentEntite = false;
            const roles = Object.values(state.roles);
            roles.forEach(role => {
                if (role.includes('Référent entité')) {
                    isReferentEntite = true;
                }
            })
            return isReferentEntite;
        },
        isReferentEnsemble (state) {
            let isReferentEnsemble = false;
            const roles = Object.values(state.roles);
            roles.forEach(role => {
                if (role.includes('Référent ensemble')) {
                    isReferentEnsemble = true;
                }
            })
            return isReferentEnsemble;
        },
        isReferent (state) {
            return state.isReferent;
        }
    },
    mutations: {
        ['RESET'](state) {
            state.isLoading = false;
            state.error = null;
            state.isAuthenticated = false;
            state.id = null;
            state.nom = null;
            state.prenom = null;
            state.email = null;
            state.fonction = null;
            state.telephone = null;
            state.estAdministrateurCentral = false;
            state.estAdministrateurSimulation = false;
            state.droits = [];
            state.roles = [];
            state.isReferent = false;
        },
        ['AUTHENTICATING'](state) {
            state.isLoading = true;
            state.error = null;
            state.isAuthenticated = false;
            state.id = null;
            state.nom = null;
            state.prenom = null;
            state.email = null;
            state.estAdministrateurCentral = false;
            state.estAdministrateurSimulation = false;
            state.droits = [];
            state.roles = [];
            state.isReferent = false;
        },
        ['AUTHENTICATING_SUCCESS'](state, payload) {
            state.isLoading = false;
            state.error = null;
            state.isAuthenticated = true;
            state.id = payload.id;
            state.nom = payload.nom;
            state.prenom = payload.prenom;
            state.email = payload.email;
            state.fonction = payload.fonction;
            state.telephone = payload.telephone;
            state.estAdministrateurCentral = payload.estAdministrateurCentral;
            state.estAdministrateurSimulation = payload.estAdministrateurSimulation;
            state.droits = payload.droits;
            state.roles = payload.roles;
            state.isReferent = payload.isReferent;
        },
        ['AUTHENTICATING_ERROR'](state, error) {
            state.isLoading = false;
            state.error = error;
            state.isAuthenticated = false;
            state.id = null;
            state.nom = null;
            state.prenom = null;
            state.email = null;
            state.fonction = null;
            state.telephone = null;
            state.estAdministrateurCentral = false;
            state.estAdministrateurSimulation = false;
            state.droits = [];
            state.roles = [];
            state.isReferent = false;
        },
        ['PROVIDING_DATA_ON_REFRESH_SUCCESS'](state, payload) {
            state.isLoading = false;
            state.error = null;
            state.isAuthenticated = payload.isAuthenticated;
            state.id = payload.id;
            state.nom = payload.nom;
            state.prenom = payload.prenom;
            state.email = payload.email;
            state.fonction = payload.fonction;
            state.telephone = payload.telephone;
            state.estAdministrateurCentral = payload.estAdministrateurCentral;
            state.estAdministrateurSimulation = payload.estAdministrateurSimulation;
            state.droits = payload.droits;
            state.roles = payload.roles;
            state.isReferent = payload.isReferent;
        },
        ['UPDATE_UTILISATEUR'](state, payload) {
            state.id = payload.id;
            state.nom = payload.nom;
            state.prenom = payload.prenom;
            state.email = payload.email;
            state.fonction = payload.fonction;
            state.telephone = payload.telephone;
        },
    },
    actions: {
        login ({commit}, payload) {
            commit('AUTHENTICATING');
            return SecurityAPI.login(payload.login, payload.password)
                .then(res => commit('AUTHENTICATING_SUCCESS', res.data))
                .catch(err => commit('AUTHENTICATING_ERROR', err));
        },
        logout ({ commit }) {
            commit('RESET');
            return SecurityAPI.logout();
        },
        onRefresh({commit}, payload) {
            commit('PROVIDING_DATA_ON_REFRESH_SUCCESS', payload);
        },
        confirmPassword ({commit}, payload) {
            return SecurityAPI.confirmPassword(payload.token, payload.password);
        },
        updateUtilisateur({commit}, payload) {
            commit('UPDATE_UTILISATEUR', payload);
        }
    },
}
