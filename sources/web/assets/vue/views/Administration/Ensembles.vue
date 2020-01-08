<template>
  <back-wrapper>
    <template v-slot:breadcrumb>
      <div>
        <el-breadcrumb separator-class="el-icon-arrow-right">
          <el-breadcrumb-item :to="{ path: '/administration' }">Administration</el-breadcrumb-item>
          <el-breadcrumb-item>Liste des ensembles</el-breadcrumb-item>
        </el-breadcrumb>
      </div>
    </template>
    <div class="container px-5">
      <div class="admin-content-wrap">
        <div class="d-flex py-4 align-items-center">
          <h1 class="admin-content-title mb-0">Liste des ensembles</h1>
            <el-button
              v-if="isSupeAdmin"
              type="primary"
              class="ml-auto"
              @click="navigateToNewEnsembleForm"
            >Créer un ensemble</el-button>
        </div>
        <!-- START filters -->
        <el-form :model="filters" label-width="200px" ref="filtersForm" label-position="top">
          <div class="row">
            <div class="col-md-3 px-3 col-sm-12">
              <el-form-item label="Nom" prop="nom">
                <el-input type="text" v-model="form.nom" autocomplete="off"></el-input>
              </el-form-item>
            </div>
            <div class="col-md-3 px-3 col-sm-12">
              <el-form-item label="Description" prop="description">
                <el-input type="text" v-model="form.description" autocomplete="off"></el-input>
              </el-form-item>
            </div>
            <div class="col-md-3 px-3 col-sm-12">
              <el-form-item label="Créé par" prop="creePar">
                <el-input type="text" v-model="form.creePar" autocomplete="off"></el-input>
              </el-form-item>
            </div>
            <div class="col-md-3 px-3 col-sm-12">
              <el-form-item label="Modifié par" prop="modifiePar">
                <el-input type="text" v-model="form.modifiePar" autocomplete="off"></el-input>
              </el-form-item>
            </div>
            <div class="col-md-3 px-3 col-sm-12">
              <el-form-item label="Référents de l'ensemble" prop="referent">
                <el-input type="text" v-model="form.referent" autocomplete="off"></el-input>
              </el-form-item>
            </div>
            <div class="col-md-3 px-3 col-sm-12">
              <el-form-item label="Entités" prop="entite">
                <el-input type="text" v-model="form.entite" autocomplete="off"></el-input>
              </el-form-item>
            </div>
            <div class="col-md-3 col-sm-12 px-3 d-flex align-items-center" style="padding-top:8px">
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
          </div>
        </el-form>
        <!-- END filters -->

        <!-- START Ensembles table -->
        <ApolloQuery
          :query="require('../../graphql/administration/ensembles/ensembles.gql')"
          :variables="{
                    limit: limit,
                    offset: offset,
                    nom: filters.nom,
                    description: filters.description,
                    creePar: filters.creePar,
                    modifiePar: filters.modifiePar,
                    entite: filters.entite,
                    referent: filters.referent,
                    sortColumn: sortColumn,
                    sortOrder: sortOrder
                }"
        >
          <template slot-scope="{ result: { loading, error, data }, isLoading }">
            <div v-if="error">Une erreur est survenue !</div>
            <el-table
              v-loading="isLoading === 1"
              :data="tableData(data)"
              @row-click="navigateToExistingEnsembleForm"
              @sort-change="sortTable"
              style="width: 100%"
            >
              <el-table-column sortable="custom" column-key="nom" prop="nom" label="Nom"></el-table-column>
              <el-table-column
                sortable="custom"
                column-key="description"
                prop="description"
                label="Description"
              ></el-table-column>
              <el-table-column column-key="creePar" prop="creePar.nom" label="Créé par">
                <template slot-scope="scope">
                  <el-popover trigger="hover" placement="top">
                    <p>
                      <i class="far fa-user"></i>
                      {{ scope.row.creePar.prenom }} {{ scope.row.creePar.nom }}
                    </p>
                    <p>
                      <i class="far fa-envelope"></i>
                      {{ scope.row.creePar.email }}
                    </p>
                    <div
                      slot="reference"
                      class="name-wrapper"
                    >{{ scope.row.creePar.prenom }} {{ scope.row.creePar.nom }}</div>
                  </el-popover>
                </template>
              </el-table-column>
              <el-table-column
                sortable="custom"
                column-key="dateCreation"
                prop="dateCreation"
                label="Date de création"
              >
                <template slot-scope="scope">{{ scope.row.dateCreation | dateFR}}</template>
              </el-table-column>
              <el-table-column column-key="modifiePar" prop="modifiePar.nom" label="Modifié par">
                <template slot-scope="scope">
                  <el-popover trigger="hover" placement="top" v-if="scope.row.modifiePar">
                    <p>
                      <i class="far fa-user"></i>
                      {{ scope.row.modifiePar.prenom }} {{ scope.row.modifiePar.nom }}
                    </p>
                    <p>
                      <i class="far fa-envelope"></i>
                      {{ scope.row.modifiePar.email }}
                    </p>
                    <p>
                      <i class="far fa-calendar-alt"></i>
                      {{ scope.row.dateModification | dateFR}}
                    </p>
                    <div
                      slot="reference"
                      class="name-wrapper"
                    >{{ scope.row.modifiePar.prenom }} {{ scope.row.modifiePar.nom }}</div>
                  </el-popover>
                </template>
              </el-table-column>
              <el-table-column label="Entités">
                <template slot-scope="scope">
                  <p
                    v-if="scope.row.entitesByEnsemblesEntites.length === 1"
                  >{{ scope.row.entitesByEnsemblesEntites[0].nom }}</p>
                  <el-popover
                    trigger="hover"
                    placement="top"
                    v-if="scope.row.entitesByEnsemblesEntites.length > 1
                                        && scope.row.entitesByEnsemblesEntites.length <= 5"
                  >
                    <p
                      v-for="(entite,i) in scope.row.entitesByEnsemblesEntites"
                      :key="i"
                    >{{ entite.nom }}</p>
                    <div slot="reference" class="name-wrapper">
                      {{scope.row.entitesByEnsemblesEntites.length}}
                      <i class="el-icon-question"></i>
                    </div>
                  </el-popover>
                  <div
                    slot="reference"
                    class="name-wrapper"
                    v-if="scope.row.entitesByEnsemblesEntites.length > 5"
                    @click="$router.push('/gestion/entites?ensemble=' + scope.row.nom)"
                  >
                    {{scope.row.entitesByEnsemblesEntites.length}}
                    <i
                      class="fas fa-external-link-square-alt"
                    ></i>
                  </div>
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
        <!-- END Ensembles table -->
      </div>
    </div>
  </back-wrapper>
