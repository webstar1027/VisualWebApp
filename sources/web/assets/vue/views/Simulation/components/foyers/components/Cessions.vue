<template>
    <div class="cession-foyers">
        <el-row class="mb-3">
            <el-col :span="2" :offset="19">
                <el-upload
                  ref="upload"
                  :action.native="'/import-cession-foyers/'+ simulationID"
                  multiple
                  :limit="10"
                  :on-success="onSuccess"
                  :on-error="onError">
                  <el-button size="small" type="primary">Importer</el-button>
                </el-upload>
            </el-col>
            <el-col :span="2">
                <el-button type="success" @click.stop="exportCessionFoyers">
                    Exporter
                </el-button>
            </el-col>
        </el-row>
        <ApolloQuery
                :query="require('../../../../../graphql/simulations/foyers/cessions/cessionFoyers.gql')"
                :variables="{
                simulationId: simulationID
            }">
            <template slot-scope="{ result:{ loading, error, data }, isLoading, query }">
                <div v-if="error">Une erreur est survenue !</div>
                <el-table
                    v-loading="isLoading"
                    :data="tableData(data, query)"
                    style="width: 100%">
                    <el-table-column sortable column-key="nGroupe" prop="nGroupe" min-width="120" label="N°Groupe">
                        <template slot="header">
                            <span title="N°Groupe">N°Groupe</span>
                        </template>
                    </el-table-column>
                    <el-table-column sortable column-key="nomIntervention" prop="nomIntervention" min-width="200" label="Nom de l’opération" placeholder="Nom de l’opération">
                        <template slot="header">
                            <span title="Nom de l’opération">Nom de l’opération</span>
                        </template>
                    </el-table-column>
                    <el-table-column sortable column-key="nature" prop="nature" min-width="180" label="Nature de l’opération">
                        <template slot="header">
                            <span title="Nature de l’opération">Nature de l’opération</span>
                        </template>
                    </el-table-column>
                    <el-table-column sortable column-key="indexerInflation" prop="indexerInflation" min-width="120" label="Indexé à l'inflation">
                        <template slot-scope="scope">
                            {{scope.row.indexerInflation ? 'Oui': 'Non'}}
                        </template>
                        <template slot="header">
                            <span title="Indexé à l'inflation">Indexé à l'inflation</span>
                        </template>
                    </el-table-column>
                    <el-table-column sortable column-key="nombreLogements" prop="nombreLogements" min-width="140" label="Nombre équivalent logements">
                        <template slot="header">
                            <span title="Nombre équivalent logements">Nombre équivalent logements</span>
                        </template>
                    </el-table-column>
                    <el-table-column sortable column-key="dateCession" prop="dateCession" min-width="150" label="Date de cession">
                        <template slot-scope="scope">
                            {{scope.row.dateCession | dateFR}}
                        </template>
                        <template slot="header">
                            <span title="Date de cession">Date de cession</span>
                        </template>
                    </el-table-column>
                    <el-table-column sortable column-key="prixNetCession" prop="prixNetCession" min-width="120" label="Prix net de cession">
                        <template slot="header">
                            <span title="Prix net de cession">Prix net de cession</span>
                        </template>
                    </el-table-column>
                    <el-table-column sortable column-key="valeurNetteComptable" prop="valeurNetteComptable" min-width="120" label="Valeur nette comptable">
                        <template slot="header">
                            <span title="Valeur nette comptable">Valeur nette comptable</span>
                        </template>
                    </el-table-column>
                    <el-table-column sortable column-key="remboursementAnticipe" prop="remboursementAnticipe" min-width="120" label="Remboursement anticipé">
                        <template slot="header">
                            <span title="Remboursement anticipé">Remboursement anticipé</span>
                        </template>
                    </el-table-column>
                    <el-table-column sortable column-key="reductionAmortissementAnnuelle" prop="reductionAmortissementAnnuelle" min-width="200" label="Réduction d'amortissement technique annuelle (k€)">
                        <template slot="header">
                            <span title="Réduction d'amortissement technique annuelle (k€)">Réduction d'amortissement technique annuelle (k€)</span>
                        </template>
                    </el-table-column>
                    <el-table-column sortable column-key="reductionRepriseAnnuelle" prop="reductionRepriseAnnuelle" min-width="200" label="Réduction de reprise de subvention annuelle (k€)">
                        <template slot="header">
                            <span title="Réduction de reprise de subvention annuelle (k€)">Réduction de reprise de subvention annuelle (k€)</span>
                        </template>
                    </el-table-column>
                    <el-table-column sortable column-key="dureeResiduelle" prop="dureeResiduelle" min-width="220" label="Durée d'amortissement technique résiduelle (année)">
                        <template slot="header">
                            <span title="Durée d'amortissement technique résiduelle (année)">Durée d'amortissement technique résiduelle (année)</span>
                        </template>
                    </el-table-column>
                    <el-table-column sortable column-key="tauxEvolutionTfpb" prop="tauxEvolutionTfpb" min-width="150" label="Taux d'évolution TFPB">
                        <template slot="header">
                            <span title="Taux d'évolution TFPB">Taux d'évolution TFPB</span>
                        </template>
                    </el-table-column>
                    <el-table-column sortable column-key="tauxEvolutionMaintenance" prop="tauxEvolutionMaintenance" min-width="150" label="Taux d'évolution Maintenance courante">
                        <template slot="header">
                            <span title="Taux d'évolution Maintenance courante">Taux d'évolution Maintenance courante</span>
                        </template>
                    </el-table-column>
                    <el-table-column sortable column-key="tauxEvolutionGrosEntretien" prop="tauxEvolutionGrosEntretien" min-width="150" label="Taux d'évolution Gros entretien">
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
            <el-button type="primary" :disabled="!isModify" @click="showCreateModal">Création d’une fiche de cessions foyers</el-button>
        </el-row>
        <el-dialog
            title="Création/Modification des cessions foyers"
            :visible.sync="dialogVisible"
            :close-on-click-modal="false"
            width="70%">
            <el-row v-if="isEdit" class="form-slider text-center mb-4">
                <i class="el-icon-back font-weight-bold" @click="back"></i>
                <i class="el-icon-right font-weight-bold" @click="next"></i>
            </el-row>
            <el-form label-position="left" :model="form" :rules="formRules" label-width="160px" ref="cessionFoyerForm">
                <el-collapse v-model="collapseList">
                    <el-collapse-item title="Données Générales" name="1">
                        <el-row :gutter="20">
                            <el-col :span="6">
                                <el-form-item label="N° de groupe" prop="nGroupe">
                                    <el-input type="text" v-model="form.nGroupe" readonly></el-input>
                                </el-form-item>
                            </el-col>
                            <el-col :span="6">
                                <el-form-item label="Nom de l'opération" prop="nomIntervention">
                                    <el-input type="text" v-model="form.nomIntervention" placeholder="Nom de l'intervention"></el-input>
                                </el-form-item>
                            </el-col>
                            <el-col :span="6">
                                <el-form-item label="Nature" prop="nature">
                                    <el-select v-model="form.nature">
                                        <el-option v-for="item in natures"
                                            :key="item"
                                            :label="item"
                                            :value="item"></el-option>
                                    </el-select>
                                </el-form-item>
                            </el-col>
                            <el-col span="6">
                            <el-form-item label="Nombre équivalents logements" prop="nombreLogements">
                                <el-input type="text" v-model="form.nombreLogements" placeholder="0"
                                          @change="formatInput('nombreLogements')"></el-input>
                            </el-form-item>
                            </el-col>
                        </el-row>
                        <el-row :gutter="24">
                            <el-col :span="6">
                            <el-form-item label="Date de cession / fin de bail" prop="dateCession">
                                <el-date-picker
                                        v-model="form.dateCession"
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
                    <el-collapse-item title="Eléments finnanciers de la cession en K€" name="2">
                        <el-row :gutter="20">
                            <el-col :span="8">
                                <el-form-item label="Prix net de cession" prop="prixNetCession">
                                    <el-input type="text" v-model="form.prixNetCession" placeholder="0.0"
                                              @change="formatInput('prixNetCession')"></el-input>
                                </el-form-item>
                                <el-checkbox v-model="form.indexerInflation">A l’indexer à l’inflation</el-checkbox>
                            </el-col>
                            <el-col :span="8">
                                <el-form-item label="Valeur nette comptable" prop="valeurNetteComptable">
                                    <el-input type="text" v-model="form.valeurNetteComptable" placeholder="0.0"
                                             @change="formatInput('valeurNetteComptable')"></el-input>
                                </el-form-item>
                            </el-col>
                        </el-row>
                    </el-collapse-item>
                    <el-collapse-item title="Impacts de la cession" name="3">
                        <el-row :gutter="20" class="mt-3">
                            <el-col :span="8">
                                <el-form-item label="Réduction d'amortissement technique annuelle" prop="reductionAmortissementAnnuelle">
                                    <el-input type="text" v-model="form.reductionAmortissementAnnuelle" placeholder="0.0"
                                              @change="formatInput('reductionAmortissementAnnuelle')"></el-input>
                                </el-form-item>
                                <el-form-item label="Remboursement anticipé" prop="remboursementAnticipe">
                                    <el-input type="text" v-model="form.remboursementAnticipe" placeholder="0.0"
                                              @change="formatInput('remboursementAnticipe')"></el-input>
                                </el-form-item>
                            </el-col>
                            <el-col :span="8">
                                <el-form-item label="Réduction de reprise de subventions annuelle" prop="reductionRepriseAnnuelle">
                                    <el-input type="text" v-model="form.reductionRepriseAnnuelle" placeholder="0.0"
                                              @change="formatInput('reductionRepriseAnnuelle')"></el-input>
                                </el-form-item>
                            </el-col>
                            <el-col :span="8">
                                <el-form-item label="Durée d'amortissement résiduelle" prop="dureeResiduelle">
                                    <el-input type="text" v-model="form.dureeResiduelle" placeholder="0.0"
                                              @change="formatInput('dureeResiduelle')"></el-input>
                                </el-form-item>
                            </el-col>
                        </el-row>
                    </el-collapse-item>
                    <el-collapse-item title="Compléments" name="4">
                        <el-row class="mt-4">
                            <el-col :span="4" style="padding-top: 50px;">
                                <div class="carousel-head">
                                    <p>Compléments d'annuités - part capital</p>
                                </div>
                                <div class="carousel-head">
                                    <p>Compléments d'annuités - part intérêts</p>
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
                                <div class="carousel-head" style="margin-top: 128px;">
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
            </el-form>
            <div slot="footer" class="dialog-footer">
                <el-button type="primary" @click="dialogVisible = false">Retour</el-button>
                <el-button type="success" :disabled="isSubmitting" @click="save('cessionFoyerForm')">Valider</el-button>
            </div>
        </el-dialog>
    </div>
