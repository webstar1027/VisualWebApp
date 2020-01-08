<template>
    <div class="produits-charges">
        <el-card>
            <template slot="title">
                <label> Liste des Produits Et Charges </label>
            </template>
            <ApolloQuery
                    :query="require('../../../../../../../graphql/simulations/accessions/autres-couts/produit-charge/accessionProduitCharges.gql')"
                    :variables="{
								simulationId: simulationID
						}">
                <template slot-scope="{ result:{ loading, error, data }, isLoading, query }">
                    <div v-if="error">Une erreur est survenue !</div>
                    <el-table
                            :data="tableData(data, query)"
                            v-loading="isLoading"
                            style="width: 100%">
                        <el-table-column sortable fixed column-key="number" prop="number" min-width="60" label="N°"></el-table-column>
                        <el-table-column sortable fixed column-key="activite" prop="activite" min-width="120" label="Activite"></el-table-column>
                        <el-table-column sortable fixed column-key="libelle" prop="libelle" min-width="120" label="Libelle"></el-table-column>
                        <el-table-column sortable fixed column-key="type" prop="type" min-width="100" label="Nature">
                            <template slot-scope="scope">
                                <span>{{renderType(scope.row.type)}}</span>
                            </template>
                        </el-table-column>
                        <el-table-column sortable fixed column-key="type" prop="indexation" min-width="120" label="Indexation automatique">
                            <template slot-scope="scope">
                                <span v-if="!scope.row.noAction">{{scope.row.indexation ? 'Oui' : 'Non'}}</span>
                            </template>
                        </el-table-column>
                        <el-table-column sortable fixed column-key="tauxDevolution" prop="tauxDevolution" min-width="150" align="center" label="Taux d'évolution"></el-table-column>
                        <el-table-column
                                v-for="column in produitChargesColumns"
                                sortable
                                align="center"
                                :key="column.prop"
                                :prop="column.prop"
                                :label="column.label">
                        </el-table-column>
                        <el-table-column fixed="right" width="120" label="Actions">
                            <template slot-scope="scope">
                                <el-button v-if="!scope.row.noAction" type="primary" :disabled="!isModify" icon="el-icon-edit" circle @click="handleEdit(scope.$index, scope.row)"></el-button>
                                <el-button v-if="!scope.row.noAction" type="danger" :disabled="!isModify" icon="el-icon-delete" circle @click="handleDelete(scope.$index, scope.row)"></el-button>
                            </template>
                        </el-table-column>
                    </el-table>
                </template>
            </ApolloQuery>

            <el-row class="d-flex justify-content-end my-3">
                <el-button type="primary" :disabled="!isModify" @click="showCreateModal">Créer un produit / charge</el-button>
            </el-row>
        </el-card>
        <el-dialog
                title="Création / Modification de produits et charges"
                :visible.sync="dialogVisible"
                :close-on-click-modal="false"
                width="70%">
            <el-row v-if="isEdit" class="form-slider text-center mb-5">
                <i class="el-icon-back font-weight-bold" @click="back"></i>
                <i class="el-icon-right font-weight-bold" @click="next"></i>
            </el-row>
            <el-form :model="form" :rules="formRules" label-width="160px" ref="produitChargeForm">
                <div class="row">
                    <div class="col-sm-6">
                        <el-form-item label="N°  " prop="number">
                            <el-input type="text" v-model="form.number" placeholder="0" class="text-input" disabled></el-input>
                        </el-form-item>
                    </div>
                    <div class="col-sm-6">
                        <el-form-item label="Activite " prop="activite">
                            <el-select v-model="form.activite" :disabled="isEdit">
                                <el-option v-for="item in activites"
                                           :key="item.value"
                                           :label="item.label"
                                           :value="item.value"></el-option>
                            </el-select>
                        </el-form-item>
                    </div>
                </div>
                <div class="row mt-3" type="flex" justify="space-around">
                    <div class="col-sm-6">
                        <el-form-item label="Libellé " prop="libelle">
                            <el-input type="text" v-model="form.libelle" placeholder="Libellé" class="text-input" :disabled="isEdit"></el-input>
                        </el-form-item>
                    </div>
                    <div class="col-sm-6">
                        <el-form-item label="Nature " prop="type">
                            <el-select v-model="form.type" :disabled="isEdit">
                                <el-option v-for="item in types"
                                           :key="item.value"
                                           :label="item.label"
                                           :value="item.value"></el-option>
                            </el-select>
                        </el-form-item>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-sm-6">
                        <div style="padding-top:30px">
                            <el-form-item>
                                <el-checkbox v-model="form.indexation" label="Indexation automatique" name="indexation"></el-checkbox>
                            </el-form-item>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <el-form-item label="Taux d'évolution " prop="tauxDevolution">
                            <el-input type="text" v-model="form.tauxDevolution" placeholder="0" class="text-input" :disabled="!form.indexation"></el-input>
                        </el-form-item>
                    </div>
                </div>
                <periodique :anneeDeReference="anneeDeReference"
                      v-model="form.periodiques"
                      @hasError="hasPeriodiqueError"></periodique>
            </el-form>
            <div slot="footer" class="dialog-footer">
                <el-button type="primary" @click="dialogVisible = false">Retour</el-button>
                <el-button type="success" @click="save('produitChargeForm')">Valider</el-button>
            </div>
        </el-dialog>
    </div>
