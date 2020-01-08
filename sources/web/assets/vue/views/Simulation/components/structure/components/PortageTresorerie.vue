<template>
    <div class="portage admin-content-wrap">
		<div class="row">
        <h1 class="admin-content-title">Portage tresorerie</h1>
			<el-col :span="2" :offset="4">
                <el-button type="success" @click="exportPortageTresorerie">Exporter</el-button>
			</el-col>
        </div>
        <el-row>
            <el-tabs v-model="activeTab">
                <el-tab-pane v-for="(tab, i) in types" :label="tab" :name="`${i+1}`" :key="i">
                    <el-form label-position="left" :model="form" :rules="formRules" label-width="180px" ref="parametreForm">
                        <el-row type="flex" :gutter=20 justify="center">
                            <el-col :span="12" v-if="i==1">
                                <el-form-item :label="'Solde Emplois Ressources des opérations locatives en cours à fin ' + anneeDeReference" prop="soldeEmplois">
                                    <el-input type="text" v-model="form.soldeEmplois" placeholder="0" @change="saveParametres()"></el-input>
                                </el-form-item>
                            </el-col>
                            <el-col :span="12" v-if="i==1">
                                <el-form-item :label="'Dettes fournisseurs d\'immobilisations à fin ' + anneeDeReference" prop="detteFournisseurs">
                                    <el-input type="text" v-model="form.detteFournisseurs" placeholder="0" @change="saveParametres()"></el-input>
                                </el-form-item>
                            </el-col>
                            <el-col :span="12" v-if="i==0">
                                <el-form-item label="Promotion Accession" prop="promotionAccession">
                                    <el-input type="text" v-model="form.promotionAccession" placeholder="0" @change="saveParametres()"></el-input>
                                </el-form-item>
                            </el-col>
                            <el-col :span="12" v-if="i==0">
                                <el-form-item label="Taux d’intérêt du financement externe" prop="tauxInteretFinancement">
                                    <el-input type="text" v-model="form.tauxInteretFinancement" placeholder="0" @change="saveParametres()"></el-input>
                                </el-form-item>
                            </el-col>
                            <el-col :span="12" v-if="i==2">
                                <el-form-item label="Taux d’intérêts sur concours bancaires courants" prop="tauxInteretConcours">
                                    <el-input type="text" v-model="form.tauxInteretConcours" placeholder="0" @change="saveParametres()"></el-input>
                                </el-form-item>
                            </el-col>
                        </el-row>
                    </el-form>
                    <el-row class="mt-2" v-loading="isLoading">
                        <el-col :span="5" style="padding-top: 65px;">
                            <div class="carousel-head" v-for="(row, j) in portage[tab].periodiques" :key="j">
                                <p class="carousel-head">
                                    {{addAnneDeReferenceTitle(row.nom)}}
                                    <el-tooltip
                                            v-if="i==1 && j==0"
                                            class="item"
                                            effect="dark"
                                            content="Terminées non soldées"
                                            placement="top"
                                    >
                                        <i class="el-icon-question"></i>
                                    </el-tooltip>
                                </p>
                            </div>

                        </el-col>
                        <el-col :span="19">
                            <el-carousel :autoplay="false" :loop="false" trigger="click" :height="`${setCarouselHeight(tab)}px`" arrow="always" style="min-height: 200px">
                                <el-carousel-item v-for="item in 5" :key="item" >
                                    <div class="item-wrapper">
                                        <div v-for="key in 12" :key="key" class="period-item" >
                                        <span v-for="(row, j) in portage[tab].periodiques" :key="j"
                                              class="input-wrapper" :class="{'is-error':getIteration(item, key) < 50 && !isFloat(row.portageTresoreriePeriodique.items[getIteration(item, key)].valeur)}">
                                            <p v-if="getIteration(item, key) < 50 && (row.nom == 'Préliminaires locatifs' ||
                                            row.nom =='Subventions à encaisser sur opérations locatives en cours et TNS à fin ' ||
                                            row.nom =='Dettes / Créances hors d’exploitation'
                                            )" class="text-center">{{parseInt(anneeDeReference) + getIteration(item, key)}}
                                                <span>*</span>
                                            </p>
                                            <p v-else class="empty-head">&nbsp;</p>
                                            <el-input placeholder="0"
                                                      v-if="getIteration(item, key) < 50"
                                                      v-model="portage[tab].periodiques[j].portageTresoreriePeriodique.items[getIteration(item, key)].valeur"
                                                      @change="save(tab, j)"
                                            ></el-input>
                                            <el-input v-else
                                                      :disabled="true"
                                            ></el-input>
                                        </span>
                                        </div>
                                        <div class="period-item">
                                            <p>&nbsp;</p>
                                            <div class="reset-control" v-for="(row, j) in portage[tab].periodiques" :key="j">
                                                <i class="el-icon-refresh" @click="refreshPeriodic(tab, j)"></i>
                                            </div>
                                        </div>
                                    </div>
                                </el-carousel-item>
                            </el-carousel>
                        </el-col>
                    </el-row>
                </el-tab-pane>
            </el-tabs>
        </el-row>
    </div>
