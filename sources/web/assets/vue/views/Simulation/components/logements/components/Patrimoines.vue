<template>
    <div class="patrimoines admin-content-wrap">
        <h1 class="admin-content-title">Patrimoines</h1>
        <ApolloQuery
                :query="require('../../../../../graphql/simulations/logements-familiaux/patrimoines/patrimoines.gql')"
                :variables="{
                    simulationID: simulationID
                }">
            <template slot-scope="{ result:{ loading, error, data }, isLoading, query }">
               <div class="d-flex mb-4">
                    <div class="ml-auto d-flex">
                        <el-upload
                          ref="upload"
                          :action="'/import-patrimoines/'+ simulationID"
                          multiple
                          :limit="10"
                          :disabled="!isModify"
                          :on-success="onSuccessImport"
                          :on-error="onErrorImport">
                          <el-button size="small" type="primary">Importer</el-button>
                        </el-upload>
                        <el-button class="ml-2" type="success" @click="exportPatrimoines">Exporter</el-button>
                    </div>
                </div>
                <el-row>
                    <div v-if="error">Une erreur est survenue !</div>
                    <el-table
                        class="fw"
                        v-loading="isLoading"
                        :data="tableData(data, query)"
                        style="width: 100%">
                        <el-table-column sortable column-key="nGroupe" prop="nGroupe" min-width="120" label="N° groupe">
                            <template slot="header">
                                <span title="N° groupe">N° groupe</span>
                            </template>
                        </el-table-column>
                        <el-table-column sortable column-key="nSousGroupe" prop="nSousGroupe" min-width="150" label="N° sous-groupe">
                            <template slot="header">
                                <span title="N° sous-groupe">N° sous-groupe</span>
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
                        <el-table-column sortable column-key="conventionne" prop="conventionne" min-width="150" label="Conventionné">
                            <template slot-scope="scope">
                                {{scope.row.conventionne ? 'Oui': 'Non'}}
                            </template>
                            <template slot="header">
                                <span title="Conventionné">Conventionné</span>
                            </template>
                        </el-table-column>
                        <el-table-column sortable column-key="surfaceQuittancee" prop="surfaceQuittancee" min-width="170" label="Surface quittancée">
                            <template slot="header">
                                <span title="Surface quittancée">Surface quittancée</span>
                            </template>
                        </el-table-column>
                        <el-table-column sortable column-key="nombreLogements" prop="nombreLogements" min-width="200" label="Nombre de logements">
                            <template slot="header">
                                <span title="Nombre de logements">Nombre de logements</span>
                            </template>
                        </el-table-column>
                        <el-table-column sortable column-key="loyerMensuel" prop="loyerMensuel" min-width="180" label="Loyer mensuel €/m²">
                            <template slot="header">
                                <span title="Loyer mensuel €/m²">Loyer mensuel €/m²</span>
                            </template>
                        </el-table-column>
                        <el-table-column sortable column-key="loyerMensuelPlafond" prop="loyerMensuelPlafond" min-width="230" label="Loyer mensuel plafond €/m²">
                            <template slot="header">
                                <span title="Loyer mensuel plafond €/m²">Loyer mensuel plafond €/m²</span>
                            </template>
                        </el-table-column>
                        <el-table-column sortable column-key="profilsEvolutionLoyerNom" prop="profilsEvolutionLoyerNom" min-width="220" label="Profil d’évolution des loyers">
                            <template slot="header">
                                <span title="Profil d’évolution des loyers">Profil d’évolution des loyers</span>
                            </template>
                        </el-table-column>
                        <el-table-column sortable column-key="secteurFinancier" prop="secteurFinancier" min-width="160" label="Secteur financier">
                            <template slot="header">
                                <span title="Secteur financier">Secteur financier</span>
                            </template>
                        </el-table-column>
                        <el-table-column sortable column-key="natureOperation" prop="natureOperation" min-width="180" label="Nature de l'opération">
                            <template slot="header">
                                <span title="Nature de l'opération">Nature de l'opération</span>
                            </template>
                        </el-table-column>
                        <el-table-column sortable column-key="rehabilite" prop="rehabilite" min-width="150" label="Réhabilité">
                            <template slot-scope="scope">
                                {{scope.row.rehabilite ? 'Oui': 'Non'}}
                            </template>
                            <template slot="header">
                                <span title="Réhabilité">Réhabilité</span>
                            </template>
                        </el-table-column>
                        <el-table-column sortable column-key="zoneGeographique" prop="zoneGeographique" min-width="170" label="Zone géographique">
                            <template slot="header">
                                <span title="Zone géographique">Zone géographique</span>
                            </template>
                        </el-table-column>
                        <el-table-column sortable column-key="typeHabitat" prop="typeHabitat" min-width="130" label="Type habitat">
                            <template slot="header">
                                <span title="Type habitat">Type habitat</span>
                            </template>
                        </el-table-column>
                        <el-table-column sortable column-key="anneeMes" prop="anneeMes" min-width="120" label="Année MES">
                            <template slot="header">
                                <span title="Année MES">Année MES</span>
                            </template>
                        </el-table-column>
                        <el-table-column fixed="right" width="120" label="Actions">
                            <template slot-scope="scope">
                                <el-button type="primary" :disabled="!isModify" icon="el-icon-edit" circle @click="handleEdit(scope.$index, scope.row)"></el-button>
                                <el-button type="danger" :disabled="!isModify" icon="el-icon-delete" circle @click="handleDelete(scope.$index, scope.row)"></el-button>
                            </template>
                        </el-table-column>
                    </el-table>
                </el-row>

                <el-row class="d-flex justify-content-end my-3">
                    <el-button type="primary" :disabled="!isModify" @click="showCreateModal">Créer un groupe du patrimoine</el-button>
                </el-row>
            </template>
        </ApolloQuery>
        <el-dialog
            title="Créer un groupe du patrimoine"
            :visible.sync="dialogVisible"
            :close-on-click-modal="false"
            width="65%">
            <el-row v-if="isEdit" class="form-slider text-center mb-5">
                <i class="el-icon-back font-weight-bold" @click="back"></i>
                <i class="el-icon-right font-weight-bold" @click="next"></i>
            </el-row>
            <el-form :model="form" :rules="formRules" label-width="170px" ref="patrimoineForm">
                <el-row type="flex">
                    <el-col :span="8">
                        <el-form-item label="N° groupe:" prop="nGroupe">
                            <el-input type="text" v-model="form.nGroupe" placeholder="0"></el-input>
                        </el-form-item>
                    </el-col>
                    <el-col :span="8">
                        <el-form-item label="Nom du groupe:" prop="nomGroupe">
                            <el-input type="text" v-model="form.nomGroupe"></el-input>
                        </el-form-item>
                    </el-col>
                    <el-col :span="8">
                        <el-form-item label="N° sous-groupe:" prop="nSousGroupe">
                            <el-input type="text" v-model="form.nSousGroupe" placeholder="0"></el-input>
                        </el-form-item>

                    </el-col>
                </el-row>
                <el-row type="flex" class="mt-2">

                    <el-col :span="8">
                        <el-form-item label="Informations:" prop="informations">
                            <el-input type="text" v-model="form.informations"></el-input>
                            <el-checkbox v-model="form.conventionne">Conventionné</el-checkbox>
                            <el-checkbox v-model="form.rehabilite">Réhabilité</el-checkbox>
                        </el-form-item>
                    </el-col>
                </el-row>
                <el-row type="flex" :gutter="40" class="mt-5">
                    <el-col :span="8">
                        <el-form-item label="Surface quittancée:" prop="surfaceQuittancee">
                            <el-input type="text" placeholder="0"
                                      v-model="form.surfaceQuittancee"
                                      @change="formatInput('surfaceQuittancee')"></el-input>
                            <el-tooltip class="item" effect="dark" content="Surface en m2 habitable, utile ou corrigée servant au calcul du quittancement avant vacance" placement="top">
                                <i class="el-icon-info"></i>
                            </el-tooltip>
                        </el-form-item>
                    </el-col>
                    <el-col :span="8">
                        <el-form-item label="Loyer mensuel €/m²:" prop="loyerMensuel">
                            <el-input type="text" placeholder="0"
                                      v-model="form.loyerMensuel"
                                      @change="formatInput('loyerMensuel')"></el-input>
                        </el-form-item>
                    </el-col>
                    <el-col :span="8">
                        <el-form-item label="Profil d’évolution des loyers:" prop="profilsEvolutionLoyers">
                            <el-select v-model="form.profilsEvolutionLoyers">
                                <el-option v-for="item in profilDevolutionOptions"
                                    :key="item.id"
                                    :label="item.nom"
                                    :value="item.id"></el-option>
                            </el-select>
                        </el-form-item>
                    </el-col>
                </el-row>
                <el-row type="flex" :gutter="40" class="mt-2">
                    <el-col :span="8">
                        <el-form-item label="Nombre de logements:" prop="nombreLogements">
                            <el-input type="text" placeholder="0"
                                      v-model="form.nombreLogements"
                                      @change="formatInput('nombreLogements')"></el-input>
                            <el-tooltip class="item" effect="dark" content="Uniquement les logements familiaux" placement="top">
                                <i class="el-icon-info"></i>
                            </el-tooltip>
                        </el-form-item>
                    </el-col>
                    <el-col :span="8">
                        <el-form-item label="Loyer mensuel plafond €/m²:" prop="loyerMensuelPlafond">
                            <el-input type="text" placeholder="0"
                                      v-model="form.loyerMensuelPlafond"
                                      @change="formatInput('loyerMensuelPlafond')"></el-input>
                        </el-form-item>
                    </el-col>
                </el-row>
                <el-row type="flex" :gutter="40" class="mt-5">
                    <el-col :span="8">
                        <el-form-item label="Secteur financier:" prop="secteurFinancier">
                            <el-input type="text" v-model="form.secteurFinancier" placeholder="Secteur financier"></el-input>
                        </el-form-item>
                    </el-col>
                    <el-col :span="8">
                        <el-form-item label="Nature de l'opération:" prop="natureOperation">
                            <el-input type="text" v-model="form.natureOperation" placeholder="Nature de l'opération"></el-input>
                        </el-form-item>
                    </el-col>
                    <el-col :span="8">
                        <el-form-item label="Zone géographique:" prop="zoneGeographique">
                            <el-input type="text" v-model="form.zoneGeographique" placeholder="Zone géographique"></el-input>
                        </el-form-item>
                    </el-col>
                </el-row>
                <el-row type="flex" :gutter="40" class="mt-2">
                    <el-col :span="8">
                        <el-form-item label="Type habitat:" prop="typeHabitat">
                            <el-input type="text" v-model="form.typeHabitat" placeholder="Type habitat"></el-input>
                        </el-form-item>
                    </el-col>
                    <el-col :span="8">
                        <el-form-item label="Année MES:" prop="anneeMes">
                            <el-input type="text" v-model="form.anneeMes" placeholder="0"></el-input>
                        </el-form-item>
                    </el-col>
                </el-row>
            </el-form>
            <span slot="footer" class="dialog-footer">
                <el-button type="outline-primary" @click="dialogVisible = false">Retour</el-button>
                <el-button type="primary" @click="save('patrimoineForm')" :disabled="isSubmitting">Valider</el-button>
            </span>
        </el-dialog>
    </div>
