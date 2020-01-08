<template>
  <div class="container-fluid">
    <div class="types-emprunts admin-content-wrap">
      <h1 class="admin-content-title">Types d'emprunts</h1>
      <ApolloQuery
              :query="require('../../../graphql/simulations/types-emprunts/typesEmprunts.gql')"
              :variables="{
                    simulationID: simulationID
                }"
      >
        <template slot-scope="{ result:{ loading, error, data }, isLoading , query}">
          <div class="d-flex mb-4">
            <div class="ml-auto d-flex">
              <el-upload
                      ref="upload"
                      :action="'/import-types-emprunts/'+ simulationID"
                      multiple
                      :limit="10"
                      :disabled="!isModify"
                      :on-success="onSuccessImport"
                      :on-error="onErrorImport"
              >
                <el-button size="small" type="primary">Importer</el-button>
              </el-upload>
              <el-button type="success" :disabled="!isModify" class="ml-2" @click="exportTypesEmprunts">Exporter</el-button>
            </div>
          </div>
          <el-row>
            <div v-if="error">Une erreur est survenue !</div>
            <el-table v-loading="isLoading" :data="tableData(data, query)" class="fw">
              <el-table-column
                      sortable
                      column-key="numero"
                      prop="numero"
                      title
                      label="N° du type d’emprunt"
              >
                <template v-slot:header>
                  <span title="N° du type d’emprunt">N° du type d’emprunt</span>
                </template>
              </el-table-column>
              <el-table-column sortable column-key="nom" prop="nom" label="Nom du type d’emprunt">
                <template v-slot:header>
                  <span title="Nom du type d’emprunt">Nom du type d’emprunt</span>
                </template>
              </el-table-column>
              <el-table-column
                      sortable
                      column-key="margeEmprunt"
                      prop="margeEmprunt"
                      label="Marge sur emprunt"
              >
                <template v-slot:header>
                  <span title="Marge sur emprunt">Marge sur emprunt</span>
                </template>
              </el-table-column>
              <el-table-column
                      sortable
                      column-key="tauxInteret"
                      prop="tauxInteret"
                      label="Taux d’intérêt"
              >
                <template v-slot:header>
                  <span title="Taux d’intérêt">Taux d’intérêt</span>
                </template>
              </el-table-column>
              <el-table-column
                      sortable
                      column-key="tauxPlancher"
                      prop="tauxPlancher"
                      label="Taux plancher"
              >
                <template v-slot:header>
                  <span title="Taux plancher">Taux plancher</span>
                </template>
              </el-table-column>
              <el-table-column
                      sortable
                      column-key="dureeEmprunt"
                      prop="dureeEmprunt"
                      label="Durée de l’emprunt"
              >
                <template v-slot:header>
                  <span title="Durée de l’emprunt">Durée de l’emprunt</span>
                </template>
              </el-table-column>
              <el-table-column
                      sortable
                      column-key="tauxProgressivite"
                      prop="tauxProgressivite"
                      label="Taux de progressivité"
              >
                <template v-slot:header>
                  <span title="Taux de progressivité">Taux de progressivité</span>
                </template>
              </el-table-column>
              <el-table-column
                      sortable
                      column-key="dureeAmortissement"
                      prop="dureeAmortissement"
                      label="Durée du différé"
              >
                <template v-slot:header>
                  <span title="Durée du différé">Durée du différé</span>
                </template>
              </el-table-column>
              <el-table-column prop="revisability" label="Révisabilité" width="120" align="center">
                <template slot-scope="scope">
                  <el-tag
                          :type="scope.row.revisability === 0 ? 'primary' : (scope.row.revisability === 1 ? 'success' : 'warning')"
                          disable-transitions
                  >{{scope.row.revisability === 0 ? 'Simple': (scope.row.revisability === 1 ? 'Double': 'Double limité')}}</el-tag>
                </template>
              </el-table-column>
              <el-table-column fixed="right" width="120" label="Actions">
                <template slot-scope="scope">
                  <el-tooltip class="item" effect="dark" content="Editer" placement="top">
                    <el-button
                            :disabled="!isModify"
                            type="primary"
                            icon="el-icon-edit"
                            circle
                            @click="handleEdit(scope.$index, scope.row)"
                    ></el-button>
                  </el-tooltip>
                  <el-tooltip class="item" effect="dark" content="Supprimer" placement="top">
                    <el-button
                            :disabled="!isModify"
                            type="danger"
                            icon="el-icon-delete"
                            circle
                            @click="handleDelete(scope.$index, scope.row)"
                    ></el-button>
                  </el-tooltip>
                </template>
              </el-table-column>
            </el-table>
          </el-row>
          <el-row class="d-flex justify-content-end my-3">
            <el-button type="primary" :disabled="!isModify" @click="showCreateModal">Créer un type d’emprunt</el-button>
          </el-row>
        </template>
      </ApolloQuery>
      <el-dialog title="Créer un type d’emprunt" :visible.sync="dialogVisible" :close-on-click-modal="false" width="70%">
        <el-form :model="form" label-width="220px" ref="typeDempruntsForm" :rules="rules">
          <el-row type="flex" justify="space-between">
            <el-col :span="8">
              <el-form-item label="N° du type d’emprunt:" prop="numero">
                <el-input
                        type="text"
                        v-model="form.numero"
                        placeholder="0"
                        autocomplete="off"
                        class="number-input fixed-input"
                ></el-input>
              </el-form-item>
            </el-col>
            <el-col :span="14">
              <el-form-item label="Nom du type d’emprunt:" prop="nom">
                <el-input
                        type="text"
                        v-model="form.nom"
                        placeholder="Nom du type d’emprunt"
                        autocomplete="off"
                ></el-input>
              </el-form-item>
            </el-col>
          </el-row>
          <el-checkbox v-model="form.livretA" class="mb-3">Emprunts indexés / Livret A</el-checkbox>
          <div v-if="form.livretA">
            <el-form-item label="Marge sur emprunt:" prop="margeEmprunt">
              <el-input
                      type="text"
                      v-model="form.margeEmprunt"
                      placeholder="0.00"
                      autocomplete="off"
                      class="number-input fixed-input"
              ></el-input>
            </el-form-item>
            <el-row class="mb-3">
              <el-checkbox v-model="tauxPlancherEnable" class="mb-3">Taux plancher</el-checkbox>
              <el-form-item prop="tauxPlancher">
                <el-input
                        type="text"
                        v-model="form.tauxPlancher"
                        placeholder="%"
                        autocomplete="off"
                        class="number-input fixed-input"
                        :disabled="!tauxPlancherEnable"
                ></el-input>
              </el-form-item>
            </el-row>
            <p>Soit un taux d'intérêt initial de la période d'amortissement de</p>
            <el-row type="flex" class="mb-3 period-wrapper">
              <div
                      v-for="(rate, index) in getAsArray(form.typesEmpruntsPeriodique)"
                      v-bind:key="index"
                      class="pl-1 pr-1 period-item"
              >
                <p class="text-center">N + {{ index + 1 }}</p>
                <el-input
                        type="text"
                        placeholder="0.00"
                        class="number-input"
                        :value="tauxInteretInitialPeriodique"
                        :disabled="true"
                ></el-input>
              </div>
            </el-row>
          </div>

          <el-form-item label="Taux d’intérêt:" prop="tauxInteret">
            <el-input
                    type="text"
                    placeholder="%"
                    autocomplete="off"
                    class="number-input fixed-input"
                    v-model="form.tauxInteret"
                    :disabled="!form.livretA"
                    @change="formatInput('tauxInteret')"
            ></el-input>
          </el-form-item>
          <el-form-item label="Durée de l’emprunt:" prop="dureeEmprunt">
            <el-input
                    type="text"
                    placeholder="0"
                    autocomplete="off"
                    class="number-input fixed-input"
                    v-model="form.dureeEmprunt"
                    @change="formatInput('dureeEmprunt')"
            ></el-input>
          </el-form-item>
          <el-form-item label="Dont différé d’amortissement:" prop="dureeAmortissement">
            <el-input
                    type="text"
                    placeholder="0"
                    autocomplete="off"
                    class="number-input fixed-input"
                    v-model="form.dureeAmortissement"
                    @change="formatInput('dureeAmortissement')"
            ></el-input>
          </el-form-item>
          <el-row class="mb-3" style="padding-left: 220px">
            <span>
              Soit une durée d’amortissement de :
              <b>{{(form.dureeEmprunt - form.dureeAmortissement) || 0}}</b> ans
            </span>
          </el-row>
          <el-form-item
                  v-if="form.tauxInteret !== 0 || form.margeEmprunt !== 0"
                  label="Taux de progressivité:"
                  prop="tauxProgressivite"
          >
            <el-input
                    type="text"
                    placeholder="%"
                    autocomplete="off"
                    class="number-input fixed-input"
                    v-model="form.tauxProgressivite"
                    @change="formatInput('tauxProgressivite')"
            ></el-input>
            <span v-if="form.livretA" class="ml-2">Révisabilité:</span>
            <el-radio-group v-if="form.livretA" v-model="form.revisability">
              <el-radio :label="0">Simple</el-radio>
              <el-radio :label="1" :disabled="tauxPlancherEnable">Double</el-radio>
              <el-radio :label="2" :disabled="tauxPlancherEnable">Double limité</el-radio>
            </el-radio-group>
          </el-form-item>
          <el-row v-if="!form.livretA">
            <el-form-item>
              <span>Taux de la première annuité payée après différé d'amortissement:</span>
              <el-input
                      type="text"
                      placeholder="%"
                      autocomplete="off"
                      class="number-input fixed-input"
                      :disabled="true"
                      v-model="form.firstAnnuityRate"
                      @change="formatInput('tauxProgressivite')"
              ></el-input>
            </el-form-item>
          </el-row>
          <span v-if="form.livretA">Taux de la première annuité payée après différé d'amortissement:</span>
          <el-row v-if="form.livretA" type="flex" class="period-wrapper">
            <div
                    v-for="(rate, index) in getAsArray(form.typesEmpruntsPeriodique)"
                    v-bind:key="index"
                    class="pl-1 pr-1 period-item"
            >
              <p class="text-center">N + {{ index + 1 }}</p>
              <el-input
                      type="text"
                      placeholder="%"
                      class="number-input"
                      :value="rate.tauxPremiereAnnuitePayee"
                      :disabled="true"
              ></el-input>
            </div>
          </el-row>
        </el-form>
        <div slot="footer" class="dialog-footer">
          <el-button type="outline-primary" @click="dialogVisible = false">Retour</el-button>
          <el-button type="primary" @click="save('typeDempruntsForm')" :disabled="submitted">Valider</el-button>
        </div>
      </el-dialog>
    </div>
  </div>
