<template>
    <div class="demolition-identifiees">
        <div v-if="error">Une erreur est survenue !</div>
        <el-table
            v-loading="isLoading"
            :data="tableData(data)"
            style="width: 100%">
            <el-table-column sortable column-key="nGroupe" prop="nGroupe" min-width="100" label="N° groupe">
                <template slot="header">
                    <span title="N° groupe">N° groupe</span>
                </template>
            </el-table-column>
            <el-table-column sortable column-key="nSousGroupe" prop="nSousGroupe" min-width="120" label="N° sous groupe">
                <template slot="header">
                    <span title="N° sous groupe">N° sous groupe</span>
                </template>
            </el-table-column>
            <el-table-column sortable column-key="nomGroupe" prop="nomGroupe" min-width="150" label="Nom du groupe">
                <template slot="header">
                    <span title="Nom du groupe">Nom du groupe</span>
                </template>
            </el-table-column>
            <el-table-column sortable column-key="information" prop="information" min-width="150" label="Informations">
                <template slot="header">
                    <span title="Informations">Informations</span>
                </template>
            </el-table-column>
            <el-table-column sortable column-key="numero" prop="numero" min-width="100" label="N° tranche">
                <template slot="header">
                    <span title="N° tranche">N° tranche</span>
                </template>
            </el-table-column>
            <el-table-column sortable column-key="nomTranche" prop="nomTranche" min-width="150" label="Nom de la tranche">
                <template slot="header">
                    <span title="Nom de la tranche">Nom de la tranche</span>
                </template>
            </el-table-column>
            <el-table-column sortable column-key="conventionAnru" prop="conventionAnru" min-width="150" label="Conventions ANRU">
                <template slot-scope="scope">
                    <span>{{scope.row.conventionAnru ? 'Oui' : 'Non'}}</span>
                </template>
                <template slot="header">
                    <span title="Conventions ANRU">Conventions ANRU</span>
                </template>
            </el-table-column>
            <el-table-column sortable column-key="surfaceDemolie" prop="surfaceDemolie" min-width="150" label="Surface démolie">
                <template slot="header">
                    <span title="N°">N°</span>
                </template>
            </el-table-column>
            <el-table-column sortable column-key="nombreLogementDemolis" prop="nombreLogementDemolis" min-width="170" label="Nombre de logements">
                <template slot="header">
                    <span title="Surface démolie">Surface démolie</span>
                </template>
            </el-table-column>
            <el-table-column sortable column-key="dateDemolution" prop="dateDemolution" min-width="150" label="Date de démolition">
                <template slot-scope="scope">
                    <span>{{scope.row.dateDemolution | dateFR}}</span>
                </template>
                <template slot="header">
                    <span title="Date de démolition">Date de démolition</span>
                </template>
            </el-table-column>
            <el-table-column sortable column-key="coutDemolution" prop="coutDemolution" min-width="150" label="Coût de la démolition">
                <template slot="header">
                    <span title="Coût de la démolition">Coût de la démolition</span>
                </template>
            </el-table-column>
            <el-table-column sortable column-key="remboursementCrd" prop="remboursementCrd" min-width="150" label="Remboursement de CRD">
                <template slot="header">
                    <span title="Remboursement de CRD">Remboursement de CRD</span>
                </template>
            </el-table-column>
            <el-table-column sortable column-key="coutAnnexes" prop="coutAnnexes" min-width="150" label="Coûts annexes">
                <template slot="header">
                    <span title="Coûts annexes">Coûts annexes</span>
                </template>
            </el-table-column>
            <el-table-column sortable column-key="remboursementSubventions" prop="remboursementSubventions" min-width="150" label="Remboursement de subventions">
                <template slot="header">
                    <span title="Remboursement de subventions">Remboursement de subventions</span>
                </template>
            </el-table-column>
            <el-table-column sortable column-key="foundsPropres" prop="foundsPropres" min-width="140" label="Fonds propres">
                <template slot="header">
                    <span title="Fonds propres">Fonds propres</span>
                </template>
            </el-table-column>
            <el-table-column sortable column-key="subventionsEtat" prop="subventionsEtat" min-width="120" label="Subventions d'Etat">
                <template slot="header">
                    <span title="Subventions d'Etat">Subventions d'Etat</span>
                </template>
            </el-table-column>
            <el-table-column sortable column-key="subventionsAnru" prop="subventionsAnru" min-width="120" label="Subventions ANRU">
                <template slot="header">
                    <span title="Subventions ANRU">Subventions ANRU</span>
                </template>
            </el-table-column>
            <el-table-column sortable column-key="subventionsEpci" prop="subventionsEpci" min-width="140" label="Subventions EPCI / Commune">
                <template slot="header">
                    <span title="Subventions EPCI / Commune">Subventions EPCI / Commune</span>
                </template>
            </el-table-column>
            <el-table-column sortable column-key="subventionsDepartement" prop="subventionsDepartement" min-width="120" label=" Subventions département">
                <template slot="header">
                    <span title="Subventions département">Subventions département</span>
                </template>
            </el-table-column>
            <el-table-column sortable column-key="subventionsRegion" prop="subventionsRegion" min-width="120" label="Subventions Région">
                <template slot="header">
                    <span title="Subventions Région">Subventions Région</span>
                </template>
            </el-table-column>
            <el-table-column sortable column-key="subventionsCollecteur" prop="subventionsCollecteur" min-width="120" label="Subventions collecteur">
                <template slot="header">
                    <span title="Subventions collecteur">Subventions collecteur</span>
                </template>
            </el-table-column>
            <el-table-column sortable column-key="autresSubventions" prop="autresSubventions" min-width="120" label="Autres subventions">
                <template slot="header">
                    <span title="Autres subventions">Autres subventions</span>
                </template>
            </el-table-column>
            <el-table-column sortable column-key="tfpb" prop="tfpb" min-width="150" label="TFPB en €/logt">
                <template slot="header">
                    <span title="TFPB en €/logt">TFPB en €/logt</span>
                </template>
            </el-table-column>
            <el-table-column sortable column-key="maintenanceCourante" prop="maintenanceCourante" min-width="150" label="Maintenance courante en €/logt">
                <template slot="header">
                    <span title="Maintenance courante en €/logt">Maintenance courante en €/logt</span>
                </template>
            </el-table-column>
            <el-table-column sortable column-key="grosEntretien" prop="grosEntretien" min-width="150" label="Gros entretien en €/logt">
                <template slot="header">
                    <span title="Gros entretien en €/logt">Gros entretien en €/logt</span>
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

        <el-row class="d-flex justify-content-end my-3">
            <el-button type="primary" :disabled="!isModify" @click="showCreateModal">Créer une démolition identifiée</el-button>
        </el-row>

        <el-dialog
            :title="dialogTitle"
            :visible.sync="dialogVisible"
            :close-on-click-modal="false"
            width="75%">
            <el-row v-if="isEdit" class="form-slider text-center mb-4">
                <i class="el-icon-back font-weight-bold" @click="back"></i>
                <i class="el-icon-right font-weight-bold" @click="next"></i>
            </el-row>
            <el-form label-position="left" :model="form" :rules="formRules" label-width="180px" ref="demolitionForm">
                <el-tabs v-model="activeTab" type="card">
                    <el-tab-pane label="Caractéristiques Générales" name="1">
                        <el-collapse v-model="collapseList">
                            <el-collapse-item title="Données Générales" name="1">
                                <el-row :gutter="20">
                                    <el-col :span="6">
                                        <el-form-item label="N° groupe" prop="nGroupe">
                                            <el-select v-model="form.nGroupe" @change="changeNgroupe">
                                                <el-option v-for="item in patrimoines"
                                                    :key="item.id"
                                                    :label="item.nGroupe"
                                                    :value="item.nGroupe"></el-option>
                                            </el-select>
                                        </el-form-item>
                                        <el-checkbox v-model="form.conventionAnru">Convention ANRU</el-checkbox>
                                    </el-col>
                                    <el-col :span="6">
                                        <el-form-item label="N° sous groupe" prop="nSousGroupe">
                                            <el-input type="text" v-model="form.nSousGroupe" placeholder="N° sous groupe" readonly></el-input>
                                        </el-form-item>
                                    </el-col>
                                    <el-col :span="6">
                                        <el-form-item label="Nom du groupe" prop="nomGroupe">
                                            <el-input type="text" v-model="form.nomGroupe" readonly></el-input>
                                        </el-form-item>
                                    </el-col>
                                    <el-col :span="6">
                                        <el-form-item label="Informations" prop="information">
                                            <el-input type="text" v-model="form.information" readonly></el-input>
                                        </el-form-item>
                                    </el-col>
                                </el-row>
                            </el-collapse-item>
                            <el-collapse-item title="Caractéristiques de la tranche" name="2">
                                <el-row :gutter="20">
                                    <el-col :span="8">
                                        <el-form-item label="N° de la tranche" prop="numero">
                                            <el-input type="text" v-model="form.numero" placeholder="0" class="fixed-input"></el-input>
                                        </el-form-item>
                                        <el-form-item label="Nombre de logements démolis" prop="nombreLogementDemolis">
                                            <el-input type="text" v-model="form.nombreLogementDemolis" placeholder="0" class="fixed-input"
                                                      @change="formatInput('nombreLogementDemolis')"></el-input>
                                        </el-form-item>
                                    </el-col>
                                     <el-col :span="8">
                                        <el-form-item label="Nom de la tranche" prop="nomTranche">
                                            <el-input type="text" v-model="form.nomTranche"></el-input>
                                        </el-form-item>
                                        <el-form-item label="Date de démolition" prop="dateDemolution">
                                            <el-date-picker
                                              v-model="form.dateDemolution"
                                              type="month"
                                              format="MM/yyyy"
                                              :picker-options="datePickerOptions"
                                              placeholder="Date de démolition">
                                            </el-date-picker>
                                        </el-form-item>
                                    </el-col>
                                     <el-col :span="8">
                                        <el-form-item label="Surface démolie en m²" prop="surfaceDemolie">
                                            <el-input type="text" v-model="form.surfaceDemolie" placeholder="0" class="fixed-input"
                                                      @change="formatInput('surfaceDemolie')"></el-input>
                                        </el-form-item>
                                    </el-col>
                                </el-row>
                            </el-collapse-item>
                            <el-collapse-item title="Réductions liées aux démolitions" name="3">
                                <div class="row">
                                    <div class="col-sm-4">
                                        <el-form-item label="TFPB en €/logt" prop="tfpb">
                                            <el-input type="text" v-model="form.tfpb" placeholder="0" class="fixed-input"
                                                      @change="formatInput('tfpb')"></el-input>
                                        </el-form-item>
                                    </div>
                                    <div class="col-sm-4">
                                        <el-form-item label="Maintenance courante en €/logt" prop="maintenanceCourante">
                                            <el-input type="text" v-model="form.maintenanceCourante" placeholder="0" class="fixed-input"
                                                      @change="formatInput('maintenanceCourante')"></el-input>
                                        </el-form-item>
                                    </div>
                                    <div class="col-sm-4">
                                        <el-form-item label="Gros entretien en €/logt" prop="grosEntretien">
                                            <el-input type="text" v-model="form.grosEntretien" placeholder="0" class="fixed-input"
                                                      @change="formatInput('grosEntretien')"></el-input>
                                        </el-form-item>
                                    </div>
                                </div>
                                <el-row class="mt-4">
                                    <el-col :span="5" style="padding-top: 55px;">
                                        <div class="carousel-head">Economies d'annuités cumulées suite RA - Part K</div>
                                        <div class="carousel-head">Economies d'annuités cumulées suite RA - Part i</div>
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
                            <el-collapse-item title="Investissements en K€" name="1">
                                <el-row :gutter="20">
                                    <el-col :span="6">
                                        <el-form-item label="Coût de la démolition" prop="coutDemolution">
                                            <el-input type="text" v-model="form.coutDemolution" placeholder="0" class="fixed-input"
                                                      @change="formatInput('coutDemolution')"></el-input>
                                        </el-form-item>
                                        <el-checkbox v-model="form.indexationIcc">Indexation à I'ICC</el-checkbox>
                                    </el-col>
                                    <el-col :span="6">
                                        <el-form-item label="Coûts annexes" prop="coutAnnexes">
                                            <el-input type="text" v-model="form.coutAnnexes" placeholder="0" class="fixed-input"
                                                      @change="formatInput('coutAnnexes')"></el-input>
                                        </el-form-item>
                                    </el-col>
                                    <el-col :span="6">
                                        <el-form-item label="Remboursement CRD" prop="remboursementCrd">
                                            <el-input type="text" v-model="form.remboursementCrd" placeholder="0" class="fixed-input"
                                                      @change="formatInput('remboursementCrd')"></el-input>
                                        </el-form-item>
                                    </el-col>
                                    <el-col :span="6">
                                        <el-form-item label="Remboursement subventions" prop="remboursementSubventions">
                                            <el-input type="text" v-model="form.remboursementSubventions" placeholder="0" class="fixed-input"
                                                      @change="formatInput('remboursementSubventions')"></el-input>
                                        </el-form-item>
                                    </el-col>
                                </el-row>
                            </el-collapse-item>
                            <el-collapse-item title="Financements en K€" name="2">
                                <el-row class="mt-4">
                                    <el-col :span="13">
                                        <div class="row">
                                            <div class="col-sm-6">
                                                <el-form-item label="Montant de fonds propres" prop="foundsPropres">
                                                    <el-input type="text" v-model="form.foundsPropres" placeholder="0" class="fixed-input"
                                                              @change="formatInput('foundsPropres')"></el-input>
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
                                                <p><strong>Montant des Subventions en k€</strong></p>
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
                                    </el-col>
                                    <el-col :span="11">
                                        <p><strong>Emprunts en K€</strong></p>
                                        <span class="form-label">Ajouter un emprunt</span>
                                        <el-select v-model="form.typeEmprunt" class="w-100 mb-2">
                                            <el-option v-for="item in availableTypesEmprunts"
                                                :key="item.id"
                                                :label="item.nom"
                                                :value="item.id"></el-option>
                                        </el-select>
                                        <div class="d-flex">
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
                                                <el-form-item label="Date de la 1ère annuité" label-width="105px" prop="datePremier">
                                                    <el-date-picker
                                                        v-model="form.datePremier"
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
                                            :data="form.typeEmpruntDemolition"
                                            style="width: 100%">
                                            <el-table-column sortable column-key="typesEmprunts" prop="typesEmprunts" min-width="60" label="Numéro">
                                                <template slot-scope="scope">
                                                    {{scope.row.typesEmprunts.nom}}
                                                </template>
                                            </el-table-column>
                                            <el-table-column sortable column-key="montant" prop="montant" min-width="100" label="Montant emprunt" align="center"></el-table-column>
                                            <el-table-column sortable column-key="datePremiere" prop="datePremiere" min-width="100" label="Date de la 1ère annuité" align="center">
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
                                    </el-col>
                                </el-row>
                            </el-collapse-item>
                        </el-collapse>
                    </el-tab-pane>
                </el-tabs>
            </el-form>
            <div slot="footer" class="dialog-footer">
                <el-button type="outline-primary" @click="dialogVisible = false">Retour</el-button>
                <el-button type="primary" @click="save('demolitionForm')" :disabled="isSubmitting">Valider</el-button>
            </div>
        </el-dialog>
    </div>
