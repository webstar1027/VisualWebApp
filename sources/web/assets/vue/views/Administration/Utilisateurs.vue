<template>
  <back-wrapper>
    <template v-slot:breadcrumb>
      <div>
        <el-breadcrumb separator-class="el-icon-arrow-right">
          <el-breadcrumb-item :to="{ path: '/administration' }">Administration</el-breadcrumb-item>
          <el-breadcrumb-item>Gestion des utilisateurs</el-breadcrumb-item>
        </el-breadcrumb>
      </div>
    </template>
    <div class="container px-5">
      <div class="admin-content-wrap">
        <div class="d-flex py-4 justify-content-between">
          <h1 class="admin-content-title">Gestion des utilisateurs</h1>
          <el-button
                  type="primary"
                  @click="navigateToNewUserForm"
          >Ajouter un nouveau utilisateur</el-button>
        </div>
        <!-- START filters -->
        <el-form :model="filters" label-width="200px" ref="filtersForm" label-position="top">
          <div class="row">
            <div class="col-sm-12 col-md-3 px-3">
              <el-form-item label="Nom" prop="nom">
                <el-input type="text" v-model="form.nom" autocomplete="off"></el-input>
              </el-form-item>
            </div>

            <div class="col-sm-12 col-md-3 px-3">
              <el-form-item label="Prénom" prop="prenom">
                <el-input type="text" v-model="form.prenom" autocomplete="off"></el-input>
              </el-form-item>
            </div>

            <div class="col-sm-12 col-md-3 px-3">
              <el-form-item label="E-mail" prop="email">
                <el-input type="text" v-model="form.email" autocomplete="off"></el-input>
              </el-form-item>
            </div>

            <div class="col-sm-12 col-md-3 px-3">
              <el-form-item label="Téléphone" prop="telephone">
                <el-input type="text" v-model="form.telephone" autocomplete="off"></el-input>
              </el-form-item>
            </div>

            <div class="col-sm-12 col-md-3 px-3">
              <el-form-item label="Entités">
                <el-select v-model="form.selectedEntite" placeholder="Liste des entités">
                  <el-option
                    v-for="item in filtersEntites"
                    :key="item.value"
                    :label="item.value"
                    :value="item.value"
                  ></el-option>
                </el-select>
              </el-form-item>
            </div>

            <div class="col-sm-12 col-md-3 px-3">
              <el-form-item label="Role">
                <el-select v-model="form.selectedRole" placeholder="Liste des roles">
                  <el-option
                    v-for="item in roles"
                    :key="item.id"
                    :label="item.nom"
                    :value="item.id"
                  ></el-option>
                </el-select>
              </el-form-item>
            </div>

            <div class="col-sm-12 col-md-3 px-3">
              <el-form-item label="Fonction" prop="fonction">
                <el-input type="text" v-model="form.fonction" autocomplete="off"></el-input>
              </el-form-item>
            </div>
          </div>

          <div class="d-flex justify-content-end p-3">
            <el-button
              type="success"
              style="float: right; margin-left: 20px"
              @click="filterResults()"
            >
              <i class="fa fa-filter"></i> &nbsp;Filtrer
            </el-button>
            <el-button
              type="primary"
              class="btn btn-primary"
              style="float: right;"
              @click="resetFilters()"
            >
              <i class="fa fa-filter"></i> &nbsp;Réinitialiser
            </el-button>
          </div>
        </el-form>
        <!-- END filters -->

        <!-- START Utilisateurs table -->
        <ApolloQuery
          :query="require('../../graphql/administration/utilisateurs/utilisateurs.gql')"
          :variables="{
            limit: limit,
            offset: offset,
            nom: filters.nom,
            prenom: filters.prenom,
            email: filters.email,
            telephone: filters.telephone,
            role: filters.selectedRole,
            fonction: filters.fonction,
            entite: filters.selectedEntite,
            sortColumn: sortColumn,
            sortOrder: sortOrder
          }"
        >
          <template slot-scope="{ result:{ loading, error, data }, isLoading }">
            <div v-if="error">Une erreur est survenue !</div>
            <el-table
              v-loading="isLoading === 1"
              :data="tableData(data)"
              @row-click="navigateToExistingUserForm"
              @sort-change="sortTable"
              style="width: 100%"
            >
              <el-table-column sortable="custom" column-key="nom" prop="nom" label="Nom"></el-table-column>
              <el-table-column sortable="custom" column-key="prenom" prop="prenom" label="Prénom"></el-table-column>
              <el-table-column sortable="custom" column-key="email" prop="email" label="E-mail"></el-table-column>
              <el-table-column column-key="telephone" prop="telephone" label="Téléphone"></el-table-column>
              <el-table-column label="Entités">
                <template slot-scope="scope">
                  <p
                    v-if="scope.row.utilisateursRolesEntites.items.length === 1"
                  >{{ scope.row.utilisateursRolesEntites.items[0].entite.nom}}</p>
                  <el-popover
                    trigger="hover"
                    placement="top"
                    v-if="scope.row.utilisateursRolesEntites.items.length > 1"
                  >
                    <p
                      v-for="(i,e) in scope.row.utilisateursRolesEntites.items"
                      :key="e"
                    >{{ i.entite.nom }}</p>
                    <div slot="reference" class="name-wrapper">
                      {{scope.row.utilisateursRolesEntites.items.length}}
                      <i
                        class="el-icon-question"
                      ></i>
                    </div>
                  </el-popover>
                </template>
              </el-table-column>
              <el-table-column v-if="role === 'administrateur'" label="Status">
                <template slot-scope="scope">
                  {{scope.row.estActive ? 'Activé': 'Désactivé'}}
                </template>
              </el-table-column>
            </el-table>

            <el-pagination
              v-if="isLoading === 0"
              @current-change="handleCurrentChange"
              style="text-align: center"
              layout="prev, pager, next"
              :current-page="currentPage"
              :total="count(data)"
            ></el-pagination>
          </template>
        </ApolloQuery>
        <!-- END Utilisateurs table -->


      </div>
    </div>
  </back-wrapper>
