<template>
    <div class="cession-identifiees">
        <div v-if="error">Une erreur est survenue !</div>
        <el-row class="mb-3">
            <el-col :span="2" :offset="19">
                <el-upload
                  ref="upload"
                  :action.native="'/import-cession-identifiees/'+ simulationID"
                  multiple
                  :limit="10"
                  :on-success="onSuccess"
                  :on-error="onError">
                  <el-button size="small" type="primary">Importer</el-button>
                </el-upload>
            </el-col>
            <el-col :span="2">
                <el-button type="success" @click.stop="exportCessionIdentifiees">
                    Exporter
                </el-button>
            </el-col>
        </el-row>
        <el-table
            v-loading="isLoading"
            :data="tableData(data)"
            style="width: 100%">
            <el-table-column sortable column-key="nGroupe" prop="nGroupe" min-width="110" label="N° groupe">
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
            <el-table-column sortable column-key="informations" prop="informations" min-width="150" label="Informations">
                <template slot="header">
                    <span title="Informations">Informations</span>
                </template>
            </el-table-column>
            <el-table-column sortable column-key="nature" prop="nature" min-width="100" label="Nature">
                <template slot="header">
                    <span title="Nature">Nature</span>
                </template>
            </el-table-column>
            <el-table-column sortable column-key="indexerInflation" prop="indexerInflation" min-width="150" label="À indexer à l’inflation">
                <template slot-scope="scope">
                    <span>{{scope.row.indexerInflation ? 'Oui' : 'Non'}}</span>
                </template>
                <template slot="header">
                    <span title="À indexer à l’inflation">À indexer à l’inflation</span>
                </template>
            </el-table-column>
            <el-table-column sortable column-key="reductionTfpb" prop="reductionTfpb" min-width="150" label="Réduction TFPB €/lgt cédé">
                <template slot="header">
                    <span title="Réduction TFPB €/lgt cédé">Réduction TFPB €/lgt cédé</span>
                </template>
            </el-table-column>
            <el-table-column sortable column-key="reductionGe" prop="reductionGe" min-width="150" label="Réduction de GE €/lgt cédé">
                <template slot="header">
                    <span title="Réduction de GE €/lgt cédé">Réduction de GE €/lgt cédé</span>
                </template>
            </el-table-column>
            <el-table-column sortable column-key="reductionMaintenance" prop="reductionMaintenance" min-width="200" label="Réduction de maintenance courante €/lgt cédé">
                <template slot="header">
                    <span title="Réduction de maintenance courante €/lgt cédé">Réduction de maintenance courante €/lgt cédé</span>
                </template>
            </el-table-column>
            <el-table-column sortable column-key="cessionFinMois" prop="cessionFinMois" min-width="150" label="Cession de fin de mois">
                <template slot="header">
                    <span title="Cession de fin de mois">Cession de fin de mois</span>
                </template>
            </el-table-column>
            <el-table-column sortable column-key="reductionAmortissementAnnuelle" prop="reductionAmortissementAnnuelle" min-width="210" label="Réduction d'amortissement technique annuelle (k€)">
                <template slot="header">
                    <span title="Réduction d'amortissement technique annuelle (k€)">Réduction d'amortissement technique annuelle (k€)</span>
                </template>
            </el-table-column>
            <el-table-column sortable column-key="reductionRepriseAnnuelle" prop="reductionRepriseAnnuelle" min-width="210" label="Réduction de reprise de subvention annuelle (k€)">
                <template slot="header">
                    <span title="Réduction de reprise de subvention annuelle (k€)">Réduction de reprise de subvention annuelle (k€)</span>
                </template>
            </el-table-column>
            <el-table-column sortable column-key="dureeResiduelle" prop="dureeResiduelle" min-width="210" label="Durée d'amortissement technique résiduelle (année)">
                <template slot="header">
                    <span title="Durée d'amortissement technique résiduelle (année)">Durée d'amortissement technique résiduelle (année)</span>
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
                    <el-button type="primary" icon="el-icon-edit" circle @click="handleEdit(scope.$index, scope.row)"></el-button>
                    <el-button type="danger" icon="el-icon-delete" circle @click="handleDelete(scope.$index, scope.row)"></el-button>
                </template>
            </el-table-column>
        </el-table>

        <el-row class="d-flex justify-content-end my-3">
          <el-button type="primary" :disabled="!isModify" @click="showCreateModal">Créer une cession identifiée</el-button>
        </el-row>

        <el-dialog
            title="Création/Modification de cession identifiée"
            :visible.sync="dialogVisible"
            :close-on-click-modal="false"
            width="75%">
            <el-row v-if="isEdit" class="form-slider text-center mb-4">
                <i class="el-icon-back font-weight-bold" @click="back"></i>
                <i class="el-icon-right font-weight-bold" @click="next"></i>
            </el-row>
            <el-form label-position="left" :model="form" :rules="formRules" label-width="180px" ref="cessionForm">
                <el-collapse v-model="collapseList">
                    <el-collapse-item title="Données Générales" name="1">
                        <el-row :gutter="24">
                            <el-col :span="6">
                                <el-form-item label="N° de groupe" prop="nGroupe">
                                    <el-select v-model="form.nGroupe" @change="changeNgroupe">
                                        <el-option v-for="item in nGroupes"
                                            :key="item"
                                            :label="item"
                                            :value="item"></el-option>
                                    </el-select>
                                </el-form-item>
                                <el-form-item label="Informations" prop="informations">
                                    <el-input type="text" v-model="form.informations" readonly></el-input>
                                </el-form-item>
                                <el-checkbox v-model="form.indexerInflation">A indexer à l’inflation</el-checkbox>
                            </el-col>
                            <el-col :span="6">
                                <el-form-item label="N° sous groupe" prop="nSousGroupe">
                                    <el-select v-model="form.nSousGroupe" @change="changeNSousGroupe">
                                        <el-option v-for="item in nSousGroupes"
                                            :key="item.id"
                                            :label="item.nSousGroupe"
                                            :value="item.nSousGroupe"></el-option>
                                    </el-select>
                                </el-form-item>
                                <el-form-item label="Cession de fin de mois" prop="cessionFinMois" class="custom-append-input">
                                    <el-input type="text" v-model="form.cessionFinMois" class="fixed-input" placeholder="0" :disabled="!cessionFinMoisEnabled"
                                              @change="formatInput('cessionFinMois')">
                                        <template slot="append">
                                            <el-tooltip class="item" effect="dark" content="les loyers seront déduits à partir du mois suivant la cession" placement="top">
                                                <i class="el-icon-info"></i>
                                            </el-tooltip>
                                        </template>
                                    </el-input>
                                </el-form-item>
                            </el-col>
                            <el-col :span="6">
                                <el-form-item label="Nom du groupe" prop="nomGroupe">
                                    <el-input type="text" v-model="form.nomGroupe" readonly></el-input>
                                </el-form-item>
                            </el-col>
                            <el-col :span="6">
                                <el-form-item label="Nature" prop="nature">
                                    <el-select v-model="form.nature" @change="changeNature">
                                        <el-option v-for="item in natures"
                                                   :key="item"
                                                   :label="item"
                                                   :value="item"/>
                                    </el-select>
                                </el-form-item>
                            </el-col>
                        </el-row>
                    </el-collapse-item>
                    <el-collapse-item title="Impacts de la cession" name="2">
                        <el-row :gutter="20">
                            <el-col :span="8">
                                <el-form-item label="Réduction TFPB en €/lot cédé" prop="reductionTfpb">
                                    <el-input type="text" v-model="form.reductionTfpb" placeholder="0"
                                              @change="formatInput('reductionTfpb')"></el-input>
                                </el-form-item>
                                <el-form-item label="Réduction d'amortissement technique annuelle (k€)" prop="reductionAmortissementAnnuelle">
                                    <el-input type="text" v-model="form.reductionAmortissementAnnuelle" class="fixed-input" placeholder="0" :disabled="!reductionAmortissementAnnuelleEnabled"
                                              @change="formatInput('reductionAmortissementAnnuelle')"></el-input>
                                </el-form-item>
                            </el-col>
                            <el-col :span="8">
                                <el-form-item label="Réduction de GE /lgt cédé" prop="reductionGe">
                                    <el-input type="text" v-model="form.reductionGe" placeholder="0"
                                              @change="formatInput('reductionGe')"></el-input>
                                </el-form-item>
                                <el-form-item label="Réduction de reprise de subvention annuelle(k€)" prop="reductionRepriseAnnuelle">
                                    <el-input type="text" v-model="form.reductionRepriseAnnuelle" class="fixed-input" placeholder="0"
                                              @change="formatInput('reductionRepriseAnnuelle')"></el-input>
                                </el-form-item>
                            </el-col>
                            <el-col :span="8">
                                <el-form-item label="Réduction de maintenance courante en €/lgt cédé" prop="reductionMaintenance">
                                    <el-input type="text" v-model="form.reductionMaintenance" class="fixed-input" placeholder="0"
                                              @change="formatInput('reductionMaintenance')"></el-input>
                                </el-form-item>
                                <el-form-item label="Durée d'amortissement technique résiduelle(année)" prop="dureeResiduelle">
                                    <el-input type="text" v-model="form.dureeResiduelle" class="fixed-input" placeholder="0"
                                              @change="formatInput('dureeResiduelle')"></el-input>
                                </el-form-item>
                            </el-col>
                        </el-row>
                    </el-collapse-item>
                    <el-collapse-item title="Compléments" name="3">
                        <el-row>
                            <el-col :span="5" style="padding-top: 55px;">
                                <div class="carousel-head">
                                    <p>Mois cession</p>
                                </div>
                                <div class="carousel-head">
                                    <p>Nombre de logements</p>
                                </div>
                                <div class="carousel-head">
                                    <p>Prix nets de cession K€/logt</p>
                                </div>
                                <div class="carousel-head">
                                    <p>Remboursement anticipé en K€</p>
                                </div>
                                <div class="carousel-head">
                                    <p>Economies d'annuités cumulées suite à RA-part capital</p>
                                </div>
                                <div class="carousel-head">
                                    <p>Economies d'annuités cumulées suite à RA-part intérèt</p>
                                </div>
                                <div class="carousel-head">
                                    <p>Valeur comptable en k€/log cédé</p>
                                </div>
                            </el-col>
                            <el-col :span="19">
                                <periodique :anneeDeReference="anneeDeReference"
                                            v-model="form.periodiques"
                                            :options="{disable: disable}"
                                            @onChange="periodicOnChange"></periodique>
                            </el-col>
                        </el-row>
                    </el-collapse-item>
                </el-collapse>
            </el-form>
            <span slot="footer" class="dialog-footer">
                <el-button type="primary" @click="dialogVisible = false">Retour</el-button>
                <el-button type="success" @click="save('cessionForm')">Valider</el-button>
            </span>
        </el-dialog>
    </div>
