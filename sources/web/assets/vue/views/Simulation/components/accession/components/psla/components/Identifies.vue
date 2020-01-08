<template>
    <div class="accession-psla-identifiees">
        <el-row class="mb-3">
            <el-col :span="2" :offset="19">
                <el-upload
                  ref="upload"
                  :action.native="'/import-psla-identifiees/'+ simulationID"
                  multiple
                  :limit="10"
                  :on-success="onSuccess"
                  :on-error="onError">
                  <el-button type="primary">Importer</el-button>
                </el-upload>
            </el-col>
            <el-col :span="2">
                <el-button type="success" @click.stop="exportPslaIdentifiees">
                    Exporter
                </el-button>
            </el-col>
        </el-row>
        <ApolloQuery
                :query="require('../../../../../../../graphql/simulations/accession/psla/pslas.gql')"
                :variables="{
                simulationId: simulationID,
                type: 0
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
                    <el-table-column sortable column-key="nom" prop="nom" min-width="150" label="Nom de l’opération">
                        <template slot="header">
                            <span title="Nom de l’opération">Nom de l’opération</span>
                        </template>
                    </el-table-column>
                    <el-table-column sortable column-key="directSci" prop="directSci" min-width="120" label="Direct / SCI">
                        <template slot="header">
                            <span title="Direct / SCI">Direct / SCI</span>
                        </template>
                    </el-table-column>
                    <el-table-column sortable column-key="detention" prop="detention" min-width="150" label="% de détention">
                        <template slot="header">
                            <span title="% de détention">% de détention</span>
                        </template>
                    </el-table-column>
                    <el-table-column sortable column-key="operationStock" prop="operationStock" min-width="120" label="Opération en stock">
                        <template slot-scope="scope">
                            {{scope.row.operationStock ? 'Oui': 'Non'}}
                        </template>
                        <template slot="header">
                            <span title="Opération en stock">Opération en stock</span>
                        </template>
                    </el-table-column>
                    <el-table-column sortable column-key="nombreLogements" prop="nombreLogements" min-width="150" label="Nombre de logts">
                        <template slot="header">
                            <span title="Nombre de logts">Nombre de logts</span>
                        </template>
                    </el-table-column>
                    <el-table-column sortable column-key="prixVente" prop="prixVente" min-width="130" label="Prix de vente">
                        <template slot="header">
                            <span title="Prix de vente">Prix de vente</span>
                        </template>
                    </el-table-column>
                    <el-table-column sortable column-key="tauxBrute" prop="tauxBrute" min-width="170" label="Taux marge brute">
                        <template slot="header">
                            <span title="Taux marge brute">Taux marge brute</span>
                        </template>
                    </el-table-column>
                    <el-table-column sortable column-key="dureeConstruction" prop="dureeConstruction" min-width="180" label="Durée de construction">
                        <template slot="header">
                            <span title="Durée de construction">Durée de construction</span>
                        </template>
                    </el-table-column>
                    <el-table-column sortable column-key="dateLivraison" prop="dateLivraison" min-width="150" label="Date de livraison">
                        <template slot-scope="scope">
                            {{scope.row.dateLivraison | dateFR}}
                        </template>
                        <template slot="header">
                            <span title="Date de livraison">Date de livraison</span>
                        </template>
                    </el-table-column>
                    <el-table-column sortable column-key="loyerMensuel" prop="loyerMensuel" min-width="170" label="Loyer mensuel (€/lgt)">
                        <template slot="header">
                            <span title="Loyer mensuel (€/lgt)">Loyer mensuel (€/lgt)</span>
                        </template>
                    </el-table-column>
                    <el-table-column sortable column-key="tauxEvolution" prop="tauxEvolution" min-width="180" label="Taux d’évolution du loyer">
                        <template slot="header">
                            <span title="Taux d’évolution du loyer">Taux d’évolution du loyer</span>
                        </template>
                    </el-table-column>
                    <el-table-column sortable column-key="montantSubventions" prop="montantSubventions" min-width="180" label="Montant des subventions">
                        <template slot="header">
                            <span title="Montant des subventions">Montant des subventions</span>
                        </template>
                    </el-table-column>
                    <el-table-column sortable column-key="total" prop="total" min-width="170" label="Montants des emprunts">
                        <template slot="header">
                            <span title="Montants des emprunts">Montants des emprunts</span>
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
            <el-button type="primary" :disabled="!isModify" @click="showCreateModal">Créer un PSLA Identifié</el-button>
        </el-row>

        <el-dialog
            title="Création/Modification de PSLA Identifié"
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
                                    <el-col :span="6">
                                        <el-form-item label="N°" prop="numero">
                                            <el-input type="text" v-model="form.numero" placeholder="N°" readonly></el-input>
                                        </el-form-item>
                                    </el-col>
                                    <el-col :span="6">
                                        <el-form-item label="Nom de l’opération" prop="nom">
                                            <el-input type="text" v-model="form.nom" placeholder="Nom de l’opération"></el-input>
                                        </el-form-item>
                                    </el-col>
                                    <el-col :span="6">
                                    <el-form-item label="DIRECT / SCI" prop="directSci">
                                        <el-select v-model="form.directSci">
                                            <el-option v-for="item in directScis"
                                                       :key="item"
                                                       :label="item"
                                                       :value="item"></el-option>
                                        </el-select>
                                    </el-form-item>
                                    </el-col>
                                    <el-col :span="6">
                                    <el-form-item label="% de détention" prop="detention">
                                        <el-input type="text" v-model="form.detention" placeholder="0" @change="formatInput('detention')">
                                            <template slot="append">%</template>
                                        </el-input>
                                    </el-form-item>
                                    </el-col>
                                </el-row>
                                <el-row :gutter="24">
                                    <el-col :span="8">
                                        <el-form-item label="Nombre de logements" prop="nombreLogements">
                                            <el-input type="text" v-model="form.nombreLogements" placeholder="0" @change="formatInput('nombreLogements')"></el-input>
                                        </el-form-item>
                                    </el-col>
                                    <el-col :span="8">
                                        <el-form-item label="Date de livraison" prop="dateLivraison">
                                            <el-date-picker
                                                    v-model="form.dateLivraison"
                                                    type="month"
                                                    :picker-options="datePickerOptions"
                                                    value-format="yyyy-MM-dd"
                                                    format="MM/yyyy"
                                                    placeholder="Date">
                                            </el-date-picker>
                                        </el-form-item>
                                    </el-col>
                                    <el-col :span="8">
                                        <div style="padding-top:30px">
                                            <el-checkbox v-model="form.operationStock" class="mb-3">Opération en stock au 31/12/{{anneeDeReference}}</el-checkbox>
                                        </div>
                                    </el-col>
                                </el-row>
                            </el-collapse-item>
                            <el-collapse-item title="Prévisions PSLA" name="2">
                                <el-row class="mt-4">
                                    <el-col :span="5" style="padding-top: 55px;">
                                        <div class="carousel-head">Contrats location accession</div>
                                        <div class="carousel-head">Levées d'option</div>
                                        <div class="carousel-head">Coûts internes % du prix vente</div>
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
                                        <el-form-item label="Prix de vente k€/logt" prop="prixVente">
                                            <el-input type="text" v-model="form.prixVente" placeholder="0" @change="formatInput('prixVente')"></el-input>
                                        </el-form-item>
                                        <el-form-item label="Taux d'évolution des loyers" prop="tauxEvolution">
                                            <el-input type="text" v-model="form.tauxEvolution" placeholder="0" @change="formatInput('tauxEvolution')">
                                                <template slot="append">%</template>
                                            </el-input>
                                        </el-form-item>
                                    </el-col>
                                    <el-col :span="8">
                                        <el-form-item label="Taux marge brute" prop="tauxBrute">
                                            <el-input type="text" v-model="form.tauxBrute" placeholder="0" @change="formatInput('tauxBrute')">
                                                <template slot="append">%</template>
                                            </el-input>
                                        </el-form-item>
                                        <el-form-item label="Durée de construction (mois)" prop="dureeConstruction">
                                            <el-input type="text" v-model="form.dureeConstruction" placeholder="0" @change="formatInput('dureeConstruction')"></el-input>
                                        </el-form-item>
                                    </el-col>
                                    <el-col :span="8">
                                        <el-form-item label="Loyer mensuel (€/lgt)" prop="loyerMensuel">
                                            <el-input type="text" v-model="form.loyerMensuel" placeholder="0" @change="formatInput('loyerMensuel')"></el-input>
                                        </el-form-item>
                                    </el-col>
                                </el-row>
                            </el-collapse-item>
                            <el-collapse-item title="Financement" name="2">
                                <div class="row mt-4">
                                    <div class="col-sm-6">
                                        <el-form-item label="Montants des subventions" prop="montantSubventions">
                                            <el-input type="text" v-model="form.montantSubventions" placeholder="0" class="fixed-input"
                                                      @change="formatInput('montantSubventions')"></el-input>
                                        </el-form-item>
                                    </div>
                                    <div class="col-sm-6">
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
                                                <el-form-item label="Montant" label-width="70px" prop="montant">
                                                    <el-input type="text" v-model="form.montant" placeholder="0" class="fixed-input"
                                                              @change="formatInput('montant')"></el-input>
                                                </el-form-item>
                                            </el-col>
                                            <el-col :span="15">
                                                <el-form-item label="Date de première échéance" label-width="105px" prop="datePremiere">
                                                    <el-date-picker
                                                        v-model="form.datePremiere"
                                                        type="month"
                                                        :picker-options="datePickerOptions"
                                                        value-format="yyyy-MM-dd"
                                                        format="MM/yyyy"
                                                        placeholder="jj/mm/yyyy"
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
                                            <el-table-column sortable column-key="montant" prop="montant" min-width="100" label="Montant" align="center"></el-table-column>
                                            <el-table-column sortable column-key="datePremiere" prop="datePremiere" min-width="100" label="Date de première annuité" align="center">
                                                <template slot-scope="scope">
                                                    {{scope.row.datePremiere | dateFR}}
                                                </template>
                                            </el-table-column>
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
import { initPeriodic, checkAllPeriodics, mathInput, periodicFormatter } from'../../../../../../../utils/inputs';
import customValidator from '../../../../../../../utils/validation-rules';
import Periodique from '../../../../../../../components/partials/Periodique';

