<template>
    <cartes :cardsOne="cardsOne" :titlePage="titlePage">
    </cartes>
</template>

<script>
import Cartes from '../../partials/Cartes';

export default {
    name: "ResultatComptable",
    components: {Cartes},
    data() {
        return {
            titlePage: 'Résultat comptable',
            subCategorytotal : '05117',
            cardsOne: [
                {
                    title: 'Modèles d\'amortissement',
                    sousRubrique: [
                        { label: 'Modèles', path: 'resultat-comptable/modeles-amortissement',  name: 'modeleDamortissements', total: 777 }
                    ],
                    path: 'resultat-comptable/modeles-amortissement',
                    queryPath: require('../../../../graphql/simulations/modeles-amortissements/modeleDamortissements.gql')
                },
                {
                    title: 'Résultat comptable',
                    sousRubrique: [
                        { label: 'Données à saisir', path: 'resultat-comptable/donnees-du-resultat',  name: 'resultatComptables', total: 777 }
                    ],
                    path: 'resultat-comptable/donnees-du-resultat',
                    queryPath: require('../../../../graphql/simulations/resultatComptables/resultatComptables.gql')
                }
            ]
        }
    },
}
</script>
