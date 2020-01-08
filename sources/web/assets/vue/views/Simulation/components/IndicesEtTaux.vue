<template>
  <div class="container-fluid">
    <div class="admin-content-wrap">
      <h1 class="admin-content-title">Indice et Taux</h1>
	  <div class="">

      <div class=" indices-et-taux">
        <div class="d-flex justify-content-end mb-3">
          
            <el-button type="success" :disabled="!isModify" @click="exportIndicesTaux" >Exporter</el-button>

        </div>
        <el-table :data="indicesTaux" id="indice-table">
          <el-table-column label prop="type" width="200" fixed></el-table-column>

          <el-table-column label prop="indexationSurInflation" width="40">
            <template slot-scope="scope">
              <div v-if="scope.row.indexationSurInflation != null" style="text-align: center">
                <el-checkbox
                  v-bind:checked="scope.row.indexationSurInflation"
                  v-model="scope.row.indexationSurInflation"
                  @change="saveIndiceTaux(scope.$index, scope.row)"
                  :disabled="!isModify"
                ></el-checkbox>
              </div>
            </template>
          </el-table-column>

          <el-table-column prop="ecart" label="Indexation" width="100">
            <template slot-scope="scope">
              <el-input
                v-if="scope.row.indexationSurInflation !== null"
                v-model="scope.row.ecart"
                placeholder="0,00"
                v-bind:disabled="!scope.row.indexationSurInflation || !isModify"
                @change="saveIndiceTaux(scope.$index, scope.row)"
              ></el-input>
            </template>
          </el-table-column>

          <el-table-column
            prop="indicesTauxPeriodique"
            v-bind:label="parseInt(anneeDeReference).toString()"
            width="80"
          >
            <template slot-scope="scope">
              <el-input
                v-if="scope.row.type == 'Taux du livret A'"
                placeholder="0"
                v-model="scope.row.indicesTauxPeriodique.items[0].valeur"
                v-bind:disabled="!isModify"
                @change="saveIndiceTauxPeriodiqueNN10(scope.row,scope.row.id,scope.row.indicesTauxPeriodique.items[0].iteration, scope.row.indicesTauxPeriodique.items[0].valeur,0)"
              ></el-input>
            </template>
          </el-table-column>

          <el-table-column
            v-for="indice in 10"
            :key="indice"
            prop="indicesTauxPeriodique"
            v-bind:label="(parseInt(anneeDeReference) + indice).toString()"
            width="80"
          >
            <template slot-scope="scope">
              <el-input
                v-if="scope.row.type === 'Taux de livret A - Taux d\'inflation' || !isModify"
                :disabled="true"
                :value="indicesTaux[3].indicesTauxPeriodique.items[indice].valeur - indicesTaux[0].indicesTauxPeriodique.items[indice].valeur"
                placeholder="0"
              ></el-input>
              <el-input
                v-else-if="scope.row.type === 'Taux de livret A - Taux de variation de l\'IRL' || !isModify"
                :disabled="true"
                :value="indicesTaux[3].indicesTauxPeriodique.items[indice].valeur - indicesTaux[2].indicesTauxPeriodique.items[indice].valeur"
                placeholder="0"
              ></el-input>
              <el-input
                v-else-if="scope.row.type === 'Taux de livret A - Taux de rémunération de la trésorerie' || !isModify"
                :disabled="true"
                :value="indicesTaux[3].indicesTauxPeriodique.items[indice].valeur - indicesTaux[4].indicesTauxPeriodique.items[indice].valeur"
                placeholder="0"
              ></el-input>
              <el-input
                v-else-if="scope.row.indexationSurInflation"
                :value="calculatedPeriodique(indice, scope.row)"
                :disabled="true"
                placeholder="0"
              ></el-input>
              <el-input
                v-else-if="!scope.row.indexationSurInflation"
                v-model.lazy="scope.row.indicesTauxPeriodique.items[indice].valeur"
                @change="saveIndiceTauxPeriodiqueNN10(scope.row,scope.row.id,scope.row.indicesTauxPeriodique.items[indice].iteration,scope.row.indicesTauxPeriodique.items[indice].valeur, indice)"
                placeholder="0"
              ></el-input>
            </template>
          </el-table-column>

          <el-table-column
            prop="indicesTauxPeriodique"
            v-bind:label="(parseInt(anneeDeReference)+11).toString() + ' - ' +(parseInt(anneeDeReference)+50).toString()"
            width="100"
            :disabled="!isModify"
          >
            <template slot-scope="scope">
              <el-input
                v-if="scope.row.type === 'Taux de livret A - Taux d\'inflation' || !isModify"
                :disabled="true"
                :value="indicesTaux[3].indicesTauxPeriodique.items[11].valeur - indicesTaux[0].indicesTauxPeriodique.items[11].valeur"
                placeholder="0"
              ></el-input>
              <el-input
                v-else-if="scope.row.type === 'Taux de livret A - Taux de variation de l\'IRL' || !isModify"
                :disabled="true"
                :value="indicesTaux[3].indicesTauxPeriodique.items[11].valeur - indicesTaux[2].indicesTauxPeriodique.items[11].valeur"
                placeholder="0"
              ></el-input>
              <el-input
                v-else-if="scope.row.type === 'Taux de livret A - Taux de rémunération de la trésorerie' || !isModify"
                :disabled="true"
                :value="indicesTaux[3].indicesTauxPeriodique.items[11].valeur - indicesTaux[4].indicesTauxPeriodique.items[11].valeur"
                placeholder="0"
              ></el-input>
              <el-input
                v-else-if="scope.row.indexationSurInflation"
                :value="calculatedPeriodique(11, scope.row)"
                :disabled="true"
                placeholder="0"
              ></el-input>
              <el-input
                v-else-if="!scope.row.indexationSurInflation"
                v-model.lazy="scope.row.indicesTauxPeriodique.items[11].valeur"
                @change="saveIndiceTauxPeriodiqueN11N50(scope.row.id,scope.row.indicesTauxPeriodique.items[11].valeur,11)"
                placeholder="0"
              ></el-input>
            </template>
          </el-table-column>

          <el-table-column prop="arrow" width="70" fixed="right">
            <template slot-scope="scope">
              <div class="d-flex justify-content-end">
                <el-button
                  type="primary"
                  icon="el-icon-refresh"
                  v-if="scope.row.type !== 'Taux de livret A - Taux d\'inflation'
                  && scope.row.type !== 'Taux de livret A - Taux de variation de l\'IRL'
                  && scope.row.type !== 'Taux de livret A - Taux de rémunération de la trésorerie'"
                  :disabled="!isModify"
                  @click="pasteValueByArrowsButtons(scope.row)"
                ></el-button>
              </div>
            </template>
          </el-table-column>
        </el-table>
      </div>
	  </div>

    </div>
  </div>
