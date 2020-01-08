<template>
    <cartes :cardsOne="cardsOne" :cardsTwo="cardsTwo" :titlePage="titlePage">
    </cartes>
</template>

<script>
import Cartes from '../../partials/Cartes';
export default {
    name: "Accession",
    components: {Cartes},
    data() {
        return {
            titlePage: 'Accession',
            counts: null,
            cardsOne: [
                {
                    title: 'PSLA',
                    sousRubrique:[
                        { label: 'Identifiés', path: 'accession/psla?tab=1', name: 'pslas', total: undefined },
                        { label: 'Non identifiés', path: 'accession/psla?tab=2', name: 'pslas', total: undefined }
                    ],
                    path: 'accession/psla'
                },
                {
                    title: 'VEFA',
                    sousRubrique: [
                        { label: 'Identifiés', path: 'accession/vefa?tab=1', name: 'vefa', total: undefined },
                        { label: 'Non identifiés', path: 'accession/vefa?tab=2', name: 'vefa', total: undefined },
                    ],
                    path: 'accession/vefa'
                },
                {
                    title: 'Lotissement',
                    sousRubrique: [
                        { label: 'Identifiés', path: 'accession/lotissement?tab=1', name: 'lotissements', total: undefined },
                        { label: 'Non identifiés', path: 'accession/lotissement?tab=2', name: 'lotissements', total: undefined }
                    ],
                    path: 'accession/lotissement'
                }
            ],
            cardsTwo: [
                {
                    title: 'CCMI',
                    sousRubrique: [
                        { label: 'CCMI', path: 'accessions/ccmi', name: 'ccmi', path: 'accession/ccmi', total: undefined }
                    ],
                    path: 'accession/ccmi',
                },
                {
                    title: 'Autres coûts',
                    sousRubrique: [
                        { label: 'Codifications', path: 'accession/autres-Couts?tab=1', name: 'accessionCodifications', total: undefined },
                        { label: 'Frais de structure', path: 'accession/autres-Couts?tab=2', name: 'accessionFraisStructures', total: undefined },
                        { label: 'Produits et charges', path: 'accession/autres-Couts?tab=3', name: 'accessionProduitCharges', total: undefined }
                    ],
                    path: 'accession/autres-Couts',
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
                pslas: [undefined, undefined],
                vefa: [undefined, undefined],
                lotissements: [undefined, undefined],
                ccmi: undefined,
                accessionCodifications: undefined,
                accessionFraisStructures: undefined,
                accessionProduitCharges: undefined,
            };
            this.applyCounts([...this.cardsOne, ...this.cardsTwo]);
        },
        getCounts() {
            this.$apollo.query({
                query: require('../../../../graphql/simulations/accessions/fetchAccessionCounts.gql'),
                fetchPolicy: 'no-cache',
                variables: {
                    simulationID: this.$route.params.id
                }
            }).then((res) => {
                if (res.data && res.data.fetchAccessionCounts) {
                    this.counts = JSON.parse(res.data.fetchAccessionCounts);
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
