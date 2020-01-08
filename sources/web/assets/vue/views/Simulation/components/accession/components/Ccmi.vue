<template>
    <div class="ccmi">
        <el-row class="d-flex justify-content-end my-3">
            <el-button type="primary" @click="showCreateModal">Création d’un CCMI</el-button>
        </el-row>
        <ApolloQuery
                :query="require('../../../../../graphql/simulations/accessions/ccmi/ccmi.gql')"
                :variables="{
                simulationId: simulationID
            }">
            <template slot-scope="{ result:{ loading, error, data }, isLoading, query }">
                <div v-if="error">Une erreur est survenue !</div>
                <el-table
                        v-loading="isLoading"
                        :data="tableData(data, query)"
                        style="width: 100%">
                    <el-table-column sortable column-key="numero" prop="numero" min-width="120" label="N°">
                        <template slot="header">
                            <span title="N°">N°</span>
                        </template>
                    </el-table-column>
                    <el-table-column sortable column-key="nomOperation" prop="nomOperation" min-width="150" label="Nom de l'opération">
                        <template slot="header">
                            <span title="Nom de l'opération">Nom de l'opération</span>
                        </template>
                    </el-table-column>
                    <el-table-column sortable column-key="prixVente" prop="prixVente" min-width="150" label="Prix de vente par maison en K€">
                        <template slot="header">
                            <span title="Prix de vente par maison en K€">Prix de vente par maison en K€</span>
                        </template>
                    </el-table-column>
                    <el-table-column sortable column-key="tauxMargeBrute" prop="tauxMargeBrute" min-width="150" label="Taux de marge brute">
                        <template slot="header">
                            <span title="Taux de marge brute">Taux de marge brute</span>
                        </template>
                    </el-table-column>
                    <el-table-column sortable column-key="coutsInternesStockes" prop="coutsInternesStockes" min-width="180" label="Coûts internes stockés en %">
                        <template slot="header">
                            <span title="Coûts internes stockés en %">Coûts internes stockés en %</span>
                        </template>
                    </el-table-column>
                    <el-table-column sortable column-key="portageFondsPropres" prop="portageFondsPropres" min-width="170" label="Portage sur fonds propres">
                        <template slot="header">
                            <span title="Portage sur fonds propres">Portage sur fonds propres</span>
                        </template>
                    </el-table-column>
                    <el-table-column v-for="column in columns"
                        sortable
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
            </template>
        </ApolloQuery>

        <el-dialog
                :title="!isEdit ? 'Création d’un CCMI' : 'Modification du CCMI'"
                :visible.sync="dialogVisible"
                :close-on-click-modal="false"
                width="65%">
            <el-row v-if="isEdit" class="form-slider text-center mb-5">
                <i class="el-icon-back font-weight-bold" @click="back"></i>
                <i class="el-icon-right font-weight-bold" @click="next"></i>
            </el-row>
            <el-form :model="form" :rules="formRules" label-width="170px" ref="ccmiForm">
                <el-collapse v-model="collapseList">
                    <el-collapse-item title="Données Générales" name="1">
                        <el-row type="flex" justify="space-around" :gutter="24">
                            <el-col :span="8">
                                <el-form-item label="Numéro" prop="numero">
                                    <el-input type="text" v-model="form.numero" placeholder="0"></el-input>
                                </el-form-item>
                            </el-col>
                            <el-col :span="8">
                                <el-form-item label="Nom de l’opération" prop="nomOperation">
                                    <el-input type="text" v-model="form.nomOperation"></el-input>
                                </el-form-item>
                            </el-col>
                        </el-row>
                    </el-collapse-item>
                    <el-collapse-item title="Eléments financiers CCMI" name="2">
                        <el-row type="flex" justify="space-around" :gutter="24">
                            <el-col :span="8">
                                <el-form-item prop="prixVente" class="custom-append-input" label="Prix de vente à la maison en K€">
                                    <el-input type="text" v-model="form.prixVente" placeholder="0"
                                              @change="formatInput('prixVente')">
                                        <template slot="append">
                                            <el-tooltip class="item" effect="dark" content="Hors terrain" placement="top">
                                                <i class="el-icon-info"></i>
                                            </el-tooltip>
                                        </template>
                                    </el-input>
                                </el-form-item>
                            </el-col>
                            <el-col :span="8">
                                <el-form-item prop="tauxMargeBrute" label="Taux de marge brute" class="custom-append-input">
                                    <el-input type="text" v-model="form.tauxMargeBrute" placeholder="0"
                                              @change="formatInput('tauxMargeBrute')">
                                        <template slot="append">
                                            <el-tooltip class="item" effect="dark" content="Prix de vente - coût de production (y compris les coûts internes stockés)" placement="top">
                                                <i class="el-icon-info"></i>
                                            </el-tooltip>
                                        </template>
                                    </el-input>
                                </el-form-item>
                            </el-col>
                        </el-row>
                        <el-row type="flex" justify="space-around" class="mt-2" :gutter="24">
                            <el-col :span="8">
                                <el-form-item label="Coûts internes stockés en % du prix de vente" prop="coutsInternesStockes">
                                    <el-input type="text" v-model="form.coutsInternesStockes" placeholder="0"
                                              @change="formatInput('coutsInternesStockes')"></el-input>
                                </el-form-item>
                            </el-col>
                            <el-col :span="8">
                                <el-form-item prop="portageFondsPropres" label="Portage sur fonds propres en K€">
                                    <el-input type="text" v-model="form.portageFondsPropres" placeholder="0"
                                              @change="formatInput('portageFondsPropres')"></el-input>
                                </el-form-item>
                            </el-col>
                        </el-row>
                    </el-collapse-item>
                    <el-collapse-item title="Livraisons" name="3">
                        <el-row :gutter="24">
                            <el-col :span="5" style="padding-top: 55px;">
                                <div class="carousel-head">Nombre de maisons livrées</div>
                            </el-col>
                            <el-col :span="19">
                        <periodique :anneeDeReference="anneeDeReference"
                                    v-model="form.periodiques"
                                    @onChange="periodicOnChange"></periodique>
                            </el-col>
                        </el-row>
                    </el-collapse-item>
                </el-collapse>
            </el-form>
            <div slot="footer" class="dialog-footer">
                <el-button type="primary" @click="dialogVisible = false">Retour</el-button>
                <el-button type="success" :disabled="isSubmitting" @click="save">Valider</el-button>
            </div>
        </el-dialog>
    </div>
