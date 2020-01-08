<template>
	<div class="sidebar sidebar-admin bo">
		<div class="bo-menu">
			<div class="bo-menu-item-wrap">
				<el-button
					class="bo-menu-item"
					:class="{'active':isRoute('utilisateurs')}"
					@click="navigateToMenu('utilisateurs')"
				>
					<md-icon class>people</md-icon>
					<span class="bo-menu-item-title">Utilisateurs</span>
				</el-button>
			</div>
			<div class="bo-menu-item-wrap">
				<el-button
					class="bo-menu-item"
					:class="{'active':isRoute('entites')}"
					@click="navigateToMenu('entites')"
				>
					<md-icon class>fiber_manual_record</md-icon>
					<span class="bo-menu-item-title">Entités</span>
				</el-button>
			</div>
			<div class="bo-menu-item-wrap">
				<el-button
					class="bo-menu-item"
					:class="{'active':isRoute('ensembles')}"
					@click="navigateToMenu('ensembles')"
				>
					<md-icon class>fiber_smart_record</md-icon>
					<span class="bo-menu-item-title">Ensembles</span>
				</el-button>
			</div>
			<div v-if="isSuperAdmin" class="bo-menu-item-wrap">
				<el-button
					class="bo-menu-item"
					:class="{'active':isRoute('roles')}"
					@click="navigateToMenu('roles')"
				>
					<md-icon class>verified_user</md-icon>
					<span class="bo-menu-item-title">Rôles</span>
				</el-button>
			</div>
			<div v-if="isSuperAdmin" class="bo-menu-item-wrap">
				<el-button
					class="bo-menu-item"
					:class="{'active':isRoute('fusion-entites')}"
					@click="navigateToMenu('fusion-entites')"
				>
					<md-icon class>group_work</md-icon>
					<span class="bo-menu-item-title">Fusion d’entités</span>
				</el-button>
			</div>
		</div>
	</div>
</template>
<script>
	export default {
		name: "BackSidebar",
		computed: {
			routeArr() {
				return this.$route.path.split("/");
			},
			isSuperAdmin() {
				return this.$store.getters["security/estAdministrateurCentral"];
			}
		},
		methods: {
			navigateToMenu(rubrique) {
				this.$router.push("/gestion/" + rubrique);
			},
			isRoute(route) {
				return this.routeArr.includes(route);
			}
		}
	};
</script>
