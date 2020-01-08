<template>
    <div class="accession-psla-nonidentifiees">
        <ApolloQuery
                :query="require('../../../../../../../graphql/simulations/accession/psla/pslas.gql')"
                :variables="{
                simulationId: simulationID,
                type: 1
            }">
            <template slot-scope="{ result:{ loading, error, data }, isLoading, query }">
                <div v-if="error">Une erreur est survenue !</div>
                <el-table
                    v-loading="isLoading"
                    :data="tableData(data, query)"
                    style="width: 100%">
                    <el-table-column sortable column-key="numero" prop="numero" label="N°">
                        <template slot="header">
                            <span title="N°">N°</span>
                        </template>
                    </el-table-column>
                    <el-table-column sortable column-key="nom" prop="nom" min-width="150" label="Nom de catégorie">
                        <template slot="header">
                            <span title="Nom de catégorie">Nom de catégorie</span>
                        </template>
                    </el-table-column>
                    <el-table-column sortable column-key="directSci" prop="directSci" min-width="120" label="Direct / SCI">
                        <template slot="header">
                            <span title="Direct / SC">Direct / SC</span>
                        </template>
                    </el-table-column>
                    <el-table-column sortable column-key="detention" prop="detention" min-width="150" label="% de détention">
                        <template slot="header">
                            <span title="% de détention">% de détention</span>
                        </template>
                    </el-table-column>
                    <el-table-column sortable column-key="prixVente" prop="prixVente" min-width="180" label="Prix de vente en k€/logt/lot en K€">
                        <template slot="header">
                            <span title="Prix de vente en k€/logt/lot en K€">Prix de vente en k€/logt/lot en K€</span>
                        </template>
                    </el-table-column>
                    <el-table-column sortable column-key="tauxEvolution" prop="tauxEvolution" min-width="180" label="Taux d’évolution du loyer">
                        <template slot="header">
                            <span title="Taux d’évolution du loyer">Taux d’évolution du loyer</span>
                        </template>
                    </el-table-column>
                    <el-table-column sortable column-key="tauxBrute" prop="tauxBrute" min-width="170" label="Taux marge brute">
                        <template slot="header">
                            <span title="Taux marge brute">Taux marge brute</span>
                        </template>
                    </el-table-column>
                    <el-table-column sortable column-key="dureePhase" prop="dureePhase" min-width="170" label="Durée de la phase locative">
                        <template slot="header">
                            <span title="Durée de la phase locative">Durée de la phase locative</span>
                        </template>
                    </el-table-column>
                    <el-table-column sortable column-key="loyerMensuel" prop="loyerMensuel" min-width="170" label="Loyer mensuel moyen en e/logt">
                        <template slot="header">
                            <span title="Loyer mensuel moyen en e/logt">Loyer mensuel moyen en e/logt</span>
                        </template>
                    </el-table-column>
                    <el-table-column sortable column-key="dureeConstruction" prop="dureeConstruction" min-width="200" label="Durée moyenne de la période de construction (année)">
                        <template slot="header">
                            <span title="Durée moyenne de la période de construction (année)">Durée moyenne de la période de construction (année)</span>
                        </template>
                    </el-table-column>
                    <el-table-column sortable column-key="total" prop="total" label="Total">
                        <template slot="header">
                            <span title="Total">Total</span>
                        </template>
                    </el-table-column>
                    <el-table-column v-for="column in columns"
                        sortable
                        min-width="120"
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

        <el-row class="d-flex justify-content-end my-3">
            <el-button type="primary" :disabled="!isModify" @click="showCreateModal">Créer un PSLA non Identifié</el-button>
        </el-row>

        <el-dialog
            title="Création/Modification de PSLA non Identifié"
            :visible.sync="dialogVisible"
            :close-on-click-modal="false"
            width="75%">
            <el-row v-if="isEdit" class="form-slider text-center mb-4">
                <i class="el-icon-back font-weight-bold" @click="back"></i>
                <i class="el-icon-right font-weight-bold" @click="next"></i>
            </el-row>
            <el-form label-position="left" :model="form" :rules="formRules" label-width="180px" ref="pslaForm">
                <el-tabs v-model="activeTab" type="card">
                    <el-tab-pane label="Caractéristiques générales" name="1">
                        <el-collapse v-model="collapseList">
                            <el-collapse-item title="Données Générales" name="1">
                                <el-row :gutter="20">
                                    <el-col :span="8">
                                        <el-form-item label="N°" prop="numero">
                                            <el-input type="text" v-model="form.numero" placeholder="N°" readonly></el-input>
                                        </el-form-item>
                                        <el-form-item label="Nom de catégorie" prop="nom">
                                            <el-input type="text" v-model="form.nom" placeholder="Nom de catégorie"></el-input>
                                        </el-form-item>
                                    </el-col>
                                    <el-col :span="8">
                                        <el-form-item label="% de détention" prop="detention">
                                            <el-input type="text" v-model="form.detention" placeholder="0"
                                                      @change="formatInput('detention')">
                                                <template slot="append">%</template>
                                            </el-input>
                                        </el-form-item>
                                        <el-form-item label="DIRECT / SCI" prop="directSci">
                                            <el-select v-model="form.directSci">
                                                <el-option v-for="item in directScis"
                                                    :key="item"
                                                    :label="item"
                                                    :value="item"></el-option>
                                            </el-select>
                                        </el-form-item>
                                    </el-col>
                                </el-row>
                                <el-row :gutter="20">
                                    <el-col :span="6">
                                        <el-form-item label="Durée de phase locative (année)" prop="dureePhase">
                                            <el-select v-model="form.dureePhase">
                                                <el-option v-for="item in 3"
                                                    :key="item"
                                                    :label="item"
                                                    :value="item"></el-option>
                                            </el-select>
                                        </el-form-item>
                                    </el-col>
                                    <el-col :span="6">
                                        <el-form-item label="Durée de construction (année)" prop="dureeConstruction">
                                            <el-input type="text" v-model="form.dureeConstruction" placeholder="0"
                                                      @change="formatInput('dureeConstruction')"></el-input>
                                        </el-form-item>
                                    </el-col>
                                </el-row>
                            </el-collapse-item>
                            <el-collapse-item title="Prévisions PSLA" name="2">
                                <el-row class="mt-3">
                                    <el-col :span="5" style="padding-top: 40px;">
                                        <div class="carousel-head">Portage sur FP K€</div>
                                        <div class="carousel-head">Coûts internes stockés % prix de vent</div>
                                    </el-col>
                                    <el-col :span="19" style="padding-left: 60px;">
                                        <div v-for="key in 5" :key="key" class="fixed-period-item">
                                            <p class="text-center m-0">{{parseInt(anneeDeReference) + key - 6}}</p>
                                            <el-input
                                                type="text"
                                                placeholder="0"
                                                :class="{'is-error':form.portageFp[key] !== null && !isFloat(form.portageFp[key])}"
                                                v-model="form.portageFp[key]"
                                                @change="formatInput('portageFp', true)">
                                            </el-input>
                                            <el-input
                                                type="text"
                                                placeholder="0"
                                                :class="{'is-error':form.coutsInternes[key] !== null && !isFloat(form.coutsInternes[key])}"
                                                v-model="form.coutsInternes[key]"
                                                @change="formatInput('coutsInternes', true)">
                                            </el-input>
                                        </div>
                                    </el-col>
                                </el-row>
                                <el-row class="mt-2">
                                    <el-col :span="5" style="padding-top: 55px;">
                                        <div class="carousel-head">Nombre de logements livrés</div>
                                    </el-col>
                                    <el-col :span="19">
                                        <periodique :anneeDeReference="anneeDeReference"
                                                    v-model="form.periodiques"
                                                    @onChange="periodicOnChange"></periodique>
                                    </el-col>
                                </el-row>
                            </el-collapse-item>
                        </el-collapse>
                    </el-tab-pane>
                    <el-tab-pane label="Investissements et financements" name="2">
                        <el-collapse v-model="collapseList">
                            <el-collapse-item title="Eléments financiers de la vente" name="1">
                                <el-row :gutter="20">
                                    <el-col :span="8">
                                        <el-form-item label="Prix de vente en k€/logt" prop="prixVente">
                                            <el-input type="text" v-model="form.prixVente" placeholder="0"
                                                      @change="formatInput('prixVente')"></el-input>
                                        </el-form-item>
                                        <el-form-item label="Taux d’évolution du loyer" prop="tauxEvolution">
                                            <el-input type="text" v-model="form.tauxEvolution" placeholder="0"
                                                      @change="formatInput('tauxEvolution')">
                                                <template slot="append">%</template>
                                            </el-input>
                                        </el-form-item>
                                    </el-col>
                                    <el-col :span="8">
                                        <el-form-item label="Taux marge brute" prop="tauxBrute">
                                            <el-input type="text" v-model="form.tauxBrute" placeholder="0"
                                                      @change="formatInput('tauxBrute')">
                                                <template slot="append">%</template>
                                            </el-input>
                                        </el-form-item>
                                    </el-col>
                                    <el-col :span="8">
                                        <el-form-item label="Loyer mensuel (€/logt)" prop="loyerMensuel">
                                            <el-input type="text" v-model="form.loyerMensuel" placeholder="0"
                                                      @change="formatInput('loyerMensuel')"></el-input>
                                        </el-form-item>
                                    </el-col>
                                </el-row>
                            </el-collapse-item>
                            <el-collapse-item title="Financement" name="2">
                                <div class="row mt-4">
                                    <div class="col-sm-12" >
                                        <p><strong>Emprunts</strong></p>
                                        <span>Ajouter un emprunt</span>
                                        <div class="d-flex">
                                            <el-select v-model="form.typeEmprunt">
                                                <el-option v-for="item in availableTypesEmprunts"
                                                    :key="item.id"
                                                    :label="item.nom"
                                                    :value="item.id"></el-option>
                                            </el-select>
                                            <el-button type="primary" class="ml-2" @click="addTypeEmprunt" :disabled="!form.typeEmprunt">Ajouter</el-button>
                                        </div>
                                        <el-row v-if="form.typeEmprunt" class="mt-5">
                                            <el-col :span="9">
                                                <el-form-item label="Quotité emprunt" label-width="70px" prop="montant">
                                                    <el-input type="text" v-model="form.montant" placeholder="0" class="fixed-input"
                                                              @change="formatInput('montant')"></el-input>
                                                </el-form-item>
                                            </el-col>
                                            <el-col :span="15">
                                                <el-form-item label="Date de la première annuité" label-width="105px" prop="datePremiere">
                                                    <el-date-picker
                                                        v-model="form.datePremiere"
                                                        type="month"
                                                        :picker-options="datePickerOptions"
                                                        value-format="yyyy-MM-dd"
                                                        format="MM/yyyy"
                                                        placeholder="Sélectionner"
                                                        style="width:130px;">
                                                    </el-date-picker>
                                                    <el-tooltip class="item" effect="dark" content="Date de première annuité ou intérêts en cas de différé d'amortissement" placement="top">
                                                        <i class="el-icon-info"></i>
                                                    </el-tooltip>
                                                </el-form-item>
                                            </el-col>
                                        </el-row>
                                        <p class="mt-5"><strong>Liste des emprunts</strong></p>
                                        <el-table
                                            :data="form.typeEmpruntPsla"
                                            style="width: 100%">
                                            <el-table-column sortable column-key="typesEmprunts" prop="typesEmprunts" min-width="60" label="Numéro emprunt">
                                                <template slot-scope="scope">
                                                    {{scope.row.typesEmprunts.nom}}
                                                </template>
                                            </el-table-column>
                                            <el-table-column sortable column-key="montant" prop="montant" min-width="100" label="Quotité emprunt" align="center"></el-table-column>
                                            <el-table-column fixed="right" width="90" label="supprimer">
                                                <template slot-scope="scope">
                                                    <el-button type="danger" icon="el-icon-delete" circle @click="handleDeleteEmprunt(scope.$index, scope.row)"></el-button>
                                                </template>
                                            </el-table-column>
                                        </el-table>
                                        <div class="text-center">
                                            <p>Total des emprunts : {{totalMontant}}</p>
                                        </div>
                                    </div>
                                </div>
                            </el-collapse-item>
                        </el-collapse>
                    </el-tab-pane>
                </el-tabs>
            </el-form>
            <div slot="footer" class="dialog-footer">
                <el-button type="outline-primary" @click="dialogVisible = false">Retour</el-button>
                <el-button type="primary" @click="save('pslaForm')" :disabled="isSubmitting">Valider</el-button>
            </div>
        </el-dialog>
    </div>