</template>

<script>
import _ from "lodash";
import store from "../../store";
import { mapState } from "vuex";

const DEFAULT_SORT_COLUMN = "nom";
const DEFAULT_SORT_ORDER = "ASC";

export default {
  name: "utilisateurs",
  data() {
    return {
      limit: 10,
      offset: 0,
      sortColumn: DEFAULT_SORT_COLUMN,
      sortOrder: DEFAULT_SORT_ORDER,
      entites: [],
      roles: [],
      form: {
        nom: null,
        prenom: null,
        email: null,
        telephone: null,
        selectedEntite: null,
        selectedRole: null,
        fonction: null,
      },
      filters: {
        nom: null,
        prenom: null,
        email: null,
        telephone: null,
        selectedEntite: null,
        selectedRole: null,
        fonction: null,
      },
      currentPage: null
    };
  },
  created() {
    this.loadEntites();
    this.loadRoles();
  },
  computed: {
    ...mapState({
      selectedEntite: state => state.choixEntite.userSelectedId
    }),
    filtersEntites() {
      return this.entites.filter(function(e) {
        return e.value != null;
      });
    },
    role() {
      const estAdministrateurCentral = this.$store.getters["security/estAdministrateurCentral"];
      const estAdministrateurSimulation = this.$store.getters["security/estAdministrateurSimulation"];
      const isReferentEntite = this.$store.getters["security/isReferentEntite"];
      const isReferentEnsemble = this.$store.getters["security/isReferentEnsemble"];
      if (estAdministrateurCentral || estAdministrateurSimulation) {
        return 'administrateur';
      } else if(isReferentEnsemble) {
        return 'referentEnsemble';
      } else {
        return 'referentEntite';
      }
    }
  },
  watch: {
    selectedEntite(newVal) {
      this.form.selectedEntite = newVal.nom;
      this.filterResults();
    }
  },
  methods: {
    navigateToNewUserForm() {
      this.$router.push("/gestion/utilisateur");
    },
    navigateToExistingUserForm(utilisateur) {
      this.$router.push("/gestion/utilisateur/" + utilisateur.id);
    },
    sortTable(column) {
      if (_.isNil(column.prop)) {
        this.sortColumn = DEFAULT_SORT_COLUMN;
        this.sortOrder = DEFAULT_SORT_ORDER;
      } else {
        this.sortColumn = column.prop;
        this.sortOrder = column.order === "ascending" ? "ASC" : "DESC";
      }
    },
    tableData(data) {
      if (!_.isNil(data)) {
        return data.utilisateurs.items;
      } else {
        return [];
      }
    },
    count(data) {
      if (!_.isNil(data)) {
        return data.utilisateurs.count;
      }
      return 0;
    },
    handleCurrentChange(page) {
      this.offset = this.limit * (page - 1);
    },
    resetFilters() {
      this.form = {
        nom: null,
        prenom: null,
        email: null,
        telephone: null,
        selectedEntite: null,
        selectedRole: null,
        fonction: null,
      };
      this.filterResults();
    },
    filterResults: function() {
      this.filters = { ...this.form };
    },
    loadEntites() {
      this.$apollo
        .query({
          query: require("../../graphql/administration/entites/allEntites.gql")
        })
        .then(response => {
          for (let entite of response.data.allEntites) {
            this.entites.push({
              label: entite.id,
              value: entite.nom
            });
          }
          if (this.selectedEntite) {
            this.form.selectedEntite = this.selectedEntite.nom;
            this.filterResults();
          }
        });
    },
    loadRoles() {
      this.$apollo
        .query({
          query: require("../../graphql/administration/roles/allRoles.gql"),
        })
        .then(response => {
          if(response.data && response.data.allRoles) {
            const allRoles = response.data.allRoles;
            if (this.role === 'administrateur') {
              this.roles = allRoles;
            } else if (this.role === 'referentEnsemble') {
              this.roles = allRoles.filter(role => role.nom == 'Utilisateur' || role.nom == 'Référent entité');
            } else {
              this.roles = allRoles.filter(role => role.nom == 'Utilisateur');
            }
          }
        });
    }
  }
};
</script>