</template>

<script>
import { initPeriodic, periodicFormatter, mathInput, checkAllPeriodics } from '../../../../../../../utils/inputs';
import { updateData, groupBy } from '../../../../../../../utils/helpers'
import customValidator from '../../../../../../../utils/validation-rules';
import Periodique from '../../../../../../../components/partials/Periodique';

export default {
    name: "CessionIdentifiees",
    components: { Periodique },
    props: ['simulationID', 'anneeDeReference', 'patrimoines', 'data', 'error', 'isLoading', 'query'],
    data() {
        return {
            cessions: [],
            dialogVisible: false,
            collapseList: ['1'],
            form: null,
            isEdit: false,
            selectedIndex: null,
            cessionFinMoisEnabled: false,
            reductionAmortissementAnnuelleEnabled: false,
            periodiqueHasError: false,
            columns: [],
            isSubmitting: false,
            nSousGroupes: [],
            natures: [
                'Vente Hlm',
                'Vente en bloc hors groupe',
                'Vente en bloc groupe',
                'Fin de bail LT',
                'Fin usufruit locatif',
                'Autres'
            ],
            formRules: {
                nGroupe: [
                    { required: true, message: "Veuillez sélectionner un N° de groupe", trigger: 'change' }
                ],
                cessionFinMois: customValidator.getRule('positiveInt'),
                reductionAmortissementAnnuelle: customValidator.getRule('positiveInt'),
                reductionTfpb: customValidator.getRule('positiveDouble'),
                reductionGe: customValidator.getRule('positiveDouble'),
                reductionMaintenance: customValidator.getRule('positiveDouble'),
                reductionRepriseAnnuelle: customValidator.getRule('positiveDouble'),
                dureeResiduelle: customValidator.getRule('positiveDouble')
            },
            disable: {}
        }
    },
    created () {
        this.initForm();
    },
    computed: {
        nGroupes() {
            let nGroupes = [];
            this.patrimoines.map(item => {
                if (!nGroupes.includes(item.nGroupe)) {
                    nGroupes.push(item.nGroupe)
                }
            });
            return nGroupes;
        },
        isModify() {
            return this.$store.getters['choixEntite/isModify'];
        }
    },
    methods: {
        initForm() {
            this.form = {
                id: null,
                indexerInflation: true,
                periodiques: {
                    moisCessions: initPeriodic(),
                    nombreLogements: initPeriodic(),
                    prixNetsCessions: initPeriodic(),
                    remboursementAnticipes: initPeriodic(),
                    ecomoniesCapitals: initPeriodic(),
                    ecomoniesInterets: initPeriodic(),
                    valeurCedes: initPeriodic()
                }
            }
            this.initPeriodicOptions();
        },
        initPeriodicOptions() {
            const types = Object.keys(this.form.periodiques);
            types.forEach(type => {
                if (type === 'ecomoniesCapitals') {
                    this.disable['ecomoniesCapitals'] = initPeriodic(50, true);
                } else {
                    this.disable[type] = initPeriodic(50, false);
                }
            });
        },
        tableData(data) {
            if (!_.isNil(data)) {
                let cessions = data.cessions.items.map(item => {
                    if (item.type === 0) {
                        let moisCessions = [];
                        let nombreLogements = [];
                        let prixNetsCessions = [];
                        let remboursementAnticipes = [];
                        let ecomoniesCapitals = [];
                        let ecomoniesInterets = [];
                        let valeurCedes = [];
                        item.cessionPeriodique.items.forEach(periodique => {
                            moisCessions[periodique.iteration - 1] = periodique.moisCession;
                            nombreLogements[periodique.iteration - 1] = periodique.nombreLogements;
                            prixNetsCessions[periodique.iteration - 1] = periodique.prixNetsCession;
                            remboursementAnticipes[periodique.iteration - 1] = periodique.remboursementAnticipe;
                            ecomoniesCapitals[periodique.iteration - 1] = periodique.ecomoniesCapital;
                            ecomoniesInterets[periodique.iteration - 1] = periodique.ecomoniesInterets;
                            valeurCedes[periodique.iteration - 1] = periodique.valeurCede;
                        });
                        return {
                            id: item.id,
                            nGroupe: item.nGroupe,
                            nSousGroupe: item.nSousGroupe,
                            nomGroupe: item.nomGroupe,
                            informations: item.informations,
                            nature: item.nature,
                            indexerInflation: item.indexerInflation,
                            reductionTfpb: item.reductionTfpb,
                            reductionGe: item.reductionGe,
                            reductionMaintenance: item.reductionMaintenance,
                            cessionFinMois: item.cessionFinMois,
                            reductionAmortissementAnnuelle: item.reductionAmortissementAnnuelle,
                            reductionRepriseAnnuelle: item.reductionRepriseAnnuelle,
                            dureeResiduelle: item.dureeResiduelle,
                            periodiques: {
                                moisCessions,
                                nombreLogements,
                                prixNetsCessions,
                                remboursementAnticipes,
                                ecomoniesCapitals,
                                ecomoniesInterets,
                                valeurCedes
                            }
                        };
                    } else {
                        return false;
                    }
                });
                cessions = cessions.filter((item) => item);
                this.cessions = cessions ;
                return cessions;
            } else {
                return [];
            }
        },
        showCreateModal() {
            this.initForm();
            this.dialogVisible = true;
            this.selectedIndex = null;
            this.isEdit = false;
            this.cessionFinMoisEnabled = false;
            this.reductionAmortissementAnnuelleEnabled = false;
        },
        handleEdit(index, row) {
            this.dialogVisible = true;
            this.form = {...row};
            this.selectedIndex = index;
            this.isEdit = true;
            this.changeNature(row.nature);
            this.changeValeurCede();
            this.changeRemboursementAnticipes();
        },
        handleDelete(index, row) {
            this.$confirm('Êtes-vous sûr de vouloir supprimer cette cession identifiée?')
                .then(_ => {
                    this.$apollo.mutate({
                        mutation: require('../../../../../../../graphql/simulations/logements-familiaux/cessions/removeCession.gql'),
                        variables: {
                            cessionUUID: row.id,
                            simulationId: this.simulationID
                        }
                    }).then(() => {
                        updateData(this.query, this.simulationID).then(() => {
                            this.$message({
                                showClose: true,
                                message: 'Cette cession identifiée a bien été supprimée.',
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
                    this.$apollo.mutate({
                        mutation: require('../../../../../../../graphql/simulations/logements-familiaux/cessions/saveCession.gql'),
                        variables: {
                            cession: {
                                simulationId: this.simulationID,
                                uuid: this.form.id,
                                nGroupe: this.form.nGroupe,
                                nSousGroupe: this.form.nSousGroupe,
                                nomGroupe: this.form.nomGroupe,
                                informations: this.form.informations,
                                nature: this.form.nature,
                                indexerInflation: this.form.indexerInflation,
                                reductionTfpb: this.form.reductionTfpb,
                                reductionGe: this.form.reductionGe,
                                reductionMaintenance: this.form.reductionMaintenance,
                                cessionFinMois: this.form.cessionFinMois,
                                reductionAmortissementAnnuelle: this.form.reductionAmortissementAnnuelle,
                                reductionRepriseAnnuelle: this.form.reductionRepriseAnnuelle,
                                dureeResiduelle: this.form.dureeResiduelle,
                                type: 0,
                                periodique: JSON.stringify({
                                    mois_cession: this.form.periodiques.moisCessions,
                                    nombre_logements: this.form.periodiques.nombreLogements,
                                    prix_nets_cession: this.form.periodiques.prixNetsCessions,
                                    remboursement_anticipe: this.form.periodiques.remboursementAnticipes,
                                    ecomonies_capital: this.form.periodiques.ecomoniesCapitals,
                                    ecomonies_interets: this.form.periodiques.ecomoniesInterets,
                                    valeur_cede: this.form.periodiques.valeurCedes
                                })
                            }
                        }
                    }).then(() => {
                        this.isSubmitting = false;
                        this.dialogVisible = false;
                        updateData(this.query, this.simulationID).then(() => {
                            this.$message({
                                showClose: true,
                                message: 'La cession identifiée a bien été enregistrée.',
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
        changeNgroupe(value) {
            const grouped = groupBy(this.patrimoines, patrimoine => patrimoine.nGroupe);
            this.nSousGroupes = grouped.get(value);
            this.form.nSousGroupe = this.nSousGroupes[0].nSousGroupe;
            this.changeNSousGroupe();
        },
        changeNSousGroupe() {
            const patrimoine = this.nSousGroupes.find(item => item.nSousGroupe === this.form.nSousGroupe);
            let form = this.form;
            form.nomGroupe = patrimoine.nomGroupe;
            form.informations = patrimoine.informations;
            this.form = null;
            this.form = form;
        },
        changeNature(val) {
            this.cessionFinMoisEnabled = val === 'Vente en bloc hors groupe' || val === 'Vente en bloc groupe';
        },
        changeValeurCede() {
            this.reductionAmortissementAnnuelleEnabled = false;
            this.form.periodiques.valeurCedes.forEach(item => {
                if (item > 0) {
                    this.reductionAmortissementAnnuelleEnabled = true;
                }
            })
        },
        changeRemboursementAnticipes() {
            this.initPeriodicOptions();
            this.form.periodiques.remboursementAnticipes.forEach(item => {
                if (item > 0) {
                    this.disable['ecomoniesCapitals'] = initPeriodic(50, false);
                }
            });
        },
        back() {
            if (this.selectedIndex > 0) {
                this.selectedIndex--;
                this.form = {...this.cessions[this.selectedIndex]};
            }
        },
        next() {
            if (this.selectedIndex < (this.cessions.length - 1)) {
                this.selectedIndex++;
                this.form = {...this.cessions[this.selectedIndex]};
            }
        },
        periodicOnChange(type) {
            let newPeriodics = this.form.periodiques[type];
            this.form.periodiques[type] = [];
            this.form.periodiques[type] = periodicFormatter(newPeriodics);
            if (type === 'valeurCedes') {
                this.changeValeurCede();
            } else if(type === 'remboursementAnticipes') {
                this.changeRemboursementAnticipes();
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
        setTableColumns() {
            this.columns = [];
            for (var i = 0; i < 50; i++) {
                this.columns.push({
                    label: (parseInt(this.anneeDeReference) + i).toString() + ' Mois cession',
                    prop: `periodiques.moisCessions[${i}]`
                });
                this.columns.push({
                    label: (parseInt(this.anneeDeReference) + i).toString() + ' Nombre de logements',
                    prop: `periodiques.nombreLogements[${i}]`
                });
                this.columns.push({
                    label: (parseInt(this.anneeDeReference) + i).toString() + ' Prix nets de cession K€/logt',
                    prop: `periodiques.prixNetsCessions[${i}]`
                });
                this.columns.push({
                    label: (parseInt(this.anneeDeReference) + i).toString() + ' Remboursement anticipé en K€',
                    prop: `periodiques.remboursementAnticipes[${i}]`
                });
                this.columns.push({
                    label: (parseInt(this.anneeDeReference) + i).toString() + ' Economies d\'annuités cumulée suite à RA-part capital',
                    prop: `periodiques.ecomoniesCapitals[${i}]`
                });
                this.columns.push({
                    label: (parseInt(this.anneeDeReference) + i).toString() + ' Economies d\'annuités cumulée suite à RA-part intérèt',
                    prop: `periodiques.ecomoniesInterets[${i}]`
                });
                this.columns.push({
                    label: (parseInt(this.anneeDeReference) + i).toString() + ' "Valeur nette comptable en k€/log cédé',
                    prop: `periodiques.valeurCedes[${i}]`
                });
            }
        },
        exportCessionIdentifiees() {
           window.location.href = "/export-cession-identifiees/" + this.simulationID;
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
    .cession-identifiees .el-button--small {
        font-weight: 500;
        padding: 0 15px;
        height: 40px;
        font-size: 14px;
        border-radius: 4px;
        text-align: center;
        height: 40px;
    }
</style>