</template>

<script>
    import {
        isFloat,
        checkObjectPeriodic,
        periodicObjectFormatter,
        repeatObjectPeriodic
    } from '../../../../../utils/inputs'
    import customValidator from '../../../../../utils/validation-rules'

    export default {
        name: "PortageTresorerie",
        data() {
            return {
                activeTab: null,
                simulationID: null,
                anneeDeReference: null,
                inputError: false,
                portageTresorerie: null,
                isLoading: true,
                types: [
                    "Portage moyen terme des investissements",
                    "Portage court terme des investissements",
                    "Autres variations potentiel financier"
                ],
                portage : {
                    "Portage moyen terme des investissements": {
                        parametres: [],
                        periodiques: [],
                    },
                    "Portage court terme des investissements":  {
                        parametres: [],
                        periodiques: [],
                    },
                    "Autres variations potentiel financier":  {
                        parametres: [],
                        periodiques: [],
                    }
                },
                form: {},
                formRules: {
                    soldeEmplois: customValidator.getRule('positiveDouble'),
                    detteFournisseurs: customValidator.getRule('positiveDouble'),
                    promotionAccession: customValidator.getRule('positiveDouble'),
                    tauxInteretFinancement: customValidator.getRule('positiveDouble'),
                    tauxInteretConcours: customValidator.getRule('positiveDouble'),
                }
            }
        },
        created() {
            const simulationID = _.isNil(this.$route.params.id) ? null : this.$route.params.id;
            if (_.isNil(simulationID)) {
                return;
            }
            this.simulationID = simulationID;
            this.activeTab = _.isNil(this.$route.query.tab) ? '1' : this.$route.query.tab;
            this.getAnneeDeReference();
            this.getPortageTresorerieParametre();
            this.getPortageTresorerie();
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
            isFloat(valeur) {
                return valeur === null? true: isFloat(valeur);
            },
            changeTab() {
                this.$router.push({
                    path: 'portage-tresorerie',
                    query: {tab: this.activeTab}
                });
            },
            setTabData () {
                let data = this.portageTresorerie;
                if (data) {
                    data.filter((row) => {
                        if (row.estParametre) {
                            this.portage[row.type].parametres.push(row);
                        } else {
                            this.portage[row.type].periodiques.push(row);
                        }
                    });
                    this.isLoading = false
                }
            },
            setCarouselHeight (type) {
                return this.portage[type].periodiques.length * 90 + 30
            },
            getIteration(item, key) {
                return ((item - 1) * 11) + key - 1;
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
                    }
                });
            },
            getPortageTresorerie () {
                this.$apollo.query({
                    query: require('../../../../../graphql/simulations/structure-financiere/portage-et-tresorerie/portageTresorerie.gql'),
                    variables: {
                        simulationID: this.simulationID
                    }
                }).then((res) => {
                    this.portageTresorerie = res.data.portageTresorerie.items;
                    this.setTabData();
                })
            },
            getPortageTresorerieParametre () {
                this.$apollo.query({
                    query: require('../../../../../graphql/simulations/structure-financiere/portage-et-tresorerie/portageTresorerieParametre.gql'),
                    variables: {
                        simulationID: this.simulationID
                    }
                }).then((res) => {
                    this.form = res.data.portageTresorerieParametre.items[0];
                })
            },
            refreshPeriodic(type, index) {
                let periodics = this.portage[type].periodiques[index].portageTresoreriePeriodique.items;
                if (checkObjectPeriodic(periodics)) {
                    const newPeriodiques = repeatObjectPeriodic(periodics);
                    this.savePortageTresorerie(this.portage[type].periodiques[index].id, newPeriodiques)
                        .then(() => {
                            this.$message({
                                showClose: true,
                                message: 'Les valeurs ont été enregistrées.',
                                type: 'success'
                            });
                        });
                } else {
                    this.showError()
                }
            },
            save(type, index) {
                let periodics = this.portage[type].periodiques[index].portageTresoreriePeriodique.items;
                this.periodicFormatter(type, index);
                if (checkObjectPeriodic(periodics)) {
                    this.savePortageTresorerie(this.portage[type].periodiques[index].id, periodics)
                        .then(() => {
                            this.$message({
                                showClose: true,
                                message: 'Les valeurs ont été enregistrées.',
                                type: 'success'
                            });
                        })
                } else {
                    this.showError()
                }
            },
            savePortageTresorerie(id, periodiques) {
                return this.$apollo.mutate({
                    mutation: require('../../../../../graphql/simulations/structure-financiere/portage-et-tresorerie/savePortageTresoreriePeriodique.gql'),
                    variables: {
                        portageTresorerie: {
                            id: id,
                            periodique: JSON.stringify({periodique: periodiques})
                        }
                    }
                })
            },
            saveParametres() {
                if (this.isFloat(this.form.soldeEmplois) && this.isFloat(this.form.detteFournisseurs) && this.isFloat(this.form.promotionAccession) && this.isFloat(this.form.tauxInteretFinancement) && this.isFloat(this.form.tauxInteretConcours)) {
                    this.$apollo.mutate({
                        mutation: require('../../../../../graphql/simulations/structure-financiere/portage-et-tresorerie/savePortageTresorerieParametre.gql'),
                        variables: {
                            simulationID: this.simulationID,
                            soldeEmplois: parseFloat(this.form.soldeEmplois),
                            detteFournisseurs: parseFloat(this.form.detteFournisseurs),
                            promotionAccession: parseFloat(this.form.promotionAccession),
                            tauxInteretFinancement: parseFloat(this.form.tauxInteretFinancement),
                            tauxInteretConcours: parseFloat(this.form.tauxInteretConcours)
                        }
                    }).then(() => {
                        this.$message({
                            showClose: true,
                            message: 'Les valeurs ont été enregistrées.',
                            type: 'success'
                        });
                    });
                }
            },
            showError() {
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
            addAnneDeReferenceTitle (data) {
                if(data.indexOf('à fin')  > -1 || data.indexOf('à la fin')  > -1 ) {
                    data += this.anneeDeReference;
                }
                return data;
            },
            periodicFormatter (type, index) {
                let data = periodicObjectFormatter(this.portage[type].periodiques[index].portageTresoreriePeriodique.items);
                Object.assign({}, this.portage[type].periodiques[index].portageTresoreriePeriodique, {'items': data})
            },
            exportPortageTresorerie() {
                window.location.href = "/export-portage-tresorerie/" + this.simulationID;
            },
            onSuccess(res) {
                if (res === 'Résultat comptable importé') {
                    this.$toasted.success(res, {
                        theme: 'toasted-primary',
                        icon: 'check',
                        position: 'top-right',
                        duration: 5000
                    });

                    this.$refs.upload.clearFiles();
                    this.$router.go(0);
                } else {
                    this.$toasted.error(res, {
                        theme: 'toasted-primary',
                        icon: 'error',
                        position: 'top-right',
                        duration: 5000
                    });

                    this.$refs.upload.clearFiles();
                }
            }
        },
        watch: {
            'activeTab' (newVal, oldVal) {
                if (oldVal) {
                    this.changeTab();
                }
            }
        }
    }
