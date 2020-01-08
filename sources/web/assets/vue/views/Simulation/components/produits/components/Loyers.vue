<template>
    <div class="produits-loyers admin-content-wrap">
        <div class="row">
			<h1 class="admin-content-title">Loyers</h1>
			<el-col :span="2" :offset="2">
                <el-upload
                        ref="upload"
                        :action="'/import-produit-loyer/'+ simulationID"
                        multiple
                        :limit="10"
                        :disabled="!isModify"
                        :on-success="onSuccessImport"
                        :on-error="onErrorImport">
                    <el-button type="primary">Importer</el-button>
                </el-upload>
		</el-col>
			<el-col :span="2">
                <el-button class="ml-2" type="success" :disabled="!isModify" @click="exportProduitLoyer">Exporter</el-button>
			</el-col>
        </div>
		<el-collapse v-model="collapseList">
			<el-collapse-item :title="`Loyers des logements de l’année ${anneeDeReference}`" name="1">
				<el-form :model="logements" label-width="300px" class="pb-3 pt-3" ref="parameterForm" :rules="formRules" >
					<div class="row" >
						<div class="col-sm-12 col-md-6">
							<el-form-item label="Nombre pondéré de logements:" prop="nombre">
								<el-input type="text" v-model="logements.nombre" placeholder="0" class="fixed-input"
										  :disabled="!isModify" @change="saveLogement"></el-input>
							</el-form-item>
						</div>
						<div class="col-sm-12 col-md-6">
							<el-form-item label="Montant des loyers théoriques avant RLS:" prop="montant">
								<el-input type="text" v-model="logements.montant" placeholder="0" class="fixed-input"
										  :disabled="!isModify" @change="saveLogement"></el-input>
							</el-form-item>
						</div>
					</div>
				</el-form>
			</el-collapse-item>
		</el-collapse>
        <el-tabs v-model="activeTab">
            <el-tab-pane v-for="(tab , i) in types" :label="tab" :name="`${parseInt(i)+1}`" :key="i">
                <el-collapse v-model="collapseList">
                    <el-collapse-item title="Réduction de loyer de solidarité" name="2" class="mt-3">
                        <el-row v-if="i === 0" v-loading="loading">
                            <el-col :span="5" style="padding-top: 55px;">
                                <div v-for="(nom, index) in solidariteNomList" :key="index" class="carousel-head">
                                    <p>{{ nom }}
                                        <el-tooltip
                                            v-if="index==0"
                                            class="item"
                                            effect="dark"
                                            content="S'applique sur les logements conventionnés (Case à cocher du formulaire Patrimoine)"
                                            placement="top"
                                        >
                                            <i class="el-icon-question"></i>
                                        </el-tooltip>
                                        <el-tooltip
                                            v-if="index==1"
                                            class="item"
                                            effect="dark"
                                            content="S'applique aux loyers des logements PLAI, PLUS et PLS"
                                            placement="top"
                                        >
                                            <i class="el-icon-question"></i>
                                        </el-tooltip>
                                    </p>
                                </div>
                            </el-col>
                            <el-col :span="19">
                                <periodique
                                    :anneeDeReference="anneeDeReference"
                                    v-model="solidariteForm"
                                    @hasError="hasPeriodiqueError"
                                    @onChange="solidaritePeriodicOnChange"></periodique>
                            </el-col>
                        </el-row>
                        <ApolloQuery
                                v-else
                                :query="require('../../../../../graphql/simulations/produits/loyers/loyers.gql')"
                                :variables="{
                                simulationId: simulationID,
                                type: i+1
                            }">
                            <template slot-scope="{ result:{ loading, error, data }, isLoading, query }">
                                <div v-if="error">Une erreur est survenue !</div>
                                <el-table
                                        class="fw"
                                        v-loading="isLoading === 1"
                                        :data="tableData(data, query)"
                                        style="width: 100%">
                                    <el-table-column column-key="nom" prop="nom" width="200"></el-table-column>
                                    <el-table-column v-if="i ===1" sortable column-key="tauxDevolution" prop="tauxDevolution" width="140" align="center" label="Taux d'évolution"></el-table-column>
                                    <el-table-column v-for="column in columns"
                                                     sortable
                                                     align="center"
                                                     :key="column.prop"
                                                     :prop="column.prop"
                                                     :label="column.label">
                                    </el-table-column>
                                    <el-table-column fixed="right" width="120" label="Actions">
                                        <template slot-scope="scope">
                                            <el-button v-if="!scope.row.noAction" :disabled="!isModify" type="primary" icon="el-icon-edit" circle @click="updateAutresLoyer(scope.$index, scope.row)"></el-button>
                                            <el-button v-if="scope.row.deletable" :disabled="!isModify" type="danger" icon="el-icon-delete" circle @click="deleteAutresLoyer(scope.$index, scope.row)"></el-button>
                                        </template>
                                    </el-table-column>
                                </el-table>
                                <el-row v-if="i === 1" class="d-flex justify-content-end my-3">
                                    <el-button type="primary" :disabled="!isModify" @click="showCreateLoyerModal">Créer un autre loyer</el-button>
                                </el-row>
                            </template>
                        </ApolloQuery>
                    </el-collapse-item>
                </el-collapse>
            </el-tab-pane>
        </el-tabs>

        <el-dialog
                :title="`${isEdit ? 'Modifier' : 'Créer'} un autre loyer`"
                :visible.sync="autresLoyerDialogVisible"
                :close-on-click-modal="false"
                width="70%">
            <el-row v-if="isEdit" class="form-slider text-center mb-5">
                <i class="el-icon-back font-weight-bold" @click="back"></i>
                <i class="el-icon-right font-weight-bold" @click="next"></i>
            </el-row>
            <el-form :model="autresLoyerForm" :rules="formRules" label-width="160px" ref="autresLoyerForm">
                <el-row type="flex" justify="space-around">
                    <el-col :span="12">
                        <el-form-item label="Nom:" prop="nom">
                            <el-input type="text" v-model="autresLoyerForm.nom" placeholder="Nom" class="text-input"></el-input>
                        </el-form-item>
                    </el-col>
                    <el-col :span="12">
                        <el-form-item label="Taux d'évolution:" prop="tauxDevolution">
                            <el-input type="text" placeholder="0" class="text-input"
                                      v-model="autresLoyerForm.tauxDevolution"
                                      @change="() => {autresLoyerForm = Object.assign({}, autresLoyerForm ,{'tauxDevolution' : mathInput(autresLoyerForm.tauxDevolution)})}"  ></el-input>
                        </el-form-item>
                    </el-col>
                </el-row>
                <periodique :anneeDeReference="anneeDeReference"
                            v-model="autresLoyerForm.periodiques"
                            @hasError="hasPeriodiqueError"></periodique>
            </el-form>
            <div slot="footer" class="dialog-footer">
                <el-button type="outline-primary" @click="autresLoyerDialogVisible = false">Retour</el-button>
                <el-button type="primary" @click="save('autresLoyerForm')">Valider</el-button>
            </div>
        </el-dialog>
    </div>
