<template>
    <div class="renouvellement-composant">
        <div v-if="error">Une erreur est survenue !</div>
        <el-row class="mb-3">
            <el-col :span="2" :offset="18">
                <el-upload
                  ref="upload"
                  :action.native="'/import-renouvellement-composant/'+ simulationID"
                  multiple
                  :limit="10"
                  :on-success="onSuccess"
                  :on-error="onError">
                  <el-button size="small" type="primary">Importer</el-button>
                </el-upload>
            </el-col>
            <el-col :span="2">
                <el-button type="success" @click.stop="exportRenouvellementComposant">
                    Exporter
                </el-button>
            </el-col>
        </el-row>
        <el-table
                class="fw"
                v-loading="isLoading"
                :data="tableData(data)"
                style="width: 100%">
            <el-table-column sortable column-key="nGroupe" prop="nGroupe" min-width="100" label="N°">
                <template slot="header">
                    <span title="N°">N°</span>
                </template>
            </el-table-column>
            <el-table-column sortable column-key="nomCategorie" prop="nomCategorie" min-width="150" label="Nom catégorie">
                <template slot="header">
                    <span title="Nom catégorie">Nom catégorie</span>
                </template>
            </el-table-column>
            <el-table-column sortable column-key="modeleDamortissement" prop="modeleDamortissement" min-width="160" label="Modèle d’amortissement technique">
                <template slot-scope="scope">
                    {{getModeleDamortissement(scope.row.modeleDamortissement)}}
                </template>
                <template slot="header">
                    <span title="Modèle d’amortissement technique">Modèle d’amortissement technique</span>
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
            <el-table-column sortable column-key="anneePremiereEcheance" prop="anneePremiereEcheance" min-width="130" label="Année de première annuité">
                <template slot="header">
                    <span title="Année de première annuité">Année de première annuité</span>
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
                             :key="column.prop"
                             :prop="column.prop"
                             :label="column.label">
                             <template slot="header">
                                 <span :title="column.label">{{ column.label }}</span>
                             </template>
            </el-table-column>
            <el-table-column fixed="right" width="120" label="Actions">
                <template slot-scope="scope">
                    <el-button type="primary" icon="el-icon-edit" circle :disabled="!isModify" @click="handleEdit(scope.$index, scope.row)"></el-button>
                    <el-button type="danger" icon="el-icon-delete" circle :disabled="!isModify" @click="handleDelete(scope.$index, scope.row)"></el-button>
                </template>
            </el-table-column>
        </el-table>
        <el-row class="d-flex justify-content-end my-3">
            <el-button type="primary" :disabled="!isModify" @click="showCreateModal()">Créer un renouvellement de composant</el-button>
        </el-row>
        <el-dialog
                title="Création/Modification de Renouvellement de composants"
                :visible.sync="dialogVisible"
                :close-on-click-modal="false"
                width="70%">
            <el-row v-if="isEdit" class="form-slider text-center mb-4">
                <i class="el-icon-back font-weight-bold" @click="back"></i>
                <i class="el-icon-right font-weight-bold" @click="next"></i>
            </el-row>
            <el-form label-position="left" :model="form" :rules="formRules" label-width="180px" ref="travauxImmobiliseForm">
                <el-tabs v-model="activeTab" type="card">
                    <el-tab-pane label="Caractéristiques générales" name="1">
                        <el-collapse v-model="collapseList">
                            <el-collapse-item title="Données Générales" name="1">
                                <el-row :gutter="20">
                                    <el-col :span="12">
                                        <el-form-item label="Numéro" prop="nGroupe">
                                            <el-input type="text" v-model="form.nGroupe" placeholder="N°" class="fixed-input"></el-input>
                                        </el-form-item>
                                        <el-checkbox v-model="form.conventionAnru">Convention ANRU</el-checkbox>
                                    </el-col>
                                    <el-col :span="12">
                                        <el-form-item label="Nom catégorie" prop="nomCategorie">
                                            <el-input type="text" v-model="form.nomCategorie" placeholder="Nom catégorie"></el-input>
                                        </el-form-item>
                                    </el-col>
                                </el-row>
                            </el-collapse-item>
                            <el-collapse-item title="Investissement en K€" name="2">
                                <el-row :gutter="20">
                                    <el-col :span="12">
                                        <el-checkbox v-model="form.indexationIcc" class="mt-3">Indexation à I'ICC</el-checkbox>
                                    </el-col>
                                    <el-col :span="12">
                                        <el-form-item label="Modèle d’amortissement technique" prop="modeleDamortissement">
                                            <el-select v-model="form.modeleDamortissement">
                                                <el-option v-for="item in modeleDamortissements"
                                                           :key="item.id"
                                                           :label="item.nom"
                                                           :value="item.id"></el-option>
                                            </el-select>
                                        </el-form-item>
                                    </el-col>
                                </el-row>
                                <el-row class="mt-4">
                                    <el-col :span="3" class="text-center" style="padding-top: 55px;">
                                        <p class="carousel-head">Montant des travaux en k€</p>
                                    </el-col>
                                    <el-col :span="21">
                                        <periodique :anneeDeReference="anneeDeReference"
                                                    v-model="form.periodiques"
                                                    @onChange="periodicOnChange"></periodique>
                                    </el-col>
                                </el-row>
                            </el-collapse-item>
                            <el-collapse-item title="Financements en %" name="3">
                                <el-row :gutter="20">
                                    <el-col :span="14">
                                        <el-row :gutter="20">
                                            <el-col :span="12">
                                                <el-form-item label="Quotités des fonds Propres" label-width="240px" prop="foundsPropres">
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
                                            </el-col>
                                            <el-col :span="12">
                                                <p><strong>Quotités Subventions en %</strong></p>
                                                <el-form-item label="Subventions d'Etat" label-width="240px" prop="subventionsEtat">
                                                    <el-input type="text" placeholder="0" class="fixed-input"
                                                              v-model="form.subventionsEtat"
                                                              @change="formatInput('subventionsEtat')"></el-input>
                                                </el-form-item>
                                                <el-form-item label="Subventions ANRU" label-width="240px" prop="subventionsAnru">
                                                    <el-input type="text" placeholder="0" class="fixed-input"
                                                              v-model="form.subventionsAnru"
                                                              @change="formatInput('subventionsAnru')"></el-input>
                                                </el-form-item>
												<el-form-item label="Subventions Régions" label-width="240px" prop="subventionsRegion">
													<el-input type="text" placeholder="0" class="fixed-input"
															  v-model="form.subventionsRegion"
															  @change="formatInput('subventionsRegion')"></el-input>
												</el-form-item>
												<el-form-item label="Subventions départements" label-width="240px" prop="subventionsDepartement">
													<el-input type="text" placeholder="0" class="fixed-input"
															  v-model="form.subventionsDepartement"
															  @change="formatInput('subventionsDepartementt')"></el-input>
												</el-form-item>
                                                <el-form-item label="Subventions EPCI/Communes" label-width="240px" prop="subventionsEpci">
                                                    <el-input type="text" placeholder="0" class="fixed-input"
                                                              v-model="form.subventionsEpci"
                                                              @change="formatInput('subventionsEpci')"></el-input>
                                                </el-form-item>
                                                <el-form-item label="Subventions collecteurs" label-width="240px" prop="subventionsCollecteur">
                                                    <el-input type="text"  placeholder="0" class="fixed-input"
                                                              v-model="form.subventionsCollecteur"
                                                              @change="formatInput('subventionsCollecteur')"></el-input>
                                                </el-form-item>
                                                <el-form-item label="Autres subventions" label-width="240px" prop="autresSubventions">
                                                    <el-input type="text" placeholder="0" class="fixed-input"
                                                              v-model="form.autresSubventions"
                                                              @change="formatInput('autresSubventions')"></el-input>
                                                </el-form-item>
                                            </el-col>
                                        </el-row>
                                    </el-col>
                                    <el-col :span="10">
                                        <p><strong>Emprunts en %</strong></p>
                                        <el-form-item label="Année de première annuité" label-width="240px" prop="anneePremiereEcheance">
                                            <el-input type="text" v-model="form.anneePremiereEcheance" placeholder="0" class="fixed-input"></el-input>
                                        </el-form-item>
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
                                            <el-input type="text" v-model="form.quotiteEmprunt" placeholder="0" class="fixed-input"></el-input>
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
                                            <p>Total quotité emprunts : {{totalQuotiteEmprunt}}</p>
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
import { initPeriodic, periodicFormatter, mathInput, checkPeriodic } from '../../../../../../../utils/inputs';
import { updateData, getFloat } from '../../../../../../../utils/helpers'
import customValidator from '../../../../../../../utils/validation-rules';
import Periodique from '../../../../../../../components/partials/Periodique';

