<template>
  <div class="container-fluid">
    <div class="hypotheses admin-content-wrap px-5">
      <el-form
        :model="form"
        label-width="200px"
        class="block-form"
        ref="hypothesesForm"
        :rules="rules"
      >
        <el-row type="flex" align="middle">
          <el-col :span="12">
            <p class="sub-title admin-content-title">Hypothèses liées aux investissements</p>
          </el-col>
          <el-col class="d-flex justify-content-end" :span=12>
            <el-button
              type="success"
              @click="exportHypotheses"
              :disabled="!exportFlag || !isModify"
              class="export-button"
            >Exporter</el-button>
          </el-col>
        </el-row>
        <span>Mobilisation des fonds propres</span>
        <el-tooltip
          class="item"
          effect="dark"
          content="A l'OS équivaut à une approche à terminaison des opérations."
          placement="top"
        >
          <i class="el-icon-question"></i>
        </el-tooltip>
        <el-radio-group v-model="form.mobilisation" :disabled="!isModify" class="ml-2">
          <el-radio :label="true">A la livraison</el-radio>
          <el-radio :label="false">A l'OS</el-radio>
        </el-radio-group>
        <div class="row"  v-loading="isLoading">
          <div class="col-sm-12 col-lg-5">
            <div class="form-box">
              <p class="sub-title">Hypothèses liées aux opérations nouvelles de logements locatifs</p>
              <el-row type="flex">
                <el-col>
                  <el-form-item label="Maintenance courante" prop="maintenance">
                    <el-input
                      type="text"
                      v-model="form.maintenance"
                      placeholder="0"
                      autocomplete="off"
                      :disabled="!isModify"
                      class="number-input fixed-input"
                      @change="formatInput('maintenance')"
                    >
                    <template slot="append">€/lgt</template>
                    </el-input>
                  </el-form-item>
                </el-col>
                <el-col>
                  <el-form-item
                    label="Avec un différé de"
                    label-width="125px"
                    prop="maintenanceDiffere"
                  >
                    <el-input
                      type="text"
                      v-model="form.maintenanceDiffere"
                      @change="formatInput('maintenanceDiffere')"
                      placeholder="0"
                      autocomplete="off"
                      :disabled="!isModify"
                      class="year-input fixed-input"
                    >
                    <template slot="append">ans</template>
                    </el-input>
                  </el-form-item>
                </el-col>
              </el-row>
              <el-row type="flex">
                <el-col>
                  <el-form-item label="Gros entretien" prop="grosEntretien">
                    <el-input
                      type="text"
                      v-model="form.grosEntretien"
                      @change="formatInput('grosEntretien')"
                      placeholder="0"
                      autocomplete="off"
                      :disabled="!isModify"
                      class="number-input fixed-input"
                    >
                    <template slot="append">€/lgt</template>
                    </el-input>
                  </el-form-item>
                </el-col>
                <el-col>
                  <el-form-item
                    label="Avec un différé de"
                    label-width="125px"
                    prop="grosEntretienDiffere"
                  >
                    <el-input
                      type="text"
                      v-model="form.grosEntretienDiffere"
                      @change="formatInput('grosEntretienDiffere')"
                      placeholder="0"
                      autocomplete="off"
                      :disabled="!isModify"
                      class="year-input fixed-input"
                    >
                    <template slot="append">ans</template>
                    </el-input>
                  </el-form-item>
                </el-col>
              </el-row>
              <el-row type="flex">
                <el-col>
                  <el-form-item label="Provision pour gros entretien" prop="provisionGros">
                    <el-input
                      type="text"
                      v-model="form.provisionGros"
                      placeholder="0"
                      autocomplete="off"
                      :disabled="!isModify"
                      class="number-input fixed-input"
                      @change="formatInput('provisionGros')"
                    >
                    <template slot="append">€/lgt</template>
                    </el-input>
                  </el-form-item>
                </el-col>
                <el-col>
                  <el-form-item
                    label="Avec un différé de"
                    label-width="125px"
                    prop="provisionGrosDiffere"
                  >
                    <el-input
                      type="text"
                      v-model="form.provisionGrosDiffere"
                      @change="formatInput('provisionGrosDiffere')"
                      placeholder="0"
                      autocomplete="off"
                      :disabled="!isModify"
                      class="year-input fixed-input"
                    >
                    <template slot="append">ans</template>
                    </el-input>
                  </el-form-item>
                </el-col>
              </el-row>
              <el-form-item label="Taux de vacance logements" class="mt-3" prop="tauxVacance">
                <el-input
                  type="text"
                  v-model="form.tauxVacance"
                  @change="formatInput('tauxVacance')"
                  placeholder="%"
                  autocomplete="off"
                  :disabled="!isModify"
                  class="number-input fixed-input"
                >
                <template slot="append">%</template>
                </el-input>
              </el-form-item>
              <el-form-item
                label="Taux de vacance sur les garages/parkings"
                prop="tauxVacanceGarages"
              >
                <el-input
                  type="text"
                  v-model="form.tauxVacanceGarages"
                  @change="formatInput('tauxVacanceGarages')"
                  placeholder="%"
                  autocomplete="off"
                  :disabled="!isModify"
                  class="number-input fixed-input"
                >
                <template slot="append">%</template>
                </el-input>
              </el-form-item>
              <el-form-item label="Taux de vacance sur les commerces" prop="tauxVacanceCommerces">
                <el-input
                  type="text"
                  v-model="form.tauxVacanceCommerces"
                  @change="formatInput('tauxVacanceCommerces')"
                  placeholder="%"
                  autocomplete="off"
                  :disabled="!isModify"
                  class="number-input fixed-input"
                >
                <template slot="append">%</template>
                </el-input>
              </el-form-item>
              <el-form-item label="Application des frais de personnel et de gestion sur la variation des logts par tranche de" prop="applicationFrais">
                <el-input
                  type="text"
                  placeholder="1"
                  autocomplete="off"
                  class="number-input fixed-input"
                  v-model="form.applicationFrais"
                  @change="formatInput('applicationFrais')"></el-input>
              </el-form-item>
            </div>
          </div>
          <div class="col-sm-12 col-lg-5 offset-1">
            <div class="form-box">
              <p class="sub-title">
                <span>Evaluation des coûts de fonctionnement</span>
                <el-tooltip
                  class="item"
                  effect="dark"
                  content="Variation à la hausse / baisse selon l'évolution du patrimoine (logements et foyers)"
                  placement="top"
                >
                  <i class="el-icon-question"></i>
                </el-tooltip>
              </p>
              <el-form-item label="Frais de personnel" prop="fraisPersonnel">
                <el-input
                  type="text"
                  v-model="form.fraisPersonnel"
                  @change="formatInput('fraisPersonnel')"
                  placeholder="0"
                  autocomplete="off"
                  :disabled="!isModify"
                  class="number-input fixed-input"
                >
                <template slot="append">€/lgt</template>
                </el-input>
              </el-form-item>
              <el-form-item label="Frais de gestion" prop="fraisGestion">
                <el-input
                  type="text"
                  v-model="form.fraisGestion"
                  @change="formatInput('fraisGestion')"
                  placeholder="0"
                  autocomplete="off"
                  :disabled="!isModify"
                  class="number-input fixed-input"
                >
                <template slot="append">€/lgt</template>
                </el-input>
              </el-form-item>
              <el-form-item prop="seuilDeclenchement">
                <template slot="label">
                  <span>Seuil de déclenchement</span>
                  <el-tooltip
                    class="item"
                    effect="dark"
                    content="Application du supplément ou de l'économie de frais de personnel et de gestion à chaque palier de lgt définie dans la cellule Seuil de déclenchement (Ex : si seuil de déclenchement= 50 lgts alors le supplément ou l'économie se calculera uniquement à toute variation à la hausse / baisse de 50 lgts)"
                    placement="top"
                  >
                    <i class="el-icon-question"></i>
                  </el-tooltip>
                </template>
                <el-input
                  type="text"
                  v-model="form.seuilDeclenchement"
                  @change="formatInput('seuilDeclenchement')"
                  placeholder="0"
                  autocomplete="off"
                  :disabled="!isModify"
                  class="number-input fixed-input"
                >
                <template slot="append">lgt</template>
                </el-input>
              </el-form-item>
            </div>
            <div class="form-box">
              <p class="sub-title">
                <span>Montage et conduite d'opération locative</span>
                <el-tooltip
                  class="item"
                  effect="dark"
                  content="Coûts internes intégrés au prix de revient alimentant la production immobilisée"
                  placement="top"
                >
                  <i class="el-icon-question"></i>
                </el-tooltip>
              </p>
              <el-form-item label="Taux pour la maitrise d'ouvrage directe" prop="tauxDirecte">
                <el-input
                  type="text"
                  v-model="form.tauxDirecte"
                  @change="formatInput('tauxDirecte')"
                  placeholder="%"
                  autocomplete="off"
                  :disabled="!isModify"
                  class="number-input fixed-input"
                >
                <template slot="append">%</template>
                </el-input>
              </el-form-item>
              <el-form-item prop="tauxVefa">
                <template slot="label">
                  <span>Taux pour les acquisitions en VEFA</span>
                  <el-tooltip
                    class="item"
                    effect="dark"
                    content="Vente en l’état futur d'achèvement"
                    placement="top"
                  >
                    <i class="el-icon-question"></i>
                  </el-tooltip>
                </template>
                <el-input
                  type="text"
                  v-model="form.tauxVefa"
                   @change="formatInput('tauxVefa')"
                  placeholder="%"
                  autocomplete="off"
                  :disabled="!isModify"
                  class="number-input fixed-input"
                >
                <template slot="append">%</template>
                </el-input>
              </el-form-item>
              <el-form-item prop="tauxRehabilitation">
                <template slot="label">
                  <span>Taux pour la réhabilitation</span>
                  <el-tooltip
                    class="item"
                    effect="dark"
                    content="S'applique aux travaux immobilisés identifiés et non identifiés"
                    placement="top"
                  >
                    <i class="el-icon-question"></i>
                  </el-tooltip>
                </template>
                <el-input
                  type="text"
                  v-model="form.tauxRehabilitation"
                   @change="formatInput('tauxRehabilitation')"
                  placeholder="%"
                  autocomplete="off"
                  :disabled="!isModify"
                  class="number-input fixed-input"
                >
                <template slot="append">%</template>
                </el-input>
              </el-form-item>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-11">
            <div class="d-flex justify-content-end">
              <el-button
                type="success"
                @click="save('hypothesesForm')"
                :disabled="submitted || !isModify"
              >Valider</el-button>
            </div>
          </div>
        </div>
      </el-form>
    </div>
  </div>
