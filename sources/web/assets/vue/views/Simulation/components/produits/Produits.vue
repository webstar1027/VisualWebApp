<template>
    <cartes :cardsOne="cardsOne" :titlePage="titlePage">
    </cartes>
</template>

<script>
import Cartes from '../../partials/Cartes';

export default {
    name: "Produits",
    components: {Cartes},
    data() {
        return {
            titlePage: 'Produits',
            cardsOne: [
                {
                    title: 'Loyers',
                    sousRubrique: [
                        { label: 'Loyers et RLS', path: 'produits/loyers?tab=1', name: 'loyers', total: 777, variables: { type: 1 } },
                        { label: 'Autres loyers', path: 'produits/loyers?tab=2', name: 'loyers', total: 777, variables: { type: 2 } },
                    ],
                    path: 'produits/loyers',
                    queryPath: require('../../../../graphql/simulations/produits/loyers/loyers.gql'),
                    variables: { simulationId: this.$route.params.id }
                } ,
                {
                    title: 'Autres produits',
                    sousRubrique: [
                        { label: 'Autres', path: 'produits/autres', name: 'produitAutres', total: 777 }
                    ],
                    path: 'produits/autres',
                    queryPath: require('../../../../graphql/simulations/produits/autres/produitAutres.gql')
                },
            ],
        }
    },
}
</script>
