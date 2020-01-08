<template>
  <back-wrapper>
      <div>
        <el-breadcrumb separator-class="el-icon-arrow-right">
          <el-breadcrumb-item :to="{ path: '/administration' }">Administration</el-breadcrumb-item>
          <el-breadcrumb-item :to="{ path: '/gestion/entites' }">Gestion des entités</el-breadcrumb-item>
          <el-breadcrumb-item v-if="isCreation">Création d'une entité</el-breadcrumb-item>
          <el-breadcrumb-item v-else>Modification d'une entité</el-breadcrumb-item>
        </el-breadcrumb>
      </div>
    <div class="container">
      <div class="admin-content-wrap">
        <h1 class="admin-content-title" v-if="!isCreation">{{ nomEntiteActuel }}</h1>

        <!-- START Entite form -->
        <el-form
          :model="entite"
          label-width="150px"
          ref="entiteForm"
          :rules="rules"
          class="block-form"
        >
          <div class="row">
            <div class="col-sm-12 col-md-6 col-lg-4">
              <el-form-item label="SIREN" prop="siren">
                <el-input
                  type="text"
                  v-model="entite.siren"
                  autocomplete="off"
                  v-mask="'### ### ###'"
                ></el-input>
              </el-form-item>
            </div>
            <div class="col-sm-12 col-md-6 col-lg-4">
              <el-form-item label="Code de l'entité" prop="code">
                <el-input type="text" v-model="entite.code" autocomplete="off"></el-input>
              </el-form-item>
            </div>
            <div class="col-sm-12 col-md-6 col-lg-4">
              <el-form-item label="Nom de l'entité" prop="nom">
                <el-input type="text" v-model="entite.nom" autocomplete="off"></el-input>
              </el-form-item>
            </div>
            <div class="col-sm-12 col-md-6 col-lg-4">
              <el-form-item label="Type de l'entité" prop="type">
                <el-select v-model="entite.type" placeholder="Type de l'entité">
                  <el-option
                    v-for="item in typeOptions"
                    :key="item.value"
                    :label="item.label"
                    :value="item.value"
                  ></el-option>
                </el-select>
              </el-form-item>
            </div>
            <div v-if="enableOrganisme" class="col-sm-12 col-md-6 col-lg-4">
              <el-form-item label="Type de l'organisme" prop="typeOrganisme">
                <el-select v-model="entite.typeOrganisme" placeholder="Type de l'entité">
                  <el-option
                    v-for="item in typeOrganismeOptions"
                    :key="item.value"
                    :label="item.label"
                    :value="item.value"
                  ></el-option>
                </el-select>
              </el-form-item>
            </div>
            <div v-if="enableOrganisme" class="col-sm-12 col-md-6 col-lg-4">
              <el-form-item label="Code de l'organisme" prop="codeOrganisme">
                <el-input type="text" v-model="entite.codeOrganisme" autocomplete="off"></el-input>
              </el-form-item>
            </div>
            <div class="col-sm-12 col-md-6 col-lg-4">
              <el-form-item label="Référents de l’entité" prop="referents">
                <el-select
                  v-model="entite.referents"
                  multiple
                  filterable
                  placeholder="Type de l'utilisateur"
                  @remove-tag="removeTag"
                >
                  <el-option
                    v-for="item in utilisateurs"
                    :key="item.id"
                    :label="`${item.prenom} ${item.nom}`"
                    :value="item.id"
                  ></el-option>
                </el-select>
              </el-form-item>
            </div>
            <template v-if="!isCreation">
              <div class="col-sm-12 col-md-6 col-lg-4">
                <el-form-item label="Date de création" prop="dateCreation">
                  <el-input type="text" :value="dateCreation" :disabled="true"></el-input>
                </el-form-item>
              </div>
              <div class="col-sm-12 col-md-6 col-lg-4">
                <el-form-item label="Date de modification" prop="dateModification">
                  <el-input type="text" :value="dateModification" :disabled="true"></el-input>
                </el-form-item>
              </div>
              <div class="col-sm-12 col-md-6 col-lg-4">
                <el-form-item label="Créé par" prop="creePar">
                  <el-input type="text" :value="creePar" :disabled="true"></el-input>
                </el-form-item>
              </div>
              <div class="col-sm-12 col-md-6 col-lg-4">
                <el-form-item label="Modifié par" prop="modifierPar">
                  <el-input type="text" :value="modifierPar" :disabled="true"></el-input>
                </el-form-item>
              </div>
            </template>
          </div>
            <div class="d-flex my-4">
              <template v-if="isCreation">
				  <div class="ml-auto">

                <el-button plein @click="goBack()">Retour</el-button>
                <el-button type="primary" @click="submitForm('entiteForm')">Créer une entité</el-button>
				  </div>
              </template>
              <template v-else>
                  <el-button
                    v-if="isReferentEntite"
                    @click="inviteUtilisateursIntoThisEntite"
                    type="warning"
                  >Inviter des utilisateurs</el-button>
                  <el-button
                    type="warning"
                    @click="$router.push('/gestion/ensembles?entite=' + entite.nom)"
                  >Liste des ensembles</el-button>

				  <div class="ml-auto">

                
                  <el-button plein @click="goBack()">Retour</el-button>
                  <el-button
                    v-if="entite.estActivee"
                    type="danger"
                    @click="disableEntite('entiteForm')"
                  >Supprimer l'entité</el-button>
                  <el-button
                    v-else
                    type="success"
                    @click="enableEntite('entiteForm')"
                  >Réactiver l'entité</el-button>
                  <el-button
                    type="success"
                    class="btn btn-success"
                    @click="submitForm('entiteForm')"
                  >Valider</el-button>
				  </div>
              </template>
            </div>
        </el-form>
        <!-- END Entite form -->
      </div>
    </div>
  </back-wrapper>
