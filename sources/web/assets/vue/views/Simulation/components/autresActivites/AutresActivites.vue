<template>
	<cartes :cardsOne="cardsOne" :cardsTwo="cardsTwo" :titlePage="titlePage">
	</cartes>
</template>

<script>
import Cartes from '../../partials/Cartes';

export default {
    name: "AutresActivites",
    created () {
        this.runQueries([...this.cardsOne, ...this.cardsTwo])
    },
    components:{Cartes},
    data() {
        return {
            titlePage: 'Autres activités',
            cardsOne: [
                {
                    title: 'Produits et charges',
                    sousRubrique:[
                        { label: 'Produits et charges', path: 'autres-activites/produits-et-charges', name: 'produitCharges', total: undefined }
                    ],
                    queryPath: require('../../../../graphql/simulations/produit-charge/produitCharges.gql'),
                    variables: { simulationId: this.$route.params.id },
					path: 'autres-activites/produits-et-charges'
                },
                {
                    title: 'Frais de structure',
                    sousRubrique:[
                        { label: 'Frais de structure', path: 'autres-activites/frais-de-structure', name: 'fraisStructures', total: undefined }
                    ],
                    queryPath: require('../../../../graphql/simulations/frais/fraisStructures.gql'),
                    variables: { simulationId: this.$route.params.id },
					path: 'autres-activites/frais-de-structure'
                },
            ],
            cardsTwo: [
                {
                    title: 'Récapitulatifs',
                    sousRubrique: [
                        { label: 'Récapitulatifs', path: 'autres-activites/recapitulatifs', name: 'produitCharges', total: undefined  }
                    ],
                    queryPath: require('../../../../graphql/simulations/produit-charge/produitCharges.gql'),
                    variables: { simulationId: this.$route.params.id },
					path: 'autres-activites/recapitulatifs'
                },
                {
                    title: 'Codifications',
                    sousRubrique: [
                        { label: 'Cessions', path: 'autres-activites/codifications', name: 'codifications', total: undefined  }
                    ],
                    queryPath: require('../../../../graphql/simulations/autres-activites/codifications.gql'),
                    variables: { simulationId: this.$route.params.id },
					path: 'autres-activites/codifications'
                }
            ]
        }
    },
    methods: {
        runQueries(array) {
            array.forEach((item) => {
                item.sousRubrique.forEach(subCategory => {
                    if ('queryPath' in item) {
                        subCategory.queryPath = item.queryPath
                        /**
                         **   If we have specific variables for each category, we merge with the card variables
                         **   otherwise we take the card variable, and if there are none, we set the simulationId by default
                         **/
                        if ('variables' in subCategory) {
                            subCategory.variables = Object.assign(item.variables, subCategory.variables)
                        } else {
                            subCategory.variables = item.variables || {simulationId: this.$route.params.id}
                        }
                        subCategory.variables.total = true

                        this.$apollo.query({
                            query: subCategory.queryPath,
                            variables: subCategory.variables
                        }).then(response => {
                            subCategory.total = response.data[subCategory.name].count
                        });
                    } else  {
                        subCategory.total = '05117'
                    }
                })
            });
        }
    }
}
</script>
