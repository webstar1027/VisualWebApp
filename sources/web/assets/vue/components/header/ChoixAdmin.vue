<template>
	<div v-if="enabled" class="admin-trigger pr-5">
		<span class="font-smaller pr-2 text-gray">Mode admin</span>
		<el-switch @change="switchAdminMode" v-model="isAdminMode"></el-switch>
	</div>
</template>
<script>
export default {
	name: "ChoixAdmin",
	data() {
		return {
			isAdminMode: false,
			enabled: false
		};
	},
	created() {
		const userRoles = this.$store.getters["security/roles"];
		if (this.isSupeAdmin) {
			this.enabled = true;
		} else if (_.isNil(userRoles)) {
			this.enabled = false;
		} else {
			const roles = Object.values(userRoles);
			roles.forEach(role => {
				if (role.includes('Référent entité') || role.includes('Référent ensemble')) {
					this.enabled = true;
				}
			})
		}
	},
	computed:{
		isUserInBackOffice(){
			return this.$route.path.split('/').includes('administration') || this.$route.path.split('/').includes('gestion');
		},
		isSupeAdmin() {
			return this.$store.getters["security/estAdministrateurCentral"] || this.$store.getters["security/estAdministrateurSimulation"];
		}
	},
	methods: {
		switchAdminMode() {
			setTimeout(() => {
				if (this.isAdminMode === true) {
					this.$router.push({path: '/administration'});
				} else if (this.isAdminMode === false) {
					this.$router.push({path: '/'});
				}
			}, 200);
		}
	},
	mounted(){
		this.isAdminMode = this.isUserInBackOffice;
	}

};
</script>
