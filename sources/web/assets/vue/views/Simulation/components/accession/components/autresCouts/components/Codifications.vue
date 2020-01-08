<template>
    <div class="autres-activites">
        <ApolloQuery
                :query="require('../../../../../../../graphql/simulations/accessions/autres-couts/codifications/accessionCodifications.gql')"
                :variables="{
                simulationId: simulationID
            }">
            <template slot-scope="{ result:{ loading, error, data }, isLoading, query }">
                <div v-if="error">Une erreur est survenue !</div>
                <el-table
                        v-loading="isLoading"
                        :data="tableData(data, query)"
                        style="width: 100%">
                    <el-table-column sortable column-key="numero" prop="numero" label="N°"></el-table-column>
                    <el-table-column sortable column-key="activite" prop="activite" min-width="200" label="Activité"></el-table-column>
                    <el-table-column fixed="right" width="120" label="Actions">
                        <template slot-scope="scope">
                            <el-button type="primary" :disabled="!isModify" icon="el-icon-edit" circle @click="handleEdit(scope.$index, scope.row, 0)"></el-button>
                            <el-button type="danger" :disabled="!isModify" icon="el-icon-delete" circle @click="handleDelete(scope.$index, scope.row, 0)"></el-button>
                        </template>
                    </el-table-column>
                </el-table>
            </template>
        </ApolloQuery>

        <el-row class="d-flex justify-content-end my-3">
            <el-button type="primary" :disabled="!isModify" @click="showCreateModal">Créer une activité</el-button>
        </el-row>

        <el-dialog
                title="Créer une activité"
                :visible.sync="dialogVisible"
                :close-on-click-modal="false"
                width="70%">

            <el-row v-if="isEdit" class="form-slider text-center mb-5">
                <i class="el-icon-back font-weight-bold" @click="back"></i>
                <i class="el-icon-right font-weight-bold" @click="next"></i>
            </el-row>

            <el-form :model="form" :rules="formRules" label-width="160px" ref="codificationForm">
                <div class="row">
                    <div class="col-sm-6">
                        <el-form-item label="N°" prop="numero">
                            <el-input type="text" v-model="form.numero" disabled></el-input>
                        </el-form-item>
                    </div>
                    <div class="col-sm-6">
                        <el-form-item label="Activité" prop="activite">
                            <el-input type="text" v-model="form.activite" placeholder="Activité"></el-input>
                        </el-form-item>
                    </div>
                </div>
            </el-form>

            <div slot="footer" class="dialog-footer">
                <el-button type="primary" @click="dialogVisible = false">Retour</el-button>
                <el-button type="success" @click="submit('codificationForm')">Valider</el-button>
            </div>
        </el-dialog>
    </div>
</template>

<script>
    import {updateData} from "../../../../../../../utils/helpers";

    export default {
        name: "Codifications",
        props: ['showError'],
        data() {
            var validateActivite = (rule, value, callback) => {
                if (!value) {
                    return callback(new Error("Vous devez saisir un nom d’activité"));
                } else if(this.checkExistActivite(value)) {
                    return callback(new Error("Nom d’activité déjà existant"));
                } else {
                    callback();
                }
            };
            return {
                simulationID: null,
                dialogVisible: false,
                isEdit: false,
                form: null,
                selectedIndex: null,
                codifications: [],
                formRules: {
                    activite: [
                        {required: true, validator: validateActivite, trigger: 'change'}
                    ]
                },
                currentLastNumber: 0,
                query: null
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

            this.initForm();
            this.$apollo
                .query({
                    query: require("../../../../../../../graphql/administration/partagers/checkStatus.gql"),
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
                    numero: null,
                    activite: ''
                };
            },
            tableData(data, query) {
                if (!_.isNil(data)) {
                    this.query = query;
                    this.codifications = data.accessionCodifications.items;
                    let codifications = data.accessionCodifications.items;
                    if (codifications.length > 0) {
                        this.currentLastNumber = parseInt(codifications[codifications.length-1].numero);
                    }
                    return data.accessionCodifications.items;
                } else {
                    return [];
                }
            },
            checkExistActivite(value) {
                let codifications = [...this.codifications];
                if (this.isEdit) {
                    codifications = codifications.filter(item => item.activite !== this.codifications[this.selectedIndex].activite);
                }
                return codifications.some(item => item.activite === value);
            },
            showCreateModal() {
                this.initForm();
                this.form.numero = this.currentLastNumber + 1;
                this.$refs['codificationForm'] && this.$refs['codificationForm'].resetFields();
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
                this.$confirm('Êtes-vous sûr de vouloir supprimer cette codification?')
                    .then(_ => {
                        this.$apollo.mutate({
                            mutation: require('../../../../../../../graphql/simulations/accessions/autres-couts/codifications/removeAccessionCodification.gql'),
                            variables: {
                                codificationUUID: row.id,
                                simulationId: this.simulationID
                            }
                        }).then(() => {
                            updateData(this.query, this.simulationID).then(() => {
                                this.$message({
                                    showClose: true,
                                    message: 'Cette codification a bien été supprimé.',
                                    type: 'success'
                                });
                            })
                        }).catch(error => {
                            updateData(this.query, this.simulationID).then(() => {
                                this.$message({
                                    showClose: true,
                                    message: 'Cette codification est utilisée autre part.',
                                    type: 'error',
                                });
                            });
                        });
                    })
                    .catch(_ => {});
            },
            submit(formName) {
                this.$refs[formName].validate((valid) => {
                    if (valid) {
                        this.$apollo.mutate({
                            mutation: require('../../../../../../../graphql/simulations/accessions/autres-couts/codifications/saveAccessionCodification.gql'),
                            variables: {
                                uuid: this.form.id,
                                simulationId: this.simulationID,
                                activite: this.form.activite
                            }
                        }).then(() => {
                            this.dialogVisible = false;
                            this.isSubmitting = false;
                            updateData(this.query, this.simulationID).then(() => {
                                this.$message({
                                    showClose: true,
                                    message: 'Cette codification a bien été enregistré.',
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
            back() {
                if (this.selectedIndex > 0) {
                    this.selectedIndex--;
                    this.form = this.codifications[this.selectedIndex];
                }
            },
            next() {
                if (this.selectedIndex < (this.codifications.length - 1)) {
                    this.selectedIndex++;
                    this.form = this.codifications[this.selectedIndex];
                }
            }
        }
    }
</script>

<style>
    .autres-activites .form-slider i {
        font-size: 25px;
        margin-left: 50px;
        cursor: pointer;
    }
    .autres-activites .el-form-item__label {
        line-height: 40px;
    }
</style>
