<template>
    <div class="vefa-identifiees">
        <ApolloQuery
                :query="require('../../../../../../../graphql/simulations/accessions/vefa/vefa.gql')"
                :variables="{
                            simulationId: simulationId,
                            type: 'Identifiée'
                        }">
            <template slot-scope="{ result:{ loading, error, data }, isLoading, query }">
                <div v-if="error">Une erreur est survenue !</div>
                <el-table
                        v-loading="isLoading"
                        :data="tableData(data, query)"
                        style="width: 100%">
                    <el-table-column sortable column-key="numero" prop="numero" min-width="120" label="N°">
                        <template slot="header">
                            <span title="N°">N°</span>
                        </template>
                    </el-table-column>
                    <el-table-column sortable column-key="nomOperation" prop="nomOperation" min-width="150" label="Nom de l'opération">
                        <template slot="header">
                            <span title="Nom de l'opération">Nom de l'opération</span>
                        </template>
                    </el-table-column>
                    <el-table-column sortable column-key="directSci" prop="directSci" min-width="180" label="Direct / SCI">
                        <template slot="header">
                            <span title="Direct / SCI">Direct / SCI</span>
                        </template>
                    </el-table-column>
                    <el-table-column sortable column-key="pourcentageDetention" prop="pourcentageDetention" min-width="170" label="% de détention">
                        <template slot="header">
                            <span title="% de détention">% de détention</span>
                        </template>
                    </el-table-column>
                    <el-table-column sortable column-key="prixVente" prop="prixVente" min-width="150" label="Prix de vente">
                        <template slot="header">
                            <span title="Prix de vente">Prix de vente</span>
                        </template>
                    </el-table-column>
                    <el-table-column sortable column-key="tauxMargeBrute" prop="tauxMargeBrute" min-width="150" label="Taux marge brute">
                        <template slot="header">
                            <span title="Taux marge brute">Taux marge brute</span>
                        </template>
                    </el-table-column>
                    <el-table-column sortable column-key="nombreLogement" prop="nombreLogement" min-width="180" label="Nombre de logements">
                        <template slot="header">
                            <span title="Nombre de logements">Nombre de logements</span>
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
            <el-button type="primary" :disabled="!isModify" @click="showCreateModal">Créer une VEFA identifiée</el-button>
        </el-row>

        <el-dialog
                :title="!isEdit ? 'Création d’une VEFA' : 'Modification de la VEFA'"
                :visible.sync="dialogVisible"
                :close-on-click-modal="false"
                width="65%">
            <el-row v-if="isEdit" class="form-slider text-center mb-5">
                <i class="el-icon-back font-weight-bold" @click="back"></i>
                <i class="el-icon-right font-weight-bold" @click="next"></i>
            </el-row>
            <el-form :model="form" :rules="formRules" ref="vefaForm">
                <el-collapse v-model="collapseList">
                    <el-collapse-item title="Données Générales" name="1">
                        <el-row type="flex" justify="space-around" :gutter="24">
                            <el-col :span="8">
                                <el-form-item label="Numéro" prop="numero">
                                    <el-input type="text" v-model="form.numero"></el-input>
                                </el-form-item>
                            </el-col>
                            <el-col :span="8">
                                <el-form-item label="Nom de l’opération" prop="nomOperation">
                                    <el-input type="text" v-model="form.nomOperation"></el-input>
                                </el-form-item>
                            </el-col>
                            <el-col :span="8">
                                <el-form-item label="Direct/SCI" prop="directSci">
                                    <el-select v-model="form.directSci" clearable>
                                        <el-option
                                                v-for="item in directSciList"
                                                v-if="item.value != null"
                                                :key="item.value"
                                                :label="item.label"
                                                :value="item.value">
                                        </el-option>
                                    </el-select>
                                </el-form-item>
                            </el-col>
                        </el-row>

                        <el-row justify="space-around" class="mt-2" :gutter="24">
                            <template>
                                <el-col :span="8">
                                    <el-form-item label="% de détention" prop="pourcentageDetention">
                                        <el-input type="text" v-model="form.pourcentageDetention"
                                                  @change="formatInput('pourcentageDetention')">
                                        </el-input>
                                    </el-form-item>
                                </el-col>
                                <el-col :span="8">
                                    <el-form-item label="Nombre de logement" prop="nombreLogement">
                                        <el-input type="text" v-model="form.nombreLogement"
                                                  @change="formatInput('nombreLogement')"/>
                                    </el-form-item>
                                </el-col>
                            </template>
                        </el-row>
                    </el-collapse-item>
                    <el-collapse-item title="Eléments financiers de la vente" name="2">
                        <el-row justify="space-around" class="mt-2" :gutter="16">
                            <el-col :span="8">
                                <el-form-item label="Prix de vente en K€/logt" prop="prixVente">
                                    <el-input type="text" v-model="form.prixVente"
                                              @change="formatInput('prixVente')"/>
                                </el-form-item>
                            </el-col>
                            <el-col :span="8">
                                <el-form-item prop="tauxMargeBrute">
                                    <template slot="label">
                                        <span>Taux marge brute</span>
                                        <el-tooltip class="item" effect="dark" content="Prix de vente - coût de production (y compris les coûts internes stockés)" placement="top">
                                            <i class="el-icon-info"/>
                                        </el-tooltip>
                                    </template>
                                    <el-input type="text" v-model="form.tauxMargeBrute"
                                              @change="formatInput('tauxMargeBrute')"/>
                                </el-form-item>
                            </el-col>
                        </el-row>
                    </el-collapse-item>
                    <el-collapse-item title="Autres élements VEFA" name="3">
                        <el-row class="mt-4">
                            <el-col :span="5" style="padding-top: 55px;">
                                <div class="carousel-head">Nombre de logements livrés</div>
                                <div class="carousel-head">Portage sur FP K€</div>
                                <div class="carousel-head">Coûts interne (% du prix de vente)</div>
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
                <el-button type="success" :disabled="isSubmitting" @click="save">Valider</el-button>
            </div>
        </el-dialog>
    </div>
