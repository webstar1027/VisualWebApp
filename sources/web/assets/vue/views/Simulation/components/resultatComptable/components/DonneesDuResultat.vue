<template>
    <el-card class="patrimoine-reference">
        <ApolloQuery
                :query="require('../../../../../graphql/simulations/resultatComptables/resultatComptables.gql')"
                :variables="{
                    simulationId: simulationID
                }">
            <template slot-scope="{ result:{ loading, error, data }, isLoading, query }">
				<div class="row">
					<h1 class="admin-content-title">Données du résultat comptable</h1>
					<el-col :span="2" :offset="2">
						<el-upload
								ref="upload"
								:action="'/import-resultat-compatible/'+ simulationID"
								multiple
								:limit="10"
								:before-upload="setQuery(query)"
								:disabled="!isModify"
								:on-success="onSuccessImport"
								:on-error="onErrorImport">
							<el-button size="small" type="primary">Importer</el-button>
						</el-upload>
					</el-col>
					<el-col :span="2" class="export-button">
						<el-button type="success" :disabled="!isModify" class="ml-2" @click="exportResultat">Exporter</el-button>
					</el-col>
				</div>
                <el-row>
                        <div v-if="error">Une erreur est survenue !</div>
                        <el-table
                            v-loading="isLoading === 1"
                            :data="tableData(data)"
                            style="width: 100%">
                            <el-table-column sortable column-key="libelle" prop="libelle" width="300" label="Libellé"></el-table-column>
                            <el-table-column v-for="column in columns"
                                sortable
                                align="center"
                                :key="column.prop"
                                :prop="column.prop"
                                :label="column.label">
                            </el-table-column>
                            <el-table-column fixed="right" width="120" label="Actions">
                                <template slot-scope="scope">
                                    <el-button type="primary" :disabled="!isModify" icon="el-icon-edit" circle @click="handleEdit(scope.$index, scope.row, query)"></el-button>
                                    <el-button v-if="scope.row.deletable" type="danger" :disabled="!isModify" icon="el-icon-delete" circle @click="handleDelete(scope.$index, scope.row, query)"></el-button>
                                </template>
                            </el-table-column>
                        </el-table>
                        <el-row class="d-flex justify-content-end my-3">
                            <el-button type="primary" :disabled="!isModify" @click="showCreateModal(query)">Créer une nouvelle ligne</el-button>
                        </el-row>
                 </el-row>
            </template>
        </ApolloQuery>
        <el-dialog
            title="Créer une nouvelle ligne"
            :visible.sync="dialogVisible"
            :close-on-click-modal="false"
            width="70%">
            <el-row v-if="isEdit" class="form-slider text-center mb-5">
                <i class="el-icon-back font-weight-bold" @click="back"></i>
                <i class="el-icon-right font-weight-bold" @click="next"></i>
            </el-row>
            <el-form :model="form" :rules="formRules" label-width="80px" ref="resultatComptableForm">
                <el-form-item label="Libellé:" prop="libelle" class="mr-5 pr-5 ml-5 pl-3">
                    <el-input type="text" v-model="form.libelle" placeholder="Libellé" :readonly="isEdit"></el-input>
                </el-form-item>
                <periodique :anneeDeReference="anneeDeReference"
                            v-model="form.periodiques"
                            @onChange="periodicOnChange"></periodique>
            </el-form>
            <div slot="footer" class="dialog-footer">
                <el-button type="outline-primary" @click="dialogVisible = false">Retour</el-button>
                <el-button type="primary" @click="save('resultatComptableForm')">Valider</el-button>
            </div>
        </el-dialog>
    </el-card>
</template>

<script>
import Periodique from '../../../../../components/partials/Periodique';
import {    initPeriodic,
            periodicFormatter,
            checkAllPeriodics } from '../../../../../utils/inputs';
import { updateData } from '../../../../../utils/helpers'