</template>

<script>
import moment from "moment";
import { initPeriodic, periodicFormatter, mathInput, checkAllPeriodics } from '../../../../../../../utils/inputs';
import { updateData, getFloat } from '../../../../../../../utils/helpers'
import customValidator from '../../../../../../../utils/validation-rules';
import Periodique from '../../../../../../../components/partials/Periodique';

export default {
    name: "DemolitionIdentifiees",
    components: { Periodique },
    props: ['simulationID', 'anneeDeReference', 'typesEmprunts', 'patrimoines', 'data', 'error', 'isLoading', 'query'],
    data() {
        var resteFinancerValidate = (rule, value, callback) => {
            if (this.resteFinancer !== 0) {
                callback(new Error("le plan de financement n’est pas équilibré"));
            } else {
                callback();
            }
        };
        var nombreLogementDemolisValidate = (rule, value, callback) => {
            if (value && this.nombreLogementsLimit && value > this.nombreLogementsLimit) {
                callback(new Error("Le nombre de logements démolis ne peut pas dépasser " + this.nombreLogementsLimit));
            } else {
                callback();
            }
        };
        return {
            identifees: [],
            dialogVisible: false,
            collapseList: ['1'],
            form: null,
            isEdit: false,
            selectedIndex: null,
            activeTab: '1',
            isSubmitting: false,
            nombreLogementsLimit: null,
            availableTypesEmprunts: [],
            periodiqueHasError: false,
            columns: [],
            datePickerOptions: {
            },
            formRules: {
                nGroupe: customValidator.getRule('required'),
                numero: customValidator.getPreset('number.positiveInt'),
                coutDemolution: customValidator.getPreset('number.positiveDouble'),
                foundsPropres: customValidator.getPreset('number.positiveDouble'),
                subventionsEtat: customValidator.getRule('positiveDouble'),
                subventionsAnru: customValidator.getRule('positiveDouble'),
                subventionsEpci: customValidator.getRule('positiveDouble'),
                subventionsDepartement: customValidator.getRule('positiveDouble'),
                subventionsRegion: customValidator.getRule('positiveDouble'),
                subventionsCollecteur: customValidator.getRule('positiveDouble'),
                autresSubventions: customValidator.getRule('positiveDouble'),
                nombreLogementDemolis: [
                    { validator: nombreLogementDemolisValidate, trigger: 'change' }
                ],
                resteFinancer: [
                    { validator: resteFinancerValidate, trigger: 'change' }
                ]
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
                nGroupe: null,
                nSousGroupe: null,
                nomGroupe: '',
                information: '',
                numero: null,
                nomTranche: '',
                conventionAnru: true,
                surfaceDemolie: null,
                nombreLogementDemolis: null,
                dateDemolution: new Date(),
                indexationIcc: true,
                coutDemolution: null,
                remboursementCrd: null,
                coutAnnexes: null,
                remboursementSubventions: null,
                foundsPropres: null,
                subventionsEtat: null,
                subventionsAnru: null,
                subventionsEpci: null,
                subventionsDepartement: null,
                subventionsRegion: null,
                subventionsCollecteur: null,
                autresSubventions: null,
                montant: null,
                datePremier: new Date(),
                tfpb: null,
                maintenanceCourante: null,
                grosEntretien: null,
                typeEmpruntDemolition: [],
                periodiques: {
                    partCapital: initPeriodic(),
                    partInterets: initPeriodic()
                }
            };
        },
        tableData(data) {
            if (!_.isNil(data)) {
                let demolitions = data.demolitions.items.map(item => {
                    if (item.type === 0) {
                        let partCapital = [];
                        let partInterets = [];
                        item.demolitionPeriodique.items.forEach(periodique => {
                            partCapital[periodique.iteration - 1] = periodique.partCapital;
                            partInterets[periodique.iteration - 1] = periodique.partInterets;
                        });

                        let row = {...item};
                        row.typeEmpruntDemolition = item.typeEmpruntDemolition.items;
                        row.periodiques = {
                            partCapital,
                            partInterets
                        };
                        return row;
                    } else {
                        return false;
                    }
                });
                demolitions = demolitions.filter((item) => item);
                this.demolitions = demolitions ;
                return demolitions;
            } else {
                return [];
            }
        },
        save(formName) {
            this.$refs[formName].validate((valid) => {
                if (valid && checkAllPeriodics(this.form.periodiques)) {
                    this.isSubmitting = true;
                    const typeEmpruntDemolition = this.form.typeEmpruntDemolition.map(item => {
                        return JSON.stringify({
                            id: item.typesEmprunts.id,
                            montant: item.montant,
                            datePremiere: item.datePremiere
                        })
                    });
                    this.$apollo.mutate({
                        mutation: require('../../../../../../../graphql/simulations/logements-familiaux/demolitions/saveDemolition.gql'),
                        variables: {
                            demolition: {
                                simulationId: this.simulationID,
                                uuid: this.form.id,
                                nGroupe: this.form.nGroupe,
                                nSousGroupe: this.form.nSousGroupe,
                                nomGroupe: this.form.nomGroupe,
                                information: this.form.information,
                                numero: this.form.numero,
                                nomTranche: this.form.nomTranche,
                                conventionAnru: this.form.conventionAnru,
                                surfaceDemolie: this.form.surfaceDemolie,
                                nombreLogementDemolis: this.form.nombreLogementDemolis,
                                dateDemolution: this.form.dateDemolution,
                                indexationIcc: this.form.indexationIcc,
                                coutDemolution: this.form.coutDemolution,
                                remboursementCrd: this.form.remboursementCrd,
                                coutAnnexes: this.form.coutAnnexes,
                                remboursementSubventions: this.form.remboursementSubventions,
                                foundsPropres: this.form.foundsPropres,
                                subventionsEtat: this.form.subventionsEtat,
                                subventionsAnru: this.form.subventionsAnru,
                                subventionsEpci: this.form.subventionsEpci,
                                subventionsDepartement: this.form.subventionsDepartement,
                                subventionsRegion: this.form.subventionsRegion,
                                subventionsCollecteur: this.form.subventionsCollecteur,
                                autresSubventions: this.form.autresSubventions,
                                tfpb: this.form.tfpb,
                                maintenanceCourante: this.form.maintenanceCourante,
                                grosEntretien: this.form.grosEntretien,
                                type: 0,
                                typeEmprunts: typeEmpruntDemolition,
                                periodique: JSON.stringify({part_capital: this.form.periodiques.partCapital, part_interets: this.form.periodiques.partInterets})
                            }
                        }
                    }).then(() => {
                        this.isSubmitting = false;
                        this.dialogVisible = false;
                        updateData(this.query, this.simulationID).then(() => {
                            this.$message({
                                showClose: true,
                                message: 'La démolition identifiée a bien été enregistrée.',
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
            this.$confirm('Êtes-vous sûr de vouloir supprimer cette démolition identifiée?')
                .then(_ => {
                    this.$apollo.mutate({
                        mutation: require('../../../../../../../graphql/simulations/logements-familiaux/demolitions/removeDemolition.gql'),
                        variables: {
                            demolitionUUID: row.id,
                            simulationId: this.simulationID,
                        }
                    }).then(() => {
                        updateData(this.query, this.simulationID).then(() => {
                            this.$message({
                                showClose: true,
                                message: 'Cette démolition identifiée a bien été supprimée.',
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
        changeNgroupe(nGroupe) {
            const patrimoine = this.patrimoines.find(item => item.nGroupe === this.form.nGroupe);
            let form = this.form;
            if (patrimoine) {
                form.nSousGroupe = patrimoine.nSousGroupe;
                form.nomGroupe = patrimoine.nomGroupe;
                form.information = patrimoine.informations;
                this.nombreLogementsLimit = patrimoine.nombreLogements;
            } else {
                form.nSousGroupe = null;
                form.nomGroupe = '';
                form.information = '';
                this.nombreLogementsLimit = null;
            }
            this.form = null;
            this.form = form;
        },
        addTypeEmprunt() {
            if (this.form.typeEmprunt) {
                const typeEmprunt = this.availableTypesEmprunts.find(item => item.id == this.form.typeEmprunt);
                this.form.typeEmpruntDemolition.push({
                    montant: this.form.montant | 0,
                    datePremiere: this.form.datePremier,
                    typesEmprunts: typeEmprunt,
                    local: true
                });
                this.form.typeEmprunt = null;
                this.getTypesEmprunts();
            }
        },
        periodicOnChange(type) {
            let newPeriodics = this.form.periodiques[type];
            this.form.periodiques[type] = [];
            this.form.periodiques[type] = periodicFormatter(newPeriodics);
        },
        handleDeleteEmprunt(index, row) {
            this.$confirm('Êtes-vous sûr de vouloir supprimer ce type d’emprunt?')
                .then(_ => {
                    if (row.local) {
                        this.form.typeEmpruntDemolition.splice(index, 1);
                        this.getTypesEmprunts();
                    } else {
                        this.$apollo.mutate({
                            mutation: require('../../../../../../../graphql/simulations/logements-familiaux/demolitions/removeTypeDempruntDemolition.gql'),
                            variables: {
                                typesEmpruntsUUID: row.typesEmprunts.id,
                                demolitionUUID: this.identifees[this.selectedIndex].id
                            }
                        }).then((res) => {
                            this.form.typeEmpruntDemolition.splice(index, 1);
                            this.getTypesEmprunts();
                        });
                    }
                })
                .catch(_ => {});
        },
        getTypesEmprunts() {
            let emprunts = [];
            const linkedEmprunts = this.form.typeEmpruntDemolition;
            this.typesEmprunts.forEach(item => {
                if(!linkedEmprunts.some(emprunt => emprunt.typesEmprunts.id === item.id)) {
                    emprunts.push(item);
                }
            });
            this.availableTypesEmprunts = emprunts;
        },
        back() {
            if (this.selectedIndex > 0) {
                this.selectedIndex--;
                this.form = {...this.identifees[this.selectedIndex]};
            }
        },
        next() {
            if (this.selectedIndex < (this.identifees.length - 1)) {
                this.selectedIndex++;
                this.form = {...this.identifees[this.selectedIndex]};
            }
        },
        setTableColumns() {
            this.columns = [];
            for (var i = 0; i < 50; i++) {
                this.columns.push({
                    label: (parseInt(this.anneeDeReference) + i).toString() + ' Economies d\'annuités suite à RA-part capital',
                    prop: `periodiques.partCapital[${i}]`
                });
                this.columns.push({
                    label: (parseInt(this.anneeDeReference) + i).toString() + ' Econimies d\'anuuités suite à RA-part intéréts',
                    prop: `periodiques.partInterets[${i}]`
                });
            }
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
        },
        formatInput(type) {
            this.form[type] = mathInput(this.form[type]);
        },
        exportIdentifees() {
            window.location.href = "/export-identifees/" + this.simulationID;
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
    },
    computed: {
        dialogTitle() {
            return this.isEdit ? 'Modifier une démolition identifiée' : 'Créer une démolition identifiée';
        },
        totalMontant() {
            let emprunt = 0;
            this.form.typeEmpruntDemolition.forEach(item => {
                emprunt += getFloat(item.montant);
            });
            return emprunt;
        },
        totalSubventions() {
            return getFloat(this.form.subventionsEtat) + getFloat(this.form.subventionsAnru) + getFloat(this.form.subventionsEpci) + getFloat(this.form.subventionsDepartement) + getFloat(this.form.subventionsRegion) + getFloat(this.form.subventionsCollecteur) + getFloat(this.form.autresSubventions);
        },
        resteFinancer() {
            return getFloat(this.form.coutDemolution) - (getFloat(this.form.foundsPropres) + this.totalSubventions + this.totalMontant);
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
    .demolition-identifiees .el-dialog__body {
        padding-top: 0;
    }
    .demolition-identifiees .el-button--small {
        font-weight: 500;
        padding: 0 15px;
        height: 40px;
        font-size: 14px;
        border-radius: 4px;
        text-align: center;
        height: 40px;
    }
    /*.demolition-identifiees .el-form-item__error {
        left: -140px;
    }*/
</style>
