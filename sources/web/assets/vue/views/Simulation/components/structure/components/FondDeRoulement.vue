<template>
  <div class="fond-roulement admin-content-wrap">
	  <div class="row">
    <h1 class="admin-content-title">Fond roulement</h1>
	  <el-col :span="2" :offset="2">
		  <el-upload
				  ref="upload"
				  :action.native="'/import-fondroulement/'+ simulationID"
				  multiple
				  :limit="10"
				  :on-success="onSuccess"
				  :on-error="onError">
			  <el-button size="small" type="primary">Importer</el-button>
		  </el-upload>
	  </el-col>
	  <el-col :span="2" class="export-button">
		  <el-button type="success" @click.stop="exportFondroulement">
			  Exporter
		  </el-button>
	  </el-col>
    </div>
    <el-collapse v-model="isCollapsed">
      <el-collapse-item :title="`Fond de roulement long terme à fin ${anneeDeReference}`" name="1">
        <el-form
          class="pt-3"
          :rules="formRules"
          :model="fondDeRoulementParametre"
          ref="fondDeRoulementParametreForm"
        >
          <div class="row">
            <div class="col-sm-12 col-md-6 col-lg-4">
              <el-form-item
                label="Potentiel financier à terminaison"
                prop="potentielFinancierTermination"
              >
                <el-input
                  type="text"
                  placeholder="0"
                  class="fixed-input"
                  v-model="fondDeRoulementParametre.potentielFinancierTermination"
                  :disabled="!fondDeRoulementID || !isModify"
                  @change="onChangeParametre"
                ></el-input>
              </el-form-item>
            </div>
            <div class="col-sm-12 col-md-6 col-lg-4">
              <el-form-item
                class="custom-append-input"
                label="Fonds propres sur opérations en cours"
                prop="fondsPropresSurOperation"
              >
                <el-input
                  type="text"
                  placeholder="0"
                  class="fixed-input"
                  v-model="fondDeRoulementParametre.fondsPropresSurOperation"
                  :disabled="!fondDeRoulementID || !isModify"
                  @change="onChangeParametre"
                >
                  <template slot="append">
                    <el-tooltip class="item" effect="dark" placement="top">
                      <div slot="content">
                        Il s’agit du montant des fonds
                        <br />
                        propres affectés à fin {{ anneeDeReference }} aux
                        <br />opérations en cours, qu'il faut
                        <br />corriger du potentiel financier à
                        <br />
                        terminaison de l'année {{ anneeDeReference }}
                        <br />car ces fonds propres sont déjà
                        <br />déduits de celui-ci.
                        <br />
                      </div>
                      <i class="el-icon-question"></i>
                    </el-tooltip>
                  </template>
                </el-input>
              </el-form-item>
            </div>
            <div class="col-sm-12 col-md-6 col-lg-4">
              <el-form-item label="Dépôt de garantie" prop="depotDeGarantie">
                <el-input
                  type="text"
                  placeholder="0"
                  class="fixed-input"
                  v-model="fondDeRoulementParametre.depotDeGarantie"
                  :disabled="!fondDeRoulementID || !isModify"
                  @change="onChangeParametre"
                  width="200px"
                ></el-input>
              </el-form-item>
            </div>
          </div>
        </el-form>
      </el-collapse-item>
    </el-collapse>
    <div class="mt-4">
      <el-tabs v-model="activeTab">
        <el-tab-pane v-for="(tab, i) in types" :label="tab" :name="`${i+1}`" :key="i">
          <el-table class="fw" v-loading="isLoading" :data="setTableData(fondDeRoulement, i)">
            <el-table-column fixed width="250" label prop="nom"></el-table-column>
            <el-table-column
              v-if="activeTab === '1'"
              align="center"
              width="150"
              label="Type d'emprunt"
              prop="typeEmprunt.nom"
            ></el-table-column>
            <el-table-column
              v-if="activeTab === '1'"
              align="center"
              width="150"
              label="Date de première échéance"
            >
              <template slot-scope="scope">{{dateFormatter(scope.row.dateEcheance)}}</template>
            </el-table-column>
            <el-table-column
              v-if="activeTab === '2'"
              align="center"
              width="150"
              label="Taux d'évolution"
              prop="tauxEvolution"
            ></el-table-column>
            <el-table-column
              v-for="column in columns"
              align="center"
              :key="column.prop"
              :prop="column.prop"
              :label="column.label"
            ></el-table-column>
            <el-table-column fixed="right" width="120" label="Actions" align="center">
              <template slot-scope="scope">
                <el-button
                  v-if="!scope.row.noAction"
                  type="primary"
                  icon="el-icon-edit"
                  circle
                  :disabled="!isModify"
                  @click="handleEdit(scope.$index, scope.row)"
                ></el-button>
                <el-button
                  v-if="scope.row.deletable && !scope.row.noAction"
                  type="danger"
                  icon="el-icon-delete"
                  circle
                  :disabled="!isModify"
                  @click="handleDelete(scope.$index, scope.row)"
                ></el-button>
              </template>
            </el-table-column>
          </el-table>
          <el-row class="d-flex justify-content-end my-3">
            <el-button type="primary" :disabled="!isModify" @click="handleCreate">{{activeTab === '1'? 'Créer une variation': 'Créer une provision'}}</el-button>
          </el-row>
        </el-tab-pane>
      </el-tabs>
    </div>

    <el-dialog
      :title="`${isEdit ? 'Modifier' : 'Créer' } une variation`"
      :visible.sync="dialogVisible"
      :close-on-click-modal="false"
      width="70%"
    >
      <el-row v-if="isEdit" class="form-slider text-center mb-5">
        <i
          :class="{'disabled' : selectedIndex === 0}"
          class="el-icon-back font-weight-bold"
          @click="back"
        ></i>
        <i
          :class="{'disabled' : selectedIndex === tableDataLength}"
          class="el-icon-right font-weight-bold"
          @click="next"
        ></i>
      </el-row>
      <el-form
        v-if="form"
        :rules="dialogVisible ? formRules : null"
        :model="form"
        label-width="160px"
        ref="fondDeRoulementForm"
      >
      <div class="row">
          <div class="col-sm-12">
            <el-form-item label="Désignation:" prop="nom">
                <el-input type="text" v-model="form.nom" placeholder="Désignation" class="fixed-input"></el-input>
            </el-form-item>
          </div>
      </div>
        <div class="row" v-if="activeTab === '1'" >
          <div class="col-sm-12 col-md-6">
            <el-form-item label="Type d'emprunt" prop="typeEmprunt">
              <el-select v-model="form.typeEmprunt.id" @change="changeTypeEmprunt">
                <el-option
                  v-for="item in typesEmprunt"
                  :key="item.id"
                  :label="item.nom"
                  :value="item.id"
                ></el-option>
              </el-select>
            </el-form-item>
          </div>
          <div class="col-sm-12 col-md-4">
            <el-form-item label="Date de première échéance" prop="dateEcheance">
              <el-date-picker
                v-model="form.dateEcheance"
                type="month"
                format="MM/yyyy"
                placeholder="Sélectionner"
              ></el-date-picker>
            </el-form-item>
          </div>
        </div>
        <el-form-item v-else label="Taux d'évolution:" prop="tauxEvolution">
          <el-input v-model="form.tauxEvolution" placeholder="0" class="fixed-input-small"></el-input>
        </el-form-item>
        <periodique
          :anneeDeReference="anneeDeReference"
          v-model="form.periodiques"
          @onChange="periodicOnChange"
        ></periodique>
      </el-form>
      <span slot="footer" class="dialog-footer">
        <el-button type="primary" @click="dialogVisible = false">Retour</el-button>
        <el-button type="success" @click="save">Valider</el-button>
      </span>
    </el-dialog>
  </div>
