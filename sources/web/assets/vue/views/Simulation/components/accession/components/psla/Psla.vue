<template>
    <div class="accession-psla">
        <el-tabs v-model="activeTab">
            <el-tab-pane label="PSLA identifié" name="1">
                <psla-identifiees
                    :simulationID="simulationID"
                    :anneeDeReference="anneeDeReference"
                    :typesEmprunts="typesEmprunts"
                    :showError="showError"/>
            </el-tab-pane>
            <el-tab-pane label="PSLA non identifié" name="2">
                <psla-nonidentifiees
                    :simulationID="simulationID"
                    :anneeDeReference="anneeDeReference"
                    :typesEmprunts="typesEmprunts"
                    :showError="showError"/>
            </el-tab-pane>
        </el-tabs>
    </div>
</template>

<script>
import PslaIdentifiees from './components/Identifies';
import PslaNonidentifiees from './components/Nonidentifies';

export default {
    name: "AccessionPsla",
    components: { PslaIdentifiees, PslaNonidentifiees },
    data() {
        return {
            activeTab: '1',
            simulationID: null,
            anneeDeReference: null,
            typesEmprunts: []
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
        changeTab(tab, event) {
            this.$router.push({
                path: 'psla',
                query: { tab: this.activeTab }
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
    },
    watch: {
        'activeTab' () {
            this.changeTab();
        }
    }
}
</script>

<style>
    .accession-psla .form-slider i {
        font-size: 25px;
        margin-left: 50px;
        cursor: pointer;
    }
    .accession-psla .fixed-input {
        width: 80px;
    }
    .accession-psla .el-form-item__label {
        margin-top: 7px;
    }
    .accession-psla .el-tooltip.el-icon-info {
        font-size: 25px;
        color: #2491eb;
        vertical-align: middle;
    }
    .accession-psla .carousel-head {
        height: 50px;
    }
    .accession-psla .el-input-group__append {
        padding: 0 10px;
    }
    .accession-psla .el-collapse-item__header{
        padding-left: 15px;
        font-weight: bold;
        font-size: 16px;
        color: #00436f;
        margin-top: 20px
    }
    .accession-psla .el-collapse-item__header:hover{
        background-color: rgba(0, 0, 0, 0.03);
    }
    .accession-psla .el-collapse-item__content {
        padding-top: 20px;
    }
</style>