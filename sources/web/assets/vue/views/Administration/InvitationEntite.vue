<template>
  <back-wrapper>
  <div class="invitation-entite">
    <div>
      <el-breadcrumb separator-class="el-icon-arrow-right">
        <el-breadcrumb-item :to="{ path: '/administration' }">Administration</el-breadcrumb-item>
        <el-breadcrumb-item :to="{ path: '/gestion/entites' }">Gestion des entite</el-breadcrumb-item>
        <el-breadcrumb-item>Invitation d’entité</el-breadcrumb-item>
      </el-breadcrumb>
    </div>
    <br />
    <div class="admin-content-wrap text-center">
      <h4 class="admin-content-title">Utilisateurs à rattacher</h4>
      <el-form :model="invitation" ref="invitationForm">
        <el-form-item prop="utilisateurs" :rules="tagsRule">
          <el-select v-model="invitation.utilisateurs" multiple filterable placeholder="Select">
            <el-option
              v-for="key in utilisateursList"
              :key="key.id"
              :label="key.prenom + ' ' + key.nom"
              :value="key.id"
            ></el-option>
          </el-select>
        </el-form-item>
        <el-form-item>
          <div class="text-center">
            <el-button
              type="outline-primary"
              @click="$router.push('/gestion/entite/'+$route.params.id)"
            >Retour</el-button>
            <el-button
              v-if="invitation.isReferentEntite"
              type="primary"
              @click="submitForm('invitationForm')"
            >Valider</el-button>
          </div>
        </el-form-item>
      </el-form>

      <ApolloQuery
        :query="require('../../graphql/administration/invitations/invitationEntites.gql')"
        :variables="{
				limit: limit,
				offset: offset,
				entiteID: invitation.entiteID,
			}"
      >
        <template slot-scope="{ result: { loading, error, data }, isLoading }">
          <div v-if="error">Une erreur est survenue !</div>

          <el-table v-loading="isLoading === 1" :data="tableData(data)" style="width: 100%">
            <el-table-column column-key="nom" prop="nom" label="Nom"></el-table-column>
            <el-table-column column-key="prenom" prop="prenom" label="Prenom"></el-table-column>
            <el-table-column column-key="utilisateurs" label="Référents de l'entité">
              <template slot-scope="scope">
                <p v-if="scope.row.isReferentEntite">Oui</p>
                <p v-else>Non</p>
              </template>
            </el-table-column>
            <el-table-column column-key="email" prop="email" label="Email"></el-table-column>
            <el-table-column column-key="telephone" prop="telephone" label="Telephone"></el-table-column>
            <el-table-column column-key="statut" prop="statut" label="Statut"></el-table-column>
            <el-table-column column-key="delete" label="Retrait">
              <template slot-scope="scope">
                <el-button
                  type="danger"
                  icon="el-icon-close"
                  circle
                  @click="removeInvitaionEntite(scope.row)"
                ></el-button>
                <el-button
                  v-if="scope.row.statut === 'Refuser'"
                  type="primary"
                  icon="el-icon-s-promotion"
                  circle
                  @click="resendInvitation(scope.row)"
                ></el-button>
              </template>
            </el-table-column>
          </el-table>

          <el-pagination
            v-if="isLoading === 0"
            @current-change="handleCurrentChange"
            style="text-align: center"
            layout="prev, pager, next"
            :current-page="current_page"
            :total="count(data)"
          ></el-pagination>
        </template>
      </ApolloQuery>
    </div>
  </div>
  </back-wrapper>
</template>

<script>
import _ from "lodash";

