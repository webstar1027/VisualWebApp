<template>
  <div class="demolition-nonidentifiees">
    <div class="d-flex mb-4">
      <div class="ml-auto d-flex">
        <el-upload
          ref="upload"
          :action="'/import-nonIdentifiees/'+ simulationID"
          multiple
          :limit="10"
          :disabled="!isModify"
          :on-success="onSuccess"
          :on-error="onError"
        >
          <el-button size="small" type="primary">Importer</el-button>
        <el-button class="ml-2" type="success" :disabled="!isModify" @click="exportNonIdentifees">Exporter</el-button>
        </el-upload>
      </div>
    </div>
    <div v-if="error">Une erreur est survenue !</div>
    <el-table v-loading="isLoading" :data="tableData(data)" style="width: 100%">
      <el-table-column
        sortable
        column-key="nGroupe"
        prop="nGroupe"
        min-width="100"
        label="N° groupe"
      >
        <template slot="header">
          <span title="N° groupe">N° groupe</span>
        </template>
      </el-table-column>
      <el-table-column
        sortable
        column-key="nomCategorie"
        prop="nomCategorie"
        min-width="150"
        label="Nom catégorie"
      >
        <template slot="header">
          <span title="Nom catégorie">Nom catégorie</span>
        </template>
      </el-table-column>
      <el-table-column
        sortable
        column-key="conventionAnru"
        prop="conventionAnru"
        min-width="150"
        label="Conventions ANRU"
      >
        <template slot-scope="scope">
          <span>{{scope.row.conventionAnru ? 'Oui' : 'Non'}}</span>
        </template>
        <template slot="header">
          <span title="Conventions ANRU">Conventions ANRU</span>
        </template>
      </el-table-column>
      <el-table-column
        sortable
        column-key="logementsConventionees"
        prop="logementsConventionees"
        min-width="150"
        label="Logements conventionnés"
      >
        <template slot-scope="scope">
          <span>{{scope.row.logementsConventionees ? 'Oui' : 'Non'}}</span>
        </template>
        <template slot="header">
          <span title="Logements conventionnés">Logements conventionnés</span>
        </template>
      </el-table-column>
      <el-table-column
        sortable
        column-key="indexationIcc"
        prop="indexationIcc"
        min-width="150"
        label="Indexation à l’ICC"
      >
        <template slot-scope="scope">
          <span>{{scope.row.indexationIcc ? 'Oui' : 'Non'}}</span>
        </template>
        <template slot="header">
          <span title="Indexation à l’ICC">Indexation à l’ICC</span>
        </template>
      </el-table-column>
      <el-table-column
        sortable
        column-key="tfpb"
        prop="tfpb"
        min-width="150"
        label="TFPB en €/logt"
      >
        <template slot="header">
          <span title="TFPB en €/logt">TFPB en €/logt</span>
        </template>
      </el-table-column>
      <el-table-column
        sortable
        column-key="grosEntretien"
        prop="grosEntretien"
        min-width="150"
        label="Gros entretien en €/logt"
      >
        <template slot="header">
          <span title="Gros entretien en €/logt">Gros entretien en €/logt</span>
        </template>
      </el-table-column>
      <el-table-column
        sortable
        column-key="maintenanceCourante"
        prop="maintenanceCourante"
        min-width="150"
        label="Maintenance courante en €/logt"
      >
        <template slot="header">
          <span title="Maintenance courante en €/logt">Maintenance courante en €/logt</span>
        </template>
      </el-table-column>
      <el-table-column
        sortable
        column-key="nombreAnneesAmortissements"
        prop="nombreAnneesAmortissements"
        min-width="150"
        label="Nombre d'années d'amortissements financiers résiduels"
      >
        <template slot="header">
          <span title="Nombre d'années d'amortissements financiers résiduels">Nombre d'années d'amortissements financiers résiduels</span>
        </template>
      </el-table-column>
      <el-table-column
        sortable
        column-key="foundsPropres"
        prop="foundsPropres"
        min-width="140"
        label="Fonds propres"
      >
        <template slot="header">
          <span title="Fonds propres">Fonds propres</span>
        </template>
      </el-table-column>
      <el-table-column
        sortable
        column-key="subventionsEtat"
        prop="subventionsEtat"
        min-width="120"
        label="Subventions d'Etat"
      >
        <template slot="header">
          <span title="Subventions d'Etat">Subventions d'Etat</span>
        </template>
      </el-table-column>
      <el-table-column
        sortable
        column-key="subventionsAnru"
        prop="subventionsAnru"
        min-width="120"
        label="Subventions ANRU"
      >
        <template slot="header">
          <span title="Subventions ANRU">Subventions ANRU</span>
        </template>
      </el-table-column>
      <el-table-column
        sortable
        column-key="subventionsEpci"
        prop="subventionsEpci"
        min-width="140"
        label="Subventions EPCI / Commune"
      >
        <template slot="header">
          <span title="Subventions EPCI / Commune">Subventions EPCI / Commune</span>
        </template>
      </el-table-column>
      <el-table-column
        sortable
        column-key="subventionsDepartement"
        prop="subventionsDepartement"
        min-width="120"
        label=" Subventions département"
      >
        <template slot="header">
          <span title="Subventions département">Subventions département</span>
        </template>
      </el-table-column>
      <el-table-column
        sortable
        column-key="subventionsRegion"
        prop="subventionsRegion"
        min-width="120"
        label="Subventions Région"
      >
        <template slot="header">
          <span title="Subventions Région">Subventions Région</span>
        </template>
      </el-table-column>
      <el-table-column
        sortable
        column-key="subventionsCollecteur"
        prop="subventionsCollecteur"
        min-width="120"
        label="Subventions collecteur"
      >
        <template slot="header">
          <span title="Subventions collecteur">Subventions collecteur</span>
        </template>
      </el-table-column>
      <el-table-column
        sortable
        column-key="autresSubventions"
        prop="autresSubventions"
        min-width="120"
        label="Autres subventions"
      >
        <template slot="header">
          <span title="Autres subventions">Autres subventions</span>
        </template>
      </el-table-column>
      <el-table-column
        v-for="column in columns"
        sortable
        min-width="120"
        :key="column.prop"
        :prop="column.prop"
        :label="column.label"
      >
        <template slot="header">
          <span :title="column.label">{{ column.label }}</span>
        </template>
      </el-table-column>
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

    <el-row class="d-flex justify-content-end my-3">
      <el-button type="primary" :disabled="!isModify" @click="showCreateModal">Créer une démolition non identifiée</el-button>
    </el-row>

    <el-dialog
            :title="dialogTitle"
            :visible.sync="dialogVisible"
            :close-on-click-modal="false"
            width="75%">
            <el-row v-if="isEdit" class="form-slider text-center mb-4">
                <i class="el-icon-back font-weight-bold" @click="back"></i>
                <i class="el-icon-right font-weight-bold" @click="next"></i>
            </el-row>
            <el-form label-position="left" :model="form" :rules="formRules" label-width="180px" ref="demolitionForm">
                <el-tabs v-model="activeTab" type="card">
                    <el-tab-pane label="Caractéristiques Générales" name="1">
                      <el-collapse v-model="collapseList">
                            <el-collapse-item title="Données Générales" name="1">
                                <el-row :gutter="20">
                                    <el-col :span="6">
                                        <el-form-item label="N° de l'opération" prop="nGroupe">
                                            <el-input type="text" v-model="form.nGroupe" placeholder="0" class="fixed-input"></el-input>
                                        </el-form-item>
                                        <el-checkbox v-model="form.indexationIcc">Indexation à l’ICC</el-checkbox>
                                    </el-col>
                                    <el-col :span="6">
                                        <el-form-item label="Nom catégorie" prop="nomCategorie">
                                            <el-input type="text" v-model="form.nomCategorie" placeholder="Nom catégorie"></el-input>
                                        </el-form-item>
                                        <el-checkbox v-model="form.conventionAnru">Convention ANRU</el-checkbox>
                                    </el-col>
                                    <el-col :span="6">
                                        <el-form-item label="Surface moyenne / logt en m²" prop="surfaceMoyenne">
                                            <el-input type="text" v-model="form.surfaceMoyenne" placeholder="0" class="fixed-input"
                                                      @change="formatInput('surfaceMoyenne')"></el-input>
                                        </el-form-item>
                                        <el-checkbox v-model="form.logementsConventionees">Logements conventionnés</el-checkbox>
                                    </el-col>
                                    <el-col :span="6">
                                        <el-form-item label="Loyer mensuel moyen en €/m²" prop="loyerMensuel">
                                            <el-input type="text" v-model="form.loyerMensuel" placeholder="0" class="fixed-input"
                                                      @change="formatInput('loyerMensuel')"></el-input>
                                        </el-form-item>
                                    </el-col>
                                </el-row>
                            </el-collapse-item>
                            <el-collapse-item title="Réductions liées aux démolitions" name="2">
                                <el-row :gutter="20">
                                    <el-col :span="6">
                                        <el-form-item label="TFPB en €/logt" prop="tfpb">
                                            <el-input type="text" v-model="form.tfpb" placeholder="0" class="fixed-input"
                                                      @change="formatInput('tfpb')"></el-input>
                                        </el-form-item>
                                    </el-col>
                                    <el-col :span="6">
                                        <el-form-item label="Maintenance courante en €/logt" prop="maintenanceCourante">
                                            <el-input type="text" v-model="form.maintenanceCourante" placeholder="0" class="fixed-input"
                                                      @change="formatInput('maintenanceCourante')"></el-input>
                                        </el-form-item>
                                    </el-col>
                                    <el-col :span="6">
                                        <el-form-item label="Gros entretien en €/logt" prop="grosEntretien">
                                            <el-input type="text" v-model="form.grosEntretien" placeholder="0" class="fixed-input"
                                                      @change="formatInput('grosEntretien')"></el-input>
                                        </el-form-item>
                                    </el-col>
                                    <el-col :span="6">
                                        <el-form-item label="Nombre d'années d'amortissements financiers résiduels" prop="nombreAnneesAmortissements" class="custom-append-input">
                                            <el-input type="text" v-model="form.nombreAnneesAmortissements" class="fixed-input" placeholder="0"
                                                      @change="formatInput('nombreAnneesAmortissements')">
                                              <template slot="append">
                                                 <el-tooltip class="item" effect="dark" content="visial calcule les économies d’annuités à partir du remboursement du CRD et d’un taux d’intérêts moyen" placement="top">
                                                    <i class="el-icon-info"></i>
                                                </el-tooltip>
                                              </template>
                                            </el-input>
                                        </el-form-item>
                                    </el-col>
                                </el-row>
                            </el-collapse-item>
                        </el-collapse>
                    </el-tab-pane>
                    <el-tab-pane label="Investissements et financements" name="2">
                        <el-collapse v-model="collapseList">
                            <el-collapse-item title="Investissements" name="1">
                                <el-checkbox v-model="form.indexationIcc">Indexation à l’ICC</el-checkbox>
                                <el-row class="mt-2">
                                    <el-col :span="5" style="padding-top: 55px;">
                                        <div class="carousel-head">Nombre de logements démolis</div>
                                        <div class="carousel-head">Coût moyen de la démolition en K€/lgt</div>
                                        <div class="carousel-head">Remboursement du CRD en K€/lgt</div>
                                        <div class="carousel-head">Coûts annexes des démolitions en K€/lgt</div>
                                    </el-col>
                                    <!-- <el-col :span="2" class="text-center">
                                        <p></p>
                                        <el-checkbox v-model="form.indexationIcc" class="indexation-chbox"></el-checkbox>
                                    </el-col> -->
                                    <el-col :span="17">
                                        <periodique :anneeDeReference="anneeDeReference"
                                                    v-model="form.periodiques"
                                                    @onChange="periodicOnChange"></periodique>
                                    </el-col>
                                </el-row>
                            </el-collapse-item>
                            <el-collapse-item title="Financements en %" name="2">
                                <div class="row mt-4" >
                                    <div class="col-sm-7">
                                        <div class="row">
                                            <div class="col-sm-6">
                                                <el-form-item label="Quotités de fonds Propres" label-width="240px" prop="foundsPropres">
                                                    <el-input type="text" v-model="form.foundsPropres" placeholder="0" class="fixed-input"
                                                              @change="formatInput('foundsPropres')"></el-input>
                                                </el-form-item>
                                                <el-form-item label="Total quotité subventions" prop="totalSubventions">
                                                    {{totalSubventions}}
                                                </el-form-item>
                                                <el-form-item label="Total quotité emprunts" prop="totalQuotiteEmprunt">
                                                    {{totalQuotiteEmprunt}}
                                                </el-form-item>
                                                <el-form-item label="Reste à financer" prop="resteFinancer">
                                                    {{resteFinancer}}K€
                                                </el-form-item>
                                            </div>
                                            <div class="col-sm-6">
                                                <p><strong>Quotité Subventions en %</strong></p>
                                                <el-form-item label="Subventions d'Etat" label-width="240px" prop="subventionsEtat">
                                                    <el-input type="text" v-model="form.subventionsEtat" placeholder="0" class="fixed-input"
                                                              @change="formatInput('subventionsEtat')"></el-input>
                                                </el-form-item>
                                                <el-form-item label="Subventions ANRU" label-width="240px" prop="subventionsAnru">
                                                    <el-input type="text" v-model="form.subventionsAnru" placeholder="0" class="fixed-input"
                                                              @change="formatInput('subventionsAnru')"></el-input>
                                                </el-form-item>
												<el-form-item label="Subventions Régions" label-width="240px" prop="subventionsRegion">
													<el-input type="text" v-model="form.subventionsRegion" placeholder="0" class="fixed-input"
															  @change="formatInput('subventionsRegion')"></el-input>
												</el-form-item>
                                                <el-form-item label="Subventions départements" label-width="240px" prop="subventionsDepartement">
                                                    <el-input type="text" v-model="form.subventionsDepartement" placeholder="0" class="fixed-input"
                                                              @change="formatInput('subventionsDepartement')"></el-input>
                                                </el-form-item>
												<el-form-item label="Subventions EPCI/Communes" label-width="240px" prop="subventionsEpci">
													<el-input type="text" v-model="form.subventionsEpci" placeholder="0" class="fixed-input"
															  @change="formatInput('subventionsEpci')"></el-input>
												</el-form-item>
                                                <el-form-item label="Subventions collecteurs" label-width="240px" prop="subventionsCollecteur">
                                                    <el-input type="text" v-model="form.subventionsCollecteur" placeholder="0" class="fixed-input"
                                                              @change="formatInput('subventionsCollecteur')"></el-input>
                                                </el-form-item>
                                                <el-form-item label="Autres subventions" label-width="240px" prop="autresSubventions">
                                                    <el-input type="text" v-model="form.autresSubventions" placeholder="0" class="fixed-input"
                                                              @change="formatInput('autresSubventions')"></el-input>
                                                </el-form-item>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-5">
                                        <p><strong>Emprunts en %</strong></p>
                                        <el-form-item label="Ajouter un emprunt" prop="typeEmprunt">
                                            <el-select v-model="form.typeEmprunt">
                                                <el-option v-for="item in availableTypesEmprunts"
                                                    :key="item.id"
                                                    :label="item.nom"
                                                    :value="item.id"></el-option>
                                            </el-select>
                                        </el-form-item>
                                        <el-form-item v-if="form.typeEmprunt" label="Quotité emprunt" prop="quotiteEmprunt">
                                            <el-input type="text" v-model="form.quotiteEmprunt" placeholder="0" class="fixed-input"
                                                      @change="formatInput('quotiteEmprunt')"></el-input>
                                            <el-button type="primary" @click="addTypeEmprunt">Ajouter</el-button>
                                        </el-form-item>
                                        <p class="mt-5"><strong>Liste des emprunts</strong></p>
                                        <el-table
                                            :data="form.typeEmpruntDemolition"
                                            style="width: 100%">
                                            <el-table-column sortable column-key="typesEmprunts" prop="typesEmprunts" min-width="60" label="Numéro emprunt">
                                                <template slot-scope="scope">
                                                    {{scope.row.typesEmprunts.nom}}
                                                </template>
                                            </el-table-column>
                                            <el-table-column sortable column-key="quotiteEmprunt" prop="quotiteEmprunt" min-width="60" label="Quotité"></el-table-column>
                                            <el-table-column fixed="right" width="90" label="supprimer">
                                                <template slot-scope="scope">
                                                    <el-button type="danger" icon="el-icon-delete" circle @click="handleDeleteEmprunt(scope.$index, scope.row)"></el-button>
                                                </template>
                                            </el-table-column>
                                        </el-table>
                                        <div class="text-center">
                                            <p>Total quotité emprunts: {{totalQuotiteEmprunt}}</p>
                                        </div>
                                    </div>
                                </div>
                            </el-collapse-item>
                        </el-collapse>
                    </el-tab-pane>
                </el-tabs>
            </el-form>
            <span slot="footer" class="dialog-footer">
                <el-button type="outline-primary" @click="dialogVisible = false">Retour</el-button>
                <el-button type="primary" @click="save('demolitionForm')" :disabled="isSubmitting">Valider</el-button>
            </span>
        </el-dialog>
    </div>