export default {
    name: "PslaIdentifiees",
    components: { Periodique },
    props: ['simulationID', 'anneeDeReference', 'typesEmprunts', 'showError'],
    data() {
        var validateNom = (rule, value, callback) => {
            if (value === '') {
                callback(new Error("Veuillez saisir un nom de d’opération"));
            } else if (this.checkExistNom(value)) {
                callback(new Error("Ce nom existe déjà"));
            } else {
                callback();
            }
        };
        var validateDureeConstruction = (rule, value, callback) => {
            if (value > 36) {
                callback(new Error("Veuillez renseigner une valeur entre 0 - 36"));
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
            datePickerOptions: {
            },
            directScis: ['DIRECT', 'SCI'],
            columns: [],
            formRules: {
                numero: customValidator.getPreset('number.positiveInt'),
                nom: [
                    { required: true, validator: validateNom, trigger: 'change' }
                ],
                detention: customValidator.getRule('positiveDouble'),
                nombreLogements: customValidator.getRule('positiveInt'),
                prixVente: customValidator.getRule('positiveDouble'),
                tauxBrute: customValidator.getRule('positiveDouble'),
                dureeConstruction: [
                    { pattern: /^\d{1,9}$/, message: 'Ce champs est invalide', trigger: 'change' },
                    { validator: validateDureeConstruction, trigger: 'change' }
                ],
                loyerMensuel: customValidator.getRule('positiveDouble'),
                tauxEvolution: customValidator.getRule('positiveDouble')
            }
        }
    },
    created () {
        this.initForm();
    },
    methods: {
        initForm() {
            this.form = {
                id: null,
                numero: getIncremental(this.pslas, 'numero'),
                nom: '',
                operationStock: false,
                typeEmpruntPsla: [],
                periodiques: {
                    contratsAccession: initPeriodic(),
                    leveesOption: initPeriodic(),
                    coutsInternes: initPeriodic()
                }
            }
        },
        tableData(data, query) {
            if (!_.isNil(data)) {
                this.query = query;
                const pslas = data.pslas.items.map(item => {
                    let contratsAccession = [];
                    let leveesOption = [];
                    let coutsInternes = [];
                    item.periodique.items.forEach(periodique => {
                        contratsAccession[periodique.iteration - 1] = periodique.contratsAccession;
                        leveesOption[periodique.iteration - 1] = periodique.leveesOption;
                        coutsInternes[periodique.iteration - 1] = periodique.coutsInternes;
                    });
                    let row = {...item};
                    row.typeEmpruntPsla = item.typeEmpruntPsla.items;
                    row.periodiques = {
                        contratsAccession,
                        leveesOption,
                        coutsInternes
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
                                directSci: this.form.directSci,
                                detention: this.form.detention,
                                operationStock: this.form.operationStock,
                                nombreLogements: this.form.nombreLogements,
                                prixVente: this.form.prixVente,
                                tauxBrute: this.form.tauxBrute,
                                dureeConstruction: parseInt(this.form.dureeConstruction),
                                dateLivraison: this.form.dateLivraison,
                                loyerMensuel: this.form.loyerMensuel,
                                tauxEvolution: this.form.tauxEvolution,
                                montantSubventions: this.form.montantSubventions,
                                total: this.totalMontant,
                                typeEmprunts: typeEmpruntPsla,
                                type: 0,
                                periodique: JSON.stringify({
                                    contrats_accession: this.form.periodiques.contratsAccession,
                                    levees_option: this.form.periodiques.leveesOption,
                                    couts_internes: this.form.periodiques.coutsInternes
                                })
                            }
                        }
                    }).then(() => {
                        this.dialogVisible = false;
                        this.isSubmitting = false;
                        updateDataByType(this.query, this.simulationID, 0).then(() => {
                            this.$message({
                                showClose: true,
                                message: 'Ce PSLA identifié a bien été enregistré.',
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
                        updateDataByType(this.query, this.simulationID, 0).then(() => {
                            this.$message({
                                showClose: true,
                                message: 'Ce PSLA identifié a bien été supprimé.',
                                type: 'success'
                            });
                        })
                    }).catch(error => {
                        updateDataByType(this.query, this.simulationID, 0).then(() => {
                            this.$message({
                                showClose: true,
                                message: 'Ce PSLA identifié n\'existe pas.',
                                type: 'error',
                            });
                        });
                    });
                })
                .catch(_ => {});
        },
        addTypeEmprunt() {
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
                if(!linkedEmprunts.some(emprunt => emprunt.typesEmprunts.id == item.id)) {
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
            return pslas.some(item => item.nom == value);
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
                for (var i = 0; i < 50; i++) {
                    this.columns.push({
                        label: (parseInt(this.anneeDeReference) + i).toString() + ' Contrats location accession',
                        prop: `periodiques.contratsAccession[${i}]`
                    });
                    this.columns.push({
                        label: (parseInt(this.anneeDeReference) + i).toString() + ' Levées d\'option',
                        prop: `periodiques.leveesOption[${i}]`
                    });
                    this.columns.push({
                        label: (parseInt(this.anneeDeReference) + i).toString() + ' Coûts internes',
                        prop: `periodiques.coutsInternes[${i}]`
                    });
                }
            },
        formatInput(type) {
            this.form[type] = mathInput(this.form[type]);
        },
        exportPslaIdentifiees() {
           window.location.href = "/export-psla-identifiees/" + this.simulationID;
        },
        onSuccess(res) {
            this.$toasted.success(res, {
                theme: 'toasted-primary',
                icon: 'check',
                position: 'top-right',
                duration: 5000
            });
            updateDataByType(this.query, this.simulationID, 0);
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
