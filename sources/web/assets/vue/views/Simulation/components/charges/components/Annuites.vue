<template>
    <div class="annuites admin-content-wrap">
		<div class="row">
        <h1 class="admin-content-title">Annuités</h1>
            <el-col :span="2" :offset="2" class="pr-2">
                <el-upload
                        multiple
                        ref="upload"
                        :action="'/import-charges-annuite/'+ simulationID"
                        :limit="10"
                        :on-success="onSuccess"
                        :on-error="onError">
                    <el-button size="small" type="primary">Importer</el-button>
                </el-upload>
            </el-col>
            <el-col :span="2" class="export-button">
                <el-button type="success" @click="exportChargesAnnuite">Exporter</el-button>
            </el-col>
		</div>
        <el-row>
            <el-tabs v-model="activeTab">
                <el-tab-pane label="Options de calcul des annuités d’emprunt" name="1">
                    <el-form :model="optionsCalcul" label-width="300px" class="pb-3 pt-3" ref="optionsCalculForm" :rules="formRules" >
                        <el-col :span="5">
                            <p class="m-0">Capital restant dû sur le patrimoine de référence</p>
                        </el-col>
                        <el-col :span="19" class="el-form-item-param">
                            <el-form-item prop="capitalRestant">
                                <el-input type="text" placeholder="0" class="text-input"
                                          v-model="optionsCalcul.capitalRestant"
                                          :disabled="!acneId"
                                          @change="saveParameters('optionsCalculForm', 1)"></el-input>
                            </el-form-item>
                        </el-col>
                    </el-form>
                    <p class="mt-5"><b>Options de calcul des annuités d'emprunt</b></p>
                    <el-checkbox v-model="optionsCalcul.priseIcneAcne" class="mt-3" @change="changePriseIcneAcne">Prise en compte des ICNE/ACNE</el-checkbox>
                    <el-row class="mt-2" v-loading="loading">
                        <el-col :span="5" style="padding-top: 65px;">
                            <div class="carousel-head">
                                <p>ACNE du patrimoine de référence</p>
                            </div>
                            <div class="carousel-head">
                                <p>ICNE du patrimoine de référence</p>
                            </div>
                        </el-col>
                        <el-col :span="19">
                            <el-carousel :autoplay="false" :loop="false" trigger="click" height="215px" arrow="always">
                                <el-carousel-item v-for="item in 5" :key="item" >
                                    <div class="item-wrapper">
                                        <div v-for="key in 12" :key="key" class="period-item" >
                                            <span class="input-wrapper" :class="{'is-error':!isFloat(acne[getIteration(item, key)])}">
                                                <p v-if="getIteration(item, key) < 50" class="text-center">{{parseInt(anneeDeReference) + getIteration(item, key)}}
                                                    <span>*</span>
                                                </p>
                                                <p v-else class="text-center">&nbsp;</p>
                                                <el-input placeholder="0"
                                                          :name="`acne_${parseInt(anneeDeReference) + getIteration(item, key)}`"
                                                          :disabled="isDisabledOptionsCalculs || getIteration(item, key) > 49"
                                                          v-model="acne[getIteration(item, key)]"
                                                          @change="saveForm('optionsCalculForm',1)"></el-input>
                                            </span>
                                            <span class="input-wrapper" :class="{'is-error':!isFloat(icne[getIteration(item, key)])}">
                                                <el-input placeholder="0"
                                                          :name="`icne_${parseInt(anneeDeReference) + getIteration(item, key)}`"
                                                          :disabled="isDisabledOptionsCalculs || getIteration(item, key) > 49"
                                                          v-model="icne[getIteration(item, key)]"
                                                          @change="saveForm('optionsCalculForm',2)"></el-input>
                                            </span>
                                        </div>
                                        <div class="period-item">
                                            <p>&nbsp;</p>
                                            <div class="reset-control">
                                                <i class="el-icon-refresh" @click="resetPeriodique(1)"></i>
                                            </div>
                                            <div class="reset-control">
                                                <i class="el-icon-refresh" @click="resetPeriodique(2)"></i>
                                            </div>
                                        </div>
                                    </div>
                                </el-carousel-item>
                            </el-carousel>
                        </el-col>
                    </el-row>
                </el-tab-pane>
                <el-tab-pane label="Emprunts locatifs patrimoine de référence" name="2">
                    <el-collapse v-model="collapseList">
                        <el-collapse-item title="Récapitulatif des annuités locative du patrimoine de référence" name="1">
                            <el-table
                                    v-loading="loading"
                                    :data="recapList"
                                    style="width: 100%">
                                <el-table-column sortable fixed column-key="type" prop="type" width="430" label="Annuités des emprunts locatifs sur le patrimoine de référence"></el-table-column>
                                <el-table-column v-for="column in columns"
                                                 sortable
                                                 align="center"
                                                 :key="column.prop"
                                                 :prop="column.prop"
                                                 :label="column.label">
                                </el-table-column>
                            </el-table>
                        </el-collapse-item>
                        <el-collapse-item title="Détails des annuités des emprunts locatifs" name="2" class="mt-3">
                            <ApolloQuery
                                    :query="require('../../../../../graphql/simulations/charges/annuites/annuites.gql')"
                                    :variables="{
                                    simulationId: simulationID
                                }">
                                <template slot-scope="{ result:{ loading, error, data }, isLoading , query}">
                                    <div v-if="error">Une erreur est survenue !</div>
                                    <el-table
                                            v-loading="isLoading === 1"
                                            :data="tableData(data, query, 1)"
                                            :default-sort="{prop: 'numero'}"
                                            style="width: 100%">
                                        <el-table-column sortable fixed :sort-method="sortTable" label="N°" column-key="numero" prop="numero"></el-table-column>
                                        <el-table-column sortable fixed column-key="libelle" prop="libelle" width="200" label="Libellé"></el-table-column>
                                        <el-table-column sortable fixed column-key="nature" prop="nature" width="150" label="Nature">
                                            <template slot-scope="scope">
                                                <span v-if="scope.row.nature === 0">Remboursement en capital</span>
                                                <span v-else>Charges d'intérêts</span>
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
                                                <el-button type="primary" icon="el-icon-edit" circle @click="handleEdit(scope.$index, scope.row)"></el-button>
                                                <el-button v-if="scope.row.deletable" type="danger" icon="el-icon-delete" circle @click="handleDelete(scope.$index, scope.row)"></el-button>
                                            </template>
                                        </el-table-column>
                                    </el-table>
                                    <el-dialog
                                            :title="`${isEdit ? 'Modifier' : 'Créer' } une annuité des emprunts locatifs`"
                                            :visible.sync="dialogVisible"
                                            :close-on-click-modal="false"
                                            width="70%">
                                        <el-row v-if="isEdit" class="form-slider text-center mb-5">
                                            <i class="el-icon-back font-weight-bold" @click="back"></i>
                                            <i class="el-icon-right font-weight-bold" @click="next"></i>
                                        </el-row>
                                        <el-form :model="form" :rules="dialogVisible ? formRules : null" label-width="160px" ref="annuiteForm">
                                            <el-form-item label="Numéro:" prop="numero">
                                                <el-input type="text" v-model="form.numero" placeholder="Numéro" class="fixed-input"></el-input>
                                            </el-form-item>
                                            <el-row type="flex" justify="space-around">
                                                <el-col :span="12">
                                                    <el-form-item label="Libellé:" prop="libelle">
                                                        <el-input type="text" v-model="form.libelle" placeholder="Libellé" class="fixed-input" :disabled="!form.deletable"></el-input>
                                                    </el-form-item>
                                                </el-col>
                                                <el-col :span="12">
                                                    <el-form-item label="Nature:" prop="nature">
                                                        <el-select v-model="form.nature" placeholder="Sélectionner">
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
                                            <el-button type="primary" @click="save('annuiteForm', query)">Valider</el-button>
                                        </div>
                                    </el-dialog>
                                </template>
                            </ApolloQuery>

                            <el-row class="d-flex justify-content-end my-3">
                                <el-button type="primary" @click="showCreateModal">Créer une annuité des emprunts locatifs</el-button>
                            </el-row>

                        </el-collapse-item>
                    </el-collapse>
                </el-tab-pane>
                <el-tab-pane label="Autres emprunts " name="3">
                    <p><b>Autres emprunts long terme de structure ou non affectés</b></p>
                    <el-form :model="autresEmprunts" label-width="300px" class="pb-3 pt-3" ref="autresEmpruntsForm" :rules="formRules" >
                        <el-row class="mt-3">
                            <el-col :span="5">
                                <p class="m-0">Capital restant dû à la fin de l'année de référence</p>
                            </el-col>
                            <el-col :span="19" class="el-form-item-param">
                                <el-form-item prop="capitalRestant">
                                    <el-input type="text" placeholder="0" class="text-input"
                                              v-model="autresEmprunts.capitalRestant"
                                              @change="saveParameters('autresEmpruntsForm',3)"
                                              :disabled="!icneId"></el-input>
                                </el-form-item>
                            </el-col>
                        </el-row>
                    </el-form>
                    <el-row class="mt-4"  v-loading="loading">
                        <el-col :span="5" style="padding-top: 65px;">
                            <div class="carousel-head">
                                <p>Remboursement en capital des autres emprunts</p>
                            </div>
                            <div class="carousel-head">
                                <p>Charges d'intérêt des autres emprunts</p>
                            </div>
                        </el-col>
                        <el-col :span="19">
                            <el-carousel :autoplay="false" :loop="false" trigger="click" height="215px" arrow="always">
                                <el-carousel-item v-for="item in 5" :key="item">
                                    <div class="item-wrapper">
                                        <div v-for="key in 12" :key="key" class="period-item">
                                            <span class="input-wrapper" :class="{'is-error':!isFloat(remboursementEmprunts[getIteration(item, key)])}">
                                                <p v-if="getIteration(item, key) < 50" class="text-center">{{parseInt(anneeDeReference) + getIteration(item, key)}}
                                                    <span>*</span>
                                                </p>
                                                <p v-else class="text-center">&nbsp;</p>
                                                <el-input   placeholder="0"
                                                            :name="`remboursement_${parseInt(anneeDeReference) + getIteration(item, key)}`"
                                                            v-model="remboursementEmprunts[getIteration(item, key)]"
                                                            :disabled="getIteration(item, key) > 49"
                                                            @change="saveForm('autresEmpruntsForm',3)"></el-input>
                                            </span>
                                            <span class="input-wrapper" :class="{'is-error':!isFloat(chargesEmprunts[getIteration(item, key)])}">
                                                <el-input   placeholder="0"
                                                            :name="`charges_${parseInt(anneeDeReference) + getIteration(item, key)}`"
                                                            v-model="chargesEmprunts[getIteration(item, key)]"
                                                            :disabled="getIteration(item, key) > 49"
                                                            @change="saveForm('autresEmpruntsForm',4)"></el-input>
                                            </span>
                                        </div>
                                        <div class="period-item">
                                            <p>&nbsp;</p>
                                            <div class="reset-control">
                                                <i class="el-icon-refresh" @click="resetPeriodique(3)"></i>
                                            </div>
                                            <div class="reset-control">
                                                <i class="el-icon-refresh" @click="resetPeriodique(4)"></i>
                                            </div>
                                        </div>
                                    </div>
                                </el-carousel-item>
                            </el-carousel>
                        </el-col>
                    </el-row>
                </el-tab-pane>
            </el-tabs>
        </el-row>
    </div>
