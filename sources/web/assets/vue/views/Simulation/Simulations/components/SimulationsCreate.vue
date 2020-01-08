<template>
    <div>
        <el-dialog
                title="Création d'une simulation"
                :visible.sync="show"
                :close-on-click-modal="false"
                width="50%"
                :before-close="close"
        >
            <el-form ref="form" :model="form" :rules="rules" label-position="top">
                <el-form-item prop="annee" label="Année de référence">
                    <el-select v-model="form.annee" clearable placeholder="Année de référence">
                        <el-option
                                v-for="item in listAnnees"
                                v-if="item.value != null"
                                :key="item.value"
                                :label="item.label"
                                :value="item.value"
                        />
                    </el-select>
                </el-form-item>
                <el-form-item v-if="isAdmin" prop="entite" label="Choix de l'entité">
                    <el-select v-model="form.entite" placeholder="Sélectionner une entité">
                        <el-option
                                v-for="item in listEntites"
                                v-if="item.value != null"
                                :key="item.value"
                                :label="item.label"
                                :value="item.value"
                        />
                    </el-select>
                </el-form-item>
                <el-form-item prop="nomSimulation" label="Nom">
                    <el-input placeholder="Nom" v-model="form.nomSimulation"/>
                </el-form-item>
                <el-form-item prop="description" label="Description">
                    <el-input
                            type="textarea"
                            placeholder="Description de la simulation"
                            :rows="2"
                            v-model="form.description"
                    />
                </el-form-item>
            </el-form>
            <div slot="footer" class="dialog-footer">
                <el-button
                        type="info"
                        class="flex-shrink-1"
                        style="width: 200px"
                        @click="close"
                >Annuler</el-button>
                <el-button
                        type="primary"
                        style="width: 200px"
                        class="flex-shrink-1"
                        :disabled="isSubmitting"
                        @click="saveSimulation()"
                >Créer une simulation</el-button>
            </div>
        </el-dialog>
        <progress-loading :visible="visibleProgress" :finish="finishDuplication"/>
    </div>
</template>
<script>
  import customValidator from "../../../../utils/validation-rules";
  import ProgressLoading from "../../partials/ProgressLoading";
  import store from "../../../../store";
  import {isNull} from "lodash";
    export default {
      name: "SimulationsCreate",
        store,
      components: {
        ProgressLoading
      },
      props: [
        'show',
        'close',
        'listAnnees',
        'listEntites',
        'isAdmin',
        'getSimulations',
        'getCurrentEntityId'
      ],
      data () {
        return {
          form: {},
          isSubmitting: false,
          visibleProgress: false,
          finishDuplication: false,
          rules: {
            annee: [{ required: true, message: 'L’année de référence doit être renseignée', trigger: 'change' }],
            entite: customValidator.getRule("required", "change"),
            nomSimulation: [
              { required: true, message: 'Veuillez saisir un nom de simulation', trigger: 'change' },
              customValidator.getRule("maxVarchar")
            ]
          },
        }
      },
      mounted() {
        this.initForm();
      },
      methods: {
        resetForm() {
          this.initForm();
          this.close();
        },
        initForm() {
          this.form = {
            annee: null,
            entite: null,
            nomSimulation: null,
            description: null
          };
        },
        saveSimulation() {
          this.visibleProgress = true;
          this.finishDuplication = false;
          this.$refs['form'].validate(valid => {
            if (valid) {
              this.isSubmitting = true;
              this.$apollo
                .mutate({
                  mutation: require("../../../../graphql/simulations/saveSimulation.gql"),
                  variables: {
                    id: null,
                    annee: this.form.annee,
                    entite: !isNull(this.form.entite) ? this.form.entite : this.getCurrentEntityId(),
                    nom: this.form.nomSimulation,
                    description: this.form.description,
                    estVerrouillee: false,
                    estPartagee: false,
                    estFusionnee: false
                  }
                })
                .then(() => {
                  this.getSimulations();
                  this.finishDuplication = true;
                  this.resetForm();
                  this.$message({
                    message: "La simulation a été créée avec succès.",
                    type: "success"
                  });
                  setTimeout(() => {
                    this.visibleProgress = false;
                  }, 2000);
                })
                .catch((error) => {
                  this.visibleProgress = false;
                  this.$message({
                    showClose: true,
                    message: error.networkError.result,
                    type: 'error',
                  });
                })
                .finally(() => {
                  this.isSubmitting = false;
                  setTimeout(() => {
                    this.visibleProgress = false;
                  }, 2000);
                });
            } else {
              this.$message({
                showClose: true,
                message: 'Les valeurs entrées doivent être valides.',
                type: 'error',
              });
            }
          });
        },
      }
    }
</script>