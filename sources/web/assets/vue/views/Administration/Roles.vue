<template>
  <back-wrapper>
    <template v-slot:breadcrumb>
      <div>
        <el-breadcrumb separator-class="el-icon-arrow-right">
          <el-breadcrumb-item :to="{ path: '/administration' }">Administration</el-breadcrumb-item>
          <el-breadcrumb-item>Liste des rôles</el-breadcrumb-item>
        </el-breadcrumb>
      </div>
    </template>
    <div class="container px-5">
      <div class="admin-content-wrap">
        <div class="d-flex py-4 align-items-center">
		  <h1 class="admin-content-title mb-0">Liste des rôles</h1>
        <el-button
                type="primary"
                class="ml-auto"
                @click="navigateToNewRoleForm"
        >Ajouter un nouveau rôle</el-button>
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
              <el-form-item label="Droits">
                <el-select
                  v-model="form.selectedDroits"
                  multiple
                  placeholder="Liste des droits"
                  filterable
                  style="width: 450px"
                >
                  <el-option
                    v-for="item in droits"
                    v-if="item.value != null"
                    :key="item.value"
                    :label="item.label"
                    :value="item.value"
                  ></el-option>
                </el-select>
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

        <!-- START Roles table -->
        <ApolloQuery
          :query="require('../../graphql/administration/roles/roles.gql')"
          fetchPolicy="no-cache"
          :variables="{
				limit: limit,
				offset: offset,
				nom: filters.nom,
				description: filters.description,
				droits: filters.selectedDroits,
				sortColumn: sortColumn,
				sortOrder: sortOrder
			}"
        >
          <template slot-scope="{ result: { loading, error, data }, isLoading }">
            <div v-if="error">Une erreur est survenue !</div>
            <el-table
              v-loading="isLoading === 1"
              :data="tableData(data)"
              @row-click="navigateToExistingRoleForm"
              @sort-change="sortTable"
              style="width: 100%"
            >
              <el-table-column sortable="custom" column-key="nom" label="Nom du rôle" prop="nom"></el-table-column>
              <el-table-column
                sortable="custom"
                column-key="description"
                label="Description"
                prop="description"
              ></el-table-column>
              <el-table-column label="Status">
                <template slot-scope="scope">
                  {{scope.row.estVisible ? 'Activé': 'Désactivé'}}
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
        <!-- END Roles table -->
      </div>
    </div>
  </back-wrapper>
</template>

<script>
import _ from "lodash";

const DEFAULT_SORT_COLUMN = "nom";
const DEFAULT_SORT_ORDER = "ASC";
export default {
  name: "roles",
  data() {
    return {
      limit: 10,
      offset: 0,
      sortColumn: DEFAULT_SORT_COLUMN,
      sortOrder: DEFAULT_SORT_ORDER,
      filters: {
        nom: null,
        description: null,
        droits: [
          {
            value: null,
            label: null
          }
        ],
        selectedDroits: null
      },
      form: {
        nom: null,
        description: null,
        droits: [
          {
            value: null,
            label: null
          }
        ],
        selectedDroits: null
      },
      droits: [],
      currentPage: null
    };
  },
  created() {
    this.loadDroits();
  },
  methods: {
    navigateToNewRoleForm() {
      this.$router.push("/gestion/role");
    },
    navigateToExistingRoleForm(role) {
      this.$router.push("/gestion/role/" + role.id);
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
        return data.roles.items;
      } else {
        return [];
      }
    },
    count(data) {
      if (!_.isNil(data)) {
        return data.roles.count;
      }
      return 0;
    },
    handleCurrentChange(page) {
      this.offset = this.limit * (page - 1);
    },
    resetFilters() {
      this.form = {
        nom: null,
        description: null,
        selectedDroits: null
      };
      this.filterResults();
    },
    filterResults: function() {
      this.filters = { ...this.form };
    },
    loadDroits() {
      this.$apollo
        .query({
          query: require("../../graphql/administration/droits/droits.gql")
        })
        .then(response => {
          for (let droit of response.data.droits) {
            this.droits.push({
              label: droit.libelle,
              value: droit.id
            });
          }
        });
    }
  }
};
</script>
