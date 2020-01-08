<template>
    <el-dialog
            title="Partage d'une simulation"
            :visible.sync="show"
            width="50%"
            class="share-dialog"
            :close-on-click-modal="false"
            :before-close="handleClose"
    >
        <el-form :model="form" class="pl-4">
            <el-row>
          <span>
            Vous allez partager
            <strong style="font-size: 24px;">{{ selectedSimulations.length }}</strong>
            {{ selectedSimulations.length > 1 ? 'simulations' : 'simulation' }}
          </span>
            </el-row>

            <el-row :gutter="20" class="mt-5">
                <el-col :span="8" :offset="8">
                    <span class="filter-title">Type de partage</span>
                    <el-select
                            v-model="form.shareType"
                            clearable
                            placeholder="Type de partage"
                            class="shareForm-select"
                    >
                        <el-option
                                v-for="(item, i) in shareTypeOptions"
                                :key="i"
                                :label="item"
                                :value="item"
                        ></el-option>
                    </el-select>
                </el-col>
            </el-row>

            <el-row :gutter="20" class="mt-5">
                <el-col :span="8">
                    <span class="filter-title">Rechercher par entité</span>
                    <vue-bootstrap-typeahead
                            v-model="shareFilter.entite"
                            :data="form.entites"
                            :serializer="s => s.label"
                            placeholder="Sélectionnez une entité"></vue-bootstrap-typeahead>
                </el-col>

                <el-col :span="8">
                    <span class="filter-title">Rechercher par ensemble</span>
                    <vue-bootstrap-typeahead
                            v-model="shareFilter.ensemble"
                            :data="simulationsEnsembles"
                            :serializer="s => s.label"
                            placeholder="Sélectionnez un ensemble"></vue-bootstrap-typeahead>
                </el-col>

                <el-col :span="8">
                    <span class="filter-title">Rechercher par utilisateur</span>
                    <vue-bootstrap-typeahead
                            v-model="shareFilter.nom"
                            :data="users"
                            :serializer="s => s.label"
                            placeholder="Sélectionnez un utilisateur"></vue-bootstrap-typeahead>
                </el-col>
            </el-row>

            <el-row class="mt-5" v-if="selectedSimulations.length > 0">
                <ApolloQuery
                        :query="require('../../../../graphql/administration/utilisateurs/fetchUtilisateurs.gql')"
                        :variables="{
							limit: limit,
							offset: offset,
							nom: shareFilter.nom,
							entite: shareFilter.entite,
							ensemble: shareFilter.ensemble,
							simulationID: selectedSimulations[0].simulation.id,
							currentEntite: selectedId ? selectedId.id : '',
							sortColumn: sortColumn,
							sortOrder: sortOrder
						}"
                >
                    <template slot-scope="{ result:{ loading, error, data }, isLoading, query }">
                        <div v-if="error || !setQuery(query)">Une erreur est survenue !</div>
                        <span>
                Il y a
                <strong style="font-size: 24px;">{{ getCount(data, 'fetchUtilisateurs') }}</strong>
                {{ getCount(data, 'fetchUtilisateurs') > 1 ? 'utilisateurs' : 'utilisateur' }}
              </span>
                        <el-table
                                v-loading="isLoading"
                                ref="utilisateurTable"
                                :data="getItems(data, 'fetchUtilisateurs')"
                                @selection-change="checkedUsers"
                        >
                            <el-table-column sortable="custom" type="selection" width="60"></el-table-column>
                            <el-table-column sortable="custom" prop="nom" label="Nom"></el-table-column>
                            <el-table-column sortable="custom" prop="email" label="Email"></el-table-column>
                            <el-table-column
                                    sortable="custom"
                                    prop="utilisateursRolesEntites.items[0].entite.nom"
                                    label="Entité"
                            ></el-table-column>
                            <template slot="empty">Aucun résultat</template>
                        </el-table>
                        <el-pagination
                                v-if="isLoading === 0"
                                @current-change="handleCurrentChange"
                                style="text-align: center"
                                layout="prev, pager, next"
                                :current-page="currentPage"
                                :total="getCount(data, 'fetchUtilisateurs')"
                        ></el-pagination>
                    </template>
                </ApolloQuery>
            </el-row>

            <el-row class="mt-5">
                <el-col :span="6" :offset="5">
                    <el-button type="info" style="width: 200px" @click="close">Annuler</el-button>
                </el-col>
                <el-col :span="8" :offset="2">
                    <el-button
                            type="primary"
                            style="width: 200px"
                            :disabled="form.users.length === 0 || form.shareType === null || selectedSimulations.length > 1"
                            @click="saveShare"
                    >Partager</el-button>
                </el-col>
            </el-row>
        </el-form>
    </el-dialog>
