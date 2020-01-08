<template>
    <cartes :cardsTwo="cardsTwo" :cardsOne="cardsOne" :titlePage="titlePage">
    </cartes>
</template>

<script>
import Cartes from '../../partials/Cartes';

export default {
    name: "Logements",
    components: {Cartes},
    data() {
        return {
            titlePage: 'Logements familiaux',
            counts: null,
            cardsOne: [
                {
                    title: 'Patrimoine',
                    sousRubrique:[
                        { label: 'Logements', path: 'logements/patrimoines', name: 'patrimoines', total: undefined }
                    ],
                    path: 'logements/patrimoines',
                },
                {
                    title: 'Travaux immobilisés',
                    sousRubrique: [
                        { label: 'Renouvellement de composant', path: 'logements/travaux?tab=1', name: 'travauxImmobilises', total: undefined },
                        { label: 'Identifiés', path: 'logements/travaux?tab=2', name: 'travauxImmobilises', total: undefined },
                        { label: 'Non identifiés', path: 'logements/travaux?tab=3', name: 'travauxImmobilises', total: undefined }
                    ],
                    path: 'logements/travaux',
                },
                {
                    title: 'Opérations nouvelles',
                    sousRubrique: [
                        { label: 'Identifiées', path: 'logements/operations?tab=1', name: 'operations', total: undefined },
                        { label: 'Non identifiées', path: 'logements/operations?tab=2', name: 'operations', total: undefined }
                    ],
                    path: 'logements/operations',
                }
            ],
            cardsTwo :[
                {
                    title: 'Cessions',
                    sousRubrique: [
                        { label: 'Identifiées', path: 'logements/cessions?tab=1', name: 'cessions', total: undefined },
                        { label: 'Non identifiées', path: 'logements/cessions?tab=2', name: 'cessions', total: undefined }
                    ],
                    path: 'logements/cessions',
                },
                {
                    title: 'Vacance',
                    sousRubrique :[
                        {label: 'Identifiée', path: 'logements/vacance', name: 'vacance', total: undefined }
                    ],
                    path: 'logements/vacance'
                },
                {
                    title: 'Démolitions',
                    sousRubrique: [
                        { label: 'Identifiées', path: 'logements/demolitions?tab=1', name: 'demolitions', total: undefined },
                        { label: 'Non identifiées', path: 'logements/demolitions?tab=2', name: 'demolitions', total: undefined }
                    ],
                    path: 'logements/demolitions',
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
                patrimoines: undefined,
                travauxImmobilises: [undefined, undefined, undefined],
                operations: [undefined, undefined],
                cessions: [undefined, undefined],
                vacance: undefined,
                demolitions: [undefined, undefined],
            };
            this.applyCounts([...this.cardsOne, ...this.cardsTwo]);
        },
        getCounts() {
            this.$apollo.query({
                query: require('../../../../graphql/simulations/logements-familiaux/fetchLogementCounts.gql'),
                fetchPolicy: 'no-cache',
                variables: {
                    simulationID: this.$route.params.id
                }
            }).then((res) => {
                if (res.data && res.data.fetchLogementCounts) {
                    this.counts = JSON.parse(res.data.fetchLogementCounts);
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