</script>

<style type="text/css">
    .portage {
        font-size: 14px;
    }
    .portage .item-wrapper {
        margin: 20px 60px;
        display: table;
    }
    .portage .period-item {
        width: 8.33%;
        font-size: 12px;
        padding: 0 5px;
        display: table-cell;
        text-align: center;
    }
    .portage .period-item .el-input{
        margin-top: 10px;
    }
    .portage .period-item .el-input__inner{
        padding: 0 3px;
        text-align: right;
    }
    .portage .el-carousel__button {
        background-color: #2591eb;
        pointer-events: none;
    }
    .portage .el-carousel__arrow i {
        color: #2591eb;
    }
    .portage .carousel-head {
        height: 58px;
        line-height: 16px;
		margin-top: 8px;
    }
    .portage .reset-control {
        width: 40px;
        height: 68px;
        padding-top: 8px;
    }
    .portage .el-icon-refresh {
        color: #2591eb;
        font-size: 20px;
        font-weight: bold;
        cursor: pointer;
		margin-top: 13px;
    }
	.portage .empty-head{
		margin-bottom: 0;
	}
    .portage .input-wrapper.is-error input {
        border-color: #f56c6c;
    }
    .portage .input-wrapper span {
        display: none;
        color: #f56c6c;
    }
    .portage .input-wrapper input {
        border-radius: 0;
    }
    .portage .input-wrapper.is-error span {
        display: contents;
    }
    .portage [class*=" el-icon-"],
    .portage [class^="el-icon-"] {
        color: #2491eb;
        font-size: 20px;
    }
</style>
