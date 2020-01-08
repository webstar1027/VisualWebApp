<template>
	<back-wrapper>
	<div>
		<div>
			<el-breadcrumb separator-class="el-icon-arrow-right">
				<el-breadcrumb-item :to="{ path: '/administration' }">Administration</el-breadcrumb-item>
				<el-breadcrumb-item :to="{ path: '/gestion/roles' }">Liste des rôles</el-breadcrumb-item>
				<el-breadcrumb-item v-if="isCreation">Création d'un rôle</el-breadcrumb-item>
				<el-breadcrumb-item v-else>Modification d'un rôle</el-breadcrumb-item>
			</el-breadcrumb>
		</div>
		<div class="container">
		<div class="admin-content-wrap">

			<h2 v-if="!isCreation">{{ nomRoleActuel }}</h2>

			<!-- START Role form -->

			<el-form :model="role" label-width="120px" ref="roleForm" :rules="rules">
				<div class="row">
					<div class="col-sm-12 col-md-6 col-lg-6">
				<el-form-item label="Nom" prop="nom">
					<el-input type="text" autocomplete="off" v-model="role.nom"></el-input>
				</el-form-item>
					</div>
					<div class="col-sm-12 col-md-6 col-lg-6">
				<el-form-item label="Description" prop="description">
					<el-input type="text" v-model="role.description"></el-input>
				</el-form-item>
					</div>
				</div>
				<el-form-item label="Droits">
					<el-tabs v-model="activeCategorie">
						<el-tab-pane
								v-for="droit in groupedDroits"
								:key="droit.category"
								:label="droit.category"
								:name="droit.category"
						>
							<el-checkbox-group v-model="role.droits">
								<el-checkbox v-for="item in droit.data" :key="item.id" :label="item.id">{{item.libelle}}</el-checkbox>
							</el-checkbox-group>
						</el-tab-pane>
					</el-tabs>
				</el-form-item>
				<div class="d-flex my-4">
					<div class="ml-auto">
				<el-form-item v-if="isCreation">
					<el-button plein @click="goBack()">Retour</el-button>
					<el-button type="primary" @click="submitForm('roleForm')">Ajouter un rôle</el-button>
				</el-form-item>
				<el-form-item v-else>
					<el-button plein @click="goBack()">Retour</el-button>
					<el-button type="primary" @click="submitForm('roleForm')">Mettre à jour le rôle</el-button>
					<el-button v-if="role.estVisible" type="danger" @click="deleteRole('roleForm')">Supprimer</el-button>
					<el-button v-else type="success" @click="activateRole('roleForm')">Activer</el-button>
				</el-form-item>
					</div>
				</div>
			</el-form>
			<!-- END Role form -->
		</div>
	</div>
	</div>
	</back-wrapper>
</template>

<script>
import _ from "lodash";
import customValidator from '../../utils/validation-rules'

export default {
	name: "role",
	data() {
		return {
			droitsOptions: [],
			role: {
				id: null,
				nom: null,
				description: null,
				droits: []
			},
			activeCategorie: "Entités",
            rules: {
                nom: [
                    customValidator.getRule('requiredNoWhitespaces'),
                    customValidator.getRule('maxVarchar'),
                ],
                description: customValidator.getRule('maxLongtext')
            },
            nomRoleActuel: null
		};
	},
	created() {
		let roleID = _.isNil(this.$route.params.id) ? null : this.$route.params.id;
		this.loadDroits();
		if (_.isNil(roleID)) {
			return;
		}
		const loading = this.$loading({ lock: true });
		this.$apollo
			.query({
				query: require("../../graphql/administration/roles/role.gql"),
				fetchPolicy: 'no-cache',
				variables: {
					roleID: roleID
				}
			})
			.then(response => {
				this.role = response.data.role;
				this.nomRoleActuel = response.data.role.nom;
				this.role.droits = Array.from(
					response.data.role.droits,
					data => data.id
				);
			})
			.finally(() => {
				loading.close();
			});
	},
	computed: {
		isCreation() {
			return _.isNil(this.$route.params.id);
		},
		groupedDroits() {
			let droitsObject = [],
				groupedByCategories = _.groupBy(this.droitsOptions, "categorie");

			for (let droit in groupedByCategories) {
				droitsObject.push({
					category: droit,
					data: groupedByCategories[droit]
				});
			}
			return droitsObject;
		}
	},
	methods: {
		submitForm(formName) {
			let scope = this;
			this.$refs[formName].validate(valid => {
				if (valid) {
					const loading = this.$loading({ lock: true });
					this.$apollo
						.mutate({
							mutation: require("../../graphql/administration/roles/saveRole.gql"),
							variables: {
								roleID: this.role.id,
								nom: this.role.nom,
								description: this.role.description,
								droits: this.role.droits
							}
						})
						.then(() => {
							this.$router.push("/gestion/roles");
						})
						.catch(error => {
							this.$message({
								showClose: true,
								message: error.networkError.result,
								type: "error",
								duration: 10000
							});
							if (scope.activeOrInactive) {
								scope.entite.estActivee = !scope.entite.estActivee;
								scope.activeOrInactive = false;
							}
						})
						.finally(() => {
							loading.close();
						});
				}
				return valid;
			});
		},
		deleteRole() {
			this.$confirm("Are you sure to delete the role?")
	        .then(_ => {
	         	 this.$apollo
					.mutate({
						mutation: require("../../graphql/administration/roles/deleteRole.gql"),
						variables: {
							roleID: this.role.id,
						}
					})
					.then(() => {
						this.$router.push("/gestion/roles");
					})
	        });
		},
		activateRole() {
			this.$confirm("Are you sure to activate the role?")
	        .then(_ => {
	         	 this.$apollo
					.mutate({
						mutation: require("../../graphql/administration/roles/activateRole.gql"),
						variables: {
							roleID: this.role.id,
						}
					})
					.then(() => {
						this.$router.push("/gestion/roles");
					})
	        });
		},
		loadDroits() {
			this.$apollo
				.query({
					query: require("../../graphql/administration/droits/droits.gql")
				})
				.then(response => {
					this.droitsOptions = response.data.droits;
				});
		},
		goBack(){
			this.$router.push("/gestion/roles");
		}
	}
};
</script>