</template>

<script>
import moment from "moment";
import { getIncremental, updateDataByType } from '../../../../../../../utils/helpers';
import { isFloat, initPeriodic, checkAllPeriodics, mathInput, periodicFormatter } from '../../../../../../../utils/inputs';
import customValidator from '../../../../../../../utils/validation-rules';
import Periodique from '../../../../../../../components/partials/Periodique';

export default {
    name: "PslaNonidentifiees",
    components: { Periodique },
    props: ['simulationID', 'anneeDeReference', 'typesEmprunts', 'showError'],
    data() {
        var validateNom = (rule, value, callback) => {
            if (value === '') {
                callback(new Error("Veuillez saisir un nom de catégorie"));
            } else if (this.checkExistNom(value)) {
                callback(new Error("Cenom existe déjà"));
            } else {
                callback();
            }
        };
        var validateDureeConstruction = (rule, value, callback) => {
            if (value > 36) {
                callback(new Error("Veuillez choisir une valeur entre 0 et 36."));
            } else {
                callback();
            }
        };
        return {
            pslas: [],
            dialogVisible: false,
            collapseList: ['1'],
            form: null,
            isEdit: false,
            selectedIndex: null,
            activeTab: '1',
            availableTypesEmprunts: [],
            isSubmitting: false,
            query: null,
            columns: [],
            datePickerOptions: {
            },
            directScis: ['DIRECT', 'SCI'],
            formRules: {
                numero: customValidator.getPreset('number.positiveInt'),
                nom: [
                    { required: true, validator: validateNom, trigger: 'change' }
                ],
                detention: customValidator.getRule('positiveDouble'),
                prixVente: customValidator.getPreset("number.positiveDouble"),
                tauxBrute: customValidator.getRule('positiveDouble'),
                dureeConstruction: [
                    { pattern: /^\d{1,9}$/, message: 'Ce champs est invalide', trigger: 'change' },
                    { validator: validateDureeConstruction, trigger: 'change' }
                ],
                loyerMensuel: customValidator.getRule('positiveDouble'),
                tauxEvolution: customValidator.getRule('positiveDouble'),
                montant: [
                    { required: true, message: 'Veuillez saisir la quotité d\'emprunt', trigger: 'blur' },
                    customValidator.getRule('positiveDouble')
                ]
            }
        }
    },
    created () {
        this.initForm();
    },
    methods: {
        isFloat: isFloat,
        initForm() {
            this.form = {
                id: null,
                numero: getIncremental(this.pslas, 'numero'),
                nom: '',
                operationStock: false,
                typeEmpruntPsla: [],
                portageFp: initPeriodic(5),
                coutsInternes: initPeriodic(5),
                periodiques: {
                    nombreLogements: initPeriodic()
                }
            }
        },
        tableData(data, query) {
            if (!_.isNil(data)) {
                this.query = query;
                const pslas = data.pslas.items.map(item => {
                    let portageFp = [];
                    let coutsInternes = [];
                    let nombreLogements = [];
                    item.periodique.items.forEach(periodique => {
                        portageFp[periodique.iteration - 1] = periodique.portageFp;
                        coutsInternes[periodique.iteration - 1] = periodique.coutsInternes;
                        nombreLogements[periodique.iteration - 1] = periodique.nombreLogements;
                    });
                    let row = {...item};
                    row.typeEmpruntPsla = item.typeEmpruntPsla.items;
                    row.portageFp = portageFp;
                    row.coutsInternes = coutsInternes;
                    row.periodiques = {
                        nombreLogements
                    };
                    return row;
                });

                this.pslas = pslas;
                return pslas;
            } else {
                return [];
            }
        },

        save(formName) {
            this.$refs[formName].validate((valid) => {
                if (valid && checkAllPeriodics(this.form.periodiques)) {
                    this.isSubmitting = true;
                    const typeEmpruntPsla = this.form.typeEmpruntPsla.map(item => {
                        return JSON.stringify({
                            id: item.typesEmprunts.id,
                            montant: item.montant,
                            datePremiere: item.datePremiere
                        })
                    });
                    this.$apollo.mutate({
                        mutation: require('../../../../../../../graphql/simulations/accession/psla/savePsla.gql'),
                        variables: {
                            psla: {
                                uuid: this.form.id,
                                simulationId: this.simulationID,
                                numero: this.form.numero,
                                nom: this.form.nom,
                                operationStock: this.form.operationStock,
                                directSci: this.form.directSci,
                                detention: this.form.detention,
                                prixVente: this.form.prixVente,
                                tauxBrute: this.form.tauxBrute,
                                dureeConstruction: this.form.dureeConstruction,
                                dateLivraison: this.form.dateLivraison,
                                dureePhase: this.form.dureePhase,
                                loyerMensuel: this.form.loyerMensuel,
                                tauxEvolution: this.form.tauxEvolution,
                                total: this.totalMontant,
                                typeEmprunts: typeEmpruntPsla,
                                type: 1,
                                periodique: JSON.stringify({
                                    portage_fp: this.form.portageFp,
                                    couts_internes: this.form.coutsInternes,
                                    nombre_logements: this.form.periodiques.nombreLogements
                                })
                            }
                        }
                    }).then(() => {
                        this.dialogVisible = false;
                        this.isSubmitting = false;
                        updateDataByType(this.query, this.simulationID, 1).then(() => {
                            this.$message({
                                showClose: true,
                                message: 'Ce PSLA non identifié a bien été enregistré.',
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
        showCreateModal() {
            this.initForm();
            this.dialogVisible = true;
            this.selectedIndex = null;
            this.isEdit = false;
            this.getTypesEmprunts();
        },
        handleEdit(index, row) {
            this.dialogVisible = true;
            this.form = {...row};
            this.selectedIndex = index;
            this.isEdit = true;
            this.getTypesEmprunts();
        },
        handleDelete(index, row) {
            this.$confirm('Êtes-vous sûr de vouloir supprimer ce PSLA identifié?')
                .then(_ => {
                    this.$apollo.mutate({
                        mutation: require('../../../../../../../graphql/simulations/accession/psla/removePsla.gql'),
                        variables: {
                            pslaUUID: row.id,
                            simulationId: this.simulationID
                        }
                    }).then(() => {
                        updateDataByType(this.query, this.simulationID, 1).then(() => {
                            this.$message({
                                showClose: true,
                                message: 'Ce PSLA non identifié a bien été supprimé.',
                                type: 'success'
                            });
                        })
                    }).catch(error => {
                        updateDataByType(this.query, this.simulationID, 1).then(() => {
                            this.$message({
                                showClose: true,
                                message: 'Ce PSLA non identifié n\'existe pas.',
                                type: 'error',
                            });
                        });
                    });
                })
                .catch(_ => {});
        },
        addTypeEmprunt() {
            this.$refs['pslaForm'].validate((valid) => {
                if (this.form.typeEmprunt && this.form.montant && this.form.datePremiere) {
                    const typeEmprunt = this.availableTypesEmprunts.find(item => item.id == this.form.typeEmprunt);
                    this.form.typeEmpruntPsla.push({
                        montant: this.form.montant | 0,
                        datePremiere: this.form.datePremiere,
                        typesEmprunts: typeEmprunt,
                        local: true
                    });
                    this.form.typeEmprunt = null;
                    this.getTypesEmprunts();
                }
            });
        },
        handleDeleteEmprunt(index, row) {
            this.$confirm('Êtes-vous sûr de vouloir supprimer ce type d’emprunt?')
                .then(_ => {
                    if (row.local) {
                        this.form.typeEmpruntPsla.splice(index, 1);
                        this.getTypesEmprunts();
                    } else {
                        this.$apollo.mutate({
                            mutation: require('../../../../../../../graphql/simulations/accession/psla/removeTypeDempruntPsla.gql'),
                            variables: {
                                typesEmpruntsUUID: row.typesEmprunts.id,
                                pslaUUID: this.pslas[this.selectedIndex].id
                            }
                        }).then((res) => {
                            this.form.typeEmpruntPsla.splice(index, 1);
                            this.getTypesEmprunts();
                        });
                    }
                })
                .catch(_ => {});
        },
        getTypesEmprunts() {
            let emprunts = [];
            const linkedEmprunts = this.form.typeEmpruntPsla;
            this.typesEmprunts.forEach(item => {
                if(!linkedEmprunts.some(emprunt => emprunt.typesEmprunts.id === item.id)) {
                    emprunts.push(item);
                }
            });
            this.availableTypesEmprunts = emprunts;
        },
        checkExistNom(value) {
            let pslas = this.pslas;
            if (this.isEdit) {
                pslas = pslas.filter(item => item.nom !== this.pslas[this.selectedIndex].nom);
            }
            return pslas.some(item => item.nom === value);
        },
        back() {
            if (this.selectedIndex > 0) {
                this.selectedIndex--;
                this.form = {...this.pslas[this.selectedIndex]};
            }
        },
        next() {
            if (this.selectedIndex < (this.pslas.length - 1)) {
                this.selectedIndex++;
                this.form = {...this.pslas[this.selectedIndex]};
            }
        },
        periodicOnChange(type) {
            let newPeriodics = this.form.periodiques[type];
            this.form.periodiques[type] = [];
            this.form.periodiques[type] = periodicFormatter(newPeriodics);
        },
        setTableColumns() {
            this.columns = [];
            for (var i = 0; i < 5; i++) {
                this.columns.push({
                    label: (parseInt(this.anneeDeReference) + i - 5).toString() + ' Portage sur FP',
                    prop: `portageFp[${i}]`
                });
                this.columns.push({
                    label: (parseInt(this.anneeDeReference) + i - 5).toString() + ' Coûts internes stockés',
                    prop: `coutsInternes[${i}]`
                });
            }
            for (var i = 0; i < 50; i++) {
                this.columns.push({
                    label: (parseInt(this.anneeDeReference) + i).toString() + ' Nombre de logements livrés',
                    prop: `periodiques.nombreLogements[${i}]`
                });
            }
        },
        formatInput(type, periodic=false) {
            if (periodic) {
                let newPeriodics = this.form[type];
                this.form[type] = [];
                this.form[type] = periodicFormatter(newPeriodics);
            } else {
                this.form[type] = mathInput(this.form[type]);
            }
        }
    },
    computed: {
        isModify() {
            return this.$store.getters['choixEntite/isModify'];
        },
        totalMontant() {
            let emprunt = 0;
            this.form.typeEmpruntPsla.forEach(item => {
                emprunt += parseFloat(item.montant);
            });
            return emprunt;
        },
        isModify() {
            return this.$store.getters['choixEntite/isModify'];
        }
    },
    filters: {
        dateFR: function(value) {
            return value ? moment.utc(String(value)).format("MM/YYYY") : "";
        }
    },
    watch: {
        anneeDeReference (newVal) {
            if (newVal) {
                this.setTableColumns();
            }
        }
    }
}
</script>

<style>
    .accession-psla-nonidentifiees .fixed-period-item {
        width: 60px;
        font-size: 12px;
        padding: 0 5px;
        display: table-cell;
        text-align: center;
    }
    .accession-psla-nonidentifiees .fixed-period-item .el-input{
        margin-top: 10px;
    }
    .accession-psla-nonidentifiees .fixed-period-item .el-input__inner{
        padding: 0 3px;
        text-align: right;
    }
</style>
