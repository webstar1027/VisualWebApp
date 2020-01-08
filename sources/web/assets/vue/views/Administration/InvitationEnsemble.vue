<template>
  <back-wrapper>
  <div>
    <div>
      <el-breadcrumb separator-class="el-icon-arrow-right">
        <el-breadcrumb-item :to="{ path: '/administration' }">Administration</el-breadcrumb-item>
        <el-breadcrumb-item :to="{ path: '/gestion/ensembles' }">Gestion des ensembles</el-breadcrumb-item>
        <el-breadcrumb-item>Invitation d’entité</el-breadcrumb-item>
      </el-breadcrumb>
    </div>
    <br />
    <div class="admin-content-wrap text-center">
      <h4 class="admin-content-title">Entités à rattacher</h4>
      <el-form :model="invitation" ref="invitationForm">
        <el-form-item prop="entites" :rules="tagsRule">
          <el-select v-model="invitation.entites" multiple filterable placeholder="Select">
            <el-option v-for="key in entitesList" :key="key.id" :label="key.nom" :value="key.id"></el-option>
          </el-select>
        </el-form-item>
        <el-form-item>
          <div class="text-center">
            <el-button
              type="outline-primary"
              @click="$router.push('/gestion/ensemble/'+$route.params.id)"
            >Retour</el-button>
            <el-button
              v-if="invitation.isReferentEnsemble"
              type="primary"
              @click="submitForm('invitationForm')"
            >Valider</el-button>
          </div>
        </el-form-item>
      </el-form>

      <ApolloQuery
        :query="require('../../graphql/administration/invitations/invitationEnsembles.gql')"
        :variables="{
				limit: limit,
				offset: offset,
				ensembleID: invitation.ensembleID,
			}"
      >
        <template slot-scope="{ result: { loading, error, data }, isLoading }">
          <div v-if="error">Une erreur est survenue !</div>

          <el-table v-loading="isLoading === 1" :data="tableData(data)" style="width: 100%">
            <el-table-column column-key="nom" prop="entite.nom" label="Nom"></el-table-column>
            <el-table-column column-key="utilisateurs" label="Référents de l’ensemble">
              <template slot-scope="scope">
                <p v-if="isEntiteReferentEnsemble(scope.$index)">Oui</p>
                <p v-else>Non</p>
              </template>
            </el-table-column>
            <el-table-column column-key="entiteType" prop="entite.type" label="Type d'entité"></el-table-column>
            <el-table-column
              column-key="organizationType"
              prop="entite.typeOrganisme"
              label="Type d'organisme"
            ></el-table-column>
            <el-table-column
              column-key="organizationCode"
              prop="entite.code"
              label="Code organisme"
            ></el-table-column>
            <el-table-column column-key="siren" prop="entite.siren" label="Siren"></el-table-column>
            <el-table-column column-key="statut" prop="statut" label="Statut"></el-table-column>
            <el-table-column column-key="delete" label="Retrait">
              <template slot-scope="scope">
                <i class="el-icon-close" @click="removeInvitaionEntite(scope.$index)"></i> &nbsp;
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
  name: "InvitationEnsemble",
  data() {
    let checkEntites = (rule, value, callback) => {
      if (this.invitation.entites.length === 0) {
        callback(new Error("Veuillez sélectionner au moins une entité."));
      } else {
        callback();
      }
    };
    return {
      tagsRule: [
        {
          required: true,
          validator: checkEntites,
          message: "Veuillez sélectionner au moins une entité.",
          change: "blur"
        }
      ],
      entitesList: [],
      invitation: {
        ensembleID: null,
        entites: []
      },
      invitationEnsembleEntites: [],
      limit: 10,
      offset: 0,
      current_page: null
    };
  },
  created() {
    let scope = this;
    this.invitation.ensembleID = _.isNil(this.$route.params.id)
      ? null
      : this.$route.params.id;
    const loading = this.$loading({ lock: true });
    this.loadEntites();
    if (_.isNil(this.invitation.ensembleID)) {
      return;
    }
    this.$apollo
      .query({
        query: require("../../graphql/administration/ensembles/referentEnsemble.gql"),
        variables: {
          ensembleID: this.invitation.ensembleID
        }
      })
      .then(response => {
        scope.invitation.isReferentEnsemble = response.data.referentEnsemble;
      })
      .finally(() => {
        loading.close();
      });
    loading.close();
  },
  methods: {
    loadEntites() {
      this.$apollo
        .query({
          query: require("../../graphql/administration/entites/allEntites.gql")
        })
        .then(response => {
          this.getInvitableEntites(response.data.allEntites);
        });
    },
    getInvitableEntites(allEntites) {
      this.$apollo
        .query({
          query: require("../../graphql/administration/ensembles/ensemble.gql"),
          variables: {
            ensembleID: this.invitation.ensembleID
          }
        }).then(response => {
          const entitesBelong = response.data.ensemble.entitesByEnsemblesEntites;
          entitesBelong.map(entite => {
            let index = allEntites.findIndex(obj => {
              if (obj.id === entite.id) return obj.id;
            });
            index >= 0 ? allEntites.splice(index, 1) : false;
          });
          this.entitesList = allEntites;
        });
    },
    tableData(data) {
      if (!_.isNil(data)) {
        data.invitationEnsembles.items.map(invitation => {
          let index = this.entitesList.findIndex(obj => {
            if (obj.id === invitation.entite.id) return obj.id;
          });
          index >= 0 ? this.entitesList.splice(index, 1) : false;
        });
        this.invitationEnsembleEntites = data.invitationEnsembles.items;
        return data.invitationEnsembles.items;
      } else {
        return [];
      }
    },
    count(data) {
      if (!_.isNil(data)) {
        return data.invitationEnsembles.count;
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
              mutation: require("../../graphql/administration/invitations/saveInvitationEnsemble.gql"),
              variables: {
                ensembleID: this.invitation.ensembleID,
                entites: this.invitation.entites
              }
            })
            .then(data => {
              if (data.data.saveInvitationEnsemble === null) {
                this.$message({
                  showClose: true,
                  message: "Vous n'êtes pas référent de l'ensemble.",
                  type: "error",
                  duration: 10000
                });
              } else {
                this.$router.push(
                  "/gestion/ensemble/" +
                    this.invitation.ensembleID +
                    "/invitation"
                );
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
    removeInvitaionEntite(index) {
      const loading = this.$loading({ lock: true });
      this.$apollo
        .mutate({
          mutation: require("../../graphql/administration/invitations/deleteInvitationEnsemble.gql"),
          variables: {
            ensembleID: this.invitation.ensembleID,
            entiteID: this.invitationEnsembleEntites[index].entite.id
          }
        })
        .then(() => {
          this.$router.push(
            "/gestion/ensemble/" + this.invitation.ensembleID + "/invitation"
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
    isEntiteReferentEnsemble(index) {
      let result = this.invitationEnsembleEntites[
        index
      ].entite.ensemblesByReferentsEnsembles.findIndex(referentEnsemble => {
        return this.invitation.ensembleID === referentEnsemble.id;
      });
      return result !== -1;
    }
  }
};
</script>
