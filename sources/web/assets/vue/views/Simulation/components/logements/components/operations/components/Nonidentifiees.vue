<template>
    <div class="operation-nonidentifiees">
        <ApolloQuery
                :query="require('../../../../../../../graphql/simulations/logements/operations/operations.gql')"
                :variables="{
                simulationId: simulationID,
                type: 1
            }">
            <template slot-scope="{ result:{ loading, error, data }, isLoading }">
                <div v-if="error">Une erreur est survenue !</div>
                <el-table
                        v-loading="isLoading == 1"
                        :data="tableData(data)"
                        style="width: 100%">
                    <el-table-column sortable column-key="nOperation" prop="nOperation" min-width="150" label="N° de l’opération">
                        <template slot="header">
                            <span title="N° de l’opération">N° de l’opération</span>
                        </template>
                    </el-table-column>
                    <el-table-column sortable column-key="nom" prop="nom" min-width="120" label="Nom">
                        <template slot="header">
                            <span title="Nom">Nom</span>
                        </template>
                    </el-table-column>
                    <el-table-column sortable column-key="natureOperation" prop="natureOperation" min-width="120" label="Nature de l’opération">
                        <template slot-scope="scope">
                            {{natures[scope.row.natureOperation - 1]? natures[scope.row.natureOperation - 1].label: ''}}
                        </template>
                        <template slot="header">
                            <span title="Nature de l’opération">Nature de l’opération</span>
                        </template>
                    </el-table-column>
                    <el-table-column sortable column-key="conventionAnru" prop="conventionAnru" min-width="120" label="Convention ANRU">
                        <template slot-scope="scope">
                            {{scope.row.conventionAnru ? 'Oui': 'Non'}}
                        </template>
                        <template slot="header">
                            <span title="Convention ANRU">Convention ANRU</span>
                        </template>
                    </el-table-column>
                    <el-table-column sortable column-key="secteurFinancement" prop="secteurFinancement" min-width="120" label="Secteur de financement">
                        <template slot-scope="scope">
                            {{financements[scope.row.secteurFinancement - 1]? financements[scope.row.secteurFinancement - 1].label: ''}}
                        </template>
                        <template slot="header">
                            <span title="Secteur de financement">Secteur de financement</span>
                        </template>
                    </el-table-column>
                    <el-table-column sortable column-key="surfaceQuittancee" prop="surfaceQuittancee" min-width="120" label="Surface quittancée moyenne en m2/logt">
                        <template slot="header">
                            <span title="Surface quittancée moyenne en m2/logt">Surface quittancée moyenne en m2/logt</span>
                        </template>
                    </el-table-column>
                    <el-table-column sortable column-key="loyerMensuel" prop="loyerMensuel" min-width="120" label="Loyer mensuel €/m²">
                        <template slot="header">
                            <span title="Loyer mensuel €/m²">Loyer mensuel €/m²</span>
                        </template>
                    </el-table-column>
                    <el-table-column sortable column-key="profilLoyerNom" prop="profilLoyerNom" min-width="120" label="Profil de loyer N°">
                        <template slot="header">
                            <span title="Profil de loyer N°">Profil de loyer N°</span>
                        </template>
                    </el-table-column>
                    <el-table-column sortable column-key="tfpbLogt" prop="tfpbLogt" min-width="120" label="TFPB au logement">
                        <template slot="header">
                            <span title="TFPB au logement">TFPB au logement</span>
                        </template>
                    </el-table-column>
                    <el-table-column sortable column-key="tfpbDuree" prop="tfpbDuree" min-width="120" label="Durée exonération TFPB">
                        <template slot="header">
                            <span title="Durée exonération TFPB">Durée exonération TFPB</span>
                        </template>
                    </el-table-column>
                    <el-table-column sortable column-key="dureeChantier" prop="dureeChantier" min-width="120" label="Durée de chantier">
                        <template slot="header">
                            <span title="Durée de chantier">Durée de chantier</span>
                        </template>
                    </el-table-column>
                    <el-table-column sortable column-key="moyenFoncier" prop="moyenFoncier" min-width="120" label="dont Coût moyen du foncier en €/m2">
                        <template slot="header">
                            <span title="dont Coût moyen du foncier en €/m2">dont Coût moyen du foncier en €/m2</span>
                        </template>
                    </el-table-column>
                    <el-table-column sortable column-key="surfaceQuittancee" prop="surfaceQuittancee" min-width="120" label="Surface quittancée moyenne en m²/logt"></el-table-column>
                    <el-table-column sortable column-key="loyerMensuel" prop="loyerMensuel" min-width="120" label="Loyer mensuel €/m²"></el-table-column>
                    <el-table-column sortable column-key="profilLoyerNom" prop="profilLoyerNom" min-width="120" label="Profil de loyer N°"></el-table-column>
                    <el-table-column sortable column-key="tfpbLogt" prop="tfpbLogt" min-width="120" label="TFPB au logement"></el-table-column>
                    <el-table-column sortable column-key="tfpbDuree" prop="tfpbDuree" min-width="120" label="Durée exonération TFPB"></el-table-column>
                    <el-table-column sortable column-key="dureeChantier" prop="dureeChantier" min-width="120" label="Durée de chantier"></el-table-column>
                    <el-table-column sortable column-key="moyenFoncier" prop="moyenFoncier" min-width="120" label="dont Coût moyen du foncier en €/m²"></el-table-column>
                    <el-table-column sortable column-key="indexationIcc" prop="indexationIcc" min-width="120" label="Indexation ICC">
                        <template slot-scope="scope">
                            {{scope.row.indexationIcc ? 'Oui': 'Non'}}
                        </template>
                        <template slot="header">
                            <span title="Indexation ICC">Indexation ICC</span>
                        </template>
                    </el-table-column>
                    <el-table-column sortable column-key="modeleDamortissement" prop="modeleDamortissement" min-width="150" label="Modèle d'amortissement technique">
                        <template slot-scope="scope">
                            {{getModeleDamortissement(scope.row.modeleDamortissement)}}
                        </template>
                        <template slot="header">
                            <span title="Modèle d'amortissement technique">Modèle d'amortissement technique</span>
                        </template>
                    </el-table-column>
                    <el-table-column sortable column-key="prixRevient" prop="prixRevient" min-width="140" label="Prix de revient">
                        <template slot="header">
                            <span title="Prix de revient">Prix de revient</span>
                        </template>
                    </el-table-column>
                    <el-table-column sortable column-key="fondsPropres" prop="fondsPropres" min-width="140" label="Fonds propres">
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
                    <el-table-column sortable column-key="subventionsDepartement" prop="subventionsDepartement" min-width="120" label="Subventions département">
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
                    <el-table-column sortable column-key="subventionsAutres" prop="subventionsAutres" min-width="120" label="Autres subventions">
                        <template slot="header">
                            <span title="Autres subventions">Autres subventions</span>
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

        <el-row class="text-center mt-3 mb-3">
            <el-button type="primary" :disabled="!isModify" @click="showCreateModal">Créer une opération non identifiée</el-button>
        </el-row>

        <el-dialog
                title="Création/Modification d'une opération nouvelle non identifiée"
                :visible.sync="dialogVisible"
                :close-on-click-modal="false"
                width="80%">
            <el-row v-if="isEdit" class="form-slider text-center mb-4">
                <i class="el-icon-back font-weight-bold" @click="back"></i>
                <i class="el-icon-right font-weight-bold" @click="next"></i>
            </el-row>
            <el-form label-position="top" :model="form" :rules="formRules" label-width="160px" ref="operationForm">
                <el-tabs v-model="activeTab" type="card">
                    <el-tab-pane label="Caractéristiques générales" name="1">
                        <el-collapse v-model="collapseList">
                            <el-collapse-item title="Données Générales" name="1" >
                                <el-row :gutter="24">
                                    <el-col :span="24">
                                        <el-col :span="6">
                                            <el-form-item label="N° de l’opération" prop="Operation">
                                                <el-input type="text" v-model="form.nOperation" placeholder="N° de l’opération" class="input-collection" readonly></el-input>
                                            </el-form-item>
                                            <el-checkbox v-model="form.conventionAnru">Convention ANRU</el-checkbox>
                                        </el-col>
                                        <el-col :span="6">
                                            <el-form-item label="Nom de l'opération" prop="nom">
                                                <el-input type="text" v-model="form.nom" class="input-collection" placeholder="Nom"></el-input>
                                            </el-form-item>
                                        </el-col>
                                        <el-col :span="6">
                                            <el-form-item label="Nature de l’opération" prop="natureOperation">
                                                <el-select v-model="form.natureOperation" class="input-collection">
                                                    <el-option v-for="item in natures"
                                                               :key="item.value"
                                                               :label="item.label"
                                                               :value="item.value"></el-option>
                                                </el-select>
                                            </el-form-item>
                                        </el-col>
                                        <el-col :span="6">
                                            <el-form-item label="Secteur de financement" prop="secteurFinancement">
                                                <el-select v-model="form.secteurFinancement" class="input-collection">
                                                    <el-option v-for="item in financements"
                                                               :key="item.value"
                                                               :label="item.label"
                                                               :value="item.value">
                                                    </el-option>
                                                </el-select>
                                            </el-form-item>
                                        </el-col>
                                    </el-col>
                                </el-row>
                                <el-row :gutter="24">
                                    <el-col :span="24">
                                        <el-col :span="6">
                                            <el-form-item label="Surface quittancée par logement en m²" prop="surfaceQuittancee">
                                                <el-input type="text" v-model="form.surfaceQuittancee" placeholder="0" class="input-collection"></el-input>
                                            </el-form-item>
                                        </el-col>
                                        <el-col :span="6">
                                            <el-form-item label="Durée exonération TFPB (en année)" prop="tfpbDuree">
                                                <el-input type="text" v-model="form.tfpbDuree" placeholder="0" class="input-collection"></el-input>
                                            </el-form-item>
                                        </el-col>
                                        <el-col :span="6">
                                            <el-form-item label="TFPB au logement en €/logt" prop="tfpbLogt">
                                                <el-input type="text" v-model="form.tfpbLogt" placeholder="0" class="input-collection"></el-input>
                                            </el-form-item>
                                        </el-col>
                                        <el-col :span="6">
                                            <el-form-item label="Durée de chantier (en années)" prop="dureeChantier">
                                                <el-select v-model="form.dureeChantier" class="input-collection">
                                                    <el-option v-for="item in dureeChantiers"
                                                               :key="item.value"
                                                               :label="item.label"
                                                               :value="item.value"></el-option>
                                                </el-select>
                                            </el-form-item>
                                        </el-col>
                                    </el-col>
                                </el-row>
                            </el-collapse-item>

                            <el-collapse-item title="Loyers" name="2" >
                                <el-row :gutter="24">
                                    <el-col :span="24">
                                        <el-col :span="6">

                                            <el-form-item label="Loyer mensuel €/m²" prop="loyerMensuel">
                                                <el-input type="text" v-model="form.loyerMensuel" placeholder="0" class="input-collection"></el-input>
                                            </el-form-item>
                                        </el-col>
                                        <el-col :span="6">
                                            <el-form-item label="Loyer mensuel garages/parkings en €/lot" prop="loyerMensuelGarages">
                                                <el-input type="text" v-model="form.loyerMensuelGarages" placeholder="0" class="input-collection"></el-input>
                                            </el-form-item>
                                        </el-col>
                                        <el-col :span="6">
                                            <el-form-item label="Profil de loyer N°" prop="profilLoyer">
                                                <el-select v-model="form.profilLoyer" class="input-collection">
                                                    <el-option v-for="item in profilLoyers"
                                                               :key="item.id"
                                                               :label="item.nom"
                                                               :value="item.id"></el-option>
                                                </el-select>
                                            </el-form-item>
                                        </el-col>
                                    </el-col>
                                </el-row>
                            </el-collapse-item>
                            <el-collapse-item title="Données complémentaires en K€" name="3">
                                <el-row :gutter="24">
                                    <el-col :span="24">
                                        <el-col :span="5" style="padding-top: 55px;">
                                            <div class="carousel-head">
                                                <p>Nombre d'agréments</p>
                                            </div>
                                            <div class="carousel-head">
                                                <p>Nombre logements MEC</p>
                                            </div>
                                            <div class="carousel-head">
                                                <p>Nb de garages / parkings MEC</p>
                                            </div>
                                        </el-col>
                                        <el-col :span="19">
                                            <periodique :anneeDeReference="anneeDeReference" v-model="form.periodiques"></periodique>
                                        </el-col>
                                    </el-col>
                                </el-row>
                            </el-collapse-item>
                        </el-collapse>
                    </el-tab-pane>

                    <el-tab-pane label="Investissements et financements" name="2">
                        <el-collapse v-model="collapseList">
                            <el-collapse-item title="Invertissement" name="1">
                                <el-row :gutter="24">
                                    <el-col :span="24">
                                        <el-col :span="8">
                                            <el-form-item label="Coût moyen d'opération en €/m²" prop="moyenOperation">
                                                <el-input type="text" v-model="form.moyenOperation" placeholder="0" class="input-collection"></el-input>
                                            </el-form-item>
                                            <el-checkbox v-model="form.indexationIcc">Indexation à I'ICC</el-checkbox>
                                        </el-col>
                                        <el-col :span="8">
                                            <el-form-item label="Dont coût moyen du foncier en €/m²" prop="moyenFoncier">
                                                <el-input type="text" v-model="form.moyenFoncier" placeholder="0" class="input-collection"></el-input>
                                            </el-form-item>
                                        </el-col>
                                        <el-col :span="8">
                                            <el-form-item label="Choisir un modèle d'amortissement" prop="modeleDamortissement">
                                                <el-select v-model="form.modeleDamortissement" class="input-collection">
                                                    <el-option v-for="item in modeleDamortissements"
                                                               :key="item.id"
                                                               :label="item.nom"
                                                               :value="item.id"></el-option>
                                                </el-select>
                                            </el-form-item>
                                        </el-col>
                                    </el-col>
                                </el-row>
                            </el-collapse-item>
                            <el-collapse-item title="Financements" name="2" style="margin-bottom: 20px">
                                <el-row :gutter="24">
                                    <el-col :span="14">
                                        <el-row :gutter="20">
                                            <el-col :span="12">
                                                <el-form-item label="Prix de revient" prop="prixRevient">
                                                    <el-input type="text" v-model="form.prixRevient" placeholder="0" class="input-collection"></el-input>
                                                </el-form-item>
                                                <el-form-item label="Quotité de Fonds propres" prop="fondsPropres">
                                                    <el-input type="text" v-model="form.fondsPropres" placeholder="0" class="input-collection"></el-input>
                                                </el-form-item>
                                                <el-form-item label="Total quotité subventions" prop="totalSubventions">
                                                    {{totalSubventions}}
                                                </el-form-item>
                                                <el-form-item label="Total quotité emprunts" prop="totalMontant">
                                                    {{totalMontant}}
                                                </el-form-item>
                                                <el-form-item label="Reste à financer" prop="resteFinancer">
                                                    {{resteFinancer}}K€
                                                </el-form-item>
                                            </el-col>
                                            <el-col :span="12">
                                                <p><strong>Quotités subventions en K€</strong></p>
                                                <el-form-item label="Subventions d'Etat" prop="subventionsEtat">
                                                    <el-input type="text" v-model="form.subventionsEtat" placeholder="0" class="input-collection"></el-input>
                                                </el-form-item>
                                                <el-form-item label="Subventions ANRU" prop="subventionsAnru">
                                                    <el-input type="text" v-model="form.subventionsAnru" placeholder="0" class="input-collection"></el-input>
                                                </el-form-item>
												<el-form-item label="Subventions Régions" prop="subventionsRegion">
													<el-input type="text" v-model="form.subventionsRegion" placeholder="0" class="input-collection"></el-input>
												</el-form-item>
                                                <el-form-item label="Subventions EPCI/Communes" prop="subventionsEpci">
                                                    <el-input type="text" v-model="form.subventionsEpci" placeholder="0" class="input-collection"></el-input>
                                                </el-form-item>
                                                <el-form-item label="Subventions départements" prop="subventionsDepartement">
                                                    <el-input type="text" v-model="form.subventionsDepartement" placeholder="0" class="input-collection"></el-input>
                                                </el-form-item>
                                                <el-form-item label="Subventions collecteurs" prop="subventionsCollecteur">
                                                    <el-input type="text" v-model="form.subventionsCollecteur" placeholder="0" class="input-collection"></el-input>
                                                </el-form-item>
                                                <el-form-item label="Autres subventions" prop="subventionsAutres">
                                                    <el-input type="text" v-model="form.subventionsAutres" placeholder="0" class="input-collection"></el-input>
                                                </el-form-item>
                                            </el-col>
                                        </el-row>
                                    </el-col>
                                    <el-col :span="10">
                                        <p><strong>Emprunts en K€</strong></p>
                                        <span>Ajouter un emprunt</span>
                                        <el-select v-model="form.typeEmprunt">
                                            <el-option v-for="item in availableTypesEmprunts"
                                                       :key="item.id"
                                                       :label="item.nom"
                                                       :value="item.id"></el-option>
                                        </el-select>
                                        <el-button type="primary" @click="addTypeEmprunt" :disabled="!form.typeEmprunt">Ajouter</el-button>
                                        <el-row v-if="form.typeEmprunt" :gutter="24">
                                            <el-col :span="12">
                                                <el-form-item label="Montant" label-width="70px" prop="montant">
                                                    <el-input type="text" v-model="form.montant" placeholder="0" class="input-collection"></el-input>
                                                </el-form-item>
                                            </el-col>
                                            <el-col :span="12">
                                                <el-form-item label="Date de la première annuité" label-width="105px" prop="datePremier">
                                                    <el-date-picker
                                                            v-model="form.datePremier"
                                                            type="month"
                                                            format="MM/yyyy"
                                                            :picker-options="datePickerOptions"
                                                            value-format="yyyy-MM-dd"
                                                            placeholder="JJ/MM/AAAA"
                                                            style="width:130px;">
                                                    </el-date-picker>
                                                    <el-tooltip class="item" effect="dark" content="Date de première annuité ou intérêts en cas de différé d'amortissement" placement="top">
                                                        <i class="el-icon-info"></i>
                                                    </el-tooltip>
                                                </el-form-item>
                                            </el-col>
                                        </el-row>
                                        <strong>Liste des emprunts</strong>
                                        <el-table
                                                :data="form.typeEmpruntOperation"
                                                style="width: 100%">
                                            <el-table-column sortable column-key="typesEmprunts" prop="typesEmprunts" min-width="60" label="Numéro">
                                                <template slot-scope="scope">
                                                    {{scope.row.typesEmprunts.nom}}
                                                </template>
                                            </el-table-column>
                                            <el-table-column sortable column-key="montant" prop="montant" min-width="100" label="Montant emprunt" align="center"></el-table-column>
                                            <el-table-column sortable column-key="datePremiere" prop="datePremiere" min-width="100" label="Date d'emprunt" align="center">
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
                                            <p>Total quotité emprunts : {{totalMontant}}</p>
                                        </div>
                                    </el-col>
                                </el-row>
                            </el-collapse-item>
                        </el-collapse>
                    </el-tab-pane>
                </el-tabs>
            </el-form>
            <span slot="footer" class="dialog-footer">
                <el-button type="primary" @click="dialogVisible = false">Retour</el-button>
                <el-button type="success" @click="save('operationForm')">Valider</el-button>
            </span>
        </el-dialog>
    </div>
