<template>
	<div class="auth-wrap d-flex flex-column h-100 align-items-center bg-primary text-white">
		<div class="d-flex justify-content-center mb-5">
			<img src="../assets/logo-white.svg"/>
		</div>
		<el-row class="px-3 d-flex w-100 justify-content-center">
			<el-col :md="5">
				<el-alert
					v-if="hasError"
					class="mb-3"
					title="Login / Mot de passe incorrect"
					type="error"
					show-icon
					:closable="false">
				</el-alert>
				<el-form :model="credentials" label-width="120px" label-position="top" ref="loginForm">
					<div class="mb-4">

						<el-form-item
							label="E-mail"
							prop="login"
							:rules="[{ required: true, message: 'Merci de renseigner une adresse e-mail', trigger: 'blur' },{ type: 'email', message: 'Merci de renseigner une adresse e-mail valide', trigger: ['blur']}]"
						>
							<el-input class="block" type="email" v-model="credentials.login" autocomplete="on" placeholder="xxx@xxx.com"></el-input>
						</el-form-item>

					</div>
					<div class="mb-4">
						<el-form-item
							label="Mot de passe"
							prop="password"
							:rules="[{ required: true, message: 'Merci de renseigner un mot de passe', trigger: 'blur' }]"
						>
							<el-input class="block" type="password" v-model="credentials.password" autocomplete="on"></el-input>
						</el-form-item>

					</div>
					<div>
						<el-form-item>
							<el-button class="block border" @click="submitForm('loginForm')">Connexion</el-button>
						</el-form-item>
					</div>
				</el-form>
				<div class="mt-4 text-center">
					<router-link class="d-block font-small" to="/forgot-password">Mot de passe oublié</router-link>
				</div>
			</el-col>
		</el-row>
		<el-row>
			<div class="mt-5 text-center">V0.6.0</div>
		</el-row>
	</div>
</template>

<script>
export default {
	name: "connexion",
	data() {
		return {
			credentials: {
				login: "",
				password: ""
			}
		};
	},
	created() {
		let redirect = this.$route.query.redirect;
		if (this.$store.getters["security/isAuthenticated"]) {
			if (typeof redirect !== "undefined") {
				this.$router.push({ path: redirect });
			} else {
				this.$router.push({ path: "/" });
			}
		}
		this.onPressEnter();
	},
	computed: {
		hasError() {
			return this.$store.getters["security/hasError"];
		},
		error() {
			return this.$store.getters["security/error"];
		},
		estAdministrateurCentral () {
			return this.$store.getters['security/estAdministrateurCentral'];
		}
	},
	methods: {
		submitForm(formName) {
			this.$refs[formName].validate(valid => {
				if (valid) {
					let payload = this.$data.credentials,
						redirect = this.$route.query.redirect;
					const loading = this.$loading({ lock: true });
					this.$store.dispatch("security/login", payload).then(() => {
						loading.close();
						if (!this.$store.getters["security/hasError"]) {
							if (typeof redirect !== "undefined") {
								this.$router.push({ path: redirect });
							} else {
								if (this.estAdministrateurCentral) {
									this.$router.push({ path: "/administration" });
								} else {
									this.$router.push({ path: "/" });
								}
							}
						}
					});
				}
				return valid;
			});
		},
		onPressEnter() {
			window.addEventListener('keydown', (e) => {
				if (e.keyCode === 13) {
					this.submitForm('loginForm');
				}
			});
		}
	}
};
</script>
