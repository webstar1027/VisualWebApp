<template>
    <div class="cglls-ancols admin-content-wrap">
		<div class="row">
        <h1 class="admin-content-title">CGLLS et ANCOLS</h1>
			<el-col :span="2" :offset="4">
				<el-button type="success" :disabled="!isModify" @click="exportCgllsAncols">Exporter</el-button>
			</el-col>
		</div>
        <template>
            <el-table
                    :data="cgllsAncols"
                    v-loading="isLoading"
                    :border="false"
                    style="width: 100%; margin-top: 3%;"
                    row-class-name="cglls-row"
            >
                <el-table-column
                        prop="type"
                        class-name="row-head"
                        fixed
                        width="350"/>
                <el-table-column
                        fixed
                        width="110"
                >
                    <template slot-scope="scope" v-if="!cgllsAncols[scope.$index].cgllsAncolsPeriodique">
                        <el-select
                                v-if="scope.$index === 1"
                                v-model="calculAutomatique"
                                :disabled="!isModify"
                                @change="saveCgllsAncolsParam"
                                placeholder="Select">
                            <el-option
                                    label="Sélectionner"
                                    :value="null">
                            </el-option>
                            <el-option
                                    v-for="key in 50"
                                    :key="key"
                                    :label="parseInt(anneeDeReference) + key -1"
                                    :value="parseInt(anneeDeReference) + key -1">
                            </el-option>
                        </el-select>
                        <span v-if="scope.$index === 10"
                              class="input-wrapper"
                              :class="{'is-error':!isFloat(lissageNet)}">
                            <el-input v-model="lissageNet"
                                      placeholder="0"
                                      :disabled="!isModify"
                                      @change="saveCgllsAncolsParam"
                            >
                            </el-input>
                        </span>
                    </template>
                </el-table-column>
                <el-table-column
                        v-for="(item, key) in 50"
                        :key="key"
                        :label="`${parseInt(anneeDeReference) + key}`"
                >
                    <template slot-scope="scope">
                       <span
                               v-if="cgllsAncols[scope.$index].cgllsAncolsPeriodique"
                               class="input-wrapper"
                               :class="{'is-error':!isFloat(cgllsAncols[scope.$index].cgllsAncolsPeriodique.items[key].valeur),
                                    'disabled':isDisabled(parseInt(anneeDeReference) + key)}">
                            <el-input v-model="cgllsAncols[scope.$index].cgllsAncolsPeriodique.items[key].valeur"
                                      @change="saveCgllsAncols(cgllsAncols[scope.$index].id, cgllsAncols[scope.$index].cgllsAncolsPeriodique.items[key].iteration, cgllsAncols[scope.$index].cgllsAncolsPeriodique.items[key].valeur, scope.$index)"
                                      :disabled="isDisabled(parseInt(anneeDeReference) + key) || !isModify"
                                      placeholder="0"
                            >
                            </el-input>
                       </span>
                    </template>
                </el-table-column>
                <el-table-column fixed="right">
                    <template slot-scope="scope">
                        <el-button v-if="cgllsAncols[scope.$index].cgllsAncolsPeriodique" :disabled="!isModify" @click="repeatRowValues(scope.$index)">
                            <i class="el-icon-refresh"></i>
                       </el-button>
                    </template>
                </el-table-column>
            </el-table>
        </template>
    </div>
</template>

