<template>
    <div class="autresCouts">
        <el-tabs v-model="activeTab">
            <el-tab-pane label="Codifications" name="1">
                <codifications
                        :simulationId="simulationId"
                        :showError="showError"
                />
            </el-tab-pane>
            <el-tab-pane label="Frais de structure" name="2">
                <frais-de-structure
                        :simulationId="simulationId"
                        :anneeDeReference="anneeDeReference"
                        :showError="showError"
                />
            </el-tab-pane>
            <el-tab-pane label="Produits et charges" name="3">
                <produits-charges
                        :simulationId="simulationId"
                        :anneeDeReference="anneeDeReference"
                        :showError="showError"
                />
            </el-tab-pane>
        </el-tabs>
    </div>
</template>

<style>
    .autresCouts .form-slider i {
        font-size: 25px;
        margin-left: 50px;
        cursor: pointer;
    }
    .autresCouts .fixed-input {
        width: 80px;
    }
    .autresCouts .el-form-item__label {
        margin-top: 7px;
    }
    .autresCouts .carousel-head {
        height: 50px;
    }
    .autresCouts .el-input-group__append {
        padding: 0 10px;
    }
</style>
<script>
    import Codifications from "../../../accession/components/autresCouts/components/Codifications";
    import FraisDeStructure from "../../../accession/components/autresCouts/components/FraisDeStructure";
    import ProduitsCharges from "./components/ProduitsEtCharges";
    export default {
        name: "AutresCouts",
        components: {ProduitsCharges, FraisDeStructure, Codifications},
        data () {
            return {
                simulationId: null,
                anneeDeReference: null,
                activeTab: '1',
            }
        },

        computed: {
            typeCodifications () {
                return this.activeTab === '1'
            },
            typeFraisDeStructure () {
                return this.activeTab === '2'
            },
            typeProduitsEtCharges () {
                return this.activeTab === '3'
            },
            typeLabel () {
                if (this.activeTab === '1') {
                    return 'Codifications'
                }
                if (this.activeTab === '2') {
                    return 'Frais de structure'
                }
                if (this.activeTab === '3') {
                    return 'Produits et charges'
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
            }
        }

    }
</script>
