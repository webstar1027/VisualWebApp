<template>
    <ApolloQuery
            :query="require('../../../../../../graphql/simulations/logements-familiaux/demolitions/demolitions.gql')"
            :variables="{
                simulationId: simulationID
            }">
        <template slot-scope="{ result:{ loading, error, data }, isLoading, query }">
            <div class="demolitions">
                <el-tabs v-model="activeTab">
                    <el-tab-pane label="Démolitions identifiées" name="1">
                        <demolition-identifiees
                            :simulationID="simulationID"
                            :anneeDeReference="anneeDeReference"
                            :typesEmprunts="typesEmprunts"
                            :patrimoines="patrimoines"
                            :data="data"
                            :isLoading="isLoading"
                            :query="query"
                            :error="error"/>
                    </el-tab-pane>
                    <el-tab-pane label="Démolitions non identifiées" name="2">
                        <demolition-nonidentifiees
                            :simulationID="simulationID"
                            :anneeDeReference="anneeDeReference"
                            :typesEmprunts="typesEmprunts"
                            :data="data"
                            :isLoading="isLoading"
                            :query="query"
                            :error="error"/>
                    </el-tab-pane>
                </el-tabs>
            </div>
        </template>
    </ApolloQuery>
</template>

<script>
import DemolitionIdentifiees from './components/Identifiees';
import DemolitionNonidentifiees from './components/Nonidentifiees';

export default {
    name: "Demolitions",
    components: { DemolitionIdentifiees, DemolitionNonidentifiees },
    data() {
        return {
            activeTab: null,
            simulationID: null,
            anneeDeReference: null,
            typesEmprunts: [],
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
        this.getTypeEmprunts();
        this.getPatrimoines();
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
                path: 'demolitions',
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
    .demolitions .form-slider i {
        font-size: 25px;
        margin-left: 50px;
        cursor: pointer;
    }
    .demolitions .fixed-input {
        width: 80px;
    }
    .demolitions .el-form-item__label {
        margin-top: 7px;
    }
    .demolitions .el-tooltip.el-icon-info {
        font-size: 25px;
        color: #2491eb;
        vertical-align: middle;
    }
    .demolitions .carousel-head {
        height: 50px;
    }
    .demolitions .el-collapse-item__header{
        padding-left: 15px;
        font-weight: bold;
        font-size: 16px;
        color: #00436f;
        margin-top: 20px
    }
    .demolitions .el-collapse-item__header:hover{
        background-color: rgba(0, 0, 0, 0.03);
    }
    .demolitions .el-collapse-item__content {
        padding-top: 20px;
    }
</style>