</template>

<script>
import _ from "lodash";
import customValidator from "../../../utils/validation-rules";
import { mathInput } from "../../../utils/inputs";

export default {
	name: "Hypotheses",
	data() {
		return {
			simulationID: null,
			titlePage: 'Hypothèses',
			isLoading: true,
			form: {
				mobilisation: true,
        maintenance: null,
        maintenanceDiffere: null,
        grosEntretien: null,
        grosEntretienDiffere: null,
        provisionGros: null,
        provisionGrosDiffere: null,
        tauxVacance: null,
        tauxVacanceGarages: null,
        tauxVacanceCommerces: null,
        applicationFrais: null,
        fraisPersonnel: null,
        fraisGestion: null,
        seuilDeclenchement: null,
        tauxDirecte: null,
        tauxVefa: null,
        tauxRehabilitation: null
			},
			submitted: false,
			rules: {
				mobilisation: customValidator.getRule('required'),
				maintenance: customValidator.getRule('positiveInt'),
        maintenanceDiffere: customValidator.getPreset('differe'),
				grosEntretien: customValidator.getRule('positiveInt'),
        grosEntretienDiffere: customValidator.getPreset('differe'),
				provisionGros: customValidator.getRule('positiveInt'),
        provisionGrosDiffere: customValidator.getPreset('differe'),
				tauxVacance: customValidator.getRule('taux'),
				tauxVacanceGarages: customValidator.getRule('taux'),
				tauxVacanceCommerces: customValidator.getRule('taux'),
				applicationFrais: customValidator.getRule('positiveInt'),
				fraisPersonnel: customValidator.getRule('positiveInt'),
				fraisGestion: customValidator.getRule('positiveInt'),
				seuilDeclenchement: customValidator.getRule('positiveInt'),
				tauxDirecte: customValidator.getRule('taux'),
				tauxVefa: customValidator.getRule('taux'),
				tauxRehabilitation: customValidator.getRule('taux'),
			},
			exportFlag: false
		}
	},
  computed: {
    isModify() {
      return this.$store.getters['choixEntite/isModify'];
    }
  },
	created () {
		let simulationID = _.isNil(this.$route.params.id) ? null : this.$route.params.id;
		if (_.isNil(simulationID)) {
			return;
		}
		this.simulationID = simulationID;
		this.getHypotheses();
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
	methods: {
		save(hypothesesForm) {
			this.$refs[hypothesesForm].validate(valid => {
				if (valid) {
					this.submitted = true;
					this.isLoading = true;
					this.$apollo.mutate({
						mutation: require('../../../graphql/simulations/hypotheses/saveHypothese.gql'),
						variables: {
							hypothese: {
                                simulationId: this.simulationID,
                                id: this.form.id,
                                mobilisation: this.form.mobilisation,
                                maintenance: this.form.maintenance,
                                maintenanceDiffere: this.form.maintenanceDiffere,
                                grosEntretien: this.form.grosEntretien,
                                grosEntretienDiffere: this.form.grosEntretienDiffere,
                                provisionGros: this.form.provisionGros,
                                provisionGrosDiffere: this.form.provisionGrosDiffere,
                                tauxVacance: this.form.tauxVacance,
                                tauxVacanceGarages: this.form.tauxVacanceGarages,
                                tauxVacanceCommerces: this.form.tauxVacanceCommerces,
                                applicationFrais: this.form.applicationFrais,
                                fraisPersonnel: this.form.fraisPersonnel,
                                fraisGestion: this.form.fraisGestion,
                                seuilDeclenchement: this.form.seuilDeclenchement,
                                tauxDirecte: this.form.tauxDirecte,
                                tauxVefa: this.form.tauxVefa,
                                tauxRehabilitation: this.form.tauxRehabilitation
							}
						}
					}).then(() => {
						this.submitted = false;
						this.$message({
							showClose: true,
							message: 'Les valeurs ont bien été enregistrées.',
							type: 'success',
						});
						this.getHypotheses();
					}).catch((error) =>{
						this.$message({
							showClose: true,
							message: error.networkError.result,
							type: 'error',
						});
						this.submitted = false;
						this.isLoading = false;
					})
				}
			})
		},
		getHypotheses () {
			this.isLoading = true;
			this.$apollo.query({
				query: require('../../../graphql/simulations/hypotheses/hypotheses.gql'),
				fetchPolicy: 'no-cache',
				variables: {
					simulationID: this.simulationID
				}
			}).then((res) => {
				if (res.data && res.data.hypotheses && res.data.hypotheses.items.length > 0) {
					this.form = res.data.hypotheses.items[0];
					this.exportFlag = true;
				}
				this.isLoading = false;
			})
		},
		exportHypotheses() {
		   window.location.href = "/export-hypothese/" + this.simulationID;
		},
		formatInput(type){
			this.form[type] = mathInput(this.form[type]);
		}
	}
}
</script>

<style lang="scss" scoped>
.hypotheses .el-form-item__label {
  text-align: left !important;
  line-height: 15px;
  margin-top: 10px;
}
.hypotheses .number-input {
  width: 80px;
}
.hypotheses .year-input {
  width: 50px;
}
.hypotheses .fixed-input .el-input__inner {
  padding: 0 3px !important;
  text-align: right !important;
}
.hypotheses .sub-title {
  margin: 30px 0;
  color: #524646;
}
.hypotheses [class*=" el-icon-"],
.hypotheses [class^="el-icon-"] {
  color: #2491eb;
  font-size: 20px;
}
.form-box {
  $gap: 1em;
  margin-top: $gap;
  padding-top: $gap;
}
</style>

<style type="text/css">
.el-tooltip__popper.is-dark {
  max-width: 250px;
}
</style>