<script>
    import {isFloat, mathInput} from "../../../../../utils/inputs";

    export default {
        name: "CgllsAncols",
        data() {
            return {
                anneeDeReference: null,
                cgllsAncols: null,
                simulationID: null,
                inputError: false,
                calculAutomatique: null,
                cgllsAncolsParametresID: null,
                lissageNet: 0,
                isLoading: true,
            }
        },
        computed: {
            disabledIteration() {
                if (this.calculAutomatique) {
                    return parseInt(this.calculAutomatique) - parseInt(this.anneeDeReference) + 1;
                }
                return 51;
            },
            isModify() {
              return this.$store.getters['choixEntite/isModify'];
            }
        },
        created() {
            const simulationID = _.isNil(this.$route.params.id) ? null : this.$route.params.id;
            if (_.isNil(simulationID)) {
                return;
            }
            this.simulationID = simulationID;

            this.getAnneeDeReference();
            this.getCgllsAncolsParam();
            this.getCgllsAncols();
            this.$apollo
                .query({
                    query: require("../../../../../graphql/administration/partagers/checkStatus.gql"),
                    variables: {
                      simulationID: this.simulationID
                    }
                })
                .then(response => {
                    this.$store.commit('choixEntite/setModify', response.data.checkStatus);
                });
        },
        methods: {
            getAnneeDeReference() {
                this.$apollo.query({
                    query: require('../../../../../graphql/simulations/simulation.gql'),
                    variables: {
                        simulationID: this.simulationID
                    }
                }).then((res) => {
                    if (res.data && res.data.simulation) {
                        this.anneeDeReference = res.data.simulation.anneeDeReference;
                    }
                });
            },
            getCgllsAncols() {
                this.$apollo.query({
                    query: require('../../../../../graphql/simulations/charges/cgllsAncols/cgllsAncols.gql'),
                    fetchPolicy: 'no-cache',
                    variables: {
                        simulationID: this.simulationID,
                    }
                }).then((res) => {
                    this.cgllsAncols = res.data.cgllsAncols.items;
                    this.cgllsAncols.splice(9, 0, {type: 'Lissage net au 31/12/' + this.anneeDeReference + ' (en k€)'});
                    this.cgllsAncols.splice(1, 0, {type: 'Calcul automatique à partir de'});
                    this.isLoading = false
                })
            },
            getCgllsAncolsParam() {
                this.$apollo.query({
                    query: require('../../../../../graphql/simulations/charges/cgllsAncols/cgllsAncolsParametres.gql'),
                    fetchPolicy: 'no-cache',
                    variables: {
                        simulationID: this.simulationID,
                    }
                }).then((res) => {
                    this.cgllsAncolsParametresID = res.data.cgllsAncolsParametres.items[0].id;
                    this.calculAutomatique = res.data.cgllsAncolsParametres.items[0].calculAutomatique;
                    this.lissageNet = res.data.cgllsAncolsParametres.items[0].lissageNet || 0;
                })
            },
            saveCgllsAncols(cgllsAncolsID, iteration, valeur, index, isRepeat = false) {
                let data = this.cgllsAncols[index].cgllsAncolsPeriodique.items;

                if (/\t/.test(valeur)) {
                    let split = valeur.split("\t");

                    for (let i = 0; i < split.length; i++) {
                        if (data[iteration + i - 1]) {
                            data[iteration + i - 1].valeur = mathInput(split[i]);
                        }
                    }
                    for (let i = 0; i < split.length; i++) {
                        this.saveCgllsAncols(cgllsAncolsID, iteration + i, mathInput(split[i]), index, isRepeat);
                    }
                } else {
                    let val = mathInput(valeur);
                    if (isFloat(val)) {
                        data[iteration - 1].valeur = val;
                        this.saveOneCgllsAncols(cgllsAncolsID, iteration, val, isRepeat);
                    } else {
                        this.showError();
                    }
                }
            },
            saveOneCgllsAncols(cgllsAncolsID, iteration, valeur, isRepeat) {
                this.$apollo.mutate({
                    mutation: require('../../../../../graphql/simulations/charges/cgllsAncols/saveCgllsAncolsPeriodique.gql'),
                    variables: {
                        cgllsAncolsID,
                        iteration,
                        valeur,
                        simulationID: this.simulationID
                    }
                }).then(() => {
                    if (!isRepeat) {
                        this.$message({
                            showClose: true,
                            message: 'Les valeurs ont été enregistrées.',
                            type: 'success'
                        });
                    }
                });
            },
            saveCgllsAncolsParam() {
                this.lissageNet = mathInput(this.lissageNet);
                let lissageNet = this.lissageNet;

                if (isFloat(lissageNet)) {
                    this.$apollo.mutate({
                        mutation: require('../../../../../graphql/simulations/charges/cgllsAncols/saveCgllsAncolsParametres.gql'),
                        variables: {
                            cgllsAncolsParametresID: this.cgllsAncolsParametresID,
                            calculAutomatique: this.calculAutomatique,
                            lissageNet,
                            simulationID: this.simulationID
                        }
                    }).then(() => {
                        this.$message({
                            showClose: true,
                            message: 'Les valeurs ont été enregistrées.',
                            type: 'success'
                        });
                    });
                } else {
                    this.showError();
                }
            },
            showError() {
                if (!this.inputError) {
                    this.inputError = true;
                    this.$message({
                        showClose: true,
                        message: 'Les valeurs entrées doivent être valides.',
                        type: 'error',
                        onClose: () => {
                            this.inputError = false;
                        }
                    });
                }
            },
            isDisabled(year) {
                return this.calculAutomatique ? parseInt(this.calculAutomatique) <= year : false;
            },
            repeatRowValues(index) {
                let lastIndex = 0;
                let data = this.cgllsAncols[index].cgllsAncolsPeriodique.items;

                const values = data.filter((item, key) => {
                    if (item.valeur !== 0 && item.valeur !== null) {
                        lastIndex = key;
                        return true;
                    }
                    return false;
                });
                if (values.length > 0) {
                    const lastValue = values[values.length - 1];
                    for (let i = 0; i < data.length; i++) {
                        if (i > lastIndex) {
                            if (this.disabledIteration > data[i].iteration) {
                                data[i].valeur = lastValue.valeur;
                            }
                        }
                    }
                }
                const periodique = data.map(item => item.valeur);
                this.$apollo.mutate({
                        mutation: require('../../../../../graphql/simulations/charges/cgllsAncols/saveCgllsAncol.gql'),
                        variables: {
                            cgllsAncolsID: this.cgllsAncols[index].id,
                            simulationID: this.simulationID,
                            periodique: JSON.stringify({periodique: periodique})
                        }
                    }).then(() => {
                        this.$message({
                            showClose: true,
                            message: 'Les valeurs ont été enregistrées.',
                            type: 'success'
                        });
                    });
                return data;
            },
            isFloat: isFloat,
            exportCgllsAncols() {
               window.location.href = "/export-cglls-ancols/" + this.simulationID;
            },
        }
    }
