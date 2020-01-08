<template>
    <div class="produits-autres">
		<div class="row">
			<h1 class="admin-content-title">Autres produits</h1>
			<el-col :span="2" :offset="2">
				<el-upload
						ref="upload"
						:action.native="'/import-produit-autres/'+ simulationID"
						multiple
						:limit="10"
						:on-success="onSuccess"
						:on-error="onError">
					<el-button size="small" type="primary">Importer</el-button>
				</el-upload>
			</el-col>
			<el-col :span="2" class="export-button">
				<el-button type="success" @click.stop="exportProduitAutres">
					Exporter
				</el-button>
			</el-col>
		</div>
        <el-collapse v-model="collapseList">
            <el-collapse-item name="1">
                <template v-slot:title>
                    <el-row class="w-100">
                        <el-col :span="18"><label class="mb-0">Production immobilisée</label></el-col>
                    </el-row>
                </template>
                <el-row class="mt-2" v-loading="loading">
                    <el-col :span="3" class="text-center" style="padding-top: 65px;">
                        <div class="carousel-head">
                            <p>Montant: </p>
                        </div>
                    </el-col>
                    <el-col :span="3">
                        <div class="carousel-head pt-3">
                            <p>Calcul automatique à partir de</p>
                        </div>
                        <div class="carousel-head">
                            <el-select v-model="immobilisee.calculAutomatique" :disabled="!isModify" @change="changeCalculAutomatique(0)">
                                <el-option v-for="item in anneeDeReferences"
                                           :key="item"
                                           :label="item"
                                           :value="item"></el-option>
                            </el-select>
                        </div>
                    </el-col>
                    <el-col :span="18">
                        <periodique :anneeDeReference="anneeDeReference"
                                    :options="{disable: immobilisee.disable}"
                                    v-model="immobilisee.periodique"
                                    @onChange="otherPeriodicOnChange"></periodique>
                    </el-col>
                </el-row>
            </el-collapse-item>
            <el-collapse-item title="Produits financiers" name="2" class="mt-3">
                <el-row class="mt-2" v-loading="loading">
                    <el-col :span="3" class="text-center" style="padding-top: 65px;">
                        <div class="carousel-head">
                            <p>Montant: </p>
                        </div>
                    </el-col>
                    <el-col :span="3">
                        <div class="carousel-head pt-3">
                            <p>Calcul automatique à partir de</p>
                        </div>
                        <div class="carousel-head">
                            <el-select v-model="financiers.calculAutomatique" :disabled="!isModify" @change="changeCalculAutomatique(1)">
                                <el-option v-for="item in anneeDeReferences"
                                           :key="item"
                                           :label="item"
                                           :value="item"></el-option>
                            </el-select>
                        </div>
                    </el-col>
                    <el-col :span="18">
                        <periodique :anneeDeReference="anneeDeReference"
                                    :options="{disable: financiers.disable}"
                                    v-model="financiers.periodique"
                                    @onChange="otherPeriodicOnChange"></periodique>
                    </el-col>
                </el-row>
            </el-collapse-item>
            <el-collapse-item title="Autres produits courants et exceptionnels" name="3" class="mt-3">

                <ApolloQuery
                        :query="require('../../../../../graphql/simulations/produits/autres/produitAutres.gql')"
                        :variables="{
                        simulationId: simulationID
                    }">
                    <template slot-scope="{ result:{ loading, error, data }, isLoading, query}">
                        <div v-if="error">Une erreur est survenue !</div>
                        <el-table
                                v-loading="isLoading"
                                :data="tableData(data, query)"
                                style="width: 100%">
                            <el-table-column sortable column-key="nom" prop="nom" width="200" label="Nom"></el-table-column>
                            <el-table-column sortable column-key="nature" prop="nature" width="220" label="Nature">
                                <template slot-scope="scope">
                                    <span v-if="scope.row.nature === 0">Exceptionnel</span>
                                    <span v-else-if="scope.row.nature === 1">Autre produit courant</span>
                                    <span v-else-if="scope.row.nature === 2">Produit d'activités (compte 70)</span>
                                    <span v-else>Total</span>
                                </template>
                            </el-table-column>
                            <el-table-column v-for="column in columns"
                                             sortable
                                             align="center"
                                             :key="column.prop"
                                             :prop="column.prop"
                                             :label="column.label">
                            </el-table-column>
                            <el-table-column fixed="right" width="120" label="Actions">
                                <template slot-scope="scope">
                                    <el-button v-if="!scope.row.noAction" type="primary" icon="el-icon-edit" circle :disabled="!isModify" @click="handleEdit(scope.$index, scope.row)"></el-button>
                                    <el-button v-if="!scope.row.noAction" type="danger" icon="el-icon-delete" circle :disabled="!isModify" @click="handleDelete(scope.$index, scope.row)"></el-button>
                                </template>
                            </el-table-column>
                        </el-table>
                        <el-row class="d-flex justify-content-end my-3">
                            <el-button type="primary" :disabled="!isModify" @click="showCreateModal">Créer un produit courant et exceptionnel</el-button>
                        </el-row>
                    </template>
                </ApolloQuery>
            </el-collapse-item>
        </el-collapse>

        <el-dialog
                title="Créer une annuité des emprunts locatifs"
                :visible.sync="dialogVisible"
                :close-on-click-modal="false"
                width="70%">
            <el-row v-if="isEdit" class="form-slider text-center mb-5">
                <i class="el-icon-back font-weight-bold" @click="back"></i>
                <i class="el-icon-right font-weight-bold" @click="next"></i>
            </el-row>
            <el-form :model="form" :rules="formRules" label-width="140px" ref="autreForm">
                <el-row type="flex" :gutter="24">
                    <el-col :span="8">
                        <el-form-item label="Nom:" prop="nom">
                            <el-input type="text" v-model="form.nom" placeholder="Nom" class="fixed-input"></el-input>
                        </el-form-item>
                    </el-col>
                    <el-col :span="7">
                        <el-form-item label="Taux évolution" prop="tauxEvolution">
                            <el-input type="text" placeholder="0.0" class="fixed-input"
                                      v-model="form.tauxEvolution"
                                      @change="() => {form = Object.assign({}, form ,{'tauxEvolution' : mathInput(form.tauxEvolution)})}" ></el-input>
                        </el-form-item>
                    </el-col>
                    <el-col :span="9">
                        <el-form-item label="Nature:" prop="nature">
                            <el-select v-model="form.nature">
                                <el-option v-for="item in natureOptions"
                                           :key="item.value"
                                           :label="item.label"
                                           :value="item.value"></el-option>
                            </el-select>
                        </el-form-item>
                    </el-col>
                </el-row>
                <periodique :anneeDeReference="anneeDeReference"
                            v-model="form.periodiques"
                            @onChange="periodicOnChange"></periodique>
            </el-form>
            <div slot="footer" class="dialog-footer">
                <el-button type="outline-primary" @click="dialogVisible = false">Retour</el-button>
                <el-button type="primary" @click="save('autreForm')">Valider</el-button>
            </div>
        </el-dialog>

    </div>
