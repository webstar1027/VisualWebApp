<template>
    <cartes :cardsOne="cardsOne" :titlePage="titlePage">
    </cartes>
</template>

<script>
    import Cartes from '../../partials/Cartes';

    export default {
        name: "Strucuture",
        components: {Cartes},
        data() {
            return {
                titlePage: 'Structure financière',
                cardsOne: [
                    {
                        title: 'Fond de roulement LT',
                        sousRubrique: [
                            {label: 'Autres variations', path: 'structure/fond-roulement?tab=1', total: 777},
                            {label: 'Provisions de haut de bilan', path: 'structure/fond-roulement?tab=2', total: 777},
                        ],
                        path: 'structure/fond-roulement'
                    },
                    {
                        title: 'Portage et Trésorerie',
                        sousRubrique: [
                            {label: 'Portage moyen terme des investissements', path: 'structure/portage-tresorerie?tab=1', total: 777},
                            {label: 'Portage court terme des investissements',  path: 'structure/portage-tresorerie?tab=2', total: 777},
                            {label: 'Autres variations potentiel financier',  path: 'structure/portage-tresorerie?tab=3', total: 777}
                            ],
                        path: 'structure/portage-tresorerie'
                    },
                ],
            }
        },
    }
</script>
