<template>
    <div class="cession-nonidentifiees">
        <div v-if="error">Une erreur est survenue !</div>
        <el-table
            v-loading="isLoading"
            :data="tableData(data)"
            style="width: 100%">
            <el-table-column sortable column-key="numero" prop="numero">
                <template slot="header">
                    <span title="N°">N°</span>
                </template>
            </el-table-column>
            <el-table-column sortable column-key="nomCategory" prop="nomCategory" min-width="140">
                <template slot="header">
                    <span title="Nom catégorie">Nom catégorie</span>
                </template>
            </el-table-column>
            <el-table-column sortable column-key="nature" prop="nature" min-width="120">
                <template slot="header">
                    <span title="Nature">Nature</span>
                </template>
            </el-table-column>
            <el-table-column sortable column-key="loyerMensuel" prop="loyerMensuel" min-width="150">
                <template slot="header">
                    <span title="Loyer mensuel €/m²/logt">Loyer mensuel €/m²/logt</span>
                </template>
            </el-table-column>
            <el-table-column sortable column-key="reductionTfpb" prop="reductionTfpb" min-width="150">
                <template slot="header">
                    <span title="Réduction TFPB €/lgt cédé">Réduction TFPB €/lgt cédé</span>
                </template>
            </el-table-column>
            <el-table-column sortable column-key="reductionGe" prop="reductionGe" min-width="150">
                <template slot="header">
                    <span title="Réduction de GE €/lgt cédé">Réduction de GE €/lgt cédé</span>
                </template>
            </el-table-column>
            <el-table-column sortable column-key="reductionMaintenance" prop="reductionMaintenance" min-width="200">
                <template slot="header">
                    <span title="Réduction de maintenance courante €/lgt cédé">Réduction de maintenance courante €/lgt cédé</span>
                </template>
            </el-table-column>
            <el-table-column sortable column-key="nombreResiduelle" prop="nombreResiduelle" min-width="210">
                <template slot="header">
                    <span title="Nombre d'années d'amortissement financier résiduelles">Nombre d'années d'amortissement financier résiduelles</span>
                </template>
            </el-table-column>
            <el-table-column sortable column-key="dureeResiduelle" prop="dureeResiduelle" min-width="210">
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
                    <el-button type="primary" :disabled="!isModify" icon="el-icon-edit" circle @click="handleEdit(scope.$index, scope.row)"></el-button>
                    <el-button type="danger" :disabled="!isModify" icon="el-icon-delete" circle @click="handleDelete(scope.$index, scope.row)"></el-button>
                </template>
            </el-table-column>
        </el-table>

        <el-row class="d-flex justify-content-end my-3">
            <el-button type="primary" :disabled="!isModify" @click="showCreateModal">Créer une cession non identifiée</el-button>
        </el-row>

         <el-dialog
            title="Création/Modification de cession NON identifiée"
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
                                <el-form-item label="N° de groupe" prop="numero">
                                    <el-input type="text" v-model="form.numero" placeholder="N°" class="fixed-input" readonly></el-input>
                                </el-form-item>
                            </el-col>
                            <el-col :span="6">
                                <el-form-item label="Nom de la catégorie" prop="nomCategory">
                                    <el-input type="text" v-model="form.nomCategory" placeholder="Nom catégorie"></el-input>
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
                            <el-col :span="6">
                                <el-form-item label="Loyer mensuel moyen en €/m²" prop="loyerMensuel">
                                    <el-input type="text" v-model="form.loyerMensuel" placeholder="0"
                                              @change="formatInput('loyerMensuel')"></el-input>
                                </el-form-item>
                            </el-col>
                        </el-row>
                    </el-collapse-item>
                    <el-collapse-item title="Impacts cessions" name="2">
                        <el-row :gutter="20">
                            <el-col :span="8">
                                <el-form-item label="Réduction TFPB €/lgt cédé" prop="reductionTfpb">
                                    <el-input type="text" v-model="form.reductionTfpb" placeholder="0"
                                              @change="formatInput('reductionTfpb')"></el-input>
                                </el-form-item>
								<el-form-item label="Réduction de GE €/lgt cédé" prop="reductionGe">
									<el-input type="text" v-model="form.reductionGe" placeholder="0"
											  @change="formatInput('reductionGe')"></el-input>
								</el-form-item>
								<el-form-item label="Réduction maintenance en €/logt cédé" prop="reductionMaintenance">
									<el-input type="text" v-model="form.reductionMaintenance" placeholder="0"
											  @change="formatInput('reductionMaintenance')"></el-input>
								</el-form-item>
                            </el-col>
                            <el-col :span="8">
								<el-form-item label="Nombre d'année d'amotissement financier résiduel" prop="nombreResiduelle">
									<el-input type="text" v-model="form.nombreResiduelle" class="fixed-input" placeholder="0"
											  @change="formatInput('nombreResiduelle')"></el-input>
								</el-form-item>
                                <el-form-item label="Durée amortissement technique résiduelle (année)" prop="dureeResiduelle">
                                    <el-input type="text" v-model="form.dureeResiduelle" class="fixed-input" placeholder="0"
                                              @change="formatInput('dureeResiduelle')"></el-input>
                                </el-form-item>
                            </el-col>
                            <el-col :span="8">

                            </el-col>
                        </el-row>
                    </el-collapse-item>
                    <el-collapse-item title="Compléments" name="3">
                        <el-row>
                            <el-col :span="5" style="padding-top: 55px;">
                                <div class="carousel-head">
                                    <span>Nombre de logements cédés</span>
                                </div>
                                <div class="carousel-head">
                                    <span>Prix nets de cession en K€/logt</span>
                                    <el-tooltip class="item" effect="dark" content="Prix de cession déduction faite des frais de commercialisation et d'éventuels frais de remise en état" placement="top">
                                        <i class="el-icon-info"></i>
                                    </el-tooltip>
                                </div>
                                <div class="carousel-head">
                                    <span>Remboursement anticipé en K€/logt cédé</span>
                                </div>
                                <div class="carousel-head">
                                    <span>Valeur nette comptable en K€/logt cédé</span>
                                </div>
                            </el-col>
                            <el-col :span="19">
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
                <el-button type="success" @click="save('cessionForm')">Valider</el-button>
            </div>
        </el-dialog>
    </div>
