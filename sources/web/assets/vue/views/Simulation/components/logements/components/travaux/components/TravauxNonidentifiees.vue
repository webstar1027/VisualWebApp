<template>
    <div class="travaux-nonidentifiees">
        <div v-if="error">Une erreur est survenue !</div>
        <el-row class="mb-3">
            <el-col :span="2" :offset="18">
                <el-upload
                  ref="upload"
                  :action.native="'/import-travaux-immobilises-non-identifiees/'+ simulationID"
                  multiple
                  :limit="10"
                  :on-success="onSuccess"
                  :on-error="onError">
                  <el-button size="small" type="primary">Importer</el-button>
                </el-upload>
            </el-col>
            <el-col :span="2">
                <el-button type="success" @click.stop="exportTravauxImmobilisesNonIdentifiees">
                    Exporter
                </el-button>
            </el-col>
        </el-row>
        <el-table
                class="fw"
                v-loading="isLoading"
                :data="tableData(data)"
                style="width: 100%">
            <el-table-column sortable column-key="nGroupe" prop="nGroupe" min-width="80" label="N°">
                <template slot="header">
                    <span title="N°">N°</span>
                </template>
            </el-table-column>
            <el-table-column sortable column-key="nomCategorie" prop="nomCategorie" min-width="140" label="Nom catégorie">
                <template slot="header">
                    <span title="Nom catégorie">Nom catégorie</span>
                </template>
            </el-table-column>
            <el-table-column sortable column-key="surfaceMoyenne" prop="surfaceMoyenne" min-width="150" label="Surface moyenne par logement en m2">
                <template slot="header">
                    <span title="Surface moyenne par logement en m2">Surface moyenne par logement en m2</span>
                </template>
            </el-table-column>
            <el-table-column sortable column-key="loyerMensuelMoyen" prop="loyerMensuelMoyen" min-width="150" label="Loyer mensuel €/m²">
                <template slot="header">
                    <span title="Loyer mensuel €/m²">Loyer mensuel €/m²</span>
                </template>
            </el-table-column>
            <el-table-column sortable column-key="variationLoyer" prop="variationLoyer" min-width="150" label="Variation du loyer après travaux">
                <template slot="header">
                    <span title="Variation du loyer après travaux">Variation du loyer après travaux</span>
                </template>
            </el-table-column>
            <el-table-column sortable column-key="anneeApplication" prop="anneeApplication" min-width="150" label="Année d'application de la variation">
                <template slot-scope="scope">
                    <span v-if="scope.row.anneeApplication===1">Livraison</span>
                    <span v-if="scope.row.anneeApplication===2">Livraison +1</span>
                </template>
                <template slot="header">
                    <span title="Année d'application de la variation">Année d'application de la variation</span>
                </template>
            </el-table-column>
            <el-table-column sortable column-key="dureeChantier" prop="dureeChantier" min-width="150" label="Durée de chantier en année">
                <template slot="header">
                    <span title="Durée de chantier en année"">Durée de chantier en année"</span>
                </template>
            </el-table-column>
            <el-table-column sortable column-key="modeleDamortissementNom" prop="modeleDamortissementNom" min-width="150" label="Modèle d’amortissement">
                <template slot="header">
                    <span title="Modèle d’amortissement">Modèle d’amortissement</span>
                </template>
            </el-table-column>
            <el-table-column sortable column-key="montantTravaux" prop="montantTravaux" min-width="150" label="Montant des travaux en K€/logt">
                <template slot="header">
                    <span title="Montant des travaux en K€/logt">Montant des travaux en K€/logt</span>
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
            <el-button type="primary" :disabled="!isModify" @click="showCreateModal">Création de travaux non identifiés</el-button>
        </el-row>

        <el-dialog
            title="Création/Modification des fiches de travaux immobilisés non identifiés"
            :visible.sync="dialogVisible"
            :close-on-click-modal="false"
            width="75%">
            <el-row v-if="isEdit" class="form-slider text-center mb-4">
                <i class="el-icon-back font-weight-bold" @click="back"></i>
                <i class="el-icon-right font-weight-bold" @click="next"></i>
            </el-row>
            <el-form label-position="left" :model="form" :rules="formRules" label-width="150px" ref="travauxImmobiliseForm">
                <el-tabs v-model="activeTab" type="card">
                    <el-tab-pane label="Caractéristiques générales" name="1">
                        <el-collapse v-model="collapseList">
                            <el-collapse-item title="Données Générales" name="1">
                                <el-row :gutter="24">
                                    <el-col :span="6">
                                        <el-form-item label="N° de l'opération" prop="nGroupe">
                                            <el-input type="text" v-model="form.nGroupe" placeholder="N°" class="fixed-input"></el-input>
                                        </el-form-item>
                                        <el-checkbox v-model="form.conventionAnru" class="mt-4">Convention ANRU</el-checkbox>
                                    </el-col>
                                    <el-col :span="6">
                                        <el-form-item label="Nom catégorie" prop="nomCategorie">
                                            <el-input type="text" v-model="form.nomCategorie" placeholder="Nom catégorie"></el-input>
                                        </el-form-item>
                                        <el-checkbox v-model="form.logementConventionnes" class="mt-4">Logements conventionnés</el-checkbox>
                                    </el-col>
                                    <el-col :span="6">
                                        <el-form-item label="Surface moyenne /logt en m²" prop="surfaceMoyenne">
                                            <el-input type="text" v-model="form.surfaceMoyenne" placeholder="0" class="fixed-input"
                                                      @change="formatInput('surfaceMoyenne')"></el-input>
                                        </el-form-item>
                                    </el-col>
                                    <el-col :span="6">
                                    <el-form-item label="Durée de chantier (année)" prop="dureeChantier">
                                        <el-select v-model="form.dureeChantier">
                                            <el-option v-for="item in dureeChantiers"
                                                       :key="item"
                                                       :label="item"
                                                       :value="item"></el-option>
                                        </el-select>
                                    </el-form-item>
                                    </el-col>
                                </el-row>
                            </el-collapse-item>
                            <el-collapse-item title="Loyers" name="2">
                                <el-row :gutter="20">
                                    <el-col :span="8">
                                        <el-form-item label="Loyer mensuel €/m²" prop="loyerMensuelMoyen">
                                            <el-input type="text" v-model="form.loyerMensuelMoyen" placeholder="0" class="fixed-input"
                                                      @change="formatInput('loyerMensuelMoyen')"></el-input>
                                        </el-form-item>
                                    </el-col>
                                    <el-col :span="8">
                                        <el-form-item label="Evolution du loyer après travaux (en %)" prop="variationLoyer">
                                            <el-input type="text" v-model="form.variationLoyer" placeholder="0" class="fixed-input"
                                                      @change="formatInput('variationLoyer')"></el-input>
                                        </el-form-item>
                                    </el-col>
                                    <el-col :span="8">
                                        <el-form-item label="Année d'application de la variation" prop="anneeApplication">
                                            <el-select v-model="form.anneeApplication">
                                                <el-option v-for="item in anneeApplications"
                                                    :key="item.value"
                                                    :label="item.label"
                                                    :value="item.value"></el-option>
                                            </el-select>
                                        </el-form-item>
                                    </el-col>
                                </el-row>
                            </el-collapse-item>
                            <el-collapse-item title="Compléments" name="3">
                                <el-row class="mt-4">
                                    <el-col :span="4" style="padding-top: 55px;">
                                        <div class="carousel-head"><p>Nombre d'agréments</p></div>
                                        <div class="carousel-head"><p>Nombre de logements MEC</p></div>
                                    </el-col>
                                    <el-col :span="20">
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
                                        <el-form-item label="Montant des travaux en K€/logt" prop="montantTravaux">
                                            <el-input type="text" placeholder="0" class="fixed-input" :disabled="form.dureeChantier===0"
                                                      v-model="form.montantTravaux"
                                                      @change="formatInput('montantTravaux')"></el-input>
                                        </el-form-item>
                                        <el-checkbox v-model="form.indexationIcc">Indexation à l’ICC</el-checkbox>
                                    </el-col>
                                    <el-col :span="8">
                                        <el-form-item label="Choisir un modèle d’amortissement technique" prop="modeleDamortissement">
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
                                                <el-form-item label="Fonds Propres" label-width="240px" prop="foundsPropres">
                                                    <el-input type="text" placeholder="0" class="fixed-input"
                                                              v-model="form.foundsPropres"
                                                              @change="formatInput('foundsPropres')"></el-input>
                                                </el-form-item>
                                                <el-form-item label="Total quotité subventions" prop="totalSubventions">
                                                    {{totalSubventions}}
                                                </el-form-item>
                                                <el-form-item label="Total quotité emprunts" prop="totalQuotiteEmprunt">
                                                    {{totalQuotiteEmprunt}}
                                                </el-form-item>
                                                <el-form-item label="Reste à financer" prop="resteFinancer">
                                                    {{resteFinancer}}K€
                                                </el-form-item>
                                            </div>
                                            <div class="col-sm-6">
                                                <p><strong>Quotités subventions en K€</strong></p>
                                                <el-form-item label="Subventions d'Etat" label-width="240px" prop="subventionsEtat">
                                                    <el-input type="text" placeholder="0" class="fixed-input"
                                                              v-model="form.subventionsEtat"
                                                              @change="formatInput('subventionsEtat')"></el-input>%
                                                </el-form-item>
                                                <el-form-item label="Subventions ANRU" label-width="240px" prop="subventionsAnru">
                                                    <el-input type="text" placeholder="0" class="fixed-input"
                                                              v-model="form.subventionsAnru"
                                                              @change="formatInput('subventionsAnru')"></el-input>%
                                                </el-form-item>
												<el-form-item label="Subventions Régions" label-width="240px" prop="subventionsRegion">
													<el-input type="text" placeholder="0" class="fixed-input"
															  v-model="form.subventionsRegion"
															  @change="formatInput('subventionsRegion')"></el-input>%
												</el-form-item>
												<el-form-item label="Subventions départements" label-width="240px" prop="subventionsDepartement">
													<el-input type="text" placeholder="0" class="fixed-input"
															  v-model="form.subventionsDepartement"
															  @change="formatInput('subventionsDepartement')"></el-input>%
												</el-form-item>
                                                <el-form-item label="Subventions EPCI/Communes" label-width="240px" prop="subventionsEpci">
                                                    <el-input type="text" placeholder="0" class="fixed-input"
                                                              v-model="form.subventionsEpci"
                                                              @change="formatInput('subventionsEpci')"></el-input>%
                                                </el-form-item>

                                                <el-form-item label="Subventions collecteurs" label-width="240px" prop="subventionsCollecteur">
                                                    <el-input type="text" placeholder="0" class="fixed-input"
                                                              v-model="form.subventionsCollecteur"
                                                              @change="formatInput('subventionsCollecteur')"></el-input>%
                                                </el-form-item>
                                                <el-form-item label="Autres subventions" label-width="240px" prop="autresSubventions">
                                                    <el-input type="text" v-model="form.autresSubventions" placeholder="0" class="fixed-input"
                                                              @change="formatInput('autresSubventions')"></el-input>%
                                                </el-form-item>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-5">
                                        <p><strong>Emprunts</strong></p>
                                        <el-form-item label="Ajouter un emprunt" prop="typeEmprunt">
                                            <el-select v-model="form.typeEmprunt">
                                                <el-option v-for="item in availableTypesEmprunts"
                                                    :key="item.id"
                                                    :label="item.nom"
                                                    :value="item.id"></el-option>
                                            </el-select>
                                        </el-form-item>
                                        <el-form-item v-if="form.typeEmprunt" label="Quotité emprunt" prop="quotiteEmprunt">
                                            <el-input type="text" placeholder="0" class="fixed-input"
                                                      v-model="form.quotiteEmprunt"
                                                      @change="formatInput('quotiteEmprunt')"></el-input>
                                            <el-button type="primary" @click="addTypeEmprunt">Ajouter</el-button>
                                        </el-form-item>
                                        <p class="mt-5"><strong>Liste des emprunts</strong></p>
                                        <el-table
                                            :data="form.typeEmpruntTravauxImmobilises"
                                            style="width: 100%">
                                            <el-table-column sortable column-key="typesEmprunts" prop="typesEmprunts" min-width="60" label="Numéro">
                                                <template slot-scope="scope">
                                                    {{scope.row.typesEmprunts.nom}}
                                                </template>
                                            </el-table-column>
                                            <el-table-column sortable column-key="quotiteEmprunt" prop="quotiteEmprunt" min-width="60" label="Quotité emprunt"></el-table-column>
                                            <el-table-column fixed="right" width="90" label="supprimer">
                                                <template slot-scope="scope">
                                                    <el-button type="danger" icon="el-icon-delete" circle @click="handleDeleteEmprunt(scope.$index, scope.row)"></el-button>
                                                </template>
                                            </el-table-column>
                                        </el-table>
                                        <div class="text-center">
                                            <p>Total : {{totalQuotiteEmprunt}}</p>
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
                <el-button type="success" @click="save('travauxImmobiliseForm')" :disabled="isSubmitting">Valider</el-button>
            </div>
        </el-dialog>
    </div>
