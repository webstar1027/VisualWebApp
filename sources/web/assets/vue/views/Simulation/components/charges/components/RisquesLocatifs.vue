<template>
  <div class="risques-locatifs admin-content-wrap">
    <div class="d-flex">
      <h1 class="admin-content-title">Risques Locatifs</h1>
      <el-tooltip
        class="item ml-2 mt-1"
        effect="dark"
        content="Pour renseigner les taux de vacance sur le développement, se reporter au formulaire des hypothèses liées aux investissements"
        placement="top"
      >
        <i class="el-icon-info"></i>
      </el-tooltip>
	  <el-col :span="2" :offset="15">
		<el-button type="success" :disabled="!isModify" @click="exportRisquesLocatifs">Exporter</el-button>
	  </el-col>
    </div>
    <div class="d-flex mb-4">

    </div>
    <el-row>
      <el-col :span="6">
        <p>
          <b>Taux des impayés</b>
          <el-tooltip
            class="item"
            effect="dark"
            content="Coût des impayés exprimé en % des loyers quittancés totaux"
            placement="top"
          >
            <i class="el-icon-info"></i>
          </el-tooltip>
        </p>

        <p class="mt-4">Taux annuel</p>
      </el-col>
      <el-col :span="18" v-loading="isLoading">
        <el-carousel :autoplay="false" :loop="false" height="130px" trigger="click" arrow="always">
          <el-carousel-item v-for="item in 5" :key="item">
            <div class="item-wrapper">
              <div v-for="key in 12" :key="key" class="period-item">
                <span
                  class="input-wrapper"
                  :class="{'is-error':!isFloat(risquesLocatifs.acne[getIteration(item, key)])}"
                >
                  <p v-if="getIteration(item, key) < 50" class="text-center">{{parseInt(anneeDeReference) + getIteration(item, key)}}</p>
                  <p v-else class="text-center">&nbsp;</p>
                  <el-input
                    type="text"
                    placeholder="0.00"
                    v-model="risquesLocatifs.acne[getIteration(item, key)]"
                    :disabled="!isModify || getIteration(item, key) > 49"
                    @change="save(0, 'acne')"
                  ></el-input>
                </span>
              </div>
              <div class="period-item">
                <p>&nbsp;</p>
                <div class="reset-control">
                  <el-button :disabled="!isModify" @click="reset(0,'acne')">
                    <i class="el-icon-refresh"></i>
                  </el-button>
                </div>
              </div>
            </div>
          </el-carousel-item>
        </el-carousel>
      </el-col>
    </el-row>
    <el-row class="mt-5">
      <p>
        <b>Taux de pertes de loyers dues à la vacances sur le patrimoine de référence</b>
      </p>
      <el-col :span="6">
        <p class="mt-4">
          Taux de vacances sur les logements familliaux
          <el-tooltip
            class="item"
            effect="dark"
            content="Pertes de loyers rapportée aux loyers théoriques logements du patrimoine de référence"
            placement="top"
          >
            <i class="el-icon-info"></i>
          </el-tooltip>
        </p>
        <p class="mt-6">
          Taux de vacance sur les garages et les parkings
          <el-tooltip
            class="item"
            effect="dark"
            content="Pertes de loyers rapportées aux loyers théoriques des garages et parkings du patrimoine de référence. Pour le développement, se reporter au formulaire des hypothèses liées aux investissements"
            placement="top"
          >
            <i class="el-icon-info"></i>
          </el-tooltip>
        </p>
        <p class="mt-6">
          Taux de vacance sur les locaux commerciaux
          <el-tooltip
            class="item"
            effect="dark"
            content="Perte de loyer rapportées aux loyers théoriques des locaux commerciaux du patrimoine de référence. Pour le développement, se reporter au formulaire des hypothèses liées aux investissements"
            placement="top"
          >
            <i class="el-icon-info"></i>
          </el-tooltip>
        </p>
      </el-col>
      <el-col :span="18" v-loading="isLoading">
        <el-carousel :autoplay="false" :loop="false" trigger="click" arrow="always">
          <el-carousel-item v-for="item in 5" :key="item">
            <div class="item-wrapper">
              <div v-for="key in 12" :key="key" class="period-item">
                <span
                  class="input-wrapper"
                  :class="{'is-error':!isFloat(risquesLocatifs.patrimoine[getIteration(item, key)])}"
                >
                  <p v-if="getIteration(item, key) < 50" class="text-center">{{parseInt(anneeDeReference) + getIteration(item, key)}}</p>
                  <p v-else class="text-center">&nbsp;</p>
                  <el-input
                    type="text"
                    placeholder="0.00"
                    v-model="risquesLocatifs.patrimoine[getIteration(item, key)]"
                    :disabled="!isModify || getIteration(item, key) > 49"
                    @change="save(1,'patrimoine')"
                  ></el-input>
                </span>
                <span
                  class="input-wrapper"
                  :class="{'is-error':!isFloat(risquesLocatifs.garages[getIteration(item, key)])}"
                >
                  <p class="text-center">&nbsp;</p>
                  <el-input
                    type="text"
                    placeholder="0.00"
                    v-model="risquesLocatifs.garages[getIteration(item, key)]"
                    :disabled="!isModify || getIteration(item, key) > 49"
                    @change="save(2, 'garages')"
                  ></el-input>
                </span>
                <span
                  class="input-wrapper"
                  :class="{'is-error':!isFloat(risquesLocatifs.commerciaux[getIteration(item, key)])}"
                >
                  <p class="text-center">&nbsp;</p>
                  <el-input
                    type="text"
                    placeholder="0.00"
                    v-model="risquesLocatifs.commerciaux[getIteration(item, key)]"
                    :disabled="!isModify || getIteration(item, key) > 49"
                    @change="save(3, 'commerciaux')"
                  ></el-input>
                </span>
              </div>
              <div class="period-item">
                <p>&nbsp;</p>
                <div class="reset-control">
                  <el-button :disabled="!isModify" @click="reset(1, 'patrimoine')">
                    <i class="el-icon-refresh"></i>
                  </el-button>
                </div>
                <div class="reset-control">
                  <el-button :disabled="!isModify" @click="reset(2, 'garages')">
                    <i class="el-icon-refresh"></i>
                  </el-button>
                </div>
                <div class="reset-control">
                  <el-button :disabled="!isModify" @click="reset(3, 'commerciaux')">
                    <i class="el-icon-refresh"></i>
                  </el-button>
                </div>
              </div>
            </div>
          </el-carousel-item>
        </el-carousel>
      </el-col>
    </el-row>
    <el-row class="mt-5">
      <el-col :span="6">
        <p>
          <b>Taux de charges non récupérées sur la vacance logement</b>
          <el-tooltip
            class="item"
            effect="dark"
            content="Pertes de loyers rapportées aux loyers théoriques des garages et parkings du patrimoine de référence. Pour le développement, se reporter au formulaire des hypothèses liées aux investissements"
            placement="top"
          >
            <i class="el-icon-info"></i>
          </el-tooltip>
        </p>
        <p>Taux annuel</p>
      </el-col>
      <el-col :span="18" v-loading="isLoading">
        <el-carousel :autoplay="false" :loop="false" height="130px" trigger="click" arrow="always">
          <el-carousel-item v-for="item in 5" :key="item">
            <div class="item-wrapper">
              <div v-for="key in 12" :key="key" class="period-item">
                <span
                  class="input-wrapper"
                  :class="{'is-error':!isFloat(risquesLocatifs.annuel[getIteration(item, key)])}"
                >
                  <p v-if="getIteration(item, key) < 50" class="text-center">{{parseInt(anneeDeReference) + getIteration(item, key)}}</p>
                  <p v-else class="text-center">&nbsp;</p>
                  <el-input
                    type="text"
                    placeholder="0.00"
                    v-model="risquesLocatifs.annuel[getIteration(item, key)]"
                    :disabled="!isModify || getIteration(item, key) > 49"
                    @change="save(4, 'annuel')"
                  ></el-input>
                </span>
              </div>
              <div class="period-item">
				<p class="text-center">&nbsp;</p>
                <el-button :disabled="!isModify" @click="reset(4, 'annuel')">
                  <i class="el-icon-refresh"></i>
                </el-button>
              </div>
            </div>
          </el-carousel-item>
        </el-carousel>
      </el-col>
    </el-row>
  </div>
