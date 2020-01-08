<template>
    <div class="patrimoines-foyers">
        <el-form :inline="true" :model="formParameter" ref="nombrePondereForm" :rules="formRules">
            <div class="row align-items-center">
                <div class="col-sm-12 col-md-8">
                    <el-form-item label="Nombre pondéré équivalent logements" prop="nombrePondere">
                        <el-input type="text" v-model="formParameter.nombrePondere" :disabled="paramIsLoading" placeholder="0" class="text-input" @change="saveNombrePondere"></el-input>
                    </el-form-item>
                </div>
                <div class="col-sm-12 col-md-4">
                    <div class="d-flex justify-content-end">
                    <el-upload
                      ref="upload"
                      :action="'/import-patrimoine-foyers/'+ simulationID"
                      multiple
                      :limit="10"
                      :on-success="onSuccessImport"
                      :on-error="onErrorImport">
                      <el-button type="primary">Importer</el-button>
                    </el-upload>
                    <el-button class="ml-2" type="success" @click="exportPatrimoineFoyers">Exporter</el-button>
                    </div>
                </div>
            </div>
        </el-form>
        <ApolloQuery
                :query="require('../../../../../graphql/simulations/foyers/patrimoines/patrimoines.gql')"
                :variables="{
                simulationID: simulationID
            }">
            <template slot-scope="{ result:{ loading, error, data }, isLoading, query }">
                <div v-if="error">Une erreur est survenue !</div>
                <el-table
                        v-loading="isLoading"
                        :data="tableData(data, query)"
                        style="width: 100%">
                    <el-table-column sortable column-key="nGroupe" prop="nGroupe" min-width="120" label="N° groupe">
                        <template slot="header">
                            <span title="N° groupe">N° groupe</span>
                        </template>
                    </el-table-column>
                    <el-table-column sortable column-key="nSousGroupe" prop="nSousGroupe" min-width="150" label="N° sous-groupe">
                        <template slot="header">
                            <span title="N° sous-groupe">N° sous-groupe</span>
                        </template>
                    </el-table-column>
                    <el-table-column sortable column-key="nomGroupe" prop="nomGroupe" min-width="150" label="Nom du groupe">
                        <template slot="header">
                            <span title="Nom du groupe">Nom du groupe</span>
                        </template>
                    </el-table-column>
                    <el-table-column sortable column-key="informations" prop="informations" min-width="150" label="Information">
                        <template slot="header">
                            <span title="Information">Information</span>
                        </template>
                    </el-table-column>
                    <el-table-column sortable column-key="nombreLogements" prop="nombreLogements" min-width="170" label="Nombre d'équivalents logements">
                        <template slot="header">
                            <span title="Nombre d'équivalents logements">Nombre d'équivalents logements</span>
                        </template>
                    </el-table-column>
                    <el-table-column sortable column-key="secteurFinancier" prop="secteurFinancier" min-width="180" label="Secteur financier">
                        <template slot="header">
                            <span title="Secteur financier">Secteur financier</span>
                        </template>
                    </el-table-column>
                    <el-table-column sortable column-key="natureOperation" prop="natureOperation" min-width="180" label="Nature de l'opération">
                        <template slot="header">
                            <span title="Nature de l'opération">Nature de l'opération</span>
                        </template>
                    </el-table-column>
                    <el-table-column sortable column-key="tauxEvolutionRedevances" prop="tauxEvolutionRedevances" min-width="180" label="Taux réel d'évolution des redevances">
                        <template slot="header">
                            <span title="Taux réel d'évolution des redevances">Taux réel d'évolution des redevances</span>
                        </template>
                    </el-table-column>
                    <el-table-column fixed="right" width="120" label="Actions">
                        <template slot-scope="scope">
                            <el-button type="primary" :disabled="!isModify" icon="el-icon-edit" circle @click="handleEdit(scope.$index, scope.row)"></el-button>
                            <el-button type="danger" :disabled="!isModify" icon="el-icon-delete" circle @click="handleDelete(scope.$index, scope.row)"></el-button>
                        </template>
                    </el-table-column>
                </el-table>
            </template>
        </ApolloQuery>
        <el-row class="d-flex justify-content-end my-3">
            <el-button type="primary" :disabled="!isModify" @click="showCreateModal">Créer un groupe du patrimoine</el-button>
        </el-row>
        <el-dialog
                title="Créer un groupe du patrimoine"
                :visible.sync="dialogVisible"
                :close-on-click-modal="false"
                width="65%">
            <el-row v-if="isEdit" class="form-slider text-center mb-5">
                <i class="el-icon-back font-weight-bold" @click="back"></i>
                <i class="el-icon-right font-weight-bold" @click="next"></i>
            </el-row>
            <el-form :model="form" :rules="formRules" label-width="170px" ref="patrimoineFoyersForm">
                <div class="row">
                    <div class="col-sm-6">
                        <el-form-item label="N° groupe:" prop="nGroupe">
                            <el-input type="text" v-model="form.nGroupe" placeholder="0"></el-input>
                        </el-form-item>
                    </div>
                    <div class="col-sm-6">
                        <el-form-item label="Nom du groupe:" prop="nomGroupe">
                            <el-input type="text" v-model="form.nomGroupe"></el-input>
                        </el-form-item>
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-sm-6">
                        <el-form-item label="N° sous-groupe:" prop="nSousGroupe">
                            <el-input type="text" v-model="form.nSousGroupe" placeholder="0"></el-input>
                        </el-form-item>
                    </div>
                    <div class="col-sm-6">
                        <el-form-item label="Information:" prop="informations">
                            <el-input type="textarea" v-model="form.informations"></el-input>
                        </el-form-item>
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-sm-6">
                        <el-form-item label="Nombre d'équivalents logements:" prop="nombreLogements" class="custom-append-input">
                            <el-input type="text" v-model="form.nombreLogements" placeholder="0"
                                      @change="formatInput('nombreLogements')">
                                <template slot="append">
                                    <el-tooltip class="item" effect="dark" content="Uniquement les foyers" placement="top">
                                        <i class="el-icon-info"></i>
                                    </el-tooltip>
                                </template>
                            </el-input>
                        </el-form-item>
                    </div>
                    <div class="col-sm-6">
                        <el-form-item label="Secteur financier:" prop="secteurFinancier">
                            <el-input type="text" v-model="form.secteurFinancier"></el-input>
                        </el-form-item>
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-sm-6">
                        <el-form-item label="Nature de l'opération:" prop="natureOperation">
                            <el-input type="text" v-model="form.natureOperation"></el-input>
                        </el-form-item>
                    </div>
                    <div class="col-sm-6">
                        <el-form-item label="Taux réel d'évolution des redevances:" prop="tauxEvolutionRedevances">
                            <el-input type="text" v-model="form.tauxEvolutionRedevances" placeholder="%"
                                        @change="formatInput('tauxEvolutionRedevances')"></el-input>
                        </el-form-item>
                    </div>
                </div>
                <el-row class="mt-4">
                    <el-col :span="4" style="padding-top: 50px;">
                        <div class="carousel-head text-center">
                            <p>Redevance</p>
                        </div>
                    </el-col>
                    <el-col :span="20">
                        <periodique :anneeDeReference="anneeDeReference"
                            v-model="form.periodiques"
                            @onChange="periodicOnChange"></periodique>
                    </el-col>
                </el-row>
            </el-form>
            <div slot="footer" class="dialog-footer">
                <el-button type="primary" @click="dialogVisible = false">Retour</el-button>
                <el-button type="success" :disabled="isSubmitting" @click="save">Valider</el-button>
            </div>
        </el-dialog>
    </div>