</template>

<script>
    import customValidator from '../../../../../utils/validation-rules'
    import {
        isFloat,
        mathInput,
        initPeriodic,
        repeatPeriodic,
        checkPeriodic,
        checkAllPeriodics,
        periodicFormatter} from '../../../../../utils/inputs'
    import { updateData } from "../../../../../utils/helpers";
    import Periodique from "../../../../../components/partials/Periodique";
    export default {
        name:"Annuites",
        components: {Periodique},
        data() {
            return {
                simulationID: null,
                anneeDeReference: null,
                activeTab: null,
                columns: [],
                loading: true,
                optionsCalcul: null,
                autresEmprunts: null,
                acne: [],
                icne: [],
                remboursementEmprunts: [],
                chargesEmprunts: [],
                acneId: null,
                icneId: null,
                remboursementEmpruntsId: null,
                collapseList: ['2'],
                recapList: [],
                annuitesList: [],
                selectedIndex: null,
                dialogVisible: false,
                form: null,
                isEdit: false,
                isDisabledOptionsCalculs: true,
                natureOptions: [{
                    value: 0,
                    label: 'Remboursement en capital'
                }, {
                    value: 1,
                    label: 'Charges d\'intérêt'
                }],
                formRules: {
                    numero: customValidator.getPreset('number.positiveInt'),
                    libelle: [
                        customValidator.getRule('requiredNoWhitespaces'),
                        customValidator.getRule('maxVarchar'),
                    ],
                    capitalRestant: customValidator.getRule(('positiveDouble')),
                    nature: customValidator.getRule('required')
                },
                query: null
            }
        },
        created () {
            const simulationID = _.isNil(this.$route.params.id) ? null : this.$route.params.id;
            if (_.isNil(simulationID)) {
                return;
            }
            this.simulationID = simulationID;

            this.activeTab = _.isNil(this.$route.query.tab) ? '1' : this.$route.query.tab;

            this.init();
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
            isFloat: isFloat,
            periodicFormatter: periodicFormatter,
            init() {
                this.optionsCalcul = {
                    capitalRestant: null,
                    priseIcneAcne: false,
                };

                this.autresEmprunts = {
                    capitalRestant: null,
                };

                this.acne = initPeriodic();
                this.icne = initPeriodic();
                this.remboursementEmprunts = initPeriodic();
                this.chargesEmprunts = initPeriodic();

                this.recapList = [
                    {type: 'Remboursement en capital des emprunts locatifs', periodiques: initPeriodic()},
                    {type: 'Charges d\'intérêts des emprunts locatifs', periodiques: initPeriodic()},
                    {type: 'Total Annuités des emprunts locatifs sur le patrimoine de référence', periodiques: initPeriodic()}
                ];

                this.initForm();
                this.getAnnuites(1);
                this.getAnnuites(2);
                this.getAnnuites(3);
                this.getAnnuites(4);
            },
            initForm() {
                this.form = {
                    id: null,
                    libelle: '',
                    nature: null,
                    deletable: true,
                    periodiques: {
                        items: initPeriodic()
                    }
                };
            },
            getIteration(item, key) {
                return ((item - 1) * 11) + key - 1;
            },
            getAnnuites(type) {
                this.loading = true;
                this.$apollo.query({
                    query: require('../../../../../graphql/simulations/charges/annuites/annuites.gql'),
                    fetchPolicy: 'no-cache',
                    variables: {
                        simulationId: this.simulationID,
                        type: type
                    }
                }).then((res) => {
                    if (res.data && res.data.annuites && res.data.annuites.items.length > 0) {
                        const annuites = res.data.annuites.items[0].annuitePeriodique.items;

                        switch(type) {
                            case 1:
                                this.optionsCalcul = {
                                    capitalRestant: res.data.annuites.items[0].capitalRestantPatrimoine,
                                    priseIcneAcne: res.data.annuites.items[0].priseIcneAcne,
                                };
                                this.isDisabledOptionsCalculs = !this.optionsCalcul.priseIcneAcne;
                                this.acneId = res.data.annuites.items[0].id;
                                this.acne = annuites.map(item => item.value);
                                break;
                            case 2:
                                this.icneId = res.data.annuites.items[0].id;
                                this.icne = annuites.map(item => item.value);
                                break;
                            case 3:
                                this.autresEmprunts = {
                                    capitalRestant: res.data.annuites.items[0].capitalRestantPatrimoine,
                                };
                                this.remboursementEmpruntsId = res.data.annuites.items[0].id;
                                this.remboursementEmprunts = annuites.map(item => item.value);
                                break;
                            case 4:
                                this.chargesEmpruntsId = res.data.annuites.items[0].id;
                                this.chargesEmprunts = annuites.map(item => item.value);
                                break;
                            default: break;
                        }

                        this.loading = false;
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
                        prop:  `periodiques.items[${i}]`
                    });
                }
            },
            tableData(data, query) {
                if (!_.isNil(data)) {
                    this.query = query;
                    const annuites = data.annuites.items.map(item => {
                        let periodiques = [];
                        item.annuitePeriodique.items.forEach(periodique => {
                            periodiques[periodique.iteration - 1] = periodique.value;
                        });
                        return {
                            id: item.id,
                            numero: item.numero,
                            libelle: item.libelle,
                            nature: item.nature,
                            type: item.type,
                            deletable: item.deletable,
                            periodiques: {
                                items: periodiques
                            }
                        };
                    });

                    this.annuitesList = annuites;

                    // Calculate the recap list.
                    this.getRecapList(annuites);
                    this.query = query;

                    return annuites;
                } else {
                    return [];
                }
            },
            sortTable (a, b){
                return parseInt(a.numero, 10) > parseInt(b.numero, 10) ? 1 : -1
            },
            getRecapList(data) {
                for (let i = 0; i < 5; i++) {
                    let recapList = [];
                    let groups = data.filter(record => record.nature === i);
                    if (groups.length > 0) {
                        groups.forEach(group => {
                            group.periodiques.items.forEach((item, index) => {
                                if (!recapList[index]) {
                                    recapList[index] = 0;
                                }
                                recapList[index] += item;
                            });
                        });
                        this.recapList[i].periodiques.items = recapList;
                    }
                }

                // Get total sum.
                let recapList = [];
                data.forEach(group => {
                    group.periodiques.items.forEach((item, index) => {
                        if (!recapList[index]) {
                            recapList[index] = 0;
                        }
                        recapList[index] += item;
                    });
                });
                this.recapList[2].periodiques.items = recapList;
            },
            changePriseIcneAcne(value) {
                this.isDisabledOptionsCalculs = !value;
                this.acne = initPeriodic();
                this.icne = initPeriodic();
                this.saveAnnuites(1);
                this.saveAnnuites(2);
            },
            checkAllPeriodique (type) {
                let newPeriodic = [];
                switch(type) {
                    case 1:
                        newPeriodic = periodicFormatter(this.acne);
                        this.acne = [];
                        this.acne = newPeriodic;
                        return checkPeriodic(this.acne);
                    case 2:
                        newPeriodic = periodicFormatter(this.icne);
                        this.icne = [];
                        this.icne = newPeriodic;
                        return checkPeriodic(this.icne);
                    case 3:
                        newPeriodic = periodicFormatter(this.remboursementEmprunts);
                        this.remboursementEmprunts = [];
                        this.remboursementEmprunts =newPeriodic;
                        return checkPeriodic(this.remboursementEmprunts);
                    case 4:
                        newPeriodic = periodicFormatter(this.chargesEmprunts);
                        this.chargesEmprunts = [];
                        this.chargesEmprunts =newPeriodic;
                        return checkPeriodic(this.chargesEmprunts);
                    default:
                        return false;
                }
            },
            saveForm(formName, type) {
                this.$refs[formName].validate((valid) => {
                    if (valid) {
                        this.saveAnnuites(type)
                    } else {
                        this.showError();
                    }
                })
            },
            saveParameters(formName, type) {
                this.optionsCalcul.capitalRestant = mathInput(this.optionsCalcul.capitalRestant);
                this.autresEmprunts.capitalRestant = mathInput(this.autresEmprunts.capitalRestant);
                this.saveForm(formName, type);
            },
            saveAnnuites(type) {
                let capitalRestant = null;
                let priseIcneAcne = null;
                let periodique = null;
                let libelle = '';
                let uuid = null;

                if (this.checkAllPeriodique(type))
                {
                    switch(type) {
                        case 1:
                            uuid = this.acneId;
                            capitalRestant = this.optionsCalcul.capitalRestant;
                            priseIcneAcne = this.optionsCalcul.priseIcneAcne;
                            libelle = 'ACNE du patrimoine de référence';
                            periodique = this.acne;
                            break;
                        case 2:
                            uuid = this.icneId;
                            libelle = 'ICNE du patrimoine de référence';
                            periodique = this.icne;
                            break;
                        case 3:
                            uuid = this.remboursementEmpruntsId;
                            capitalRestant = this.autresEmprunts.capitalRestant;
                            periodique = this.remboursementEmprunts;
                            libelle = 'Remboursement en capital des autres emprunts';
                            break;
                        case 4:
                            uuid = this.chargesEmpruntsId;
                            periodique = this.chargesEmprunts;
                            libelle = 'Charges d\'intérét des autres emprunts';
                            break;
                        default: break;
                    }

                    this.$apollo.mutate({
                        mutation: require('../../../../../graphql/simulations/charges/annuites/saveAnnuite.gql'),
                        variables: {
                            annuite: {
                                simulationId: this.simulationID,
                                numero: null,
                                uuid: uuid,
                                capital_restant_patrimoine: capitalRestant,
                                prise_icne_acne: priseIcneAcne,
                                libelle: libelle,
                                nature: null,
                                type: type,
                                periodique: JSON.stringify({periodique: periodique})
                            }
                        }
                    }).then(() => {
                        updateData(this.query, this.simulationID).then(() => {
                            this.$message({
                                showClose: true,
                                message: 'Les valeurs ont été enregistrées.',
                                type: 'success'
                            });
                        });
                    }).catch(error => {
                        updateData(this.query, this.simulationID).then(() => {
                            this.$message({
                                showClose: true,
                                message: error.networkError.result,
                                type: 'error',
                            });
                        });
                    });
                }
                else {
                    this.showError()
                }
            },
            save(formName) {
                this.$refs[formName].validate((valid) => {
                    if (valid && checkAllPeriodics(this.form.periodiques)) {
                        this.$apollo.mutate({
                            mutation: require('../../../../../graphql/simulations/charges/annuites/saveAnnuite.gql'),
                            variables: {
                                annuite: {
                                    simulationId: this.simulationID,
                                    numero: parseInt(this.form.numero),
                                    uuid: this.form.id,
                                    libelle: this.form.libelle,
                                    nature: this.form.nature,
                                    type: 0,
                                    periodique: JSON.stringify({periodique: this.form.periodiques.items})
                                }
                            }
                        }).then(() => {
                            this.dialogVisible = false;
                            updateData(this.query, this.simulationID).then(() => {
                                this.$message({
                                    message: 'L\'annuité a bien été enregistrée.',
                                    type: 'success',
                                });
                            });
                        }).catch(error => {
                            updateData(this.query, this.simulationID).then(() => {
                                this.$message({
                                    showClose: true,
                                    message: error.networkError.result,
                                    type: 'error',
                                });
                            })
                        });
                    } else {
                        this.showError()
                    }
                });
            },
            showCreateModal() {
                this.initForm();
                this.dialogVisible = true;
                this.isEdit = false;
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
            handleEdit(index, row) {
                this.dialogVisible = true;
                this.form = row;
                this.selectedIndex = index;
                this.isEdit = true;
            },
            handleDelete(index, row) {
                this.$confirm('Êtes-vous sûr de vouloir supprimer cette charge ?')
                    .then(() => {
                        this.$apollo.mutate({
                            mutation: require('../../../../../graphql/simulations/charges/annuites/removeAnnuite.gql'),
                            variables: {
                                annuiteUUID: row.id,
                                simulationId: this.simulationID
                            }
                        }).then(() => {
                            updateData(this.query, this.simulationID).then(() => {
                                this.$message({
                                    message: 'L\'annuité a bien été supprimée.',
                                    type: 'success',
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
                    });
            },
            resetPeriodique(type) {
                let newPeriodique = [];
                switch(type) {
                    case 0:
                        newPeriodique = repeatPeriodic(this.form.periodiques);
                        this.form.periodiques = [];
                        this.form.periodiques = newPeriodique;
                        break;
                    case 1:
                        newPeriodique = repeatPeriodic(this.acne);
                        this.acne = [];
                        this.acne = newPeriodique;
                        this.saveAnnuites(type);
                        break;
                    case 2:
                        newPeriodique = repeatPeriodic(this.icne);
                        this.icne = [];
                        this.icne = newPeriodique;
                        this.saveAnnuites(type);
                        break;
                    case 3:
                        newPeriodique = repeatPeriodic(this.remboursementEmprunts);
                        this.remboursementEmprunts = [];
                        this.remboursementEmprunts = newPeriodique;
                        this.saveAnnuites(type);
                        break;
                    case 4:
                        newPeriodique = repeatPeriodic(this.chargesEmprunts);
                        this.chargesEmprunts = [];
                        this.chargesEmprunts = newPeriodique;
                        this.saveAnnuites(type);
                        break;
                    default: break;
                }
            },
            back() {
                if (this.selectedIndex > 0) {
                    this.selectedIndex--;
                    this.form = this.annuitesList[this.selectedIndex];
                }
            },
            next() {
                if (this.selectedIndex < (this.annuitesList.length - 1)) {
                    this.selectedIndex++;
                    this.form = this.annuitesList[this.selectedIndex];
                }
            },
            periodicOnChange(type) {
                let newPeriodics = this.form.periodiques[type];
                this.form.periodiques[type] = [];
                this.form.periodiques[type] = periodicFormatter(newPeriodics);
            },
            changeTab () {
                this.$router.push({
                    path: 'annuites',
                    query: { tab: this.activeTab }
                });
            },
            exportChargesAnnuite() {
                window.location.href = "/export-charges-annuite/" + this.simulationID;
            },
            onSuccess(res) {
                this.$toasted.success(res, {
                    theme: 'toasted-primary',
                    icon: 'check',
                    duration: 5000
                });
                position: 'top-right',

                this.$refs.upload.clearFiles();
                this.getAnnuites(1);
                this.getAnnuites(2);
                this.getAnnuites(3);
                this.getAnnuites(4);
                updateData(this.query, this.simulationID);

            },
            onError(error) {
                this.$toasted.error(JSON.parse(error.message), {
                    icon: 'error',
                    theme: 'toasted-primary',
                    position: 'top-right',
                });
                duration: 5000
                this.$refs.upload.clearFiles();

            }
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

<style type="text/css">
    .annuites {
        font-size: 14px;
    }
    .annuites .text-input {
        width: 150px;
        margin-left: 60px;
    }
    .annuites .el-button--small {
        font-weight: 500;
        padding: 0 15px;
        height: 40px;
        font-size: 14px;
        border-radius: 4px;
        text-align: center;
        height: 40px;
    }
    .annuites .fixed-input {
        width: 235px;
    }
    .annuites .el-collapse-item__header {
        padding-left: 15px;
        font-weight: bold;
        font-size: 15px;
    }
    .annuites .el-collapse-item__content {
        padding-bottom: 0;
    }
    .annuites .el-collapse-item__header i {
        font-weight: bold;
    }
    .annuites .item-wrapper {
        margin: 20px 60px;
        display: table;
    }
    .annuites .period-item {
        width: 8.33%;
        font-size: 12px;
        padding: 0 5px;
        display: table-cell;
        text-align: center;
    }
    .annuites .el-form-item-param .el-form-item__content {
        margin-left: 0 !important;
    }
    .annuites .el-form-item-param .el-form-item__content .el-form-item__error {
        margin-left: 60px;
    }
    .annuites .input-wrapper.is-error input {
        border-color: #f56c6c;
    }
    .annuites .input-wrapper span {
        display: none;
        color:  #f56c6c;
    }
    .annuites .input-wrapper input {
        border-radius: 0;
    }
    .annuites .input-wrapper.is-error span {
        display: contents;
    }
    .annuites .period-item .el-input{
        margin-top: 10px;
    }
    .annuites .period-item .el-input__inner{
        padding: 0 3px;
        text-align: right;
    }
    .annuites .el-carousel__button {
        background-color: #2591eb;
        pointer-events: none;
    }
    .annuites .el-carousel__arrow i {
        color: #2591eb;
    }
    .annuites .carousel-head {
        height: 50px;
    }
    .annuites .reset-control {
        width: 40px;
        height: 50px;
        padding-top: 20px;
    }
    .annuites .el-icon-refresh {
        color: #2591eb;
        font-size: 20px;
        font-weight: bold;
        cursor: pointer;
    }
    .annuites .form-slider i {
        font-size: 25px;
        margin-left: 50px;
        cursor: pointer;
    }
    .annuites .el-form-item__label {
        line-height: 40px;
    }
    .capitalRestant .el-form-item__error {
        margin-left: 60px
    }
</style>