</template>

<script>
import {
  isFloat,
  periodicFormatter,
  checkPeriodic,
  repeatPeriodic
} from "../../../../../utils/inputs";
export default {
  name: "RisquesLocatifs",
  data() {
    return {
      simulationID: null,
      anneeDeReference: null,
      risquesLocatifs: {
        acne: [],
        patrimoine: [],
        garages: [],
        commerciaux: [],
        annuel: []
      },
      acne: [],
      patrimoine: [],
      garages: [],
      commerciaux: [],
      annuel: [],
      isLoading: true,
      inputError: null
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
    this.getRisquesLocatifs();
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
    isFloat: isFloat,
    getIteration(item, key) {
      return ((item - 1) * 11) + key - 1;
    },
    getRisquesLocatifs() {
      this.$apollo
        .query({
          query: require("../../../../../graphql/simulations/charges/risquesLocatifs/risquesLocatifs.gql"),
          fetchPolicy: "no-cache",
          variables: {
            simulationId: this.simulationID
          }
        })
        .then(res => {
          this.processData(res.data.risquesLocatifs.items);
          this.isLoading = false;
        });
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
          }
        });
    },
    processData(data) {
      data.sort((a, b) => a.iteration - b.iteration);
      this.risquesLocatifs.acne = data.map(item => item.acne);
      this.risquesLocatifs.patrimoine = data.map(
        item => item.tauxVacancePatrimoine
      );
      this.risquesLocatifs.garages = data.map(item => item.tauxVacanceGarages);
      this.risquesLocatifs.commerciaux = data.map(
        item => item.tauxVacanceCommerciaux
      );
      this.risquesLocatifs.annuel = data.map(item => item.tauxAnnuel);
    },
    save(type, title) {
      let periodics = {};
      periodics[title] = periodicFormatter(this.risquesLocatifs[title]);
      this.risquesLocatifs = Object.assign({}, this.risquesLocatifs, periodics);

      if (checkPeriodic(this.risquesLocatifs[title])) {
        this.saveRisquesLocatifs(type, periodics).then(() => {
          this.$message({
            showClose: true,
            message: "Les valeurs ont été enregistrées.",
            type: "success"
          });
        });
      } else {
        this.showError();
      }
    },
    saveRisquesLocatifs(type, periodics) {
      return this.$apollo.mutate({
        mutation: require("../../../../../graphql/simulations/charges/risquesLocatifs/resetRisquesLocatifs.gql"),
        variables: {
          simulationId: this.simulationID,
          periodique: JSON.stringify(periodics),
          type: type
        }
      });
    },
    reset(type, title) {
      let items = periodicFormatter(this.risquesLocatifs[title]);
      if (checkPeriodic(items)) {
        const periodics = repeatPeriodic(items);
        let newPeriodics = {};
        newPeriodics[title] = periodics;
        this.saveRisquesLocatifs(type, newPeriodics).then(() => {
          this.$message({
            showClose: true,
            message: "Les valeurs ont été enregistrées.",
            type: "success"
          });
          this.risquesLocatifs = Object.assign(
            {},
            this.risquesLocatifs,
            newPeriodics
          );
        });
      } else {
        this.showError();
      }
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
    exportRisquesLocatifs() {
      window.location.href = "/export-risques-locatifs/" + this.simulationID;
    }
  }
};
</script>

