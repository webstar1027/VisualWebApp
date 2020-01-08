<template>
    <cartes :cardsOne="cardsOne" :cardsTwo="cardsTwo" :titlePage="titlePage">
    </cartes>
</template>

<script>
import Cartes from '../../partials/Cartes';
export default {
    name: "Foyers",
    components: {Cartes},
    data() {
        return {
            titlePage: 'Foyers',
            counts: null,
            cardsOne: [
                {
                    title: 'Patrimoine foyers',
                    sousRubrique:[
                        { label: 'Patrimoine foyers', path: 'foyers/patrimoines', name: 'patrimoinesFoyer', total: undefined }
                    ],
                    path: 'foyers/patrimoines',
                },
                {
                    title: 'Travaux foyers',
                    sousRubrique: [
                        { label: 'Travaux',  path: 'foyers/travaux', name: 'travauxFoyers', total: undefined }
                    ],
                    path: 'foyers/travaux'
                },
                {
                    title: 'Nouveaux foyers',
                    sousRubrique: [
                        { label: 'Nouveaux', path: 'foyers/nouveaux', name: 'nouveauxFoyers', total: undefined }
                    ],
                    path: 'foyers/nouveaux'
                }
            ],
            cardsTwo: [
                {
                    title: 'Cessions foyers',
                    sousRubrique: [
                        { label: 'Cessions', path: 'foyers/cessions', name: 'cessionFoyers', total: undefined }
                    ],
                    path: 'foyers/cessions'
                },
                {
                    title: 'Autres coûts',
                    sousRubrique: [
                        { label: 'Frais de structure', path: 'foyers/frais-de-structure', name: 'foyersFraisStructures', total: undefined }
                    ],
                    path: 'foyers/frais-de-structure'
                },
                {
                    title: 'Démolitions',
                    sousRubrique: [
                        { label: 'Démolitions', path: 'foyers/demolitions', name: 'demolitionFoyers', total: undefined },
                    ],
                    path: 'foyers/demolitions'
                }
            ]
        }
    },
    created () {
        this.init();
        this.getCounts();
    },
    methods: {
        init() {
            this.counts = {
                patrimoinesFoyer: undefined,
                travauxFoyers: undefined,
                nouveauxFoyers: undefined,
                cessionFoyers: undefined,
                foyersFraisStructures: undefined,
                demolitionFoyers: undefined,
            };
            this.applyCounts([...this.cardsOne, ...this.cardsTwo]);
        },
        getCounts() {
            this.$apollo.query({
                query: require('../../../../graphql/simulations/foyers/fetchFoyerCounts.gql'),
                fetchPolicy: 'no-cache',
                variables: {
                    simulationID: this.$route.params.id
                }
            }).then((res) => {
                if (res.data && res.data.fetchFoyerCounts) {
                    this.counts = JSON.parse(res.data.fetchFoyerCounts);
                    this.applyCounts([...this.cardsOne, ...this.cardsTwo]);
                }
            });
        },
        applyCounts(array) {
            array.forEach((item) => {
                item.sousRubrique.forEach((subCategory, index) => {
                    if (Array.isArray(this.counts[subCategory.name])) {
                        subCategory.total = this.counts[subCategory.name][index];
                    } else {
                        subCategory.total = this.counts[subCategory.name];
                    }
                })
            });
        }
    }
}
</script>