</template>

<script>
    import customValidator from '../../../../../utils/validation-rules'
    import { initPeriodic, periodicFormatter, checkAllPeriodics, mathInput } from '../../../../../utils/inputs'
    import { updateData } from '../../../../../utils/helpers'
    import Periodique from "../../../../../components/partials/Periodique"
    export default {
        name: "PatrimoinesFoyers",
        components: { Periodique },
        data() {
            return {
                simulationID: null,
                patrimoines: [],
                dialogVisible: false,
                form: null,
                formParameter: {
                    nombrePondere: null,
                },
                selectedIndex: null,
                isEdit: false,
                isSubmitting: false,
                paramIsLoading: true,
                anneeDeReference: null,
                periodiqueHasError: false,
                columns: [],
                query: null,
                formRules: {
                    nombrePondere: customValidator.getRule('positiveInt'),
                    nGroupe: [
                        customValidator.getRule('requiredNoWhitespaces'),
                        customValidator.getRule('positiveInt')
                    ],
                    nSousGroupe: [
                        customValidator.getRule('requiredNoWhitespaces'),
                        customValidator.getRule('positiveInt')
                    ],
                    nomGroupe: [
                        customValidator.getRule('requiredNoWhitespaces'),
                        customValidator.getRule('maxVarchar')
                    ],
                    informations: customValidator.getRule('maxLongtext'),
                    nombreLogements: [
                        customValidator.getRule('required'),
                        customValidator.getRule('positiveDouble'),
                    ],
                    secteurFinancier: customValidator.getRule('maxVarchar'),
                    natureOperation: customValidator.getRule('maxVarchar'),
                    tauxEvolutionRedevances: customValidator.getRule('taux')
                }
            }
        },
        computed: {
            isModify() {
                return this.$store.getters['choixEntite/isModify'];
            }
        },
        created () {
            this.simulationID = _.isNil(this.$route.params.id) ? null : this.$route.params.id;
            if (_.isNil(this.simulationID)) {
                return;
            }
            this.initForm();
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
            initForm() {
                this.form = {
                    id: null,
                    nGroupe: null,
                    nSousGroupe: null,
                    nomGroupe: '',
                    informations: '',
                    nombreLogements: null,
                    secteurFinancier: null,
                    natureOperation: null,
                    tauxEvolutionRedevances: null,
                    periodiques: {
                        items: initPeriodic()
                    }
                };
                this.getParametre()
                this.getAnneeDeReference();
            },
            getParametre () {
                this.$apollo.query({
                    query: require('../../../../../graphql/simulations/foyers/patrimoines/patrimoineFoyerParametre.gql'),
                    variables: { simulationId: this.simulationID}
                }).then(res => {
                    this.paramIsLoading = false;
                    if (res && res.data && res.data.patrimoineFoyerParametre) {
                        this.formParameter.nombrePondere = res.data.patrimoineFoyerParametre.nombrePondereLogement
                    }
                }).catch(err => {
                    this.paramIsLoading = false;
                })

            },
            tableData(data, query) {
                if (!_.isNil(data)) {
                    this.query = query;
                    const patrimoinesFoyer = data.patrimoinesFoyer.items.map(item => {
                        let periodiques = [];
                        item.periodique.items.forEach(periodique => {
                            periodiques[periodique.iteration - 1] = periodique.value;
                        });
                        let row = {...item};
                        row.nGroupe += ''
                        row.nSousGroupe += ''
                        row.nombreLogement += ''
                        row.periodiques = {
                            items: periodiques
                        }
                        return row
                    });
                    this.patrimoines = patrimoinesFoyer;
                    return patrimoinesFoyer;
                } else {
                    return [];
                }
            },
            showCreateModal() {
                this.initForm();
                this.dialogVisible = true;
                this.isEdit = false;
            },
            handleEdit(index, row) {
                this.dialogVisible = true;
                this.form = {...row};
                this.selectedIndex = index;
                this.isEdit = true;
            },
            handleDelete(index, row) {
                this.$confirm('Êtes-vous sûr de vouloir supprimer ce patrimoine?')
                    .then(() => {
                        this.$apollo.mutate({
                            mutation: require('../../../../../graphql/simulations/foyers/patrimoines/removePatrimoine.gql'),
                            variables: {
                                patrimoineFoyerUUID: row.id,
                                simulationId: this.simulationID
                            }
                        }).then(() => {
                            updateData(this.query, this.simulationID).then(() => {
                                this.$message({
                                    showClose: true,
                                    message: 'Ce patrimoine a bien été supprimé.',
                                    type: 'success'
                                });
                            })
                        }).catch(error => {
                            updateData(this.query, this.simulationID).then(() => {
                                this.$message({
                                    showClose: true,
                                    message: error.networkError.result,
                                    type: 'error',
                                });
                            });
                        });
                    })
            },
            back() {
                if (this.selectedIndex > 0) {
                    this.selectedIndex--;
                    this.form = {...this.patrimoines[this.selectedIndex]};
                }
            },
            next() {
                if (this.selectedIndex < (this.patrimoines.length - 1)) {
                    this.selectedIndex++;
                    this.form = {...this.patrimoines[this.selectedIndex]};
                }
            },
            periodicOnChange(type) {
                let newPeriodics = this.form.periodiques[type];
                this.form.periodiques[type] = [];
                this.form.periodiques[type] = periodicFormatter(newPeriodics);
            },
            save() {
                this.$refs['patrimoineFoyersForm'].validate((valid) => {
                    if (valid && checkAllPeriodics(this.form.periodiques)) {
                        this.isSubmitting = true;
                        this.$apollo.mutate({
                            mutation: require('../../../../../graphql/simulations/foyers/patrimoines/savePatrimoine.gql'),
                            variables: {
                                patrimoine: {
                                    uuid: this.form.id,
                                    simulationId: this.simulationID,
                                    nGroupe: this.form.nGroupe,
                                    nSousGroupe: this.form.nSousGroupe,
                                    nomGroupe: this.form.nomGroupe,
                                    informations: this.form.informations,
                                    nombreLogements: this.form.nombreLogements,
                                    secteurFinancier: this.form.secteurFinancier,
                                    natureOperation: this.form.natureOperation,
                                    tauxEvolutionRedevances: this.form.tauxEvolutionRedevances | 0,
                                    periodique: JSON.stringify({periodique: this.form.periodiques.items})
                                }
                            }
                        }).then(() => {
                            this.dialogVisible = false;
                            this.isSubmitting = false;
                            updateData(this.query, this.simulationID).then(() => {
                                this.$message({
                                    showClose: true,
                                    message: 'Ce patrimoine a bien été enregistré.',
                                    type: 'success'
                                });
                            })
                        }).catch(error => {
                            this.isSubmitting = false;
                            this.$message({
                                showClose: true,
                                message: error.networkError.result,
                                type: 'error',
                            });
                        });
                    } else {
                        this.showError();
                    }
                });
            },
            formatInput(type) {
                this.form[type] = mathInput(this.form[type]);
            },
            saveNombrePondere () {
                this.formParameter.nombrePondere = mathInput(this.formParameter.nombrePondere);
                this.$refs['nombrePondereForm'].validate((valid) => {
                    if (valid) {
                        this.$apollo.mutate({
                            mutation: require('../../../../../graphql/simulations/foyers/patrimoines/savePatrimoineParametre.gql'),
                            variables: {
                                simulationId: this.simulationID,
                                nombrePondereLogement: this.formParameter.nombrePondere,
                            }
                        }).then(res => {
                            this.$message({
                                showClose: true,
                                message: 'Le nombre pondéré a bien été enregistré.',
                                type: "success",
                            });
                        })
                    }
                })
            },
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
            setTableColumns() {
                this.columns = [];
                for (var i = 0; i < 50; i++) {
                    this.columns.push({
                        label: (parseInt(this.anneeDeReference) + i).toString(),
                        prop: `periodiques.items[${i}]`
                    });
                }
            },
            exportPatrimoineFoyers() {
                window.location.href = "/export-patrimoine-foyers/" + this.simulationID;
            },
            onSuccessImport(res) {
                this.$toasted.success(res, {
                    theme: 'toasted-primary',
                    icon: 'check',
                    position: 'top-right',
                    duration: 5000
                });
                this.$refs.upload.clearFiles();
                updateData(this.query, this.simulationID);
            },
            onErrorImport (error) {
                this.$toasted.error(JSON.parse(error.message), {
                    theme: 'toasted-primary',
                    icon: 'error',
                    position: 'top-right',
                    duration: 5000
                });
                this.$refs.upload.clearFiles();
            },
        }
    }
</script>

<style>
    .patrimoines-foyers textarea {
        font-family: inherit;
        font-size: 12px;
    }
    .patrimoines-foyers .text-input {
        width: 235px;
    }
    .patrimoines-foyers .el-input.is-disabled .el-input__inner {
        color: black;
    }
    .patrimoines-foyers .form-slider i {
        font-size: 25px;
        margin-left: 50px;
        cursor: pointer;
    }
    .patrimoines-foyers .carousel-head {
        height: 50px;
    }
</style>