<template>
  <div class="admin-content-wrap simulations">
    <h1 class="admin-content-title">Gestion des simulations</h1>
    <!--Create modal-->
    <SimulationsCreate
      :show="dialogVisible"
      :close="closeCreateDialog"
      :list-annees="listAnnees"
      :list-entites="listEntites"
      :is-admin="isAdmin"
      :get-simulations="getSimulations"
      :get-current-entity-id="getCurrentEntityId"
    />
    <!--Share modal-->
    <SimulationsPartage
      :show="displayShareDialog"
      :close="closeShareDialog"
      :selected-simulations="selectedSimulations"
      :current-page="currentPage"
      :limit="limit"
      :sort-column="sortColumn"
      :offset="offset"
      :sort-order="sortOrder"
      :handle-current-change="handleCurrentChange"
      :get-simulations="getSimulations"
      :simulations-ensembles="simulationsEnsembles"
    />
    <!--Share History modal-->
    <SimulationsPartageHistorique
      :show="displayHistoryDialog"
      :close="closeHistoryDialog"
      :limit="limit"
      :offset="offset"
      :simulation-share-history="simulationShareHistory"
      :current-page="currentPage"
      :handle-current-change="handleCurrentChange"
    />
    <!--Notes modal-->
    <SimulationsNotes
      :show="displayNotesDialog"
      :close="closeNotesDialog"
      :current-simulation="currentSimulation"
    />
    <!--Notes modal-->
    <SimulationsFusion
      :show="displayFusionDialog"
      :close="closeFusionDialog"
      :selected-simulations="selectedSimulations"
      :get-simulations="getSimulations"
    />
    <!--Notes modal-->
    <SimulationsCopie
      :show="displayCopieDialog"
      :close="closeCopieDialog"
      :simulation-id="simulationIdToCopy"
      :get-simulations="getSimulations"
    />
      <div class="row d-flex justify-content-between align-items-center">
        <div class="col">
          <el-select class="shadowy block" v-model="filters.annee" placeholder="Année de référence">
            <el-option
                    v-for="item in listAnnees"
                    v-if="item.value != null"
                    :key="item.value"
                    :label="item.label"
                    :value="item.value"
            />
          </el-select>
        </div>
        <div class="col">
          <el-input placeholder="Code" class="shadowy" v-model="filters.code"/>
        </div>
        <div class="col">
          <el-input placeholder="Nom de la simulation" class="shadowy" v-model="filters.nom"/>
        </div>

        <div class="col ml-2">
          <div class="row p-0 m-0 action-btn-list">
            <div class="col-md-6 col-lg-6">
              <el-button
                class="outline thin-border block flex-grow-1 flex-shrink-1"
                @click="getSimulations"
              >Filtrer</el-button>
            </div>
            <div class="col-md-3 col-lg-5 d-flex">
              <el-button
                class="bg-dark text-white btn-icon block px-2"
                style="max-width: 60px; min-width: 30px;"
                @click="resetFilter"
              >
                <md-icon>refresh</md-icon>
              </el-button>
              <el-button
                class="bg-primary text-white btn-icon block px-2"
                style="max-width: 60px; min-width: 30px;"
                @click="showFilter = !showFilter"
              >
                <md-icon>filter_list</md-icon>
              </el-button>
            </div>
          </div>
        </div>
      </div>

      <template v-if="showFilter">
        <div class="bg-light-gray mt-3 p-3">
          <div class="my-4 d-flex justify-content-between">
            <div :md="7" class="col-sm-12 col-md-4 mr-auto">
              <el-select class="shadowy block" v-model="filters.entite" placeholder="Nom entité">
                <el-option
                        v-for="item in listEntites"
                        v-if="item.value != null"
                        :key="item.value"
                        :label="item.label"
                        :value="item.value"
                />
              </el-select>
            </div>
            <div class="col-sm-12 col-md-4">
              <el-select class="shadowy block" v-model="filters.ensemble" placeholder="Ensemble">
                <el-option
                        v-for="item in simulationsEnsembles"
                        v-if="item.value != null"
                        :key="item.value"
                        :label="item.label"
                        :value="item.value"
                />
              </el-select>
            </div>
            <div class="col-sm-12 col-md-4">
              <el-select
                class="shadowy block"
                v-model="filters.typeEntite"
                placeholder="Type Entités"
              >
                <el-option
                        v-for="item in entiteTypeOptions"
                        :key="item.value"
                        :label="item.value"
                        :value="item.value"
                />
              </el-select>
            </div>
          </div>

          <div class="row my-4">
            <div class="col-sm-12 col-md-4 col-lg-3">
              <el-checkbox v-model="checked.one">Simulations fusionnées</el-checkbox>
            </div>
            <div class="col-sm-12 col-md-4 col-lg-3">
              <el-checkbox v-model="checked.two">Simulations anonymisées</el-checkbox>
            </div>
          </div>
        </div>
      </template>
      <el-row class="mt-4 d-flex">
        <el-col :md="6">
          <el-button @click="showCreateModal" class="outline thin-border">Créer une simulation</el-button>
        </el-col>
        <el-col class="ml-auto" :md="16">
          <div class="d-flex">
            <el-button
              icon="share"
              class="block has-icon"
              :class="{'outline thin-border': selectedSimulations.length > 0}"
              :disabled="selectedSimulations.length === 0"
              @click="showPartagerDialog"
            >
              <md-icon>share</md-icon>Partager
            </el-button>
            <el-button
                icon="share"
                class="block has-icon"
                :class="{'outline thin-border': selectedSimulations.length === 2}"
                :disabled="selectedSimulations.length !== 2"
                @click="confirmFusion">
              <md-icon>merge_type</md-icon>Fusionner
            </el-button>
            <el-button disabled icon="share" class="block has-icon">
              <md-icon>filter_none</md-icon>Agréger
            </el-button>
            <el-button disabled icon="share" class="block">Comparer</el-button>
          </div>
        </el-col>
      </el-row>

      <el-row class="mt-5">
        <el-table
          v-loading="isLoading"
          :data="simulations"
          :default-sort="{prop: 'dateModification', order: 'descending'}"
          @cell-click="simulationCellClick"
          @selection-change="checkedSimulations"
          class="fw"
        >
          <el-table-column sortable="custom" type="selection" width="60" :selectable="canSelectRow"/>
          <el-table-column sortable="custom" prop="entite.code" label="Code" width="150">
             <template slot="header"><span title="Code">Code</span></template>
            <template slot-scope="scope">
              {{scope.row.simulation.anneeDeReference}}_[{{scope.row.simulation.entite.code}}]_{{scope.row.simulation.incrementation}}
            </template>
          </el-table-column>
          <el-table-column
            sortable="custom"
            prop="simulation.nom"
            label="Nom simulation"
            width="250"
          >
            <template slot="header"><span title="Nom simulation">Nom simulation</span></template>
          </el-table-column>
          <el-table-column
            sortable="custom"
            prop="simulation.entite.nom"
            label="Entité"
            width="180"
          >
             <template slot="header"><span title="Entité">Entité</span></template>
          </el-table-column>
          <el-table-column
            sortable="custom"
            prop="dateModification"
            label="Date de modification"
            width="200"
          >
            <template slot="header"><span title="Date de modification">Date de modification</span></template>
            <template slot-scope="scope">{{ scope.row.simulation.dateModification | dateFR}}</template>
          </el-table-column>
          <el-table-column sortable="custom" label="Modifié par" prop="modifierPar.nom" width="180">
            <template slot="header"><span title="Modifié par">Modifié par</span></template>
            <template slot-scope="scope">
              <div
                slot="reference"
                class="name-wrapper"
                v-if="scope.row.simulation.modifiePar!==null"
              >{{ scope.row.simulation.modifiePar.prenom }} {{ scope.row.simulation.modifiePar.nom }}</div>
            </template>
          </el-table-column>
          <el-table-column label="Informations">
            <template slot="header"><span title="Informations">Informations</span></template>
            <template slot-scope="scope">
              <div class="d-flex btn-icons">
                <el-button round class="p-0 text-black">
                  <md-icon v-if="scope.row.simulation.verrouillePar !== null">lock</md-icon>
                  <md-icon v-else>lock_open</md-icon>
                </el-button>
                <el-button round class="p-0 text-black">
                  <md-icon id="share">share</md-icon>
                </el-button>
                <el-button round class="p-0">
                  <md-icon>merge_type</md-icon>
                </el-button>
              </div>
            </template>
          </el-table-column>
          <el-table-column min-width="150" label="Actions">
            <template slot="header"><span title="Actions">Actions</span></template>
            <template slot-scope="scope">
              <div class="d-flex btn-icons-bg">
                <el-button class="bg-primary text-white" content="Dupliquer" round @click.stop="confirmDuplicate(scope.row.simulation.id)">
                  <md-icon>library_books</md-icon>
                </el-button>

                <el-tooltip class="item" effect="dark" content="Supprimer" placement="bottom-end">
                  <el-button class="bg-dark text-white" round @click.stop="deleteSimulation(scope.row.simulation.id)">
                    <md-icon>delete</md-icon>
                  </el-button>
                </el-tooltip>

                <el-tooltip v-if="isAdmin && scope.row.simulation.supprime" class="item" effect="dark" content="Activer" placement="bottom-end">
                  <el-button class="bg-success text-white" icon="el-icon-refresh-right" round
                             @click.stop="activateSimulation(scope.row.simulation.id)"/>
                </el-tooltip>
              </div>
            </template>
          </el-table-column>
        </el-table>
        <!--    Liste des simulations   -->
      </el-row>
    </div>
