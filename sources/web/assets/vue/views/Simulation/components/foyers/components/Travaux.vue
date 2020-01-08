<template>
    <div class="travaux-foyers">
        <ApolloQuery
                :query="require('../../../../../graphql/simulations/foyers/travaux/travauxFoyers.gql')"
                :variables="{
                simulationId: simulationID
            }">
            <template slot-scope="{ result:{ loading, error, data }, isLoading, query }">
                <div v-if="error">Une erreur est survenue !</div>
                <el-table
                    v-loading="isLoading"
                    :data="tableData(data, query)"
                    style="width: 100%">
                    <el-table-column sortable column-key="numero" prop="numero">
                        <template slot="header">
                            <span title="N°">N°</span>
                        </template>
                    </el-table-column>
                    <el-table-column sortable column-key="nomIntervention" prop="nomIntervention" min-width="200">
                        <template slot="header">
                            <span title="Nom de l'intervention">Nom de l'intervention</span>
                        </template>
                    </el-table-column>
                    <el-table-column sortable column-key="nombreLogements" prop="nombreLogements" min-width="180">
                        <template slot="header">
                            <span title="Nombre équivalent logements">Nombre équivalent logements</span>
                        </template>
                    </el-table-column>
                    <el-table-column sortable column-key="anneeAgrement" prop="anneeAgrement" min-width="180">
                        <template slot="header">
                            <span title="Année d'agrément">Année d'agrément</span>
                        </template>
                    </el-table-column>
                    <el-table-column sortable column-key="dateAcquisition" prop="dateAcquisition" min-width="120">
                        <template slot-scope="scope">
                            {{scope.row.dateAcquisition | dateFR}}
                        </template>
                        <template slot="header">
                            <span title="Date OS">Date OS</span>
                        </template>
                    </el-table-column>
                    <el-table-column sortable column-key="dateTravaux" prop="dateTravaux" min-width="120">
                        <template slot-scope="scope">
                            {{scope.row.dateTravaux | dateFR}}
                        </template>
                        <template slot="header">
                            <span title="Date MES">Date MES</span>
                        </template>
                    </el-table-column>
                    <el-table-column sortable column-key="indexationIcc" prop="indexationIcc" min-width="160">
                        <template slot-scope="scope">
                            {{scope.row.indexationIcc ? 'Oui': 'Non'}}
                        </template>
                        <template slot="header">
                            <span title="A indexer à l'ICC">A indexer à l'ICC</span>
                        </template>
                    </el-table-column>
                    <el-table-column sortable column-key="modeleDamortissementNom" prop="modeleDamortissementNom" min-width="160">
                        <template slot="header">
                            <span title="Modèle d'amortissement technique">Modèle d'amortissement technique</span>
                        </template>
                    </el-table-column>
                    <el-table-column sortable column-key="prixRevient" prop="prixRevient" min-width="150">
                        <template slot="header">
                            <span title="Prix de revient">Prix de revient</span>
                        </template>
                    </el-table-column>
                    <el-table-column sortable column-key="fondsPropres" prop="fondsPropres" min-width="150">
                        <template slot="header">
                            <span title="Fonds propres">Fonds propres</span>
                        </template>
                    </el-table-column>
                    <el-table-column sortable column-key="subventionsEtat" prop="subventionsEtat" min-width="180" label="Subventions d'Etat">
                        <template slot="header">
                            <span title="Date OS">Date OS</span>
                        </template>
                    </el-table-column>
                    <el-table-column sortable column-key="subventionsAnru" prop="subventionsAnru" min-width="180">
                        <template slot="header">
                            <span title="Subventions d'Etat">Subventions d'Etat</span>
                        </template>
                    </el-table-column>
                    <el-table-column sortable column-key="subventionsEpci" prop="subventionsEpci" min-width="170">
                        <template slot="header">
                            <span title="Subventions EPCI / Commune">Subventions EPCI / Commune</span>
                        </template>
                    </el-table-column>
                    <el-table-column sortable column-key="subventionsDepartement" prop="subventionsDepartement" min-width="170">
                        <template slot="header">
                            <span title="Subventions département">Subventions département</span>
                        </template>
                    </el-table-column>
                    <el-table-column sortable column-key="subventionsRegion" prop="subventionsRegion" min-width="180">
                        <template slot="header">
                            <span title="Subventions Région">Subventions Région</span>
                        </template>
                    </el-table-column>
                    <el-table-column sortable column-key="subventionsCollecteur" prop="subventionsCollecteur" min-width="190">
                        <template slot="header">
                            <span title="Subventions collecteur">Subventions collecteur</span>
                        </template>
                    </el-table-column>
                    <el-table-column sortable column-key="autresSubventions" prop="autresSubventions" min-width="170">
                        <template slot="header">
                            <span title="Autres subventions">Autres subventions</span>
                        </template>
                    </el-table-column>
                    <el-table-column sortable column-key="totalEmprunt" prop="totalEmprunt" min-width="170">
                        <template slot="header">
                            <span title="Total (Emprunt)">Total (Emprunt)</span>
                        </template>
                    </el-table-column>
                    <el-table-column v-for="column in columns"
                        sortable
                        min-width="120"
                        :key="column.prop"
                        :prop="column.prop"
                        :label="column.label">
                        <template slot="header">
                            <span :title="column.label">{{ column.label }}</span>
                        </template>
                    </el-table-column>
                    <el-table-column fixed="right" width="120" label="Actions">
                        <template slot-scope="scope">
                            <el-button type="primary" :disabled="!isModify" icon="el-icon-edit" circle @click="handleEdit(scope.$index, scope.row)"></el-button>
                            <el-button type="danger" :disabled="!isModify" icon="el-icon-delete" circle @click="handleDelete(scope.$index, scope.row)"></el-button>
                        </template>
                    </el-table-column>
                </el-table>
            </template>
        </ApolloQuery>
        <el-row class="d-flex justify-content-end my-3">
            <el-button type="primary" :disabled="!isModify" @click="showCreateModal">Créer une nouvelle fiche de démolitions foyers</el-button>
        </el-row>
        <el-dialog
            title="Création/Modification des travaux foyers"
            :visible.sync="dialogVisible"
            :close-on-click-modal="false"
            width="75%">
            <el-row v-if="isEdit" class="form-slider text-center mb-4">
                <i class="el-icon-back font-weight-bold" @click="back"></i>
                <i class="el-icon-right font-weight-bold" @click="next"></i>
            </el-row>
            <el-form label-position="left" :model="form" :rules="formRules" label-width="180px" ref="nouveauxForm">
                <el-tabs v-model="activeTab" type="card">
                    <el-tab-pane label="Caractéristiques Générales" name="1">
                        <el-collapse v-model="collapseList">
                            <el-collapse-item title="Données Générales" name="1">
                                <el-row :gutter="20">
                                    <el-col :span="8">
                                        <el-form-item label="N°" prop="numero">
                                            <el-input type="text" v-model="form.numero" placeholder="N°" readonly></el-input>
                                        </el-form-item>
                                    </el-col>
                                    <el-col :span="8">
                                        <el-form-item label="Nom de l'opération" prop="nomIntervention">
                                            <el-input type="text" v-model="form.nomIntervention" placeholder="Nom de l'intervention"></el-input>
                                        </el-form-item>
                                    </el-col>
                                    <el-col :span="8">
                                        <el-form-item label="Nombre équivalent logement" prop="nombreLogements">
                                            <el-input   type="text" v-model="form.nombreLogements" placeholder="0"
                                                        @change="formatInput('nombreLogements')"></el-input>
                                        </el-form-item>
                                    </el-col>
                                </el-row>
                            </el-collapse-item>
                            <el-collapse-item title="Calendrier de l'opération" name="2">
                                <el-row :gutter="20">
                                    <el-col :span="8">
                                        <el-form-item label="Année d'Agrément" prop="anneeAgrement">
                                            <el-input   type="text" v-model="form.anneeAgrement" placeholder="0"
                                                        @change="formatInput('anneeAgrement')"></el-input>
                                        </el-form-item>
                                    </el-col>
                                    <el-col :span="8">
                                        <el-form-item label="Date d'ordre de service" prop="dateAcquisition">
                                            <el-date-picker
                                                v-model="form.dateAcquisition"
                                                type="month"
                                                :picker-options="datePickerOptions"
                                                value-format="yyyy-MM-dd"
                                                format="MM/yyyy"
                                                placeholder="Sélectionner">
                                            </el-date-picker>
                                        </el-form-item>
                                    </el-col>
                                    <el-col :span="8">
                                        <el-form-item label="Date de mise en service" prop="dateTravaux">
                                            <el-date-picker
                                                v-model="form.dateTravaux"
                                                type="month"
                                                :picker-options="datePickerOptions"
                                                value-format="yyyy-MM-dd"
                                                format="MM/yyyy"
                                                placeholder="Sélectionner">
                                            </el-date-picker>
                                        </el-form-item>
                                    </el-col>
                                </el-row>
                            </el-collapse-item>
                            <el-collapse-item title="Compléments" name="3">
                                <el-row class="mt-4">
                                    <el-col :span="5" style="padding-top: 50px;">
                                        <div class="carousel-head">
                                            <span>Redevance</span>
                                            <el-tooltip class="item" effect="dark" content="Saisir la variation de la redevance suite aux travaux" placement="top">
                                                <i class="el-icon-info"></i>
                                            </el-tooltip>
                                        </div>
                                    </el-col>
                                    <el-col :span="19">
                                        <periodique
                                            :anneeDeReference="anneeDeReference"
                                            v-model="form.periodiques"
                                            @hasError="hasPeriodiqueError"
                                        ></periodique>
                                    </el-col>
                                </el-row>
                            </el-collapse-item>
                        </el-collapse>
                    </el-tab-pane>
                    <el-tab-pane label="Investissements et financements" name="2">
                        <el-collapse v-model="collapseList">
                            <el-collapse-item title="Investissements en K€" name="1">
                                <el-row :gutter="20">
                                    <el-col :span="8">
                                        <el-form-item label="Prix de revient" prop="prixRevient">
                                            <el-input
                                                type="text"
                                                v-model="form.prixRevient"
                                                placeholder="0"
                                                class="fixed-input"
                                            ></el-input>
                                        </el-form-item>
                                        <el-checkbox v-model="form.indexationIcc" class="mb-3">Indexation à l'ICC</el-checkbox>
                                    </el-col>
                                    <el-col :span="8">
                                        <el-form-item label="Choisir un modèle d'amortissement technique" prop="modeleDamortissement">
                                            <el-select v-model="form.modeleDamortissement">
                                                <el-option
                                                    v-for="item in modeleDamortissements"
                                                    :key="item.id"
                                                    :label="item.nom"
                                                    :value="item.id"
                                                ></el-option>
                                            </el-select>
                                        </el-form-item>
                                    </el-col>
                                </el-row>
                            </el-collapse-item>
                            <el-collapse-item title="Financements en K€" name="2">
                                <div class="row mt-4">
                                    <div class="col-sm-12 col-md-7">
                                        <div class="row">
                                            <div class="col-sm-12 col-md-6" :span="13">
                                                <el-form-item label="Montant de fonds propres" prop="fondsPropres">
                                                    <el-input type="text" v-model="form.fondsPropres" placeholder="0" class="fixed-input"
                                                              @change="formatInput('fondsPropres')" ></el-input>
                                                </el-form-item>
                                                <el-form-item label="Total montant subventions" prop="totalSubventions">
                                                    {{totalSubventions}}
                                                </el-form-item>
                                                <el-form-item label="Total montant emprunts" prop="totalMontant">
                                                    {{totalMontant}}
                                                </el-form-item>
                                                <el-form-item label="Reste à financer" prop="resteFinancer">{{resteFinancer}}K€</el-form-item>
                                            </div>
                                            <div class="col-sm-12 col-md-6 border-left">
                                                <p><strong>Montant des Subventions en K€</strong></p>
                                                <el-form-item label="Subventions d'Etat" prop="subventionsEtat">
                                                    <el-input
                                                        type="text"
                                                        v-model="form.subventionsEtat"
                                                        placeholder="0"
                                                        class="fixed-input"
                                                    ></el-input>
                                                </el-form-item>
                                                <el-form-item label="Subventions ANRU" prop="subventionsAnru">
                                                    <el-input
                                                        type="text"
                                                        v-model="form.subventionsAnru"
                                                        placeholder="0"
                                                        class="fixed-input"
                                                    ></el-input>
                                                </el-form-item>
												<el-form-item label="Subventions Régions" prop="subventionsRegion">
													<el-input
															type="text"
															v-model="form.subventionsRegion"
															placeholder="0"
															class="fixed-input"
													></el-input>
												</el-form-item>
                                                <el-form-item label="Subventions départements" prop="subventionsDepartement">
                                                    <el-input
                                                        type="text"
                                                        v-model="form.subventionsDepartement"
                                                        placeholder="0"
                                                        class="fixed-input"
                                                    ></el-input>
                                                </el-form-item>
												<el-form-item label="Subventions EPCI/Communes" prop="subventionsEpci">
													<el-input
															type="text"
															v-model="form.subventionsEpci"
															placeholder="0"
															class="fixed-input"
													></el-input>
												</el-form-item>
                                                <el-form-item label="Subventions collecteurs" prop="subventionsCollecteur">
                                                    <el-input
                                                        type="text"
                                                        v-model="form.subventionsCollecteur"
                                                        placeholder="0"
                                                        class="fixed-input"
                                                    ></el-input>
                                                </el-form-item>
                                                <el-form-item label="Autres subventions" prop="autresSubventions">
                                                    <el-input
                                                        type="text"
                                                        v-model="form.autresSubventions"
                                                        placeholder="0"
                                                        class="fixed-input"
                                                    ></el-input>
                                                </el-form-item>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-12 col-md-5 border-left">
                                        <p><strong>Emprunts en K€</strong></p>
                                        <span>Ajouter un emprunt</span>
                                        <div class="d-flex">
                                            <el-select v-model="form.typeEmprunt">
                                                <el-option
                                                    v-for="item in availableTypesEmprunts"
                                                    :key="item.id"
                                                    :label="item.nom"
                                                    :value="item.id"
                                                ></el-option>
                                            </el-select>
                                            <el-button
                                                type="primary"
                                                class="ml-2"
                                                @click="addTypeEmprunt"
                                                :disabled="!form.typeEmprunt"
                                            >Ajouter</el-button>
                                        </div>
                                        <div class="row mt-5" v-if="form.typeEmprunt">
                                            <div class="col-sm-12 col-md-4">
                                                <el-form-item label="Montant" label-width="70px" prop="montant">
                                                    <el-input
                                                        type="text"
                                                        v-model="form.montant"
                                                        placeholder="0"
                                                        class="fixed-input"
                                                    ></el-input>
                                                </el-form-item>
                                            </div>
                                            <div class="col-sm-12 col-md-8">
                                                <el-form-item
                                                    label="Date de la première annuité"
                                                    label-width="105px"
                                                    prop="datePremiere"
                                                >
                                                    <el-date-picker
                                                        v-model="form.datePremiere"
                                                        type="month"
                                                        :picker-options="datePickerOptions"
                                                        value-format="yyyy-MM-dd"
                                                        format="MM/yyyy"
                                                        placeholder="JJ/MM/AAAA"
                                                        style="width:130px;"
                                                    ></el-date-picker>
                                                    <el-tooltip
                                                        class="item"
                                                        effect="dark"
                                                        content="Date de première annuité ou intérêts en cas de différé d'amortissement"
                                                        placement="top"
                                                    >
                                                        <i class="el-icon-info"></i>
                                                    </el-tooltip>
                                                </el-form-item>
                                            </div>
                                        </div>
                                        <p class="mt-5"><strong>Liste des emprunts</strong></p>
                                        <el-table :data="form.typeEmpruntTravauxFoyer" style="width: 100%">
                                            <el-table-column
                                                sortable
                                                column-key="typesEmprunts"
                                                prop="typesEmprunts"
                                                min-width="60"
                                                label="Numéro"
                                            >
                                                <template slot-scope="scope">{{scope.row.typesEmprunts.nom}}</template>
                                            </el-table-column>
                                            <el-table-column
                                                sortable
                                                column-key="montant"
                                                prop="montant"
                                                min-width="100"
                                                label="Montant emprunt"
                                                align="center"
                                            ></el-table-column>
                                            <el-table-column
                                                sortable
                                                column-key="datePremiere"
                                                prop="datePremiere"
                                                min-width="100"
                                                label="Date d'emprunt"
                                                align="center"
                                            >
                                                <template slot-scope="scope">{{scope.row.datePremiere | dateFR}}</template>
                                            </el-table-column>
                                            <el-table-column fixed="right" width="90" label="supprimer">
                                                <template slot-scope="scope">
                                                    <el-button
                                                        type="danger"
                                                        icon="el-icon-delete"
                                                        circle
                                                        @click="handleDeleteEmprunt(scope.$index, scope.row)"
                                                    ></el-button>
                                                </template>
                                            </el-table-column>
                                        </el-table>
                                        <div class="text-center">
                                            <p>Total des emprunts: {{totalMontant}}</p>
                                        </div>
                                    </div>
                                </div>
                            </el-collapse-item>
                        </el-collapse>
                    </el-tab-pane>
                </el-tabs>
            </el-form>
            <div slot="footer" class="dialog-footer">
                <el-button type="primary" @click="dialogVisible = false">Retour</el-button>
                <el-button type="success" :disabled="isSubmitting" @click="save('nouveauxForm')">Valider</el-button>
            </div>
        </el-dialog>
    </div>
