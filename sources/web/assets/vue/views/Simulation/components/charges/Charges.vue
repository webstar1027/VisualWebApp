<template>
    <cartes :cardsOne="cardsOne" :cardsTwo="cardsTwo" :titlePage="titlePage">
    </cartes>
</template>
<script>
    import Cartes from '../../partials/Cartes';

    export default {
        name: "Charges",
        components: {Cartes},
        data() {
            return {
                titlePage: 'Charges',
                cardsOne: [
                    {
                        title: 'Annuités',
                        sousRubrique: [
                            { label: 'Option de calcul des annuités d\'emprunt', path: 'charges/annuites?tab=1', name: 'optionAnnuites', total: 777 },
                            { label: 'Détail des annuités des emprunts locatifs', path: 'charges/annuites?tab=2', name: 'annuites', total: 777 },
                            { label: 'Autres emprunts', path: 'charges/annuites?tab=3', name: 'autresEmprunts', total: 777 }
                        ],
                        path: 'charges/annuites',
                        queryPath: require("../../../../graphql/simulations/charges/annuites/annuites.gql"),
                    },
                    {
                        title: 'Autres charges',
                        sousRubrique: [
                            { label: 'Autres charges', path: 'charges/autres-charges', name: 'autresCharges', total: 777 },
                        ],
                        path: 'charges/autres-charges',
                        queryPath: require("../../../../graphql/simulations/charges/autresCharges.gql"),
                    },
                    {
                        title: 'Maintenance',
                        sousRubrique: [
                            { label: 'Détail de la maintenance', path: 'charges/maintenance?tab=1', name: 'maintenance', total: 777 },
                            { label: 'Détail du gros entretien', path: 'charges/maintenance?tab=2', name: 'grosEntretien', total: 777 }
                        ],
                        path: 'charges/maintenance',
                        queryPath: require("../../../../graphql/simulations/charges/maintenance/maintenance.gql"),
                    }
                ],
                cardsTwo: [
                    {
                        title: 'Risques locatifs',
                        sousRubrique: [
                            { label: 'Risques Locatifs', path: 'charges/risques-locatifs', name: 'risquesLocatifs', total: 777  }
                        ],
                        path: 'charges/risques-locatifs',
                        queryPath: require("../../../../graphql/simulations/charges/risquesLocatifs/risquesLocatifs.gql"),
                    },
                    {
                        title: 'CGLLS et ANCOLS',
                        sousRubrique: [
                            { label: 'CGLLS et ANCOLS', path: 'charges/cglls-ancols', name: 'cgllsAncols', total: 777  }
                        ],
                        path: 'charges/cglls-ancols',
                        queryPath: require("../../../../graphql/simulations/charges/cgllsAncols/cgllsAncols.gql"),
                        variables: {
                            simulationID: this.$route.params.id
                        }
                    }
                ],
            }
        },
    }
</script>