</template>

<script>
import moment from "moment";
import { initPeriodic, checkAllPeriodics } from '../../../../../../../utils/inputs';
import { updateData, getFloat } from '../../../../../../../utils/helpers';
import customValidator from '../../../../../../../utils/validation-rules';
import Periodique from '../../../../../../../components/partials/Periodique';

export default {
    name: "OperationNonidentifiees",
    components: { Periodique },
    props: ['simulationID', 'anneeDeReference', 'typesEmprunts', 'modeleDamortissements', 'profilLoyers', 'data', 'isLoading', 'error', 'query'],
    data() {
        var resteFinancerValidate = (rule, value, callback) => {
            if (this.resteFinancer !== 0) {
                callback(new Error("le plan de financement n’est pas équilibré"));
            } else {
                callback();
            }
        };
        return {
            collapseList: ['1'],
            operations: [],
            dialogVisible: false,
            form: null,
            isEdit: false,
            selectedIndex: null,
            activeTab: '1',
            availableTypesEmprunts: [],
            periodiqueHasError: false,
            columns: [],
            isSubmitting: false,
            datePickerOptions: {
            },
            natures: [
                { label: 'Neuf', value: 1 },
                { label: 'VEFA', value: 2 },
                { label: 'Acquisition Amélioration Groupe', value: 3 },
                { label: 'Acquisition Amélioration Hors groupe', value: 4 },
                { label: 'Acquisition groupe', value: 5 },
                { label: 'Acquisition Hors groupe', value: 6 },
                { label: 'Usufruit locatif social', value: 7 },
                { label: 'Autres', value: 8 }
            ],
            financements: [
                { label: 'PLUS', value: 1 },
                { label: 'PLAI', value: 2 },
                { label: 'PLS', value: 3 },
                { label: 'PLI', value: 4 },
                { label: 'Autres', value: 5 }
            ],
            dureeChantiers: [
                { label: '1 ans', value: 1 },
                { label: '2 ans', value: 2 },
                { label: '3 ans', value: 3 },
            ],
            formRules: {
                nOperation: [
                    { required: true, message: "Veuillez sélectionner un n° de l’opération", trigger: 'change' }
                ],
                nom: [
                    { required: true, message: "Veuillez saisir un nom pour cette opération", trigger: 'change' }
                ],
                loyerMensuelGarages: customValidator.getRule('positiveInt'),
                prixRevient: customValidator.getPreset('number.positiveDouble'),
                moyenOperation: customValidator.getPreset('number.positiveInt'),
                moyenFoncier: customValidator.getRule('positiveInt'),
                fondsPropres: customValidator.getPreset('number.positiveDouble'),
                subventionsEtat: customValidator.getRule('positiveDouble'),
                subventionsAnru: customValidator.getRule('positiveDouble'),
                subventionsEpci: customValidator.getRule('positiveDouble'),
                subventionsDepartement: customValidator.getRule('positiveDouble'),
                subventionsRegion: customValidator.getRule('positiveDouble'),
                subventionsCollecteur: customValidator.getRule('positiveDouble'),
                subventionsAutres: customValidator.getRule('positiveDouble'),
                surfaceQuittancee: customValidator.getRule('positiveDouble'),
                loyerMensuel: customValidator.getRule('positiveDouble'),
                tfpbLogt: customValidator.getRule('positiveInt'),
                tfpbDuree: customValidator.getRule('positiveInt'),
                dureeChantier: customValidator.getRule('positiveDouble'),
                montant: customValidator.getPreset('number.positiveDouble'),
                datePremier: customValidator.getRule('required'),
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
                nOperation: this.operations.length + 1,
                dureeChantier: 1,
                typeEmpruntOperation: [],
                indexationIcc: false,
                periodiques: {
                    nombreAgrements: initPeriodic(),
                    nombreLogements: initPeriodic(),
                    nbGarages: initPeriodic()
                }
            }
        },
        tableData(data) {
            if (!_.isNil(data)) {
                let operations = data.operations.items.map(item => {
                    if (item.type === 1) {
                        let nombreAgrements = [];
                        let nombreLogements = [];
                        let loyerMensuels = [];
                        let nombreLots = [];
                        let nbGarages = [];
                        item.operationPeriodique.items.forEach(periodique => {
                            nombreAgrements[periodique.iteration - 1] = periodique.nombreAgrement;
                            nombreLogements[periodique.iteration - 1] = periodique.nombreLogement;
                            loyerMensuels[periodique.iteration - 1] = periodique.loyerMensuel;
                            nombreLots[periodique.iteration - 1] = periodique.nombreLots;
                            nbGarages[periodique.iteration - 1] = periodique.nbGarages;
                        });

                        let row = {...item};
                        row.profilLoyer = item.profilLoyer ? item.profilLoyer.id : null;
                        row.profilLoyerNom = item.profilLoyer ? item.profilLoyer.nom : null;
                        row.modeleDamortissement = item.modeleDamortissement ? item.modeleDamortissement.id: null;
                        row.typeEmpruntOperation = item.typeEmpruntOperation.items;
                        row.periodiques = {
                            nombreAgrements,
                            nombreLogements,
                            loyerMensuels,
                            nombreLots,
                            nbGarages
                        };
                        return row;
                    } else {
                        return false;
                    }
                });
                operations = operations.filter((item) => item);
                this.operations = operations ;
                return operations;
            } else {
                return [];
            }
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
            this.$confirm('Êtes-vous sûr de vouloir supprimer cette opération identifiée?')
                .then(_ => {
                    this.$apollo.mutate({
                        mutation: require('../../../../../../../graphql/simulations/logements/operations/removeOperation.gql'),
                        variables: {
                            operationUUID: row.id,
                            simulationId: this.simulationID
                        }
                    }).then(() => {
                        updateData(this.query, this.simulationID).then(() => {
                            this.$message({
                                showClose: true,
                                message: 'Cette opération non identifiée a bien été supprimée.',
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
        save(formName) {
            this.$refs[formName].validate((valid) => {
                if (valid && checkAllPeriodics(this.form.periodiques)) {
                    this.isSubmitting = true;
                    const typeEmpruntOperation = this.form.typeEmpruntOperation.map(item => {
                        return JSON.stringify({
                            id: item.typesEmprunts.id,
                            montant: item.montant,
                            datePremiere: item.datePremiere
                        })
                    });
                    this.$apollo.mutate({
                        mutation: require('../../../../../../../graphql/simulations/logements/operations/saveOperation.gql'),
                        variables: {
                            operation: {
                                simulationId: this.simulationID,
                                uuid: this.form.id,
                                nom: this.form.nom,
                                natureOperation: this.form.natureOperation,
                                conventionAnru: this.form.conventionAnru,
                                surfaceQuittancee: this.form.surfaceQuittancee,
                                loyerMensuel: this.form.loyerMensuel,
                                secteurFinancement: this.form.secteurFinancement,
                                profilLoyerUUID: this.form.profilLoyer,
                                tfpbLogt: this.form.tfpbLogt,
                                tfpbDuree: this.form.tfpbDuree,
                                dureeChantier: this.form.dureeChantier,
                                moyenOperation: this.form.moyenOperation,
                                moyenFoncier: this.form.moyenFoncier,
                                loyerMensuelGarages: this.form.loyerMensuelGarages,
                                indexationIcc: this.form.indexationIcc,
                                prixRevient: this.form.prixRevient,
                                modeleDamortissementUUID: this.form.modeleDamortissement,
                                fondsPropres: this.form.fondsPropres,
                                subventionsEtat: this.form.subventionsEtat,
                                subventionsAnru: this.form.subventionsAnru,
                                subventionsEpci: this.form.subventionsEpci,
                                subventionsDepartement: this.form.subventionsDepartement,
                                subventionsRegion: this.form.subventionsRegion,
                                subventionsCollecteur: this.form.subventionsCollecteur,
                                subventionsAutres: this.form.subventionsAutres,
                                resteFinancer: this.resteFinancer,
                                total: this.totalSubventions,
                                typeEmprunts: typeEmpruntOperation,
                                type: 1,
                                periodique: JSON.stringify({
                                    nombre_agrement: this.form.periodiques.nombreAgrements,
                                    nombre_logement: this.form.periodiques.nombreLogements,
                                    nb_garages: this.form.periodiques.nbGarages
                                })
                            }
                        }
                    }).then(() => {
                        this.isSubmitting = false;
                        this.dialogVisible = false;
                        updateData(this.query, this.simulationID).then(() => {
                            this.$message({
                                showClose: true,
                                message: 'L\'opération non identifiée ont bien été enregistrée.',
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
        addTypeEmprunt() {
            if (this.form.typeEmprunt && this.form.montant && this.form.datePremier) {
                const typeEmprunt = this.availableTypesEmprunts.find(item => item.id == this.form.typeEmprunt);
                this.form.typeEmpruntOperation.push({
                    montant: this.form.montant | 0,
                    datePremiere: this.form.datePremier,
                    typesEmprunts: typeEmprunt,
                    local: true
                });
                this.form.typeEmprunt = null;
                this.getTypesEmprunts();
            }
        },
        getTypesEmprunts() {
            let emprunts = [];
            const linkedEmprunts = this.form.typeEmpruntOperation;
            this.typesEmprunts.forEach(item => {
                if(!linkedEmprunts.some(emprunt => emprunt.typesEmprunts.id === item.id)) {
                    emprunts.push(item);
                }
            });
            this.availableTypesEmprunts = emprunts;
        },
        handleDeleteEmprunt(index, row) {
            this.$confirm('Êtes-vous sûr de vouloir supprimer ce type d’emprunt?')
                .then(_ => {
                    if (row.local) {
                        this.form.typeEmpruntOperation.splice(index, 1);
                        this.getTypesEmprunts();
                    } else {
                        this.$apollo.mutate({
                            mutation: require('../../../../../../../graphql/simulations/logements/operations/removeTypeDempruntOperation.gql'),
                            variables: {
                                typesEmpruntsUUID: row.typesEmprunts.id,
                                operationUUID: this.operations[this.selectedIndex].id
                            }
                        }).then((res) => {
                            this.form.typeEmpruntOperation.splice(index, 1);
                            this.getTypesEmprunts();
                        });
                    }
                })
                .catch(_ => {});
        },
        getModeleDamortissement(id) {
            const modeleDamortissement = this.modeleDamortissements.find(item => item.id === id);
            return modeleDamortissement ? modeleDamortissement.nom : '';
        },
        back() {
            if (this.selectedIndex > 0) {
                this.selectedIndex--;
                this.form = {...this.operations[this.selectedIndex]};
            }
        },
        next() {
            if (this.selectedIndex < (this.operations.length - 1)) {
                this.selectedIndex++;
                this.form = {...this.operations[this.selectedIndex]};
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
        setTableColumns() {
            this.columns = [];
            for (var i = 0; i < 50; i++) {
                this.columns.push({
                    label: (parseInt(this.anneeDeReference) + i).toString() + ' Nombre d\'agrément',
                    prop: `periodiques.nombreAgrements[${i}]`
                });
                this.columns.push({
                    label: (parseInt(this.anneeDeReference) + i).toString() + ' Nombre logements MEC',
                    prop: `periodiques.nombreLogements[${i}]`
                });
                this.columns.push({
                    label: (parseInt(this.anneeDeReference) + i).toString() + ' Loyer mensuel lots annexes €/log',
                    prop: `periodiques.loyerMensuels[${i}]`
                });
                this.columns.push({
                    label: (parseInt(this.anneeDeReference) + i).toString() + ' Nombre de lots annexes MEC',
                    prop: `periodiques.nombreLots[${i}]`
                });
                this.columns.push({
                    label: (parseInt(this.anneeDeReference) + i).toString() + ' Nb de garages / parkings MEC',
                    prop: `periodiques.nbGarages[${i}]`
                });
            }
        }
    },
    computed: {
        isModify() {
            return this.$store.getters['choixEntite/isModify'];
        },
        totalMontant() {
            let emprunt = 0;
            this.form.typeEmpruntOperation.forEach(item => {
                emprunt += getFloat(item.montant);
            });
            return emprunt;
        },
        totalSubventions() {
            return getFloat(this.form.fondsPropres) + getFloat(this.form.subventionsEtat) + getFloat(this.form.subventionsAnru) + getFloat(this.form.subventionsEpci) + getFloat(this.form.subventionsDepartement) + getFloat(this.form.subventionsRegion) + getFloat(this.form.subventionsCollecteur) + getFloat(this.form.subventionsAutres);
        },
        resteFinancer() {
            return getFloat(this.form.prixRevient) - (this.totalSubventions + this.totalMontant);
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
    .operation-nonidentifiees .el-collapse-item__header{
        padding-left: 15px;
        font-weight: bold;
        font-size: 16px;
        color: #00436f;
        margin-top: 20px
    }
    .operation-nonidentifiees .el-collapse-item__header:hover{
        background-color: rgba(0, 0, 0, 0.03);
    }
    .operation-nonidentifiees .el-collapse-item__content {
        padding-top: 20px;
    }
    .operation-nonidentifiees .input-collection {
        width: 200px;
    }
</style>