</template>

<script>
    import {
        initPeriodic,
        checkPeriodic,
        mathInput,
        periodicFormatter
    } from '../../../../../utils/inputs';
    import { updateData } from '../../../../../utils/helpers'
    import Periodique from "../../../../../components/partials/Periodique";
    import customValidator from '../../../../../utils/validation-rules'
    import ExportAPI from '../../../../../api/export'
    export default {
        name: "Autres",
        components: { Periodique },
        data() {
            return {
                simulationID: null,
                anneeDeReference: null,
                columns: [],
                collapseList: ['1', '2', '3'],
                immobilisee: null,
                financiers: null,
                anneeDeReferences: [],
                produitAutres: [],
                dialogVisible: false,
                form: null,
                isEdit: false,
                selectedIndex: null,
                loading: false,
                query: null,
                natureOptions: [{
                    value: 0,
                    label: 'Exceptionnel'
                }, {
                    value: 1,
                    label: 'Autre produit courant'
                }, {
                    value: 2,
                    label: 'Produit d\'activités (compte 70)'
                }],
                formRules: {
                    nom: [
                        { required: true, message: "S'il vous plaît entrez un nom", trigger: 'change' }
                    ],
                    nature: [
                        { required: true, message: "S'il vous plaît sélectionner la nature", trigger: 'change' }
                    ],
                    tauxEvolution : customValidator.getRule('taux')
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

            this.init();
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
            mathInput,
            init() {
                const immobilisee = initPeriodic();
                const financiers = initPeriodic();
                const disable = initPeriodic();

                this.immobilisee = {
                    calculAutomatique: null,
                    periodique: {
                        immobilisee
                    },
                    disable: {immobilisee: disable}
                };
                this.financiers = {
                    calculAutomatique: null,
                    periodique: {
                        financiers
                    },
                    disable: {financiers: disable}
                };

                this.loading = true;

                this.getAnneeDeReference();
                this.getAutres(0);
                this.getAutres(1);
            },
            initForm() {
                this.form = {
                    id: null,
                    nom: '',
                    nature: 0,
                    periodiques: {
                        periodiques: initPeriodic()
                    },
                    tauxEvolution: null
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
            setTableColumns() {
                this.columns = [];
                this.anneeDeReferences = [];
                for (var i = 0; i < 50; i++) {
                    let anneeDeReference = parseInt(this.anneeDeReference) + i;
                    this.anneeDeReferences.push(anneeDeReference);
                    this.columns.push({
                        label: anneeDeReference.toString(),
                        prop: `periodiques.periodiques[${i}]`
                    });
                }
            },
            tableData(data, query) {
                if (!_.isNil(data)) {
                    this.query = query;
                    let produitAutres = data.produitAutres.items.map(item => {
                        let periodiques = [];
                        item.produitsAutresPeriodique.items.forEach(periodique => {
                            periodiques[periodique.iteration - 1] = periodique.value;
                        });
                        return {
                            id: item.id,
                            nom: item.nom,
                            nature: item.nature,
                            tauxEvolution: item.tauxEvolution,
                            periodiques: {periodiques}
                        };
                    });

                    this.produitAutres = produitAutres;
                    this.query = query;
                    // Calculate total sum.
                    if (produitAutres.length > 0) {
                        let sumPeriodique = [];
                        produitAutres.forEach(produitAutre => {
                            produitAutre.periodiques.periodiques.forEach((item, index) => {
                                if (!sumPeriodique[index]) {
                                    sumPeriodique[index] = 0;
                                }
                                sumPeriodique[index] += item;
                            });
                        });
                        produitAutres.push({
                            nom: '',
                            nature: null,
                            periodiques: {periodiques: sumPeriodique},
                            noAction: true
                        });
                    }

                    return produitAutres;
                } else {
                    return [];
                }
            },
            getAutres(type) {
                this.$apollo.query({
                    query: require('../../../../../graphql/simulations/produits/autres/produitAutres.gql'),
                    fetchPolicy: 'no-cache',
                    variables: {
                        simulationId: this.simulationID,
                        type: type
                    }
                }).then((res) => {
                    if (res.data && res.data.produitAutres && res.data.produitAutres.items.length > 0) {
                        const produitAutres = res.data.produitAutres.items[0];
                        let periodiques = [];
                        produitAutres.produitsAutresPeriodique.items.forEach(periodique => {
                            periodiques[periodique.iteration - 1] = periodique.value;
                        });

                        if (type === 0) {
                            this.immobilisee = {
                                id: produitAutres.id,
                                calculAutomatique: produitAutres.calculAutomatique,
                                periodique: { immobilisee: periodiques},
                                disable: {immobilisee: this.getDisabledFields(produitAutres.calculAutomatique)}
                            }
                        } else if(type === 1) {
                            this.financiers = {
                                id: produitAutres.id,
                                calculAutomatique: produitAutres.calculAutomatique,
                                periodique: { financiers : periodiques},
                                disable: {financiers: this.getDisabledFields(produitAutres.calculAutomatique)}
                            }
                        }
                    }

                    this.loading = false;
                });
            },
            getDisabledFields(calculAutomatique) {
                let disable = [];
                for (var i = 0; i < 50; i++) {
                    let year = parseInt(this.anneeDeReference) + i;
                    if (calculAutomatique && year > calculAutomatique) {
                        disable.push(true);
                    } else {
                        disable.push(false);
                    }
                }
                return disable;
            },
            getLastMonant(autres, periodiques) {
                const index = parseInt(autres.calculAutomatique) - parseInt(this.anneeDeReference);
                return periodiques[index];
            },
            changeCalculAutomatique(type, value) {
                if (type === 0) {
                    this.immobilisee.disable = {immobilisee: this.getDisabledFields(this.immobilisee.calculAutomatique)};
                    this.saveAutres(0, this.immobilisee, this.immobilisee.periodique.immobilisee);
                } else if(type === 1) {
                    this.financiers.disable = {financiers: this.getDisabledFields(this.financiers.calculAutomatique)};
                    this.saveAutres(1, this.financiers, this.financiers.periodique.financiers);
                }
            },
            saveAutres(type, autres, periodiques) {
                if (checkPeriodic(periodiques)){
                    this.$apollo.mutate({
                        mutation: require('../../../../../graphql/simulations/produits/autres/saveProduitAutre.gql'),
                        variables: {
                            produitAutre: {
                                simulationId: this.simulationID,
                                uuid: autres.id,
                                montants: this.getLastMonant(autres, periodiques),
                                calculAutomatique : autres.calculAutomatique,
                                type: type,
                                periodique: JSON.stringify({periodique: periodiques})
                            }
                        }
                    }).then(() => {
                        this.$message({
                            showClose: true,
                            message: 'Les valeurs ont été enregistrées.',
                            type: 'success'
                        });
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
            },
            showCreateModal() {
                this.initForm();
                this.dialogVisible = true;
                this.isEdit = false;
            },
            save(formName) {
                this.$refs[formName].validate((valid) => {
                    if (valid && checkPeriodic(this.form.periodiques.periodiques)) {
                        this.$apollo.mutate({
                            mutation: require('../../../../../graphql/simulations/produits/autres/saveProduitAutre.gql'),
                            variables: {
                                produitAutre: {
                                    simulationId: this.simulationID,
                                    uuid: this.form.id,
                                    nom: this.form.nom,
                                    nature: this.form.nature,
                                    tauxEvolution: this.form.tauxEvolution,
                                    type: 2,
                                    periodique: JSON.stringify({periodique: this.form.periodiques.periodiques})
                                }
                            }
                        }).then(() => {
                            this.dialogVisible = false;
                            updateData(this.query, this.simulationID).then(() => {
                                this.$message({
                                    showClose: true,
                                    message: 'Cette variation a bien été enregistrée.',
                                    type: 'success'
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
            handleEdit(index, row) {
                this.dialogVisible = true;
                this.form = {...row};
                this.selectedIndex = index;
                this.isEdit = true;
            },
            handleDelete(index, row) {
                this.$confirm('Êtes-vous sûr de vouloir supprimer cette variation ?')
                    .then(_ => {
                        this.$apollo.mutate({
                            mutation: require('../../../../../graphql/simulations/produits/autres/removeProduitAutre.gql'),
                            variables: {
                                produitAutreUUID: row.id,
                                simulationId: this.simulationID
                            }
                        }).then(() => {
                            updateData(this.query, this.simulationID).then(() => {
                                this.$message({
                                    showClose: true,
                                    message: 'Cette variation a bien été supprimée.',
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
                    .catch(_ => {});
            },
            back() {
                if (this.selectedIndex > 0) {
                    this.selectedIndex--;
                    this.form = {...this.produitAutres[this.selectedIndex]};
                }
            },
            next() {
                if (this.selectedIndex < (this.produitAutres.length - 2)) {
                    this.selectedIndex++;
                    this.form = {...this.produitAutres[this.selectedIndex]};
                }
            },
            exportProduitAutres() {
                ExportAPI.exportProduitAutres(this.simulationID).then(res => {
                    const url = window.URL.createObjectURL(new Blob([res.data]));
                    const link = document.createElement('a');
                    link.href = url;
                    link.setAttribute('download', 'export.xlsx');
                    document.body.appendChild(link);
                    link.click()
                }).catch(async (error) => {
                    const message = await (new Response(error.response.data)).text();
                    this.$toasted.error(message, {
                        theme: 'toasted-primary',
                        icon: 'error',
                        position: 'top-right',
                        duration: 5000
                    });
                })
            },
            onSuccess(res) {
                this.$toasted.success(res, {
                    theme: 'toasted-primary',
                    icon: 'check',
                    position: 'top-right',
                    duration: 5000
                });

                this.$refs.upload.clearFiles();
                updateData(this.query, this.simulationID);
            },
            onError(error) {
                this.$toasted.error(JSON.parse(error.message), {
                    theme: 'toasted-primary',
                    icon: 'error',
                    position: 'top-right',
                    duration: 5000
                });

                this.$refs.upload.clearFiles();
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
            otherPeriodicOnChange(type) {
                if (type === 'immobilisee') {
                    let newPeriodics = this.immobilisee.periodique.immobilisee;
                    this.immobilisee.periodique.immobilisee = [];
                    this.immobilisee.periodique.immobilisee = periodicFormatter(newPeriodics);
                    this.saveAutres(0, this.immobilisee, this.immobilisee.periodique.immobilisee);
                } else {
                    let newPeriodics = this.financiers.periodique.financiers;
                    this.financiers.periodique.financiers = [];
                    this.financiers.periodique.financiers = periodicFormatter(newPeriodics);
                    this.saveAutres(1,this.financiers, this.financiers.periodique.financiers);
                }
            },
            periodicOnChange(type) {
                let newPeriodics = this.form.periodiques[type];
                this.form.periodiques[type] = [];
                this.form.periodiques[type] = periodicFormatter(newPeriodics);
            },
        }
    }
</script>

<style>
    .produits-autres .fixed-input {
        width: 235px;
    }
    .produits-autres .el-button--small {
        font-weight: 500;
        padding: 0 15px;
        height: 40px;
        font-size: 14px;
        border-radius: 4px;
        text-align: center;
    }
    .produits-autres .el-collapse-item__header {
        padding-left: 15px;
        font-weight: bold;
        font-size: 15px;
        height: max-content;
    }
    .produits-autres .el-collapse-item__header i {
        font-weight: bold;
    }
    .produits-autres .item-wrapper {
        margin: 20px 60px;
        display: table;
    }
    .produits-autres .period-item {
        width: 10%;
        font-size: 12px;
        padding: 0 5px;
        display: table-cell;
        text-align: center;
    }
    .produits-autres .period-item .el-input__inner{
        padding: 0 3px;
        text-align: right;
    }
    .produits-autres .el-carousel__button {
        background-color: #2591eb;
        pointer-events: none;
    }
    .produits-autres .el-carousel__arrow i {
        color: #2591eb;
    }
    .produits-autres .carousel-head {
        height: 58px;
        line-height: 1;
    }
    .produits-autres .reset-control {
        width: 40px;
        height: 50px;
        padding-top: 20px;
    }
    .produits-autres .el-icon-refresh {
        color: #2591eb;
        font-size: 20px;
        font-weight: bold;
        cursor: pointer;
    }
    .produits-autres .form-slider i {
        font-size: 25px;
        margin-left: 50px;
        cursor: pointer;
    }
    .produits-autres .el-form-item__label {
        line-height: 40px;
    }
	.produits-autres .period-item .el-input {
		width: 45px;
	}
</style>
