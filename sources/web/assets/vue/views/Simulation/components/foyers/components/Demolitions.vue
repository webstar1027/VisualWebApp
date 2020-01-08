<template>
    <div class="demolition-foyers">
        <ApolloQuery
                :query="require('../../../../../graphql/simulations/foyers/demolitions/demolitionFoyers.gql')"
                :variables="{
                simulationId: simulationID
            }">
            <template slot-scope="{ result:{ loading, error, data }, isLoading , query}">
                <div v-if="error">Une erreur est survenue !</div>
                <el-table
                    v-loading="isLoading"
                    :data="tableData(data, query)"
                    style="width: 100%">
                    <el-table-column sortable column-key="numero" prop="numero">
                        <template slot="header">
                            <span title="N°">N°</span>
                        </template>
                    </el-table-column>
                    <el-table-column sortable column-key="nomIntervention" prop="nomIntervention" min-width="200">
                        <template slot="header">
                            <span title="Nom de l'opération">Nom de l'opération</span>
                        </template>
                    </el-table-column>
                    <el-table-column sortable column-key="nombreLogements" prop="nombreLogements" min-width="180">
                        <template slot="header">
                            <span title="Nombre équivalent logements">Nombre équivalent logements</span>
                        </template>
                    </el-table-column>
                    <el-table-column sortable column-key="date" prop="date" min-width="120">
                        <template slot-scope="scope">
                            {{scope.row.date | dateFR}}
                        </template>
                        <template slot="header">
                            <span title="Date de démolition">Date de démolition</span>
                        </template>
                    </el-table-column>
                    <el-table-column sortable column-key="remboursementAnticipe" prop="remboursementAnticipe" min-width="160">
                        <template slot="header">
                            <span title="Remboursement anticipé">Remboursement anticipé</span>
                        </template>
                    </el-table-column>
                    <el-table-column sortable column-key="indexationIcc" prop="indexationIcc" min-width="160">
                        <template slot-scope="scope">
                            {{scope.row.indexationIcc ? 'Oui': 'Non'}}
                        </template>
                        <template slot="header">
                            <span title="Indexation ICC">Indexation ICC</span>
                        </template>
                    </el-table-column>
                    <el-table-column sortable column-key="prixRevient" prop="prixRevient" min-width="150">
                        <template slot="header">
                            <span title="Prix de revient">Prix de revient</span>
                        </template>
                    </el-table-column>
                    <el-table-column sortable column-key="fondsPropres" prop="fondsPropres" min-width="150">
                        <template slot="header">
                            <span title="Fonds propres">Fonds propres</span>
                        </template>
                    </el-table-column>
                    <el-table-column sortable column-key="subventionsEtat" prop="subventionsEtat" min-width="180">
                        <template slot="header">
                            <span title="Subventions d'Etat">Subventions d'Etat</span>
                        </template>
                    </el-table-column>
                    <el-table-column sortable column-key="subventionsAnru" prop="subventionsAnru" min-width="180">
                        <template slot="header">
                            <span title="Subventions ANRU">Subventions ANRU</span>
                        </template>
                    </el-table-column>
                    <el-table-column sortable column-key="subventionsEpci" prop="subventionsEpci" min-width="170">
                        <template slot="header">
                            <span title="Subventions EPCI / Commune">Subventions EPCI / Commune</span>
                        </template>
                    </el-table-column>
                    <el-table-column sortable column-key="subventionsDepartement" prop="subventionsDepartement" min-width="170">
                        <template slot="header">
                            <span title="Subventions département">Subventions département</span>
                        </template>
                    </el-table-column>
                    <el-table-column sortable column-key="subventionsRegion" prop="subventionsRegion" min-width="180">
                        <template slot="header">
                            <span title="Subventions Région">Subventions Région</span>
                        </template>
                    </el-table-column>
                    <el-table-column sortable column-key="subventionsCollecteur" prop="subventionsCollecteur" min-width="190">
                        <template slot="header">
                            <span title="Subventions collecteur">Subventions collecteur</span>
                        </template>
                    </el-table-column>
                    <el-table-column sortable column-key="autresSubventions" prop="autresSubventions" min-width="170">
                        <template slot="header">
                            <span title="Autres subventions">Autres subventions</span>
                        </template>
                    </el-table-column>
                    <el-table-column sortable column-key="totalEmprutns" prop="totalEmprutns" min-width="150">
                        <template slot="header">
                            <span title="Total (Emprunt)">Total (Emprunt)</span>
                        </template>
                    </el-table-column>
                    <el-table-column sortable column-key="tauxEvolutionTfpb" prop="tauxEvolutionTfpb" min-width="150">
                        <template slot="header">
                            <span title="Taux d'évolution TFPB">Taux d'évolution TFPB</span>
                        </template>
                    </el-table-column>
                    <el-table-column sortable column-key="tauxEvolutionMaintenance" prop="tauxEvolutionMaintenance" min-width="150">
                        <template slot="header">
                            <span title="Taux d'évolution Maintenance courante">Taux d'évolution Maintenance courante</span>
                        </template>
                    </el-table-column>
                    <el-table-column sortable column-key="tauxEvolutionGrosEntretien" prop="tauxEvolutionGrosEntretien" min-width="150">
                        <template slot="header">
                            <span title="Taux d'évolution Gros entretien">Taux d'évolution Gros entretien</span>
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
            <el-button type="primary" :disabled="!isModify" @click="showCreateModal">Créer une nouvelle fiche de démolitions foyers</el-button>
        </el-row>
        <el-dialog
            title="Création/Modification des démolitions foyers"
            :visible.sync="dialogVisible"
            :close-on-click-modal="false"
            width="75%">
            <el-row v-if="isEdit" class="form-slider text-center mb-4">
                <i class="el-icon-back font-weight-bold" @click="back"></i>
                <i class="el-icon-right font-weight-bold" @click="next"></i>
            </el-row>
            <el-form label-position="left" :model="form" :rules="formRules" label-width="180px" ref="demolitionForm">
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
                                        <el-form-item label="Nom de l'intervention" prop="nomIntervention">
                                            <el-input type="text" v-model="form.nomIntervention" placeholder="Nom de l'intervention"></el-input>
                                        </el-form-item>
                                    </el-col>
                                    <el-col :span="6">
                                        <el-form-item label="Nombre équivalent logements" prop="nombreLogements">
                                            <el-input type="text" v-model="form.nombreLogements" placeholder="0"
                                                      @change="formatInput('nombreLogements')"></el-input>
                                        </el-form-item>
                                    </el-col>
                                    <el-col :span="6">
                                        <el-form-item label="Date" prop="date">
                                            <el-date-picker
                                                v-model="form.date"
                                                type="month"
                                                :picker-options="datePickerOptions"
                                                value-format="yyyy-MM-dd"
                                                format="MM/yyyy"
                                                placeholder="Sélectionner">
                                            </el-date-picker>
                                        </el-form-item>
                                    </el-col>
                                </el-row>
                            </el-collapse-item>
                            <el-collapse-item title="Impact démolitions" name="2">
                                <el-row :gutter="20">
                                    <el-col :span="8">
                                        <el-form-item label="Remboursement anticipé" prop="remboursementAnticipe">
                                            <el-input type="text" v-model="form.remboursementAnticipe" placeholder="0.0"
                                                      @change="formatInput('remboursementAnticipe')"></el-input>
                                        </el-form-item>
                                    </el-col>
                                </el-row>
                            </el-collapse-item>
                            <el-collapse-item title="Compléments" name="3">
                                <el-row class="mt-4">
                                    <el-col :span="4" style="padding-top: 50px;">
                                        <div class="carousel-head">
                                            <p>Compléments d'annuités - part K</p>
                                        </div>
                                        <div class="carousel-head">
                                            <p>Compléments d'annuités - part i</p>
                                        </div>
                                        <div class="carousel-head">
                                            <p>Redevances</p>
                                        </div>
                                        <div class="carousel-head">
                                            <p>TFPB</p>
                                        </div>
                                        <div class="carousel-head">
                                            <p>Maintenance courante</p>
                                        </div>
                                        <div class="carousel-head">
                                            <p>Gros entretien</p>
                                        </div>
                                    </el-col>
                                    <el-col :span="2" style="padding-top: 20px;">
                                        <div class="carousel-head">
                                            <p class="m-0">Taux d'évolution</p>
                                        </div>
                                        <div class="carousel-head" style="margin-top: 80px;">
                                            <el-form-item label-width="0" prop="tauxEvolutionTfpb">
                                                <el-input type="text" v-model="form.tauxEvolutionTfpb" placeholder="0.0" class="fixed-input"
                                                          @change="formatInput('tauxEvolutionTfpb')"></el-input>
                                            </el-form-item>
                                        </div>
                                        <div class="carousel-head">
                                            <el-form-item label-width="0" prop="tauxEvolutionMaintenance">
                                                <el-input type="text" v-model="form.tauxEvolutionMaintenance" placeholder="0.0" class="fixed-input"
                                                          @change="formatInput('tauxEvolutionMaintenance')"></el-input>
                                            </el-form-item>
                                        </div>
                                        <div class="carousel-head">
                                            <el-form-item label-width="0" prop="tauxEvolutionGrosEntretien">
                                                <el-input type="text" v-model="form.tauxEvolutionGrosEntretien" placeholder="0.0" class="fixed-input"
                                                          @change="formatInput('tauxEvolutionGrosEntretien')"></el-input>
                                            </el-form-item>
                                        </div>
                                    </el-col>
                                    <el-col :span="18">
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
                            <el-collapse-item title="Investissements en K€" name="1">
                                <el-row :gutter="20">
                                    <el-col :span="8">
                                        <el-form-item label="Prix de revient" prop="prixRevient">
                                            <el-input type="text" v-model="form.prixRevient" placeholder="0" class="fixed-input"
                                                      @change="formatInput('prixRevient')"></el-input>
                                        </el-form-item>
                                        <el-checkbox v-model="form.indexationIcc" class="mb-3">Indexation à I'ICC</el-checkbox>
                                    </el-col>
                                </el-row>
                            </el-collapse-item>
                            <el-collapse-item title="Financements en K€" name="2">
                                <div class="row mt-4">
                                    <div class="col-sm-7 p-0">
                                        <div class="row">
                                            <div class="col-sm-6">
                                                <el-form-item label="Fonds Propres" prop="fondsPropres">
                                                    <el-input type="text" v-model="form.fondsPropres" placeholder="0" class="fixed-input"
                                                              @change="formatInput('fondsPropres')"></el-input>
                                                </el-form-item>
                                                <el-form-item label="Total montant subventions" prop="totalSubventions">
                                                    {{totalSubventions}}
                                                </el-form-item>
                                                <el-form-item label="Total montant emprunts" prop="totalMontant">
                                                    {{totalMontant}}
                                                </el-form-item>
                                                <el-form-item label="Reste à financer" prop="resteFinancer">
                                                    {{resteFinancer}}K€
                                                </el-form-item>
                                            </div>
                                            <div class="col-sm-6">
                                                <p><strong>Subventions en K€</strong></p>
                                                <el-form-item label="Subventions d'Etat" prop="subventionsEtat">
                                                    <el-input type="text" v-model="form.subventionsEtat" placeholder="0" class="fixed-input"
                                                              @change="formatInput('subventionsEtat')"></el-input>
                                                </el-form-item>
                                                <el-form-item label="Subventions ANRU" prop="subventionsAnru">
                                                    <el-input type="text" v-model="form.subventionsAnru" placeholder="0" class="fixed-input"
                                                              @change="formatInput('subventionsAnru')"></el-input>
                                                </el-form-item>
												<el-form-item label="Subventions Régions" prop="subventionsRegion">
													<el-input type="text" v-model="form.subventionsRegion" placeholder="0" class="fixed-input"
															  @change="formatInput('subventionsRegion')"></el-input>
												</el-form-item>
                                                <el-form-item label="Subventions départements" prop="subventionsDepartement">
                                                    <el-input type="text" v-model="form.subventionsDepartement" placeholder="0" class="fixed-input"
                                                              @change="formatInput('subventionsDepartement')"></el-input>
                                                </el-form-item>
												<el-form-item label="Subventions EPCI/Communes" prop="subventionsEpci">
													<el-input type="text" v-model="form.subventionsEpci" placeholder="0" class="fixed-input"
															  @change="formatInput('subventionsEpci')"></el-input>
												</el-form-item>
                                                <el-form-item label="Subventions collecteurs" prop="subventionsCollecteur">
                                                    <el-input type="text" v-model="form.subventionsCollecteur" placeholder="0" class="fixed-input"
                                                              @change="formatInput('subventionsCollecteur')"></el-input>
                                                </el-form-item>
                                                <el-form-item label="Autres subventions" prop="autresSubventions">
                                                    <el-input type="text" v-model="form.autresSubventions" placeholder="0" class="fixed-input"
                                                              @change="formatInput('autresSubventions')"></el-input>
                                                </el-form-item>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-5">
                                        <p><strong>Emprunts en K€</strong></p>
                                        <span class="form-label">Ajouter un emprunt</span>
                                            <el-select v-model="form.typeEmprunt" class="w-100">
                                                <el-option v-for="item in availableTypesEmprunts"
                                                    :key="item.id"
                                                    :label="item.nom"
                                                    :value="item.id"></el-option>
                                            </el-select>
                                        <div class="d-flex mt-1">
                                            <el-button type="primary" @click="addTypeEmprunt" :disabled="!form.typeEmprunt">Ajouter</el-button>
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
                                                        placeholder="JJ/MM/AAAA"
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
                                            :data="form.typeEmpruntDemolitionFoyers"
                                            style="width: 100%">
                                            <el-table-column sortable column-key="typesEmprunts" prop="typesEmprunts" min-width="60" label="Numéro">
                                                <template slot-scope="scope">
                                                    {{scope.row.typesEmprunts.nom}}
                                                </template>
                                            </el-table-column>
                                            <el-table-column sortable column-key="montant" prop="montant" min-width="100" label="Montant emprunt" align="center"></el-table-column>
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
                                            <p>Total des emprunts: {{totalMontant}}</p>
                                        </div>
                                    </div>
                                </div>
                            </el-collapse-item>
                        </el-collapse>
                    </el-tab-pane>
                </el-tabs>
            </el-form>
            <div slot="footer" class="dialog-footer">
                <el-button type="primary" @click="dialogVisible = false">Retour</el-button>
                <el-button type="success" :disabled="isSubmitting" @click="save('demolitionForm')">Valider</el-button>
            </div>
        </el-dialog>
    </div>
