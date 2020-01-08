<template>
  <div class="charges-maintenance admin-content-wrap">
		  <div class="custom-collapsible-title">
			  <div>
				  <h1 class="admin-content-title">Maintenance</h1>
			  </div>
			  <div class="end">
				  <el-upload
						  ref="upload"
						  :action.native="'/import-charges-maintenance/'+ simulationID"
						  multiple
						  :limit="10"
						  :disabled="!isModify"
						  :on-success="onSuccessImport"
						  :on-error="onErrorImport"
				  >
					  <el-button size="small" type="primary">Importer</el-button>
				  </el-upload>
				  <el-button type="success" :disabled="!isModify" @click.stop="exportChargesMaintenance">Exporter</el-button>
			  </div>
	  </div>
    <ApolloQuery
      :query="require('../../../../../graphql/simulations/charges/maintenance/maintenance.gql')"
      :variables="{simulationId: simulationID}"
    >
      <template slot-scope="{ result:{ loading, error, data }, isLoading , query}">
		  <el-collapse v-model="activeList">
			  <el-collapse-item title="Récapitulatif de la maintenance et du gros entretien" name="recap">
				  <template v-slot:title>

				  </template>
				  <el-table v-loading="recapListIsLoading" :data="recapList" style="width: 100%">
					  <el-table-column
							  sortable
							  column-key="type"
							  prop="type"
							  width="250"
							  label="Récapitulatif de la maintenance"
							  fixed
					  ></el-table-column>
					  <el-table-column
							  v-for="column in columns"
							  sortable
							  align="center"
							  :key="column.prop"
							  :prop="column.prop"
							  :label="column.label"
					  ></el-table-column>
				  </el-table>
			  </el-collapse-item>
		  </el-collapse>
        <el-tabs v-model="activeTab">
          <el-tab-pane
            v-for="(tab, i) in types"
            :label="tab.label"
            :name="`${parseInt(i)+1}`"
            :key="i"
          >
            <el-collapse v-model="activeList">
              <el-collapse-item :title="tab.title" name="detail" class="mt-3">
                <div v-if="error">Une erreur est survenue !</div>
                <el-table
                  v-loading="isLoading"
                  :default-sort="{prop: 'numero', order: 'ascending'}"
                  :data="tableData(data, i, query)"
                  style="width: 100%"
                >
                  <el-table-column
                    sortable
                    column-key="numero"
                    prop="numero"
                    width="180"
                    align="center"
                    label="Numéro de catégorie"
                  ></el-table-column>
                  <el-table-column
                    sortable
                    column-key="nom"
                    prop="nom"
                    width="180"
                    label="Nom de la catégorie"
                    fixed
                  ></el-table-column>
                  <el-table-column
                    sortable
                    column-key="regie"
                    prop="regie"
                    width="80"
                    align="center"
                    label="Régie"
                  >
                    <template slot-scope="scope">
                      <p v-if="scope.row.regie">Oui</p>
                      <p v-else>Non</p>
                    </template>
                  </el-table-column>
                  <el-table-column
                    sortable
                    column-key="nature"
                    prop="nature"
                    width="100"
                    align="center"
                    label="Conso / FP"
                  >
                    <template slot-scope="scope">
                      <p v-if="scope.row.nature === 0">Conso</p>
                      <p v-if="scope.row.nature === 1">FP</p>
                      <p v-else></p>
                    </template>
                  </el-table-column>
                  <el-table-column
                    sortable
                    column-key="indexation"
                    prop="indexation"
                    width="100"
                    align="center"
                    label="Indexation"
                  >
                    <template slot-scope="scope">
                      <p v-if="scope.row.indexation">Oui</p>
                      <p v-else>Non</p>
                    </template>
                  </el-table-column>
                  <el-table-column
                    sortable
                    column-key="tauxDevolution"
                    prop="tauxDevolution"
                    width="140"
                    align="center"
                    label="Taux d'évolution"
                  ></el-table-column>
                  <el-table-column
                    v-for="column in columns"
                    sortable
                    align="center"
                    :key="column.prop"
                    :prop="column.prop"
                    :label="column.label"
                  ></el-table-column>
                  <el-table-column fixed="right" width="120" label="Actions">
                    <template slot-scope="scope">
                      <el-button
                        type="primary"
                        :disabled="!isModify"
                        icon="el-icon-edit"
                        circle
                        @click="handleEdit(scope.$index, scope.row, i)"
                      ></el-button>
                      <el-button
                        type="danger"
                        :disabled="!isModify"
                        icon="el-icon-delete"
                        circle
                        @click="handleDelete(scope.$index, scope.row, i)"
                      ></el-button>
                    </template>
                  </el-table-column>
                </el-table>
                <el-row class="d-flex justify-content-end my-3 pr-3">
                  <el-button
                    type="primary"
                    :disabled="!isModify"
                    @click="showCreateModal(i)"
                  >{{buttonTitle}}</el-button>
                </el-row>
              </el-collapse-item>
            </el-collapse>
          </el-tab-pane>
        </el-tabs>
      </template>
    </ApolloQuery>

    <el-dialog :title="dialogTitle" :visible.sync="dialogVisible" :close-on-click-modal="false" width="70%">
      <el-row v-if="isEdit" class="form-slider text-center mb-5">
        <i class="el-icon-back font-weight-bold" @click="back"></i>
        <i class="el-icon-right font-weight-bold" @click="next"></i>
      </el-row>

      <el-form :model="form" :rules="formRules" label-width="165px" ref="couranteForm">
        <el-row type="flex" justify="space-around">
          <el-col :span="12">
            <el-form-item label="Numéro de catégorie:" prop="numero">
              <el-input
                type="text"
                v-model="form.numero"
                placeholder="Numéro de catégorie"
                class="text-input"
                :disabled="true"
              ></el-input>
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="Nom de la catégorie:" prop="nom">
              <el-input
                type="text"
                v-model="form.nom"
                placeholder="Nom de la catégorie"
                class="text-input"
              ></el-input>
            </el-form-item>
          </el-col>
        </el-row>

        <el-row type="flex" justify="space-around">
          <el-col :span="12">
            <el-form-item>
              <el-checkbox v-model="form.regie" label="Régie" name="regie" @change="changeRegie"></el-checkbox>
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="Consommation/Frais de personnel:" prop="type">
              <el-select v-model="form.type" :disabled="!form.regie">
                <el-option
                  v-for="item in consoTypes"
                  :key="item.value"
                  :label="item.label"
                  :value="item.value"
                ></el-option>
              </el-select>
            </el-form-item>
          </el-col>
        </el-row>

        <el-row type="flex" justify="space-around">
          <el-col :span="12">
            <el-form-item>
              <el-checkbox v-model="form.indexation" label="Indexation" name="indexation"></el-checkbox>
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item prop="tauxDevolution">
              <template slot="label">
                <span>Taux d'évolution</span>
                <el-tooltip class="item" effect="dark" content="Taux d'évolution" placement="top">
                  <i class="el-icon-info"></i>
                </el-tooltip>
              </template>
              <el-input
                type="text"
                placeholder="%"
                class="taux-input"
                v-model="form.tauxDevolution"
                :disabled="!form.indexation"
                @change="() => {form = Object.assign({}, form ,{'tauxDevolution' : mathInput(form.tauxDevolution)})}"
              ></el-input>
            </el-form-item>
          </el-col>
        </el-row>
        <periodique
          :anneeDeReference="anneeDeReference"
          v-model="form.periodiques"
          @onChange="periodicOnChange"
        ></periodique>
      </el-form>

      <div slot="footer" class="dialog-footer">
        <el-button type="outline-primary" @click="dialogVisible = false">Retour</el-button>
        <el-button type="primary" @click="submit('couranteForm')" :disabled="isSubmitting">Valider</el-button>
      </div>
    </el-dialog>
  </div>
