<template>
    <div class="vacance">
        <el-row class="mb-3">
            <el-col :span="2" :offset="19">
                <el-upload
                  ref="upload"
                  :action.native="'/import-vacance-identifiees/'+ simulationID"
                  multiple
                  :limit="10"
                  :on-success="onSuccess"
                  :on-error="onError">
                  <el-button size="small" type="primary">Importer</el-button>
                </el-upload>
            </el-col>
            <el-col :span="2">
                <el-button type="success" @click.stop="exportVacanceIdentifiees">
                    Exporter
                </el-button>
            </el-col>
        </el-row>

        <el-row>
            <ApolloQuery
                    :query="require('../../../../../graphql/simulations/logements-familiaux/vacances/vacances.gql')"
                    :variables="{
                            simulationID: simulationID
                        }">
                <template slot-scope="{ result:{ loading, error, data }, isLoading, query }">
                    <div v-if="error">Une erreur est survenue !</div>
                    <el-table
                            v-loading="isLoading"
                            :data="tableData(data, query)"
                            style="width: 100%">
                        <el-table-column fixed prop="numeroGroupe" label="N° groupe" width="100">
                            <template slot="header">
                                <span title="N° groupe">N° groupe</span>
                            </template>
                        </el-table-column>
                        <el-table-column fixed prop="numeroSousGroupe" label="N° sous-groupe" width="120">
                            <template slot="header">
                                <span title="N° sous-groupe">N° sous-groupe</span>
                            </template>
                        </el-table-column>
                        <el-table-column sortable fixed prop="nomGroupe" label="nom du groupe" width="140">
                            <template slot="header">
                                <span title="nom du groupe">nom du groupe</span>
                            </template>
                        </el-table-column>
                        <el-table-column fixed prop="information" label="Information" width="100">
                            <template slot="header">
                                <span title="Information">Information</span>
                            </template>
                        </el-table-column>
                            <el-table-column v-for="column in vacanceColumns"
                                             align="center"
                                             :key="column.prop"
                                             :prop="column.prop"
                                             :label="column.label">
                                             <template slot="header">
                                                 <span :title="column.label">{{ column.label }}</span>
                                             </template>
                            </el-table-column>
                        <el-table-column fixed="right" width="120" label="Actions">
                            <template slot-scope="scope">
                                <el-button type="primary" :disabled="!isModify" icon="el-icon-edit" circle @click="handleEdit(scope.$index, scope.row)"></el-button>
                                <el-button type="danger" :disabled="!isModify" icon="el-icon-delete" circle @click="handleDelete(scope.$index, scope.row)"></el-button>
                            </template>
                        </el-table-column>
                    </el-table>
                    <el-row class="d-flex justify-content-end my-3">
                        <el-button type="primary" :disabled="!isModify" @click="showCreateModal">Créer une nouvelle vacance</el-button>
                    </el-row>
                </template>
            </ApolloQuery>
        </el-row>

        <el-dialog
                title="Création/Modification d'une vacance identifiée"
                :visible.sync=dialogVisible
                :close-on-click-modal="false"
                width="70%">
            <el-row v-if="isEdit" class="form-slider text-center mb-5">
                <i class="el-icon-back font-weight-bold" @click="back"></i>
                <i class="el-icon-right font-weight-bold" @click="next"></i>
            </el-row>
            <el-form label-position="top" :model="form" :rules="formRules" ref="vacanceForm">
                <el-row :gutter="20">
                    <el-col :span="6">
                        <el-form-item label="N° groupe" prop="numeroGroupe">
                            <el-select v-model="form.numeroGroupe" @change="changeNgroupe">
                                <el-option v-for="item in nGroupes"
                                    :key="item"
                                    :label="item"
                                    :value="item"></el-option>
                            </el-select>
                        </el-form-item>
                    </el-col>
                    <el-col :span="6">
                        <el-form-item label="N° sous-groupe" prop="numeroSousGroupe">
                            <el-select v-model="form.numeroSousGroupe" @change="changeNSousGroupe">
                                <el-option v-for="item in nSousGroupes"
                                    :key="item.id"
                                    :label="item.nSousGroupe"
                                    :value="item.nSousGroupe"></el-option>
                            </el-select>
                        </el-form-item>
                    </el-col>
                    <el-col :span="6">
                        <el-form-item label="Nom du groupe">
                            <el-input type="text" v-model="form.nomGroupe" placeholder="nom du groupe" autocomplete="off" :disabled=true></el-input>
                        </el-form-item>
                    </el-col>
                    <el-col :span="6">
                        <el-form-item>
                            <template slot="label">
                                <span>Information</span>
                                <el-tooltip class="item" effect="dark" content="Ces taux s'appliqueront à la place des taux de perte saisis dans les risques locatifs" placement="top">
                                    <i class="el-icon-info"></i>
                                </el-tooltip>
                            </template>
                            <el-input type="text" v-model="form.information" placeholder="" autocomplete="off" :disabled=true></el-input>
                        </el-form-item>
                    </el-col>
                </el-row>
            </el-form>
            <br>
            <el-row>
                <el-col :span="3" style="padding-top: 55px;">
                    <div class="carousel-head text-center">
                        <p>Montant</p>
                    </div>
                </el-col>
                <el-col :span="21">
                    <periodique :anneeDeReference="anneeDeReference"
                                v-model="form.periodiques"
                                @onChange="periodicOnChange"></periodique>
                </el-col>
            </el-row>
            <div slot="footer" class="dialog-footer">
                <el-button type="primary" @click="dialogVisible = false">Retour</el-button>
                <el-button type="success" @click="save" :disabled="isSubmitting">Valider</el-button>
            </div>
        </el-dialog>
    </div>
