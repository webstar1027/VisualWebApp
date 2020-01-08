<template>
    <div class="nouveaux-foyers">
        <ApolloQuery
                :query="require('../../../../../graphql/simulations/foyers/nouveaux/nouveauxFoyers.gql')"
                :variables="{
                simulationId: simulationID
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
                    <el-table-column sortable column-key="nomIntervention" prop="nomIntervention" min-width="200" label="Nom de l'opération">
                        <template slot="header">
                            <span title="Nom de l'opération">Nom de l'opération</span>
                        </template>
                    </el-table-column>
                    <el-table-column sortable column-key="nature" prop="nature" min-width="200" label="Nature de l’opération">
                        <template slot="header">
                            <span title="Nature de l’opération">Nature de l’opération</span>
                        </template>
                    </el-table-column>
                    <el-table-column sortable column-key="nombreLogements" prop="nombreLogements" min-width="180" label="Nombre équivalent logements">
                        <template slot="header">
                            <span title="Nombre équivalent logements">Nombre équivalent logements</span>
                        </template>
                    </el-table-column>
                    <el-table-column sortable column-key="anneeAgrement" prop="anneeAgrement" min-width="180" label="Année d'agrément">
                        <template slot-scope="scope">
                            {{scope.row.anneeAgrement | dateFR}}
                        </template>
                        <template slot="header">
                            <span title="Année d'agrément">Année d'agrément</span>
                        </template>
                    </el-table-column>
                    <el-table-column sortable column-key="dateAcquisition" prop="dateAcquisition" min-width="160" label="Date d'ordre de service / Acquisition">
                        <template slot-scope="scope">
                            {{scope.row.dateAcquisition | dateFR}}
                        </template>
                        <template slot="header">
                            <span title="Date d'ordre de service / Acquisition">Date d'ordre de service / Acquisition</span>
                        </template>
                    </el-table-column>
                    <el-table-column sortable column-key="dateTravaux" prop="dateTravaux" min-width="180" label="Date de mise en service">
                        <template slot-scope="scope">
                            {{scope.row.dateTravaux | dateFR}}
                        </template>
                        <template slot="header">
                            <span title="Date de mise en service">Date de mise en service</span>
                        </template>
                    </el-table-column>
                    <el-table-column sortable column-key="indexationIcc" prop="indexationIcc" min-width="160" label="Indexation ICC">
                        <template slot-scope="scope">
                            {{scope.row.indexationIcc ? 'Oui': 'Non'}}
                        </template>
                        <template slot="header">
                            <span title="Indexation ICC">Indexation ICC</span>
                        </template>
                    </el-table-column>
                    <el-table-column sortable column-key="modeleDamortissementNom" prop="modeleDamortissementNom" min-width="160" label="Modèle d'amortissement technique">
                        <template slot="header">
                            <span title="Modèle d'amortissement technique">Modèle d'amortissement technique</span>
                        </template>
                    </el-table-column>
                    <el-table-column sortable column-key="prixRevient" prop="prixRevient" min-width="150" label="Prix de revient">
                        <template slot="header">
                            <span title="Prix de revient">Prix de revient</span>
                        </template>
                    </el-table-column>
                    <el-table-column sortable column-key="coutFoncier" prop="coutFoncier" min-width="150" label="Dont Coût du foncier">
                        <template slot="header">
                            <span title="Dont Coût du foncier">Dont Coût du foncier</span>
                        </template>
                    </el-table-column>
                    <el-table-column sortable column-key="fondsPropres" prop="fondsPropres" min-width="150" label="Fonds propres">
                        <template slot="header">
                            <span title="Fonds propres">Fonds propres</span>
                        </template>
                    </el-table-column>
                    <el-table-column sortable column-key="subventionsEtat" prop="subventionsEtat" min-width="180" label="Subventions d'Etat">
                        <template slot="header">
                            <span title="Subventions d'Etat">Subventions d'Etat</span>
                        </template>
                    </el-table-column>
                    <el-table-column sortable column-key="subventionsAnru" prop="subventionsAnru" min-width="180" label="Subventions ANRU">
                        <template slot="header">
                            <span title="Subventions ANRU">Subventions ANRU</span>
                        </template>
                    </el-table-column>
                    <el-table-column sortable column-key="subventionsEpci" prop="subventionsEpci" min-width="170" label="Subventions EPCI / Commune">
                        <template slot="header">
                            <span title="Subventions EPCI / Commune">Subventions EPCI / Commune</span>
                        </template>
                    </el-table-column>
                    <el-table-column sortable column-key="subventionsDepartement" prop="subventionsDepartement" min-width="170" label="Subventions département">
                        <template slot="header">
                            <span title="Subventions département">Subventions département</span>
                        </template>
                    </el-table-column>
                    <el-table-column sortable column-key="subventionsRegion" prop="subventionsRegion" min-width="180" label="Subventions Région">
                        <template slot="header">
                            <span title="Subventions Région">Subventions Région</span>
                        </template>
                    </el-table-column>
                    <el-table-column sortable column-key="subventionsCollecteur" prop="subventionsCollecteur" min-width="190" label="Subventions collecteur">
                        <template slot="header">
                            <span title="Subventions collecteur">Subventions collecteur</span>
                        </template>
                    </el-table-column>
                    <el-table-column sortable column-key="autresSubventions" prop="autresSubventions" min-width="170" label="Autres subventions">
                        <template slot="header">
                            <span title="Autres subventions">Autres subventions</span>
                        </template>
                    </el-table-column>
                    <el-table-column sortable column-key="totalEmprunt" prop="totalEmprunt" min-width="170" label="Total (Emprunt)">
                        <template slot="header">
                            <span title="Total (Emprunt)">Total (Emprunt)</span>
                        </template>
                    </el-table-column>
                    <el-table-column sortable column-key="redevancesTauxEvolution" prop="redevancesTauxEvolution" min-width="150" label="Taux d'évolution redevances">
                        <template slot="header">
                            <span title="Taux d'évolution redevances">Taux d'évolution redevances</span>
                        </template>
                    </el-table-column>
                    <el-table-column sortable column-key="tfpbTauxEvolution" prop="tfpbTauxEvolution" min-width="150" label="Taux d'évolution TFPB">
                        <template slot="header">
                            <span title="Taux d'évolution TFPB">Taux d'évolution TFPB</span>
                        </template>
                    </el-table-column>
                    <el-table-column sortable column-key="maintenanceTauxEvolution" prop="maintenanceTauxEvolution" min-width="150" label="Taux d'évolution Maintenance courante">
                        <template slot="header">
                            <span title="Taux d'évolution Maintenance courante">Taux d'évolution Maintenance courante</span>
                        </template>
                    </el-table-column>
                    <el-table-column sortable column-key="grosTauxEvolution" prop="grosTauxEvolution" min-width="150" label="Taux d'évolution Gros entretien">
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
            <el-button type="primary" :disabled="!isModify" @click="showCreateModal">Création d'une nouvelle fiche de nouveaux foyers</el-button>
        </el-row>
        <el-dialog
            title="Création/Modification des nouveaux foyers"
            :visible.sync="dialogVisible"
            :close-on-click-modal="false"
            width="75%">
            <el-row v-if="isEdit" class="form-slider text-center mb-4">
                <i class="el-icon-back font-weight-bold" @click="back"></i>
                <i class="el-icon-right font-weight-bold" @click="next"></i>
            </el-row>
            <el-form label-position="left" :model="form" :rules="formRules" label-width="180px" ref="nouveauxForm">
                <el-tabs v-model="activeTab" type="card">
                    <el-tab-pane label="Caractérisitiques Générales" name="1">
                        <el-collapse v-model="collapseList">
                            <el-collapse-item title="Données Générales" name="1">
                                <el-row :gutter="20">
                                    <el-col :span="6">
                                        <el-form-item label="N°" prop="numero">
                                            <el-input type="text" v-model="form.numero" placeholder="N°" readonly></el-input>
                                        </el-form-item>
                                    </el-col>
                                    <el-col :span="6">
                                        <el-form-item label="Nom de l'opération" prop="nomIntervention">
                                            <el-input type="text" v-model="form.nomIntervention" placeholder="Nom de l'opération"></el-input>
                                        </el-form-item>
                                    </el-col>
                                    <el-col :span="6">
                                        <el-form-item label="Nature de l’opération" prop="nature">
                                            <el-select v-model="form.nature">
                                                <el-option v-for="item in natures"
                                                    :key="item"
                                                    :label="item"
                                                    :value="item"></el-option>
                                            </el-select>
                                        </el-form-item>
                                    </el-col>
                                    <el-col :span="6">
                                        <el-form-item label="Nombre équivalent logement" prop="nombreLogements">
                                            <el-input type="text" v-model="form.nombreLogements" placeholder="0"
                                                      @change="formatInput('nombreLogements')" ></el-input>
                                        </el-form-item>
                                    </el-col>
                                </el-row>
                            </el-collapse-item>
                            <el-collapse-item title="Calendrier de l'opération" name="2">
                                <el-row :gutter="20">
                                    <el-col :span="8">
                                        <el-form-item label="Année d'Agrément" prop="anneeAgrement">
                                            <el-date-picker
                                                v-model="form.anneeAgrement"
                                                type="month"
                                                :picker-options="datePickerOptions"
                                                value-format="yyyy-MM-dd"
                                                format="MM/yyyy"
                                                placeholder="Sélectionner"
                                                >
                                            </el-date-picker>
                                        </el-form-item>
                                    </el-col>
                                    <el-col :span="8">
                                        <el-form-item label="Date d'ordre de service" prop="dateAcquisition">
                                            <el-date-picker
                                                v-model="form.dateAcquisition"
                                                type="month"
                                                :picker-options="datePickerOptions"
                                                value-format="yyyy-MM-dd"
                                                format="MM/yyyy"
                                                placeholder="Sélectionner">
                                            </el-date-picker>
                                        </el-form-item>
                                    </el-col>
                                    <el-col :span="8">
                                        <el-form-item label="Date de mise en service" prop="dateTravaux">
                                            <el-date-picker
                                                v-model="form.dateTravaux"
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
                            <el-collapse-item title="Compléments" name="3">
                                <el-row class="mt-4">
                                    <el-col :span="4" style="padding-top: 50px;">
                                        <div class="carousel-head">
                                            <p>Redevances</p>
                                        </div>
                                        <div class="carousel-head">
                                            <p>Compléments d'annuités - part capital</p>
                                        </div>
                                        <div class="carousel-head">
                                            <p>Compléments d'annuités - part intérêts</p>
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
                                            <el-form-item label-width="0" prop="redevancesTauxEvolution">
                                                <el-input type="text" v-model="form.redevancesTauxEvolution" placeholder="0.0" class="fixed-input"
                                                          @change="formatInput('redevancesTauxEvolution')" ></el-input>
                                            </el-form-item>
                                        </div>
                                        <div class="carousel-head" style="margin-top: 80px;">
                                            <el-form-item label-width="0" prop="tfpbTauxEvolution">
                                                <el-input type="text" v-model="form.tfpbTauxEvolution" placeholder="0.0" class="fixed-input"
                                                          @change="formatInput('tfpbTauxEvolution')" ></el-input>
                                            </el-form-item>
                                        </div>
                                        <div class="carousel-head">
                                            <el-form-item label-width="0" prop="maintenanceTauxEvolution">
                                                <el-input type="text" v-model="form.maintenanceTauxEvolution" placeholder="0.0" class="fixed-input"
                                                          @change="formatInput('maintenanceTauxEvolution')" ></el-input>
                                            </el-form-item>
                                        </div>
                                        <div class="carousel-head">
                                            <el-form-item label-width="0" prop="grosTauxEvolution">
                                                <el-input type="text" v-model="form.grosTauxEvolution" placeholder="0.0" class="fixed-input"
                                                          @change="formatInput('grosTauxEvolution')" ></el-input>
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
                                                      @change="formatInput('prixRevient')" ></el-input>
                                        </el-form-item>
                                        <el-checkbox v-model="form.indexationIcc" class="mb-3">Indexation à I'ICC</el-checkbox>
                                    </el-col>
                                    <el-col :span="8">
                                        <el-form-item label="Dont prix du foncier" prop="coutFoncier">
                                            <el-input type="text" v-model="form.coutFoncier" placeholder="0" class="fixed-input"
                                                      @change="formatInput('coutFoncier')" ></el-input>
                                        </el-form-item>
                                    </el-col>
                                    <el-col :span="8">
                                        <el-form-item label="Choisir un modèle d'amortissement technique" prop="modeleDamortissement">
                                            <el-select v-model="form.modeleDamortissement">
                                                <el-option v-for="item in modeleDamortissements"
                                                    :key="item.id"
                                                    :label="item.nom"
                                                    :value="item.id"></el-option>
                                            </el-select>
                                        </el-form-item>
                                    </el-col>
                                </el-row>
                            </el-collapse-item>
                            <el-collapse-item title="Financements en K€" name="2">
                                <div class="row mt-4" >
                                    <div class="col-sm-7">
                                        <div class="row">
                                            <div class="col-sm-6">
                                                <el-form-item label="Montant de fonds propres" prop="fondsPropres">
                                                    <el-input type="text" v-model="form.fondsPropres" placeholder="0" class="fixed-input"
                                                              @change="formatInput('fondsPropres')" ></el-input>
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
                                                <p><strong>Montant des Subventions en K€</strong></p>
                                                <el-form-item label="Subventions d'Etat" prop="subventionsEtat">
                                                    <el-input type="text" v-model="form.subventionsEtat" placeholder="0" class="fixed-input"
                                                              @change="formatInput('subventionsEtat')" ></el-input>
                                                </el-form-item>
                                                <el-form-item label="Subventions ANRU" prop="subventionsAnru">
                                                    <el-input type="text" v-model="form.subventionsAnru" placeholder="0" class="fixed-input"
                                                              @change="formatInput('subventionsAnru')" ></el-input>
                                                </el-form-item>
												<el-form-item label="Subventions Régions" prop="subventionsRegion">
													<el-input type="text" v-model="form.subventionsRegion" placeholder="0" class="fixed-input"
															  @change="formatInput('subventionsRegion')" ></el-input>
												</el-form-item>
												<el-form-item label="Subventions départements" prop="subventionsDepartement">
													<el-input type="text" v-model="form.subventionsDepartement" placeholder="0" class="fixed-input"
															  @change="formatInput('subventionsDepartement')" ></el-input>
												</el-form-item>
                                                <el-form-item label="Subventions EPCI/Communes" prop="subventionsEpci">
                                                    <el-input type="text" v-model="form.subventionsEpci" placeholder="0" class="fixed-input"
                                                              @change="formatInput('subventionsEpci')" ></el-input>
                                                </el-form-item>
                                                <el-form-item label="Subventions collecteurs" prop="subventionsCollecteur">
                                                    <el-input type="text" v-model="form.subventionsCollecteur" placeholder="0" class="fixed-input"
                                                              @change="formatInput('subventionsCollecteur')" ></el-input>
                                                </el-form-item>
                                                <el-form-item label="Autres subventions" prop="autresSubventions">
                                                    <el-input type="text" v-model="form.autresSubventions" placeholder="0" class="fixed-input"
                                                              @change="formatInput('autresSubventions')" ></el-input>
                                                </el-form-item>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-5">
                                        <p><strong>Emprunts en K€</strong></p>
                                        <span class="form-label">Ajouter un emprunt</span>
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
                                                    <el-input type="text" v-model="form.montant" placeholder="0" class="fixed-input" @change="changeMontant"></el-input>
                                                </el-form-item>
                                            </el-col>
                                            <el-col :span="15">
                                                <el-form-item label="Date de première annuité" label-width="105px" prop="datePremiere">
                                                    <el-date-picker
                                                        v-model="form.datePremiere"
                                                        type="month"
                                                        :picker-options="datePickerOptions"
                                                        value-format="yyyy-MM-dd"
                                                        format="MM/yyyy"
                                                        placeholder="Sélectionner">
                                                    </el-date-picker>
                                                    <el-tooltip class="item" effect="dark" content="Date de première annuité ou intérêts en cas de différé d'amortissement" placement="top">
                                                        <i class="el-icon-info"></i>
                                                    </el-tooltip>
                                                </el-form-item>
                                            </el-col>
                                        </el-row>
                                        <p class="mt-5"><strong>Liste des emprunts</strong></p>
                                        <el-table
                                            :data="form.typeEmpruntNouveauxFoyer"
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
                <el-button type="success" :disabled="isSubmitting" @click="save('nouveauxForm')">Valider</el-button>
            </div>
        </el-dialog>
    </div>