</template>

<script>
import _ from "lodash";
import moment from "moment";
import store from "../../store";
import { mapState } from "vuex";

const DEFAULT_SORT_COLUMN = "nom";
const DEFAULT_SORT_ORDER = "ASC";

export default {
  name: "ensembles",
  data() {
    return {
      limit: 10,
      offset: 0,
      sortColumn: DEFAULT_SORT_COLUMN,
      sortOrder: DEFAULT_SORT_ORDER,
      form: {
        nom: null,
        description: null,
        creePar: null,
        modifiePar: null,
        entite: null
      },
      filters: {
        nom: null,
        description: null,
        creePar: null,
        modifiePar: null,
        entite: null,
        referent: null
      },
      currentPage: null
    };
  },
  computed: {
    ...mapState({
      selectedEntite: state => state.choixEntite.userSelectedId
    }),
    isSupeAdmin() {
      return (
        this.$store.getters["security/estAdministrateurCentral"] ||
        this.$store.getters["security/estAdministrateurSimulation"]
      );
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
      this.form.entite = newVal.nom;
      this.filterResults();
    }
  },
  created() {
    if (this.$route.query.entite) {
      this.form.entite = this.$route.query.entite;
      this.filterResults();
    }
  },
  methods: {
    navigateToNewEnsembleForm() {
      this.$router.push("/gestion/ensemble");
    },
    navigateToExistingEnsembleForm(ensemble) {
      this.$router.push("/gestion/ensemble/" + ensemble.id);
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
        if (this.isSupeAdmin) {
          return data.ensembles.items;
        } else {
          return data.ensembles.items.filter(ensemble => {
            return ensemble.entitesByEnsemblesEntites.some(
              entite => entite.nom == this.selectedEntite.nom
            );
          });
        }
      } else {
        return [];
      }
    },
    count(data) {
      if (!_.isNil(data)) {
        return data.ensembles.count;
      }
      return 0;
    },
    handleCurrentChange(page) {
      this.currentPage = page;
      this.offset = this.limit * (page - 1);
    },
    resetFilters() {
      this.form = {
        nom: null,
        description: null,
        creePar: null,
        modifiePar: null,
        entite: null,
        referent: null
      };
      this.filterResults();
    },
    filterResults: function() {
      this.filters = { ...this.form };
    }
  },
  filters: {
    dateFR: function(value) {
      return value ? moment(String(value)).format("DD/MM/YYYY") : "";
    }
  }
};
</script>
