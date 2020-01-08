<template>
    <div class="operations">
        <ApolloQuery
                :query="require('../../../../../../graphql/simulations/logements/operations/operations.gql')"
                :variables="{
                simulationId: simulationID
            }">
            <template slot-scope="{ result:{ loading, error, data }, isLoading, query }">
                <el-tabs v-model="activeTab">
                    <el-tab-pane label="Opérations identifiées" name="1">
                        <operation-identifiees
                            :simulationID="simulationID"
                            :anneeDeReference="anneeDeReference"
                            :typesEmprunts="typesEmprunts"
                            :modeleDamortissements="modeleDamortissements"
                            :profilLoyers="profilLoyers"
                            :data="data"
                            :isLoading="isLoading"
                            :error="error"
                            :query="query"
                        />
                    </el-tab-pane>
                    <el-tab-pane label="Opérations non identifiées" name="2">
                        <operation-nonidentifiees
                            :simulationID="simulationID"
                            :anneeDeReference="anneeDeReference"
                            :typesEmprunts="typesEmprunts"
                            :modeleDamortissements="modeleDamortissements"
                            :profilLoyers="profilLoyers"
                            :data="data"
                            :isLoading="isLoading"
                            :error="error"
                            :query="query"/>
                    </el-tab-pane>
                </el-tabs>
            </template>
        </ApolloQuery>
    </div>
</template>

<script>
import OperationIdentifiees from './components/Identifiees';
import OperationNonidentifiees from './components/Nonidentifiees';

export default {
    name: "Operations",
    components: { OperationIdentifiees, OperationNonidentifiees },
    data() {
        return {
            activeTab: null,
            simulationID: null,
            anneeDeReference: null,
            typesEmprunts: [],
            modeleDamortissements: [],
            profilLoyers: []
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
        this.getTypeEmprunts();
        this.getProfilLoyers();
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
        getProfilLoyers() {
            this.$apollo.query({
                query: require('../../../../../../graphql/simulations/profils-evolution-loyers/profilsEvolutionLoyers.gql'),
                variables: {
                    simulationID: this.simulationID
                }
            }).then((res) => {
                if (res.data && res.data.profilsEvolutionLoyers) {
                    this.profilLoyers = res.data.profilsEvolutionLoyers.items;
                }
            });
        },
        changeTab() {
            this.$router.push({
                path: 'operations',
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
    .operations .form-slider i {
        font-size: 25px;
        margin-left: 50px;
        cursor: pointer;
    }
    .operations .fixed-input {
        width: 80px;
    }
    .operations .el-form-item__label {
        margin-top: 7px;
        line-height: 15px;
    }
    .operations .el-tooltip.el-icon-info {
        font-size: 25px;
        color: #2491eb;
        vertical-align: middle;
    }
    .operations .carousel-head {
        height: 50px;
    }
    .operations .el-collapse-item__header{
        padding-left: 15px;
        font-weight: bold;
        font-size: 16px;
        color: #00436f;
        margin-top: 20px
    }
    .operations .el-collapse-item__header:hover{
        background-color: rgba(0, 0, 0, 0.03);
    }
    .operations .el-collapse-item__content {
        padding-top: 20px;
    }
</style>
