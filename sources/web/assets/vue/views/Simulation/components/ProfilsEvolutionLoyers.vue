<template>
    <div class="profil-evolution-loyer admin-content-wrap">
        <h1 class="admin-content-title">Profil d'évolution des Loyers</h1>
        <ApolloQuery
                :query="require('../../../graphql/simulations/profils-evolution-loyers/profilsEvolutionLoyers.gql')"
                :variables="{
                simulationID: simulationID
            }">
            <template slot-scope="{ result:{ loading, error, data }, isLoading, query }">
                <div class="d-flex">
                    <div>
                        <el-checkbox v-model="plafonnement" v-bind:checked="plafonnement" :disabled="!isModify" @change="saveParametre()">Plafonnement des loyers pratiqués au loyer plafond</el-checkbox>
                    </div>
                    <div class="ml-auto d-flex">
                        <el-upload
                          ref="upload"
                          :action="'/import-profils-evolution-loyers/'+ simulationID"
                          multiple
                          :limit="10"
                          :disabled="!isModify"
                          :on-success="onSuccessImport"
                          :on-error="onErrorImport">
                          <el-button size="small" type="primary">Importer</el-button>
                        </el-upload>
                        <el-button type="success" :disabled="!isModify" class="ml-2" @click="exportProfilsEvolutionLoyers">Exporter</el-button>
                    </div>
                  
                </div>
                <div v-if="error">Une erreur est survenue !</div>
                <el-table
                    v-loading="isLoading"
                    :data="tableData(data, query)"
                    class="mt-4"
                    style="width: 100%">
                    <el-table-column sortable column-key="numero" prop="numero" min-width="100" label="N° profil"></el-table-column>
                    <el-table-column sortable column-key="nom" prop="nom" min-width="200" label="Nom profil d’évolution"></el-table-column>
                    <el-table-column v-for="column in columns"
                        sortable
                        min-width="100"
                        :key="column.prop"
                        :prop="column.prop"
                        :label="column.label">
                    </el-table-column>
                    <el-table-column fixed="right" width="120" label="Actions">
                        <template slot-scope="scope">
                            <el-button type="primary" :disabled="!isModify" icon="el-icon-edit" circle @click="handleEdit(scope.$index, scope.row)"></el-button>
                            <el-button type="danger" :disabled="!isModify" icon="el-icon-delete" circle @click="handleDelete(scope.$index, scope.row)"></el-button>
                        </template>
                    </el-table-column>
                </el-table>
                <el-row class="d-flex my-3 justify-content-end">
                    <el-button type="primary" :disabled="!isModify" @click="showCreateModal()">Créer un profil d'évolution des loyers</el-button>
                </el-row>
            </template>
        </ApolloQuery>
        <el-dialog
                title="Nouveau profil d'évolution loyers"
                :visible.sync="dialogVisible"
                :close-on-click-modal="false"
                width="70%">
            <el-row class="input-wrapper">
                <el-form label-position="top" :model="form" :rules="formRules" label-width="180px" ref="profilForm" @submit.native.prevent="save('profilForm')">
                    <p v-if="edit">N° profil</p>
                    <p v-if="edit" class="profil-name">{{ form.numero }}</p>
                    <el-form-item v-else label="N° profil" prop="numero">
                        <el-input type="text" v-model="form.numero" placeholder="N° profil" autocomplete="off"></el-input>
                    </el-form-item>

                    <el-form-item label="Nom profil du taux d’évolution" prop="nom">
                        <el-input type="text" v-model="form.nom" placeholder="Nom profil du taux d’évolution" autocomplete="off"></el-input>
                    </el-form-item>

                    <el-checkbox v-model="form.appliquerIrl">Appliquer I'lRL</el-checkbox>

                    <periodique :anneeDeReference="anneeDeReference"
                                v-model="form.periodiques"
                                @onChange="periodicOnChange"></periodique>
                </el-form>
            </el-row>
            <div slot="footer" class="dialog-footer">
                <el-button type="outline-primary" @click="dialogVisible = false">Retour</el-button>
                <el-button type="primary" @click="save('profilForm')" :disabled="submitted">Valider</el-button>
            </div>
        </el-dialog>
    </div>
