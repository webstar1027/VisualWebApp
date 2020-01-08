<template>
    <div class="operation-identifiees">
            <div v-if="error">Une erreur est survenue !</div>
            <el-table
                v-loading="isLoading"
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
                <el-table-column sortable column-key="conventionAnru" prop="conventionAnru" min-width="100" label="Convention ANRU">
                    <template slot-scope="scope">
                        {{scope.row.conventionAnru ? 'Oui': 'Non'}}
                    </template>
                    <template slot="header">
                        <span title="Convention ANRU">Convention ANRU</span>
                    </template>
                </el-table-column>
                <el-table-column sortable column-key="videOccupe" prop="videOccupe" min-width="130" label="Vide/Occupé">
                    <template slot-scope="scope">
                        <span v-if="scope.row.videOccupe == 1">Vide</span>
                        <span v-if="scope.row.videOccupe == 2">Occupé</span>
                    </template>
                    <template slot="header">
                        <span title="Vide/Occupé">Vide/Occupé</span>
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
                <el-table-column sortable column-key="surfaceQuittancee" prop="surfaceQuittancee" min-width="120" label="Surface quittancée">
                    <template slot="header">
                        <span title="Surface quittancée">Surface quittancée</span>
                    </template>
                </el-table-column>
                <el-table-column sortable column-key="nombreLogements" prop="nombreLogements" min-width="120" label="Nombre de logements">
                    <template slot="header">
                        <span title="Nombre de logements">Nombre de logements</span>
                    </template>
                </el-table-column>
                <el-table-column sortable column-key="loyerMensuel" prop="loyerMensuel" min-width="120" label="Loyer mensuel €/m²">
                    <template slot="header">
                        <span title="Loyer mensuel €/m²">Loyer mensuel €/m²</span>
                    </template>
                </el-table-column>
                <el-table-column sortable column-key="profilLoyerNom" prop="profilLoyerNom" min-width="160" label="Profil de loyer N°">
                    <template slot="header">
                        <span title="Profil de loyer N°">Profil de loyer N°</span>
                    </template>
                </el-table-column>
                <el-table-column sortable column-key="indexeIrl" prop="indexeIrl" min-width="130" label="Indexé à l’IRL">
                    <template slot-scope="scope">
                        {{scope.row.indexeIrl ? 'Oui': 'Non'}}
                    </template>
                    <template slot="header">
                        <span title="Indexé à l’IRL">Indexé à l’IRL</span>
                    </template>
                </el-table-column>
                <el-table-column sortable column-key="nombreGarages" prop="nombreGarages" min-width="150" label="Nombre de garages/ parkings">
                    <template slot="header">
                        <span title="Nombre de garages/ parkings">Nombre de garages/ parkings</span>
                    </template>
                </el-table-column>
                <el-table-column sortable column-key="loyerMensuelGarages" prop="loyerMensuelGarages" min-width="160" label="Loyer mensuel par garages / parkings">
                    <template slot="header">
                        <span title="Loyer mensuel par garages / parkings">Loyer mensuel par garages / parkings</span>
                    </template>
                </el-table-column>
                <el-table-column sortable column-key="nombreCommerces" prop="nombreCommerces" min-width="120" label="Nombre de commerces">
                    <template slot="header">
                        <span title="Nombre de commerces">Nombre de commerces</span>
                    </template>
                </el-table-column>
                <el-table-column sortable column-key="loyerMensuelCommerces" prop="loyerMensuelCommerces" min-width="150" label="Loyer mensuel par commerces">
                    <template slot="header">
                        <span title="Loyer mensuel par commerces">Loyer mensuel par commerces</span>
                    </template>
                </el-table-column>
                <el-table-column sortable column-key="anneAgrement" prop="anneAgrement" min-width="120" label="Année d'agrément">
                    <template slot="header">
                        <span title="Année d'agrément">Année d'agrément</span>
                    </template>
                </el-table-column>
                <el-table-column sortable column-key="dateOrdreService" prop="dateOrdreService" min-width="150" label="Date d'ordre de service / Acquisition">
                    <template slot-scope="scope">
                        {{scope.row.dateOrdreService | dateFR}}
                    </template>
                    <template slot="header">
                        <span title="Date d'ordre de service / Acquisition">Date d'ordre de service / Acquisition</span>
                    </template>
                </el-table-column>
                <el-table-column sortable column-key="dateMiseService" prop="dateMiseService" min-width="180" label="Date de mise en service / Début de travaux">
                    <template slot-scope="scope">
                        {{scope.row.dateMiseService | dateFR}}
                    </template>
                    <template slot="header">
                        <span title="Date de mise en service / Début de travaux">Date de mise en service / Début de travaux</span>
                    </template>
                </el-table-column>
                <el-table-column sortable column-key="acquisitionFin" prop="acquisitionFin" min-width="120" label="Acquisition fin de mois">
                    <template slot-scope="scope">
                        {{scope.row.acquisitionFin ? 'Oui': 'Non'}}
                    </template>
                    <template slot="header">
                        <span title="Acquisition fin de mois">Acquisition fin de mois</span>
                    </template>
                </el-table-column>
                <el-table-column sortable column-key="dureeTravaux" prop="dureeTravaux" min-width="120" label="Durée des travaux">
                    <template slot="header">
                        <span title=""Durée des travaux">"Durée des travaux</span>
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
                <el-table-column sortable column-key="indexationIcc" prop="indexationIcc" min-width="100" label="Indexation ICC">
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
                <el-table-column sortable column-key="prixFoncier" prop="prixFoncier" min-width="140" label="Dont prix du foncier">
                    <template slot="header">
                        <span title="Dont prix du foncier">Dont prix du foncier</span>
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
                <el-table-column sortable column-key="subventionsAutres" prop="subventionsAutres" min-width="120" label="Autres subventions">
                    <template slot="header">
                        <span title="Autres subventions">Autres subventions</span>
                    </template>
                </el-table-column>
                <el-table-column sortable column-key="total" prop="total" label="Total">
                    <template slot="header">
                        <span title="Total">Total</span>
                    </template>
                </el-table-column>
                <el-table-column sortable column-key="resteFinancer" prop="resteFinancer" min-width="100" label="Reste à financer">
                    <template slot="header">
                        <span title="Reste à financer">Reste à financer</span>
                    </template>
                </el-table-column>
                <el-table-column sortable column-key="tfpbTauxEvolution" prop="tfpbTauxEvolution" min-width="100" label="Taux d'evolution TFPB">
                    <template slot="header">
                        <span title="Taux d'evolution TFPB">Taux d'evolution TFPB</span>
                    </template>
                </el-table-column>
                <el-table-column sortable column-key="maintenanceTauxEvolution" prop="maintenanceTauxEvolution" min-width="100" label="Taux d'evolution Maintenance Courante">
                    <template slot="header">
                        <span title="Taux d'evolution Maintenance Courante">Taux d'evolution Maintenance Courante</span>
                    </template>
                </el-table-column>
                <el-table-column sortable column-key="grosTauxEvolution" prop="grosTauxEvolution" min-width="100" label="Taux d'evolution Gros entretien">
                    <template slot="header">
                        <span title="Taux d'evolution Gros entretien">Taux d'evolution Gros entretien</span>
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
            <el-button type="primary" :disabled="!isModify" @click="showCreateModal">Créer une opération identifiée</el-button>
        </el-row>

        <el-dialog
            title="Création/Modification d'une opération nouvelle identifiée"
            :visible.sync="dialogVisible"
            :close-on-click-modal="false"
            width="80%">
            <el-row v-if="isEdit" class="form-slider text-center mb-4">
                <i class="el-icon-back font-weight-bold" @click="back"></i>
                <i class="el-icon-right font-weight-bold" @click="next"></i>
            </el-row>
            <el-form label-position="left" :model="form" :rules="formRules" label-width="160px" ref="operationForm">
                <el-tabs v-model="activeTab" type="card">
                    <el-tab-pane label="Caractéristiques Générales" name="1">
                        <el-collapse v-model="collapseList">
                            <el-collapse-item title="Données Générales" name="1">
                                <el-row :gutter="20">
                                    <el-col :span="6">
                                        <el-form-item label="N° de l’opération" prop="nOperation">
                                            <el-input type="text" v-model="form.nOperation" placeholder="N° de l’opération" readonly></el-input>
                                        </el-form-item>
                                    </el-col>
                                    <el-col :span="6">
                                        <el-form-item label="Nom de l'opération" prop="nom">
                                            <el-input type="text" v-model="form.nom" placeholder="Nom"></el-input>
                                        </el-form-item>
                                    </el-col>
                                    <el-col :span="6">
                                        <el-form-item label="Nature de l’opération" prop="natureOperation">
                                            <el-select v-model="form.natureOperation" @change="changeNature">
                                                <el-option v-for="item in natures"
                                                           :key="item.value"
                                                           :label="item.label"
                                                           :value="item.value"></el-option>
                                            </el-select>
                                        </el-form-item>
                                    </el-col>
                                    <el-col :span="6">
                                        <el-form-item label="Secteur de financement" prop="secteurFinancement">
                                            <el-select v-model="form.secteurFinancement">
                                                <el-option v-for="item in financements"
                                                           :key="item.value"
                                                           :label="item.label"
                                                           :value="item.value"></el-option>
                                            </el-select>
                                        </el-form-item>
                                    </el-col>
                                </el-row>
                                <el-row :gutter="20">
                                    <el-col :span="6">
                                        <el-checkbox v-model="form.conventionAnru">Convention ANRU</el-checkbox>
                                    </el-col>
                                </el-row>
                                <el-row :gutter="20">
                                    <el-col :span="6">
                                        <el-form-item label="Vide/Occupé" prop="videOccupe">
                                            <el-select v-model="form.videOccupe">
                                                <el-option v-for="item in videOccupes"
                                                           :key="item.value"
                                                           :label="item.label"
                                                           :value="item.value"></el-option>
                                            </el-select>
                                        </el-form-item>
                                    </el-col>
                                    <el-col :span="6">
                                        <el-form-item label="Surface quittancée en m²" prop="surfaceQuittancee">
                                            <el-input type="text" v-model="form.surfaceQuittancee" placeholder="0"
                                                      @change="formatInput('surfaceQuittancee')"></el-input>
                                        </el-form-item>
                                    </el-col>
                                    <el-col :span="6">
                                        <el-form-item label="Nombre de logements" prop="nombreLogements">
                                            <el-input type="text" v-model="form.nombreLogements" placeholder="0"
                                                      @change="formatInput('nombreLogements')"></el-input>
                                        </el-form-item>
                                    </el-col>
                                </el-row>
                                <el-row :gutter="20">
                                <el-col :span="6">
                                    <el-form-item label="TFPB en €/logt" prop="tfpbLogt">
                                        <el-input type="text" v-model="form.tfpbLogt" placeholder="0"
                                                  @change="formatInput('tfpbLogt')"></el-input>
                                    </el-form-item>
                                </el-col>
                                    <el-col :span="6">
                                        <el-form-item label="Durée exonération TFPB (année)" prop="tfpbDuree">
                                            <el-input type="text" v-model="form.tfpbDuree" placeholder="0"
                                                      @change="formatInput('tfpbDuree')"></el-input>
                                        </el-form-item>
                                    </el-col>
                                    <el-col :span="6">
                                        <el-form-item label="Durée des travaux" prop="dureeTravaux">
                                            <el-input type="text" v-model="form.dureeTravaux" placeholder="0"
                                                      @change="formatInput('dureeTravaux')"></el-input>
                                        </el-form-item>
                                    </el-col>
                                </el-row>
                            </el-collapse-item>
                            <el-collapse-item title="Calendrier de l'opération" name="2">
                                <el-row :gutter="20">
                                    <el-col :span="6">
                                        <el-form-item label="Année d'agrément" prop="anneAgrement">
                                            <el-input type="text" v-model="form.anneAgrement" placeholder="0"
                                                      @change="formatInput('anneAgrement')"></el-input>
                                        </el-form-item>
                                    </el-col>
                                    <el-col :span="6">
                                        <el-form-item label="Date d'ordre de service" prop="dateOrdreService">
                                            <el-date-picker
                                                v-model="form.dateOrdreService"
                                                type="month"
                                                format="MM/yyyy"
                                                :picker-options="datePickerOptions">
                                            </el-date-picker>
                                        </el-form-item>
                                    </el-col>
                                    <el-col :span="6">
                                        <el-form-item label="Date de mise en service" prop="dateMiseService">
                                            <el-date-picker
                                                v-model="form.dateMiseService"
                                                type="month"
                                                format="MM/yyyy"
                                                :picker-options="datePickerOptions">
                                            </el-date-picker>
                                        </el-form-item>
                                        <el-form-item label-width="0" prop="acquisitionFin">
                                            <el-checkbox v-model="form.acquisitionFin">Acquisition fin de mois</el-checkbox>
                                            <el-tooltip class="item" effect="dark" content="les loyers seront pris en compte à partir du mois suivant l'acquisition" placement="top">
                                                <i class="el-icon-info"></i>
                                            </el-tooltip>
                                        </el-form-item>
                                    </el-col>
                                </el-row>
                            </el-collapse-item>
                            <el-collapse-item title="Loyers" name="3">
                                <el-row :gutter="20">
                                    <el-col :span="8">
                                        <el-form-item label="Loyer mensuel €/m²" prop="loyerMensuel">
                                            <el-input type="text" v-model="form.loyerMensuel" placeholder="0"
                                                      @change="formatInput('loyerMensuel')"></el-input>
                                        </el-form-item>
                                        <el-checkbox v-model="form.indexeIrl">Indexé à I'IRL</el-checkbox>
                                    </el-col>
                                    <el-col :span="8">
                                        <el-form-item label="Profil de hausse des loyers" prop="profilLoyer">
                                            <el-select v-model="form.profilLoyer">
                                                <el-option v-for="item in profilLoyers"
                                                    :key="item.id"
                                                    :label="item.nom"
                                                    :value="item.id"></el-option>
                                            </el-select>
                                        </el-form-item>
                                    </el-col>
                                </el-row>
                            </el-collapse-item>
                            <el-collapse-item title="Lots Annexes" name="4">
                                <el-row :gutter="20">
                                    <el-col :span="8">
                                        <el-form-item label="Nombre de garages/packings" prop="nombreGarages">
                                            <el-input type="text" v-model="form.nombreGarages" placeholder="0"
                                                      @change="formatInput('nombreGarages')"></el-input>
                                        </el-form-item>
                                        <el-form-item label="Nombre de commerces" prop="nombreCommerces">
                                            <el-input type="text" v-model="form.nombreCommerces" placeholder="0"
                                                      @change="formatInput('nombreCommerces')"></el-input>
                                        </el-form-item>
                                    </el-col>
                                    <el-col :span="8">
                                        <el-form-item label="Loyer mensuel des garages/parkings en €/lot" prop="loyerMensuelGarages">
                                            <el-input type="text" v-model="form.loyerMensuelGarages" placeholder="0"
                                                      @change="formatInput('loyerMensuelGarages')"></el-input>
                                        </el-form-item>
                                        <el-form-item label="Loyer mensuel par commerces en €/lot" prop="loyerMensuelCommerces">
                                            <el-input type="text" v-model="form.loyerMensuelCommerces" placeholder="0"
                                                      @change="formatInput('loyerMensuelCommerces')"></el-input>
                                        </el-form-item>
                                    </el-col>
                                </el-row>
                            </el-collapse-item>
                            <el-collapse-item title="Données complémentaires en K€" name="5">
                                <el-row class="mt-4">
                                    <el-col :span="4" style="padding-top: 55px;">
                                        <div class="carousel-head">
                                            <p>Taux évolution complément loyers</p>
                                        </div>
                                        <div class="carousel-head">
                                            <p>Complément de loyers net de vacance</p>
                                        </div>
                                        <div class="carousel-head">
                                            <p>Compléments d'annuités - part capital</p>
                                        </div>
                                        <div class="carousel-head">
                                            <p>Compléments d'annuités - part intérêts</p>
                                        </div>
                                        <div class="carousel-head">
                                            <p>Taux de vacance spécifique en %</p>
                                        </div>
                                        <div class="carousel-head">
                                            <p>TFPB</p>
                                        </div>
                                        <div class="carousel-head">
                                            <p>Maintenance Courante</p>
                                        </div>
                                        <div class="carousel-head">
                                            <p>Gros entretien</p>
                                        </div>
                                        <div class="carousel-head">
                                            <p>Dépôt de garantie</p>
                                        </div>
                                    </el-col>
                                    <el-col :span="2" class="text-center" style="padding-top: 20px;">
                                        <div class="carousel-head">
                                            <p>Taux d'évolution</p>
                                        </div>
                                        <div class="carousel-head" style="margin-top: 230px;">
                                            <el-input type="text" v-model="form.tfpbTauxEvolution" placeholder="0" class="fixed-input"
                                                      @change="formatInput('tfpbTauxEvolution')"></el-input>
                                        </div>
                                        <div class="carousel-head">
                                            <el-input type="text" v-model="form.maintenanceTauxEvolution" placeholder="0" class="fixed-input"
                                                      @change="formatInput('maintenanceTauxEvolution')"></el-input>
                                        </div>
                                        <div class="carousel-head">
                                            <el-input type="text" v-model="form.grosTauxEvolution" placeholder="0" class="fixed-input"
                                                      @change="formatInput('grosTauxEvolution')"></el-input>
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
									</el-col>
                                    <el-col :span="8">
                                        <el-form-item label="Dont prix du foncier" prop="prixFoncier">
                                            <el-input type="text" v-model="form.prixFoncier" placeholder="0" class="fixed-input"
                                                      @change="formatInput('prixFoncier')"></el-input>
                                        </el-form-item>
                                        <el-checkbox v-model="form.indexationIcc">Indexation à I'ICC</el-checkbox>
                                    </el-col>
                                    <el-col :span="8">
                                        <el-form-item label="Choisir un modèle d'amortissement" prop="modeleDamortissement">
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
                                <el-row :gutter="20" class="mt-4">
                                    <el-col :span="14">
                                        <el-row :gutter="20" class="mt-3">
                                            <el-col :span="14">
                                                <el-form-item label="Montant des fonds Propres" prop="fondsPropres">
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
                                            </el-col>
                                            <el-col :span="10">
                                                <p><strong>Montant des subventions en K€</strong></p>
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
                                                <el-form-item label="Autres subventions" prop="subventionsAutres">
                                                    <el-input type="text" v-model="form.subventionsAutres" placeholder="0" class="fixed-input"
                                                              @change="formatInput('subventionsAutres')"></el-input>
                                                </el-form-item>
                                            </el-col>
                                        </el-row>
                                    </el-col>
                                    <el-col :span="10" class="mt-3">
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
                                                <el-form-item label="Date de première échéance" label-width="105px" prop="datePremier">
                                                    <el-date-picker
                                                        v-model="form.datePremier"
                                                        type="month"
                                                        format="MM/yyyy"
                                                        :picker-options="datePickerOptions"
                                                        value-format="yyyy-MM-dd"
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
                                            :data="form.typeEmpruntOperation"
                                            style="width: 100%">
                                            <el-table-column sortable column-key="typesEmprunts" prop="typesEmprunts" min-width="60" label="N°">
                                                <template slot-scope="scope">
                                                    {{scope.row.typesEmprunts.nom}}
                                                </template>
                                            </el-table-column>
                                            <el-table-column sortable column-key="montant" prop="montant" min-width="100" label="Montant" align="center"></el-table-column>
                                            <el-table-column sortable column-key="datePremiere" prop="datePremiere" min-width="100" label="Date de 1ère annuité" align="center">
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
                                    </el-col>
                                </el-row>
                            </el-collapse-item>
                        </el-collapse>
                    </el-tab-pane>
                </el-tabs>
            </el-form>
            <span slot="footer" class="dialog-footer">
                <el-button type="primary" @click="dialogVisible = false">Retour</el-button>
                <el-button type="success" @click="save('operationForm')"  :disabled="isSubmitting">Valider</el-button>
            </span>
        </el-dialog>
    </div>
