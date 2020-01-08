<template>
  <back-wrapper>
    <div>
      <div>
        <el-breadcrumb separator-class="el-icon-arrow-right">
          <el-breadcrumb-item :to="{ path: '/administration' }">Administration</el-breadcrumb-item>
          <el-breadcrumb-item :to="{ path: '/gestion/ensembles' }">Gestion des ensembles</el-breadcrumb-item>
          <el-breadcrumb-item v-if="isCreation">Création d'un ensemble</el-breadcrumb-item>
          <el-breadcrumb-item v-else>Modification d'un ensemble</el-breadcrumb-item>
        </el-breadcrumb>
      </div>
      <div class="container">
      <div class="admin-content-wrap">
        <h2 v-if="!isCreation">{{ ensemble.nom }}</h2>

        <el-dialog title="Suppression d'un ensemble" :visible.sync="deleteDialogVisible" :close-on-click-modal="false">
          <span>Êtes-vous sûr de vouloir supprimer cet ensemble ?</span>
          <div slot="footer" class="dialog-footer">
            <el-button @click="deleteDialogVisible = false">Annuler</el-button>
            <el-button type="danger" @click="disableEnsemble('ensembleForm')">Supprimer</el-button>
          </div>
        </el-dialog>

        <!-- START Entite form -->
        <el-form :model="ensemble" label-width="180px" ref="ensembleForm" :rules="rules">
          <div class="row">
          <div class="col-sm-12 col-md-6 col-lg-6">
          <el-form-item label="Nom" prop="nom">
            <el-input type="text" v-model="ensemble.nom" autocomplete="off"></el-input>
          </el-form-item>
          </div>
            <div class="col-sm-12 col-md-6 col-lg-6">
          <el-form-item label="Référents de l’ensemble" prop="referents">
            <el-select v-model="ensemble.referents" multiple filterable placeholder="Select">
              <el-option
                      v-for="key in referentOptions"
                      :key="key.id"
                      :label="key.nom"
                      :value="key.id"
              ></el-option>
            </el-select>
          </el-form-item>
            </div>
          </div>
          <el-form-item label="Description" prop="description">
            <el-input type="text" v-model="ensemble.description" autocomplete="off"></el-input>
          </el-form-item>
          <div class="d-flex my-4">
            <div class="ml-auto">
          <el-form-item v-if="isCreation">
            <el-button plein @click="goBack()">Retour</el-button>
            <el-button type="primary" @click="submitForm('ensembleForm')">Créer un ensemble</el-button>
          </el-form-item>
          <el-form-item v-else>
            <div class="text-center m-2">
              <el-button
                v-if="isReferentEnsemble"
                @click="inviteEntitesIntoThisEnsemble"
                type="warning"
              >Inviter des entités</el-button>
              <el-button
                v-if="isReferentEnsemble"
                type="warning"
                @click="$router.push('/gestion/entites?ensemble=' + ensemble.nom)"
              >Liste des entités</el-button>
            </div>
            <br />
            <div class="text-center">
              <el-button plein @click="goBack()">Retour</el-button>
              <el-button
                v-if="isReferentEnsemble"
                type="primary"
                @click="leaveEnsemble"
              >Quitter l'ensemble</el-button>
              <el-button
                v-if="isReferentEnsemble && ensemble.estActive"
                type="danger"
                @click="deleteDialogVisible = true"
              >Supprimer l'ensemble</el-button>
              <el-button
                v-if="isReferentEnsemble && !ensemble.estActive"
                type="success"
                @click="enableEnsemble('ensembleForm')"
              >Réactiver l'ensemble</el-button>
              <el-button
                v-if="isReferentEnsemble"
                type="success"
                class="btn btn-success"
                @click="submitForm('ensembleForm')"
              >Valider</el-button>
            </div>
          </el-form-item>
            </div>
          </div>
        </el-form>
        <!-- END Entite form -->
      </div>
    </div>
    </div>
  </back-wrapper>
</template>

<script>
import _ from "lodash";
import customValidator from "../../utils/validation-rules";

export default {
  name: "ensemble",
  data() {
    return {
      ensemble: {
        id: null,
        nom: null,
        description: null,
        referents: [],
        estActive: true
      },
      referentOptions: [],
      deleteDialogVisible: false,
      activeOrInactive: false,
      isReferentEnsemble: false,
      rules: {
        nom: [
          customValidator.getRule("requiredNoWhitespaces"),
          customValidator.getRule("maxVarchar")
        ],
        description: customValidator.getRule("maxLongtext"),
        referents: customValidator.getRule("required")
      }
    };
  },
  created() {
    let ensembleID = _.isNil(this.$route.params.id)
      ? null
      : this.$route.params.id;
    this.loadEntites();
    if (_.isNil(ensembleID)) {
      return;
    }
    const loading = this.$loading({ lock: true });
    this.$apollo
      .query({
        query: require("../../graphql/administration/ensembles/referentEnsemble.gql"),
        variables: {
          ensembleID: ensembleID
        }
      })
      .then(response => {
        this.isReferentEnsemble = response.data.referentEnsemble;
      })
      .finally(() => {
        loading.close();
      });
    this.$apollo
      .query({
        query: require("../../graphql/administration/ensembles/ensemble.gql"),
        variables: {
          ensembleID: ensembleID
        }
      })
      .then(response => {
        this.ensemble.id = response.data.ensemble.id;
        this.ensemble.nom = response.data.ensemble.nom;
        this.ensemble.description = response.data.ensemble.description;
        this.ensemble.estActive = response.data.ensemble.estActive;
        this.ensemble.referents = Array.from(
          response.data.ensemble.entitesByReferentsEnsembles,
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
    }
  },
  methods: {
    inviteEntitesIntoThisEnsemble() {
      this.$router.push(
        "/gestion/ensemble/" + this.ensemble.id + "/invitation"
      );
    },
    submitForm(formName) {
      let scope = this;
      this.$refs[formName].validate(valid => {
        if (valid) {
          const loading = this.$loading({ lock: true });
          this.$apollo
            .mutate({
              mutation: require("../../graphql/administration/ensembles/saveEnsemble.gql"),
              variables: {
                ensembleID: this.ensemble.id,
                nom: this.ensemble.nom,
                description: this.ensemble.description,
                referents: this.ensemble.referents,
                estActive: this.ensemble.estActive
              }
            })
            .then(() => {
              this.$router.push("/gestion/ensembles");
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
                scope.ensemble.estActive = !scope.ensemble.estActive;
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
    disableEnsemble(formName) {
      this.ensemble.estActive = false;
      this.activeOrInactive = true;
      this.submitForm(formName);
    },
    enableEnsemble(formName) {
      this.ensemble.estActive = true;
      this.activeOrInactive = true;
      this.submitForm(formName);
    },
    leaveEnsemble() {
      this.$apollo
        .query({
          query: require("../../graphql/administration/ensembles/leaveEnsemble.gql"),
          variables: {
            ensembleID: this.ensemble.id
          }
        })
        .then(response => {
          this.$router.push("/gestion/ensembles");
          this.$router.go(0);
        });
    },
    loadEntites() {
      this.$apollo
        .query({
          query: require("../../graphql/administration/entites/allEntites.gql")
        })
        .then(response => {
          this.referentOptions = response.data.allEntites;
        });
    },
    goBack() {
      this.$router.push("/gestion/ensembles");
    }
  }
};
</script>
