<template>
  <back-wrapper>
    <template v-slot:breadcrumb>
      <div>
        <el-breadcrumb separator-class="el-icon-arrow-right">
          <el-breadcrumb-item :to="{ path: '/administration' }">Administration</el-breadcrumb-item>
          <el-breadcrumb-item :to="{ path: '/gestion/utilisateurs' }">Liste des utilisateurs</el-breadcrumb-item>
          <el-breadcrumb-item v-if="isCreation">Création d'un utilisateur</el-breadcrumb-item>
          <el-breadcrumb-item v-else>Modification d'un utilisateur</el-breadcrumb-item>
        </el-breadcrumb>
      </div>
    </template>

    <div class="container">
      <div class="admin-content-wrap">
        <h2 v-if="!isCreation">{{ utilisateurActuel }}</h2>

        <el-dialog title="Suppression d'un utilisateur" :visible.sync="deleteDialogVisible" :close-on-click-modal="false">
          <span>Êtes-vous sûr de vouloir supprimer cet utilisateur ?</span>
          <div slot="footer" class="dialog-footer">
            <el-button @click="deleteDialogVisible = false">Annuler</el-button>
            <el-button type="danger" @click="disableUtilisateur('utilisateurForm')">Supprimer</el-button>
          </div>
        </el-dialog>

        <!-- START Utilisateur form -->
        <el-form :model="utilisateur" label-width="120px" ref="utilisateurForm" :rules="rules">
          <div class="row">
            <div class="col-sm-12 col-md-6 col-lg-4">
              <el-form-item label="Nom" prop="nom">
                <el-input type="text" v-model="utilisateur.nom" autocomplete="off"></el-input>
              </el-form-item>
            </div>
            <div class="col-sm-12 col-md-6 col-lg-4">
              <el-form-item label="Prénom" prop="prenom">
                <el-input type="text" v-model="utilisateur.prenom" autocomplete="off"></el-input>
              </el-form-item>
            </div>
            <div class="col-sm-12 col-md-6 col-lg-4">
              <el-form-item label="Fonction" prop="fonction">
                <el-input type="text" v-model="utilisateur.fonction" autocomplete="off"></el-input>
              </el-form-item>
            </div>
            <div class="col-sm-12 col-md-6 col-lg-4">
              <el-form-item label="Téléphone" prop="telephone">
                <el-input type="string" v-model="utilisateur.telephone" autocomplete="off"></el-input>
              </el-form-item>
            </div>
            <div class="col-sm-12 col-md-6 col-lg-4">
              <el-form-item label="E-mail" prop="email">
                <el-input type="email" v-model="utilisateur.email" autocomplete="off"></el-input>
              </el-form-item>
            </div>
            <div class="col-sm-12 col-md-6 col-lg-4">
              <el-form-item label="Date de création" prop="dateCreation" v-if="!isCreation">
                <el-input type="text" :value="dateCreation" :disabled="true"></el-input>
              </el-form-item>
            </div>
            <div class="col-sm-12 col-md-6 col-lg-4">
              <el-form-item label="Créé par" prop="creePar" v-if="!isCreation">
                <el-input type="text" :value="creePar" :disabled="true"></el-input>
              </el-form-item>
            </div>
            <div class="col-sm-12 col-md-6 col-lg-4">
              <el-form-item label="Date de modification" prop="dateModification" v-if="!isCreation">
                <el-input type="text" :value="dateModification" :disabled="true"></el-input>
              </el-form-item>
            </div>
            <div class="col-sm-12 col-md-6 col-lg-4">
              <el-form-item label="Modifié par" prop="modifiePar" v-if="!isCreation">
                <el-input type="text" :value="modifierPar" :disabled="true"></el-input>
              </el-form-item>
            </div>
            <div class="col-sm-12 col-md-6 col-lg-4">
              <el-form-item label="Role" prop="role">
                <el-select v-model="utilisateur.role" placeholder="Liste des roles">
                  <el-option
                    v-for="item in roles"
                    v-if="item.id != null"
                    :key="item.id"
                    :label="item.nom"
                    :value="item.id"
                  ></el-option>
                </el-select>
              </el-form-item>
            </div>
            <div class="col-sm-12 col-md-6 col-lg-4">
              <el-form-item label="Entités" prop="entites">
                <el-select
                  v-model="utilisateur.entites"
                  multiple
                  placeholder="Liste des entites"
                  filterable
                >
                  <el-option
                    v-for="item in entites"
                    v-if="item.id != null"
                    :key="item.id"
                    :label="item.nom"
                    :value="item.id"
                  ></el-option>
                </el-select>
              </el-form-item>
            </div>
          </div>

          <el-form-item v-if="isCreation">
            <div class="d-flex justify-content-end mt-4">
              <el-button plein @click="goBack()">Retour</el-button>
              <el-button type="primary" @click="submitForm('utilisateurForm')">Créer un utilisateur</el-button>
            </div>
          </el-form-item>

          <el-form-item v-else>
            <div class="d-flex justify-content-end mt-4">
              <el-button plein @click="goBack()">Retour</el-button>
              <el-button
                v-if="utilisateur.estActive"
                type="danger"
                @click="deleteDialogVisible = true"
              >Supprimer l'utilisateur</el-button>
              <el-button
                v-else
                type="success"
                @click="enableUtilisateur('utilisateurForm')"
              >Réactiver l'utilisateur</el-button>
              <el-button
                type="success"
                class="btn btn-success"
                @click="submitForm('utilisateurForm')"
              >Valider</el-button>
            </div>
          </el-form-item>
        </el-form>
        <!-- END Utilisateur form -->
      </div>
    </div>
  </back-wrapper>