</template>
<script>
import SimulationsPartage from './components/SimulationsPartage';
import SimulationsPartageHistorique from './components/SimulationsPartageHistorique';
import SimulationsNotes from './components/SimulationsNotes';
import SimulationsCreate from './components/SimulationsCreate';
import SimulationsFusion from './components/SimulationsFusion';
import SimulationsCopie from './components/SimulationsCopie';
import "bootstrap/scss/bootstrap.scss";
import moment from "moment";
import store from "../../../store";
import { mapState } from "vuex";
import customValidator from "../../../utils/validation-rules";
import {getYearsFromCurrent} from '../../../utils/helpers';

const DEFAULT_SORT_COLUMN = "nom";
const DEFAULT_SORT_ORDER = "ASC";

export default {
  name: "Simulations",
  store,
  computed: {
    ...mapState({
      selectedId: state => state.choixEntite.userSelectedId
    }),
    queryBodies: function() {
      return [
        {
          name: "fetchEntites",
          queryPath: require("../../../graphql/administration/entites/entites.gql"),
          variables: {
            limit: 100,
            offset: 0,
            utilisateur: this.$store.getters[
              "security/estAdministrateurCentral"
            ]
              ? null
              : this.$store.getters["security/email"],
            sortColumn: DEFAULT_SORT_COLUMN,
            sortOrder: DEFAULT_SORT_ORDER
          }
        },
        {
          name: "allEnsembles",
          queryPath: require("../../../graphql/administration/ensembles/allEnsembles.gql"),
          variables: null
        }
      ];
    },
    isAdmin() {
      return !!(this.estAdministrateurCentral || this.estAdministrateurSimulation);
    }
  },
  components: {
    SimulationsPartage,
    SimulationsPartageHistorique,
    SimulationsNotes,
    SimulationsCreate,
    SimulationsFusion,
    SimulationsCopie
  },
  data() {
    return {
      limit: 10,
      offset: 0,
      showFilter: false,
      shareDialog: false,
      dialogVisible: false,
      displayShareDialog: false,
      displayHistoryDialog: false,
      displayNotesDialog: false,
      displayFusionDialog: false,
      displayCopieDialog: false,
      sortColumn: DEFAULT_SORT_COLUMN,
      sortOrder: DEFAULT_SORT_ORDER,
      simulations: [],
      partagers: [],
      loadingFilter: false,
      allSimulations: [],
      isLoading: false,
      listEntites: [],
      listAnnees: [],
      selectedSimulations: [],
      currentPage: null,
      simulationShareHistory: null,
      simulationsEnsembles: [],
      filters: {},
      form: {},
      rules: {
        annee: [{ required: true, message: 'L’année de référence doit être renseignée', trigger: 'change' }],
        entite: customValidator.getRule("required", "change"),
        nomSimulation: [
          { required: true, message: 'Veuillez saisir un nom de simulation', trigger: 'change' },
          customValidator.getRule("maxVarchar")
        ]
      },
      entiteTypeOptions: [
        { value: "Organisme" },
        { value: "Partenaire" },
        { value: "Holding" },
        { value: "Confédération" }
      ],
      checked: {
        one: true,
        two: false
      },
      isSubmittingForm: false,
      estAdministrateurCentral: null,
      estAdministrateurSimulation: null,
      currentSimulation: null,
      simulationIdToCopy: null
    };
  },
  created() {
    this.estAdministrateurCentral = this.$store.getters[
      "security/estAdministrateurCentral"
    ];
    this.estAdministrateurSimulation = this.$store.getters[
      "security/estAdministrateurSimulation"
    ];
    this.getSimulations();
    this.listAnnees = getYearsFromCurrent(2, 5)
    this.initFilter();
  },
  watch: {
    selectedId(newVal) {
      this.form.entite = newVal.id;
      this.filters.entite = newVal.id;
      this.getSimulations();
    }
  },
  methods: {
    initFilter() {
      this.filters = {
        nom: null,
        entite: this.selectedId && this.selectedId.id,
        ensemble: null,
        annee: null,
        typeEntite: null,
        code: null
      };
    },
    resetFilter() {
      this.initFilter();
      this.getSimulations();
    },
    runQueries() {
      this.listEntites = [];
      this.queryBodies.forEach(item => {
        this.$apollo
          .query({
            query: item.queryPath,
            variables: item.variables
          })
          .then(response => {
            switch (item.name) {
              case "fetchEntites":
                for (let entite of response.data.entites.items) {
                  this.listEntites.push({
                    label: entite.nom,
                    value: entite.id
                  });
                }
                break;
              case "allEnsembles":
                for (let ensemble of response.data.allEnsembles.items) {
                  this.simulationsEnsembles.push({
                    label: ensemble.nom,
                    value: ensemble.id
                  });
                }
                break;
            }
          });
      });
    },
    getSimulations() {
      this.isLoading = true;
      this.simulations = [];
      this.$apollo
        .query({
          query: require("../../../graphql/simulations/simulations.gql"),
          fetchPolicy: "no-cache",
          variables: {
            entiteID: this.filters.entite,
            anneeDeReference: this.filters.annee,
            nom: this.filters.nom,
            ensembleId: this.filters.ensemble,
            entiteType: this.filters.typeEntite,
            code: this.filters.code
          }
        })
        .then(response => {
          if (response.data && response.data.simulations) {
            if (!this.estAdministrateurCentral && this.selectedId) {
              let simulations = response.data.simulations.items;
              this.checkedSimulationByShare(simulations);
            } else {
                response.data.simulations.items.forEach(simulation => {
                  let data = {
                    partager: {
                      userType: "admin",
                      shareType: null
                    },
                    simulation: simulation
                  };
                  this.simulations.push(data);
                });
            }
            this.isLoading = false;
          }
        });
    },
    getCurrentEntityId(){
      return this.selectedId.id;
    },
    showPartagerDialog() {
      this.displayShareDialog = true;
      if (this.listEntites.length === 0) {
        this.runQueries(this.queryBodies);
      }
    },
    showCreateModal() {
      this.dialogVisible = true;
      if (this.listEntites.length === 0) {
        this.runQueries(this.queryBodies);
      }
    },
    checkedSimulationByShare(simulations) {
      this.$apollo
        .query({
          query: require("../../../graphql/administration/partagers/allPartagers.gql"),
          variables: {
            entiteID: this.selectedId.id
          }
        })
        .then(response => {
          this.partagers = response.data.allPartagers.items;
          this.modifySimulations(simulations);
        });
    },
    getFilteredEntites(query) {
      if (query !== '') {
        this.loadingFilter = true;
        this.$apollo
                .query({
                  query: require("../../../graphql/administration/entites/entites.gql"),
                  fetchPolicy: "no-cache",
                  variables: {
                    limit: 100,
                    offset: 0,
                    nom: query,
                    sortColumn: DEFAULT_SORT_COLUMN,
                    sortOrder: DEFAULT_SORT_ORDER
                  }
                })
                .then(response => {
                  if (response.data && response.data.entites.items) {
                    this.loadingFilter = false;
                    this.filteredEntites = response.data.entites.items.map(item => {
                      return {
                        label: item.nom,
                        value: item.id
                      }
                    });
                  }
                });
      } else {
        this.filteredEntites = [];
      }
    },
    getFilteredEnsembles(query) {
      if (query !== '') {
        this.$apollo
                .query({
                  query: require("../../../graphql/administration/ensembles/ensembles.gql"),
                  fetchPolicy: "no-cache",
                  variables: {
                    limit: 100,
                    offset: 0,
                    nom: query,
                    sortColumn: DEFAULT_SORT_COLUMN,
                    sortOrder: DEFAULT_SORT_ORDER
                  }
                })
                .then(response => {
                  if (response.data && response.data.ensembles.items) {
                    this.filteredEnsembles = response.data.ensembles.items.map(item => {
                      return {
                        label: item.nom,
                        value: item.id
                      }
                    });
                  }
                });
      } else {
        this.filteredEnsembles = [];
      }
    },
    modifySimulations(simulations) {
      simulations.forEach((item, index) => {
        let partagerData = this.checkOwner(item.id);
        let simulationData = {
          partager: partagerData,
          simulation: item
        };
        this.simulations.push(simulationData);
      });
    },
    checkOwner(simulationId) {
      let data = {
        userType: null,
        shareType: null
      };

      for (let i = 0; i < this.partagers.length; i++) {
        if (this.partagers[i].simulation.id === simulationId && this.partagers[i].owner.id === this.selectedId.id)
        {
          data = {
            userType: "owner",
            shareType: null
          };
        }
        else if ( (this.partagers[i].simulation.id === simulationId && this.partagers[i].entite.id === this.selectedId.id) ||
                  (this.partagers[i].owner.code === "ADMIN" && this.partagers[i].simulation.id === simulationId))
        {
          data = {
            userType: "shared",
            shareType: this.partagers[i].partageType
          };
        }
      }
      return data;
    },
    navigateToSelection(cell) {
      this.$store.commit("choixEntite/setUserType", cell.partager.userType);
      this.$router.push("/simulation/" + cell.simulation.id);
    },
    checkedSimulations(selection) {
      this.selectedSimulations = selection;
    },
    handleCurrentChange(val) {
      this.currentPage = val;
      this.offset = this.limit * (val - 1);
    },
    simulationCellClick(cell, row, column, event) {
      switch (event.target.id) {
        case "share":
          this.simulationShareHistory = cell;
          this.displayHistoryDialog = true;
          break;
        default:
          this.navigateToSelection(cell);
          break;
      }
    },
    canSelectRow(row, index) {
      return row.partager.userType !== "shared";
    },
    deleteSimulation(simulationId) {
      this.$confirm('Voulez-vous vraiment supprimer cette simulation?')
        .then(() => {
            this.$apollo
              .mutate({
                mutation: require("../../../graphql/simulations/deleteSimulation.gql"),
                variables: {
                  simulationId: simulationId
                }
              })
              .then(() => {
                this.getSimulations();
              });
        });
    },
    activateSimulation(simulationId) {
      this.$confirm('Voulez-vous vraiment activer cette simulation?')
        .then(() => {
            this.$apollo
              .mutate({
                mutation: require("../../../graphql/simulations/activateSimulation.gql"),
                variables: {
                  simulationId: simulationId
                }
              })
              .then(() => {
                this.getSimulations();
              });
        });
    },
    confirmFusion() {
      this.$confirm('Voulez-vous vraiment fusionner ces deux simulations?')
              .then(() => {
                const simulation1 = this.selectedSimulations[0];
                const simulation2 = this.selectedSimulations[1];
                if (simulation1.simulation.entite.id !== simulation2.simulation.entite.id) {
                  this.$message({
                    showClose: true,
                    message: 'Vous ne pouvez pas fusionnez deux simulations avec une entité différente',
                    type: 'error',
                    duration: 10000
                  });
                  return;
                }

                if (simulation1.simulation.anneeDeReference !== simulation2.simulation.anneeDeReference) {
                  this.$message({
                    showClose: true,
                    message: 'Vous ne pouvez pas fusionnez deux simulations avec une année de référence différente',
                    type: 'error',
                    duration: 10000
                  });
                  return;
                }
                this.displayFusionDialog = true;
              });
    },
    confirmDuplicate (simulationId) {
      this.$confirm('Voulez-vous vraiment dupliquer cette simulation?')
        .then(() => {
          this.displayCopieDialog = true
          this.simulationIdToCopy = simulationId
        })
        .catch(() => {
          this.$message('Vous avez refusé la duplication.')
        })
    },
    closeShareDialog () {
      this.displayShareDialog = false;
      this.simulationsEnsembles = [];
    },
    closeHistoryDialog () {
      this.displayHistoryDialog = false;
    },
    closeNotesDialog () {
      this.displayNotesDialog = false;
    },
    closeCreateDialog () {
      this.dialogVisible = false;
    },
    closeFusionDialog () {
      this.displayFusionDialog = false;
    },
    closeCopieDialog () {
      this.displayCopieDialog= false;
    }
  },
  filters: {
    dateFR(value) {
      return value ? moment(String(value)).format("DD/MM/YYYY") : "";
    }
  },
};
</script>

<style lang="scss">
.el-table{
  max-width: unset;
}
.share-dialog {
  .el-dialog__header {
    padding: 40px;
  }

  .el-dialog__body {
    padding-top: 0;

    .shareForm-select {
      width: 100%;
    }

    .form-control {
      font-size: 12px;
      background: white;
      box-sizing: border-box;
      height: 40px;
      line-height: 40px;
      outline: 0;
      padding: 0 15px;
      border: 1px solid lightgray;
    }

    .filter-title {
      color: #000;
      line-height: 25px;
      font-weight: 500;
      font-size: 16px;
    }
  }
}
.simulation-note-dialog {
  .el-dialog {
    margin-top: 5%;
  }
  .simulation-note {
    white-space: pre-line;
  }
  .simulation-notes-container {
    overflow-y: scroll;
    max-height: 30em;
    border: 2px solid whitesmoke;
  }
  .note-action {
    cursor: pointer;
  }
}
</style>
