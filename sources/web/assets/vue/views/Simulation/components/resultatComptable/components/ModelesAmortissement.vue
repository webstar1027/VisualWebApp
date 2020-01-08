<template>
    <div class="modeles-amortissement admin-content-wrap">
		 <div class="row">
           <h1 class="admin-content-title">Modèles d'amortissement</h1>
				 <el-col :span="2" :offset="2">
					 <el-upload
							 ref="upload"
							 :action="'/import-modeles-amortissement/'+ simulationID"
							 multiple
							 :limit="10"
							 :before-upload="setQuery(query)"
							 :disabled="!isModify"
							 :on-success="onSuccessImport"
							 :on-error="onErrorImport">
						 <el-button size="small" type="primary">Importer</el-button>
					 </el-upload>
				 </el-col>
					 <el-col :span="2" class="export-button">
					 <el-button type="success" :disabled="!isModify" class="ml-2" @click="exportModelesAmortissement">Exporter</el-button>
					 </el-col>
		 </div>
        <ApolloQuery
                :query="require('../../../../../graphql/simulations/modeles-amortissements/modeleDamortissements.gql')"
                :variables="{
                    simulationId: simulationID
                }">
            <template slot-scope="{ result:{ loading, error, data }, isLoading, query }">

                <el-row>
                    <div v-if="error">Une erreur est survenue !</div>
                    <el-table
                        v-loading="isLoading === 1"
                        :data="tableData(data)"
                        style="width: 100%">
                        <el-table-column sortable column-key="numero" prop="numero" width="150px" label="N° du modèle d’amortissement"></el-table-column>
                        <el-table-column sortable column-key="nom" prop="nom" width="150px" label="Nom du modèle d’amortissement"></el-table-column>
                        <el-table-column sortable column-key="dureeReprise" prop="dureeReprise" width="200px" label="Durée de reprise de la subvention - part foncier"></el-table-column>
                        <el-table-column sortable column-key="structureVentilation" prop="structureVentilation" width="150px" label="Structure et assimilé en %"></el-table-column>
                        <el-table-column sortable column-key="structureAmortissement" prop="structureAmortissement" width="100px" label="Durée"></el-table-column>
                        <el-table-column sortable column-key="menuiserieVentilation" prop="menuiserieVentilation" width="150px" label="Menuiserie extérieure en %"></el-table-column>
                        <el-table-column sortable column-key="menuiserieAmortissement" prop="menuiserieAmortissement" width="100px" label="Durée"></el-table-column>
                        <el-table-column sortable column-key="chauffageVentilation" prop="chauffageVentilation" width="150px" label="Chauffage en %"></el-table-column>
                        <el-table-column sortable column-key="chauffageAmortissement" prop="chauffageAmortissement" width="100px" label="Durée"></el-table-column>
                        <el-table-column sortable column-key="etancheiteVentilation" prop="etancheiteVentilation" width="150px" label="Etanchéité en %"></el-table-column>
                        <el-table-column sortable column-key="etancheiteAmortissement" prop="etancheiteAmortissement" width="100px" label="Durée"></el-table-column>
                        <el-table-column sortable column-key="ravalementVentilation" prop="ravalementVentilation" width="150px" label="Ravalement avec amélioration en %"></el-table-column>
                        <el-table-column sortable column-key="ravalementAmortissement" prop="ravalementAmortissement" width="100px" label="Durée"></el-table-column>
                        <el-table-column sortable column-key="electriciteVentilation" prop="electriciteVentilation" width="150px" label="Electricité en %"></el-table-column>
                        <el-table-column sortable column-key="electriciteAmortissement" prop="electriciteAmortissement" width="100px" label="Durée"></el-table-column>
                        <el-table-column sortable column-key="plomberieVentilation" prop="plomberieVentilation" width="150px" label="Plomberie Sanitaire en %"></el-table-column>
                        <el-table-column sortable column-key="plomberieAmortissement" prop="plomberieAmortissement" width="100px" label="Durée"></el-table-column>
                        <el-table-column sortable column-key="ascenseursVentilation" prop="ascenseursVentilation" width="150px" label="Ascenseurs en %"></el-table-column>
                        <el-table-column sortable column-key="ascenseursAmortissement" prop="ascenseursAmortissement" width="100px" label="Durée"></el-table-column>
                        <el-table-column fixed="right" width="120" label="Actions">
                            <template slot-scope="scope">
                                <el-button type="primary" :disabled="!isModify" icon="el-icon-edit" circle @click="handleEdit(scope.$index, scope.row, query)"></el-button>
                                <el-button type="danger" :disabled="!isModify" icon="el-icon-delete" circle @click="handleDelete(scope.$index, scope.row, query)"></el-button>
                            </template>
                        </el-table-column>
                    </el-table>
                    <el-row class="d-flex justify-content-end my-3">
                        <el-button type="primary" :disabled="!isModify" @click="showCreateModal(query)">Créer un modèle d'amortissement</el-button>
                    </el-row>
                </el-row>
            </template>
        </ApolloQuery>
        <el-dialog
            title="Modification / Création d’un modèle d’amortissement"
            :visible.sync="dialogVisible"
            :close-on-click-modal="false"
            width="65%">
            <el-row v-if="isEdit" class="form-slider text-center mb-5">
                <i class="el-icon-back font-weight-bold" @click="back"></i>
                <i class="el-icon-right font-weight-bold" @click="next"></i>
            </el-row>
            <el-form label-position="left" :model="form" :rules="formRules" label-width="180px" ref="modelesAmortissementForm">
                <el-row :gutter="30">
                    <el-col :span="12">
                        <el-form-item label="N° du modèle d’amortissement:" prop="numero">
                            <el-input type="text" v-model="form.numero" placeholder="N°" class="small-input"></el-input>
                        </el-form-item>
                    </el-col>
                    <el-col :span="12">
                        <el-form-item label="Nom du modèle d’amortissement:" prop="nom">
                            <el-input type="text" v-model="form.nom" placeholder="Nom du modèle"></el-input>
                        </el-form-item>
                    </el-col>
                    <el-col :span="12">
                        <div class="d-flex align-items-center">
                            <el-form-item label="Durée de reprise de la subvention - part foncier:" prop="dureeReprise" class="flex-grow-1">
                                <el-input type="text" v-model="form.dureeReprise" placeholder="0" class="small-input"></el-input>
                            </el-form-item>
                            <el-tooltip class="item ml-2" effect="dark" content="cette donnée n'est utile que pour le développement" placement="top">
                                <i class="el-icon-info"></i>
                            </el-tooltip>
                        </div>
                    </el-col>
                </el-row>
                <el-row class="mt-5">

                    <el-col :span="16" class="ma-rows-align-these">

                        <div class="row">
                            <div class="col col-sm-9">
                                <p class="input-head">Ventilation par composant</p>
                            </div>
                            <div class="col">
                                <p class="input-head">Durée d'amortissement</p>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col col-sm-9">
                                <el-form-item label-width="300px" label="Structure et assimilé" prop="structureVentilation">
                                    <el-input type="text" v-model="form.structureVentilation" class="small-input" placeholder="0"></el-input>
                                </el-form-item>
                            </div>
                            <div class="col">
                                <el-form-item prop="structureAmortissement">
                                    <el-input type="text" v-model="form.structureAmortissement" class="small-input"></el-input>
                                </el-form-item>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col col-sm-9">
                                 <el-form-item label-width="300px" label="Menuiserie extérieure" prop="menuiserieVentilation">
                                    <el-input type="text" v-model="form.menuiserieVentilation" class="small-input" placeholder="0"></el-input>
                                </el-form-item>
                            </div>
                            <div class="col">
                                 <el-form-item prop="menuiserieAmortissement">
                                    <el-input type="text" v-model="form.menuiserieAmortissement" class="small-input" placeholder="0"></el-input>
                                </el-form-item>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col col-sm-9">
                                 <el-form-item label="Chauffage" prop="chauffageVentilation">
                                    <el-input type="text" v-model="form.chauffageVentilation" class="small-input" placeholder="0"></el-input>
                                </el-form-item>
                            </div>
                            <div class="col">
                                <el-form-item prop="chauffageAmortissement">
                                    <el-input type="text" v-model="form.chauffageAmortissement" class="small-input" placeholder="0"></el-input>
                                </el-form-item>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col col-sm-9">
                                <el-form-item label="Etanchéité" prop="etancheiteVentilation">
                                    <el-input type="text" v-model="form.etancheiteVentilation" class="small-input" placeholder="0"></el-input>
                                </el-form-item>
                            </div>
                            <div class="col">
                                 <el-form-item prop="etancheiteAmortissement">
                                    <el-input type="text" v-model="form.etancheiteAmortissement" class="small-input" placeholder="0"></el-input>
                                </el-form-item>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col col-sm-9">
                                 <el-form-item label="Ravalement avec amélioration" prop="ravalementVentilation">
                                    <el-input type="text" v-model="form.ravalementVentilation" class="small-input" placeholder="0"></el-input>
                                </el-form-item>
                            </div>
                            <div class="col">
                                <el-form-item prop="ravalementAmortissement">
                                    <el-input type="text" v-model="form.ravalementAmortissement" class="small-input" placeholder="0"></el-input>
                                </el-form-item>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col col-sm-9">
                                <el-form-item label="Electricité" prop="electriciteVentilation">
                                    <el-input type="text" v-model="form.electriciteVentilation" class="small-input" placeholder="0"></el-input>
                                </el-form-item>
                            </div>
                            <div class="col">
                                 <el-form-item prop="electriciteAmortissement">
                                    <el-input type="text" v-model="form.electriciteAmortissement" class="small-input" placeholder="0"></el-input>
                                </el-form-item>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col col-sm-9">
                                <el-form-item label="Plomberie Sanitaire" prop="plomberieVentilation">
                                    <el-input type="text" v-model="form.plomberieVentilation" class="small-input" placeholder="0"></el-input>
                                </el-form-item>
                            </div>
                            <div class="col">
                                <el-form-item prop="plomberieAmortissement">
                                    <el-input type="text" v-model="form.plomberieAmortissement" class="small-input" placeholder="0"></el-input>
                                </el-form-item>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col col-sm-9">
                                 <el-form-item label="Ascenseurs" prop="ascenseursVentilation">
                                    <el-input type="text" v-model="form.ascenseursVentilation" class="small-input" placeholder="0"></el-input>
                                </el-form-item>
                            </div>
                            <div class="col">
                                <el-form-item prop="ascenseursAmortissement">
                                    <el-input type="text" v-model="form.ascenseursAmortissement" class="small-input" placeholder="0"></el-input>
                                </el-form-item>
                            </div>
                        </div>
                    </el-col>

                    <el-col :span="6">
                        <el-form-item label="Contrôle" prop="controle" class="total-input">
                            <el-input type="text" :value="controle" placeholder="0" class="small-input" readonly></el-input>
                        </el-form-item>
                    </el-col>
                </el-row>
            </el-form>
            <div slot="footer" class="dialog-footer">
                <el-button type="outline-primary" @click="dialogVisible = false">Retour</el-button>
                <el-button type="primary" @click="save('modelesAmortissementForm')">Valider</el-button>
            </div>
        </el-dialog>
    </div>
