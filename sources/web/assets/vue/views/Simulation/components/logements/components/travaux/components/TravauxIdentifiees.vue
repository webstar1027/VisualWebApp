<template>
    <div class="travaux-identifiees">
        <div v-if="error">Une erreur est survenue !</div>
        <el-row class="mb-3">
            <el-col :span="2" :offset="18">
                <el-upload
                  ref="upload"
                  :action.native="'/import-travaux-immobilises-identifiees/'+ simulationID"
                  multiple
                  :limit="10"
                  :on-success="onSuccess"
                  :on-error="onError">
                  <el-button size="small" type="primary">Importer</el-button>
                </el-upload>
            </el-col>
            <el-col :span="2">
                <el-button type="success" @click.stop="exportTravauxImmobilisesIdentifiees">
                    Exporter
                </el-button>
            </el-col>
        </el-row>
        <el-table
            class="fw"
            v-loading="isLoading"
            :data="tableData(data)"
            style="width: 100%">
            <el-table-column sortable column-key="nGroupe" prop="nGroupe" min-width="120" label="N° groupe">
                <template slot="header">
                    <span title="N° groupe">N° groupe</span>
                </template>
            </el-table-column>
            <el-table-column sortable column-key="nSousGroupe" prop="nSousGroupe" min-width="140" label="N° sous groupe">
                <template slot="header">
                    <span title="N° sous groupe">N° sous groupe</span>
                </template>
            </el-table-column>
            <el-table-column sortable column-key="nomGroupe" prop="nomGroupe" min-width="150" label="Nom du groupe">
                <template slot="header">
                    <span title="Nom du groupe">Nom du groupe</span>
                </template>
            </el-table-column>
            <el-table-column sortable column-key="information" prop="information" min-width="130" label="Informations">
                <template slot="header">
                    <span title="Informations">Informations</span>
                </template>
            </el-table-column>
            <el-table-column sortable column-key="profilEvolutionLoyerNumero" prop="profilEvolutionLoyerNumero" min-width="160" label="Profil de loyer N°">
                <template slot="header">
                    <span title="Profil de loyer N°">Profil de loyer N°</span>
                </template>
            </el-table-column>
            <el-table-column sortable column-key="loyerMensuelInitial" prop="loyerMensuelInitial" min-width="130" label="Loyer mensuel Inital">
                <template slot="header">
                    <span title="Loyer mensuel Inital">Loyer mensuel Inital</span>
                </template>
            </el-table-column>
            <el-table-column sortable column-key="numeroTranche" prop="numeroTranche" min-width="120" label="N° tranche">
                <template slot="header">
                    <span title="N° tranche">N° tranche</span>
                </template>
            </el-table-column>
            <el-table-column sortable column-key="nomTranche" prop="nomTranche" min-width="170" label="Nom de la tranche">
                <template slot="header">
                    <span title="Nom de la tranche">Nom de la tranche</span>
                </template>
            </el-table-column>
            <el-table-column sortable column-key="surfaceTraitee" prop="surfaceTraitee" min-width="140" label="Surface traitée">
                <template slot="header">
                    <span title="Surface traitée">Surface traitée</span>
                </template>
            </el-table-column>
            <el-table-column sortable column-key="variationSurfaceQuittance" prop="variationSurfaceQuittance" min-width="140" label="Variation de la surface quittancée après travaux">
                <template slot="header">
                    <span title="Variation de la surface quittancée après travaux">Variation de la surface quittancée après travaux</span>
                </template>
            </el-table-column>
            <el-table-column sortable column-key="nombreLogement" prop="nombreLogement" min-width="130" label="Nombre de logements">
                <template slot="header">
                    <span title="Nombre de logements">Nombre de logements</span>
                </template>
            </el-table-column>
            <el-table-column sortable column-key="variationNombreLogement" prop="variationNombreLogement" min-width="130" label="Variation du nombre de logements après travaux">
                <template slot="header">
                    <span title="Variation du nombre de logements après travaux">Variation du nombre de logements après travaux</span>
                </template>
            </el-table-column>
            <el-table-column sortable column-key="anneeAgreement" prop="anneeAgreement" min-width="170" label="Année d'agrément">
                <template slot="header">
                    <span title="N°">N°</span>
                </template>
            </el-table-column>
            <el-table-column sortable column-key="dateOrdreService" prop="dateOrdreService" min-width="170" label="Date d'ordre service">
                <template slot-scope="scope">
                    {{scope.row.dateOrdreService | dateFR}}
                </template>
                <template slot="header">
                    <span title="Année d'agrément">Année d'agrément</span>
                </template>
            </el-table-column>
            <el-table-column sortable column-key="dateFinTravaux" prop="dateFinTravaux" min-width="170" label="Date de fin de travaux">
                <template slot-scope="scope">
                    {{scope.row.dateFinTravaux | dateFR}}
                </template>
                <template slot="header">
                    <span title="Date de fin de travaux">Date de fin de travaux</span>
                </template>
            </el-table-column>
            <el-table-column sortable column-key="conventionAnru" prop="conventionAnru" min-width="130" label="Convention ANRU">
                <template slot-scope="scope">
                    {{scope.row.conventionAnru ? 'Oui' : 'Non'}}
                </template>
                <template slot="header">
                    <span title="Convention ANRU">Convention ANRU</span>
                </template>
            </el-table-column>
            <el-table-column sortable column-key="indexationIcc" prop="indexationIcc" min-width="130" label="Indexation à I'ICC">
                <template slot-scope="scope">
                    {{scope.row.indexationIcc ? 'Oui' : 'Non'}}
                </template>
                <template slot="header">
                    <span title="Indexation à I'ICC">Indexation à I'ICC</span>
                </template>
            </el-table-column>
            <el-table-column sortable column-key="tauxVariationLoyer" prop="tauxVariationLoyer" min-width="170" label="Taux de variation du loyer après travaux">
                <template slot="header">
                    <span title="Taux de variation du loyer après travaux">Taux de variation du loyer après travaux</span>
                </template>
            </el-table-column>
            <el-table-column sortable column-key="dateApplication" prop="dateApplication" min-width="170" label="Date d'application de la variation de loyer">
                <template slot-scope="scope">
                    {{scope.row.dateApplication | dateFR}}
                </template>
                <template slot="header">
                    <span title="Date d'application de la variation de loyer">Date d'application de la variation de loyer</span>
                </template>
            </el-table-column>
            <el-table-column sortable column-key="modeleDamortissementNom" prop="modeleDamortissementNom" min-width="150" label="Modèle d'amortissement">
                <template slot="header">
                    <span title="Modèle d'amortissement">Modèle d'amortissement</span>
                </template>
            </el-table-column>
            <el-table-column sortable column-key="prixRevient" prop="prixRevient" min-width="140" label="Prix de revient">
                <template slot="header">
                    <span title="Prix de revient">Prix de revient</span>
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
            <el-table-column fixed="right" width="120" label="Actions">
                <template slot-scope="scope">
                    <el-button type="primary" :disabled="!isModify" icon="el-icon-edit" circle @click="handleEdit(scope.$index, scope.row)"></el-button>
                    <el-button type="danger" :disabled="!isModify"  icon="el-icon-delete" circle @click="handleDelete(scope.$index, scope.row)"></el-button>
                </template>
            </el-table-column>
        </el-table>
        <el-row class="d-flex justify-content-end my-3">
            <el-button type="primary" :disabled="!isModify" @click="showCreateModal()">Création d’une nouvelle fiche de travaux immobilisée identifiée</el-button>
        </el-row>

        <el-dialog
            title="Création/Modification des fiches de travaux immobilisés identifiés"
            :visible.sync="dialogVisible"
            :close-on-click-modal="false"
            width="75%">
            <el-row v-if="isEdit" class="form-slider text-center mb-4">
                <i class="el-icon-back font-weight-bold" @click="back"></i>
                <i class="el-icon-right font-weight-bold" @click="next"></i>
            </el-row>
            <el-form label-position="left" :model="form" :rules="formRules" label-width="150px" ref="travauxImmobiliseForm">
                <el-tabs v-model="activeTab" type="card">
                    <el-tab-pane label="Caractérisitiques Générales" name="1">
                        <el-collapse v-model="collapseList">
                            <el-collapse-item title="Données du groupe" name="1">
                                <el-row :gutter="20">
                                    <el-col :span="6">
                                        <el-form-item label="N° de groupe" prop="nGroupe">
                                            <el-select v-model="form.nGroupe" @change="changeNgroupe">
                                                <el-option v-for="item in patrimoines"
                                                    :key="item.id"
                                                    :label="item.nGroupe"
                                                    :value="item.nGroupe"></el-option>
                                            </el-select>
                                        </el-form-item>
                                    </el-col>
                                    <el-col :span="6">
                                        <el-form-item label="N° de sous groupe" prop="nSousGroupe">
                                            <el-input type="text" v-model="form.nSousGroupe" placeholder="N° sous groupe" readonly></el-input>
                                        </el-form-item>
                                    </el-col>
                                    <el-col :span="6">
                                        <el-form-item label="Informations" prop="information">
                                            <el-input type="text" v-model="form.information" readonly></el-input>
                                        </el-form-item>
                                    </el-col>
                                    <el-col :span="6">
                                        <el-form-item label="Nom de l'opération" prop="nomGroupe">
                                            <el-input type="text" v-model="form.nomGroupe" readonly></el-input>
                                        </el-form-item>
                                    </el-col>
                                </el-row>
                                <el-row :gutter="20">
                                    <el-col :span="6">
                                        <el-checkbox v-model="form.conventionAnru" class="mt-3">Convention ANRU</el-checkbox>
                                    </el-col>
                                </el-row>
                                <el-row :gutter="20">
                                    <el-col :span="6">
                                        <el-form-item label="Loyer mensuel inital €/m²" prop="loyerMensuelInitial">
                                            <el-input type="text" placeholder="0" class="fixed-input"
                                                      v-model="form.loyerMensuelInitial"
                                                      @change="formatInput('loyerMensuelInitial')"></el-input>
                                        </el-form-item>
                                    </el-col>
                                    <el-col :span="6">
                                        <el-form-item label="Profil de hausse des loyers" prop="profilEvolutionLoyerNumero">
                                            <el-input type="text" v-model="form.profilEvolutionLoyerNumero" placeholder="0" readonly></el-input>
                                        </el-form-item>
                                    </el-col>
                                </el-row>
                            </el-collapse-item>
                            <el-collapse-item title="Données de la tranche" name="2">
                                <el-row :gutter="20">
                                    <el-col :span="6">
                                        <el-form-item label="N° tranche" prop="numeroTranche">
                                            <el-input type="text" v-model="form.numeroTranche" placeholder="0" class="fixed-input"></el-input>
                                        </el-form-item>
                                    </el-col>
                                    <el-col :span="6">
                                        <el-form-item label="Nom de la tranche" prop="nomTranche">
                                            <el-input type="text" v-model="form.nomTranche"></el-input>
                                        </el-form-item>
                                    </el-col>
                                </el-row>

                                <el-row :gutter="24">
                                    <el-col :span="6">
                                    <el-form-item label="Surface traitée (m²)" prop="surfaceTraitee">
                                        <el-input type="text"  placeholder="0" class="fixed-input"
                                                  v-model="form.surfaceTraitee"
                                                  @change="formatInput('surfaceTraitee')"></el-input>
                                    </el-form-item>
                                    </el-col>
                                    <el-col :span="6">
                                    <el-form-item label="Variation de la surface quittancée (m²)" prop="variationSurfaceQuittance">
                                        <el-input type="text"  placeholder="0" class="fixed-input"
                                                  v-model="form.variationSurfaceQuittance"
                                                  @change="formatInput('variationSurfaceQuittance')"></el-input>
                                    </el-form-item>
                                    </el-col>
                                    <el-col :span="6">
                                        <el-form-item label="Nombre de logements" prop="nombreLogement">
                                            <el-input type="text"  placeholder="0" class="fixed-input"
                                                      v-model="form.nombreLogement"
                                                      @change="formatInput('nombreLogement')"></el-input>
                                        </el-form-item>
                                    </el-col>
                                    <el-col :span="6">
                                        <el-form-item label="Variation du nombre de logement" prop="variationNombreLogement">
                                            <el-input type="text"  placeholder="0" class="fixed-input"
                                                      v-model="form.variationNombreLogement"
                                                      @change="formatInput('variationNombreLogement')"></el-input>
                                        </el-form-item>
                                    </el-col>
                                </el-row>
                            </el-collapse-item>
                            <el-collapse-item title="Calendrier des travaux" name="3">
                                <el-row :gutter="20">
                                    <el-col :span="6">
                                        <el-form-item label="Année d'Agrément" prop="anneeAgreement">
                                            <el-input type="text" v-model="form.anneeAgreement" placeholder="0" class="fixed-input"></el-input>
                                        </el-form-item>
                                    </el-col>
                                    <el-col :span="6">
                                        <el-form-item label="Date d'ordre service" prop="dateOrdreService">
                                            <el-date-picker
                                                v-model="form.dateOrdreService"
                                                type="month"
                                                format="MM/yyyy"
                                                :picker-options="datePickerOptions"
                                                placeholder="Date de démolition">
                                            </el-date-picker>
                                        </el-form-item>
                                    </el-col>
                                    <el-col :span="6">
                                        <el-form-item label="Date de mise en service" prop="dateFinTravaux">
                                            <el-date-picker
                                                v-model="form.dateFinTravaux"
                                                type="month"
                                                format="MM/yyyy"
                                                :picker-options="datePickerOptions"
                                                placeholder="Date de démolition">
                                            </el-date-picker>
                                        </el-form-item>
                                    </el-col>
                                </el-row>
                            </el-collapse-item>
                            <el-collapse-item title="Evolution des loyers après travaux" name="4">
                                <el-row :gutter="20">
                                    <el-col :span="6">
                                        <el-form-item label="Taux de variation du loyer" prop="tauxVariationLoyer">
                                            <el-input type="text" placeholder="0" class="fixed-input"
                                                      v-model="form.tauxVariationLoyer"
                                                      @change="formatInput('tauxVariationLoyer')"></el-input>
                                        </el-form-item>
                                    </el-col>
                                    <el-col :span="6">
                                        <el-form-item label="Date d'application de la variation" prop="dateApplication">
                                            <el-date-picker
                                                v-model="form.dateApplication"
                                                type="month"
                                                format="MM/yyyy"
                                                :picker-options="datePickerOptions"
                                                placeholder="Date d'application">
                                            </el-date-picker>
                                        </el-form-item>
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
                                            <el-input type="text" placeholder="0" class="fixed-input"
                                                      v-model="form.prixRevient"
                                                      @change="formatInput('prixRevient')"></el-input>
                                        </el-form-item>
                                        <el-checkbox v-model="form.indexationIcc">Indexation à I'ICC</el-checkbox>
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
                                <el-row :gutter="20">
                                    <el-col :span="14">
                                        <el-row :gutter="20" class="mt-3">
                                            <el-col :span="14">
                                                <el-form-item label="Montant de Fonds Propres" prop="foundsPropres">
                                                    <el-input type="text" placeholder="0" class="fixed-input"
                                                              v-model="form.foundsPropres"
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
                                            </el-col>
                                            <el-col :span="10">
                                                <p><strong>Montant des subventions en K€</strong></p>
                                                <el-form-item label="Subventions d'Etat" prop="subventionsEtat">
                                                    <el-input type="text" placeholder="0" class="fixed-input"
                                                              v-model="form.subventionsEtat"
                                                              @change="formatInput('subventionsEtat')"></el-input>
                                                </el-form-item>
                                                    <el-form-item label="Subventions ANRU" prop="subventionsAnru">
                                                    <el-input type="text" placeholder="0" class="fixed-input"
                                                              v-model="form.subventionsAnru"
                                                              @change="formatInput('subventionsAnru')"></el-input>
                                                </el-form-item>
												<el-form-item label="Subventions Régions" prop="subventionsRegion">
													<el-input type="text" placeholder="0" class="fixed-input"
															  v-model="form.subventionsRegion"
															  @change="formatInput('subventionsRegion')"></el-input>
												</el-form-item>
												<el-form-item label="Subventions départements" prop="subventionsDepartement">
													<el-input type="text" placeholder="0" class="fixed-input"
															  v-model="form.subventionsDepartement"
															  @change="formatInput('subventionsDepartement')"></el-input>
												</el-form-item>
                                                <el-form-item label="Subventions EPCI/Communes" prop="subventionsEpci">
                                                    <el-input type="text" placeholder="0" class="fixed-input"
                                                              v-model="form.subventionsEpci"
                                                              @change="formatInput('subventionsEpci')"></el-input>
                                                </el-form-item>
                                                <el-form-item label="Subventions collecteurs" prop="subventionsCollecteur">
                                                    <el-input type="text" placeholder="0" class="fixed-input"
                                                              v-model="form.subventionsCollecteur"
                                                              @change="formatInput('subventionsCollecteurs')"></el-input>
                                                </el-form-item>
                                                <el-form-item label="Autres subventions" prop="autresSubventions">
                                                    <el-input type="text"  placeholder="0" class="fixed-input"
                                                              v-model="form.autresSubventions"
                                                              @change="formatInput('autresSubventions')"></el-input>
                                                </el-form-item>
                                            </el-col>
                                        </el-row>
                                    </el-col>
                                    <el-col :span="10">
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
                                                    <el-input type="text" placeholder="0" class="fixed-input"
                                                              v-model="form.montant"
                                                              @change="formatInput('montant')"></el-input>
                                                </el-form-item>
                                            </el-col>
                                            <el-col :span="15">
                                                <el-form-item label="Date de la 1ère annuité" label-width="105px" prop="datePremier">
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
                                        <p class="mt-5"><strong>Liste des emprunts</strong></p>
                                        <el-table
                                            :data="form.typeEmpruntTravauxImmobilises"
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
                                            <p>Total : {{totalMontant}}</p>
                                        </div>
                                    </el-col>
                                </el-row>
                            </el-collapse-item>
                        </el-collapse>
                    </el-tab-pane>
                </el-tabs>
            </el-form>
            <div slot="footer" class="dialog-footer">
                <el-button type="primary" @click="dialogVisible = false">Retour</el-button>
                <el-button type="success" @click="save('travauxImmobiliseForm')" :disabled="isSubmitting">Valider</el-button>
            </div>
        </el-dialog>
    </div>