export default {
  name: "InvitationEntite",
  data() {
    let checkUtilisateurs = (rule, value, callback) => {
      if (this.invitation.utilisateurs.length === 0) {
        callback(new Error("Veuillez sélectionner au moins un utilisateur."));
      } else {
        callback();
      }
    };
    return {
      tagsRule: [
        {
          required: true,
          validator: checkUtilisateurs,
          message: "Veuillez sélectionner au minimum un utilisateur.",
          change: "blur"
        }
      ],
      utilisateursList: [],
      utilisateursBelong: [],
      invitation: {
        entiteID: null,
        utilisateurs: []
      },
      limit: 10,
      offset: 0,
      current_page: null
    };
  },
  created() {
    let scope = this;
    this.invitation.entiteID = _.isNil(this.$route.params.id)
      ? null
      : this.$route.params.id;
    const loading = this.$loading({ lock: true });
    this.loadUtilisateurs();
    if (_.isNil(this.invitation.entiteID)) {
      return;
    }
    this.$apollo
      .query({
        query: require("../../graphql/administration/entites/referentEntite.gql"),
        variables: {
          entiteID: this.invitation.entiteID
        }
      })
      .then(response => {
        scope.invitation.isReferentEntite = response.data.referentEntite;
      })
      .finally(() => {
        loading.close();
      });
    loading.close();
  },
  methods: {
    loadUtilisateurs() {
      this.$apollo
        .query({
          query: require("../../graphql/administration/utilisateurs/allUtilisateurs.gql")
        })
        .then(response => {
          this.getInvitableUtilisateurs(response.data.allUtilisateurs.items);
        });
    },
    getInvitableUtilisateurs(allUtilisateurs) {
      this.$apollo
        .query({
          query: require("../../graphql/administration/entites/entite.gql"),
          variables: {
            entiteID: this.invitation.entiteID
          }
        })
        .then(response => {
          if (response.data && response.data.entite) {
            const utilisateursRolesEntites =
              response.data.entite.utilisateursRolesEntites.items;
            this.utilisateursBelong = utilisateursRolesEntites.map(item => {
              if (item.role.nom === "Référent entité") {
                item.utilisateur.isReferentEntite = true;
              } else {
                item.utilisateur.isReferentEntite = false;
              }
              item.utilisateur.statut = "Rattachée";
              return item.utilisateur;
            });
            this.utilisateursBelong.map(utilisateur => {
              let index = allUtilisateurs.findIndex(obj => {
                if (obj.id === utilisateur.id) return obj.id;
              });
              index >= 0 ? allUtilisateurs.splice(index, 1) : false;
            });
            this.utilisateursList = allUtilisateurs;
          }
        });
    },
    tableData(data) {
      if (!_.isNil(data)) {
        const invitations = data.invitationEntites.items.map(invitation => {
          let index = this.utilisateursList.findIndex(obj => {
            if (obj.id === invitation.utilisateur.id) return obj.id;
          });
          index >= 0 ? this.utilisateursList.splice(index, 1) : false;
          let utilisateur = invitation.utilisateur;
          utilisateur.statut = invitation.statut;
          return utilisateur;
        });
        return invitations.concat(this.utilisateursBelong);
      } else {
        return [];
      }
    },
    count(data) {
      if (!_.isNil(data)) {
        return data.invitationEntites.count;
      }
      return 0;
    },
    handleCurrentChange(val) {
      this.current_page = val;
      this.offset = this.limit * (val - 1);
    },
    submitForm(formName) {
      this.$refs[formName].validate(valid => {
        if (valid) {
          const loading = this.$loading({ lock: true });
          this.$apollo
            .mutate({
              mutation: require("../../graphql/administration/invitations/saveInvitationEntite.gql"),
              variables: {
                entiteID: this.invitation.entiteID,
                utilisateurs: this.invitation.utilisateurs
              }
            })
            .then(data => {
              if (data.data.saveInvitationEntite === null) {
                this.$message({
                  showClose: true,
                  message: "Vous n'êtes pas le référent",
                  type: "error",
                  duration: 10000
                });
              } else {
                this.$router.go(0);
              }
            })
            .catch(error => {
              this.$message({
                showClose: true,
                message: error.networkError.result,
                type: "error",
                duration: 10000
              });
            })
            .finally(() => {
              loading.close();
            });
        }
        return valid;
      });
    },
    removeInvitaionEntite(data) {
      const loading = this.$loading({ lock: true });
      this.$apollo
        .mutate({
          mutation: require("../../graphql/administration/invitations/deleteInvitationEntite.gql"),
          variables: {
            entiteID: this.invitation.entiteID,
            utilisateurID: data.id
          }
        })
        .then(() => {
          this.$router.push(
            "/gestion/entite/" + this.invitation.entiteID + "/invitation"
          );
          this.$router.go(0);
        })
        .catch(error => {
          this.$message({
            showClose: true,
            message: error.networkError.result,
            type: "error",
            duration: 10000
          });
        })
        .finally(() => {
          loading.close();
        });
    },
    resendInvitation(data) {
      const loading = this.$loading({ lock: true });
      this.$apollo
        .mutate({
          mutation: require("../../graphql/administration/invitations/resendInvitationEntite.gql"),
          variables: {
            entiteID: this.invitation.entiteID,
            utilisateurID: data.id
          }
        })
        .then(data => {
          this.$router.go(0);
        })
        .catch(error => {
          this.$message({
            showClose: true,
            message: error.networkError.result,
            type: "error",
            duration: 10000
          });
        })
        .finally(() => {
          loading.close();
        });
    }
  }
};
</script>

<style type="text/css">
.invitation-entite .el-select {
  width: 500px;
}
.invitation-entite .el-table .el-button {
  height: 44px;
}
.invitation-entite .el-table .el-button i {
  color: white;
}
</style>