</template>
<script>
    import Periodique from "../../../../../../../components/partials/Periodique";
    import {updateData} from "../../../../../../../utils/helpers";
    import {initPeriodic} from "../../../../../../../utils/inputs";

    export default {
        name:"ProduitsEtCharges",
        props: ['showError'],
        components: { Periodique },
        data() {
            return {
                activeList: ['1', '2'],
                simulationID: null,
                anneeDeReference: null,
                recapColumns: [],
                recapList: [],
                produitCharges: [],
                produitChargesColumns: [],
                dialogVisible: false,
                isEdit: false,
                form: {},
                selectedIndex: null,
                periodiqueHasError: false,
                types: [
                    {
                        value: 0,
                        label: 'Produit'
                    }, {
                        value: 1,
                        label: 'Charge'
                    }
                ],
                formRules: {
                    libelle: [
                        { required: true, message: "Vous devez saisir un libellé de produit et charge", trigger: 'change' }
                    ],
                    type: [
                        { required: true, message: "Vous devez saisir la nature de produit et charge", trigger: 'change' }
                    ],
                    activite: [
                        { required: true, message: "Vous devez saisir l'activité de produit et charge", trigger: 'change' }
                    ]
                },
                activites: [],
                currentLastNumber: 0,
                query: null
            }
        },
        computed: {
            isModify() {
                return this.$store.getters['choixEntite/isModify'];
            }
        },
        created () {
            let simulationID = _.isNil(this.$route.params.id) ? null : this.$route.params.id;
            if (_.isNil(simulationID)) {
                return;
            }
            this.simulationID = simulationID;

            this.getAnneeDeReference();
            this.initForm();
            this.getActivites();
            this.$apollo
                .query({
                    query: require("../../../../../../../graphql/administration/partagers/checkStatus.gql"),
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
                    number: 1,
                    libelle: '',
                    indexation: true,
                    type: '',
                    tauxDevolution: null,
                    periodiques: {items: initPeriodic()}
                };
            },
            getAnneeDeReference() {
                this.$apollo.query({
                    query: require('../../../../../../../graphql/simulations/simulation.gql'),
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
                this.recapColumns = [];
                this.produitChargesColumns = [];
                for (var i = 0; i < 50; i++) {
                    this.recapColumns.push({
                        label: (parseInt(this.anneeDeReference) + i).toString(),
                        prop: `items[${i}]`
                    });
                    this.produitChargesColumns.push({
                        label: (parseInt(this.anneeDeReference) + i).toString(),
                        prop: `periodiques.items[${i}]`
                    });
                }
            },
            tableData(data, query) {
                if (!_.isNil(data)) {
                    this.query = query;
                    const produitCharges = data.accessionProduitCharges.items.map(item => {
                        let periodiques = [];
                        item.accessionProduitsChargesPeriodique.items.forEach(periodique => {
                            periodiques[periodique.iteration - 1] = periodique.value;
                        });
                        return {
                            id: item.id,
                            number: item.number,
                            libelle: item.libelle,
                            indexation: item.indexation,
                            type: item.type,
                            activite: item.codification.activite,
                            tauxDevolution: item.tauxDevolution,
                            periodiques: {items: periodiques},
                        }
                    });

                    if (produitCharges.length > 0) {
                        this.currentLastNumber = produitCharges[produitCharges.length - 1].number;
                    }

                    // Get total value.
                    let sumPeriodique = [];
                    produitCharges.forEach(produitCharge => {
                      produitCharge.periodiques.items.forEach((item, index) => {
                          if (!sumPeriodique[index]) {
                              sumPeriodique[index] = 0;
                          }
                          sumPeriodique[index] += item;
                      });
                    });
                    produitCharges.push({
                      libelle: 'Total',
                      periodiques: {items: sumPeriodique},
                      noAction: true
                    });

                    this.produitCharges = produitCharges;
                    return produitCharges;
                } else {
                    return [];
                }
            },
            renderType(type) {
                const selectedType = this.types.find(item => item.value === type);
                return selectedType && selectedType.label;
            },
            showCreateModal() {
                this.initForm();
                this.form.number = this.currentLastNumber + 1;
                this.dialogVisible = true;
                this.isEdit = false;
                if (this.$refs.produitChargeForm) {
                    this.$refs.produitChargeForm.clearValidate();
                }
            },
            handleEdit(index, row) {
                this.dialogVisible = true;
                this.form = {...row};
                this.selectedIndex = index;
                this.isEdit = true;
            },
            handleDelete(index, row) {
                this.$confirm('Êtes-vous sûr de vouloir supprimer ce produit ?')
                    .then(_ => {
                        this.$apollo.mutate({
                            mutation: require('../../../../../../../graphql/simulations/accessions/autres-couts/produit-charge/removeAccessionProduitCharge.gql'),
                            variables: {
                                produitChargeId: row.id,
                                simulationId: this.simulationID
                            }
                        }).then(() => {
                            updateData(this.query, this.simulationID).then(() => {
                                this.$message({
                                    showClose: true,
                                    message: 'Ce produit a bien été supprimé.',
                                    type: 'success'
                                });
                            })
                        }).catch(error => {
                            updateData(this.query, this.simulationID).then(() => {
                                this.$message({
                                    showClose: true,
                                    message: 'Ce produit n\'existe pas.',
                                    type: 'error',
                                });
                            });
                        });
                    })
                    .catch(_ => {});
            },
            getActivites() {
                this.activites = [];
                this.$apollo.query({
                    query: require('../../../../../../../graphql/simulations/accessions/autres-couts/codifications/accessionCodifications.gql'),
                    variables: {
                        simulationId: this.simulationID
                    }
                }).then((res) => {
                    let codifications = res.data.accessionCodifications.items;
                    codifications.forEach(item => {
                        let data = {
                            value: item.id,
                            label: item.activite
                        }

                        this.activites.push(data);
                    });

                });
            },
            hasPeriodiqueError(error) {
                this.periodiqueHasError = error;
            },
            save(formName) {

                let codificationId = null;

                if (this.isEdit) {
                    this.activites.forEach(item => {
                        if (item.label === this.form.activite) {
                            codificationId = item.value;
                        }
                    })
                } else {
                    codificationId = this.form.activite;
                }

                this.$refs[formName].validate((valid) => {
                    if (valid && !this.periodiqueHasError) {
                        this.$apollo.mutate({
                            mutation: require('../../../../../../../graphql/simulations/accessions/autres-couts/produit-charge/saveAccessionProduitCharge.gql'),
                            variables: {
                                produitCharge: {
                                    id: this.form.id,
                                    simulationId: this.simulationID,
                                    libelle: this.form.libelle,
                                    indexation: this.form.indexation,
                                    type: this.form.type,
                                    codificationId: codificationId,
                                    taux_devolution: this.form.tauxDevolution,
                                    periodique: JSON.stringify({periodique: this.form.periodiques.items})
                                }
                            }
                        }).then(() => {
                            this.dialogVisible = false;
                            this.isSubmitting = false;
                            updateData(this.query, this.simulationID).then(() => {
                                this.$message({
                                    showClose: true,
                                    message: 'Ce produit a bien été enregistré.',
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
            back() {
                if (this.selectedIndex > 0) {
                    this.selectedIndex--;
                    this.form = {...this.produitCharges[this.selectedIndex]};
                }
            },
            next() {
                if (this.selectedIndex < (this.produitCharges.length - 1)) {
                    this.selectedIndex++;
                    this.form = {...this.produitCharges[this.selectedIndex]};
                }
            }
        }
    }
</script>

<style type="text/css">
    .produits-charges .el-collapse-item__header {
        padding-left: 15px;
        font-weight: bold;
        font-size: 15px;
    }
    .produits-charges .el-collapse-item__header .header-btn-group {
        width: -webkit-fill-available;
        text-align: right;
        padding-right: 20px;
    }
    .produits-charges .el-collapse-item__content {
        padding-bottom: 0;
    }
    .produits-charges .el-collapse-item__header i {
        font-weight: bold;
    }
    .produits-charges .text-input {
        width: 235px;
    }
    .produits-charges .el-input.is-disabled .el-input__inner {
        color: black;
    }
    .produits-charges .form-slider i {
        font-size: 25px;
        margin-left: 50px;
        cursor: pointer;
    }
    .produits-charges .el-form-item__label {
        line-height: 40px;
    }
</style>
