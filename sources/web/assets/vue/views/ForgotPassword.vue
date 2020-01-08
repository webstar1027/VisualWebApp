<template>
    <div class="auth-wrap d-flex flex-column h-100 align-items-center bg-primary text-white forgot-password">
        <el-row class="px-3 d-flex w-100 justify-content-center">
            <el-col :md="5">
                <el-form :model="form" label-width="120px" label-position="top" ref="form">
                    <div class="mb-4">
                        <el-form-item
                            label="E-mail"
                            prop="login"
                            :rules="[{ required: true, message: 'Merci de renseigner une adresse e-mail', trigger: 'blur' },{ type: 'email', message: 'Merci de renseigner une adresse e-mail valide', trigger: ['blur']}]"
                        >
                            <el-input class="block" type="email" v-model="form.login" autocomplete="on"></el-input>
                        </el-form-item>
                    </div>
                    <div>
                        <el-form-item>
                            <el-button class="block border" @click="submitForm('form')">Envoyer</el-button>
                        </el-form-item>
                        <el-form-item>
                            <el-button class="block border" @click="goBack()">Retour</el-button>
                        </el-form-item>
                    </div>
                    <el-alert
                        v-if="success"
                        title="Nous avons envoyé l'e-mail de confirmation. S'il vous plaît vérifier votre boîte de réception."
                        type="success"
                        effect="dark">
                    </el-alert>
                    <el-alert
                        v-if="error"
                        :title="errorMessage"
                        type="error"
                        effect="dark">
                    </el-alert>
                </el-form>
            </el-col>
        </el-row>
    </div>
</template>

<script>
import SecurityAPI from './../api/security';

export default {
    name: "ForgotPassword",
    data() {
        return {
            form: {
                login: "",
            },
            error: false,
            errorMessage: '',
            success: false,
        };
    },
    methods: {
        submitForm(formName) {
            this.$refs[formName].validate(valid => {
                if (valid) {
                    this.error = false;
                    this.errorMessage = '';
                    this.success = false;
                    let payload = this.$data.form;
                    SecurityAPI.resetPassword(payload.login)
                        .then(res => {
                            if (res.data.status) {
                                this.success = true;
                                setTimeout(() => {
                                    this.$router.push("Connexion");
                                }, 2000);
                            } else {
                                this.error = true;
                                this.errorMessage = res.data.message;
                            }
                        }).catch(err => {
                            this.error = true;
                            this.errorMessage = err.message;
                        });
                }
                return valid;
            });
        },
        goBack(){
            this.$router.push("Connexion");
        }
    }
};
</script>

<style>
    .forgot-password .el-form-item__error {
        color: white;
        padding-top: 0px;
    }
</style>
