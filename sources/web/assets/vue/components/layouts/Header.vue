<template>
	<header class="border-bottom">
		<a href="/" class="logo-wrap">
			<img class="logo" :src="require('../../assets/logo-blue.svg')">
		</a>

		<div class="r-side">
			<ChoixEntite v-if="visibleChoixEntite"></ChoixEntite>
			<ChoixAdmin></ChoixAdmin>

			<Compte></Compte>

			<Notifications class="border-left px-3"></Notifications>

			<el-button class="logout-icon border-left text-primary px-3" @click="performLogout()">
				<i class="material-icons">power_settings_new</i>
			</el-button>
		</div>
	</header>
</template>
<script>
	import ChoixAdmin from "../header/ChoixAdmin";
	import ChoixEntite from "../header/ChoixEntite";
	import Notifications from "../header/Notifications";
	import Compte from "../header/Compte";

	export default {
		name: "Header",
		components: {
			ChoixAdmin,
			ChoixEntite,
			Notifications,
			Compte
		},
		computed: {
			estAdministrateurCentral() {
				return this.$store.getters["security/estAdministrateurCentral"];
			},
			estAdministrateurSimulation() {
				return this.$store.getters["security/estAdministrateurSimulation"];
			},
			visibleChoixEntite() {
				return !this.$store.getters["choixEntite/getDisable"];
			}
		},
		methods: {
			performLogout() {
				window.location.href = "/api/security/logout";
				this.$loading({ lock: true });
			}
		}
	};
</script>
