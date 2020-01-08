<template>
    <div class="auth-wrap d-flex flex-column h-100 align-items-center bg-primary text-white password-confirmation">
        <div class="d-flex justify-content-center">
            <p>Confirmation du mot de passe</p>
        </div>
        <el-row class="px-3 d-flex w-100 justify-content-center">
            <el-col :md="5">
                <el-form :model="form" :rules="formRules" label-width="0" ref="form">
                    <el-form-item  prop="password" class="mb-4">
                        <el-input class="block" type="password" v-model="form.password" placeholder="Mot de passe"></el-input>
                    </el-form-item>
                    <el-form-item prop="confirmPassword" class="mb-4">
                        <el-input class="block" type="password" v-model="form.confirmPassword" placeholder="Confirmer votre mot de passe"></el-input>
                    </el-form-item>
                    <el-form-item>
                        <el-button class="block border" @click="submitForm('form')">Confirmer</el-button>
                    </el-form-item>
                    <div v-if="form.password !== ''" class="error-messages text-danger">
                        <p v-if="!hasMinLength || !hasUppercase || !hasSpecial || !hasNumber">Let Mot de passe doit contenir:</p>
                        <p v-if="!hasMinLength" class="ml-2">-LE mot passe doit contenir au moins 8 caractères</p>
                        <p v-if="!hasUppercase" class="ml-2">-Au moins une majuscule</p>
                        <p v-if="!hasSpecial" class="ml-2">-Au moins un caractères spécial'</p>
                        <p v-if="!hasNumber" class="ml-2">-Un moin un chiffre</p>
                    </div>
                    <div v-if="hasMinLength && hasUppercase && hasSpecial && hasNumber && !isMatched" class="error-messages text-center text-danger">
                        <p>Let Mot de passe est invalide!</p>
                    </div>
                </el-form>
            </el-col>
        </el-row>
    </div>
</template>

<script>
export default {
    name: "PasswordConfirmation",
    data() {
        var validatePass = (rule, value, callback) => {
            if (!value) {
                callback(new Error('Please input the password'));
            } else {
                this.checkPassword(value);
                if (!this.hasMinLength || !this.hasUppercase || !this.hasSpecial || !this.hasNumber) {
                    callback(new Error(''));
                }
                callback();
            }
        };
        var validateConfirmPass = (rule, value, callback) => {
            if (value === '') {
                this.isMatched = false;
                callback(new Error('Please input the password again'));
            } else if (value !== this.form.password) {
                this.isMatched = false;
                callback(new Error('Let Mot de passe est invalide!'));
            } else {
                this.isMatched = true;
                callback();
            }
        };
        return {
            token: null,
            form: {
                password: '',
                confirmPassword: '',
            },
            hasMinLength: false,
            hasUppercase: false,
            hasSpecial: false,
            hasNumber: false,
            isMatched: false,
            formRules: {
                password: [
                    { validator: validatePass, trigger: 'change' }
                ],
                confirmPassword: [
                    { validator: validateConfirmPass, trigger: 'change' }
                ]
            }
        }
    },
    created() {
        const token = _.isNil(this.$route.params.token) ? null : this.$route.params.token;
        if (_.isNil(token)) {
            return;
        }
        this.token = token;
    },
    methods: {
        submitForm(formName) {
            this.$refs[formName].validate(valid => {
                if (valid) {
                    let payload = {
                        token: this.token,
                        password: this.form.password
                    };
                    this.$store.dispatch("security/confirmPassword", payload).then(() => {
                        if (!this.$store.getters["security/hasError"]) {
                            this.$router.push({ path: '/connexion' });
                        }
                    });
                } else {
                    return valid;
                }
            });
        },
        checkPassword(value) {
            this.hasMinLength = value.length >= 8;
            this.hasUppercase = /[A-Z]/.test(value);
            this.hasSpecial = /[!@#\$%\^\&*\)\(+=._-]/.test(value);
            this.hasNumber = /\d/.test(value);
        }
    }
}
</script>

<style>
    .password-confirmation .el-form-item__error {
        display: none;
    }
    .password-confirmation .error-messages p{
        margin-bottom: 0;
    }
</style>