</template>

<script>
import _ from "lodash";
import customValidator from "../../utils/validation-rules";

const DEFAULT_SORT_COLUMN = "nom";
const DEFAULT_SORT_ORDER = "ASC";

export default {
  name: "utilisateur",
  data() {
    return {
      utilisateur: {
        id: null,
        nom: null,
        prenom: null,
        fonction: null,
        email: null,
        telephone: null,
        dateCreation: null,
        dateModification: null,
        creePar: {
          nom: null,
          prenom: null
        },
        modifiePar: {
          nom: null,
          prenom: null
        },
        role: null,
        entites: [],
        estActive: true
      },
      roles: [],
      entites: [],
      deleteDialogVisible: false,
      activeOrInactive: false,
      rules: {
        nom: [
          customValidator.getRule("requiredNoWhitespaces"),
          customValidator.getRule("maxVarchar")
        ],
        prenom: [
          customValidator.getRule("requiredNoWhitespaces"),
          customValidator.getRule("maxVarchar")
        ],
        fonction: customValidator.getRule("maxVarchar"),
        telephone: customValidator.getRule("phone"),
        email: [
          customValidator.getRule("required"),
          customValidator.getRule("email")
        ],
        role: customValidator.getRule("required", "change"),
        entites: customValidator.getRule("required", "change")
      },
      utilisateurActuel: null
    };
  },
  created() {
    this.loadEntites();
    this.loadRoles();
    let utilisateurID = _.isNil(this.$route.params.id)
      ? null
      : this.$route.params.id;
    if (_.isNil(utilisateurID)) {
      return;
    }
    const loading = this.$loading({ lock: true });
    this.$apollo
      .query({
        query: require("../../graphql/administration/utilisateurs/utilisateur.gql"),
        variables: {
          utilisateurID: utilisateurID
        }
      })
      .then(response => {
        this.utilisateur.id = response.data.utilisateur.id;
        this.utilisateur.nom = response.data.utilisateur.nom;
        this.utilisateur.prenom = response.data.utilisateur.prenom;
        this.utilisateur.email = response.data.utilisateur.email;
        this.utilisateur.fonction = response.data.utilisateur.fonction;
        this.utilisateur.telephone = response.data.utilisateur.telephone;
        this.utilisateur.dateCreation = response.data.utilisateur.dateCreation;
        this.utilisateur.dateModification =
          response.data.utilisateur.dateModification;
        this.utilisateur.creePar.nom = _.isNil(
          response.data.utilisateur.creePar
        )
          ? ""
          : response.data.utilisateur.creePar.nom;
        this.utilisateur.creePar.prenom = _.isNil(
          response.data.utilisateur.creePar
        )
          ? ""
          : response.data.utilisateur.creePar.prenom;
        this.utilisateur.modifiePar.nom = _.isNil(
          response.data.utilisateur.modifiePar
        )
          ? ""
          : response.data.utilisateur.modifiePar.nom;
        this.utilisateur.modifiePar.prenom = _.isNil(
          response.data.utilisateur.modifiePar
        )
          ? ""
          : response.data.utilisateur.modifiePar.prenom;
        this.utilisateur.estActive = response.data.utilisateur.estActive;
        this.utilisateur.role =
          response.data.utilisateur.utilisateursRolesEntites.items[0].role.id;
        this.utilisateur.entites = response.data.utilisateur.utilisateursRolesEntites.items.map(
          item => item.entite.id
        );
        this.utilisateurActuel = `${response.data.utilisateur.prenom} ${response.data.utilisateur.nom}`;
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
      if (_.isNil(this.utilisateur.creePar)) {
        return null;
      }
      return (
        this.utilisateur.creePar.prenom + " " + this.utilisateur.creePar.nom
      );
    },
    modifierPar() {
      if (_.isNil(this.utilisateur.modifiePar)) {
        return null;
      }
      return (
        this.utilisateur.modifiePar.prenom +
        " " +
        this.utilisateur.modifiePar.nom
      );
    },
    dateCreation() {
      if (_.isNil(this.utilisateur.dateCreation)) {
        return null;
      }
      let date = new Date(this.utilisateur.dateCreation);
      return date.toLocaleDateString();
    },
    dateModification() {
      if (_.isNil(this.utilisateur.dateModification)) {
        return null;
      }
      let date = new Date(this.utilisateur.dateModification);
      return date.toLocaleDateString();
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
              mutation: require("../../graphql/administration/utilisateurs/saveUtilisateur.gql"),
              variables: {
                utilisateurID: this.utilisateur.id,
                nom: this.utilisateur.nom,
                prenom: this.utilisateur.prenom,
                fonction: this.utilisateur.fonction,
                email: this.utilisateur.email,
                telephone: this.utilisateur.telephone,
                motDePasse: null,
                estActive: this.utilisateur.estActive,
                roleID: this.utilisateur.role,
                entiteID: this.utilisateur.entites
              }
            })
            .then(() => {
              this.$router.push("/gestion/utilisateurs");
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
    disableUtilisateur(formName) {
      this.utilisateur.estActive = false;
      this.activeOrInactive = true;
      this.submitForm(formName);
    },
    enableUtilisateur(formName) {
      this.utilisateur.estActive = true;
      this.activeOrInactive = true;
      this.submitForm(formName);
    },
    loadEntites() {
      const utilisateurEmail = this.$store.getters["security/email"];
      const estAdministrateurCentral = this.$store.getters[
        "security/estAdministrateurCentral"
      ];
      this.$apollo
        .query({
          query: require("../../graphql/administration/entites/entites.gql"),
          variables: {
            limit: 100,
            offset: 0,
            utilisateur: estAdministrateurCentral ? null : utilisateurEmail,
            sortColumn: DEFAULT_SORT_COLUMN,
            sortOrder: DEFAULT_SORT_ORDER
          }
        })
        .then(response => {
          if (response.data && response.data.entites) {
            this.entites = response.data.entites.items;
          }
        });
    },
    loadRoles() {
      this.$apollo
        .query({
          query: require("../../graphql/administration/roles/roles.gql"),
          variables: {
            limit: 50,
            offset: 0,
            sortColumn: 'nom',
            sortOrder: 'ASC'
          }
        })
        .then(response => {
          if(response.data && response.data.roles) {
            this.roles = response.data.roles.items;
          }
        });
    },
    goBack() {
      this.$router.push("/gestion/utilisateurs");
    }
  }
};
</script>