</template>

<script>
import { mathInput } from "../../../../../../../utils/inputs";
import { updateData, getFloat } from '../../../../../../../utils/helpers'
import moment from "moment";
import { updateList } from '../../../../../../../utils/helpers';
import customValidator from '../../../../../../../utils/validation-rules';

export default {
    name: "TravauxIdentifiees",
    props: ['simulationID', 'modeleDamortissements', 'typesEmprunts', 'patrimoines', 'data', 'isLoading', 'query', 'error'],
    data() {
        var resteFinancerValidate = (rule, value, callback) => {
            if (this.resteFinancer != 0) {
                callback(new Error("le plan de financement n’est pas équilibré"));
            } else {
                callback();
            }
        };
        var nombreLogementDemolisValidate = (rule, value, callback) => {
            if (value && this.nombreLogementsLimit && value > this.nombreLogementsLimit) {
                callback(new Error("Nombre de logements démolis cannot be more than " + this.nombreLogementsLimit));
            } else {
                callback();
            }
        };
        return {
            travauxImmobilises: [],
            dialogVisible: false,
            activeTab: '1',
            collapseList: ['1'],
            form: null,
            isEdit: false,
            isSubmitting: false,
            selectedIndex: null,
            availableTypesEmprunts: [],
            datePickerOptions: {
            },
            formRules: {
                nGroupe: [
                    { required: true, message: 'Veuillez sélectionner un N° de groupe', trigger: 'change' }
                ],
                numeroTranche: [
                    { required: true, message: 'Veuillez renseigner le N° de tranche', trigger: 'change' }
                ],
                nomTranche: [
                    { required: true, message: 'Veuillez renseigner le nom de la tranche', trigger: 'change' }
                ],
                prixRevient: customValidator.getPreset('number.positiveDouble'),
                foundsPropres: customValidator.getPreset('number.positiveDouble'),
                subventionsEtat: customValidator.getRule('positiveDouble'),
                subventionsAnru: customValidator.getRule('positiveDouble'),
                subventionsEpci: customValidator.getRule('positiveDouble'),
                subventionsDepartement: customValidator.getRule('positiveDouble'),
                subventionsRegion: customValidator.getRule('positiveDouble'),
                subventionsCollecteur: customValidator.getRule('positiveDouble'),
                autresSubventions: customValidator.getRule('positiveDouble'),
                nombreLogement: [
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
                loyerMensuelInitial: null,
                profilEvolutionLoyerId: null,
                numeroTranche: null,
                nomTranche: '',
                conventionAnru: false,
                surfaceTraitee: null,
                variationSurfaceQuittance: null,
                nombreLogement: null,
                variationNombreLogement: null,
                anneeAgreement: null,
                dateOrdreService: new Date(),
                dateFinTravaux: new Date(),
                indexationIcc: false,
                tauxVariationLoyer: null,
                dateApplication: new Date(),
                modeleDamortissement: null,
                prixRevient: null,
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
                typeEmpruntTravauxImmobilises: [],
            }
        },
        tableData(data) {
            if (!_.isNil(data)) {
                const travauxImmobilises = data.travauxImmobilises.items.filter(item => item.type === 1).map(item => {
                    let row = {...item};
                    row.profilEvolutionLoyerId = item.profilEvolutionLoyer ? item.profilEvolutionLoyer.id: null;
                    row.profilEvolutionLoyerNumero = item.profilEvolutionLoyer ? item.profilEvolutionLoyer.numero: null;
                    row.modeleDamortissement = item.modeleDamortissement ? item.modeleDamortissement.id : null;
                    row.modeleDamortissementNom = item.modeleDamortissement ? item.modeleDamortissement.nom : null;
                    row.typeEmpruntTravauxImmobilises = item.typeEmpruntTravauxImmobilises.items;
                    return row;
                });

                this.travauxImmobilises = travauxImmobilises;
                return travauxImmobilises;
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
            this.$confirm('Êtes-vous sûr de vouloir supprimer ces travaux immobilisés identifiées?')
                .then(_ => {
                    this.$apollo.mutate({
                        mutation: require('../../../../../../../graphql/simulations/logements-familiaux/travaux/removeTravauxImmobilise.gql'),
                        variables: {
                            travauxImmobiliseUUID: row.id,
                            simulationId: this.simulationID
                        }
                    }).then(() => {
                        updateData(this.query, this.simulationID).then(() => {
                            this.$message({
                                showClose: true,
                                message: 'Les travaux immobilisés identifés ont bien été supprimés.',
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
                if (valid) {
                    const typeEmpruntTravauxImmobilises = this.form.typeEmpruntTravauxImmobilises.map(item => {
                        return JSON.stringify({
                            id: item.typesEmprunts.id,
                            montant: item.montant,
                            datePremiere: item.datePremiere
                        })
                    });
                    this.isSubmitting = true;
                    this.$apollo.mutate({
                        mutation: require('../../../../../../../graphql/simulations/logements-familiaux/travaux/saveTravauxImmobilise.gql'),
                        variables: {
                            travauxImmobilise: {
                                simulationId: this.simulationID,
                                uuid: this.form.id,
                                nGroupe: this.form.nGroupe,
                                nSousGroupe: this.form.nSousGroupe,
                                nomGroupe: this.form.nomGroupe,
                                information: this.form.information,
                                loyerMensuelInitial: this.form.loyerMensuelInitial,
                                profilEvolutionLoyerId: this.form.profilEvolutionLoyerId,
                                numeroTranche: this.form.numeroTranche,
                                nomTranche: this.form.nomTranche,
                                conventionAnru: this.form.conventionAnru,
                                surfaceTraitee: this.form.surfaceTraitee,
                                variationSurfaceQuittance: this.form.variationSurfaceQuittance,
                                nombreLogement: this.form.nombreLogement,
                                variationNombreLogement: this.form.variationNombreLogement,
                                anneeAgreement: this.form.anneeAgreement,
                                dateOrdreService: this.form.dateOrdreService,
                                dateFinTravaux: this.form.dateFinTravaux,
                                indexationIcc: this.form.indexationIcc,
                                tauxVariationLoyer: this.form.tauxVariationLoyer,
                                dateApplication: this.form.dateApplication,
                                modeleDamortissementId: this.form.modeleDamortissement,
                                prixRevient: this.form.prixRevient,
                                foundsPropres: this.form.foundsPropres,
                                subventionsEtat: this.form.subventionsEtat,
                                subventionsAnru: this.form.subventionsAnru,
                                subventionsEpci: this.form.subventionsEpci,
                                subventionsDepartement: this.form.subventionsDepartement,
                                subventionsRegion: this.form.subventionsRegion,
                                subventionsCollecteur: this.form.subventionsCollecteur,
                                autresSubventions: this.form.autresSubventions,
                                type: 1,
                                typeEmprunts: typeEmpruntTravauxImmobilises,
                            }
                        }
                    }).then(() => {
                        this.isSubmitting = false;
                        this.dialogVisible = false;
                        updateData(this.query, this.simulationID).then(() => {
                            this.$message({
                                showClose: true,
                                message: 'Les travaux immobilisés identifiés ont bien été enregistrés.',
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
        changeNgroupe(nGroupe) {
            const patrimoine = this.patrimoines.find(item => item.nGroupe === this.form.nGroupe);
            let form = this.form;
            if (patrimoine) {
                form.nSousGroupe = patrimoine.nSousGroupe;
                form.nomGroupe = patrimoine.nomGroupe
                form.information = patrimoine.informations;
                form.profilEvolutionLoyerId = patrimoine.profilsEvolutionLoyers ? patrimoine.profilsEvolutionLoyers.id: null;
                form.profilEvolutionLoyerNumero = patrimoine.profilsEvolutionLoyers ? patrimoine.profilsEvolutionLoyers.numero: null;
                this.nombreLogementsLimit = patrimoine.nombreLogements;
            } else {
                form.nSousGroupe = null;
                form.nomGroupe = '';
                form.information = '';
                form.profilEvolutionLoyerId = null;
                form.profilEvolutionLoyerNumero = null;
                this.nombreLogementsLimit = null;
            }
            this.form = null;
            this.form = form;
        },
        addTypeEmprunt() {
            if (this.form.typeEmprunt) {
                const typeEmprunt = this.availableTypesEmprunts.find(item => item.id == this.form.typeEmprunt);
                this.form.typeEmpruntTravauxImmobilises.push({
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
            const linkedEmprunts = this.form.typeEmpruntTravauxImmobilises;
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
                        this.form.typeEmpruntTravauxImmobilises.splice(index, 1);
                        this.getTypesEmprunts();
                    } else {
                        this.$apollo.mutate({
                            mutation: require('../../../../../../../graphql/simulations/logements-familiaux/travaux/removeTypeDempruntTravauxImmobilise.gql'),
                            variables: {
                                typesEmpruntsUUID: row.typesEmprunts.id,
                                travauxImmobiliseUUID: this.travauxImmobilises[this.selectedIndex].id
                            }
                        }).then((res) => {
                            this.form.typeEmpruntTravauxImmobilises.splice(index, 1);
                            this.getTypesEmprunts();
                        });
                    }
                })
                .catch(_ => {});
        },
        back() {
            if (this.selectedIndex > 0) {
                this.selectedIndex--;
                this.form = {...this.travauxImmobilises[this.selectedIndex]};
            }
        },
        next() {
            if (this.selectedIndex < (this.travauxImmobilises.length - 1)) {
                this.selectedIndex++;
                this.form = {...this.travauxImmobilises[this.selectedIndex]};
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
        formatInput(type) {
            this.form[type] = mathInput(this.form[type]);
        },
        exportTravauxImmobilisesIdentifiees() {
           window.location.href = "/export-travaux-immobilises-identifiees/" + this.simulationID;
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
            this.$toasted.error(error, {
                theme: 'toasted-primary',
                icon: 'error',
                position: 'top-right',
                duration: 5000
            });

            this.$refs.upload.clearFiles();
        }
    },
    computed: {
        totalMontant() {
            let emprunt = 0;
            this.form.typeEmpruntTravauxImmobilises.forEach(item => {
                emprunt += getFloat(item.montant);
            });
            return emprunt;
        },
        totalSubventions() {
            return getFloat(this.form.subventionsEtat) + getFloat(this.form.subventionsAnru) + getFloat(this.form.subventionsEpci) + getFloat(this.form.subventionsDepartement) + getFloat(this.form.subventionsRegion) + getFloat(this.form.subventionsCollecteur) + getFloat(this.form.autresSubventions);
        },
        resteFinancer() {
            return getFloat(this.form.prixRevient) - (getFloat(this.form.foundsPropres) + this.totalSubventions + this.totalMontant);
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
    .travaux-identifiees .el-button--small {
        font-weight: 500;
        padding: 0 15px;
        height: 40px;
        font-size: 14px;
        border-radius: 4px;
        text-align: center;
        height: 40px;
    }
</style>