</template>

<script>

    import customValidator from '../../../../../utils/validation-rules'
    import {
        mathInput,
        initPeriodic,
        periodicFormatter,
    } from '../../../../../utils/inputs'
    import {updateDataByType} from "../../../../../utils/helpers";
    import Periodique from "../../../../../components/partials/Periodique";

    export default {
        name: "Loyers",
        components: { Periodique },
        data() {
            return {
                simulationID: null,
                anneeDeReference: null,
                activeTab: null,
                columns: [],
                collapseList: ['1', '2'],
                query: null,
                logements: {
                    id: null,
                    nombre: null,
                    montant: null
                },
                solidariteNomList: [
                    'Taux de RLS sur le patrimoine de référence',
                    'Taux de RLS sur les opérations nouvelles',
                    'Ajustement RLS en montant'
                ],
                dialogVisible: false,
                solidariteForm: {
                    patrimoine: initPeriodic(),
                    operation: initPeriodic(),
                    montant: initPeriodic(),
                },
                selectedIndex: null,
                selectedLoyerIndex: null,
                isEdit: false,
                inputError: null,
                autresLoyersList: [],
                autresLoyerDialogVisible: false,
                autresLoyerForm: null,
                loading: true,
                periodiqueHasError: false,
                formRules: {
                    nombre: customValidator.getRule('positiveInt'),
                    montant:  customValidator.getRule('positiveDouble'),
                    nom: [
                        customValidator.getRule('maxVarchar'),
                        customValidator.getRule('required')
                    ],
                    tauxDevolution: customValidator.getRule('taux')
                },
                types: [
                    'Réduction de loyer de solidarité',
                    'Autres loyers'
                ]
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

            this.activeTab = _.isNil(this.$route.query.tab) ? '1' : this.$route.query.tab;

        this.init(0);
        this.init(1);
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
            mathInput: mathInput,
            init(type) {
                this.loading = true;
                this.$apollo.query({
                    query: require('../../../../../graphql/simulations/produits/loyers/loyers.gql'),
                    fetchPolicy: 'no-cache',
                    variables: {
                        simulationId: this.simulationID,
                        type: type
                    }
                }).then((res) => {
                    if (res.data && res.data.loyers && res.data.loyers.items.length > 0) {
                        switch(type) {
                            case 0:
                                this.logements = {
                                    id: res.data.loyers.items[0].id,
                                    nombre: res.data.loyers.items[0].nombreLogements,
                                    montant: res.data.loyers.items[0].montantRls
                                };
                                break;
                            case 1:
                                this.solidariteList = res.data.loyers.items;
                                this.solidariteNomList = [];
                                res.data.loyers.items.forEach(item => {
                                    this.solidariteNomList.push(item.nom);
                                    let periodiques = [];
                                    item.loyerPeriodique.items.forEach(periodique => {
                                        periodiques[periodique.iteration - 1] = periodique.value;
                                    });
                                    switch(item.nom) {
                                        case 'Taux de RLS sur le patrimoine de référence':
                                            this.solidariteForm.patrimoine = periodiques;
                                            break;
                                        case 'Taux de RLS sur les opérations nouvelles':
                                            this.solidariteForm.operation = periodiques;
                                            break;
                                        case 'Ajustement RLS en montant':
                                            this.solidariteForm.montant = periodiques;
                                            break;
                                        default: break;
                                    }
                                });
                                this.loading = false;
                                break;
                            default: break;
                        }
                    }
                });
            },
            initForm() {
                this.autresLoyerForm = {
                    id: null,
                    nom: '',
                    tauxDevolution: 0,
                    periodiques: {periodiques: initPeriodic()}
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
                for (var i = 0; i < 50; i++) {
                    this.columns.push({
                        label: (parseInt(this.anneeDeReference) + i).toString(),
                        prop: `periodiques.periodiques[${i}]`
                    });
                }
            },
            tableData(data, query) {
                if (!_.isNil(data)) {
                    this.query = query;
                    let loyers = data.loyers.items.map(item => {
                        let periodiques = [];
                        item.loyerPeriodique.items.forEach(periodique => {
                            periodiques[periodique.iteration - 1] = periodique.value;
                        });
                        return {
                            id: item.id,
                            nom: item.nom,
                            tauxDevolution: item.tauxDevolution,
                            nombreLogements: item.nombreLogements,
                            montantRls: item.montantRls,
                            type: item.type,
                            deletable: item.deletable,
                            periodiques: {periodiques}
                        };
                    });

                    let sumPeriodique = [];
                    let sumTauxDevolution = 0;
                    loyers.forEach(loyer => {
                        loyer.periodiques.periodiques.forEach((item, index) => {
                            if (!sumPeriodique[index]) {
                                sumPeriodique[index] = 0;
                            }
                            sumPeriodique[index] += item;
                        });
                        sumTauxDevolution += loyer.tauxDevolution;
                    });
                    loyers.push({
                        nom: 'Total',
                        tauxDevolution: sumTauxDevolution,
                        periodiques: {periodiques: sumPeriodique},
                        noAction: true
                    });
                    this.autresLoyersList = loyers;

                    return loyers;
                } else {
                    return [];
                }
            },
            showCreateLoyerModal() {
                this.initForm();
                this.autresLoyerDialogVisible = true;
                this.isEdit = false;
            },
            updateAutresLoyer(index, row) {
                this.autresLoyerDialogVisible = true;
                this.autresLoyerForm = {...row};
                this.selectedLoyerIndex = index;
                this.isEdit = true;
            },
            deleteAutresLoyer(index, row) {
                this.$confirm('Êtes-vous sûr de vouloir supprimer ce loyer ?')
                    .then(_ => {
                        this.$apollo.mutate({
                            mutation: require('../../../../../graphql/simulations/produits/loyers/removeLoyer.gql'),
                            variables: {
                                loyerUUID: row.id,
                                simulationId: this.simulationID,
                            }
                        }).then(() => {
                            updateDataByType(this.query, this.simulationID, 2).then(() => {
                                this.$message({
                                    showClose: true,
                                    message: 'Ce loyer a bien été supprimé.',
                                    type: 'success'
                                });
                            })
                        }).catch(error => {
                            updateDataByType(this.query, this.simulationID, 2).then(() => {
                                this.$message({
                                    showClose: true,
                                    message: error.networkError.result,
                                    type: 'error',
                                });
                            })
                        });
                    })
                    .catch(_ => {});
            },
            save(formName) {
                this.$refs[formName].validate((valid) => {
                    if (valid && !this.periodiqueHasError) {
                        this.$apollo.mutate({
                            mutation: require('../../../../../graphql/simulations/produits/loyers/saveLoyer.gql'),
                            variables: {
                                loyer: {
                                    simulationId: this.simulationID,
                                    uuid: this.autresLoyerForm.id,
                                    nom: this.autresLoyerForm.nom,
                                    taux_devolution: this.autresLoyerForm.tauxDevolution,
                                    type: 2,
                                    periodique: JSON.stringify({periodique: this.autresLoyerForm.periodiques.periodiques})
                                }
                            }
                        }).then(() => {
                            this.autresLoyerDialogVisible = false;
                            updateDataByType(this.query, this.simulationID, 2).then(() => {
                                this.$message({
                                    showClose: true,
                                    message: 'Les valeurs ont été enregistrées.',
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
                        this.showError()
                    }
                });
            },
            saveLogement() {
                this.logements.nombre = mathInput(this.logements.nombre);
                this.logements.montant = mathInput(this.logements.montant);
                this.$refs['parameterForm'].validate((valid) => {
                    if (valid) {
                        this.$apollo.mutate({
                            mutation: require('../../../../../graphql/simulations/produits/loyers/saveLoyer.gql'),
                            variables: {
                                loyer: {
                                    simulationId: this.simulationID,
                                    uuid: this.logements.id,
                                    nombre_logements: this.logements.nombre,
                                    montant_rls: this.logements.montant,
                                    type: 0
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
                        this.showError()
                    }
                });
            },
            back() {
                if (this.selectedLoyerIndex > 0) {
                    this.selectedLoyerIndex--;
                    this.autresLoyerForm = {...this.autresLoyersList[this.selectedLoyerIndex]};
                }
            },
            next() {
                if (this.selectedLoyerIndex < (this.autresLoyersList.length - 2)) {
                    this.selectedLoyerIndex++;
                    this.autresLoyerForm = {...this.autresLoyersList[this.selectedLoyerIndex]};
                }
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
            changeTab () {
                this.$router.push({
                    path: 'loyers',
                    query: { tab: this.activeTab }
                });
            },
            exportProduitLoyer() {
                window.location.href = "/export-produit-loyer/" + this.simulationID;
            },
            onSuccessImport(res) {
                this.$toasted.success(res, {
                    theme: 'toasted-primary',
                    icon: 'check',
                    position: 'top-right',
                    duration: 5000
                });
                this.$refs.upload.clearFiles();
                this.init(0);
                this.init(1);
                updateDataByType(this.query, this.simulationID, 1);
                updateDataByType(this.query, this.simulationID, 2);
            },
            onErrorImport(error) {
                this.$toasted.error(JSON.parse(error.message), {
                    theme: 'toasted-primary',
                    icon: 'error',
                    position: 'top-right',
                    duration: 5000
                });
                this.$refs.upload.clearFiles();
            },
            solidaritePeriodicOnChange(type) {
                let solidariteForm;
                switch(type) {
                    case 'patrimoine':
                        solidariteForm = this.solidariteList.find(item => item.nom === 'Taux de RLS sur le patrimoine de référence'); break;
                    case 'operation':
                        solidariteForm = this.solidariteList.find(item => item.nom === 'Taux de RLS sur les opérations nouvelles'); break;
                    case 'montant':
                        solidariteForm = this.solidariteList.find(item => item.nom === 'Ajustement RLS en montant'); break;
                    default: break;
                }
                if (!this.periodiqueHasError) {
                    this.$apollo.mutate({
                        mutation: require('../../../../../graphql/simulations/produits/loyers/saveLoyer.gql'),
                        variables: {
                            loyer: {
                                simulationId: this.simulationID,
                                uuid: solidariteForm.id,
                                nom: solidariteForm.nom,
                                type: 1,
                                periodique: JSON.stringify({periodique: this.solidariteForm[type]})
                            }
                        }
                    }).then(() => {
                        this.init(1);
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
            hasPeriodiqueError(error) {
                this.periodiqueHasError = error;
            },
        },
        watch: {
            'activeTab' (newVal, oldVal) {
                if (oldVal) {
                    this.changeTab();
                }
            }
        }
    }
</script>

<style>
    .produits-loyers .el-collapse-item__header {
        padding-left: 15px;
        font-weight: bold;
        font-size: 15px;
    }
    .produits-loyers .el-collapse-item__content {
        padding-bottom: 0;
    }
    .produits-loyers .el-collapse-item__header i {
        font-weight: bold;
    }
    .produits-loyers .fixed-input {
        width: 80px;
    }
    .produits-loyers .text-input {
        width: 235px;
    }
    .produits-loyers .el-form-item__label {
        line-height: 40px;
    }
    .produits-loyers .carousel-head {
        height: 50px;
    }
    .produits-loyers .form-slider i {
        font-size: 25px;
        margin-left: 50px;
        cursor: pointer;
    }
    .produits-loyers .el-input.is-disabled .el-input__inner {
        color: black;
    }
    .produits-loyers .el-tooltip.el-icon-question {
        font-size: 25px;
        color: #2491eb;
        vertical-align: middle;
    }
</style>