</template>

<script>
import Periodique from "../../../components/partials/Periodique";
import {    initPeriodic,
            periodicFormatter,
            checkAllPeriodics } from '../../../utils/inputs';
import { updateData } from '../../../utils/helpers'
import customValidator from "../../../utils/validation-rules";

export default {
    name: "ProfilsEvolutionLoyers",
    components: { Periodique },
    data() {
        return {
            titlePage: 'Profil d\'évolution des loyers',
            simulationID: null,
            anneeDeReference: null,
            parametreID: null,
            dialogVisible: false,
            plafonnement: false,
            submitted: false,
            columns: [],
            form: null,
            query: null,
            formRules: {
                numero: customValidator.getPreset('number.positiveInt'),
                nom: customValidator.getRule('required') 
            },
            edit: false
        }
    },
    computed: {
        isModify() {
            return this.$store.getters['choixEntite/isModify'];
        }
    },
    created () {
        let simulationID = _.isNil(this.$route.params.id) ? null : this.$route.params.id;
        if (_.isNil(simulationID)) {
            return;
        }
        this.simulationID = simulationID;

        this.getAnneeDeReference();
        this.init();
        this.getParametreID();
        this.$apollo
            .query({
                query: require("../../../graphql/administration/partagers/checkStatus.gql"),
                variables: {
                  simulationID: this.simulationID
                }
            })
            .then(response => {
                this.$store.commit('choixEntite/setModify', response.data.checkStatus);
            })
    },
    methods: {
        init() {
            this.form = {
                numero: null,
                nom: '',
                appliquerIrl: false,
                periodiques: {
                    s1: initPeriodic(),
                    s2: initPeriodic()
                }
            };
        },
        getAnneeDeReference() {
            this.$apollo.query({
                query: require('../../../graphql/simulations/simulation.gql'),
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
                    label: (parseInt(this.anneeDeReference) + i).toString() + ' S1',
                    prop: `periodiques.s1[${i}]`
                });
                this.columns.push({
                    label: (parseInt(this.anneeDeReference) + i).toString() + ' S2',
                    prop: `periodiques.s2[${i}]`
                })
            }
        },
        tableData(data, query) {
            if (!_.isNil(data)) {
                this.query = query;
                const profilsEvolutionLoyers = data.profilsEvolutionLoyers.items.map(item => {
                    let s1 = [];
                    let s2 = [];
                    item.profilsEvolutionLoyersPeriodique.items.forEach(periodique => {
                        s1[periodique.iteration - 1] = periodique.s1;
                        s2[periodique.iteration - 1] = periodique.s2;
                    });
                    let row = {...item};
                    row.periodiques = {
                        s1,
                        s2
                    };
                   
                    return row;
                });
                return profilsEvolutionLoyers;
            } else {
                return [];
            }
        },
        showCreateModal() {
            this.dialogVisible = true;
            this.edit = false;
            this.init();
        },
        handleEdit(index, row) {
            this.dialogVisible = true;
            this.edit = true;
            this.form = {...row};
        },
        handleDelete(index, row) {
            this.$confirm("Êtes-vous sûr de vouloir supprimer ce profil ?")
                .then(_ => {
                    this.$apollo.mutate({
                        mutation: require('../../../graphql/simulations/profils-evolution-loyers/removeProfilEvolutionLoyer.gql'),
                        variables: {
                            id: row.id,
                            simulationId: this.simulationID
                        }
                    }).then(() => {
                        updateData(this.query, this.simulationID).then(() => {
                                this.$message({
                                showClose: true,
                                message: 'Le profil a bien été supprimé.',
                                type: "success",
                            });
                        })
                    }).catch(error => {
                        updateData(this.query, this.simulationID).then(() => {
                                this.$message({
                                showClose: true,
                                message: error.networkError.result,
                                type: 'error',
                            });
                        })
                    });
                })
                .catch(_ => {});
        },
        save(formName) {
            this.$refs[formName].validate((valid) => {
                if (valid && checkAllPeriodics(this.form.periodiques)) {
                    const periodiques = JSON.stringify({
                        s1: this.form.periodiques.s1,
                        s2: this.form.periodiques.s2
                    });

                    this.submitted = true;

                    this.$apollo.mutate({
                        mutation: require('../../../graphql/simulations/profils-evolution-loyers/saveProfilEvolutionLoyer.gql'),
                        variables: {
                            profilEvolutionLoyer: {
                                numero: this.form.numero,
                                simulationId: this.simulationID,
                                nom: this.form.nom,
                                appliquerIrl: this.form.appliquerIrl,
                                periodique: periodiques,
                                edit: this.edit
                            }
                        }
                    }).then(() => {
                        this.submitted = false;
                        this.dialogVisible = false;
                        updateData(this.query, this.simulationID)
                            .then(() => {
                                this.$message({
                                    showClose: true,
                                    message: 'Le profil a bien été enregistré.',
                                    type: "success",
                                });
                            })
                    }).catch((error) =>{
                        this.submitted = false;
                        this.$message({
                            showClose: true,
                            message: error.networkError.result,
                            type: 'error'
                        });
                    })
                } else {
                    this.showError();
                }
            });
        },
        exportProfilsEvolutionLoyers() {
           window.location.href = "/export-profils-evolution-loyers/" + this.simulationID;
        },
        onSuccess(res) {
            if (res === 'Résultat comptable importé') {
                this.$toasted.success(res, {
                    theme: 'toasted-primary',
                    icon: 'check',
                    position: 'top-right',
                    duration: 5000
                });
                updateData(this.query, this.simulationID);
            } else {
                this.$toasted.error(res, {
                    theme: 'toasted-primary',
                    icon: 'error',
                    position: 'top-right',
                    duration: 5000
                });
            }
        },
        getParametreID(){
            this.$apollo.query({
                query: require('../../../graphql/simulations/profils-evolution-loyers/profilEvolutionLoyerParametre.gql'),
                variables:{
                    simulationID : this.simulationID
                },
            }).then((response) =>{
               this.parametreID = response.data.profilEvolutionLoyerParametre.items[0].id;
               this.plafonnement = response.data.profilEvolutionLoyerParametre.items[0].plafonnementDesLoyersPratiquesAuLoyerPlafond;
            })
        },
        saveParametre(){
            this.$apollo.mutate({
                mutation: require('../../../graphql/simulations/profils-evolution-loyers/saveProfilEvolutionLoyerParametre.gql'),
                variables:{
                    idParametre: this.parametreID,
                    plafonnement: this.plafonnement
                }
            }).then(() => {
                this.$message({
                    showClose: true,
                    message: 'Le paramètre a bien été enregistré.',
                    type: "success",
                });
            })
        },
        onSuccessImport(res) {
            this.$toasted.success(res, {
                theme: 'toasted-primary',
                icon: 'check',
                position: 'top-right',
                duration: 5000
            });
            this.$refs.upload.clearFiles();
            updateData(this.query, this.simulationID);
        },
        onErrorImport (error) {
            this.$toasted.error(JSON.parse(error.message), {
                theme: 'toasted-primary',
                icon: 'error',
                position: 'top-right',
                duration: 5000
            });
            this.$refs.upload.clearFiles();
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
        }
    }
}
</script>

<style type="text/css">
    .profil-evolution-loyer .profil-name {
        margin-bottom: 30px;
        font-weight: bold;
    }
    .profil-evolution-loyer .el-button--small {
        font-weight: 500;
        padding: 0 15px;
        height: 40px;
        font-size: 14px;
        border-radius: 4px;
        text-align: center;
        height: 40px;
    }
    .profil-evolution-loyer .input-wrapper {
        padding: 0 65px;
    }
    .profil-evolution-loyer .el-checkbox {
        margin-top: 15px;
    }
</style>