</template>

<script>
import { initPeriodic, periodicFormatter, mathInput, checkAllPeriodics } from '../../../../../../../utils/inputs';
import customValidator from '../../../../../../../utils/validation-rules';
import Periodique from '../../../../../../../components/partials/Periodique';
import { updateData } from '../../../../../../../utils/helpers'

export default {
    name: "CessionNonidentifiees",
    components: { Periodique },
    props: ['simulationID', 'anneeDeReference', 'data', 'error', 'isLoading', 'query'],
    data() {
        return {
            cessions: [],
            dialogVisible: false,
            collapseList: ['1'],
            form: null,
            isEdit: false,
            selectedIndex: null,
            periodiqueHasError: false,
            isSubmitting: false,
            columns: [],
            natures: [
                'Vente Hlm',
                'Vente en bloc hors groupe',
                'Vente en bloc groupe',
                'Fin de bail LT',
                'Fin usufruit locatif',
                'Autres'
            ],
            formRules: {
                numero: customValidator.getPreset('number.positiveInt'),
                nomCategory: [
                    { required: true, message: "Veuillez saisir un nom catégorie", trigger: 'change' }
                ],
                loyerMensuel: customValidator.getRule('positiveDouble'),
                reductionTfpb: customValidator.getRule('positiveDouble'),
                reductionGe: customValidator.getRule('positiveDouble'),
                reductionMaintenance: customValidator.getRule('positiveDouble'),
                nombreResiduelle: customValidator.getRule('positiveDouble'),
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
        this.initForm();
    },
    methods: {
        initForm() {
            this.form = {
                id: null,
                numero: this.cessions.length + 1,
                periodiques: {
                    nombreLogements: initPeriodic(),
                    prixNetsCessions: initPeriodic(),
                    remboursementAnticipes: initPeriodic(),
                    valeurCedes: initPeriodic()
                }
            }
        },
        tableData(data) {
            if (!_.isNil(data)) {
                let cessions = data.cessions.items.map(item => {
                    if (item.type === 1) {
                        let nombreLogements = [];
                        let prixNetsCessions = [];
                        let remboursementAnticipes = [];
                        let valeurCedes = [];
                        item.cessionPeriodique.items.forEach(periodique => {
                            nombreLogements[periodique.iteration - 1] = periodique.nombreLogements;
                            prixNetsCessions[periodique.iteration - 1] = periodique.prixNetsCession;
                            remboursementAnticipes[periodique.iteration - 1] = periodique.remboursementAnticipe;
                            valeurCedes[periodique.iteration - 1] = periodique.valeurCede;
                        });

                        let row = {...item};
                        row.periodiques = {
                            nombreLogements,
                            prixNetsCessions,
                            remboursementAnticipes,
                            valeurCedes
                        };
                        return row;
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
        },
        handleEdit(index, row) {
            this.dialogVisible = true;
            this.form = {...row};
            this.selectedIndex = index;
            this.isEdit = true;
        },
        handleDelete(index, row) {
            this.$confirm('Êtes-vous sûr de vouloir supprimer cette cession non identifiée?')
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
                                message: 'La cession non identifiée a bien été supprimée.',
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
                                nomCategory: this.form.nomCategory,
                                nature: this.form.nature,
                                loyerMensuel: this.form.loyerMensuel,
                                reductionTfpb: this.form.reductionTfpb,
                                reductionGe: this.form.reductionGe,
                                reductionMaintenance: this.form.reductionMaintenance,
                                nombreResiduelle: this.form.nombreResiduelle,
                                dureeResiduelle: this.form.dureeResiduelle,
                                type: 1,
                                periodique: JSON.stringify({
                                    nombre_logements: this.form.periodiques.nombreLogements,
                                    prix_nets_cession: this.form.periodiques.prixNetsCessions,
                                    remboursement_anticipe: this.form.periodiques.remboursementAnticipes,
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
                                message: 'La cession non identifiée a bien été enregistrée.',
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
        periodicOnChange(type) {
            let newPeriodics = this.form.periodiques[type];
            this.form.periodiques[type] = [];
            this.form.periodiques[type] = periodicFormatter(newPeriodics);
        },
        formatInput(type) {
            this.form[type] = mathInput(this.form[type]);
        },
        setTableColumns() {
            this.columns = [];
            for (var i = 0; i < 50; i++) {
                this.columns.push({
                    label: (parseInt(this.anneeDeReference) + i).toString() + ' Nombre de logements cédés',
                    prop: `periodiques.nombreLogements[${i}]`
                });
                this.columns.push({
                    label: (parseInt(this.anneeDeReference) + i).toString() + ' Prix nets de cession',
                    prop: `periodiques.prixNetsCessions[${i}]`
                });
                this.columns.push({
                    label: (parseInt(this.anneeDeReference) + i).toString() + ' Remboursement anticipé K€/lgt cédé',
                    prop: `periodiques.remboursementAnticipes[${i}]`
                });
                this.columns.push({
                    label: (parseInt(this.anneeDeReference) + i).toString() + ' Valeur comptable en k€/lgt cédé',
                    prop: `periodiques.valeurCedes[${i}]`
                });
            }
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