</template>

<script>
import _ from "lodash";
import { evaluate } from "mathjs";
import { getFloat } from "../../../utils/helpers";
export default {
  name: "indicesTaux",
  data() {
    return {
      simulationID: this.$route.params.id,
      indicesTaux: null,
      anneeDeReference: null
    };
  },
  computed: {
    isModify() {
      return this.$store.getters['choixEntite/isModify'];
    }
  },
  created() {
    const loading = this.$loading({ lock: true });
    this.$apollo
      .query({
        query: require("../../../graphql/simulations/indicesTaux/indicesTaux.gql"),
        variables: {
          simulationID: this.simulationID
        }
      })
      .then(response => {
        this.indicesTaux = response.data.indicesTaux.items;
      })
      .finally(() => {
        this.getAnneeDeReference();
        loading.close();
      });
    this.$apollo
      .query({
        query: require("../../../graphql/administration/partagers/checkStatus.gql"),
        variables: {
          simulationID: this.simulationID
        }
      })
      .then(response => {
        this.$store.commit('choixEntite/setModify', response.data.checkStatus);
      });
  },
  methods: {
    calculatedPeriodique(index, row) {
      return getFloat(row.ecart) + getFloat(this.indicesTaux[0].indicesTauxPeriodique.items[index].valeur);
    },
    saveIndiceTaux(index, row) {
      for (let i = 0; i <= 4; i++) {
        this.$apollo.mutate({
          mutation: require("../../../graphql/simulations/indicesTaux/saveIndiceTaux.gql"),
          variables: {
            indiceTauxID: this.indicesTaux[i].id,
            indexSurInflation: this.indicesTaux[i].indexationSurInflation,
            ecart: this.indicesTaux[i].ecart,
            simulationId: this.simulationID
          }
        });
      }
    },
    saveIndiceTauxPeriodiqueNN10(
      row,
      indiceTauxID,
      iteration,
      valeur,
      index = null
    ) {
      if (/\t/.test(valeur)) {
        let split = valeur.split("\t");
        let incre = 0;
        for (let i = 0; i < split.length; i++) {
          row.indicesTauxPeriodique.items[iteration].valeur = split[incre];
          this.$apollo.mutate({
            mutation: require("../../../graphql/simulations/indicesTaux/saveIndiceTauxPeriodique.gql"),
            variables: {
              indiceTauxID: indiceTauxID,
              iteration: row.indicesTauxPeriodique.items[iteration].iteration,
              valeur: this.checkType(
                row.indicesTauxPeriodique.items[iteration].valeur
              ),
              simulationId: this.simulationID
            }
          });
          ++iteration;
          ++incre;
        }
      } else {
        const val = this.checkType(valeur);
        this.$apollo
          .mutate({
            mutation: require("../../../graphql/simulations/indicesTaux/saveIndiceTauxPeriodique.gql"),
            variables: {
              indiceTauxID: indiceTauxID,
              iteration: iteration,
              valeur: val,
              simulationId: this.simulationID
            }
          })
          .then(() => {
            if (index) {
              row.indicesTauxPeriodique.items[index].valeur = val;
            }
          });
      }
    },

    getAnneeDeReference() {
      this.$apollo
        .query({
          query: require("../../../../vue/graphql/simulations/simulation.gql"),
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
      for (var i = 0; i < 11; i++) {
        this.columns.push({
          label: (parseInt(this.anneeDeReference) + i).toString()
        });
      }
    },

    saveIndiceTauxPeriodiqueN11N50(indiceTauxID, valeur) {
      for (let j = 11; j <= 49; j++) {
        this.$apollo.mutate({
          mutation: require("../../../graphql/simulations/indicesTaux/saveIndiceTauxPeriodique.gql"),
          variables: {
            indiceTauxID: indiceTauxID,
            iteration: j,
            valeur: this.checkType(valeur),
            simulationId: this.simulationID
          }
        });
      }
    },
    checkType(valeur) {
      if (_.isEmpty(valeur)) {
        return 0;
      } else if (/[a-zA-Z]/.test(valeur)) {
        this.$message.error("Les charactères alphabétiques ne sont pas permis");
        return 0;
      } else if (/[{+*\/\-}]$/.test(valeur)) {
        return 0;
      } else if (typeof valeur === "number") {
        return valeur;
      } else {
        return evaluate(valeur);
      }
    },
    pasteValueByArrowsButtons(row) {
      for (let i = 11; i >= 0; i--) {
        if (row.indicesTauxPeriodique.items[i].valeur !== null && row.indicesTauxPeriodique.items[i].valeur !== 0) {
          for (let j = i; j <= 11; j++) {
            row.indicesTauxPeriodique.items[j + 1].valeur =
              row.indicesTauxPeriodique.items[i].valeur;
            this.$apollo.mutate({
              mutation: require("../../../graphql/simulations/indicesTaux/saveIndiceTauxPeriodique.gql"),
              variables: {
                indiceTauxID: row.id,
                iteration: j,
                valeur: row.indicesTauxPeriodique.items[i].valeur,
                simulationId: this.simulationID
              }
            });
          }
          break;
        }
      }
    },

    exportIndicesTaux() {
      window.location.href = "/export-indices-taux/" + this.simulationID;
    }
  }
};
</script>