</template>
<script>
import customValidator from "../../../../../utils/validation-rules";
import {
  isFloat,
  initPeriodic,
  mathInput,
  periodicFormatter,
  repeatObjectPeriodic,
  checkAllPeriodics
} from "../../../../../utils/inputs";
import { dateFormatter, updateData } from "../../../../../utils/helpers";
import Periodique from "../../../../../components/partials/Periodique";
import fondDeRoulementQuery from "../../../../../graphql/simulations/structure-financiere/fond-de-roulement/fondDeRoulement.gql";

export default {
  name: "FondDeRoulement",
  components: { Periodique },
  data() {
    return {
      activeTab: null,
      simulationID: null,
      fondDeRoulementID: null,
      anneeDeReference: null,
      typesEmprunt: [],
      isCollapsed: ["1", "2"],
      columns: [],
      dialogVisible: false,
      form: null,
      inputError: false,
      isEdit: false,
      loading: false,
      selectedIndex: null,
      fondDeRoulement: null,
      fondDeRoulementParametre: {
        potentielFinancierTermination: null,
        fondsPropresSurOperation: null,
        depotDeGarantie: null
      },
      formRules: {
        potentielFinancierTermination: customValidator.getRule(
          "positiveDouble"
        ),
        fondsPropresSurOperation: customValidator.getRule("positiveDouble"),
        depotDeGarantie: customValidator.getRule("positiveDouble"),
        tauxEvolution: customValidator.getRule("positiveDouble"),
        nom: [
          customValidator.getRule("maxVarchar"),
          customValidator.getRule("required")
        ],
        dateEcheance: null
      },
      types: [
        "Autres variations potentiel financier",
        "Provisions de haut bilan"
      ],
      query: null
    };
  },
  computed: {
    isLoading() {
      return !this.columns.length || !this.fondDeRoulement || this.loading;
    },
    tableDataLength() {
      let data = this.setTableData(
        this.fondDeRoulement,
        parseInt(this.activeTab) - 1
      );
      return data.length;
    },
    isModify() {
      return this.$store.getters['choixEntite/isModify'];
    }
  },
  created() {
    const simulationID = _.isNil(this.$route.params.id)
      ? null
      : this.$route.params.id;
    if (_.isNil(simulationID)) {
      return;
    }
    this.simulationID = simulationID;
    this.activeTab = _.isNil(this.$route.query.tab)
      ? "1"
      : this.$route.query.tab;
    this.getAnneeDeReference();
    this.getFondDeRoulementParametre();
    this.getFondDeRoulement('init');
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
  mounted(){
    this.getTypesEmprunt();
  },
  methods: {
    isFloat: isFloat,
    dateFormatter: dateFormatter,
    changeTab() {
      this.$router.push({
        path: "fond-roulement",
        query: { tab: this.activeTab }
      });
    },
    setTableData(data, type) {
      if (data) {
        return data.filter(row => {
          if (row.type === this.types[type]) {
            return row;
          }
        });
      }
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
    getTypesEmprunt() {
      this.$apollo
        .query({
          query: require("../../../../../graphql/simulations/types-emprunts/typesEmprunts.gql"),
          variables: {
            simulationID: this.simulationID
          }
        })
        .then(res => {
          if (res.data && res.data.typesEmprunts) {
            this.typesEmprunt = res.data.typesEmprunts.items;
          }
        });
    },
    getFondDeRoulement() {
      this.loading = true;
      this.$apollo
        .query({
          query: require("../../../../../graphql/simulations/structure-financiere/fond-de-roulement/fondDeRoulement.gql"),
          fetchPolicy: 'no-cache',
          variables: {
            simulationId: this.simulationID
          }
        })
        .then(res => {
          if (!_.isNil(res.data.fondDeRoulement)) {
            const fondDeRoulement = res.data.fondDeRoulement.items.map(item => {
              let fondDeRoulementPeriodique = [];
              item.fondDeRoulementPeriodique.items.forEach(periodique => {
                fondDeRoulementPeriodique[periodique.iteration - 1] = periodique.valeur;
              });
              let row = { ...item };
              row.periodiques = {
                fondDeRoulementPeriodique
              };

              return row;
            });

            this.fondDeRoulement = fondDeRoulement;

            // Get total
            this.getTotal();
            this.loading = false;
          } else {
            this.loading = false;
            this.fondDeRoulement = [];
          }
        });
    },
    getTotal() {
      let sumPeriodique1 = [];
      let sumPeriodique2 = [];
      this.fondDeRoulement.forEach(item => {
        item.fondDeRoulementPeriodique.items.forEach((value, index) => {
          if (!sumPeriodique1[index]) {
            sumPeriodique1[index] = {iteration: index, valeur: 0};
          }
          if (!sumPeriodique2[index]) {
            sumPeriodique2[index] = {iteration: index, valeur: 0};
          }
          if (item.type === "Autres variations potentiel financier") {
            sumPeriodique1[index].valeur += value.valeur;
          } else {
            sumPeriodique2[index].valeur += value.valeur;
          }
        });
      });

      if (this.fondDeRoulement.some(item => item.nom === 'Total')) {
        let total1 = this.fondDeRoulement.find(item => item.type === "Autres variations potentiel financier");
        total1.fondDeRoulementPeriodique = {items: sumPeriodique1};
        let total2 = this.fondDeRoulement.find(item => item.type === "Provisions de haut bilan");
        total2.fondDeRoulementPeriodique = {items: sumPeriodique2};
      } else {
        this.fondDeRoulement.push({
          nom: 'Total',
          fondDeRoulementPeriodique: {items: sumPeriodique1},
          type: "Autres variations potentiel financier",
          noAction: true
        });
        this.fondDeRoulement.push({
          nom: 'Total',
          fondDeRoulementPeriodique: {items: sumPeriodique2},
          type: "Provisions de haut bilan",
          noAction: true
        });
      }
    },
    getFondDeRoulementParametre() {
      this.$apollo
        .query({
          query: require("../../../../../graphql/simulations/structure-financiere/fond-de-roulement/fondDeRoulementParametre.gql"),
          variables: {
            simulationId: this.simulationID
          }
        })
        .then(res => {
          this.fondDeRoulementParametre = {
            depotDeGarantie:
              res.data.fondDeRoulementParametre.items[0].depotDeGarantie,
            fondsPropresSurOperation:
              res.data.fondDeRoulementParametre.items[0]
                .fondsPropresSurOperation,
            potentielFinancierTermination:
              res.data.fondDeRoulementParametre.items[0]
                .potentielFinancierTermination
          };
          this.fondDeRoulementID =
            res.data.fondDeRoulementParametre.items[0].id;
        });
    },
    setTableColumns() {
      for (var i = 0; i < 50; i++) {
        this.columns.push({
          label: (parseInt(this.anneeDeReference) + i).toString(),
          prop: `fondDeRoulementPeriodique.items[${i}].valeur`
        });
      }
    },
    handleEdit(index, row) {
      this.isEdit = true;
      this.dialogVisible = true;
      this.form = _.cloneDeep(row);
      if (!this.form.typeEmprunt) {
        this.form.typeEmprunt = {
          id: null
        };
      }
      this.selectedIndex = index;
    },
    handleDelete(index, row) {
      this.$confirm(
        "Êtes-vous sûr de vouloir supprimer cette variation ?"
      ).then(() => {
        this.loading = true;
        this.$apollo
          .mutate({
            mutation: require("../../../../../graphql/simulations/structure-financiere/fond-de-roulement/removeFondDeRoulement.gql"),
            variables: {
              fondDeRoulementID: row.id,
              simulationId: this.simulationID
            }
          })
          .then(() => {
            this.loading = false;
            this.updateFondDeRoulement(row.id, null, "delete");
            this.$message({
              message: "La variation a bien été supprimée.",
              type: "success"
            });
          })
          .catch(error => {
            this.loading = false;
            this.updateFondDeRoulement(row.id, null, "delete");
            this.$message({
              showClose: true,
              message: error.networkError.result,
              type: "error"
            });
          });
      });
    },
    initForm() {
      this.form = {
        id: null,
        nom: null,
        tauxEvolution: null,
        typeEmprunt: {
          id: null
        },
        dateEcheance: null,
        periodiques: {
          fondDeRoulementPeriodique: initPeriodic()
        },
        type: this.types[parseInt(this.activeTab) - 1]
      };
      this.$refs["fondDeRoulementForm"] && this.$refs["fondDeRoulementForm"].resetFields();
      this.formRules.dateEcheance = null;
    },
    back() {
      if (this.selectedIndex > 0) {
        this.selectedIndex--;
        this.form = { ...this.fondDeRoulement[this.selectedIndex] };
      }
    },
    next() {
      if (this.selectedIndex < this.tableDataLength) {
        this.selectedIndex++;
        this.form = { ...this.fondDeRoulement[this.selectedIndex] };
      }
    },
    periodicOnChange(type) {
      let newPeriodics = this.form.periodiques[type];
      this.form.periodiques[type] = [];
      this.form.periodiques[type] = periodicFormatter(newPeriodics);
    },
    save() {
      this.$refs["fondDeRoulementForm"].validate(valid => {
        if (valid && checkAllPeriodics(this.form.periodiques)) {
          this.dialogVisible = false;
          this.loading = true;
          this.$apollo
            .mutate({
              mutation: require("../../../../../graphql/simulations/structure-financiere/fond-de-roulement/saveFondDeRoulement.gql"),
              variables: {
                fondDeRoulement: {
                  dateEcheance: this.form.dateEcheance,
                  uuid: this.form.id,
                  nom: this.form.nom,
                  periodique: JSON.stringify({
                    periodique: this.form.periodiques.fondDeRoulementPeriodique
                  }),
                  simulationId: this.simulationID,
                  tauxEvolution: this.form.tauxEvolution || 0,
                  type: this.form.type,
                  typeEmpruntId: this.form.typeEmprunt.id
                }
              }
            })
            .then(res => {
              let updatedFondDeRoulement = res.data.saveFondDeRoulement;
              this.updateFondDeRoulement(
                updatedFondDeRoulement.id,
                updatedFondDeRoulement,
                "save"
              );
              this.loading = false;
              this.$message({
                showClose: true,
                message: "Les valeurs ont été enregistrées.",
                type: "success"
              });
            })
            .catch(error => {
              this.loading = false;
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
    onChangeParametre() {
      let parametre = this.fondDeRoulementParametre;

      for (let [key, value] of Object.entries(parametre)) {
        this.fondDeRoulementParametre[key] = mathInput(value);
      }

      this.$refs["fondDeRoulementParametreForm"].validate(valid => {
        if (valid) {
          this.saveParametre();
        } else {
          this.showError();
        }
      });
    },
    saveParametre() {
      this.$apollo
        .mutate({
          mutation: require("../../../../../graphql/simulations/structure-financiere/fond-de-roulement/saveFondDeRoulementParametre.gql"),
          variables: {
            fondDeRoulementId: this.fondDeRoulementID,
            potentielFinancierTermination: this.fondDeRoulementParametre
              .potentielFinancierTermination,
            fondsPropresSurOperation: this.fondDeRoulementParametre
              .fondsPropresSurOperation,
            depotDeGarantie: this.fondDeRoulementParametre.depotDeGarantie,
            simulationId: this.simulationID
          }
        })
        .then(() => {
          this.$message({
            showClose: true,
            message: "Les valeurs ont été enregistrées.",
            type: "success"
          });
        })
        .catch(error => {
          this.$message({
            showClose: true,
            message: error.networkError.result,
            type: "error"
          });
        });
    },
    updateFondDeRoulement(id, data, type) {
      // if (type === "save") {
      //   let isNew = true;

      //   this.fondDeRoulement.filter((row, key) => {
      //     if (row.id === id) {
      //       this.$set(this.fondDeRoulement, key, data);
      //       isNew = false;
      //     }
      //   });
      //   if (isNew) {
      //     this.fondDeRoulement.push(data);
      //   }
      // } else {
      //   this.fondDeRoulement.filter((row, key) => {
      //     if (row.id === id) {
      //       this.fondDeRoulement.splice(key, 1);
      //     }
      //   });
      // }
      this.getFondDeRoulement();
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
    handleCreate() {
      this.initForm();
      this.dialogVisible = true;
      this.isEdit = false;
    },
    periodicFormatter() {
      let data = periodicFormatter(this.form.fondDeRoulementPeriodique.items);
      Object.assign({}, this.form.fondDeRoulementPeriodique, { items: data });
    },
    changeTypeEmprunt(value) {
      if (value) {
        this.formRules.dateEcheance = customValidator.getRule('required', 'change');
      } else {
        this.formRules.dateEcheance = null;
      }
    },
    exportFondroulement() {
       window.location.href = "/export-fondroulement/" + this.simulationID;
    },
    onSuccess(res) {
      this.$toasted.success(res, {
          theme: 'toasted-primary',
          icon: 'check',
          position: 'top-right',
          duration: 5000
      });
      this.$refs.upload.clearFiles();
      this.$router.go(0);
    },
    onError(error) {
      this.$toasted.error(JSON.parse(error.message), {
          theme: 'toasted-primary',
          icon: 'error',
          position: 'top-right',
          duration: 5000
      });
      this.$refs.upload.clearFiles();
    }
  },
  watch: {
    activeTab(newVal, oldVal) {
      if (oldVal) {
        this.changeTab();
      }
    }
  }
};
</script>
<style>
.fond-roulement .el-collapse-item__content .fixed-input {
  width: 80px;
}
.fond-roulement .el-collapse-item__content [class*=" el-icon-"],
.fond-roulement .el-collapse-item__content [class^="el-icon-"] {
  color: #2491eb;
  font-size: 20px;
}
.fond-roulement .el-button--small {
  font-weight: 500;
  padding: 0 15px;
  height: 40px;
  font-size: 14px;
  border-radius: 4px;
  text-align: center;
  height: 40px;
}
.fond-roulement {
  font-size: 14px;
}
.fond-roulement .text-input {
  width: 150px;
  margin-left: 60px;
}
.fond-roulement .fixed-input {
  width: 235px;
}
.fond-roulement .fixed-input-small {
  width: 80px;
}
.fond-roulement .el-collapse-item__header {
  padding-left: 15px;
  font-weight: bold;
  font-size: 15px;
}
.fond-roulement .el-collapse-item__content {
  padding-bottom: 0;
}
.fond-roulement .el-collapse-item__header i {
  font-weight: bold;
}
.fond-roulement .form-slider i {
  font-size: 25px;
  margin-left: 50px;
  cursor: pointer;
}
.fond-roulement .el-form-item__label {
  line-height: 40px;
}
.fond-roulement .form-slider .disabled {
  cursor: default;
  opacity: 0;
}
</style>