</script>

<style type="text/css">
    .cglls-ancols {
        font-size: 14px;
        margin-bottom: 20px;
    }

    .cglls-ancols .input-wrapper.is-error input {
        border-color: #f56c6c;
    }

    .cglls-ancols .input-wrapper span {
        display: none;
        color: #f56c6c;
    }

    .cglls-ancols .input-wrapper input {
        border: 1px solid transparent;
        border-radius: 0;
    }

    .cglls-ancols .input-wrapper.is-error span {
        display: contents;
    }

    .cglls-ancols .input-wrapper.disabled input {
        background-color: #dee2e6
    }

    .cglls-ancols .row-head .cell {
        font-size: 14px;
        color: black
    }

    .cglls-ancols .el-icon-refresh {
        position: relative;
        color: #2591eb;
        font-size: 20px;
        font-weight: bold;
        cursor: pointer;
    }

    .cglls-ancols .el-icon-refresh.s1 {
        top: 45px;
    }

    .cglls-ancols .el-icon-refresh.s2 {
        top: 105px;
    }

    .cglls-ancols .el-table th {
        text-align: center;
        font-weight: normal;
        background: #f2f4f6;
        border-bottom: none;
    }

    .cglls-ancols .el-table td, .cglls-ancols .el-table th.is-leaf {
        border-bottom: none;
    }

    .cglls-ancols .el-table .cglls-row {
        background: #f2f4f6;
        border: none;
    }

    .cglls-ancols .el-table .hover-row td {
        background: #f2f4f6;
    }
</style>
