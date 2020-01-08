<template>
    <div class="lotissement">
        <ApolloQuery
                :query="require('../../../../../graphql/simulations/accessions/lotissement/lotissements.gql')"
                :variables="{
                        simulationId: simulationID
                    }">
            <template slot-scope="{ result:{ loading, error, data }, isLoading, query }">
                <el-tabs v-model="activeTab" >
                    <el-tab-pane v-for="(tab, i) in types" :label="tab" :name="`${i+1}`" :key="i">
                        <el-row class="d-flex justify-content-end my-3">
                            <el-button type="primary" @click="handleCreate">
                                Création d'un Lotissement <template v-if="lotissementNonIdentifie">non</template> identifié
                            </el-button>
                        </el-row>
                        <div v-if="error">Une erreur est survenue !</div>
                        <el-table
                                v-loading="isLoading"
                                :data="tableData(data, i, query)"
                                style="width: 100%">
                            <el-table-column fixed
                                             label="N°"
                                             prop="numero">
                                             <template slot="header">
                                                 <span title="N°">N°</span>
                                             </template>
                            </el-table-column>
                            <el-table-column fixed
                                             align="center"
                                             width="250"
                                             label="Nom de l'opération"
                                             prop="nom">
                                             <template slot="header">
                                                 <span title="Nom de l'opération">Nom de l'opération</span>
                                             </template>
                            </el-table-column>
                            <el-table-column v-if="lotissementIdentifie"
                                             align="center"
                                             width="150"
                                             label="Nombre de lots livrés"
                                             prop="nombreLots">
                                             <template slot="header">
                                                 <span title="Nombre de lots livrés">Nombre de lots livrés</span>
                                             </template>
                            </el-table-column>
                            <el-table-column align="center"
                                             width="150"
                                             label="Prix de vente par lot en K€"
                                             prop="prixVente">
                                             <template slot="header">
                                                 <span title="Prix de vente par lot en K€">Prix de vente par lot en K€</span>
                                             </template>
                            </el-table-column>
                            <el-table-column v-if="lotissementNonIdentifie"
                                             align="center"
                                             width="150"
                                             label="Taux d'évolution"
                                             prop="tauxEvolution">
                                             <template slot="header">
                                                 <span title="Taux d'évolution">Taux d'évolution</span>
                                             </template>
                            </el-table-column>
                            <el-table-column align="center"
                                             width="150"
                                             label="Taux marge brute"
                                             prop="tauxBrute">
                                             <template slot="header">
                                                 <span title="Taux marge brute">Taux marge brute</span>
                                             </template>
                            </el-table-column>
                            <el-table-column v-if="lotissementNonIdentifie"
                                             align="center"
                                             width="320"
                                             label="Durée moyenne de la période de construction"
                                             prop="dureeConstruction">
                                             <template slot="header">
                                                 <span title="Durée moyenne de la période de construction">Durée moyenne de la période de construction</span>
                                             </template>
                            </el-table-column>
                            <el-table-column v-if="lotissementIdentifie"
                                             v-for="column in columns"
                                             sortable
                                             min-width="120"
                                             :key="column.prop"
                                             :prop="column.prop"
                                             :label="column.label">
                                             <template slot="header">
                                                 <span :title="column.label">{{ column.label }}</span>
                                             </template>
                            </el-table-column>
                            <el-table-column v-if="lotissementNonIdentifie"
                                             v-for="column in nonColumns"
                                             sortable
                                             min-width="120"
                                             :key="column.prop"
                                             :prop="column.prop"
                                             :label="column.label">
                                             <template slot="header">
                                                 <span :title="column.label">{{ column.label }}</span>
                                             </template>
                            </el-table-column>
                            <el-table-column fixed="right"
                                             width="120"
                                             label="Actions"
                                             align="center">
                                <template slot-scope="scope">
                                    <el-button type="primary" :disabled="!isModify" icon="el-icon-edit" circle
                                               @click="handleEdit(scope.$index, scope.row)"></el-button>
                                    <el-button type="danger" :disabled="!isModify" icon="el-icon-delete" circle
                                               @click="handleDelete(scope.$index, scope.row)"></el-button>
                                </template>
                            </el-table-column>
                        </el-table>
                    </el-tab-pane>
                </el-tabs>
            </template>
        </ApolloQuery>
        <el-dialog :title="`${isEdit ? 'Modifier' : 'Créer' } un Lotissement ${lotissementNonIdentifie ? 'non' : '' } identifié`"
                   :visible.sync="dialogVisible"
                   :close-on-click-modal="false"
                   width="75%">
            <el-row v-if="isEdit" class="form-slider text-center mb-5">
                <i class="el-icon-back font-weight-bold" @click="back"></i>
                <i class="el-icon-right font-weight-bold" @click="next"></i>
            </el-row>
            <el-form v-if="form" v-model="form" :rules="dialogVisible ? formRules : null" :model="form" label-width="160px" ref="lotissementForm">
                <el-collapse v-model="collapseList">
                    <el-collapse-item title="Données Générales" name="1">
                        <el-row :gutter="20">
                            <el-col :span="8">
                                <el-form-item label="Numéro" prop="numero">
                                    <el-input type="text" placeholder="0" v-model="form.numero"></el-input>
                                </el-form-item>
                            </el-col>
                            <el-col :span="8">
                                <el-form-item label="Nom de l'opération" prop="nom">
                                    <el-input type="text" placeholder="Nom d'opération" v-model="form.nom"></el-input>
                                </el-form-item>
                            </el-col>
                            <el-col :span="8">
                                <el-form-item label="Nombre de lots" v-if="lotissementIdentifie" prop="nombreLots">
                                    <el-input type="text" placeholder="0" v-model="form.nombreLots"
                                              @change="formatInput('nombreLots')"></el-input>
                                </el-form-item>
                                <el-form-item label="Durée moyenne de la période de construction" v-if="lotissementNonIdentifie" prop="dureeConstruction">
                                    <el-input type="text" placeholder="0" v-model="form.dureeConstruction"
                                              @change="formatInput('dureeConstruction')"></el-input>
                                </el-form-item>
                            </el-col>
                        </el-row>
                    </el-collapse-item>
                    <el-collapse-item title="Eléments financiers de la vente" name="2">
                        <el-row :gutter="20">
                            <el-col :span="8">
                                <el-form-item label="Prix de vente en K€/logt" prop="prixVente">
                                    <el-input type="text" placeholder="0" v-model="form.prixVente"
                                              @change="formatInput('prixVente')"></el-input>
                                </el-form-item>
                            </el-col>
                            <el-col :span="8">
                                <el-form-item label="Taux de marge brute" prop="tauxBrute" class="custom-append-input">
                                    <el-input type="text" placeholder="0" class="fixed-input" v-model="form.tauxBrute"
                                              @change="formatInput('tauxBrute')">
                                        <template slot="append">
                                            <el-tooltip class="item" effect="dark"  placement="top">
                                                <div slot="content">
                                                    Prix de vente - coût de production<br>
                                                    (y compris les coûts de internes stockés)
                                                </div>
                                                <i class="el-icon-info"></i>
                                            </el-tooltip>
                                        </template>
                                    </el-input>
                                </el-form-item>
                            </el-col>
                            <el-col :span="8">
                                <el-form-item label="Taux d'évolution"  v-if="lotissementNonIdentifie" prop="tauxEvolution">
                                    <el-input type="text" placeholder="0" v-model="form.tauxEvolution"
                                              @change="formatInput('tauxEvolution')"></el-input>
                                </el-form-item>
                            </el-col>
                        </el-row>
                    </el-collapse-item>
                    <el-collapse-item title="Autres éléments de la vente" name="3">
                        <el-row :gutter="20" class="mt-5" v-if="lotissementIdentifie" >
                            <el-col :span="5" style="padding-top: 55px;">
                                <div class="carousel-head">Nombre de lots livrés</div>
                                <div class="carousel-head">Portage sur FP en K€</div>
                                <div class="carousel-head">Coûts internes en % du prix de vente</div>
                            </el-col>
                            <el-col :span="19">
                                <periodique :anneeDeReference="anneeDeReference"
                                            v-model="form.periodiques1"></periodique>
                            </el-col>
                        </el-row>
                        <el-row :gutter="20" class="mt-3" v-if="lotissementNonIdentifie" >
                            <el-col :span="5" style="padding-top: 55px;">
                                <div class="carousel-head">Nombre de lots livrés</div>
                            </el-col>
                            <el-col :span="19">
                                <periodique :anneeDeReference="anneeDeReference"
                                            v-model="form.periodiques2"></periodique>
                            </el-col>
                        </el-row>
                        <el-row class="mt-3" v-if="lotissementNonIdentifie">
                            <el-col :span="5" style="padding-top: 40px;">
                                <div class="carousel-head">Portage sur FP</div>
                                <div class="carousel-head">Coûts internes</div>
                            </el-col>
                            <el-col :span="19" style="padding-left: 60px;">
                                <div v-for="key in 5" :key="key" class="fixed-period-item">
                                    <p class="text-center m-0">{{parseInt(anneeDeReference) + key - 6}}</p>
                                    <el-input
                                            type="text"
                                            placeholder="0"
                                            :class="{'is-error':form.portagePropres[key] !== null && !isFloat(form.portagePropres[key])}"
                                            v-model="form.portagePropres[key]">
                                    </el-input>
                                    <el-input
                                            type="text"
                                            placeholder="0"
                                            :class="{'is-error':form.coutsInternes[key] !== null && !isFloat(form.coutsInternes[key])}"
                                            v-model="form.coutsInternes[key]">
                                    </el-input>
                                </div>
                            </el-col>
                        </el-row>
                    </el-collapse-item>
                </el-collapse>
            </el-form>
            <div slot="footer" class="dialog-footer">
                <el-button type="primary" @click="dialogVisible = false">Retour</el-button>
                <el-button type="success" @click="save" :disabled="isSubmitting">Valider</el-button>
            </div>
        </el-dialog>
    </div>
