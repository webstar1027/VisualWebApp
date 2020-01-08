<template>
  <div class="frais-structures">
    <el-card>
      <label class="mb-5">Liste des frais de structure</label>
      <ApolloQuery
        :query="require('../../../../../graphql/simulations/frais/fraisStructures.gql')"
        :variables="{
          simulationId: simulationID}"
      >
        <template slot-scope="{ result:{ loading, error, data }, isLoading, query }">
          <div v-if="error">Une erreur est survenue !</div>
          <el-table v-loading="isLoading" :data="tableData(data, query)" style="width: 100%">
            <el-table-column sortable fixed column-key="number" prop="number" label="N°"></el-table-column>
            <el-table-column
              sortable
              fixed
              column-key="libelle"
              prop="libelle"
              width="120"
              label="Libellé"
            ></el-table-column>
            <el-table-column
              sortable
              fixed
              column-key="type"
              prop="type"
              width="120"
              label="Nature"
            >
              <template slot-scope="scope">
                <span v-if="!scope.row.noAction">{{renderType(scope.row.type)}}</span>
              </template>
            </el-table-column>
            <el-table-column
              sortable
              fixed
              column-key="type"
              prop="indexation"
              min-width="120"
              label="Indexation automatique">
              <template slot-scope="scope">
                <span v-if="!scope.row.noAction">{{scope.row.indexation ? 'Oui' : 'Non'}}</span>
              </template>
            </el-table-column>
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
              v-for="column in fraisStructuresColumns"
              sortable
              align="center"
              :key="column.prop"
              :prop="column.prop"
              :label="column.label"
            ></el-table-column>
            <el-table-column fixed="right" width="120" label="Actions">
              <template slot-scope="scope">
                <el-button
                  v-if="!scope.row.noAction"
                  type="primary"
                  :disabled="!isModify"
                  icon="el-icon-edit"
                  circle
                  @click="handleEdit(scope.$index, scope.row)"
                ></el-button>
                <el-button
                  v-if="!scope.row.noAction"
                  type="danger"
                  :disabled="!isModify"
                  icon="el-icon-delete"
                  circle
                  @click="handleDelete(scope.$index, scope.row)"
                ></el-button>
              </template>
            </el-table-column>
          </el-table>
        </template>
      </ApolloQuery>

      <el-row class="d-flex justify-content-end my-3">
        <el-button type="primary" :disabled="!isModify" @click="showCreateModal">Créer des frais de structure</el-button>
      </el-row>

      <el-dialog
        title="Creation / Modification de frais de structure"
        :visible.sync="dialogVisible"
        :close-on-click-modal="false"
        width="70%"
      >
        <el-row v-if="isEdit" class="form-slider text-center mb-5">
          <i class="el-icon-back font-weight-bold" @click="back"></i>
          <i class="el-icon-right font-weight-bold" @click="next"></i>
        </el-row>
        <el-form :model="form" :rules="formRules" label-width="160px" ref="fraisStructureForm">
          <div class="row">
            <div class="col-sm-12">
              <el-form-item label="N°  " prop="number">
                <el-input
                  type="text"
                  v-model="form.number"
                  placeholder="0"
                  class="text-input"
                  disabled
                ></el-input>
              </el-form-item>
            </div>
          </div>
          <div class="row">
            <div class="col-sm-6">
              <el-form-item label="Libellé " prop="libelle">
                <el-input
                  type="text"
                  v-model="form.libelle"
                  placeholder="Libellé"
                  class="text-input"
                  :disabled="isEdit"
                ></el-input>
              </el-form-item>
            </div>
            <div class="col-sm-6">
              <el-form-item label="Nature " prop="type">
                <el-select v-model="form.type" :disabled="isEdit">
                  <el-option
                    v-for="item in types"
                    :key="item.value"
                    :label="item.label"
                    :value="item.value"
                  ></el-option>
                </el-select>
              </el-form-item>
            </div>
          </div>
          <div class="row">
            <div class="col-sm-6">
              <div style="padding-top:30px">
                <el-form-item>
                  <el-checkbox v-model="form.indexation" label="Indexation automatique" name="indexation"></el-checkbox>
                </el-form-item>
              </div>
            </div>
            <div class="col-sm-6">
              <el-form-item label="Taux d'évolution " prop="tauxDevolution">
                <el-input
                  type="text"
                  v-model="form.tauxDevolution"
                  placeholder="0"
                  class="text-input"
                  :disabled="!form.indexation"
                  @change="formatInput('tauxDevolution')"></el-input>
              </el-form-item>
            </div>
          </div>
          <periodique
            :anneeDeReference="anneeDeReference"
            v-model="form.periodiques"
            @onChange="periodicOnChange"
          ></periodique>
        </el-form>
        <div slot="footer" class="dialog-footer">
          <el-button type="primary" @click="dialogVisible=false">Retour</el-button>
          <el-button type="success" @click="save('fraisStructureForm')" :disabled="isSubmitting">Valider</el-button>
        </div>
      </el-dialog>
    </el-card>
  </div>
