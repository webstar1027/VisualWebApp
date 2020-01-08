<template>
    <router-view></router-view>
</template>

<script>
import _ from 'lodash';

export default {
    name: 'app',
    created () {
        let isAuthenticated = JSON.parse(this.$parent.$el.attributes['data-is-authenticated'].value),
            utilisateur = JSON.parse(this.$parent.$el.attributes['data-utilisateur'].value);
        if (_.isNil(utilisateur)) {
            utilisateur = {
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
            }
            };
        let payload = {
            isAuthenticated: isAuthenticated,
            id: utilisateur.id,
            nom: utilisateur.nom,
            prenom: utilisateur.prenom,
            email: utilisateur.email,
            fonction: utilisateur.fonction,
            telephone: utilisateur.telephone,
            estAdministrateurCentral: utilisateur.estAdministrateurCentral,
            estAdministrateurSimulation: utilisateur.estAdministrateurSimulation,
            droits: utilisateur.droits,
            roles: utilisateur.roles,
            isReferent: utilisateur.isReferent
        };
        this.$store.dispatch('security/onRefresh', payload);
    },
}
</script>