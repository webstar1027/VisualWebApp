<template>
    <div class="vefa-non-identifiees">
        <ApolloQuery
                :query="require('../../../../../../../graphql/simulations/accessions/vefa/vefa.gql')"
                :variables="{
                            simulationId: simulationId,
                            type: 'Non identifiée'
                        }">
            <template slot-scope="{ result:{ loading, error, data }, isLoading, query }">
                <div v-if="error">Une erreur est survenue !</div>
                <el-table
                        v-loading="isLoading"
                        :data="tableData(data, query)"
                        style="width: 100%">
                    <el-table-column sortable column-key="numero" prop="numero" min-width="120">
                        <template slot="header">
                            <span title="N°">N°</span>
                        </template>
                    </el-table-column>
                    <el-table-column sortable column-key="nomCategorie" prop="nomCategorie" min-width="150">
                        <template slot="header">
                            <span title="Nom catégorie">Nom catégorie</span>
                        </template>
                    </el-table-column>
                    <el-table-column sortable column-key="tauxEvolution" prop="tauxEvolution" min-width="150">
                        <template slot="header">
                            <span title="Taux d'évolution">Taux d'évolution</span>
                        </template>
                    </el-table-column>
                    <el-table-column sortable column-key="directSci" prop="directSci" min-width="180">
                        <template slot="header">
                            <span title="Direct / SCI">Direct / SCI</span>
                        </template>
                    </el-table-column>
                    <el-table-column sortable column-key="pourcentageDetention" prop="pourcentageDetention" min-width="170">
                        <template slot="header">
                            <span title="% de détention">% de détention</span>
                        </template>
                    </el-table-column>
                    <el-table-column sortable column-key="prixVente" prop="prixVente" min-width="150">
                        <template slot="header">
                            <span title="Prix de vente en k€/logt">Prix de vente en k€/logt</span>
                        </template>
                    </el-table-column>
                    <el-table-column sortable column-key="tauxMargeBrute" prop="tauxMargeBrute" min-width="150">
                        <template slot="header">
                            <span title="Nom marge brute">Nom marge brute</span>
                        </template>
                    </el-table-column>
                    <el-table-column sortable column-key="dureePeriodeConstruction" prop="dureePeriodeConstruction" min-width="150">
                        <template slot="header">
                            <span title="Durée moyenne de la période de construction">Durée moyenne de la période de construction</span>
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
            <el-button type="primary" :disabled="!isModify" @click="showCreateModal">Créer une VEFA non identifiée</el-button>
        </el-row>

        <el-dialog
                :title="!isEdit ? 'Création d’une VEFA' : 'Modification de la VEFA'"
                :visible.sync="dialogVisible"
                :close-on-click-modal="false"
                width="65%">
            <el-row v-if="isEdit" class="form-slider text-center mb-5">
                <i class="el-icon-back font-weight-bold" @click="back"/>
                <i class="el-icon-right font-weight-bold" @click="next"/>
            </el-row>
            <el-form :model="form" :rules="formRules" label-position="right" label-width="auto" ref="vefaForm">
                <el-collapse v-model="collapseList">
                    <el-collapse-item title="Données Générales" name="1">
                        <el-row type="flex" justify="space-around" :gutter="24">
                            <el-col :span="8">
                                <el-form-item label="N°" prop="numero">
                                    <el-input type="text" v-model="form.numero"/>
                                </el-form-item>
                            </el-col>
                            <el-col :span="8">
                                <el-form-item label="Nom de l'opération" prop="nomCategorie">
                                    <el-input type="text" v-model="form.nomCategorie"/>
                                </el-form-item>
                            </el-col>
                            <el-col :span="8">
                                <el-form-item label="Direct / SCI" prop="directSci">
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
                            <el-col :span="8">
                                <el-form-item label="% de détentions" prop="pourcentageDetention">
                                    <el-input type="text" v-model="form.pourcentageDetention"
                                              @change="formatInput('pourcentageDetention')"/>
                                </el-form-item>
                            </el-col>
                            <el-col :span="8">
                                <el-form-item prop="dureePeriodeConstruction">
                                    <template slot="label">
                                        <span class="d-block">Durée de construction (années)</span>
                                    </template>
                                    <el-input type="text" maxlength="1" v-model="form.dureePeriodeConstruction"></el-input>
                                </el-form-item>
                            </el-col>
                        </el-row>
                    </el-collapse-item>
                    <el-collapse-item title="Eléments financiers de la vente" name="2">
                        <el-row justify="space-around" class="mt-2" :gutter="16">
                            <el-col :span="8">
                                <el-form-item prop="prixVente" label="Prix de vente en k€/logt">
                                    <el-input type="text" v-model="form.prixVente"
                                              @change="formatInput('prixVente')">
                                    </el-input>
                                </el-form-item>
                            </el-col>
                            <el-col :span="8">
                                <el-form-item prop="tauxMargeBrute">
                                    <template slot="label">
                                        <span>Taux de marge brute</span>
                                        <el-tooltip class="item" effect="dark" content="Prix de vente - coût de production (y compris les coûts internes stockés)" placement="top">
                                            <i class="el-icon-info"></i>
                                        </el-tooltip>
                                    </template>
                                    <el-input type="text" v-model="form.tauxMargeBrute"
                                              @change="formatInput('tauxMargeBrute')"></el-input>
                                </el-form-item>
                            </el-col>
                            <el-col :span="8">
                                <el-form-item label="Taux d'évolution" prop="tauxEvolution">
                                    <el-input type="text" v-model="form.tauxEvolution"
                                              @change="formatInput('tauxEvolution')"/>
                                </el-form-item>
                            </el-col>
                        </el-row>
                    </el-collapse-item>
                    <el-collapse-item title="Prévisions VEFA" name="3">
                        <el-row class="mt-3">
                            <el-row class="mt-2">
                                <el-col :span="5" style="padding-top: 55px;">
                                    <div class="carousel-head">Nombre de logements livrés</div>
                                </el-col>
                                <el-col :span="19">
                                    <periodique :anneeDeReference="anneeDeReference"
                                                v-model="form.periodiques"
                                                @onChange="periodicOnChange"/>
                                </el-col>
                            </el-row>
                            <el-col :span="5" style="padding-top: 40px;">
                                <div class="carousel-head">Portage sur FO en K€</div>
                                <div class="carousel-head">Coûts internes stockés en % du prix de vente</div>
                            </el-col>
                            <el-col :span="19" style="padding-left: 60px;">
                                <div v-for="key in (dureeContruction || 5)" :key="key" class="fixed-period-item">
                                    <p class="text-center m-0">{{parseInt(anneeDeReference) + key - ((dureeContruction && dureeContruction + 1) || 6) }}</p>
                                    <el-input
                                            type="text"
                                            placeholder="0"
                                            :class="{'is-error':form.portageFp[key] !== null && !isFloat(form.portageFp[key])}"
                                            v-model="form.portageFp[key]"
                                            @change="formatInput('portageFp', true)">
                                    </el-input>
                                    <el-input
                                            type="text"
                                            placeholder="0"
                                            :class="{'is-error':form.coutsInternes[key] !== null && !isFloat(form.coutsInternes[key])}"
                                            v-model="form.coutsInternes[key]"
                                            @change="formatInput('coutsInternes', true)">
                                    </el-input>
                                </div>
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
<style>
    .vefa-non-identifiees .fixed-period-item {
        width: 60px;
        font-size: 12px;
        padding: 0 5px;
        display: table-cell;
        text-align: center;
    }
    .vefa-non-identifiees .fixed-period-item .el-input{
        margin-top: 10px;
    }
    .vefa-non-identifiees .fixed-period-item .el-input__inner{
        padding: 0 3px;
        text-align: right;
    }