</template>

<script>
import moment from "moment";
import { getFloat, getIncremental, updateData } from "../../../../../utils/helpers";
import customValidator from "../../../../../utils/validation-rules";
import Periodique from "../../../../../components/partials/Periodique";
import { checkAllPeriodics, initPeriodic, periodicFormatter, mathInput } from '../../../../../utils/inputs';

export default {
  name: "TravauxFoyers",
  components: { Periodique },
  data() {
    var validateNom = (rule, value, callback) => {
      if (value === "") {
        callback(new Error("Veuillez sélectionner un nom"));
      } else if (this.checkExistNom(value)) {
        callback(new Error("Cette nom existe déjà"));
      } else {
        callback();
      }
    };
    var resteFinancerValidate = (rule, value, callback) => {
      if (this.resteFinancer !== 0) {
        callback(new Error("le plan de financement n’est pas équilibré"));
      } else {
        callback();
      }
    };
    return {
      simulationID: null,
      anneeDeReference: null,
      collapseList: ['1'],
      travauxFoyers: [],
      modeleDamortissements: [],
      dialogVisible: false,
      form: null,
      selectedIndex: 0,
      isEdit: false,
      isSubmitting: false,
      activeTab: "1",
      typesEmprunts: [],
      availableTypesEmprunts: [],
      columns: [],
      datePickerOptions: {
        disabledDate(date) {
          return date < new Date();
        }
      },
      formRules: {
        numero: customValidator.getPreset("number.positiveInt"),
        nomIntervention: [
          { required: true, validator: validateNom, trigger: "change" }
        ],
        nombreLogements: customValidator.getRule("positiveInt"),
        anneeAgrement: customValidator.getRule("positiveInt"),
        dateAcquisition: customValidator.getRule("required"),
        dateTravaux: customValidator.getRule("required"),
        prixRevient: customValidator.getPreset("number.positiveDouble"),
        fondsPropres: customValidator.getPreset("number.positiveDouble"),
        subventionsEtat: customValidator.getRule("positiveDouble"),
        subventionsAnru: customValidator.getRule("positiveDouble"),
        subventionsEpci: customValidator.getRule("positiveDouble"),
        subventionsDepartement: customValidator.getRule(
          "positiveDouble"
        ),
        subventionsRegion: customValidator.getRule("positiveDouble"),
        subventionsCollecteur: customValidator.getRule(
          "positiveDouble"
        ),
        autresSubventions: customValidator.getRule("positiveDouble"),
        resteFinancer: [{ validator: resteFinancerValidate, trigger: "change" }]
      }
    };
  },
  created() {
    this.simulationID = _.isNil(this.$route.params.id)
      ? null
      : this.$route.params.id;
    if (_.isNil(this.simulationID)) {
      return;
    }
    this.initForm();
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
    mounted(){
        this.getTypeEmprunts();
        this.getModeleDamortissements();
    },
    methods: {
      initForm() {
        this.form = {
          id: null,
          numero: getIncremental(this.travauxFoyers, "numero"),
          nomIntervention: "",
          indexationIcc: false,
          typeEmpruntTravauxFoyer: [],
          periodiques: {
            revedance: initPeriodic()
          }
        };
      },
      getAnneeDeReference() {
        this.$apollo.query({
          query: require('../../../../../graphql/simulations/simulation.gql'),
          variables: {
            simulationID: this.simulationID
          }
        }).then((res) => {
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
                  label: (parseInt(this.anneeDeReference) + i).toString() + ' Redevance',
                  prop: `periodiques.revedance[${i}]`
              });
          }
      },
      getTypeEmprunts() {
        this.$apollo.query({
            query: require('../../../../../graphql/simulations/types-emprunts/typesEmprunts.gql'),
            variables: {
                simulationID: this.simulationID
            }
        }).then((res) => {
            if (res.data && res.data.typesEmprunts) {
                this.typesEmprunts = res.data.typesEmprunts.items;
            }
        });
      },
      getModeleDamortissements() {
        this.$apollo.query({
            query: require('../../../../../graphql/simulations/modeles-amortissements/modeleDamortissements.gql'),
            variables: {
                simulationId: this.simulationID
            }
        }).then((res) => {
            if (res.data && res.data.modeleDamortissements) {
                this.modeleDamortissements = res.data.modeleDamortissements.items;
            }
        });
      },
      hasPeriodiqueError(error) {
        this.periodiqueHasError = error;
      },
      addTypeEmprunt() {
        if (
          this.form.typeEmprunt &&
          this.form.montant &&
          this.form.datePremiere
        ) {
          const typeEmprunt = this.availableTypesEmprunts.find(
            item => (item.id == this.form.typeEmprunt)
          );
          this.form.typeEmpruntTravauxFoyer.push({
            montant: this.form.montant | 0,
            datePremiere: this.form.datePremiere,
            typesEmprunts: typeEmprunt,
            local: true
          });
          this.form.typeEmprunt = null;
          this.getAvailableTypesEmprunts();
        }
      },
      tableData(data, query) {
          if (!_.isNil(data)) {
              this.query = query;
              const travauxFoyers = data.travauxFoyers.items.map(item => {
                  let revedance = [];
                  item.periodique.items.forEach(periodique => {
                      revedance[periodique.iteration - 1] = periodique.revedance;
                  });
                  let row = {...item};
                  row.typeEmpruntTravauxFoyer = item.typeEmpruntTravauxFoyer.items;
                  row.modeleDamortissement = item.modeleDamortissement? item.modeleDamortissement.id: null;
                  row.modeleDamortissementNom = item.modeleDamortissement? item.modeleDamortissement.nom: null;
                  row.periodiques = {
                      revedance
                  };

                  return row;
              });
              this.travauxFoyers = travauxFoyers;
              return travauxFoyers;
          } else {
              return [];
          }
      },
      showCreateModal() {
          this.initForm();
          this.dialogVisible = true;
          this.isEdit = false;
          this.getAvailableTypesEmprunts();
      },
      handleEdit(index, row) {
          this.dialogVisible = true;
          this.form = {...row};
          this.selectedIndex = index;
          this.isEdit = true;
          this.getAvailableTypesEmprunts();
      },
      handleDelete(index, row) {
        this.$confirm('Êtes-vous sûr de vouloir supprimer ces travaux foyer?')
            .then(() => {
                this.$apollo.mutate({
                    mutation: require('../../../../../graphql/simulations/foyers/travaux/removeTravauxFoyer.gql'),
                    variables: {
                        travauxFoyerUUID: row.id,
                        simulationId: this.simulationID
                    }
                }).then(() => {
                    updateData(this.query, this.simulationID).then(() => {
                        this.$message({
                            showClose: true,
                            message: 'Ces travaux ont bien été supprimés.',
                            type: 'success'
                        });
                    })
                }).catch(error => {
                    updateData(this.query, this.simulationID).then(() => {
                        this.$message({
                            showClose: true,
                            message: 'Ces travaux n\'existent pas.',
                            type: 'error',
                        });
                    });
                });
            })
        },
        save(formName) {
            this.$refs[formName].validate((valid) => {
                if (valid && checkAllPeriodics(this.form.periodiques)) {
                    this.isSubmitting = true;
                    const typeEmpruntTravauxFoyer = this.form.typeEmpruntTravauxFoyer.map(item => {
                        return JSON.stringify({
                            id: item.typesEmprunts.id,
                            montant: item.montant,
                            datePremiere: item.datePremiere
                        })
                    });
                    this.$apollo.mutate({
                        mutation: require('../../../../../graphql/simulations/foyers/travaux/saveTravauxFoyer.gql'),
                        variables: {
                            travaux: {
                                uuid: this.form.id,
                                simulationId: this.simulationID,
                                numero: this.form.numero,
                                nomIntervention: this.form.nomIntervention,
                                nombreLogements: this.form.nombreLogements,
                                anneeAgrement: this.form.anneeAgrement,
                                dateAcquisition: this.form.dateAcquisition,
                                dateTravaux: this.form.dateTravaux,
                                indexation_icc: this.form.indexationIcc,
                                prixRevient: this.form.prixRevient,
                                fondsPropres: this.form.fondsPropres,
                                subventionsEtat: this.form.subventionsEtat,
                                subventionsAnru: this.form.subventionsAnru,
                                subventionsEpci: this.form.subventionsEpci,
                                subventionsDepartement: this.form.subventionsDepartement,
                                subventionsRegion: this.form.subventionsRegion,
                                subventionsCollecteur: this.form.subventionsCollecteur,
                                autresSubventions: this.form.autresSubventions,
                                totalEmprunt: this.totalMontant,
                                typeEmprunts: typeEmpruntTravauxFoyer,
                                modeleDamortissementUUID: this.form.modeleDamortissement,
                                periodique: JSON.stringify({
                                    revedance: this.form.periodiques.revedance
                                })
                            }
                        }
                    }).then(() => {
                        this.dialogVisible = false;
                        this.isSubmitting = false;
                        updateData(this.query, this.simulationID).then(() => {
                            this.$message({
                                showClose: true,
                                message: 'Ces travaux ont bien été enregistrés.',
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
        handleDeleteEmprunt(index, row) {
            this.$confirm('Êtes-vous sûr de vouloir supprimer ce type d’emprunt?')
                .then(_ => {
                    if (row.local) {
                        this.form.typeEmpruntTravauxFoyer.splice(index, 1);
                        this.getAvailableTypesEmprunts();
                    } else {
                        this.$apollo.mutate({
                            mutation: require('../../../../../graphql/simulations/foyers/travaux/removeTypeDempruntTravauxFoyer.gql'),
                            variables: {
                                typesEmpruntsUUID: row.typesEmprunts.id,
                                travauxFoyerUUID: this.travauxFoyers[this.selectedIndex].id
                            }
                        }).then((res) => {
                            this.form.typeEmpruntTravauxFoyer.splice(index, 1);
                            this.getAvailableTypesEmprunts();
                        });
                    }
                })
                .catch(_ => {});
        },
        getAvailableTypesEmprunts() {
            let emprunts = [];
            const linkedEmprunts = this.form.typeEmpruntTravauxFoyer;
            this.typesEmprunts.forEach(item => {
                if(!linkedEmprunts.some(emprunt => emprunt.typesEmprunts.id === item.id)) {
                    emprunts.push(item);
                }
            });
            this.availableTypesEmprunts = emprunts;
        },
        checkExistNom(value) {
            let travauxFoyers = this.travauxFoyers;
            if (this.isEdit) {
                travauxFoyers = travauxFoyers.filter(item => item.nomIntervention !== this.travauxFoyers[this.selectedIndex].nomIntervention);
            }
            return travauxFoyers.some(item => item.nomIntervention === value);
        },
        formatInput(type) {
            this.form[type] = mathInput(this.form[type]);
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
      back() {
        if (this.selectedIndex > 0) {
          this.selectedIndex--;
          this.form = { ...this.travauxFoyers[this.selectedIndex] };
        }
      },
      next() {
        if (this.selectedIndex < this.travauxFoyers.length - 1) {
          this.selectedIndex++;
          this.form = { ...this.travauxFoyers[this.selectedIndex] };
        }
      },
      periodicOnChange(type) {
        let newPeriodics = this.form.periodiques[type];
        this.form.periodiques[type] = [];
        this.form.periodiques[type] = periodicFormatter(newPeriodics);
      }
  },
  computed: {
    totalMontant() {
      let emprunt = 0;
      this.form.typeEmpruntTravauxFoyer.forEach(item => {
        emprunt += getFloat(item.montant);
      });
      return emprunt;
    },
    totalSubventions() {
        return getFloat(this.form.subventionsEtat) + getFloat(this.form.subventionsAnru) + getFloat(this.form.subventionsEpci) + getFloat(this.form.subventionsDepartement) + getFloat(this.form.subventionsRegion) + getFloat(this.form.subventionsCollecteur) + getFloat(this.form.autresSubventions);
    },
    resteFinancer() {
      return getFloat(this.form.prixRevient) - (getFloat(this.form.fondsPropres) + this.totalSubventions + this.totalMontant);
    },
    isModify() {
        return this.$store.getters['choixEntite/isModify'];
    }
  },
  filters: {
    dateFR: function(value) {
      return value ? moment.utc(String(value)).format("MM/YYYY") : "";
    }
  }
};
</script>

<style>
.travaux-foyers .el-form-item__label {
    margin-top: 7px;
};
.travaux-foyers .form-slider i {
    font-size: 25px;
    margin-left: 50px;
    cursor: pointer;
}
.travaux-foyers .fixed-input {
    width: 80px;
}
.travaux-foyers .carousel-head {
    height: 50px;
}
.travaux-foyers .el-tooltip.el-icon-info {
    font-size: 25px;
    color: #2491eb;
    vertical-align: middle;
}
.travaux-foyers .el-collapse-item__header{
    padding-left: 15px;
    font-weight: bold;
    font-size: 16px;
    color: #00436f;
    margin-top: 20px
}
.travaux-foyers .el-collapse-item__header:hover{
    background-color: rgba(0, 0, 0, 0.03);
}
.travaux-foyers .el-collapse-item__content {
    padding-top: 20px;
}
</style>