</template>
<script>
    import Periodique from '../../../../../../../components/partials/Periodique';
    import customValidator from '../../../../../../../utils/validation-rules';
    import { initPeriodic, checkAllPeriodics, mathInput, periodicFormatter } from '../../../../../../../utils/inputs';
    import {updateDataByType} from "../../../../../../../utils/helpers";

    export default {
        name: "vefaIdentifiees",
        props: ['simulationId', 'anneeDeReference', 'showError'],
        components: { Periodique },
        data() {
            return {
                vefas: [],
                dialogVisible: false,
                collapseList: ['1'],
                isEdit: false,
                isSubmitting: false,
                query: null,
                form: null,
                columns: [],
                typeEnum: {
                    IDENTIFIEE: 1,
                    NON_IDENTIFIEE: 2
                },
                directSciList: [
                    { label: 'Direct', value: 'direct' },
                    { label: 'SCI', value: 'sci' },
                ],
                formRules: {
                    numero: [
                        { required: true, message: "Veuillez saisir un N°", trigger: 'change' },
                        customValidator.getRule('positiveInt'),
                    ],
                    pourcentageDetention: customValidator.getRule('taux'),
                    tauxMargeBrute: customValidator.getRule('taux'),
                    nomOperation: [
                        { required: true, message: "Veuillez saisir un nom de l’opération", trigger: 'change' },
                        customValidator.getRule('maxVarchar')
                    ],
                    nombreLogement: customValidator.getRule('positiveInt'),
                    prixVente: [
                        { required: true, message: "Veuillez saisir un prix de vente", trigger: 'change' },
                        customValidator.getRule('positiveDouble')
                    ]
                },
            }
        },
        computed: {
            isModify() {
                return this.$store.getters['choixEntite/isModify'];
            }
        },
        created () {
            this.initForm()
        },
        methods: {
            showCreateModal() {
                this.initForm();
                this.dialogVisible = true;
                this.isEdit = false;
            },
            initForm() {
                this.form = {
                    id: null,
                    numero: null,
                    nomOperation: null,
                    nomCategorie: null,
                    directSci: null,
                    pourcentageDetention: null,
                    nombreLogement: null,
                    prixVente: null,
                    tauxMargeBrute: null,
                    tauxEvolution: null,
                    dureePeriodeConstruction: null,
                    periodiques: {
                        portageFondsPropresPeriodique: initPeriodic(),
                        coutsInternesPeriodique: initPeriodic(),
                        nombreLogementsPeriodique: initPeriodic()
                    },
                    type: 'Identifiée'
                };
            },
            save () {
                this.$refs['vefaForm'].validate((valid) => {
                    if (valid && checkAllPeriodics(this.form.periodiques)) {
                        this.isSubmitting = true;
                        this.$apollo.mutate({
                            mutation: require('../../../../../../../graphql/simulations/accessions/vefa/saveVefa.gql'),
                            variables: {
                                vefa: {
                                    uuid: this.form.id,
                                    simulationId: this.simulationId,
                                    numero: this.form.numero,
                                    nomOperation: this.form.nomOperation,
                                    nomCategorie: this.form.nomCategorie,
                                    directSci: this.form.directSci,
                                    pourcentageDetention: this.form.pourcentageDetention,
                                    nombreLogement: this.form.nombreLogement,
                                    prixVente: this.form.prixVente,
                                    tauxMargeBrute: this.form.tauxMargeBrute,
                                    tauxEvolution: this.form.tauxEvolution,
                                    dureePeriodeConstruction: this.form.dureePeriodeConstruction,
                                    type: this.form.type,
                                    portageFp: this.form.portageFp,
                                    coutsInternes: this.form.coutsInternes,
                                    periodiques: JSON.stringify({
                                        portageFondsPropresPeriodique: this.form.periodiques.portageFondsPropresPeriodique,
                                        coutsInternesPeriodique: this.form.periodiques.coutsInternesPeriodique,
                                        nombreLogementsPeriodique: this.form.periodiques.nombreLogementsPeriodique
                                    })
                                }
                            }
                        }).then(() => {
                            this.dialogVisible = false;
                            this.isSubmitting = false;
                            updateDataByType(this.query, this.simulationId, 'Identifiée').then(() => {
                                this.$message({
                                    showClose: true,
                                    message: 'Cette VEFA identifiée a bien été enregistrée.',
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
            tableData(data, query) {
                if (!_.isNil(data)) {
                    this.query = query;
                    const vefas = data.vefa.items.map(item => {
                        let portageFondsPropresPeriodique = [];
                        let coutsInternesPeriodique = [];
                        let nombreLogementsPeriodique = [];
                        item.vefaPeriodique.items.forEach(periodique => {
                            portageFondsPropresPeriodique[periodique.iteration - 1] = periodique.portageFp;
                            coutsInternesPeriodique[periodique.iteration - 1] = periodique.coutsInternes;
                            nombreLogementsPeriodique[periodique.iteration - 1] = periodique.nombreLogements;
                        });
                        let row = {...item};
                        row.numero += ''
                        row.prixVente += ''
                        row.periodiques = {
                            portageFondsPropresPeriodique,
                            coutsInternesPeriodique,
                            nombreLogementsPeriodique
                        }
                        return row
                    });
                    this.vefas = vefas;
                    return vefas;
                } else {
                    return [];
                }
            },
            back() {
                if (this.selectedIndex > 0) {
                    this.selectedIndex--;
                    this.form = {...this.vefas[this.selectedIndex]};
                }
            },
            next() {
                if (this.selectedIndex < (this.vefas.length - 1)) {
                    this.selectedIndex++;
                    this.form = {...this.vefas[this.selectedIndex]};
                }
            },
            periodicOnChange(type) {
                let newPeriodics = this.form.periodiques[type];
                this.form.periodiques[type] = [];
                this.form.periodiques[type] = periodicFormatter(newPeriodics);
            },
            handleEdit(index, row) {
                this.dialogVisible = true;
                this.form = {...row};
                this.selectedIndex = index;
                this.isEdit = true;
            },
            handleDelete(index, row) {
                this.$confirm('Êtes-vous sûr de vouloir supprimer cette VEFA?')
                    .then(() => {
                        this.$apollo.mutate({
                            mutation: require('../../../../../../../graphql/simulations/accessions/vefa/removeVefa.gql'),
                            variables: {
                                vefaId: row.id,
                                simulationId: this.simulationId
                            }
                        }).then(() => {
                            updateDataByType(this.query, this.simulationId, 'Identifiée').then(() => {
                                this.$message({
                                    showClose: true,
                                    message: 'Cette VEFA identifiée a bien été supprimée.',
                                    type: 'success'
                                });
                            })
                        }).catch(error => {
                            updateDataByType(this.query, this.simulationId, 'Identifiée').then(() => {
                                this.$message({
                                    showClose: true,
                                    message: error.networkError.result,
                                    type: 'error',
                                });
                            });
                        });
                    })
            },
            setTableColumns() {
                this.columns = [];
                for (var i = 0; i < 50; i++) {
                    this.columns.push({
                        label: (parseInt(this.anneeDeReference) + i).toString() + ' Portage sur FP',
                        prop: `periodiques.portageFondsPropresPeriodique[${i}]`
                    });
                    this.columns.push({
                        label: (parseInt(this.anneeDeReference) + i).toString() + ' Coûts internes',
                        prop: `periodiques.coutsInternesPeriodique[${i}]`
                    });
                    this.columns.push({
                        label: (parseInt(this.anneeDeReference) + i).toString() + ' Nombre de logements livrés',
                        prop: `periodiques.nombreLogementsPeriodique[${i}]`
                    });
                }
            },
            formatInput(type) {
                this.form[type] = mathInput(this.form[type]);
            },
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