</template>

<script>
  import _ from "lodash";
  import customValidator from "../../../utils/validation-rules";
  import { mathInput } from "../../../utils/inputs";
  import { updateData } from "../../../utils/helpers";

export default {
  name: "TypeDemprunts",
  data() {
    return {
      simulationID: null,
      dialogVisible: false,
      isEdit: false,
      confirmDialogVisible: false,
      tauxPlancherEnable: false,
      form: {},
      submitted: false,
      query: null,
      rules: {
        numero: customValidator.getPreset("number.positiveInt"),
        nom: [
          customValidator.getRule("required"),
          customValidator.getRule("maxVarchar")
        ],
        tauxInteret: customValidator.getRule("taux"),
        dureeEmprunt: customValidator.getPreset("number.positiveInt"),
        dureeAmortissement: customValidator.getRule("positiveInt"),
        tauxProgressivite: customValidator.getRule("taux"),
        margeEmprunt: customValidator.getRule("positiveDouble"),
        tauxPlancher: customValidator.getRule("taux")
      }
    };
  },
  created() {
    let simulationID = _.isNil(this.$route.params.id)
      ? null
      : this.$route.params.id;
    if (_.isNil(simulationID)) {
      this.$router.push({ path: "/" });
    }
    this.simulationID = simulationID;
    this.$apollo
      .query({
        query: require("../../../graphql/administration/partagers/checkStatus.gql"),
        variables: {
          simulationID: this.simulationID
        }
      })
      .then(response => {
        this.$store.commit('choixEntite/setModify', response.data.checkStatus);
      })
  },
  computed: {
    tauxInteretInitialPeriodique() {
      // The calculation must be: Taux d'intérêt - Taux de progressivité) / [1 - (1+ Taux de progressivité) / (1+ Taux d'intérêt)]
      //TODO: use MathJS instead
      let tauxInteretInitial = 0,
        firstFactor = this.form.tauxInteret - this.form.tauxProgressivite,
        secondFactor =
          (1 + this.form.tauxProgressivite) / (1 + this.form.tauxInteret),
        calculation = firstFactor / (1 - secondFactor);
      return this.form.livretA === true ? calculation : tauxInteretInitial;
    },
    isModify() {
      return this.$store.getters['choixEntite/isModify'];
    }
  },
  methods: {
    initForm() {
      this.form = {
        numero: null,
        nom: "",
        livretA: false,
        tauxInteret: null,
        dureeEmprunt: null,
        dureeAmortissement: null,
        tauxProgressivite: null,
        firstAnnuityRate: null,
        margeEmprunt: null,
        tauxPlancher: null,
        revisability: 0
      };
    },
    showCreateModal() {
      this.initForm();
      this.dialogVisible = true;
      this.submitted = false;
      this.isEdit = false;
    },
    handleEdit(index, row) {
      this.dialogVisible = true;
      this.form = { ...row };
      this.isEdit = true;
    },
    handleDelete(index, row) {
      this.$confirm(
        "Êtes-vous sûr de vouloir supprimer ce type d'emprunt?"
      ).then(() => {
        this.$apollo
          .mutate({
            mutation: require("../../../graphql/simulations/types-emprunts/removeTypeEmprunt.gql"),
            variables: {
              id: row.id,
              simulationId: this.simulationID
            }
          })
          .then(() => {
            updateData(this.query, this.simulationID).then(() => {
              this.$message({
                showClose: true,
                message: "Le type d'emprunt a bien été supprimé.",
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
      });
    },
    tableData(data, query) {
      if (!_.isNil(data)) {
        this.query = query;
        return data.typesEmprunts.items;
      } else {
        return [];
      }
    },
    getAsArray(data) {
      if (!_.isNil(data)) {
        return data.items;
      } else {
        const item = { tauxInteretInitial: 0, tauxPremiereAnnuitePayee: 0 };
        const items = [];
        for (var i = 0; i < 12; i++) {
          items.push(item);
        }
        return items;
      }
    },
    save(typeDempruntsForm) {
      this.$refs[typeDempruntsForm].validate(valid => {
        if (valid) {
          this.submitted = true;
          this.$apollo
            .mutate({
              mutation: require("../../../graphql/simulations/types-emprunts/saveTypeEmprunt.gql"),
              variables: {
                typeEmprunt: {
                  uuid: this.form.id,
                  numero: this.form.numero,
                  simulationId: this.simulationID,
                  nom: this.form.nom,
                  tauxInteret: this.form.tauxInteret,
                  margeEmprunt: this.form.margeEmprunt,
                  dureeEmprunt: this.form.dureeEmprunt | 0,
                  dureeAmortissement: this.form.dureeAmortissement | 0,
                  revisability: this.form.revisability,
                  livretA: this.form.livretA,
                  tauxPlancherEnable: this.tauxPlancherEnable,
                  tauxPlancher: this.form.tauxPlancher,
                  tauxProgressivite: this.form.tauxProgressivite,
                  edit: this.isEdit
                }
              }
            })
            .then(() => {
              this.submitted = false;
              this.dialogVisible = false;
              updateData(this.query, this.simulationID).then(() => {
                this.$message({
                  showClose: true,
                  message: "Le type d'emprunt a bien été enregistré.",
                  type: "success"
                });
              });
            })
            .catch(error => {
              this.submitted = false;
              this.$message({
                showClose: true,
                message: error.networkError.result,
                type: "error"
              });
            });
        }
      });
    },
    exportTypesEmprunts() {
      window.location.href = "/export-types-emprunts/" + this.simulationID;
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
    },
    formatInput(type) {
      this.form[type] = mathInput(this.form[type]);
    }
  }
};
</script>

<style type="text/css">
  .types-emprunts .el-button--small {
    font-weight: 500;
    padding: 0 15px;
    height: 40px;
    font-size: 14px;
    border-radius: 4px;
    text-align: center;
  }
</style>
