<template>
    <div class="travaux">
        <ApolloQuery
                :query="require('../../../../../../graphql/simulations/logements-familiaux/travaux/travauxImmobilises.gql')"
                :variables="{
                simulationId: simulationID
            }">
            <template slot-scope="{ result:{ loading, error, data }, isLoading, query }">
            <el-tabs v-model="activeTab">
                <el-tab-pane label="Renouvellement de composant" name="1">
                    <renouvellement-composant
                        :simulationID="simulationID"
                        :anneeDeReference="anneeDeReference"
                        :typesEmprunts="typesEmprunts"
                        :modeleDamortissements="modeleDamortissements"
                        :data="data"
                        :isLoading="isLoading"
                        :query="query"
                        :error="error"/>
                </el-tab-pane>
                <el-tab-pane label="Travaux immobilisés identifiées" name="2">
                    <travaux-identifiees
                        :simulationID="simulationID"
                        :typesEmprunts="typesEmprunts"
                        :modeleDamortissements="modeleDamortissements"
                        :patrimoines="patrimoines"
                        :data="data"
                        :isLoading="isLoading"
                        :query="query"
                        :error="error"/>
                </el-tab-pane>
                <el-tab-pane label="Travaux immobilisés non identifiées" name="3">
                    <travaux-nonidentifiees
                        :simulationID="simulationID"
                        :anneeDeReference="anneeDeReference"
                        :typesEmprunts="typesEmprunts"
                        :modeleDamortissements="modeleDamortissements"
                        :data="data"
                        :isLoading="isLoading"
                        :query="query"
                        :error="error"/>
                </el-tab-pane>
            </el-tabs>
            </template>
        </ApolloQuery>
    </div>
</template>

<script>
import RenouvellementComposant from './components/RenouvellementComposant';
import TravauxIdentifiees from './components/TravauxIdentifiees';
import TravauxNonidentifiees from './components/TravauxNonidentifiees';

export default {
    name: "Travaux",
    components: { RenouvellementComposant, TravauxIdentifiees, TravauxNonidentifiees },
    data() {
        return {
            activeTab: null,
            simulationID: null,
            anneeDeReference: null,
            typesEmprunts: [],
            modeleDamortissements: [],
            patrimoines: []
        }
    },
    created () {
        const simulationID = _.isNil(this.$route.params.id) ? null : this.$route.params.id;
        if (_.isNil(simulationID)) {
            return;
        }
        this.simulationID = simulationID;
        this.activeTab = _.isNil(this.$route.query.tab) ? '1' : this.$route.query.tab;

        this.getAnneeDeReference();
        this.$apollo
            .query({
                query: require("../../../../../../graphql/administration/partagers/checkStatus.gql"),
                variables: {
                    simulationID: this.simulationID
                }
            })
            .then(response => {
                this.$store.commit('choixEntite/setModify', response.data.checkStatus);
            });
    },
    mounted(){
        this.getModeleDamortissements();
        this.getPatrimoines();
        this.getTypeEmprunts();
    },
    methods: {
        getAnneeDeReference() {
            this.$apollo.query({
                query: require('../../../../../../graphql/simulations/simulation.gql'),
                variables: {
                    simulationID: this.simulationID
                }
            }).then((res) => {
                if (res.data && res.data.simulation) {
                    this.anneeDeReference = res.data.simulation.anneeDeReference;
                }
            });
        },
        getModeleDamortissements() {
            this.$apollo.query({
                query: require('../../../../../../graphql/simulations/modeles-amortissements/modeleDamortissements.gql'),
                variables: {
                    simulationId: this.simulationID
                }
            }).then((res) => {
                if (res.data && res.data.modeleDamortissements) {
                    this.modeleDamortissements = res.data.modeleDamortissements.items;
                }
            });
        },
        getTypeEmprunts() {
            this.$apollo.query({
                query: require('../../../../../../graphql/simulations/types-emprunts/typesEmprunts.gql'),
                variables: {
                    simulationID: this.simulationID
                }
            }).then((res) => {
                if (res.data && res.data.typesEmprunts) {
                    this.typesEmprunts = res.data.typesEmprunts.items;
                }
            });
        },
        getPatrimoines() {
            this.$apollo.query({
                query: require('../../../../../../graphql/simulations/logements-familiaux/patrimoines/patrimoines.gql'),
                variables: {
                    simulationID: this.simulationID
                }
            }).then((res) => {
                if (res.data && res.data.patrimoines) {
                    this.patrimoines = res.data.patrimoines.items;
                }
            });
        },
        changeTab() {
            this.$router.push({
                path: 'travaux',
                query: { tab: this.activeTab }
            });
        }
    },
    watch: {
        'activeTab' (newVal, oldVal) {
            if (oldVal) {
                this.changeTab();
            }
        }
    }
}
</script>

<style>
    .travaux .form-slider i {
        font-size: 25px;
        margin-left: 50px;
        cursor: pointer;
    }
    .travaux .fixed-input {
        width: 80px;
    }
    .travaux .el-form-item__label {
        margin-top: 7px;
        line-height: 20px;
    }
    .travaux .el-tooltip.el-icon-info {
        font-size: 25px;
        color: #2491eb;
        vertical-align: middle;
    }
    .travaux .carousel-head {
        height: 50px;
    }
    .travaux .el-collapse-item__header{
        padding-left: 15px;
        font-weight: bold;
        font-size: 16px;
        color: #00436f;
        margin-top: 20px
    }
    .travaux .el-collapse-item__header:hover{
        background-color: rgba(0, 0, 0, 0.03);
    }
    .travaux .el-collapse-item__content {
        padding-top: 20px;
    }
</style>
