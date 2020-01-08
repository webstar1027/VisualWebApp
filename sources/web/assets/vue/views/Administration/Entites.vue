<template>
  <back-wrapper>
    <template v-slot:breadcrumb>
      <div>
        <el-breadcrumb separator-class="el-icon-arrow-right">
          <el-breadcrumb-item :to="{ path: '/administration' }">Administration</el-breadcrumb-item>
          <el-breadcrumb-item>Liste des Entités</el-breadcrumb-item>
        </el-breadcrumb>
      </div>
    </template>
    <div class="container px-5">
      <div class="admin-content-wrap">
        <div class="d-flex py-4 align-items-center">
        <h1 class="admin-content-title mb-0">Liste des Entités</h1>
        <el-button
                v-if="isSuperAdmin"
                type="primary"
                class="ml-auto"
                @click="navigateToNewEntiteForm"
        >Ajouter une nouvelle entité</el-button>
      </div>
        <!-- START filters -->
        <el-form :model="filters" label-width="200px" ref="filtersForm" label-position="top">
          <div class="row">
            <div class="col-md-3 col-sm-12 px-3">
              <el-form-item label="Nom" prop="nom">
                <el-input type="text" v-model="form.nom" autocomplete="off"></el-input>
              </el-form-item>
            </div>
            <div class="col-md-3 col-sm-12 px-3">
              <el-form-item label="Type d'entité" prop="type">
                <el-input type="text" v-model="form.type" autocomplete="off"></el-input>
              </el-form-item>
            </div>
            <div class="col-md-3 col-sm-12 px-3">
              <el-form-item label="Code entité" prop="code">
                <el-input type="text" v-model="form.code" autocomplete="off"></el-input>
              </el-form-item>
            </div>
            <div class="col-md-3 col-sm-12 px-3">
              <el-form-item label="Type d'organisme" prop="typeOrganisme">
                <el-input type="text" v-model="form.typeOrganisme" autocomplete="off"></el-input>
              </el-form-item>
            </div>

            <template v-if="filtersExpanded">
              <div class="col-md-3 col-sm-12 px-3">
                <el-form-item label="Code organisme" prop="codeOrganisme">
                  <el-input type="text" v-model="form.codeOrganisme" autocomplete="off"></el-input>
                </el-form-item>
              </div>
              <div class="col-md-3 col-sm-12 px-3">
                <el-form-item label="Créé par" prop="creePar">
                  <el-input type="text" v-model="form.creePar" autocomplete="off"></el-input>
                </el-form-item>
              </div>
              <div class="col-md-3 col-sm-12 px-3">
                <el-form-item label="Modifié par" prop="modifiePar">
                  <el-input type="text" v-model="form.modifiePar" autocomplete="off"></el-input>
                </el-form-item>
              </div>
              <div class="col-md-3 col-sm-12 px-3">
                <el-form-item label="Siren" prop="siren">
                  <el-input type="text" v-model="form.siren" autocomplete="off"></el-input>
                </el-form-item>
              </div>
              <div class="col-md-3 col-sm-12 px-3">
                <el-form-item label="Utilisateur" prop="utilisateur">
                  <el-input type="text" v-model="form.utilisateur" autocomplete="off"></el-input>
                </el-form-item>
              </div>
              <div class="col-md-3 col-sm-12 px-3">
                <el-form-item label="Référent" prop="referent">
                  <el-input type="text" v-model="form.referent" autocomplete="off"></el-input>
                </el-form-item>
              </div>
              <div class="col-md-3 col-sm-12 px-3">
                <el-form-item label="Ensemble" prop="ensemble">
                  <el-input type="text" v-model="form.ensemble" autocomplete="off"></el-input>
                </el-form-item>
              </div>
             </template>

            <div class="col-md-3 col-sm-12 px-3 d-flex align-items-center" style="padding-top:8px">
               <el-button
              type="success"
              @click="filterResults()"
            >
              <i class="fa fa-filter"></i> &nbsp;Filtrer
            </el-button>
              <el-button
              type="primary"
              class="btn btn-primary"
              @click="resetFilters()"
            >
              <i class="fa fa-filter"></i> &nbsp;Réinitialiser
            </el-button>
            </div>

          </div>

          <div class="d-flex justify-content-end p-3">
           <button type="button" class="btn btn-sm" @click="filtersExpanded = !filtersExpanded"> {{filtersExpanded ? `Moins` : `Plus`}} <i class="fa fa-fw ml-2" :class="filtersExpanded ? 'fa-minus' : 'fa-plus'" aria-hidden="true"></i> </button>
          </div>
        </el-form>
        <!-- END filters -->

        <!-- START Entites table -->
        <ApolloQuery
          :query="require('../../graphql/administration/entites/entites.gql')"
          :variables="{
				limit: limit,
				offset: offset,
				nom: filters.nom,
				siren: filters.siren,
				type: filters.type,
				code: filters.code,
				typeOrganisme: filters.typeOrganisme,
				codeOrganisme: filters.codeOrganisme,
				creePar: filters.creePar,
				modifiePar: filters.modifiePar,
				utilisateur: filters.utilisateur,
				referent: filters.referent,
				ensemble: filters.ensemble,
				sortColumn: sortColumn,
				sortOrder: sortOrder
			}"
        >
          <template slot-scope="{ result: { loading, error, data }, isLoading }">
            <div v-if="error">Une erreur est survenue !</div>
            <el-table
              v-loading="isLoading === 1"
              :data="tableData(data)"
              @row-click="navigateToExistingEntiteForm"
              @sort-change="sortTable"
              style="width: 100%"
            >
              <el-table-column sortable="custom" column-key="nom" prop="nom" label="Nom"></el-table-column>
              <el-table-column
                sortable="custom"
                column-key="type"
                prop="type"
                label="Type d'entité"
              ></el-table-column>
              <el-table-column
                sortable="custom"
                column-key="typeOrganisme"
                prop="typeOrganisme"
                label="Type d'organisme"
              ></el-table-column>
              <el-table-column sortable="custom" column-key="code" prop="code" label="Code entité"></el-table-column>
              <el-table-column
                sortable="custom"
                column-key="codeOrganisme"
                prop="codeOrganisme"
                label="Code organisme"
              ></el-table-column>
              <el-table-column sortable="custom" column-key="siren" prop="siren" label="Siren"></el-table-column>
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
                    <p>
                      <i class="far fa-calendar-alt"></i>
                      {{ scope.row.dateCreation | dateFR}}
                    </p>
                    <div
                      slot="reference"
                      class="name-wrapper"
                    >{{ scope.row.creePar.prenom }} {{ scope.row.creePar.nom }}</div>
                  </el-popover>
                </template>
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
              <el-table-column v-if="role === 'administrateur'" label="Status">
                <template slot-scope="scope">
                  {{scope.row.estActivee ? 'Activé': 'Désactivé'}}
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
        <!-- END Entites table -->
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
  name: "entites",
  data() {
    return {
      limit: 10,
      offset: 0,
      sortColumn: DEFAULT_SORT_COLUMN,
      sortOrder: DEFAULT_SORT_ORDER,
      form: {
        nom: null,
        siren: null,
        code: null,
        type: null,
        codeOrganisme: null,
        typeOrganisme: null,
        creePar: null,
        modifiePar: null,
        utilisateur: null,
        referent: null,
        ensemble: null
      },
      filters: {
        nom: null,
        siren: null,
        code: null,
        type: null,
        codeOrganisme: null,
        typeOrganisme: null,
        creePar: null,
        modifiePar: null,
        utilisateur: null,
        referent: null,
        ensemble: null
      },
      currentPage: null,
      filtersExpanded:false,
    };
  },
  computed: {
    ...mapState({
      selectedEntite: state => state.choixEntite.userSelectedId
    }),
    isSuperAdmin() {
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
      this.filterResults();
    }
  },
  created() {
    if (this.$route.query.ensemble) {
      this.form.ensemble = this.$route.query.ensemble;
      this.filterResults();
    }
  },
  methods: {
    navigateToNewEntiteForm() {
      this.$router.push("/gestion/entite");
    },
    navigateToExistingEntiteForm(entite) {
      this.$router.push("/gestion/entite/" + entite.id);
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
      const estAdministrateurCentral = this.$store.getters[
        "security/estAdministrateurCentral"
      ];
      if (!_.isNil(data)) {
        if (estAdministrateurCentral) {
          return data.entites.items;
        } else {
          return data.entites.items.filter(
            entite => entite.id == this.selectedEntite.id
          );
        }
      } else {
        return [];
      }
    },
    count(data) {
      if (!_.isNil(data)) {
        return data.entites.count;
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
        siren: null,
        code: null,
        type: null,
        codeOrganisme: null,
        typeOrganisme: null,
        creePar: null,
        modifiePar: null,
        utilisateur: null,
        referent: null,
        ensemble: null
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
<style lang="scss" scoped>
.el-button{
  float: unset !important;
}

</style>