export default {
    name: "RenouvellementComposant",
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
        var validateNomCategorie = (rule, value, callback) => {
            if (!value) {
                callback(new Error("Veuillez saisir un nom catégorie"));
            } else if(this.checkNomCategorie(value)) {
                callback(new Error("Nom de catégorie déjà existant"));
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
            travauxImmobilises: [],
            dialogVisible: false,
            collapseList: ['1'],
            form: null,
            isEdit: false,
            selectedIndex: null,
            activeTab: '1',
            isSubmitting: false,
            availableTypesEmprunts: [],
            columns: [],
            formRules: {
                nGroupe: [
                    { required: true, validator: validateNgroupe, trigger: 'change' },
                ],
                nomCategorie: [
                    { required: true, validator: validateNomCategorie, trigger: 'change' }
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
                nomCategorie: null,
                modeleDamortissement: null,
                conventionAnru: false,
                indexationIcc: true,
                anneePremiereEcheance: null,
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
                    montant: initPeriodic()
                }
            }
        },
        setTableColumns() {
            this.columns = [];
            for (var i = 0; i < 50; i++) {
                this.columns.push({
                    label: (parseInt(this.anneeDeReference) + i).toString(),
                    prop: `periodiques.montant[${i}]`
                });
            }
        },
        tableData(data) {
            if (!_.isNil(data)) {
                const travauxImmobilises = data.travauxImmobilises.items.filter(item => item.type === 0).map(item => {
                    let periodiques = [];
                    item.travauxImmobilisesPeriodique.items.forEach(periodique => {
                        periodiques[periodique.iteration - 1] = periodique.montant;
                    });
                    return {
                        id: item.id,
                        nGroupe: item.nGroupe,
                        nomCategorie: item.nomCategorie,
                        modeleDamortissement: item.modeleDamortissement ? item.modeleDamortissement.id : null,
                        conventionAnru: item.conventionAnru,
                        indexationIcc: item.indexationIcc,
                        anneePremiereEcheance: item.anneePremiereEcheance,
                        foundsPropres: item.foundsPropres,
                        subventionsEtat: item.subventionsEtat,
                        subventionsAnru: item.subventionsAnru,
                        subventionsEpci: item.subventionsEpci,
                        subventionsDepartement: item.subventionsDepartement,
                        subventionsRegion: item.subventionsRegion,
                        subventionsCollecteur: item.subventionsCollecteur,
                        autresSubventions: item.autresSubventions,
                        typeEmpruntTravauxImmobilises: item.typeEmpruntTravauxImmobilises.items,
                        periodiques: {
                            montant: periodiques
                        }
                    }
                });

                this.travauxImmobilises = travauxImmobilises;
                return travauxImmobilises;
            } else {
                return [];
            }
        },
        save(formName) {
            this.$refs[formName].validate((valid) => {
                if (valid && checkPeriodic(this.form.periodiques.montant)) {
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
                                modeleDamortissementId: this.form.modeleDamortissement,
                                conventionAnru: this.form.conventionAnru,
                                indexationIcc: this.form.indexationIcc,
                                anneePremiereEcheance: this.form.anneePremiereEcheance,
                                foundsPropres: this.form.foundsPropres,
                                subventionsEtat: this.form.subventionsEtat,
                                subventionsAnru: this.form.subventionsAnru,
                                subventionsEpci: this.form.subventionsEpci,
                                subventionsDepartement: this.form.subventionsDepartement,
                                subventionsRegion: this.form.subventionsRegion,
                                subventionsCollecteur: this.form.subventionsCollecteur,
                                autresSubventions: this.form.autresSubventions,
                                type: 0,
                                typeEmprunts: typeEmpruntTravauxImmobilises,
                                periodique: JSON.stringify({montant: this.form.periodiques.montant})
                            }
                        }
                    }).then(() => {
                        this.isSubmitting = false;
                        this.dialogVisible = false;
                        updateData(this.query, this.simulationID).then(() => {
                            this.$message({
                                showClose: true,
                                message: 'Le renouvellement de composant a bien été enregistré.',
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
            this.$confirm('Êtes-vous sûr de vouloir supprimer ce renouvellement de composant?')
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
                                message: 'Le renouvellement de composant a bien été supprimé.',
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
        checkNgroupe(value) {
            let travauxImmobilises = [...this.travauxImmobilises];
            if (this.isEdit) {
                travauxImmobilises = travauxImmobilises.filter(item => item.nGroupe !== this.travauxImmobilises[this.selectedIndex].nGroupe);
            }
            return travauxImmobilises.some(item => item.nGroupe === value);
        },
        checkNomCategorie(value) {
            let travauxImmobilises = [...this.travauxImmobilises];
            if (this.isEdit) {
                travauxImmobilises = travauxImmobilises.filter(item => item.nomCategorie !== this.travauxImmobilises[this.selectedIndex].nomCategorie);
            }
            return travauxImmobilises.some(item => item.nomCategorie === value);
        },
        getModeleDamortissement(id) {
            const modeleDamortissement = this.modeleDamortissements.find(item => item.id === id);
            return modeleDamortissement ? modeleDamortissement.nom : '';
        },
        addTypeEmprunt() {
            if (this.form.typeEmprunt) {
                const typeEmprunt = this.availableTypesEmprunts.find(item => item.id = this.form.typeEmprunt);
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
                    } else {
                        this.$apollo.mutate({
                            mutation: require('../../../../../../../graphql/simulations/logements-familiaux/travaux/removeTypeDempruntTravauxImmobilise.gql'),
                            variables: {
                                typesEmpruntsUUID: row.typesEmprunts.id,
                                travauxImmobiliseUUID: this.travauxImmobilises[this.selectedIndex].id
                            }
                        }).then((res) => {
                            this.form.typeEmpruntTravauxImmobilises.splice(index, 1);
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
        exportRenouvellementComposant() {
           window.location.href = "/export-renouvellement-composant/" + this.simulationID;
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
        isModify() {
            return this.$store.getters['choixEntite/isModify'];
        },
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
            return 100 - (getFloat(this.form.foundsPropres) + this.totalSubventions);
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
    .renouvellement-composant .el-button--small {
        font-weight: 500;
        padding: 0 15px;
        height: 40px;
        font-size: 14px;
        border-radius: 4px;
        text-align: center;
        height: 40px;
    }
</style>