</template>

<script>
import moment from "moment";
import { initPeriodic, periodicFormatter, checkAllPeriodics, mathInput } from '../../../../../../../utils/inputs';
import { updateData, getFloat } from '../../../../../../../utils/helpers'
import customValidator from '../../../../../../../utils/validation-rules';
import Periodique from '../../../../../../../components/partials/Periodique';

export default {
    name: "OperationIdentifiees",
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
            operations: [],
            dialogVisible: false,
            collapseList: ['1'],
            form: null,
            isEdit: false,
            selectedIndex: null,
            activeTab: '1',
            isSubmitting: false,
            availableTypesEmprunts: [],
            columns: [],
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
            videOccupes: [
                { label: 'Vide', value: 1 },
                { label: 'Occupé', value: 2 }
            ],
            financements: [
                { label: 'PLUS', value: 1 },
                { label: 'PLAI', value: 2 },
                { label: 'PLS', value: 3 },
                { label: 'PLI', value: 4 },
                { label: 'Autres', value: 5 }
            ],
            formRules: {
                nOperation: [
                    { required: true, message: "Veuillez sélectionner un n° de l’opération", trigger: 'change' }
                ],
                nom: [
                    { required: true, message: "Veuillez saisir un nom pour cette opération", trigger: 'change' }
                ],
                tfpbLogt: customValidator.getRule('positiveInt'),
                tfpbDuree: customValidator.getRule('positiveInt'),
                prixFoncier: customValidator.getPreset('number.positiveInt'),
                prixRevient: customValidator.getRule('positiveInt'),
                fondsPropres: customValidator.getPreset('number.positiveDouble'),
                subventionsEtat: customValidator.getRule('positiveDouble'),
                subventionsAnru: customValidator.getRule('positiveDouble'),
                subventionsEpci: customValidator.getRule('positiveDouble'),
                subventionsDepartement: customValidator.getRule('positiveDouble'),
                subventionsRegion: customValidator.getRule('positiveDouble'),
                subventionsCollecteur: customValidator.getRule('positiveDouble'),
                subventionsAutres: customValidator.getRule('positiveDouble'),
                montant: customValidator.getPreset('number.positiveDouble'),
                datePremier: customValidator.getRule('required'),
                surfaceQuittancee: customValidator.getRule('positiveDouble'),
                nombreLogements: customValidator.getRule('positiveDouble'),
                loyerMensuel: customValidator.getRule('positiveDouble'),
                dureeTravaux: customValidator.getRule('positiveDouble'),
                nombreGarages: customValidator.getRule('positiveDouble'),
                loyerMensuelGarages: customValidator.getRule('positiveInt'),
                nombreCommerces: customValidator.getRule('positiveDouble'),
                loyerMensuelCommerces: customValidator.getRule('positiveDouble'),
                anneAgrement: customValidator.getRule('positiveDouble'),
                resteFinancer: [
                    { validator: resteFinancerValidate, trigger: 'blur' }
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
                typeEmpruntOperation: [],
                indexeIrl: false,
                periodiques: {
                    tauxEvolutionLoyers: initPeriodic(),
                    complementLoyers: initPeriodic(),
                    complementAnnuiteCapitals: initPeriodic(),
                    complementAnnuiteInterets: initPeriodic(),
                    tauxVacances: initPeriodic(),
                    tfpbs: initPeriodic(),
                    maintenanceCourantes: initPeriodic(),
                    grosEntretiens: initPeriodic(),
                    depotGaranties: initPeriodic()
                }
            }
        },
        tableData(data) {
            if (!_.isNil(data)) {
                let operations = data.operations.items.map(item => {
                    if (item.type === 0) {
                        let tauxEvolutionLoyers = [];
                        let complementLoyers = [];
                        let complementAnnuiteCapitals = [];
                        let complementAnnuiteInterets = [];
                        let tauxVacances = [];
                        let tfpbs = [];
                        let maintenanceCourantes = [];
                        let grosEntretiens = [];
                        let depotGaranties = [];
                        item.operationPeriodique.items.forEach(periodique => {
                            tauxEvolutionLoyers[periodique.iteration - 1] = periodique.tauxEvolutionLoyer;
                            complementLoyers[periodique.iteration - 1] = periodique.complementLoyer;
                            complementAnnuiteCapitals[periodique.iteration - 1] = periodique.complementAnnuiteCapital;
                            complementAnnuiteInterets[periodique.iteration - 1] = periodique.complementAnnuiteInteret;
                            tauxVacances[periodique.iteration - 1] = periodique.tauxVacance;
                            tfpbs[periodique.iteration - 1] = periodique.tfpb;
                            maintenanceCourantes[periodique.iteration - 1] = periodique.maintenanceCourante;
                            grosEntretiens[periodique.iteration - 1] = periodique.grosEntretien;
                            depotGaranties[periodique.iteration - 1] = periodique.depotGarantie;
                        });

                        let row = {...item};
                        row.profilLoyer = item.profilLoyer ? item.profilLoyer.id : null;
                        row.profilLoyerNom = item.profilLoyer ? item.profilLoyer.nom : null;
                        row.modeleDamortissement = item.modeleDamortissement ? item.modeleDamortissement.id: null;
                        row.typeEmpruntOperation = item.typeEmpruntOperation.items;
                        row.periodiques = {
                            tauxEvolutionLoyers,
                            complementLoyers,
                            complementAnnuiteCapitals,
                            complementAnnuiteInterets,
                            tauxVacances,
                            tfpbs,
                            maintenanceCourantes,
                            grosEntretiens,
                            depotGaranties
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
                                message: 'Cette opération identifiée a bien été supprimée.',
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
                                videOccupe: this.form.videOccupe,
                                acquisitionFin: this.form.acquisitionFin,
                                conventionAnru: this.form.conventionAnru,
                                secteurFinancement: this.form.secteurFinancement,
                                surfaceQuittancee: this.form.surfaceQuittancee,
                                nombreLogements: this.form.nombreLogements,
                                loyerMensuel: this.form.loyerMensuel,
                                dureeTravaux: this.form.dureeTravaux,
                                profilLoyerUUID: this.form.profilLoyer,
                                nombreGarages: this.form.nombreGarages,
                                loyerMensuelGarages: this.form.loyerMensuelGarages,
                                nombreCommerces: this.form.nombreCommerces,
                                tfpbLogt: this.form.tfpbLogt,
                                loyerMensuelCommerces: this.form.loyerMensuelCommerces,
                                anneAgrement: this.form.anneAgrement,
                                dateOrdreService: this.form.dateOrdreService,
                                dateMiseService: this.form.dateMiseService,
                                tfpbDuree: this.form.tfpbDuree,
                                indexationIcc: this.form.indexationIcc,
                                prixFoncier: this.form.prixFoncier,
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
                                tfpbTauxEvolution: this.form.tfpbTauxEvolution,
                                maintenanceTauxEvolution: this.form.maintenanceTauxEvolution,
                                grosTauxEvolution: this.form.grosTauxEvolution,
                                typeEmprunts: typeEmpruntOperation,
                                type: 0,
                                periodique: JSON.stringify({
                                    taux_evolution_loyer: this.form.periodiques.tauxEvolutionLoyers,
                                    complement_loyer: this.form.periodiques.complementLoyers,
                                    complement_annuite_capital: this.form.periodiques.complementAnnuiteCapitals,
                                    complement_annuite_interet: this.form.periodiques.complementAnnuiteInterets,
                                    taux_vacance: this.form.periodiques.tauxVacances,
                                    tfpb: this.form.periodiques.tfpbs,
                                    maintenance_courante: this.form.periodiques.maintenanceCourantes,
                                    gros_entretien: this.form.periodiques.grosEntretiens,
                                    depot_garantie: this.form.periodiques.depotGaranties,
                                })
                            }
                        }
                    }).then(() => {
                        this.isSubmitting = false;
                        this.dialogVisible = false;
                        updateData(this.query, this.simulationID).then(() => {
                            this.$message({
                                showClose: true,
                                message: 'L\'opération identifiée a bien été enregistrée.',
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
        changeNature(val) {
            if (val === 3 || val === 4) {
                this.formRules.prixRevient = customValidator.getPreset('number.positiveInt');
            } else {
                this.formRules.prixRevient = customValidator.getRule('positiveInt');
            }
            this.form.acquisitionFin = val >= 3 && val <= 6;
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
                    label: (parseInt(this.anneeDeReference) + i).toString() + ' Taux évolution complément loyers',
                    prop: `periodiques.tauxEvolutionLoyers[${i}]`
                });
                this.columns.push({
                    label: (parseInt(this.anneeDeReference) + i).toString() + ' Complément de loyers net de vacances variation',
                    prop: `periodiques.complementLoyers[${i}]`
                });
                this.columns.push({
                    label: (parseInt(this.anneeDeReference) + i).toString() + ' Compléments d\'annuités - part capital',
                    prop: `periodiques.complementAnnuiteCapitals[${i}]`
                });
                this.columns.push({
                    label: (parseInt(this.anneeDeReference) + i).toString() + ' Compléments d\'annuités - part intérèt',
                    prop: `periodiques.complementAnnuiteInterets[${i}]`
                });
                this.columns.push({
                    label: (parseInt(this.anneeDeReference) + i).toString() + ' Taux de vacance en %',
                    prop: `periodiques.tauxVacances[${i}]`
                });
                this.columns.push({
                    label: (parseInt(this.anneeDeReference) + i).toString() + ' TFPB',
                    prop: `periodiques.tfpbs[${i}]`
                });
                this.columns.push({
                    label: (parseInt(this.anneeDeReference) + i).toString() + ' Maintenance Courante',
                    prop: `periodiques.maintenanceCourantes[${i}]`
                });
                this.columns.push({
                    label: (parseInt(this.anneeDeReference) + i).toString() + ' Gros entretien',
                    prop: `periodiques.grosEntretiens[${i}]`
                });
                this.columns.push({
                    label: (parseInt(this.anneeDeReference) + i).toString() + ' Dépot de garantie',
                    prop: `periodiques.depotGaranties[${i}]`
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