</template>

<script>
    import { initPeriodic, periodicFormatter, checkAllPeriodics } from '../../../../../utils/inputs';
    import { updateData, groupBy } from '../../../../../utils/helpers'
    import Periodique from '../../../../../components/partials/Periodique';
    import customValidator from '../../../../../utils/validation-rules';

    export default {
        name: "Vacance",
        components: { Periodique },
        data() {
            return {
                simulationID: null,
                dialogVisible: false,
                vacanceColumns:[],
                anneeDeReference: null,
                patrimoines: [],
                nSousGroupes: [],
                isSubmitting: false,
                columns:[],
                selectedIndex: null,
                form:{},
                isEdit: false,
                query: false,
                formRules: {
                    numeroGroupe: customValidator.getPreset('number.positiveInt')
                }
            }
        },
        created(){
            const simulationID = _.isNil(this.$route.params.id) ? null : this.$route.params.id;
            if (_.isNil(simulationID)) {
                return;
            }
            this.simulationID = simulationID;
            this.getAnneeDeReference();
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
        mounted() {
            this.getPatrimoines();
        },
        computed: {
            nGroupes() {
                let nGroupes = [];
                this.patrimoines.map(item => {
                    if (!nGroupes.includes(item.nGroupe)) {
                        nGroupes.push(item.nGroupe)
                    }
                });
                return nGroupes;
            },
            isModify() {
                return this.$store.getters['choixEntite/isModify'];
            }
        },
        methods: {
            initForm() {
                this.dialogVisible=false;
                this.form = {
                    id:null,
                    numeroGroupe: null,
                    numeroSousGroupe:null,
                    nomGroupe: '',
                    information:null,
                    periodiques: {
                        vacancePeriodiques: initPeriodic()
                    }
                };
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
                        this.setTableColumns();
                    }
                });
            },
            getPatrimoines() {
                this.$apollo.query({
                    query: require('../../../../../graphql/simulations/logements-familiaux/patrimoines/patrimoines.gql'),
                    variables: {
                        simulationID: this.simulationID
                    }
                }).then((res) => {
                    if (res.data && res.data.patrimoines) {
                        this.patrimoines = res.data.patrimoines.items;
                    }
                });
            },
            showCreateModal() {
                this.initForm();
                this.dialogVisible = true;
                this.isEdit = false;
            },
            setTableColumns() {
                this.vacanceColumns = [];
                for (var i = 0; i < 50; i++) {
                    this.vacanceColumns.push({
                        label: (parseInt(this.anneeDeReference) + i).toString(),
                        prop: `periodiques.vacancePeriodiques[${i}]`
                    });
                }
            },
            tableData(data, query) {
                if (!_.isNil(data)) {
                    this.query = query;
                    const vacance = data.vacance.items.map(item => {
                        let vacancePeriodiques = [];
                        item.vacancePeriodique.items.forEach(vacancePeriodique => {
                            vacancePeriodiques[vacancePeriodique.iteration - 1] = vacancePeriodique.montant;
                        });
                        return {
                            id: item.id,
                            numeroGroupe: item.numeroGroupe,
                            numeroSousGroupe: item.numeroSousGroupe,
                            nomGroupe: item.nomGroupe,
                            information: item.information,
                            periodiques: {
                                vacancePeriodiques
                            }
                        }
                    });
                    this.vacance = vacance;

                    return vacance;
                } else {
                    return [];
                }
            },
            handleDelete(index, row) {
                this.$confirm('Êtes-vous sûr de vouloir supprimer cette vacance ?')
                    .then(_ => {
                        this.$apollo.mutate({
                            mutation: require('../../../../../graphql/simulations/logements-familiaux/vacances/removeVacance.gql'),
                            variables: {
                                vacanceId: row.id,
                                simulationId: this.simulationID
                            }
                        }).then(() => {
                            updateData(this.query, this.simulationID).then(() => {
                                this.$message({
                                    showClose: true,
                                    message: 'La vacance a bien été supprimée.',
                                    type: "success",
                                });
                            })
                        }).catch((error) => {
                            updateData(this.query, this.simulationID).then(() => {
                                this.$message({
                                    showClose: true,
                                    message: error.networkError.result,
                                    type: "error",
                                });
                            });
                        });
                    })
                    .catch(_ => {});
            },
            handleEdit(index, row) {
                this.dialogVisible = true;
                this.form = {...row};
                this.selectedIndex = index;
                this.isEdit = true;
            },
            save() {
                this.$refs['vacanceForm'].validate((valid) => {
                    if (valid && checkAllPeriodics(this.form.periodiques)) {
                        this.isSubmitting = true;
                        this.$apollo.mutate({
                            mutation: require('../../../../../graphql/simulations/logements-familiaux/vacances/saveVacance.gql'),
                            variables: {
                                vacance: {
                                    id: this.form.id,
                                    simulationId: this.simulationID,
                                    numeroGroupe: parseInt(this.form.numeroGroupe),
                                    numeroSousGroupe: parseInt(this.form.numeroSousGroupe),
                                    nom: this.form.nomGroupe,
                                    information: this.form.information || "",
                                    periodique: JSON.stringify({periodique: this.form.periodiques.vacancePeriodiques})
                                }
                            }
                        }).then(() => {
                            this.isSubmitting = false;
                            this.dialogVisible = false;
                            updateData(this.query, this.simulationID).then(() => {
                                this.$message({
                                    showClose: true,
                                    message: 'La vacance a bien été enregistrée.',
                                    type: "success",
                                });
                            });
                        }).catch((error) =>{
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
            back() {
                if (this.selectedIndex > 0) {
                    this.selectedIndex--;
                    this.form = {...this.vacance[this.selectedIndex]};
                }
            },
            next() {
                if (this.selectedIndex < (this.vacance.length - 1)) {
                    this.selectedIndex++;
                    this.form = {...this.vacance[this.selectedIndex]};
                }
            },
            changeNgroupe(value) {
                const grouped = groupBy(this.patrimoines, patrimoine => patrimoine.nGroupe);
                this.nSousGroupes = grouped.get(value);
                this.form.numeroSousGroupe = this.nSousGroupes[0].nSousGroupe;
                this.changeNSousGroupe();
            },
            changeNSousGroupe() {
                const patrimoine = this.nSousGroupes.find(item => item.nSousGroupe === this.form.numeroSousGroupe);
                let form = this.form;
                form.nomGroupe = patrimoine.nomGroupe;
                form.information = patrimoine.informations;
                this.form = null;
                this.form = form;
            },
            periodicOnChange(type) {
                let newPeriodics = this.form.periodiques[type];
                this.form.periodiques[type] = [];
                this.form.periodiques[type] = periodicFormatter(newPeriodics);
            },
            formatInput(type) {
                this.form[type] = mathInput(this.form[type]);
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
            exportVacanceIdentifiees() {
                window.location.href = "/export-vacance-identifiees/" + this.simulationID;
            },
            onSuccess(res) {
                this.$toasted.success(res, {
                    theme: 'toasted-primary',
                    icon: 'check',
                    position: 'top-right',
                    duration: 5000
                });
                updateData(this.query, this.simulationID);
                this.$refs.upload.clearFiles();
            },
            onError(error) {
                this.$toasted.error(JSON.parse(error.message), {
                    theme: 'toasted-primary',
                    icon: 'error',
                    position: 'top-right',
                    duration: 5000
                });

                this.$refs.upload.clearFiles();
            }
        }
    }
</script>

<style type="text/css">
    .vacance .form-slider i {
        font-size: 25px;
        margin-left: 50px;
        cursor: pointer;
    }
    .vacance .el-button--small {
        font-weight: 500;
        padding: 0 15px;
        height: 40px;
        font-size: 14px;
        border-radius: 4px;
        text-align: center;
        height: 40px;
    }
    .vacance .carousel-head {
        height: 50px;
    }
</style>
