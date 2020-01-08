<template>
    <div class="vefa">
        <el-tabs v-model="activeTab">
            <el-tab-pane label="VEFA identifiées" name="1">
                <vefa-identifiees
                        :simulationId="simulationId"
                        :anneeDeReference="anneeDeReference"
                        :showError="showError"/>
            </el-tab-pane>
            <el-tab-pane label="VEFA non identifiées" name="2">
                <vefa-non-identifiees
                        :simulationId="simulationId"
                        :anneeDeReference="anneeDeReference"
                        :showError="showError"/>
            </el-tab-pane>
        </el-tabs>
    </div>
</template>
<style>
    .vefa .form-slider i {
        font-size: 25px;
        margin-left: 50px;
        cursor: pointer;
    }
    .vefa .fixed-input {
        width: 80px;
    }
    .vefa .el-form-item__label {
        margin-top: 7px;
    }
    .vefa .el-tooltip.el-icon-info {
        font-size: 25px;
        color: #2491eb;
        vertical-align: middle;
    }
    .vefa .carousel-head {
        height: 50px;
    }
    .vefa .el-input-group__append {
        padding: 0 10px;
    }
    .vefa .el-collapse-item__header{
        padding-left: 15px;
        font-weight: bold;
        font-size: 16px;
        color: #00436f;
        margin-top: 20px
    }
    .vefa .el-collapse-item__header:hover{
        background-color: rgba(0, 0, 0, 0.03);
    }
    .vefa .el-collapse-item__content {
        padding-top: 20px;
    }
</style>
<script>
    import VefaIdentifiees from './components/Identifiees'
    import VefaNonIdentifiees from './components/NonIdentifiees'
    export default {
        name: "vefa",
        components: { VefaIdentifiees, VefaNonIdentifiees },
        data () {
            return {
                simulationId: null,
                anneeDeReference: null,
                activeTab: '1',
            }
        },
        computed: {
            typeIdentifiee () {
                return this.activeTab === '1'
            },
            typeNonIdentifiee () {
                return this.activeTab === '2'
            },
            typeLabel () {
                if (this.activeTab === '1') {
                    return 'Identifiée'
                }
                if (this.activeTab === '2') {
                    return 'Non identifiée'
                }
            }
        },
        created () {
            this.simulationId = _.isNil(this.$route.params.id) ? null : this.$route.params.id;
            if (_.isNil(this.simulationId)) {
                return;
            }
            let activeTab = _.isNil(this.$route.query.tab) ? null : this.$route.query.tab;
            if (_.isNil(activeTab)) {
                this.activeTab = '1'
            } else {
                this.activeTab = activeTab;
            }
            this.getAnneeDeReference()
            this.$apollo
                .query({
                    query: require("../../../../../../graphql/administration/partagers/checkStatus.gql"),
                    variables: {
                      simulationID: this.simulationId
                    }
                })
                .then(response => {
                    this.$store.commit('choixEntite/setModify', response.data.checkStatus);
                });
        },
        methods: {
            getAnneeDeReference() {
                this.$apollo.query({
                    query: require('../../../../../../graphql/simulations/simulation.gql'),
                    variables: {
                        simulationID: this.simulationId
                    }
                }).then((res) => {
                    if (res.data && res.data.simulation) {
                        this.anneeDeReference = res.data.simulation.anneeDeReference;
                    }
                });
            },
            showError () {
                if (!this.inputError) {
                    this.inputError = true;
                    this.$message({
                        showClose: true,
                        message: 'Les valeurs entrées doivent être valides.',
                        type: 'error',
                        onClose: () => {
                            this.inputError = false
                        }
                    });
                }
            },
            async updateData (query, type) {
                await query.fetchMore({
                    variables: {
                        simulationId: this.simulationId,
                        type: type
                    },
                    updateQuery: (prev, { fetchMoreResult  }) => {
                        return fetchMoreResult
                    }
                })
            },
        }
    }
</script>
