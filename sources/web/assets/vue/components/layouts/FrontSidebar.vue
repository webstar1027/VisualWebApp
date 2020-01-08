<template>
    <div class="sidebar sidebar-admin bo">
        <div class="bo-sim-title px-3 text-right border-bottom d-flex">
            <span @click="navigateToMenu('partage-parametres')" style="padding-top: 12px;" v-if="this.$store.state.choixEntite.shareType !== 'shared'">
                <md-icon class="pr-2 pl-1" style="cursor: pointer;">settings</md-icon>
            </span>
            <span class="d-block font-weight-medium text-uppercase">{{this.codeVisial}}</span>
        </div>
        <div class="bo-menu">
            <div class="bo-menu-item-wrap">
                <el-button class="bo-menu-item" :class="{'active':isMainRoute}" @click="navigateToMenu('tableauDeBord')">
                    <md-icon class>dashboard</md-icon>
                    <span class="bo-menu-item-title">Tableau de bord</span>
                </el-button>
            </div>
            <div class="bo-menu-item-wrap">
                <el-button class="bo-menu-item" :class="{'active': isRoute('indices-taux')}">
                    <md-icon class>show_chart</md-icon>
                    <span class="bo-menu-item-title" @click="navigateToMenu('indices-taux')">Indice Et Taux</span>
                </el-button>
            </div>
            <div class="bo-menu-item-wrap">
                <div class="bo-menu-item">
                    <md-icon class>euro_symbol</md-icon>
                    <span class="bo-menu-item-title">Données financières</span>
                </div>
                <div class="bo-submenu">
                    <el-button class="bo-submenu-item" :class="{'current':isRoute('structure')}"  @click="navigateToMenu('structure')">Structure Financière
                    </el-button>
                    <el-button class="bo-submenu-item" :class="{'current':isRoute('produits')}"  @click="navigateToMenu('produits')">Produits</el-button>
                    <el-button class="bo-submenu-item" :class="{'current':isRoute('charges')}"  @click="navigateToMenu('charges')">Charges</el-button>
                    <el-button class="bo-submenu-item" :class="{'current':isRoute('resultat-comptable')}"  @click="navigateToMenu('resultat-comptable')">Résultat comptable
                    </el-button>
                </div>
            </div>
            <div class="bo-menu-item-wrap">
                <div class="bo-menu-item">
                    <md-icon class>settings_input_composite</md-icon>
                    <span class="bo-menu-item-title">Paramètres activités</span>
                </div>
                <div class="bo-submenu">
                    <el-button class="bo-submenu-item" :class="{'current':isRoute('profils-evolution-loyers')}" @click="navigateToMenu('profils-evolution-loyers')">Profil d'évolution des
                        loyers
                    </el-button>
                    <el-button class="bo-submenu-item" :class="{'current':isRoute('types-emprunts')}" @click="navigateToMenu('types-emprunts')">Types d'emprunts
                    </el-button>
                    <el-button class="bo-submenu-item" :class="{'current':isRoute('hypotheses')}" @click="navigateToMenu('hypotheses')">Hypothèses liées aux
                        investissements
                    </el-button>
                </div>
            </div>
            <div class="bo-menu-item-wrap">
                <div class="bo-menu-item">
                    <md-icon class>insert_chart</md-icon>
                    <span class="bo-menu-item-title">Activités</span>
                </div>
                <div class="bo-submenu">
                    <el-button class="bo-submenu-item" :class="{'current': isRoute('logements')}" @click="navigateToMenu('logements')">Logements familiaux
                    </el-button>
                    <el-button class="bo-submenu-item" :class="{'current': isRoute('foyers')}" @click="navigateToMenu('foyers')">Foyers</el-button>
                    <el-button class="bo-submenu-item" :class="{'current': isRoute('accession')}" @click="navigateToMenu('accession')">Accession</el-button>
                    <el-button class="bo-submenu-item" :class="{'current': isRoute('autres-activites')}" @click="navigateToMenu('autres-activites')">Autres activités</el-button>
                </div>
            </div>
        </div>
        <!--<div class="menu-icon mt-auto d-flex justify-content-center py-4 resultats-btn-wrap bg-primary">
            <el-button class="border" size="medium">Résultats</el-button>
        </div> -->
    </div>
</template>
<script>
    export default {
        name: "FrontSidebar",
        data() {
            return {
                simulationID: null,
                codeVisial: null,
            }
        },
        created() {
            this.simulationID = this.$route.params.id;
            this.getSimulationCodeVisial();
        },
        computed:{
            routeArr(){
                return this.$route.path.split('/')
            },
            isMainRoute(){
                return this.routeArr.length === 3
            },
            settingUrl() {
                return `/simulation/${this.simulationID}/partage-parametres`;
            }
        },
        methods: {
            navigateToMenu(rubrique) {
                if (!this.isRoute(rubrique) && !(this.isMainRoute && rubrique === 'tableauDeBord')) {
                    if (rubrique === 'tableauDeBord') {
                        this.$router.push('/simulation/' + this.simulationID);
                    } else {
                        this.$router.push('/simulation/' + this.simulationID + '/' + rubrique);
                    }
                }
            },

            isRoute(route){
                return this.routeArr.includes(route)
            },

            getSimulationCodeVisial() {
                this.$apollo.query({
                    query: require('../../graphql/simulations/simulation.gql'),
                    variables: {
                        simulationID: this.simulationID
                    }
                }).then((res) => {
                    if (res.data && res.data.simulation) {
                        this.codeVisial = res.data.simulation.anneeDeReference  + '_['+ res.data.simulation.entite.code +']_' + res.data.simulation.incrementation;
                    }
                });
            },
        },
        watch: {
            $route(to) {
                this.simulationID = to.params.id;
            }
        }
    };
</script>