</template>

<script>
import moment from "moment";
import { getIncremental, getFloat, updateData } from '../../../../../utils/helpers';
import { initPeriodic, periodicFormatter, checkAllPeriodics, mathInput} from '../../../../../utils/inputs';
import customValidator from '../../../../../utils/validation-rules';
import Periodique from '../../../../../components/partials/Periodique';

export default {
    name: "NouveauxFoyers",
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
            nouveauxFoyers: [],
            modeleDamortissements: [],
            dialogVisible: false,
            form: null,
            selectedIndex: 0,
            isEdit: false,
            isSubmitting: false,
            activeTab: '1',
            query: null,
            typesEmprunts: [],
            availableTypesEmprunts: [],
            periodiqueHasError: false,
            columns: [],
            datePickerOptions: {
            },
            natures: [
                'Neuf',
                'VEFA',
                'Acquisition groupe',
                'Acquisition Hors groupe',
                'Bail long terme',
                'Autres'
            ],
            formRules: {
                numero: customValidator.getPreset('number.positiveInt'),
                nomIntervention: [
                    { required: true, validator: validateNom, trigger: 'change' }
                ],
                nature: customValidator.getRule('required'),
                nombreLogements: customValidator.getPreset('number.positiveInt'),
                prixRevient: customValidator.getPreset('number.positiveDouble'),
                coutFoncier: customValidator.getPreset('number.positiveDouble'),
                fondsPropres: customValidator.getPreset('number.positiveDouble'),
                subventionsEtat: customValidator.getRule('positiveDouble'),
                subventionsAnru: customValidator.getRule('positiveDouble'),
                subventionsEpci: customValidator.getRule('positiveDouble'),
                subventionsDepartement: customValidator.getRule('positiveDouble'),
                subventionsRegion: customValidator.getRule('positiveDouble'),
                subventionsCollecteur: customValidator.getRule('positiveDouble'),
                autresSubventions: customValidator.getRule('positiveDouble'),
                redevancesTauxEvolution: customValidator.getRule('positiveDouble'),
                tfpbTauxEvolution: customValidator.getRule('positiveDouble'),
                maintenanceTauxEvolution: customValidator.getRule('positiveDouble'),
                grosTauxEvolution: customValidator.getRule('positiveDouble'),
                montant: customValidator.getPreset('number.positiveDouble'),
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
    mounted(){
        this.getTypeEmprunts();
        this.getModeleDamortissements();
    },
    methods: {
        initForm() {
            this.form = {
                id: null,
                numero: getIncremental(this.nouveauxFoyers, 'numero'),
                nomIntervention: '',
                indexationIcc: false,
                typeEmpruntNouveauxFoyer: [],
                periodiques: {
                    redevances: initPeriodic(),
                    complementsCapital: initPeriodic(),
                    complementsInteret: initPeriodic(),
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
                    label: (parseInt(this.anneeDeReference) + i).toString() + ' Redevances',
                    prop: `periodiques.redevances[${i}]`
                });
                this.columns.push({
                    label: (parseInt(this.anneeDeReference) + i).toString() + ' Compléments d\'annuités - part capital',
                    prop: `periodiques.complementsCapital[${i}]`
                });
                this.columns.push({
                    label: (parseInt(this.anneeDeReference) + i).toString() + ' Compléments d\'annuités - part intérêts',
                    prop: `periodiques.complementsInteret[${i}]`
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
        getModeleDamortissements() {
            this.$apollo.query({
                query: require('../../../../../graphql/simulations/modeles-amortissements/modeleDamortissements.gql'),
                variables: {
                    simulationId: this.simulationID
                }
            }).then((res) => {
                if (res.data && res.data.modeleDamortissements) {
                    this.modeleDamortissements = res.data.modeleDamortissements.items;
                }
            });
        },
        tableData(data, query) {
            if (!_.isNil(data)) {
                this.query = query;
                const nouveauxFoyers = data.nouveauxFoyers.items.map(item => {
                    let redevances = [];
                    let complementsCapital = [];
                    let complementsInteret = [];
                    let tfpb = [];
                    let maintenanceCourante = [];
                    let grosEntretien = [];
                    item.periodique.items.forEach(periodique => {
                        redevances[periodique.iteration - 1] = periodique.redevances;
                        complementsCapital[periodique.iteration - 1] = periodique.complementsCapital;
                        complementsInteret[periodique.iteration - 1] = periodique.complementsInteret;
                        tfpb[periodique.iteration - 1] = periodique.tfpb;
                        maintenanceCourante[periodique.iteration - 1] = periodique.maintenanceCourante;
                        grosEntretien[periodique.iteration - 1] = periodique.grosEntretien;
                    });
                    let row = {...item};
                    row.typeEmpruntNouveauxFoyer = item.typeEmpruntNouveauxFoyer.items;
                    row.modeleDamortissement = item.modeleDamortissement? item.modeleDamortissement.id: null;
                    row.modeleDamortissementNom = item.modeleDamortissement? item.modeleDamortissement.nom: null;
                    row.periodiques = {
                        redevances,
                        complementsCapital,
                        complementsInteret,
                        tfpb,
                        maintenanceCourante,
                        grosEntretien
                    };

                    return row;
                });
                this.nouveauxFoyers = nouveauxFoyers;
                return nouveauxFoyers;
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
            this.$confirm('Êtes-vous sûr de vouloir supprimer ce nouveaux foyer?')
                .then(() => {
                    this.$apollo.mutate({
                        mutation: require('../../../../../graphql/simulations/foyers/nouveaux/removeNouveauxFoyer.gql'),
                        variables: {
                            nouveauxFoyerUUID: row.id,
                            simulationId: this.simulationID
                        }
                    }).then(() => {
                        updateData(this.query, this.simulationID).then(() => {
                            this.$message({
                                showClose: true,
                                message: 'Ce nouveau foyer a bien été supprimé.',
                                type: 'success'
                            });
                        })
                    }).catch(error => {
                        updateData(this.query, this.simulationID).then(() => {
                            this.$message({
                                showClose: true,
                                message: 'Ce nouveau foyer n\'existe pas.',
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
                    const typeEmpruntNouveauxFoyer = this.form.typeEmpruntNouveauxFoyer.map(item => {
                        return JSON.stringify({
                            id: item.typesEmprunts.id,
                            montant: item.montant,
                            datePremiere: item.datePremiere
                        })
                    });
                    this.$apollo.mutate({
                        mutation: require('../../../../../graphql/simulations/foyers/nouveaux/saveNouveauxFoyer.gql'),
                        variables: {
                            nouveaux: {
                                uuid: this.form.id,
                                simulationId: this.simulationID,
                                numero: this.form.numero,
                                nomIntervention: this.form.nomIntervention,
                                nombreLogements: this.form.nombreLogements,
                                nature: this.form.nature,
                                anneeAgrement: this.form.anneeAgrement,
                                dateAcquisition: this.form.dateAcquisition,
                                dateTravaux: this.form.dateTravaux,
                                indexation_icc: this.form.indexationIcc,
                                prixRevient: this.form.prixRevient,
                                coutFoncier: this.form.coutFoncier,
                                fondsPropres: this.form.fondsPropres,
                                subventionsEtat: this.form.subventionsEtat,
                                subventionsAnru: this.form.subventionsAnru,
                                subventionsEpci: this.form.subventionsEpci,
                                subventionsDepartement: this.form.subventionsDepartement,
                                subventionsRegion: this.form.subventionsRegion,
                                subventionsCollecteur: this.form.subventionsCollecteur,
                                autresSubventions: this.form.autresSubventions,
                                tfpbTauxEvolution: this.form.tfpbTauxEvolution,
                                redevancesTauxEvolution: this.form.redevancesTauxEvolution,
                                maintenanceTauxEvolution: this.form.maintenanceTauxEvolution,
                                grosTauxEvolution: this.form.grosTauxEvolution,
                                totalEmprunt: this.totalMontant,
                                typeEmprunts: typeEmpruntNouveauxFoyer,
                                modeleDamortissementUUID: this.form.modeleDamortissement,
                                periodique: JSON.stringify({
                                    redevances: this.form.periodiques.redevances,
                                    complements_capital: this.form.periodiques.complementsCapital,
                                    complements_interet: this.form.periodiques.complementsInteret,
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
                                message: 'Ce nouveau foyer a bien été enregistré.',
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
            this.$refs['nouveauxForm'].validate((valid) => {
                if (this.form.typeEmprunt && this.form.montant && this.form.datePremiere) {
                    const typeEmprunt = this.availableTypesEmprunts.find(item => item.id = this.form.typeEmprunt);
                    this.form.typeEmpruntNouveauxFoyer.push({
                        montant: this.form.montant | 0,
                        datePremiere: this.form.datePremiere,
                        typesEmprunts: typeEmprunt,
                        local: true
                    });
                    this.form.typeEmprunt = null;
                    this.getAvailableTypesEmprunts();
                }
            });
        },
        handleDeleteEmprunt(index, row) {
            this.$confirm('Êtes-vous sûr de vouloir supprimer ce type d’emprunt?')
                .then(_ => {
                    if (row.local) {
                        this.form.typeEmpruntNouveauxFoyer.splice(index, 1);
                        this.getAvailableTypesEmprunts();
                    } else {
                        this.$apollo.mutate({
                            mutation: require('../../../../../graphql/simulations/foyers/nouveaux/removeTypeDempruntNouveauxFoyer.gql'),
                            variables: {
                                typesEmpruntsUUID: row.typesEmprunts.id,
                                nouveauxFoyerUUID: this.nouveauxFoyers[this.selectedIndex].id
                            }
                        }).then((res) => {
                            this.form.typeEmpruntNouveauxFoyer.splice(index, 1);
                            this.getAvailableTypesEmprunts();
                        });
                    }
                })
                .catch(_ => {});
        },
        getAvailableTypesEmprunts() {
            let emprunts = [];
            const linkedEmprunts = this.form.typeEmpruntNouveauxFoyer;
            this.typesEmprunts.forEach(item => {
                if(!linkedEmprunts.some(emprunt => emprunt.typesEmprunts.id === item.id)) {
                    emprunts.push(item);
                }
            });
            this.availableTypesEmprunts = emprunts;
        },
        checkExistNom(value) {
            let nouveauxFoyers = this.nouveauxFoyers;
            if (this.isEdit) {
                nouveauxFoyers = nouveauxFoyers.filter(item => item.nomIntervention !== this.nouveauxFoyers[this.selectedIndex].nomIntervention);
            }
            return nouveauxFoyers.some(item => item.nomIntervention === value);
        },
        back() {
            if (this.selectedIndex > 0) {
                this.selectedIndex--;
                this.form = {...this.nouveauxFoyers[this.selectedIndex]};
            }
        },
        next() {
            if (this.selectedIndex < (this.nouveauxFoyers.length - 1)) {
                this.selectedIndex++;
                this.form = {...this.nouveauxFoyers[this.selectedIndex]};
            }
        },
        periodicOnChange(type) {
            let newPeriodics = this.form.periodiques[type];
            this.form.periodiques[type] = [];
            this.form.periodiques[type] = periodicFormatter(newPeriodics);
        },
        changeMontant(value) {
            if (value) {
                this.formRules.datePremiere = customValidator.getRule('required');
            } else {
                this.formRules.datePremiere = [];
            }
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
            this.form.typeEmpruntNouveauxFoyer.forEach(item => {
                emprunt += getFloat(item.montant);
            });
            return emprunt;
        },
        totalSubventions() {
            return getFloat(this.form.subventionsEtat) + getFloat(this.form.subventionsAnru) + getFloat(this.form.subventionsEpci) + getFloat(this.form.subventionsDepartement) + getFloat(this.form.subventionsRegion) + getFloat(this.form.subventionsCollecteur) + getFloat(this.form.autresSubventions);
        },
        resteFinancer() {
            return getFloat(this.form.prixRevient) - getFloat(this.form.coutFoncier) - (getFloat(this.form.fondsPropres) + this.totalSubventions + this.totalMontant);
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
    .nouveaux-foyers .el-form-item__label {
        margin-top: 7px;
    }
    .nouveaux-foyers .form-slider i {
        font-size: 25px;
        margin-left: 50px;
        cursor: pointer;
    }
    .nouveaux-foyers .fixed-input {
        width: 80px;
    }
    .nouveaux-foyers .carousel-head {
        height: 50px;
    }
    .nouveaux-foyers .el-table th > .cell {
        white-space: initial;
    }
    .nouveaux-foyers .el-tooltip.el-icon-info {
        font-size: 25px;
        color: #2491eb;
        vertical-align: middle;
    }
    .nouveaux-foyers .el-collapse-item__header{
        padding-left: 15px;
        font-weight: bold;
        font-size: 16px;
        color: #00436f;
        margin-top: 20px
    }
    .nouveaux-foyers .el-collapse-item__header:hover{
        background-color: rgba(0, 0, 0, 0.03);
    }
    .nouveaux-foyers .el-collapse-item__content {
        padding-top: 20px;
    }
</style>
