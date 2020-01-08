<template>
	<el-breadcrumb style="margin: 0" separator="/">
		<el-breadcrumb-item v-if="bcItems.length === 1" :to="{ path: '/' }">Gestion des simulations</el-breadcrumb-item>
        <template v-else v-for="item in bcItems">
            <el-breadcrumb-item :to="{ path: item.path }">
                <span @click="handleRouteError($event, item.path)">{{ item.name }}</span>
            </el-breadcrumb-item>
        </template>
	</el-breadcrumb>
</template>
<script>
export default {
	name: "Breadcrumb",
    data () {
	    return {
	        currentPath: null,
            bcItems: [],
            bcLabel: {
	            'simulations': 'Gestion des simulations',
                'simulation': 'Simulation',
                'indices-taux': 'Indice et Taux',
                'structure': 'Structure Financière',
                'annuites': 'Annuités',
                'risques-locatifs': 'Risques Locatifs',
                'cglls-ancols': 'CGLLS et ANCOLS',
                'profils-evolution-loyers': 'Profil d\'évolution des Loyers',
                'autres': 'Autres Produits',
                'resultat-comptable': 'Résultat comptable',
                'modeles-amortissement': 'Modèles d\'amortissement',
                'patrimoine-reference': 'Patrimoine de référence',
                'types-emprunts': 'Types d\'emprunts',
                'hypotheses': 'Hypothèses liées aux investissements',
                'logements': 'Logements Familiaux',
                'demolitions': 'Démolitions'
            }
        }
    },
    created () {
	    this.currentPath = this.$route.path
        this.analyzePath()
    },
    watch:{
        $route (to, from){
            this.currentPath = to.path
            this.analyzePath()
        }
    },
    methods: {
        analyzeName (str) {
            let res = str.charAt(0).toUpperCase() + str.substring(1).toLowerCase()
            res = res.replace(/-/g, ' ');
            return res
        },
	    analyzePath () {
	        if (this.currentPath) {
	            let arr = this.currentPath.split('/')
                let filteredArr = arr.filter(name => (name !== "" && name !== '/'))
                this.bcItems = filteredArr.map(path => {
                    let index = this.currentPath.indexOf(path)
                    let name = path
                    if (path !== this.$route.params.id) {
                        name = this.bcLabel[path] || this.analyzeName(path)
                    }
                    return {
                        name: name,
                        path: this.currentPath.substr(0, index + path.length)
                    };
                })
                if (this.bcItems.length > 1) {
                    this.handleSimulationId()
                }
            }
        },
        handleSimulationId () {
	        if (this.bcItems) {
	            if (this.bcItems.length === 2) {
	                this.bcItems[0].path = this.bcItems[1].path
                    this.bcItems[1].name = "Tableau de bord"
                } else if (this.bcItems.length > 2) {
                    this.bcItems[0].path = this.bcItems[1].path
                    this.bcItems = this.bcItems.filter(item => item.name !== this.$route.params.id)
                }
            }
        },
        handleRouteError (event, path) {
            if (path === this.$route.path) {
                event.stopPropagation();
            }
        }
    }
};
</script>
