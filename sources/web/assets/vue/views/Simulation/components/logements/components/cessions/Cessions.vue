<template>
    <div class="cessions">
        <ApolloQuery
                :query="require('../../../../../../graphql/simulations/logements-familiaux/cessions/cessions.gql')"
                :variables="{
                simulationId: simulationID
            }">
            <template slot-scope="{ result:{ loading, error, data }, isLoading, query }">
                <el-tabs v-model="activeTab">
                    <el-tab-pane label="Cessions identifiées" name="1">
                        <cession-identifiees
                            :simulationID="simulationID"
                            :anneeDeReference="anneeDeReference"
                            :patrimoines="patrimoines"
                            :data="data"
                            :error="error"
                            :isLoading="isLoading"
                            :query="query"/>
                    </el-tab-pane>
                    <el-tab-pane label="Cessions non identifiées" name="2">
                        <cession-nonidentifiees
                            :simulationID="simulationID"
                            :anneeDeReference="anneeDeReference"
                            :data="data"
                            :error="error"
                            :isLoading="isLoading"
                            :query="query"/>
                    </el-tab-pane>
                </el-tabs>
            </template>
        </ApolloQuery>
    </div>
</template>

<script>
import CessionIdentifiees from './components/Identifiees';
import CessionNonidentifiees from './components/Nonidentifiees';

export default {
    name: "Cessions",
    components: { CessionIdentifiees, CessionNonidentifiees },
    data() {
        return {
            activeTab: null,
            simulationID: null,
            anneeDeReference: null,
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
        changeTab(tab, event) {
            this.$router.push({
                path: 'cessions',
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
    .cessions .form-slider i {
        font-size: 25px;
        margin-left: 50px;
        cursor: pointer;
    }
    .cessions .fixed-input {
        width: 80px;
    }
    .cessions .el-form-item__label {
        margin-top: 7px;
        line-height: 15px;
    }
    .cessions .el-tooltip.el-icon-info {
        font-size: 25px;
        color: #2491eb;
        vertical-align: middle;
    }
    .cessions .carousel-head {
        height: 50px;
        line-height: 16px;
    }
    .cessions .el-collapse-item__header{
        padding-left: 15px;
        font-weight: bold;
        font-size: 16px;
        color: #00436f;
        margin-top: 20px
    }
    .cessions .el-collapse-item__header:hover{
        background-color: rgba(0, 0, 0, 0.03);
    }
    .cessions .el-collapse-item__content {
        padding-top: 20px;
    }
</style>