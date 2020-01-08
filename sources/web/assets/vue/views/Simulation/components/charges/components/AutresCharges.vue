<template>
  <div class="autres-charges admin-content-wrap ">
	  <div class="row">
		  <h1 class="admin-content-title">Autres charges</h1>
		  <el-col :span="2" :offset="2">
			  <el-upload
					  ref="upload"
					  :action="'/import-autres-charges/'+ simulationID"
					  multiple
					  :limit="10"
					  :disabled="!isModify"
					  :on-success="onSuccessImport"
					  :on-error="onErrorImport"
			  >
				  <el-button size="small" type="primary">Importer</el-button>
			  </el-upload>
		  </el-col>
		  <el-col :span="2" class="export-button">
			  <el-button type="success":disabled="!isModify" @click.stop="exportAutresCharges">Exporter</el-button>
		  </el-col>
	  </div>
    <el-collapse v-model="activeList">
      <ApolloQuery
        :query="require('../../../../../graphql/simulations/charges/autresCharges.gql')"
        :variables="{
                        simulationId: simulationID
                    }"
      >
        <template slot-scope="{ result:{ loading, error, data }, isLoading, query }">
          <div v-if="error">Une erreur est survenue !</div>

          <el-collapse-item title="Récapitulatif des autres charges" name="1">
            <template slot="title">
              <div class="custom-collapsible-title">
                <div>
                  <label>Récapitulatif des autres charges</label>
                </div>

              </div>
            </template>
            <el-table v-loading="loading" :data="recapList" style="width: 100%">
              <el-table-column
                sortable
                fixed
                column-key="type"
                prop="type"
                width="170"
                label="Récapitulatif des autres charges"
              ></el-table-column>
              <el-table-column
                v-for="column in recapColumns"
                sortable
                align="center"
                :key="column.prop"
                :prop="column.prop"
                :label="column.label"
              ></el-table-column>
            </el-table>
          </el-collapse-item>
          <el-collapse-item name="2" class="mt-3">
            <template slot="title">
              <el-row class="w-100 mt-3">
                <el-col :span="10">
                  <label>Autres charges</label>
                </el-col>
              </el-row>
            </template>
            <el-table v-loading="isLoading" :data="tableData(data, query)" style="width: 100%">
              <el-table-column sortable fixed column-key="numero" prop="numero" label="Numero"></el-table-column>
              <el-table-column
                sortable
                fixed
                column-key="libelle"
                prop="libelle"
                width="170"
                label="Libelle"
              ></el-table-column>
              <el-table-column
                sortable
                fixed
                column-key="nature"
                prop="nature"
                width="170"
                label="Nature"
              ></el-table-column>
              <el-table-column
                sortable
                fixed
                column-key="tauxDevolution"
                prop="tauxDevolution"
                width="150"
                align="center"
                label="Taux d'évolution"
              ></el-table-column>
              <el-table-column
                v-for="column in autresChargesColumns"
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
                    @click="handleEdit(scope.$index, scope.row)"
                  ></el-button>
                  <el-button
                    type="danger"
                    :disabled="!isModify"
                    icon="el-icon-delete"
                    circle
                    @click="handleDelete(scope.$index, scope.row)"
                  ></el-button>
                </template>
              </el-table-column>
            </el-table>
            <el-row class="d-flex justify-content-end my-3 px-3">
              <el-button type="primary" :disabled="!isModify" @click="showCreateModal">Créer une autre charge</el-button>
            </el-row>
          </el-collapse-item>
        </template>
      </ApolloQuery>
    </el-collapse>

    <el-dialog title="Créer une autre charge" :visible.sync="dialogVisible" :close-on-click-modal="false" width="70%">
      <el-row v-if="isEdit" class="form-slider text-center mb-5">
        <i class="el-icon-back font-weight-bold" @click="back"></i>
        <i class="el-icon-right font-weight-bold" @click="next"></i>
      </el-row>
      <el-form :model="form" :rules="formRules" label-width="160px" ref="autresChargeForm">
        <div class="row">
          <div class="col-sm-12 col-md-6">
            <el-form-item label="Numero:" prop="numero">
              <el-input type="text" v-model="form.numero" class="text-input" disabled></el-input>
            </el-form-item>
          </div>
          <div class="col-sm-12 col-md-6">
            <el-form-item label="Libellé de la charge:" prop="libelle">
              <el-input
                type="text"
                v-model="form.libelle"
                placeholder="Libellé"
                class="text-input"
                :disabled="isEdit"
              ></el-input>
            </el-form-item>
          </div>

          <div class="col-sm-12 col-md-6">
            <el-form-item label="Nature:" prop="nature">
              <div class="d-flex align-items-center">
                <el-select v-model="form.nature" :disabled="isEdit">
                  <el-option v-for="item in natures" :key="item" :label="item" :value="item"></el-option>
                </el-select>
                <el-tooltip
                  class="item ml-2"
                  effect="dark"
                  content="les frais de personnel et les frais de gestion doivent être renseignés ici toutes activités confondues (locatif, accession, autres activités…)"
                  placement="top"
                >
                  <i class="el-icon-info"></i>
                </el-tooltip>
              </div>
            </el-form-item>
          </div>

        </div>

        <div class="row">
          <div class="col-sm-12 col-md-6 d-flex align-items-center">
            <el-form-item class="mb-0 mt-3">
              <el-checkbox v-model="form.indexation" label="Indexation" name="indexation"></el-checkbox>
            </el-form-item>
          </div>
          <div class="col-sm-12 col-md-6">
            <el-form-item label="Taux d'évolution:" prop="tauxDevolution">
              <el-input
                type="text"
                placeholder="%"
                class="text-input"
                v-model="form.tauxDevolution"
                :disabled="!form.indexation"
                @change="() => {form = Object.assign({}, form ,{'tauxDevolution' : mathInput(form.tauxDevolution)})}"
              ></el-input>
            </el-form-item>
          </div>

        </div>
        <periodique
          :anneeDeReference="anneeDeReference"
          v-model="form.periodiques"
          @onChange="periodicOnChange"
        ></periodique>
      </el-form>
      <span slot="footer" class="dialog-footer">
        <el-button type="outline-primary" @click="dialogVisible = false">Retour</el-button>
        <el-button type="primary" @click="save('autresChargeForm')" :disabled="isSubmitting">Valider</el-button>
      </span>
    </el-dialog>
  </div>