<style type="text/css">
.risques-locatifs {
  font-size: 14px;
}
.risques-locatifs .mt-6 {
  margin-top: 2.4rem;
}
.risques-locatifs .mt-4 {
  margin-top: 1.8rem !important;
}
.risques-locatifs .item-wrapper {
  margin: 6px 60px;
  display: table;
}
.risques-locatifs .period-item {
  width: 8.33%;
  font-size: 12px;
  padding: 0 5px;
  display: table-cell;
  text-align: center;
}
.risques-locatifs .period-item .el-input__inner {
  padding: 0 3px;
  text-align: right;
}
.risques-locatifs .el-carousel__button {
  background-color: #2591eb;
  pointer-events: none;
}
.risques-locatifs .el-carousel__arrow i {
  color: #2591eb;
}
.risques-locatifs .reset-control {
  width: 40px;
  height: 75px;
  padding-top: 0px;
}
.risques-locatifs .el-icon-refresh {
  color: #2591eb;
  font-size: 20px;
  font-weight: bold;
  cursor: pointer;
}
.risques-locatifs .input-wrapper.is-error input {
  border-color: #f56c6c;
}

.risques-locatifs .input-wrapper span {
  display: none;
  color: #f56c6c;
}

.risques-locatifs .input-wrapper input {
  border: 1px solid;
  border-radius: 0;
}

.risques-locatifs .input-wrapper.is-error span {
  display: contents;
}
.spin {
  animation: rotation 2s infinite linear;
}
@keyframes rotation {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(359deg);
  }
}
.el-tooltip.el-icon-info {
  font-size: 25px;
  color: #2491eb;
  vertical-align: middle;
}
  .risques-locatifs .admin-content-title{
	width: 20%;
  }
</style>