</template>

<script>
import moment from "moment";
import { getIncremental, updateData } from '../../../../../utils/helpers';
import {    isFloat,
            initPeriodic,
            periodicFormatter,
            checkAllPeriodics,
            mathInput} from '../../../../../utils/inputs';
import customValidator from '../../../../../utils/validation-rules';
import Periodique from '../../../../../components/partials/Periodique';

export default {
    name: "CessionFoyers",
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
        return {
            simulationID: null,
            anneeDeReference: null,
            collapseList: ['1'],
            cessionFoyers: [],
            dialogVisible: false,
            form: null,
            selectedIndex: 0,
            isEdit: false,
            isSubmitting: false,
            inputError: false,
            query: null,
            periodiqueHasError: false,
            columns: [],
            natures: [
                'Vente hors groupe',
                'Vente groupe',
                'Fin de bail LT',
                'Fin Usufruit locatif',
                'Autres'
            ],
            datePickerOptions: {
            },
            formRules: {
                nGroupe: customValidator.getPreset('number.positiveInt'),
                nomIntervention: [
                    { required: true, validator: validateNom, trigger: 'change' }
                ],
                dateCession: customValidator.getRule('required'),
                prixNetCession: customValidator.getPreset('number.positiveDouble'),
                valeurNetteComptable: customValidator.getPreset('number.positiveDouble'),
                remboursementAnticipe: customValidator.getRule('positiveDouble'),
                nombreLogements: customValidator.getRule('positiveInt'),
                tauxEvolutionTfpb: customValidator.getRule('positiveDouble'),
                tauxEvolutionMaintenance: customValidator.getRule('positiveDouble'),
                tauxEvolutionGrosEntretien: customValidator.getRule('positiveDouble'),
                reductionAmortissementAnnuelle: customValidator.getRule('positiveDouble'),
                reductionRepriseAnnuelle: customValidator.getRule('positiveDouble'),
                dureeResiduelle: customValidator.getRule('positiveDouble')
            }
        }
    },
    computed: {
        isModify() {
            return this.$store.getters['choixEntite/isModify'];
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
    methods: {
        isFloat: isFloat,
        initForm() {
            this.form = {
                id: null,
                nGroupe: getIncremental(this.cessionFoyers, 'nGroupe'),
                nomIntervention: '',
                periodiques: {
                    redevance: initPeriodic(),
                    partCapital: initPeriodic(),
                    partInterets: initPeriodic(),
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
                    label: (parseInt(this.anneeDeReference) + i).toString() + ' Redevance',
                    prop: `periodiques.redevance[${i}]`
                });
                this.columns.push({
                    label: (parseInt(this.anneeDeReference) + i).toString() + ' Compléments d\'annuités - part capital',
                    prop: `periodiques.partCapital[${i}]`
                });
                this.columns.push({
                    label: (parseInt(this.anneeDeReference) + i).toString() + ' Compléments d\'annuités - part intérêts',
                    prop: `periodiques.partInterets[${i}]`
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
        tableData(data, query) {
            if (!_.isNil(data)) {
                this.query = query;
                const cessionFoyers = data.cessionFoyers.items.map(item => {
                    let redevance = [];
                    let partCapital = [];
                    let partInterets = [];
                    let tfpb = [];
                    let maintenanceCourante = [];
                    let grosEntretien = [];
                    item.periodique.items.forEach(periodique => {
                        redevance[periodique.iteration - 1] = periodique.redevance;
                        partCapital[periodique.iteration - 1] = periodique.partCapital;
                        partInterets[periodique.iteration - 1] = periodique.partInterets;
                        tfpb[periodique.iteration - 1] = periodique.tfpb;
                        maintenanceCourante[periodique.iteration - 1] = periodique.maintenanceCourante;
                        grosEntretien[periodique.iteration - 1] = periodique.grosEntretien;
                    });
                    let row = {...item};
                    row.periodiques = {
                        redevance,
                        partCapital,
                        partInterets,
                        tfpb,
                        maintenanceCourante,
                        grosEntretien
                    };

                    return row;
                });
                this.cessionFoyers = cessionFoyers;
                return cessionFoyers;
            } else {
                return [];
            }
        },
        showCreateModal() {
            this.initForm();
            this.dialogVisible = true;
            this.isEdit = false;
        },
        handleEdit(index, row) {
            this.dialogVisible = true;
            this.form = {...row};
            this.selectedIndex = index;
            this.isEdit = true;
        },
        handleDelete(index, row) {
            this.$confirm('Êtes-vous sûr de vouloir supprimer cette cession?')
                .then(() => {
                    this.$apollo.mutate({
                        mutation: require('../../../../../graphql/simulations/foyers/cessions/removeCessionFoyer.gql'),
                        variables: {
                            cessionFoyerUUID: row.id,
                            simulationId: this.simulationID
                        }
                    }).then(() => {
                        updateData(this.query, this.simulationID).then(() => {
                            this.$message({
                                showClose: true,
                                message: 'Cette cession a bien été supprimée.',
                                type: 'success'
                            });
                        })
                    }).catch(error => {
                        updateData(this.query, this.simulationID).then(() => {
                            this.$message({
                                showClose: true,
                                message: 'Cette cession n\'existe pas',
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
                    this.$apollo.mutate({
                        mutation: require('../../../../../graphql/simulations/foyers/cessions/saveCessionFoyer.gql'),
                        variables: {
                            cessionFoyer: {
                                uuid: this.form.id,
                                simulationId: this.simulationID,
                                nGroupe: this.form.nGroupe,
                                nomIntervention: this.form.nomIntervention,
                                nature: this.form.nature,
                                indexerInflation: this.form.indexerInflation,
                                nombreLogements: this.form.nombreLogements,
                                dateCession: this.form.dateCession,
                                prixNetCession: this.form.prixNetCession,
                                valeurNetteComptable: this.form.valeurNetteComptable,
                                remboursementAnticipe: this.form.remboursementAnticipe,
                                tauxEvolutionTfpb: this.form.tauxEvolutionTfpb,
                                tauxEvolutionMaintenance: this.form.tauxEvolutionMaintenance,
                                tauxEvolutionGrosEntretien: this.form.tauxEvolutionGrosEntretien,
                                reductionAmortissementAnnuelle: this.form.reductionAmortissementAnnuelle,
                                reductionRepriseAnnuelle: this.form.reductionRepriseAnnuelle,
                                dureeResiduelle: this.form.dureeResiduelle,
                                periodique: JSON.stringify({
                                    redevance: this.form.periodiques.redevance,
                                    part_capital: this.form.periodiques.partCapital,
                                    part_interets: this.form.periodiques.partInterets,
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
                                message: 'Cette cession a bien été enregistrée.',
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
        checkExistNom(value) {
            let cessionFoyers = this.cessionFoyers;
            if (this.isEdit) {
                cessionFoyers = cessionFoyers.filter(item => item.nomIntervention !== this.cessionFoyers[this.selectedIndex].nomIntervention);
            }
            return cessionFoyers.some(item => item.nomIntervention === value);
        },
        back() {
            if (this.selectedIndex > 0) {
                this.selectedIndex--;
                this.form = {...this.cessionFoyers[this.selectedIndex]};
            }
        },
        next() {
            if (this.selectedIndex < (this.cessionFoyers.length - 1)) {
                this.selectedIndex++;
                this.form = {...this.cessionFoyers[this.selectedIndex]};
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
        exportCessionFoyers() {
           window.location.href = "/export-cession-foyers/" + this.simulationID;
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
    filters: {
        dateFR: function(value) {
            return value ? moment.utc(String(value)).format("MM/YYYY") : "";
        }
    }
}
</script>

<style>
    .cession-foyers .form-slider i {
        font-size: 25px;
        margin-left: 50px;
        cursor: pointer;
    }
    .cession-foyers .el-button--small {
        font-weight: 500;
        padding: 0 15px;
        height: 40px;
        font-size: 14px;
        border-radius: 4px;
        text-align: center;
        height: 40px;
    }
    .cession-foyers .fixed-input {
        width: 80px;
    }
    .cession-foyers .carousel-head {
        height: 50px;
    }
    .cession-foyers .el-table th > .cell {
        white-space: initial;
    }
    .cession-foyers .el-collapse-item__header{
        padding-left: 15px;
        font-weight: bold;
        font-size: 16px;
        color: #00436f;
        margin-top: 20px
    }
    .cession-foyers .el-collapse-item__header:hover{
        background-color: rgba(0, 0, 0, 0.03);
    }
    .cession-foyers .el-collapse-item__content {
        padding-top: 20px;
    }
</style>