</template>
<script>
import Periodique from "../../../../../components/partials/Periodique";
import { initPeriodic, checkAllPeriodics, periodicFormatter, mathInput } from "../../../../../utils/inputs";
import {updateData} from "../../../../../utils/helpers";

export default {
  name: "FraisDeStructure",
  components: { Periodique },
  data() {
    let validateLibelle = (rule, value, callback) => {
      if (value === "") {
        callback(
          new Error("Vous devez saisir un libellé de frais de structure")
        );
      } else if (this.isLibelle(value)) {
        callback(new Error("libellé de frais de structure déjà existant"));
      } else {
        callback();
      }
    };
    return {
      activeList: ["1", "2"],
      simulationID: null,
      anneeDeReference: null,
      fraisStructures: [],
      fraisStructuresColumns: [],
      dialogVisible: false,
      isEdit: false,
      currentLastNumber: 0,
      form: {},
      selectedIndex: null,
      query: null,
      isSubmitting: false,
      types: [
        {
          value: 0,
          label: "Frais de gestion"
        },
        {
          value: 1,
          label: "Frais de personnel"
        }
      ],
      formRules: {
        libelle: [
          { required: true, validator: validateLibelle, trigger: "change" }
        ],
        type: [
          {
            required: true,
            message: "Vous devez saisir la nature de frais de structure",
            trigger: "change"
          }
        ]
      }
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
    initForm() {
      this.form = {
        id: null,
        number: 1,
        libelle: "",
        indexation: true,
        type: "",
        tauxDevolution: null,
        periodiques: {
          items: initPeriodic()
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
      this.fraisStructuresColumns = [];
      for (var i = 0; i < 50; i++) {
        this.fraisStructuresColumns.push({
          label: (parseInt(this.anneeDeReference) + i).toString(),
          prop: `periodiques.items[${i}]`
        });
      }
    },
    tableData(data, query) {
      if (!_.isNil(data)) {
        this.query = query;
        const fraisStructures = data.fraisStructures.items.map(item => {
          let periodiques = [];
          let total = 0;
          item.fraisStructuresPeriodique.items.forEach(periodique => {
            periodiques[periodique.iteration - 1] = periodique.value;
            total += parseInt(periodique.value);
          });
          return {
            id: item.id,
            number: item.number,
            libelle: item.libelle,
            indexation: item.indexation,
            type: item.type,
            tauxDevolution: item.tauxDevolution,
            periodiques: { items: periodiques },
            total: total
          };
        });
        if (fraisStructures.length > 0) {
          this.currentLastNumber = parseInt(
            fraisStructures[fraisStructures.length - 1].number
          );
        }

        // Get total value.
        let sumPeriodique = [];
        fraisStructures.forEach(fraisStructure => {
          fraisStructure.periodiques.items.forEach((item, index) => {
              if (!sumPeriodique[index]) {
                  sumPeriodique[index] = 0;
              }
              sumPeriodique[index] += item;
          });
        });
        fraisStructures.push({
          libelle: 'Total',
          periodiques: {items: sumPeriodique},
          noAction: true
        });

        this.fraisStructures = fraisStructures;

        return fraisStructures;
      } else {
        return [];
      }
    },
    renderType(type) {
      const selectedType = this.types.find(item => item.value === type);
      return selectedType && selectedType.label;
    },
    showCreateModal() {
      this.initForm();
      this.form.number = this.currentLastNumber + 1;
      this.dialogVisible = true;
      this.isEdit = false;
      if (this.$refs.fraisStructureForm) {
        this.$refs.fraisStructureForm.clearValidate();
      }
    },
    handleEdit(index, row) {
      this.dialogVisible = true;
      this.form = { ...row };
      this.selectedIndex = index;
      this.isEdit = true;
    },
    handleDelete(index, row) {
      this.$confirm("Êtes-vous sûr(e) de vouloir supprimer cette ligne ?")
        .then(_ => {
          this.$apollo
            .mutate({
              mutation: require("../../../../../graphql/simulations/frais/removeFraisStructure.gql"),
              variables: {
                fraisStructureId: row.id,
                simulationId: this.simulationID
              }
            }).then(() => {
              updateData(this.query, this.simulationID).then(() => {
                this.$message({
                  showClose: true,
                  message: 'Ces frais de structure ont bien été supprimés.',
                  type: 'success'
                });
              })
            }).catch(error => {
              updateData(this.query, this.simulationID).then(() => {
                this.$message({
                  showClose: true,
                  message: 'Ces frais de structure n\'existent pas.',
                  type: 'error',
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
              mutation: require("../../../../../graphql/simulations/frais/saveFraisStructure.gql"),
              variables: {
                fraiStructure: {
                  id: this.form.id,
                  simulationId: this.simulationID,
                  libelle: this.form.libelle,
                  indexation: this.form.indexation,
                  type: this.form.type,
                  taux_devolution: this.form.tauxDevolution,
                  periodique: JSON.stringify({
                    periodique: this.form.periodiques.items
                  })
                }
              }
            }).then(() => {
              this.dialogVisible = false;
              this.isSubmitting = false;
              updateData(this.query, this.simulationID).then(() => {
                this.$message({
                  showClose: true,
                  message: 'Ces frais de structure ont bien été enregistrés.',
                  type: 'success'
                });
              })
            }).catch(error => {
              this.isSubmitting = false;
              this.$message({
                showClose: true,
                message: error.networkError.result,
                type: 'error',
              });
            });
          } else {
            this.showError();
          }
      });
    },
    back() {
      if (this.selectedIndex > 0) {
        this.selectedIndex--;
        this.form = { ...this.fraisStructures[this.selectedIndex] };
      }
    },
    periodicOnChange(type) {
      let newPeriodics = this.form.periodiques[type];
      this.form.periodiques[type] = [];
      this.form.periodiques[type] = periodicFormatter(newPeriodics);
    },
    next() {
      if (this.selectedIndex < this.fraisStructures.length - 1) {
        this.selectedIndex++;
        this.form = { ...this.fraisStructures[this.selectedIndex] };
      }
    },
    isLibelle(libelle) {
      for (let i = 0; i < this.fraisStructures.length; i++) {
        if (
          this.fraisStructures[i].libelle === libelle &&
          this.isEdit === false
        ) {
          return true;
        }
      }
    },
    showError () {
      if (!this.inputError) {
        this.inputError = true;
        this.$message({
          showClose: true,
          message: 'Les valeurs entrées doivent être valides.',
          type: 'error',
          onClose: () => {
            this.inputError = false
          }
        });
      }
    },
    formatInput(type) {
      this.form[type] = mathInput(this.form[type]);
    },
  }
};
</script>

<style type="text/css">
.frais-structures .el-collapse-item__header {
  padding-left: 15px;
  font-weight: bold;
  font-size: 15px;
}
.frais-structures .el-collapse-item__header .header-btn-group {
  width: -webkit-fill-available;
  text-align: right;
  padding-right: 20px;
}
.frais-structures .el-collapse-item__content {
  padding-bottom: 0;
}
.frais-structures .el-collapse-item__header i {
  font-weight: bold;
}
.frais-structures .text-input {
  width: 235px;
}
.frais-structures .el-input.is-disabled .el-input__inner {
  color: black;
}
.frais-structures .form-slider i {
  font-size: 25px;
  margin-left: 50px;
  cursor: pointer;
}
.frais-structures .el-form-item__label {
  line-height: 40px;
}
</style>