</template>

<script>
import { initPeriodic, periodicFormatter, checkAllPeriodics, mathInput } from '../../../../../../../utils/inputs';
import { updateData, getFloat } from '../../../../../../../utils/helpers'
import customValidator from '../../../../../../../utils/validation-rules';
import Periodique from '../../../../../../../components/partials/Periodique';

export default {
    name: "TravauxNonidentifiees",
    components: { Periodique },
    props: ['simulationID', 'anneeDeReference', 'modeleDamortissements', 'typesEmprunts', 'data', 'isLoading', 'query', 'error'],
    data() {
        var validateNgroupe = (rule, value, callback) => {
            if (!value) {
                callback(new Error("Veuillez saisir un N°"));
            } else if(this.checkNgroupe(value)) {
                callback(new Error("N°Déjà existant"));
            } else {
                callback();
            }
        };
        var resteFinancerValidate = (rule, value, callback) => {
            if (this.resteFinancer != 0) {
                callback(new Error("le plan de financement n’est pas équilibré"));
            } else {
                callback();
            }
        };
        return {
            activeTab: '1',
            collapseList: ['1'],
            travauxImmobilises: [],
            dialogVisible: false,
            form: null,
            isEdit: false,
            isSubmitting: false,
            selectedIndex: null,
            availableTypesEmprunts: [],
            columns: [],
            anneeApplications: [
                { label: 'Livraison', value: 1 },
                { label: 'Livraison +1', value: 2 }
            ],
            dureeChantiers: [0, 1, 2, 3],
            formRules: {
                nGroupe: [
                    { required: true, validator: validateNgroupe, trigger: 'change' }
                ],
                nomCategorie: [
                    { required: true, message: 'Veuillez saisir un nom catégorie', trigger: 'change' }
                ],
                resteFinancer: [
                    { validator: resteFinancerValidate, trigger: 'change' }
                ],
                foundsPropres: customValidator.getRule('positiveDouble'),
                subventionsEtat: customValidator.getRule('positiveDouble'),
                subventionsAnru: customValidator.getRule('positiveDouble'),
                subventionsEpci: customValidator.getRule('positiveDouble'),
                subventionsDepartement: customValidator.getRule('positiveDouble'),
                subventionsRegion: customValidator.getRule('positiveDouble'),
                subventionsCollecteur: customValidator.getRule('positiveDouble'),
                autresSubventions: customValidator.getRule('positiveDouble'),
                surfaceMoyenne: customValidator.getRule('positiveDouble'),
                loyerMensuelMoyen: customValidator.getRule('positiveDouble'),
                variationLoyer: customValidator.getRule('positiveDouble'),
                montantTravaux: customValidator.getRule('positiveDouble'),
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
                nomCategorie: '',
                conventionAnru: false,
                logementConventionnes: false,
                surfaceMoyenne: null,
                loyerMensuelMoyen: null,
                variationLoyer: null,
                anneeApplication: null,
                dureeChantier: null,
                modeleDamortissement: null,
                montantTravaux: null,
                indexationIcc: false,
                foundsPropres: null,
                subventionsEtat: null,
                subventionsAnru: null,
                subventionsEpci: null,
                subventionsDepartement: null,
                subventionsRegion: null,
                subventionsCollecteur: null,
                autresSubventions: null,
                typeEmpruntTravauxImmobilises: [],
                periodiques: {
                    nombreAgrements: initPeriodic(),
                    logements: initPeriodic(),
                }
            }
        },
        tableData(data) {
            if (!_.isNil(data)) {
                const travauxImmobilises = data.travauxImmobilises.items.filter(item => {
                    if (item.type === 2) {
                        let nombreAgrements = [];
                        let logements = [];
                        item.travauxImmobilisesPeriodique.items.forEach(periodique => {
                            nombreAgrements[periodique.iteration - 1] = periodique.nombreAgrement;
                            logements[periodique.iteration - 1] = periodique.logement;
                        });

                        let row = {...item};
                        row.modeleDamortissement = item.modeleDamortissement ? item.modeleDamortissement.id : null;
                        row.modeleDamortissementNom = item.modeleDamortissement ? item.modeleDamortissement.nom : null;
                        row.typeEmpruntTravauxImmobilises = item.typeEmpruntTravauxImmobilises.items;
                        row.periodiques = {
                            nombreAgrements,
                            logements
                        };

                        return row;
                    }
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
            this.$confirm('Êtes-vous sûr de vouloir supprimer ces travaux immobilisés non identifiées?')
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
                                message: 'Les travaux immobilisés non identifés ont bien été supprimés.',
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
                    const typeEmpruntTravauxImmobilises = this.form.typeEmpruntTravauxImmobilises.map(item => {
                        return JSON.stringify({
                            id: item.typesEmprunts.id,
                            quotiteEmprunt: item.quotiteEmprunt
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
                                nomCategorie: this.form.nomCategorie,
                                conventionAnru: this.form.conventionAnru,
                                logementConventionnes: this.form.logementConventionnes,
                                surfaceMoyenne: this.form.surfaceMoyenne,
                                loyerMensuelMoyen: this.form.loyerMensuelMoyen,
                                variationLoyer: this.form.variationLoyer,
                                anneeApplication: this.form.anneeApplication,
                                dureeChantier: this.form.dureeChantier,
                                modeleDamortissementId: this.form.modeleDamortissement,
                                montantTravaux: this.form.montantTravaux,
                                indexationIcc: this.form.indexationIcc,
                                foundsPropres: this.form.foundsPropres,
                                subventionsEtat: this.form.subventionsEtat,
                                subventionsAnru: this.form.subventionsAnru,
                                subventionsEpci: this.form.subventionsEpci,
                                subventionsDepartement: this.form.subventionsDepartement,
                                subventionsRegion: this.form.subventionsRegion,
                                subventionsCollecteur: this.form.subventionsCollecteur,
                                autresSubventions: this.form.autresSubventions,
                                type: 2,
                                typeEmprunts: typeEmpruntTravauxImmobilises,
                                periodique: JSON.stringify({
                                    nombre_agrement: this.form.periodiques.nombreAgrements,
                                    logement: this.form.periodiques.logements
                                })
                            }
                        }
                    }).then(() => {
                        this.isSubmitting = false;
                        this.dialogVisible = false;
                        updateData(this.query, this.simulationID).then(() => {
                            this.$message({
                                showClose: true,
                                message: 'Les travaux immobilisés non identifiés ont bien été enregistrés.',
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
        checkNgroupe(value) {
            let travauxImmobilises = [...this.travauxImmobilises];
            if (this.isEdit) {
                travauxImmobilises = travauxImmobilises.filter(item => item.nGroupe !== this.travauxImmobilises[this.selectedIndex].nGroupe);
            }
            return travauxImmobilises.some(item => item.nGroupe === value);
        },
        addTypeEmprunt() {
            if (this.form.typeEmprunt) {
                const typeEmprunt = this.availableTypesEmprunts.find(item => item.id == this.form.typeEmprunt);
                this.form.typeEmpruntTravauxImmobilises.push({
                    quotiteEmprunt: this.form.quotiteEmprunt | 0,
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
        setTableColumns() {
            this.columns = [];
            for (var i = 0; i < 50; i++) {
                this.columns.push({
                    label: (parseInt(this.anneeDeReference) + i).toString() + ' Nombre d\'agréments',
                    prop: `periodiques.nombreAgrements[${i}]`
                });
                this.columns.push({
                    label: (parseInt(this.anneeDeReference) + i).toString() + ' Logements mis en chantier',
                    prop: `periodiques.logements[${i}]`
                })
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
        exportTravauxImmobilisesNonIdentifiees() {
           window.location.href = "/export-travaux-immobilises-non-identifiees/" + this.simulationID;
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
        totalQuotiteEmprunt() {
            let emprunt = 0;
            this.form.typeEmpruntTravauxImmobilises.forEach(item => {
                emprunt += getFloat(item.quotiteEmprunt);
            });
            return emprunt;
        },
        totalSubventions() {
            return getFloat(this.form.subventionsEtat) + getFloat(this.form.subventionsAnru) + getFloat(this.form.subventionsEpci) + getFloat(this.form.subventionsDepartement) + getFloat(this.form.subventionsRegion) + getFloat(this.form.subventionsCollecteur) + getFloat(this.form.autresSubventions);
        },
        resteFinancer() {
            return 100 - (getFloat(this.form.foundsPropres) + this.totalSubventions + this.totalQuotiteEmprunt);
        },
        isModify() {
            return this.$store.getters['choixEntite/isModify'];
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
    .travaux-nonidentifiees .el-button--small {
        font-weight: 500;
        padding: 0 15px;
        height: 40px;
        font-size: 14px;
        border-radius: 4px;
        text-align: center;
        height: 40px;
    }
</style>