</template>

<script>
import _ from "lodash";
import { mask } from "vue-the-mask";
import customValidator from "../../utils/validation-rules";

export default {
	name: "entite",
	directives: { mask },
	data() {
		return {
			entite: {
				id: null,
				nom: null,
				siren: null,
				code: null,
				type: null,
				codeOrganisme: null,
				typeOrganisme: null,
				dateCreation: null,
				dateModification: null,
				creePar: {
					nom: null,
					prenom: null,
					email: null
				},
				modifiePar: {
					nom: null,
					prenom: null,
					email: null
				},
				estActivee: true,
				referents: []
			},
			typeOptions: [
				{ value: "Organisme" },
				{ value: "Partenaire" },
				{ value: "Holding" },
				{ value: "Confédération" }
			],
			typeOrganismeOptions: [
				{ value: "ESH" },
				{ value: "OPH" },
				{ value: "Coopérative" },
				{ value: "SC" },
				{ value: "SVHLM" },
				{ value: "SEM" },
				{ value: "MOI" },
				{ value: "SACICAP" },
				{ value: "Autres" }
			],
			deleteDialogVisible: false,
			activeOrInactive: false,
			isReferentEntite: false,
            rules: {
			    siren: customValidator.getRule('siren'),
                code: customValidator.getRule('codeEntite'),
                nom: [
                    customValidator.getRule('requiredNoWhitespaces'),
                    customValidator.getRule('maxVarchar'),
                ],
				typeOrganisme : customValidator.getRule('required', 'change'),
                type: customValidator.getRule('required', 'change'),
                codeOrganisme: customValidator.getRule('maxVarchar'),
                referents: customValidator.getRule('required')
            },
            nomEntiteActuel: null,
            utilisateurs: [],
		};
	},
	created() {
		this.getUtilisateurs();
		let entiteID = _.isNil(this.$route.params.id)
			? null
			: this.$route.params.id;
		if (_.isNil(entiteID)) {
			return;
		}
		const loading = this.$loading({ lock: true });
		this.$apollo
			.query({
				query: require("../../graphql/administration/entites/referentEntite.gql"),
				variables: {
					entiteID: entiteID
				}
			})
			.then(response => {
				this.isReferentEntite = response.data.referentEntite;
			});
		this.$apollo
			.query({
				query: require("../../graphql/administration/entites/entite.gql"),
				variables: {
					entiteID: entiteID
				}
			})
			.then(response => {
				this.entite = {
					id: response.data.entite.id,
					nom: response.data.entite.nom,
					siren: response.data.entite.siren,
					code: response.data.entite.code,
					type: response.data.entite.type,
					codeOrganisme: response.data.entite.codeOrganisme,
					typeOrganisme: response.data.entite.typeOrganisme,
					dateCreation: response.data.entite.dateCreation,
					dateModification: response.data.entite.dateModification,
					creePar: response.data.entite.creePar,
					modifiePar: response.data.entite.modifiePar,
					estActivee: response.data.entite.estActivee,
					referents: response.data.entite.utilisateursRolesEntites.items.filter(item => item.role.nom === 'Référent entité').map(item => item.utilisateur.id)
				}
				this.nomEntiteActuel = response.data.entite.nom;
			})
			.finally(() => {
				loading.close();
			});
	},
	computed: {
		isCreation() {
			return _.isNil(this.$route.params.id);
		},
		creePar() {
			if (_.isNil(this.entite.creePar)) {
				return null;
			}
			return this.entite.creePar.prenom + " " + this.entite.creePar.nom;
		},
		modifierPar() {
			if (_.isNil(this.entite.modifiePar)) {
				return null;
			}
			return this.entite.modifiePar.prenom + " " + this.entite.modifiePar.nom;
		},
		dateCreation() {
			if (_.isNil(this.entite.dateCreation)) {
				return null;
			}
			let date = new Date(this.entite.dateCreation);
			return date.toLocaleDateString();
		},
		dateModification() {
			if (_.isNil(this.entite.dateModification)) {
				return null;
			}
			let date = new Date(this.entite.dateModification);
			return date.toLocaleDateString();
		},
		enableOrganisme() {
			return this.entite.type == "Organisme" ? true: false;
		}
	},
	methods: {
		getUtilisateurs() {
			this.$apollo
				.query({
					query: require("../../graphql/administration/utilisateurs/allUtilisateurs.gql")
				})
				.then(response => {
					this.utilisateurs = response.data.allUtilisateurs.items;
				});
		},
		inviteUtilisateursIntoThisEntite() {
			this.$router.push(
				"/gestion/entite/" + this.entite.id + "/invitation"
			);
		},
		submitForm(formName, isDelete = false) {
			let scope = this;
			this.$refs[formName].validate(valid => {
				if (valid) {
					const loading = this.$loading({ lock: true });
					if (isDelete) {
						this.entite.estActivee = false;
						this.activeOrInactive = true;
					}
					this.$apollo
						.mutate({
							mutation: require("../../graphql/administration/entites/saveEntite.gql"),
							variables: {
								entiteID: this.entite.id,
								nom: this.entite.nom,
								siren: this.entite.siren,
								type: this.entite.type,
								code: this.entite.code,
								typeOrganisme: this.entite.typeOrganisme,
								codeOrganisme: this.entite.codeOrganisme,
								estActivee: this.entite.estActivee,
								referents: this.entite.referents
							}
						})
						.then(() => {
							this.$router.push("/gestion/entites");
							this.$router.go(0);
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
		disableEntite(formName) {
			if (this.entite.referents.length > 1) {
				this.$confirm("Êtes-vous sûr de vouloir supprimer cette entité ?")
			        .then(_ => {
						this.submitForm(formName, true);
			        });
			} else {
				this.$message({
					showClose: true,
					message: "Cette entité comporte un seul et dernier référent",
					type: "error",
					duration: 10000
				});
			}
		},
		enableEntite(formName) {
			this.entite.estActivee = true;
			this.activeOrInactive = true;
			this.submitForm(formName);
		},
		goBack(){
			this.$router.push("/gestion/entites");
		},
		removeTag(tag) {
			if (this.entite.referents.length == 0) {
				this.entite.referents = [tag];
			}
		}
	}
};
</script>