</template>

<script>
import moment from "moment";
import { getIncremental, updateData, getFloat } from '../../../../../utils/helpers';
import {periodicFormatter, initPeriodic, checkAllPeriodics, mathInput} from '../../../../../utils/inputs';
import customValidator from '../../../../../utils/validation-rules';
import Periodique from '../../../../../components/partials/Periodique';

export default {
    name: "DemolitionFoyers",
    components: { Periodique },
    data() {
        var validateNom = (rule, value, callback) => {
            if (value === '') {
                callback(new Error("Veuillez sélectionner un nom"));
            } else if (this.checkExistNom(value)) {
                callback(new Error("Ce nom existe déjà"));
            } else {
                callback();
            }
        };
        var resteFinancerValidate = (rule, value, callback) => {
            if (this.resteFinancer !== 0) {
                callback(new Error("le plan de financement n’est pas équilibré"));
            } else {
                callback();
            }
        };
        return {
            simulationID: null,
            anneeDeReference: null,
            collapseList: ['1'],
            demolitionFoyers: [],
            dialogVisible: false,
            form: null,
            selectedIndex: 0,
            isEdit: false,
            isSubmitting: false,
            activeTab: '1',
            query: null,
            typesEmprunts: [],
            availableTypesEmprunts: [],
            columns: [],
            datePickerOptions: {
            },
            formRules: {
                numero: customValidator.getPreset('number.positiveInt'),
                nomIntervention: [
                    { required: true, validator: validateNom, trigger: 'change' }
                ],
                date: customValidator.getRule('required'),
                remboursementAnticipe: customValidator.getRule('positiveDouble'),
                prixRevient: customValidator.getPreset('number.positiveDouble'),
                fondsPropres: customValidator.getPreset('number.positiveDouble'),
                subventionsEtat: customValidator.getRule('positiveDouble'),
                subventionsAnru: customValidator.getRule('positiveDouble'),
                subventionsEpci: customValidator.getRule('positiveDouble'),
                subventionsDepartement: customValidator.getRule('positiveDouble'),
                subventionsRegion: customValidator.getRule('positiveDouble'),
                subventionsCollecteur: customValidator.getRule('positiveDouble'),
                autresSubventions: customValidator.getRule('positiveDouble'),
                tauxEvolutionTfpb: customValidator.getRule('positiveDouble'),
                tauxEvolutionMaintenance: customValidator.getRule('positiveDouble'),
                tauxEvolutionGrosEntretien: customValidator.getRule('positiveDouble'),
                datePremiere: customValidator.getRule('required'),
                resteFinancer: [
                    { validator: resteFinancerValidate, trigger: 'change' }
                ]
            }
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
    mounted() {
        this.getTypeEmprunts();
    },
    methods: {
        initForm() {
            this.form = {
                id: null,
                numero: getIncremental(this.demolitionFoyers, 'numero'),
                nomIntervention: '',
                typeEmpruntDemolitionFoyers: [],
                periodiques: {
                    partCapital: initPeriodic(),
                    partInterets: initPeriodic(),
                    redevances: initPeriodic(),
                    tfpb: initPeriodic(),
                    maintenanceCourante: initPeriodic(),
                    grosEntretien: initPeriodic()
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
        setTableColumns() {
            this.columns = [];
            for (var i = 0; i < 50; i++) {
                this.columns.push({
                    label: (parseInt(this.anneeDeReference) + i).toString() + ' Compléments d\'annuités - part capital',
                    prop: `periodiques.partCapital[${i}]`
                });
                this.columns.push({
                    label: (parseInt(this.anneeDeReference) + i).toString() + ' Compléments d\'annuités - part intérêts',
                    prop: `periodiques.partInterets[${i}]`
                });
                this.columns.push({
                    label: (parseInt(this.anneeDeReference) + i).toString() + ' Redevances',
                    prop: `periodiques.redevances[${i}]`
                });
                this.columns.push({
                    label: (parseInt(this.anneeDeReference) + i).toString() + ' TFPB',
                    prop: `periodiques.tfpb[${i}]`
                });
                this.columns.push({
                    label: (parseInt(this.anneeDeReference) + i).toString() + ' Maintenance courante',
                    prop: `periodiques.maintenanceCourante[${i}]`
                });
                this.columns.push({
                    label: (parseInt(this.anneeDeReference) + i).toString() + ' Gros entretien',
                    prop: `periodiques.grosEntretien[${i}]`
                });
            }
        },
        getTypeEmprunts() {
            this.$apollo.query({
                query: require('../../../../../graphql/simulations/types-emprunts/typesEmprunts.gql'),
                variables: {
                    simulationID: this.simulationID
                }
            }).then((res) => {
                if (res.data && res.data.typesEmprunts) {
                    this.typesEmprunts = res.data.typesEmprunts.items;
                }
            });
        },
        tableData(data, query) {
            if (!_.isNil(data)) {
                this.query = query;
                const demolitionFoyers = data.demolitionFoyers.items.map(item => {
                    let partCapital = [];
                    let partInterets = [];
                    let redevances = [];
                    let tfpb = [];
                    let maintenanceCourante = [];
                    let grosEntretien = [];
                    item.periodique.items.forEach(periodique => {
                        partCapital[periodique.iteration - 1] = periodique.partCapital;
                        partInterets[periodique.iteration - 1] = periodique.partInterets;
                        redevances[periodique.iteration - 1] = periodique.redevances;
                        tfpb[periodique.iteration - 1] = periodique.tfpb;
                        maintenanceCourante[periodique.iteration - 1] = periodique.maintenanceCourante;
                        grosEntretien[periodique.iteration - 1] = periodique.grosEntretien;
                    });
                    let row = {...item};
                    row.typeEmpruntDemolitionFoyers = item.typeEmpruntDemolitionFoyers.items;
                    row.periodiques = {
                        partCapital,
                        partInterets,
                        redevances,
                        tfpb,
                        maintenanceCourante,
                        grosEntretien
                    };

                    return row;
                });
                this.demolitionFoyers = demolitionFoyers;
                return demolitionFoyers;
            } else {
                return [];
            }
        },
        showCreateModal() {
            this.initForm();
            this.dialogVisible = true;
            this.isEdit = false;
            this.getAvailableTypesEmprunts();
        },
        handleEdit(index, row) {
            this.dialogVisible = true;
            this.form = {...row};
            this.selectedIndex = index;
            this.isEdit = true;
            this.getAvailableTypesEmprunts();
        },
        handleDelete(index, row) {
            this.$confirm('Êtes-vous sûr de vouloir supprimer cette démolition foyer?')
                .then(() => {
                    this.$apollo.mutate({
                        mutation: require('../../../../../graphql/simulations/foyers/demolitions/removeDemolitionFoyer.gql'),
                        variables: {
                            demolitionFoyerUUID: row.id,
                            simulationId: this.simulationID
                        }
                    }).then(() => {
                        updateData(this.query, this.simulationID).then(() => {
                            this.$message({
                                showClose: true,
                                message: 'Cette démolition a bien été supprimée.',
                                type: 'success'
                            });
                        })
                    }).catch(error => {
                        updateData(this.query, this.simulationID).then(() => {
                            this.$message({
                                showClose: true,
                                message:'Cette démolition n\'existe pas.',
                                type: 'error',
                            });
                        });
                    });
                })
        },
        save(formName) {
            this.$refs[formName].validate((valid) => {
                if (valid && checkAllPeriodics(this.form.periodiques)) {
                    this.isSubmitting = true;
                    const typeEmpruntDemolitionFoyers = this.form.typeEmpruntDemolitionFoyers.map(item => {
                        return JSON.stringify({
                            id: item.typesEmprunts.id,
                            montant: item.montant,
                            datePremiere: item.datePremiere
                        })
                    });
                    this.$apollo.mutate({
                        mutation: require('../../../../../graphql/simulations/foyers/demolitions/saveDemolitionFoyer.gql'),
                        variables: {
                            demolitionFoyer: {
                                uuid: this.form.id,
                                simulationId: this.simulationID,
                                numero: this.form.numero,
                                nomIntervention: this.form.nomIntervention,
                                nombreLogements: this.form.nombreLogements,
                                date: this.form.date,
                                remboursementAnticipe: this.form.remboursementAnticipe,
                                indexationIcc: this.form.indexationIcc,
                                prixRevient: this.form.prixRevient,
                                fondsPropres: this.form.fondsPropres,
                                subventionsEtat: this.form.subventionsEtat,
                                subventionsAnru: this.form.subventionsAnru,
                                subventionsEpci: this.form.subventionsEpci,
                                subventionsDepartement: this.form.subventionsDepartement,
                                subventionsRegion: this.form.subventionsRegion,
                                subventionsCollecteur: this.form.subventionsCollecteur,
                                autresSubventions: this.form.autresSubventions,
                                tauxEvolutionTfpb: this.form.tauxEvolutionTfpb,
                                tauxEvolutionMaintenance: this.form.tauxEvolutionMaintenance,
                                tauxEvolutionGrosEntretien: this.form.tauxEvolutionGrosEntretien,
                                totalEmprutns: this.totalMontant,
                                typeEmprunts: typeEmpruntDemolitionFoyers,
                                periodique: JSON.stringify({
                                    part_capital: this.form.periodiques.partCapital,
                                    part_interets: this.form.periodiques.partInterets,
                                    redevances: this.form.periodiques.redevances,
                                    tfpb: this.form.periodiques.tfpb,
                                    maintenance_courante: this.form.periodiques.maintenanceCourante,
                                    gros_entretien: this.form.periodiques.grosEntretien
                                })
                            }
                        }
                    }).then(() => {
                        this.dialogVisible = false;
                        this.isSubmitting = false;
                        updateData(this.query, this.simulationID).then(() => {
                            this.$message({
                                showClose: true,
                                message: 'Cette démolition a bien été enregistrée.',
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
        addTypeEmprunt() {
            if (this.form.typeEmprunt && this.form.montant && this.form.datePremiere) {
                const typeEmprunt = this.availableTypesEmprunts.find(item => item.id == this.form.typeEmprunt);
                this.form.typeEmpruntDemolitionFoyers.push({
                    montant: this.form.montant | 0,
                    datePremiere: this.form.datePremiere,
                    typesEmprunts: typeEmprunt,
                    local: true
                });
                this.form.typeEmprunt = null;
                this.getAvailableTypesEmprunts();
            }
        },
        handleDeleteEmprunt(index, row) {
            this.$confirm('Êtes-vous sûr de vouloir supprimer ce type d’emprunt?')
                .then(_ => {
                    if (row.local) {
                        this.form.typeEmpruntDemolitionFoyers.splice(index, 1);
                        this.getAvailableTypesEmprunts();
                    } else {
                        this.$apollo.mutate({
                            mutation: require('../../../../../graphql/simulations/foyers/demolitions/removeTypeDempruntDemolitionFoyer.gql'),
                            variables: {
                                typesEmpruntsUUID: row.typesEmprunts.id,
                                demolitionFoyerUUID: this.demolitionFoyers[this.selectedIndex].id
                            }
                        }).then((res) => {
                            this.form.typeEmpruntDemolitionFoyers.splice(index, 1);
                            this.getAvailableTypesEmprunts();
                        });
                    }
                })
                .catch(_ => {});
        },
        getAvailableTypesEmprunts() {
            let emprunts = [];
            const linkedEmprunts = this.form.typeEmpruntDemolitionFoyers;
            this.typesEmprunts.forEach(item => {
                if(!linkedEmprunts.some(emprunt => emprunt.typesEmprunts.id === item.id)) {
                    emprunts.push(item);
                }
            });
            this.availableTypesEmprunts = emprunts;
        },
        checkExistNom(value) {
            let demolitionFoyers = this.demolitionFoyers;
            if (this.isEdit) {
                demolitionFoyers = demolitionFoyers.filter(item => item.nomIntervention !== this.demolitionFoyers[this.selectedIndex].nomIntervention);
            }
            return demolitionFoyers.some(item => item.nomIntervention === value);
        },
        back() {
            if (this.selectedIndex > 0) {
                this.selectedIndex--;
                this.form = {...this.demolitionFoyers[this.selectedIndex]};
            }
        },
        next() {
            if (this.selectedIndex < (this.demolitionFoyers.length - 1)) {
                this.selectedIndex++;
                this.form = {...this.demolitionFoyers[this.selectedIndex]};
            }
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
        }
    },
    computed: {
        totalMontant() {
            let emprunt = 0;
            this.form.typeEmpruntDemolitionFoyers.forEach(item => {
                emprunt += getFloat(item.montant);
            });
            return emprunt;
        },
        totalSubventions() {
            return getFloat(this.form.subventionsEtat) + getFloat(this.form.subventionsAnru) + getFloat(this.form.subventionsEpci) + getFloat(this.form.subventionsDepartement) + getFloat(this.form.subventionsRegion) + getFloat(this.form.subventionsCollecteur) + getFloat(this.form.autresSubventions);
        },
        resteFinancer() {
            return getFloat(this.form.prixRevient) - (getFloat(this.form.fondsPropres) + this.totalSubventions + this.totalMontant);
        },
        isModify() {
            return this.$store.getters['choixEntite/isModify'];
        }
    },
    filters: {
        dateFR: function(value) {
            return value ? moment.utc(String(value)).format("MM/YYYY") : "";
        }
    }
}
</script>

<style>
    .demolition-foyers .el-form-item__label {
        margin-top: 7px;
    }
    .demolition-foyers .form-slider i {
        font-size: 25px;
        margin-left: 50px;
        cursor: pointer;
    }
    .demolition-foyers .fixed-input {
        width: 80px;
    }
    .demolition-foyers .carousel-head {
        height: 50px;
    }
    .demolition-foyers .el-form-item__error {
        left: -140px;
    }
    .demolition-foyers .el-tooltip.el-icon-info {
        font-size: 25px;
        color: #2491eb;
        vertical-align: middle;
    }
    .demolition-foyers .el-collapse-item__header{
        padding-left: 15px;
        font-weight: bold;
        font-size: 16px;
        color: #00436f;
        margin-top: 20px
    }
    .demolition-foyers .el-collapse-item__header:hover{
        background-color: rgba(0, 0, 0, 0.03);
    }
    .demolition-foyers .el-collapse-item__content {
        padding-top: 20px;
    }
</style>