</template>
<script>
    import { isFloat, initPeriodic, checkAllPeriodics, mathInput } from "../../../../../utils/inputs"
    import customValidator from '../../../../../utils/validation-rules'
    import Periodique from '../../../../../components/partials/Periodique'

    export default {
        name: "Lotissement",
        components: { Periodique },
        data () {
            return {
                query: null,
                activeTab: null,
                simulationID: null,
                anneeDeReference: null,
                collapseList: ['1'],
                inputError: false,
                lotissements: [],
                form:null,
                isEdit: false,
                dialogVisible: false,
                isSubmitting: false,
                columns: [],
                nonColumns: [],
                types : [
                    'Lotissements identifiés',
                    'Lotissements non identifiés'
                ],
                formRules: {
                    numero: customValidator.getPreset('number.positiveInt'),
                    nom: [
                        customValidator.getRule('maxVarchar'),
                        customValidator.getRule('required')
                    ],
                    prixVente: customValidator.getPreset('number.positiveDouble'),
                    nombreLots: customValidator.getRule('positiveDouble')
                },
            }
        },
        computed: {
            lotissementIdentifie () {
                return this.activeTab === '1';
            },
            lotissementNonIdentifie () {
                return this.activeTab === '2';
            },
            isModify() {
                return this.$store.getters['choixEntite/isModify'];
            }
        },
        created () {
            const simulationID = _.isNil(this.$route.params.id) ? null : this.$route.params.id;
            if (_.isNil(simulationID)) {
                return;
            }
            this.simulationID = simulationID;
            this.activeTab = _.isNil(this.$route.query.tab) ? '1' : this.$route.query.tab;
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
        methods : {
            isFloat: isFloat,
            changeTab() {
                this.$router.push({
                    path: 'lotissement',
                    query: {tab: this.activeTab}
                });
            },
            initForm () {
                this.form = {
                    numero: null,
                    nom: null,
                    nombreLots:null,
                    prixVente: null,
                    tauxEvolution:null,
                    tauxBrute:null,
                    dureeConstruction:null,
                    periodiques1: {
                        portagePropres: initPeriodic(),
                        coutsInternes: initPeriodic(),
                        nombreLivres: initPeriodic(),
                    },
                    periodiques2: {
                        nombreLivres: initPeriodic(),
                    },
                    portagePropres: initPeriodic(),
                    coutsInternes: initPeriodic()
                }
            },
            tableData(data, type, query) {
                if (!_.isNil(data)) {
                    this.query = query;
                    let lotissements = data.lotissements.items.map(item => {
                        if (item.type === type) {
                            let portagePropres = [];
                            let coutsInternes = [];
                            let nombreLivres = [];
                            item.periodique.items.forEach(periodique => {
                                portagePropres[periodique.iteration - 1] = periodique.portagePropres;
                                coutsInternes[periodique.iteration - 1] = periodique.coutsInternes;
                                nombreLivres[periodique.iteration - 1] = periodique.nombreLivres;
                            });
                            let row = {...item};
                            if (item.type === 0) {
                                row.periodiques1 = {
                                    portagePropres,
                                    coutsInternes,
                                    nombreLivres
                                };
                                row.periodiques2 = {
                                    nombreLivres: initPeriodic(),
                                };
                            } else {
                                row.portagePropres = portagePropres;
                                row.coutsInternes = coutsInternes;
                                row.periodiques2 = {
                                    nombreLivres
                                };
                                row.periodiques1 = {
                                    portagePropres: initPeriodic(),
                                    coutsInternes: initPeriodic(),
                                    nombreLivres: initPeriodic(),
                                };
                            }
                            return row;
                        } else {
                            return false;
                        }
                    });
                    lotissements = lotissements.filter((item) => item);
                    this.lotissements = lotissements;
                    return lotissements;
                } else {
                    return [];
                }
            },
            handleCreate () {
                this.isEdit = false;
                this.dialogVisible = true;
                this.selectedIndex = null;
                this.initForm();
            },
            handleEdit(index, row) {
                this.isEdit = true;
                this.dialogVisible = true;
                this.form = {...row};
                this.selectedIndex = index;
            },
            handleDelete(index, row) {
                this.$confirm('Êtes-vous sûr de vouloir supprimer ce lotissement ?')
                    .then(() => {
                        this.$apollo.mutate({
                            mutation: require('../../../../../graphql/simulations/accessions/lotissement/removeLotissement.gql'),
                            variables: {
                                lotissementUUID: row.id,
                                simulationId: this.simulationID
                            }
                        }).then(() => {
                            this.updateData(parseInt(this.activeTab) - 1).then(() => {
                                this.$message({
                                    showClose: true,
                                    message: 'Ce lotissement a bien été supprimé.',
                                    type: 'success'
                                });
                            })
                        }).catch(error => {
                            this.updateData(parseInt(this.activeTab) - 1).then(() => {
                                this.$message({
                                    showClose: true,
                                    message: 'Ce lotissement n\'existe pas.',
                                    type: 'error',
                                });
                            });
                        });
                    })
            },
            save (){
                this.$refs['lotissementForm'].validate((valid) => {
                    if (valid && checkAllPeriodics(this.form.periodiques1) && checkAllPeriodics(this.form.periodiques2)) {
                        this.isSubmitting = true;
                        let periodiques;
                        if (parseInt(this.activeTab) == 1) {
                            periodiques = JSON.stringify({
                                portage_propres: this.form.periodiques1.portagePropres,
                                couts_internes: this.form.periodiques1.coutsInternes,
                                nombre_livres: this.form.periodiques1.nombreLivres
                            });
                        } else {
                            periodiques = JSON.stringify({
                                portage_propres: this.form.portagePropres,
                                couts_internes: this.form.coutsInternes,
                                nombre_livres: this.form.periodiques2.nombreLivres
                            });
                        }
                        this.$apollo.mutate({
                            mutation: require('../../../../../graphql/simulations/accessions/lotissement/saveLotissement.gql'),
                            variables: {
                                lotissement: {
                                    uuid: this.form.id,
                                    simulationId: this.simulationID,
                                    numero: this.form.numero,
                                    nom: this.form.nom,
                                    prixVente: this.form.prixVente,
                                    nombreLots: this.form.nombreLots,
                                    prixVenteLot: this.form.prixVenteLot,
                                    tauxBrute: this.form.tauxBrute,
                                    tauxEvolution: this.form.tauxEvolution,
                                    dureeConstruction: this.dureeConstruction,
                                    type: parseInt(this.activeTab) - 1,
                                    periodique: periodiques
                                }
                            }
                        }).then(() => {
                            this.dialogVisible = false;
                            this.isSubmitting = false;
                            this.updateData(parseInt(this.activeTab) - 1).then(() => {
                                this.$message({
                                    showClose: true,
                                    message: 'Ce lotissement a bien été enregistré.',
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
                    }else {
                        this.showError();
                    }
                });
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
                this.nonColumns = [];
                for (var i = 0; i < 5; i++) {
                    this.nonColumns.push({
                        label: (parseInt(this.anneeDeReference) + i - 5).toString() + ' Portage sur FP',
                        prop: `portagePropres[${i}]`
                    });
                    this.nonColumns.push({
                        label: (parseInt(this.anneeDeReference) + i - 5).toString() + ' Coûts internes',
                        prop: `coutsInternes[${i}]`
                    });
                }
                for (var i = 0; i < 50; i++) {
                    this.columns.push({
                        label: (parseInt(this.anneeDeReference) + i).toString() + ' Portage sur FP',
                        prop: `periodiques1.portagePropres[${i}]`
                    });
                    this.columns.push({
                        label: (parseInt(this.anneeDeReference) + i).toString() + ' Coûts internes',
                        prop: `periodiques1.coutsInternes[${i}]`
                    });
                    this.columns.push({
                        label: (parseInt(this.anneeDeReference) + i).toString() + ' Nombre de lots livrés',
                        prop: `periodiques1.nombreLivres[${i}]`
                    });
                    this.nonColumns.push({
                        label: (parseInt(this.anneeDeReference) + i).toString() + ' Nombre de lots livrés',
                        prop: `periodiques2.nombreLivres[${i}]`
                    });
                }
            },
            back() {
                if (this.selectedIndex > 0) {
                    this.selectedIndex--;
                    this.form = {...this.lotissements[this.selectedIndex]};
                }
            },
            next() {
                if (this.selectedIndex < (this.lotissements.length - 1)) {
                    this.selectedIndex++;
                    this.form = {...this.lotissements[this.selectedIndex]};
                }
            },
            formatInput(type) {
                this.form[type] = mathInput(this.form[type]);
            },
            periodicOnChange(type) {
                let newPeriodics = this.form.periodiques[type];
                this.form.periodiques[type] = [];
                this.form.periodiques[type] = periodicFormatter(newPeriodics);
            },
            async updateData (type) {
                await this.query.fetchMore({
                    variables: {
                        simulationId: this.simulationID,
                        type: type
                    },
                    updateQuery: (prev, { fetchMoreResult  }) => {
                        return fetchMoreResult
                    }
                })
            },
        },
        watch : {
            'activeTab' (newVal, oldVal) {
                if (oldVal) {
                    this.changeTab();
                }
            }
        }
    }
</script>
<style>
    .lotissement .fixed-period-item {
        width: 60px;
        font-size: 12px;
        padding: 0 5px;
        display: table-cell;
        text-align: center;
    }
    .lotissement .fixed-period-item .el-input{
        margin-top: 10px;
    }
    .lotissement .fixed-period-item .el-input__inner{
        padding: 0 3px;
        text-align: right;
    }
    .lotissement {
        font-size: 14px;
    }
    .lotissement .fixed-input {
        width: 80px;
    }
    .lotissement .carousel-head {
        height: 50px;
    }
    .lotissement .el-collapse-item__header {
        padding-left: 15px;
        font-weight: bold;
        font-size: 15px;
    }
    .lotissement .el-collapse-item__content {
        padding-bottom: 0;
    }
    .lotissement .el-collapse-item__header i {
        font-weight: bold;
    }
    .lotissement .el-tooltip.el-icon-info {
        font-size: 25px;
        color: #2491eb;
        vertical-align: middle;
    }
    .lotissement .input-wrapper.is-error input {
        border-color: #f56c6c;
    }
    .lotissement .input-wrapper span {
        display: none;
        color:  #f56c6c;
    }
    .lotissement .input-wrapper input {
        border-radius: 0;
        border-color: #dcdfe6;
    }
    .lotissement .input-wrapper.is-error span {
        display: contents;
    }
    .lotissement .form-slider i {
        font-size: 25px;
        margin-left: 50px;
        cursor: pointer;
    }
    .lotissement .el-form-item__label {
        margin-top: 10px;
    }
    .lotissement .form-slider .disabled {
        cursor: default;
        opacity: 0;
    }
    .lotissement .el-collapse-item__header{
        padding-left: 15px;
        font-weight: bold;
        font-size: 16px;
        color: #00436f;
        margin-top: 20px
    }
    .lotissement .el-collapse-item__header:hover{
        background-color: rgba(0, 0, 0, 0.03);
    }
    .lotissement .el-collapse-item__content {
        padding-top: 20px;
    }
</style>
