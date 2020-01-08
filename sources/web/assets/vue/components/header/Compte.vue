<template>
    <div class="user-tab border-left px-3">
        <div class="user-tab-info d-flex align-items-center">
            <span class="d-block font-small text-gray" v-text="username" @click="showModal"></span>
        </div>
        <el-dialog
            title="Modification des informations utilisateur"
            :visible.sync="dialogVisible"
            width="45%">
            <el-form :model="utilisateur" :rules="formRules" label-width="120px" label-position="left" ref="userForm" class="p-3">
                <el-form-item label="Nom" prop="nom">
                    <el-input type="text" v-model="utilisateur.nom" autocomplete="off"></el-input>
                </el-form-item>
                <el-form-item label="Prénom" prop="prenom">
                    <el-input type="text" v-model="utilisateur.prenom" autocomplete="off"></el-input>
                </el-form-item>
                <el-form-item label="E-mail" prop="email">
                    <el-input type="email" v-model="utilisateur.email" autocomplete="off"></el-input>
                </el-form-item>
                <el-form-item label="Fonction" prop="fonction">
                    <el-input type="text" v-model="utilisateur.fonction" autocomplete="off"></el-input>
                </el-form-item>
                <el-form-item label="Téléphone" prop="telephone">
                    <el-input type="string" v-model="utilisateur.telephone" autocomplete="off"></el-input>
                </el-form-item>
            </el-form>
            <div slot="footer" class="dialog-footer">
                <el-button type="primary" @click="dialogVisible = false">Retour</el-button>
                <el-button type="success" @click="save">Valider</el-button>
            </div>
        </el-dialog>
    </div>
</template>

<script>
import customValidator from '../../utils/validation-rules';

export default {
    name:"Compte",
    data () {
        return {
            dialogVisible: false,
            utilisateur: {},
            formRules: {
                nom: customValidator.getRule('required'),
                prenom: customValidator.getRule('required'),
                email: [
                    customValidator.getRule('required'),
                    customValidator.getRule('email')
                ]
            }
        }
    },
    computed: {
        username () {
            return `${this.$store.getters['security/prenom']} ${this.$store.getters['security/nom'].toUpperCase()}`
        }
    },
    methods: {
        initForm() {
            this.utilisateur = {
                id: this.$store.getters['security/id'],
                nom: this.$store.getters['security/nom'],
                prenom: this.$store.getters['security/prenom'],
                email: this.$store.getters['security/email'],
                fonction: this.$store.getters['security/fonction'],
                telephone: this.$store.getters['security/telephone']
            };
        },
        showModal() {
            this.dialogVisible = true;
            this.initForm();
        },
        save() {
            this.$refs['userForm'].validate((valid) => {
                if (valid) {
                    const loading = this.$loading({ lock: true });
                    this.$apollo
                        .mutate({
                            mutation: require("../../graphql/administration/utilisateurs/saveUtilisateur.gql"),
                            variables: {
                                utilisateurID: this.utilisateur.id,
                                nom: this.utilisateur.nom,
                                prenom: this.utilisateur.prenom,
                                fonction: this.utilisateur.fonction,
                                email: this.utilisateur.email,
                                telephone: this.utilisateur.telephone,
                                estActive: true
                            }
                        })
                        .then((res) => {
                            const payload = res.data.saveUtilisateur;
                            this.$store.dispatch('security/updateUtilisateur', payload);
                            this.dialogVisible = false;
                            loading.close();
                            this.$message({
                                showClose: true,
                                message: 'Les informations utilisateur sont mises à jour!',
                                type: "success",
                                duration: 10000
                            });
                        })
                        .catch(error => {
                            loading.close();
                            this.$message({
                                showClose: true,
                                message: error.networkError.result,
                                type: "error",
                                duration: 10000
                            });
                        });
                } else {
                    return false;
                }
            });
        }
    }
}
</script>

<style lang="scss">
    .user-tab {
        .user-tab-info {
            .text-gray {
                cursor: pointer;
                &:hover {
                    color: #2491eb !important;
                }
            }
        } 
    }
</style>