</template>
<script>
import {
  initPeriodic,
  mathInput,
  periodicFormatter,
  checkAllPeriodics
} from "../../../../../utils/inputs";
import { updateData } from "../../../../../utils/helpers";
import customValidation from "../../../../../utils/validation-rules";
import Periodique from "../../../../../components/partials/Periodique";
export default {
  name: "AutresCharges",
  components: { Periodique },
  data() {
    return {
      activeList: ["2"],
      simulationID: null,
      anneeDeReference: null,
      recapColumns: [],
      recapList: [],
      autresCharges: [],
      autresChargesColumns: [],
      dialogVisible: false,
      isEdit: false,
      form: {},
      selectedIndex: null,
      loading: true,
      query: null,
      inputError: false,
      natures: [
        "Frais de personnel",
        "Frais de gestion",
        "Taxes foncières",
        "Charges courantes",
        "Charges exceptionnelles"
      ],
      formRules: {
        libelle: [
          customValidation.getRule("requiredNoWhitespaces"),
          customValidation.getRule("maxVarchar")
        ],
        nature: customValidation.getRule("required", "change"),
        tauxDevolution: customValidation.getPreset("taux")
      },
      isSubmitting: false
    };
  },
  computed: {
    isModify() {
      return this.$store.getters['choixEntite/isModify'];
    }
  },
  created() {
    let simulationID = _.isNil(this.$route.params.id)
      ? null
      : this.$route.params.id;
    if (_.isNil(simulationID)) {
      return;
    }
    this.simulationID = simulationID;
    this.getAnneeDeReference();
    this.init();
    this.initForm();
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
  methods: {
    periodicFormatter: periodicFormatter,
    mathInput: mathInput,
    init() {
      const items = initPeriodic();
      this.recapList = [
        { type: "Frais de personnel", items },
        { type: "Frais de gestion", items },
        { type: "Taxe foncière", items },
        { type: "Charges courantes", items },
        { type: "Charges exceptionnelles", items },
        { type: "Total", items }
      ];
    },
    initForm() {
      this.form = {
        id: null,
        numero: this.autresCharges.length + 1,
        libelle: "",
        indexation: true,
        nature: "",
        tauxDevolution: 0,
        periodiques: {
          autresChargesPeriodique: initPeriodic()
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
      this.recapColumns = [];
      this.autresChargesColumns = [];
      for (var i = 0; i < 50; i++) {
        this.recapColumns.push({
          label: (parseInt(this.anneeDeReference) + i).toString(),
          prop: `items[${i}]`
        });
        this.autresChargesColumns.push({
          label: (parseInt(this.anneeDeReference) + i).toString(),
          prop: `periodiques.autresChargesPeriodique[${i}]`
        });
      }
    },
    tableData(data, query) {
      if (!_.isNil(data)) {
        this.query = query;
        const autresCharges = data.autresCharges.items.map(item => {
          let periodiques = [];
          item.autresChargesPeriodique.items.forEach(periodique => {
            periodiques[periodique.iteration - 1] = periodique.value;
          });
          return {
            id: item.id,
            numero: item.numero,
            libelle: item.libelle,
            indexation: item.indexation,
            nature: item.nature,
            tauxDevolution: item.tauxDevolution,
            periodiques: {
              autresChargesPeriodique: periodiques
            }
          };
        });
        this.autresCharges = autresCharges;

        // Calculate the recap list.
        this.getRecapList(autresCharges);

        return autresCharges;
      } else {
        return [];
      }
    },
    getRecapList(data) {
      this.natures.forEach((nature, i) => {
        let recapList = [];
        let groups = data.filter(record => record.nature === nature);
        if (groups.length > 0) {
          groups.forEach(group => {
            group.periodiques.autresChargesPeriodique.forEach((item, index) => {
              if (!recapList[index]) {
                recapList[index] = 0;
              }
              recapList[index] += item;
            });
          });
          this.recapList[i].items = recapList;
        }
      });

      // Get total sum.
      let recapList = [];
      data.forEach(group => {
        group.periodiques.autresChargesPeriodique.forEach((item, index) => {
          if (!recapList[index]) {
            recapList[index] = 0;
          }
          recapList[index] += item;
        });
      });
      this.recapList[5].items = recapList;
      this.loading = false;
    },
    showCreateModal() {
      this.initForm();
      this.dialogVisible = true;
      this.isEdit = false;
    },
    handleEdit(index, row) {
      this.dialogVisible = true;
      this.form = { ...row };
      this.selectedIndex = index;
      this.isEdit = true;
    },
    handleDelete(index, row) {
      this.$confirm("Êtes-vous sûr(e) de vouloir supprimer cette charge ?")
        .then(() => {
          this.$apollo
            .mutate({
              mutation: require("../../../../../graphql/simulations/charges/removeAutreCharge.gql"),
              variables: {
                autreChargeId: row.id,
                simulationId: this.simulationID
              }
            })
            .then(() => {
              updateData(this.query, this.simulationID).then(() => {
                this.$message({
                  showClose: true,
                  message: "La charge a bien été supprimée.",
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
    save(formName) {
      this.$refs[formName].validate(valid => {
        if (valid && checkAllPeriodics(this.form.periodiques)) {
          this.isSubmitting = true;
          this.$apollo
            .mutate({
              mutation: require("../../../../../graphql/simulations/charges/saveAutreCharge.gql"),
              variables: {
                autreCharge: {
                  id: this.form.id,
                  simulationId: this.simulationID,
                  libelle: this.form.libelle,
                  indexation: this.form.indexation,
                  nature: this.form.nature,
                  taux_devolution: this.form.tauxDevolution,
                  periodique: JSON.stringify({
                    periodique: this.form.periodiques.autresChargesPeriodique
                  })
                }
              }
            })
            .then(() => {
              this.dialogVisible = false;
              this.isSubmitting = false;
              updateData(this.query, this.simulationID).then(() => {
                this.$message({
                  showClose: true,
                  message: "La charge a bien été enregistrée.",
                  type: "success"
                });
              });
            })
            .catch(error => {
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
    },
    back() {
      if (this.selectedIndex > 0) {
        this.selectedIndex--;
        this.form = { ...this.autresCharges[this.selectedIndex] };
      }
    },
    next() {
      if (this.selectedIndex < this.autresCharges.length - 1) {
        this.selectedIndex++;
        this.form = { ...this.autresCharges[this.selectedIndex] };
      }
    },
    periodicOnChange(type) {
      let newPeriodics = this.form.periodiques[type];
      this.form.periodiques[type] = [];
      this.form.periodiques[type] = periodicFormatter(newPeriodics);
    },
    exportAutresCharges() {
      window.location.href = "/export-autres-charges/" + this.simulationID;
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
      this.$toasted.error(JSON.parse(error.message), {
        theme: "toasted-primary",
        icon: "error",
        position: "top-right",
        duration: 5000
      });
      this.$refs.upload.clearFiles();
    }
  }
};
</script>

<style type="text/css">
.autres-charges .el-button--small {
  font-weight: 500;
  padding: 0 15px;
  height: 40px;
  font-size: 14px;
  border-radius: 4px;
  text-align: center;
}
.autres-charges .el-upload-list--text {
  display: none;
}
.autres-charges .el-collapse-item__header {
  padding-left: 15px;
  font-weight: bold;
  font-size: 15px;
}
.autres-charges .el-collapse-item__header .header-btn-group {
  width: -webkit-fill-available;
  width: -moz-available;
  text-align: right;
  padding-right: 20px;
}
.autres-charges .el-collapse-item__content {
  padding-bottom: 0;
}
.autres-charges .el-collapse-item__header i {
  font-weight: bold;
}
.autres-charges .text-input {
  width: 235px;
}
.autres-charges .el-input.is-disabled .el-input__inner {
  color: black;
}
.autres-charges .form-slider i {
  font-size: 25px;
  margin-left: 50px;
  cursor: pointer;
}
.autres-charges .el-form-item__label {
  line-height: 40px;
}
.el-tooltip.el-icon-info {
  font-size: 25px;
  color: #2491eb;
  vertical-align: middle;
}
</style>