</template>

<script>
import moment from "moment";
import {
  initPeriodic,
  periodicFormatter,
  mathInput,
  checkAllPeriodics
} from "../../../../../../../utils/inputs";
import { updateData, getFloat } from "../../../../../../../utils/helpers";
import customValidator from "../../../../../../../utils/validation-rules";
import Periodique from "../../../../../../../components/partials/Periodique";

export default {
  name: "DemolitionNonidentifiees",
  components: { Periodique },
  props: [
    "simulationID",
    "anneeDeReference",
    "typesEmprunts",
    "data",
    "error",
    "isLoading",
    "query"
  ],
  data() {
    var validateNgroupe = (rule, value, callback) => {
      if (!value) {
        callback(new Error("Veuillez sélectionner un N° de groupe"));
      } else if (this.checkExistNgroupe(value)) {
        callback(new Error("La N° de groupe existe déjà"));
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
      demolitions: [],
      dialogVisible: false,
      collapseList: ['1'],
      form: null,
      isEdit: false,
      selectedIndex: null,
      activeTab: "1",
      availableTypesEmprunts: [],
      isSubmitting: false,
      columns: [],
      formRules: {
        nGroupe: [
          { required: true, validator: validateNgroupe, trigger: "change" }
        ],
        nomCategorie: customValidator.getRule("required"),
        resteFinancer: [{ validator: resteFinancerValidate, trigger: "change" }]
      }
    };
  },
  created() {
    this.initForm();
  },
  methods: {
    initForm() {
      this.form = {
        id: null,
        nGroupe: null,
        conventionAnru: true,
        indexationIcc: true,
        foundsPropres: null,
        subventionsEtat: null,
        subventionsAnru: null,
        subventionsEpci: null,
        subventionsDepartement: null,
        subventionsRegion: null,
        subventionsCollecteur: null,
        autresSubventions: null,
        tfpb: null,
        maintenanceCourante: null,
        grosEntretien: null,
        typeEmpruntDemolition: [],
        periodiques: {
          nombreLogements: initPeriodic(),
          coutMoyen: initPeriodic(),
          remboursement: initPeriodic(),
          coutAnnexes: initPeriodic()
        },
        quotiteEmprunt: null
      };
    },
    tableData(data) {
      if (!_.isNil(data)) {
        let demolitions = data.demolitions.items.map(item => {
          if (item.type === 1) {
            let nombreLogements = [];
            let coutMoyen = [];
            let remboursement = [];
            let coutAnnexes = [];
            item.demolitionPeriodique.items.forEach(periodique => {
              nombreLogements[periodique.iteration - 1] =
                periodique.nombreLogements;
              coutMoyen[periodique.iteration - 1] = periodique.coutMoyen;
              remboursement[periodique.iteration - 1] =
                periodique.remboursement;
              coutAnnexes[periodique.iteration - 1] = periodique.coutAnnexes;
            });

            let row = { ...item };
            row.typeEmpruntDemolition = item.typeEmpruntDemolition.items;
            row.periodiques = {
              nombreLogements,
              coutMoyen,
              remboursement,
              coutAnnexes
            };
            return row;
          } else {
            return false;
          }
        });
        demolitions = demolitions.filter(item => item);
        this.demolitions = demolitions;
        return demolitions;
      } else {
        return [];
      }
    },
    save(formName) {
      this.$refs[formName].validate(valid => {
        if (valid && checkAllPeriodics(this.form.periodiques)) {
          this.isSubmitting = true;
          const typeEmpruntDemolition = this.form.typeEmpruntDemolition.map(
            item => {
              return JSON.stringify({
                id: item.typesEmprunts.id,
                quotiteEmprunt: item.quotiteEmprunt
              });
            }
          );
          this.$apollo
            .mutate({
              mutation: require("../../../../../../../graphql/simulations/logements-familiaux/demolitions/saveDemolition.gql"),
              variables: {
                demolition: {
                  simulationId: this.simulationID,
                  uuid: this.form.id,
                  nGroupe: this.form.nGroupe,
                  conventionAnru: this.form.conventionAnru,
                  indexationIcc: this.form.indexationIcc,
                  foundsPropres: this.form.foundsPropres,
                  subventionsEtat: this.form.subventionsEtat,
                  subventionsAnru: this.form.subventionsAnru,
                  subventionsEpci: this.form.subventionsEpci,
                  subventionsDepartement: this.form.subventionsDepartement,
                  subventionsRegion: this.form.subventionsRegion,
                  subventionsCollecteur: this.form.subventionsCollecteur,
                  autresSubventions: this.form.autresSubventions,
                  tfpb: this.form.tfpb,
                  maintenanceCourante: this.form.maintenanceCourante,
                  grosEntretien: this.form.grosEntretien,
                  nomCategorie: this.form.nomCategorie,
                  surfaceMoyenne: this.form.surfaceMoyenne,
                  loyerMensuel: this.form.loyerMensuel,
                  logementsConventionees: this.form.logementsConventionees,
                  nombreAnneesAmortissements: this.form
                    .nombreAnneesAmortissements,
                  type: 1,
                  typeEmprunts: typeEmpruntDemolition,
                  periodique: JSON.stringify({
                    nombre_logements: this.form.periodiques.nombreLogements,
                    cout_moyen: this.form.periodiques.coutMoyen,
                    remboursement: this.form.periodiques.remboursement,
                    cout_annexes: this.form.periodiques.coutAnnexes
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
                  message:
                    "La démolition non identifiée a bien été enregistrée.",
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
    showCreateModal() {
      this.initForm();
      this.dialogVisible = true;
      this.isEdit = false;
      this.selectedIndex = null;
      this.getTypesEmprunts();
    },
    handleEdit(index, row) {
      this.dialogVisible = true;
      this.form = { ...row };
      this.selectedIndex = index;
      this.isEdit = true;
      this.getTypesEmprunts();
    },
    handleDelete(index, row) {
      this.$confirm(
        "Êtes-vous sûr de vouloir supprimer cette démolition non identifiée?"
      )
        .then(_ => {
          this.$apollo
            .mutate({
              mutation: require("../../../../../../../graphql/simulations/logements-familiaux/demolitions/removeDemolition.gql"),
              variables: {
                demolitionUUID: row.id,
                simulationId: this.simulationID,
              }
            })
            .then(() => {
              updateData(this.query, this.simulationID).then(() => {
                this.$message({
                  showClose: true,
                  message:
                    "Cette démolition non identifiée a bien été supprimée.",
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
    addTypeEmprunt() {
      if (this.form.typeEmprunt) {
        const typeEmprunt = this.availableTypesEmprunts.find(
          item => (item.id == this.form.typeEmprunt)
        );
        this.form.typeEmpruntDemolition.push({
          quotiteEmprunt: this.form.quotiteEmprunt | 0,
          typesEmprunts: typeEmprunt,
          local: true
        });
        this.form.typeEmprunt = null;
        this.getTypesEmprunts();
      }
    },
    handleDeleteEmprunt(index, row) {
      this.$confirm("Êtes-vous sûr de vouloir supprimer ce type d’emprunt?")
        .then(_ => {
          if (row.local) {
            this.form.typeEmpruntDemolition.splice(index, 1);
          } else {
            this.$apollo
              .mutate({
                mutation: require("../../../../../../../graphql/simulations/logements-familiaux/demolitions/removeTypeDempruntDemolition.gql"),
                variables: {
                  typesEmpruntsUUID: row.typesEmprunts.id,
                  demolitionUUID: this.demolitions[this.selectedIndex].id
                }
              })
              .then(res => {
                this.form.typeEmpruntDemolition.splice(index, 1);
              });
          }
        })
        .catch(_ => {});
    },
    periodicOnChange(type) {
      let newPeriodics = this.form.periodiques[type];
      this.form.periodiques[type] = [];
      this.form.periodiques[type] = periodicFormatter(newPeriodics);
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
    formatInput(type) {
      this.form[type] = mathInput(this.form[type]);
    },
    getTypesEmprunts() {
      let emprunts = [];
      const linkedEmprunts = this.form.typeEmpruntDemolition;
      this.typesEmprunts.forEach(item => {
        if (
          !linkedEmprunts.some(emprunt => emprunt.typesEmprunts.id == item.id)
        ) {
          emprunts.push(item);
        }
      });
      this.availableTypesEmprunts = emprunts;
    },
    checkExistNgroupe(value) {
      let demolitions = this.demolitions;
      if (this.isEdit) {
        demolitions = demolitions.filter(
          item => item.nGroupe !== this.form.nGroupe
        );
      }
      return demolitions.some(item => item.nGroupe == value);
    },
    back() {
      if (this.selectedIndex > 0) {
        this.selectedIndex--;
        this.form = { ...this.demolitions[this.selectedIndex] };
      }
    },
    next() {
      if (this.selectedIndex < this.demolitions.length - 1) {
        this.selectedIndex++;
        this.form = { ...this.demolitions[this.selectedIndex] };
      }
    },
    setTableColumns() {
      this.columns = [];
      for (var i = 0; i < 50; i++) {
        this.columns.push({
          label:
            (parseInt(this.anneeDeReference) + i).toString() +
            " Nombre de logements démolis",
          prop: `periodiques.nombreLogements[${i}]`
        });
        this.columns.push({
          label:
            (parseInt(this.anneeDeReference) + i).toString() +
            " Coût moyen de la démolition en K€ / lgt",
          prop: `periodiques.coutMoyen[${i}]`
        });
        this.columns.push({
          label:
            (parseInt(this.anneeDeReference) + i).toString() +
            " Remboursement du CRD en K€ / lgt",
          prop: `periodiques.remboursement[${i}]`
        });
        this.columns.push({
          label:
            (parseInt(this.anneeDeReference) + i).toString() +
            " Coûts annexes de la démolition en K€ / lgt",
          prop: `periodiques.coutAnnexes[${i}]`
        });
      }
    },
    exportNonIdentifees() {
      window.location.href = "/export-nonIdentifees/" + this.simulationID;
    },
    onSuccess(res) {
      this.$toasted.success(res, {
        theme: "toasted-primary",
        icon: "check",
        position: "top-right",
        duration: 5000
      });
      this.$refs.upload.clearFiles();
      updateList(this.query, this.simulationID);
    },
    onError(error) {
      this.$toasted.error(JSON.parse(error.message), {
        theme: "toasted-primary",
        icon: "error",
        position: "top-right",
        duration: 5000
      });
      this.$refs.upload.clearFiles();
    }
  },
  computed: {
    dialogTitle() {
      return this.isEdit
        ? "Modifier une démolition non identifiée"
        : "Créer une démolition non identifiée";
    },
    totalQuotiteEmprunt() {
      let emprunt = 0;
      this.form.typeEmpruntDemolition.forEach(item => {
        emprunt += getFloat(item.quotiteEmprunt);
      });
      return emprunt;
    },
    totalSubventions() {
      return getFloat(this.form.subventionsEtat) + getFloat(this.form.subventionsAnru) + getFloat(this.form.subventionsEpci) + getFloat(this.form.subventionsDepartement) + getFloat(this.form.subventionsRegion) + getFloat(this.form.subventionsCollecteur) + getFloat(this.form.autresSubventions);
    },
    resteFinancer() {
      return getFloat(this.form.foundsPropres) - (this.totalSubventions + this.totalQuotiteEmprunt);
    },
    isModify() {
      return this.$store.getters['choixEntite/isModify'];
    }
  },
  watch: {
    anneeDeReference(newVal) {
      if (newVal) {
        this.setTableColumns();
      }
    }
  }
};
</script>

<style>
.demolition-nonidentifiees .el-dialog__body {
  padding-top: 0;
}
.demolition-nonidentifiees .el-button--small {
  font-weight: 500;
  padding: 0 15px;
  height: 40px;
  font-size: 14px;
  border-radius: 4px;
  text-align: center;
}
</style>