</template>

<script>
import {isFloat, mathInput} from "../../../../../utils/inputs";
import { updateData } from '../../../../../utils/helpers';
import customValidator from '../../../../../utils/validation-rules';

export default {
    name: "Patrimoines",
    data() {
        var validateGroupe = (rule, value, callback) => {
            if (!value) {
                return callback(new Error("Le N°de groupe doit être renseigné"));
            } else if(isNaN(value)) {
                callback(new Error('Veuillez vérifier le format du champ'));
            } else if(this.checkExistGroupe(value)) {
                return callback(new Error("Ce numéro de groupe/sous groupe est déjà utilisé"));
            } else {
                callback();
            }
        };
        var validateSousGroupe = (rule, value, callback) => {
            if (!value) {
                return callback(new Error("Le N° de groupe doit être renseigné"));
            } else if(isNaN(value)) {
                callback(new Error('Veuillez vérifier le format du champ'));
            } else if(this.checkExistSousGroupe(value)) {
                return callback(new Error("Ce numéro de groupe/sous groupe est déjà utilisé"));
            } else {
                callback();
            }
        };
        return {
            simulationID: null,
            inputError:null,
            patrimoines: [],
            dialogVisible: false,
            form: null,
            selectedIndex: null,
            isEdit: false,
            profilDevolutionOptions: [],
            isSubmitting: false,
            formRules: {
                nGroupe: [
                    { required: true, validator: validateGroupe, trigger: 'blur' }
                ],
                nSousGroupe: [
                    { required: true, validator: validateSousGroupe, trigger: 'blur' }
                ],
                nomGroupe: customValidator.getRule('required'),
                surfaceQuittancee: customValidator.getPreset('number.positiveDouble'),
                nombreLogements: customValidator.getPreset('number.positiveDouble'),
                loyerMensuel: customValidator.getPreset('number.positiveDouble'),
                loyerMensuelPlafond: customValidator.getRule('positiveDouble'),
                anneeMes: customValidator.getRule('positiveDouble')
            }
        }
    },
    computed: {
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
    mounted(){
        this.getProfileDevoltions();
    },
    methods: {
        initForm() {
            this.form = {
                id: null,
                nGroupe: null,
                nSousGroupe: null,
                nomGroupe: '',
                conventionne: true,
                surfaceQuittancee: null,
                nombreLogements: null,
                loyerMensuel: null,
                loyerMensuelPlafond: null,
                secteurFinancier: null,
                zoneGeographique: null,
                natureOperation: null,
                typeHabitat: null,
                rehabilite: false,
                anneeMes: null,
                profilsEvolutionLoyers: null,
                query: null
            };
        },
        getProfileDevoltions() {
            this.$apollo.query({
                query: require('../../../../../graphql/simulations/profils-evolution-loyers/profilsEvolutionLoyers.gql'),
                variables: {
                    simulationID: this.simulationID
                }
            }).then((res) => {
                if (res.data && res.data.profilsEvolutionLoyers) {
                    this.profilDevolutionOptions = res.data.profilsEvolutionLoyers.items;
                }
            });
        },
        tableData(data, query) {
            if (!_.isNil(data)) {
                this.query = query;
                const patrimoines = data.patrimoines.items.map(item => {
                    let row = {...item};
                    row.profilsEvolutionLoyers = item.profilsEvolutionLoyers? item.profilsEvolutionLoyers.id: null;
                    row.profilsEvolutionLoyerNom = item.profilsEvolutionLoyers? item.profilsEvolutionLoyers.nom: null;
                    return row;
                });
                this.patrimoines = patrimoines;
                return patrimoines;
            } else {
                return [];
            }
        },
        checkExistGroupe(value) {
            let groupes = this.patrimoines;
            if (this.isEdit) {
                groupes = groupes.filter(item => item.id !== this.form.id);
            }
            const groupe = groupes.find(patrimoine => patrimoine.nGroupe === value && patrimoine.nSousGroupe === this.form.nSousGroupe);
            return groupe ? true : false;
        },
        checkExistSousGroupe(value) {
            let groupes = this.patrimoines;
            if (this.isEdit) {
                groupes = groupes.filter(item => item.id !== this.form.id);
            }
            const groupe = groupes.find(patrimoine => patrimoine.nSousGroupe === value && patrimoine.nGroupe === this.form.nGroupe);
            return groupe ? true : false;
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
            this.$confirm('Êtes-vous sûr de vouloir supprimer ce patrimoine?')
                .then(_ => {
                    this.$apollo.mutate({
                        mutation: require('../../../../../graphql/simulations/logements-familiaux/patrimoines/removePatrimoine.gql'),
                        variables: {
                            patrimoineUUID: row.id,
                            simulationId: this.simulationID
                        }
                    }).then(() => {
                        updateData(this.query, this.simulationID).then(() => {
                            this.$message({
                                showClose: true,
                                message: 'Le patrimoine a bien été supprimé.',
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
                if (valid) {
                    this.$apollo.mutate({
                        mutation: require('../../../../../graphql/simulations/logements-familiaux/patrimoines/savePatrimoine.gql'),
                        variables: {
                            patrimoine: {
                                uuid: this.form.id,
                                simulationId: this.simulationID,
                                nGroupe: this.form.nGroupe,
                                nSousGroupe: this.form.nSousGroupe,
                                nomGroupe: this.form.nomGroupe,
                                informations: this.form.informations,
                                conventionne: this.form.conventionne,
                                surfaceQuittancee: this.form.surfaceQuittancee,
                                nombreLogements: this.form.nombreLogements,
                                loyerMensuel: this.form.loyerMensuel,
                                loyerMensuelPlafond: this.form.loyerMensuelPlafond | 0,
                                secteurFinancier: this.form.secteurFinancier,
                                zoneGeographique: this.form.zoneGeographique,
                                natureOperation: this.form.natureOperation ,
                                typeHabitat: this.form.typeHabitat,
                                rehabilite: this.form.rehabilite,
                                anneeMes: this.form.anneeMes | 0,
                                profilsEvolutionLoyersId: this.form.profilsEvolutionLoyers
                            }
                        }
                    }).then(() => {
                        this.submitted = false;
                        this.dialogVisible = false;
                        updateData(this.query, this.simulationID).then(() => {
                            this.$message({
                                showClose: true,
                                message: 'Le patrimoine a bien été enregistré.',
                                type: "success",
                            });
                        });
                    }).catch((error) =>{
                        this.submitted = false;
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
                this.form = {...this.patrimoines[this.selectedIndex]};
            }
        },
        next() {
            if (this.selectedIndex < (this.patrimoines.length - 1)) {
                this.selectedIndex++;
                this.form = {...this.patrimoines[this.selectedIndex]};
            }
        },
        exportPatrimoines() {
           window.location.href = "/export-patrimoines/" + this.simulationID;
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
        formatInput(type) {
            this.form[type] = mathInput(this.form[type]);
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
    }
}
</script>

<style scoped>
    .patrimoines .el-button--small {
        font-weight: 500;
        padding: 0 15px;
        height: 40px;
        font-size: 14px;
        border-radius: 4px;
        text-align: center;
        height: 40px;
    }
    .patrimoines .el-table th > .cell {
        white-space: initial;
    }
    .patrimoines .form-slider i {
        font-size: 25px;
        margin-left: 50px;
        cursor: pointer;
    }
    .patrimoines .el-form-item__label {
        text-align: left;
        margin-top: 7px;
    }
    .patrimoines .el-tooltip.el-icon-info {
        font-size: 25px;
        color: #2491eb;
        vertical-align: middle;
        position: absolute;
        top: 8px;
        right: -30px;
    }
</style>