export default {
    name: "ResultatComptable",
    components: { Periodique },
    data() {
        return {
            simulationID: null,
            anneeDeReference: null,
            columns: [],
            resultatComptables: [],
            dialogVisible: false,
            isEdit: false,
            form: null,
            selectedIndex: null,
            inputError: false,
            query:null,
            formRules: {
                libelle: [
                    { required: true, message: "Vous devez saisir un libellé", trigger: 'change' }
                ]
            }
        }
    },
    computed: {
        isModify() {
            return this.$store.getters['choixEntite/isModify'];
        }
    },
    created () {
        const simulationID = _.isNil(this.$route.params.id) ? null : this.$route.params.id;
        if (_.isNil(simulationID)) {
            return;
        }
        this.simulationID = simulationID;

        this.initForm();
        this.getAnneeDeReference();
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
                libelle: '',
                periodiques: {
                    items: initPeriodic()
                }
            };
        },
        setQuery (query) {
            this.query = query;
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
        setTableColumns() {
            this.columns = [];
            for (var i = 0; i < 50; i++) {
                this.columns.push({
                    label: (parseInt(this.anneeDeReference) + i).toString(),
                    prop: `periodiques.items[${i}]`
                });
            }
        },
        tableData(data) {
            if (!_.isNil(data)) {
                const resultatComptables = data.resultatComptables.items.map(item => {
                    let items = [];
                    item.resultatComptablePeriodique.items.forEach(periodique => {
                        items[periodique.iteration - 1] = periodique.value;
                    });
                    return {
                        id: item.id,
                        libelle: item.libelle,
                        deletable: item.deletable,
                        periodiques: {
                            items
                        }
                    };
                });

                this.resultatComptables = resultatComptables;

                return resultatComptables;
            } else {
                return [];
            }
        },
        showCreateModal(query) {
            this.initForm();
            this.dialogVisible = true;
            this.isEdit = false;
            this.query = query;
        },
        handleEdit(index, row, query) {
            this.dialogVisible = true;
            this.form = {...row};
            this.selectedIndex = index;
            this.isEdit = true;
            this.query = query;
        },
        handleDelete(index, row, query) {
            this.$confirm('Êtes-vous sûr de vouloir supprimer cette nouvelle ligne?')
                .then(_ => {
                    this.$apollo.mutate({
                        mutation: require('../../../../../graphql/simulations/resultatComptables/removeResultatComptable.gql'),
                        variables: {
                            resultatComptableUUID: row.id,
                            simulationId: this.simulationID
                        }
                    }).then(() => {
                        updateData(query, this.simulationID)
                            .then(() => {
                                this.$message({
                                    showClose: true,
                                    message: 'Le patrimoine a bien été supprimé.',
                                    type: "success",
                                });
                            })
                    }).catch(error => {
                        updateData(query, this.simulationID)
                        this.$message({
                            showClose: true,
                            message: error.networkError.result,
                            type: 'error',
                        });
                    });
                }).catch(_ => {});
        },
        save(formName) {
            this.$refs[formName].validate((valid) => {
                if (valid && checkAllPeriodics(this.form.periodiques)) {
                    this.$apollo.mutate({
                        mutation: require('../../../../../graphql/simulations/resultatComptables/saveResultatComptable.gql'),
                        variables: {
                            resultatComptable: {
                                simulationId: this.simulationID,
                                uuid: this.form.id,
                                libelle: this.form.libelle,
                                periodique: JSON.stringify({periodique: this.form.periodiques.items})
                            }
                        }
                    }).then(() => {
                        this.dialogVisible = false;
                        updateData(this.query, this.simulationID).then(() => {
                            this.$message({
                                showClose: true,
                                message: 'Le patrimoine a bien été enregistré.',
                                type: "success",
                            });
                        })
                    }).catch(error => {
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
                this.form = {...this.resultatComptables[this.selectedIndex]};
            }
        },
        next() {
            if (this.selectedIndex < (this.resultatComptables.length - 1)) {
                this.selectedIndex++;
                this.form = {...this.resultatComptables[this.selectedIndex]};
            }
        },
        exportResultat() {
           window.location.href = "/export-resultat-compatible/" + this.simulationID;
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
        periodicOnChange(type) {
            let newPeriodics = this.form.periodiques[type];
            this.form.periodiques[type] = [];
            this.form.periodiques[type] = periodicFormatter(newPeriodics);
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

<style type="text/css">
    .patrimoine-reference {
        font-size: 14px;
    }
    .patrimoine-reference .el-button--small {
        font-weight: 500;
        padding: 0 15px;
        height: 40px;
        font-size: 14px;
        border-radius: 4px;
        text-align: center;
    }
    .patrimoine-reference .fixed-input {
        width: 235px;
    }
    .patrimoine-reference .form-slider i {
        font-size: 25px;
        margin-left: 50px;
        cursor: pointer;
    }
    .patrimoine-reference .el-form-item__label {
        line-height: 40px;
        text-align: left;
    }
</style>