</template>
<script>
    import VueBootstrapTypeahead from "vue-bootstrap-typeahead";
    import store from "../../../../store";
    import { mapState } from "vuex";
    import {getCount, getItems} from '../../../../utils/helpers';

    export default {
      name: "SimulationsPartage",
      store,
      props: [
        'show',
        'selectedSimulations',
        'currentPage',
        'limit',
        'sortColumn',
        'handleCurrentChange',
        'offset',
        'sortOrder',
        'close',
        'getSimulations',
        'validDataType',
        'simulationsEnsembles'
      ],
      components: {VueBootstrapTypeahead},
      data() {
        return {
          query: null,
          shareTypeOptions: ["Lecture Seule", "Collaboratif"],
          users: [],
          shareFilter: {
            id: null,
            entite: null,
            ensemble: null,
            nom: null
          },
          form: {
            users: [],
            shareType: null,
            entites: [],
          }
        }
      },
      computed: {
        ...mapState({
          selectedId: state => state.choixEntite.userSelectedId
        }),
      },
      mounted() {
        this.initForm();
        //this.getUsers();
      },
      methods: {
        getCount: getCount,
        getItems: getItems,
        initForm() {
          this.form = {
            users: [],
            shareType: null,
            entites: [],
          }
        },
        checkedUsers(selection) {
          this.form.users = selection;
        },
        setQuery(query){
          this.query = query;
          return !!query;
        },
        getUsers(){
          this.$apollo
            .query({
              query: require("../../../../graphql/administration/utilisateurs/allUtilisateurs.gql"),
            }).then((response) => {
                for (let utilisateur of response.data.allUtilisateurs.items) {
                  this.users.push({
                    label: utilisateur.nom,
                    value: utilisateur.id
                  });
                }
          } )
        },
        saveShare() {
          let users = [];
          let entites = [];

          this.form.users.forEach(item => {
            entites.push(item.utilisateursRolesEntites.items[0].entite.id);
            users.push(item.id);
          });
          this.$apollo
            .mutate({
              mutation: require("../../../../graphql/administration/partagers/savePartage.gql"),
              variables: {
                id: "",
                simulationID: this.selectedSimulations[0].simulation.id,
                utilisateurs: users,
                entites: entites,
                owner: this.selectedSimulations[0].simulation.entite.id,
                partagerType: this.form.shareType
              }
            })
            .then(async () => {
              this.handleClose();
              await this.updateData();
              this.getSimulations();
              this.$message({
                showClose: true,
                message: `La simulation ${this.selectedSimulations[0].simulation.nom} vient d\'être partagée`,
                type: 'success'
              });
            }).catch(error => {
                this.isSubmitting = false;
                this.$message({
                  showClose: true,
                  message: error.networkError.result,
                  type: 'error',
                });
          })
        },
        async updateData() {
          await this.query.fetchMore({
            variables:{
                limit: this.limit,
                offset: this.offset,
                nom: this.shareFilter.nom,
                entite: this.shareFilter.entite,
                ensemble: this.shareFilter.ensemble,
                simulationID: this.selectedSimulations[0].simulation.id,
                currentEntite: this.selectedId ? this.selectedId.id : '',
                sortColumn: this.sortColumn,
                sortOrder: this.sortOrder
            },
            updateQuery: (prev, { fetchMoreResult }) => {
              return fetchMoreResult
            }
          }).catch(error => {
            this.isSubmitting = false;
            this.$message({
              showClose: true,
              message: error.networkError.result,
              type: 'error',
            });
          })
        },
        handleClose() {
          this.form.users = [];
          this.close();
          this.initForm();
        },
      },
    }
</script>