</template>

<script>
import customValidator from '../../../../../utils/validation-rules'
import { updateData } from '../../../../../utils/helpers'

const numberValidator = customValidator.getRule('positiveDouble', 'change', "Ce champ n'accepte que des champs numériques")
const emptyValidator = { required: true, message: "Le champ doit être renseigner", trigger: 'change' }

export default {
    name: "ModelesAmortissement",
    data() {
        var validateControle = (rule, value, callback) => {
            if (this.form.controle != 100) {
                callback(new Error('La somme des composants doit être égale à 100%'));
            } else {
                callback();
            }
        };
        return {
            simulationID: null,
            anneeDeReference: null,
            resultatComptables: [],
            dialogVisible: false,
            isEdit: false,
            form: null,
            selectedIndex: null,
            query:null,
            formRules: {
                numero: [
                    { required: true, message: "Vous devez saisir un Numéro de modèle d’amortissement", trigger: 'change' },
                    customValidator.getRule('positiveInt')
                ],
                nom: [
                    { required: true, message: "Vous devez saisir un nom de modèle d’amortissement", trigger: 'change' }
                ],
                structureVentilation: [emptyValidator, numberValidator],
                menuiserieVentilation: [emptyValidator, numberValidator],
                chauffageVentilation: [emptyValidator, numberValidator],
                etancheiteVentilation: [emptyValidator, numberValidator],
                ravalementVentilation: [emptyValidator, numberValidator],
                electriciteVentilation: [emptyValidator, numberValidator],
                plomberieVentilation: [emptyValidator, numberValidator],
                ascenseursVentilation: [emptyValidator, numberValidator],
                structureAmortissement: [emptyValidator, numberValidator],
                menuiserieAmortissement: [emptyValidator, numberValidator],
                chauffageAmortissement: [emptyValidator, numberValidator],
                etancheiteAmortissement: [emptyValidator, numberValidator],
                ravalementAmortissement: [emptyValidator, numberValidator],
                electriciteAmortissement: [emptyValidator, numberValidator],
                plomberieAmortissement: [emptyValidator, numberValidator],
                ascenseursAmortissement:  [emptyValidator, numberValidator],
                controle: [
                    { validator: validateControle, trigger: 'change' }
                ]
            }
        }
    },
    created () {
        const simulationID = _.isNil(this.$route.params.id) ? null : this.$route.params.id;
        if (_.isNil(simulationID)) {
            return;
        }
        this.simulationID = simulationID;

        this.initForm();
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
        initForm() {
            this.form = {
                id: null,
                numero: '',
                nom: '',
                dureeReprise: '',
                structureVentilation: 77.7,
                menuiserieVentilation: 3.3,
                chauffageVentilation: 3.2,
                etancheiteVentilation: 1.1,
                ravalementVentilation: 2.1,
                electriciteVentilation: 5.2,
                plomberieVentilation: 4.6,
                ascenseursVentilation: 2.8,
                structureAmortissement: 10,
                menuiserieAmortissement: 10,
                chauffageAmortissement: 10,
                etancheiteAmortissement: 10,
                ravalementAmortissement: 10,
                electriciteAmortissement: 10,
                plomberieAmortissement: 10,
                ascenseursAmortissement: 10,
                controle: 100
            };
        },
        setQuery (query) {
            this.query = query;
        },
        tableData(data) {
            if (!_.isNil(data)) {
                this.resultatComptables = data.modeleDamortissements.items;
                return data.modeleDamortissements.items;
            } else {
                return [];
            }
        },
        showCreateModal(query) {
            this.initForm();
            this.dialogVisible = true;
            this.isEdit = false;
            this.query = query;
        },
        handleEdit(index, row, query) {
            this.dialogVisible = true;
            this.form = {...row};
            this.selectedIndex = index;
            this.isEdit = true;
            this.query = query
        },
        handleDelete(index, row, query) {
            this.$confirm('Êtes-vous sûr de vouloir supprimer ce modèle?')
                .then(_ => {
                    this.$apollo.mutate({
                        mutation: require('../../../../../graphql/simulations/modeles-amortissements/removeModeleDamortissement.gql'),
                        variables: {
                            modeleDamortissementUUID: row.id,
                            simulationId: this.simulationID
                        }
                    }).then(() => {
                        updateData(query, this.simulationID).then(() => {
                                this.$message({
                                showClose: true,
                                message: 'Ce modèle a bien été supprimé.',
                                type: 'success'
                            });
                        })

                    }).catch(error => {
                        updateData(query, this.simulationID).then(() => {
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
                if (valid) {
                    let data = {...this.form};
                    data.simulationId = this.simulationID;
                    this.$apollo.mutate({
                        mutation: require('../../../../../graphql/simulations/modeles-amortissements/saveModeleDamortissement.gql'),
                        variables: {
                            modeleDamortissement: {
                                simulationId: this.simulationID,
                                uuid: this.form.id,
                                numero: this.form.numero,
                                nom: this.form.nom,
                                dureeReprise: this.form.dureeReprise | 0,
                                structureVentilation: this.form.structureVentilation,
                                menuiserieVentilation: this.form.menuiserieVentilation,
                                chauffageVentilation: this.form.chauffageVentilation,
                                etancheiteVentilation: this.form.etancheiteVentilation,
                                ravalementVentilation: this.form.ravalementVentilation,
                                electriciteVentilation: this.form.electriciteVentilation,
                                plomberieVentilation: this.form.plomberieVentilation,
                                ascenseursVentilation: this.form.ascenseursVentilation,
                                structureAmortissement: this.form.structureAmortissement,
                                menuiserieAmortissement: this.form.menuiserieAmortissement,
                                chauffageAmortissement: this.form.chauffageAmortissement,
                                etancheiteAmortissement: this.form.etancheiteAmortissement,
                                ravalementAmortissement: this.form.ravalementAmortissement,
                                electriciteAmortissement: this.form.electriciteAmortissement,
                                plomberieAmortissement: this.form.plomberieAmortissement,
                                ascenseursAmortissement: this.form.ascenseursAmortissement
                            }
                        }
                    }).then(() => {
                        this.dialogVisible = false;
                        updateData(this.query, this.simulationID).then(() => {
                            this.$message({
                                showClose: true,
                                message: 'Les valeurs ont été enregistrées.',
                                type: 'success'
                            });
                        });
                    }).catch(error => {
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
                this.form = {...this.resultatComptables[this.selectedIndex]};
            }
        },
        next() {
            if (this.selectedIndex < (this.resultatComptables.length - 1)) {
                this.selectedIndex++;
                this.form = {...this.resultatComptables[this.selectedIndex]};
            }
        },
        exportModelesAmortissement() {
           window.location.href = "/export-modeles-amortissement/" + this.simulationID;
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
            this.$toasted.error(error.message, {
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
        }
    },
    computed: {
        controle() {
            this.form.controle = (parseFloat(this.form.structureVentilation) +  parseFloat(this.form.menuiserieVentilation) +  parseFloat(this.form.chauffageVentilation) +  parseFloat(this.form.etancheiteVentilation) +  parseFloat(this.form.ravalementVentilation) +  parseFloat(this.form.electriciteVentilation) +  parseFloat(this.form.plomberieVentilation) +  parseFloat(this.form.ascenseursVentilation)).toFixed(2);
            return this.form.controle + '%';
        },
        isModify() {
            return this.$store.getters['choixEntite/isModify'];
        }
    }
}
</script>

<style lang="scss">
/*

    .modeles-amortissement .input-head {
        position: absolute;
        top: -60px;
        left: -40px;
    }

    */

    .modeles-amortissement .el-button--small {
        font-weight: 500;
        padding: 0 15px;
        height: 40px;
        font-size: 14px;
        border-radius: 4px;
        text-align: center;
    }
    .modeles-amortissement .small-input {
        width: 80px;
    }
    .modeles-amortissement .total-input {
        margin-top: 58px;
    }
    .modeles-amortissement .el-table th > .cell {
        white-space: initial;
    }
    .modeles-amortissement .form-slider i {
        font-size: 25px;
        margin-left: 50px;
        cursor: pointer;
    }
    .modeles-amortissement .el-form-item__label {
        margin-top: 7px;
    }

    .ma-rows-align-these{
        .row:not(:first-child){
            .col:nth-child(2){
                margin-top: 26px;
            }
        }
    }

</style>