</template>

<script>
import customValidator from "../../../../../utils/validation-rules";
import {
  isFloat,
  initPeriodic,
  mathInput,
  checkPeriodic,
  periodicFormatter
} from "../../../../../utils/inputs";
import { updateData, getIncremental } from "../../../../../utils/helpers";
import Periodique from "../../../../../components/partials/Periodique";

export default {
  name: "Maintenance",
  components: { Periodique },
  data() {
    return {
      simulationID: null,
      anneeDeReference: null,
      activeList: ["detail"],
      dialogVisible: false,
      isEdit: false,
      selectedIndex: null,
      selectedType: 0,
      activeTab: null,
      recapList: null,
      recapListIsLoading: true,
      columns: [],
      consommationList: [],
      personnelList: [],
      maintenances: [],
      query: null,
      inputError: false,
      formRules: {
        nom: [
          customValidator.getRule("requiredNoWhitespaces"),
          customValidator.getRule("maxVarchar")
        ],
        numero: customValidator.getPreset("number.positiveInt"),
        tauxDevolution: customValidator.getPreset("taux")
      },
      consoTypes: [
        {
          value: 0,
          label: "Consommation"
        },
        {
          value: 1,
          label: "Frais de personnel"
        }
      ],
      isSubmitting: false,
      form: {},
      types: {
        0: {
          label: "Détail de la maintenance",
          title:
            "Détail de la maintenance courante du partrimonie de référence y compris régie"
        },
        1: {
          label: "Détail du gros entretien",
          title:
            "Détail d’une charge de gros entretien du patrimoine de référence y compris régie"
        }
      },
      currentLastNumber: 0,
      buttonTitle: ''
    };
  },
  created() {
    let simulationID = _.isNil(this.$route.params.id)
      ? null
      : this.$route.params.id;
    if (_.isNil(simulationID)) {
      return;
    }
    this.simulationID = simulationID;

    this.activeTab = _.isNil(this.$route.query.tab)
      ? "1"
      : this.$route.query.tab;

    this.init();
    this.getAnneeDeReference();
    this.$apollo
      .query({
        query: require("../../../../../graphql/administration/partagers/checkStatus.gql"),
        variables: {
          simulationID: this.simulationID
        }
      })
      .then(response => {
        this.$store.commit('choixEntite/setModify', response.data.checkStatus);
      });
  },
  computed: {
    dialogTitle() {
      if (this.activeTab === 1) {
        return "Créer une charge de maintenance courante";
      } else {
        return "Créer une charge de gros entretien";
      }
    },
    isModify() {
      return this.$store.getters['choixEntite/isModify'];
    }
  },
  methods: {
    isFloat: isFloat,
    periodicFormatter: periodicFormatter,
    mathInput: mathInput,
    init() {
      this.recapList = [
        {
          type:
            "Maintenance courante y compris les frais de personnel en régie",
          periodiques: { maintenances: initPeriodic() }
        },
        {
          type: "Gros entretien y compris les frais de personnel en régie",
          periodiques: { maintenances: initPeriodic() }
        },
        { type: "Total", periodiques: { maintenances: initPeriodic() } }
      ];
      this.initForm();
    },
    initForm(type = 0) {
      this.form = {
        id: null,
        nom: "",
        numero: getIncremental(this.maintenances, 'numero'),
        regie: false,
        type: type,
        nature: 0,
        indexation: false,
        tauxDevolution: 0,
        periodiques: {
          maintenances: initPeriodic()
        }
      };
    },
    getAnneeDeReference() {
      this.$apollo
        .query({
          query: require("../../../../../graphql/simulations/simulation.gql"),
          variables: {
            simulationID: this.simulationID
          }
        })
        .then(res => {
          if (res.data && res.data.simulation) {
            this.anneeDeReference = res.data.simulation.anneeDeReference;
            this.setTableColumns();
          }
        });
    },
    setTableColumns() {
      this.columns = [];
      for (var i = 0; i < 50; i++) {
        this.columns.push({
          label: (parseInt(this.anneeDeReference) + i).toString(),
          prop: `periodiques.maintenances[${i}]`
        });
      }
    },
    tableData(data, type, query) {
      if (!_.isNil(data)) {
        this.query = query;
        const maintenances = data.maintenance.items.map(item => {
          let periodiques = [];
          item.maintenancePeriodique.items.forEach(periodique => {
            periodiques[periodique.iteration - 1] = periodique.value;
          });
          return {
            id: item.uuid,
            nom: item.nom,
            numero: item.numero,
            indexation: item.indexation,
            regie: item.regie,
            type: item.type,
            nature: item.nature,
            tauxDevolution: item.tauxDevolution,
            periodiques: {
              maintenances: periodiques
            }
          };
        });

        this.maintenances = maintenances;
        this.consommationList = maintenances.filter(item => item.type === 0);
        this.personnelList = maintenances.filter(item => item.type === 1);

        this.getRecapList(maintenances);

        return maintenances.filter(item => item.type === parseInt(type));
      } else {
        return [];
      }
    },
    getRecapList(data) {
      for (var i = 0; i < 2; i++) {
        let recapList = [];
        let groups = data.filter(record => record.type === i);
        if (groups.length > 0) {
          groups.forEach(group => {
            group.periodiques.maintenances.forEach((item, index) => {
              if (!recapList[index]) {
                recapList[index] = 0;
              }
              recapList[index] += item;
            });
          });
          this.recapList[i].periodiques.maintenances = recapList;
        }
      }

      // Get total sum.
      let recapList = [];
      data.forEach(group => {
        group.periodiques.maintenances.forEach((item, index) => {
          if (!recapList[index]) {
            recapList[index] = 0;
          }
          recapList[index] += item;
        });
      });
      this.recapList[2].periodiques.maintenances = recapList;
      this.recapListIsLoading = false;
    },
    showCreateModal(type) {
      this.initForm(type);
      this.dialogVisible = true;
      this.isEdit = false;
    },
    handleEdit(index, row, type) {
      this.dialogVisible = true;
      this.form = { ...row };
      this.selectedIndex = index;
      this.selectedType = type;
      this.isEdit = true;
    },
    handleDelete(index, row, type) {
      this.$confirm("Êtes-vous sûr(e) de vouloir supprimer cette maintenance ?")
        .then(_ => {
          this.$apollo
            .mutate({
              mutation: require("../../../../../graphql/simulations/charges/maintenance/removeMaintenance.gql"),
              variables: {
                maintenanceUUId: row.id,
                simulationId: this.simulationID
              }
            })
            .then(() => {
              updateData(this.query, this.simulationID).then(() => {
                this.$message({
                  showClose: true,
                  message: "La maintenance a bien été supprimée.",
                  type: "success"
                });
              });
            })
            .catch(error => {
              updateData(this.query, this.simulationID).then(() => {
                this.$message({
                  showClose: true,
                  message: error.networkError.result,
                  type: "error"
                });
              });
            });
        })
        .catch(_ => {});
    },
    submit(formName) {
      this.$refs[formName].validate(valid => {
        if (valid && checkPeriodic(this.form.periodiques.maintenances)) {
          this.isSubmitting = true;
          this.$apollo
            .mutate({
              mutation: require("../../../../../graphql/simulations/charges/maintenance/saveMaintenance.gql"),
              variables: {
                maintenance: {
                  uuid: this.form.id,
                  simulationId: this.simulationID,
                  nom: this.form.nom,
                  indexation: this.form.indexation,
                  nature: this.form.nature,
                  type: parseInt(this.activeTab) - 1,
                  regie: this.form.regie,
                  taux_devolution: this.form.tauxDevolution,
                  periodique: JSON.stringify({
                    periodique: this.form.periodiques.maintenances
                  })
                }
              }
            })
            .then(() => {
              this.isSubmitting = false;
              this.dialogVisible = false;
              updateData(this.query, this.simulationID).then(() => {
                this.$message({
                  showClose: true,
                  message: "La maintenance a bien été enregistrée.",
                  type: "success"
                });
              });
            })
            .catch(error => {
              this.isSubmitting = false;
              this.$message({
                showClose: true,
                message: error.networkError.result,
                type: "error"
              });
            });
        } else {
          this.showError();
        }
      });
    },
    changeRegie(regie) {
      if (regie === false) {
        this.form.type = null;
      }
    },
    back() {
      if (this.selectedIndex > 0) {
        this.selectedIndex--;
        if (this.selectedType === 0) {
          this.form = { ...this.consommationList[this.selectedIndex] };
        } else {
          this.form = { ...this.personnelList[this.selectedIndex] };
        }
      }
    },
    next() {
      if (this.selectedType === 0) {
        if (this.selectedIndex < this.consommationList.length - 1) {
          this.selectedIndex++;
          this.form = { ...this.consommationList[this.selectedIndex] };
        }
      } else {
        if (this.selectedIndex < this.personnelList.length - 1) {
          this.selectedIndex++;
          this.form = { ...this.personnelList[this.selectedIndex] };
        }
      }
    },
    periodicOnChange(type) {
      let newPeriodics = this.form.periodiques[type];
      this.form.periodiques[type] = [];
      this.form.periodiques[type] = periodicFormatter(newPeriodics);
    },
    changeTab() {
      this.$router.push({
        path: "maintenance",
        query: { tab: this.activeTab }
      });
    },
    exportChargesMaintenance() {
      window.location.href = "/export-charges-maintenance/" + this.simulationID;
    },
    onSuccessImport(res) {
      this.$toasted.success(res, {
        theme: "toasted-primary",
        icon: "check",
        position: "top-right",
        duration: 5000
      });
      this.$refs.upload.clearFiles();
      updateData(this.query, this.simulationID);
    },
    onErrorImport(error) {
      this.$toasted.error(error.message, {
        theme: "toasted-primary",
        icon: "error",
        position: "top-right",
        duration: 5000
      });
      this.$refs.upload.clearFiles();
    },
    showError() {
      if (!this.inputError) {
        this.inputError = true;
        this.$message({
          showClose: true,
          message: "Les valeurs entrées doivent être valides.",
          type: "error",
          onClose: () => {
            this.inputError = false;
          }
        });
      }
    }
  },
  watch: {
    activeTab(newVal, oldVal) {
      if (oldVal) {
        this.changeTab();
      }
      if (newVal === '1') {
        this.buttonTitle = "Créer une charge de maintenance courante";
      } else {
        this.buttonTitle = "Créer une charge de gros entretien";
      }
    }
  }
};
</script>

<style type="text/css">
.charges-maintenance .el-collapse-item__header {
  padding-left: 15px;
  font-weight: bold;
  font-size: 15px;
}
.charges-maintenance .el-button--small {
  font-weight: 500;
  padding: 0 15px;
  height: 40px;
  font-size: 14px;
  border-radius: 4px;
  text-align: center;
  height: 40px;
}
.charges-maintenance .el-collapse-item__content {
  padding-bottom: 0;
}
.charges-maintenance .el-collapse-item__header i {
  font-weight: bold;
}
.charges-maintenance .form-slider i {
  font-size: 25px;
  margin-left: 50px;
  cursor: pointer;
}
.charges-maintenance .text-input {
  max-width: 235px;
}
.charges-maintenance .taux-input {
  width: 130px;
}
.charges-maintenance .el-tooltip.el-icon-info {
  font-size: 25px;
  color: #2491eb;
  vertical-align: middle;
}
</style>