</template>

<script>
    import { updateData } from '../../../../../utils/helpers';
    import customValidator from '../../../../../utils/validation-rules';
    import Periodique from "../../../../../components/partials/Periodique";
    import { initPeriodic, checkAllPeriodics, mathInput, periodicFormatter } from '../../../../../utils/inputs';

    export default {
        name: "Ccmi",
        components: { Periodique },
        data () {
            var validatePortageFondsPropres = (rule, value, callback) => {
                if (value || value !== '') {
                    if (value && isNaN(value)) {
                        callback(new Error('Ce champs est incorrect'));
                    }
                    let floatVal = parseFloat(value)
                    if (floatVal !== 0 && (floatVal < 5 || floatVal > 95)) {
                        callback(new Error('La valeur doit être comprise entre 5% et 95% ou égale à 0%'));
                    }
                    callback()
                }
            };
            return {
                query: null,
                simulationID: null,
                dialogVisible: false,
                collapseList: ['1'],
                isEdit: false,
                isSubmitting: false,
                form: null,
                anneeDeReference: null,
                ccmis: [],
                columns: [],
                formRules: {
                    numero: customValidator.getPreset('number.positiveInt'),
                    nomOperation: [
                        customValidator.getRule('required'),
                        customValidator.getRule('maxVarchar')
                    ],
                    prixVente: customValidator.getRule('positiveDouble'),
                    coutsInternesStockes: customValidator.getRule('taux'),
                    tauxMargeBrute: customValidator.getRule('taux'),
                    portageFondsPropres: {validator: validatePortageFondsPropres, trigger: blur}
                },
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
            showCreateModal() {
                this.initForm();
                this.dialogVisible = true;
                this.isEdit = false;
            },
            initForm() {
                this.form = {
                    id: null,
                    numero: null,
                    nomOperation: null,
                    prixVente: null,
                    tauxMargeBrute: null,
                    portageFondsPropres: null,
                    coutsInternesStockes: null,
                    periodiques: {
                        items: initPeriodic()
                    },
                    selectedIndex: null,
                };
            },
            save () {
                this.$refs['ccmiForm'].validate((valid) => {
                    if (valid && checkAllPeriodics(this.form.periodiques)) {
                        this.isSubmitting = true;
                        this.$apollo.mutate({
                            mutation: require('../../../../../graphql/simulations/accessions/ccmi/saveCcmi.gql'),
                            variables: {
                                ccmi: {
                                    uuid: this.form.id,
                                            simulationId: this.simulationID,
                                    numero: this.form.numero,
                                    nomOperation: this.form.nomOperation,
                                    prixVente: this.form.prixVente,
                                    tauxMargeBrute: this.form.tauxMargeBrute,
                                    portageFondsPropres: this.form.portageFondsPropres,
                                    coutsInternesStockes: this.form.coutsInternesStockes,
                                    periodique: JSON.stringify({periodique: this.form.periodiques.items})
                                }
                            }
                        }).then(() => {
                            this.dialogVisible = false;
                            this.isSubmitting = false;
                            updateData(this.query, this.simulationID).then(() => {
                                this.$message({
                                    showClose: true,
                                    message: 'Ce CCMI a bien été enregistré.',
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
            back() {
                if (this.selectedIndex > 0) {
                    this.selectedIndex--;
                    this.form = {...this.ccmis[this.selectedIndex]};
                }
            },
            next() {
                if (this.selectedIndex < (this.ccmis.length - 1)) {
                    this.selectedIndex++;
                    this.form = {...this.ccmis[this.selectedIndex]};
                }
            },
            periodicOnChange(type) {
                let newPeriodics = this.form.periodiques[type];
                this.form.periodiques[type] = [];
                this.form.periodiques[type] = periodicFormatter(newPeriodics);
            },
            tableData(data, query) {
                if (!_.isNil(data)) {
                    this.query = query;
                    const ccmis = data.ccmi.items.map(item => {
                        let periodiques = [];
                        item.periodique.items.forEach(periodique => {
                            periodiques[periodique.iteration - 1] = periodique.nombreMaisonsLivrees;
                        });
                        let row = {...item};
                        row.numero += ''
                        row.prixVente += ''
                        row.tauxMargeBrute += ''
                        row.portageFondsPropres += ''
                        row.coutsInternesStockes += ''
                        row.periodiques = {
                            items: periodiques
                        };
                        return row
                    });
                    this.ccmis = ccmis;
                    return ccmis;
                } else {
                    return [];
                }
            },
            handleEdit(index, row) {
                this.dialogVisible = true;
                this.form = {...row};
                this.selectedIndex = index;
                this.isEdit = true;
            },
            handleDelete(index, row) {
                this.$confirm('Êtes-vous sûr de vouloir supprimer ce CCMI?')
                    .then(() => {
                        this.$apollo.mutate({
                            mutation: require('../../../../../graphql/simulations/accessions/ccmi/removeCcmi.gql'),
                            variables: {
                                ccmiId: row.id,
                                simulationId: this.simulationID
                            }
                        }).then(() => {
                            updateData(this.query, this.simulationID).then(() => {
                                this.$message({
                                    showClose: true,
                                    message: 'Ce CCMI a bien été supprimé.',
                                    type: 'success'
                                });
                            })
                        }).catch(error => {
                            updateData(this.query, this.simulationID).then(() => {
                                this.$message({
                                    showClose: true,
                                    message: 'Ce CCMI n\'existe pas.',
                                    type: 'error',
                                });
                            });
                        });
                    })
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
            }
        }
    }
</script>

<style>
    .ccmi .form-slider i {
        font-size: 25px;
        margin-left: 50px;
        cursor: pointer;
    }
    .ccmi .el-tooltip.el-icon-info {
        font-size: 25px;
        color: #2491eb;
        vertical-align: middle;
    }
    .ccmi .el-collapse-item__header{
        padding-left: 15px;
        font-weight: bold;
        font-size: 16px;
        color: #00436f;
        margin-top: 20px
    }
    .ccmi .el-collapse-item__header:hover{
        background-color: rgba(0, 0, 0, 0.03);
    }
    .ccmi .el-collapse-item__content {
        padding-top: 20px;
    }
</style>