</style>
<script>
    import Periodique from '../../../../../../../components/partials/Periodique';
    import {updateDataByType} from "../../../../../../../utils/helpers";
    import { isFloat, initPeriodic, checkAllPeriodics, mathInput, periodicFormatter } from '../../../../../../../utils/inputs';
    import customValidator from '../../../../../../../utils/validation-rules';

    export default {
        name: 'vefaNonIdentifiees',
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
                    nomCategorie: [
                        { required: true, message: "Veuillez saisir un nom catégorie", trigger: 'change' },
                        customValidator.getRule('maxVarchar')
                    ],
                    tauxEvolution: customValidator.getRule('taux'),
                    nombreLogement: customValidator.getPreset('number.positiveInt'),
                    prixVente: [
                        { required: true, message: "Veuillez saisir un prix de vente", trigger: 'change' },
                        customValidator.getRule('positiveDouble')
                    ],
                    dureePeriodeConstruction:  { pattern: /^[1-4]$/, message: 'Cette valeur doit être inférieure à 5', trigger: 'blur' },
                },
            }
        },
        computed: {
            dureeContruction () {
                let value =  parseInt(this.form.dureePeriodeConstruction)
                if (value >= 1 && value < 5) {
                    return value
                }
                return null
            },
            isModify() {
                return this.$store.getters['choixEntite/isModify'];
            }
        },
        created () {
            this.initForm()
        },
        methods: {
            isFloat: isFloat,
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
                    portageFp: initPeriodic(),
                    coutsInternes: initPeriodic(),
                    periodiques: {
                        nombreLogementsPeriodique: initPeriodic()
                    },
                    type: 'Non identifiée'
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
                                    periodiques: JSON.stringify({
                                        portageFondsPropresPeriodique: this.form.portageFp,
                                        coutsInternesPeriodique: this.form.coutsInternes,
                                        nombreLogementsPeriodique: this.form.periodiques.nombreLogementsPeriodique
                                    })
                                }
                            }
                        }).then(() => {
                            this.dialogVisible = false;
                            this.isSubmitting = false;
                            updateDataByType(this.query, this.simulationId, 'Non identifiée').then(() => {
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
                        row.numero += '';
                        row.prixVente += '';
                        row.periodiques = { nombreLogementsPeriodique };
                        row.portageFp = portageFondsPropresPeriodique;
                        row.coutsInternes = coutsInternesPeriodique;
                        return row;
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
                            updateDataByType(this.query, this.simulationId, 'Non identifiée').then(() => {
                                this.$message({
                                    showClose: true,
                                    message: 'Cette VEFA non identifiée a bien été supprimée.',
                                    type: 'success'
                                });
                            })
                        }).catch(error => {
                            updateDataByType(this.query, this.simulationId, 'Non identifiée').then(() => {
                                this.$message({
                                    showClose: true,
                                    message: 'Cette VEFA non identifiée n\'existe pas.',
                                    type: 'error',
                                });
                            });
                        });
                    })
            },
            setTableColumns() {
                this.columns = [];
                for (var i = 0; i < 5; i++) {
                    this.columns.push({
                        label: (parseInt(this.anneeDeReference) + i - 5).toString() + ' Portage sur FP',
                        prop: `portageFp[${i}]`
                    });
                    this.columns.push({
                        label: (parseInt(this.anneeDeReference) + i - 5).toString() + ' Coûts internes',
                        prop: `coutsInternes[${i}]`
                    });
                }
                for (var i = 0; i < 50; i++) {
                    this.columns.push({
                        label: (parseInt(this.anneeDeReference) + i).toString() + ' Nombre de logements livrés',
                        prop: `periodiques.nombreLogementsPeriodique[${i}]`
                    });
                }
            },
            formatInput(type, periodic=false) {
                if (periodic) {
                    let newPeriodics = this.form[type];
                    this.form[type] = [];
                    this.form[type] = periodicFormatter(newPeriodics);
                } else {
                    this.form[type] = mathInput(this.form[type]);
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
