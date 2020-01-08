<template>
    <div>
        <el-dialog title="Fusionner" :visible.sync="show" width="45%" :close-on-click-modal="false" :before-close="close">
            <el-form ref="fusionForm" :model="form" :rules="rules">
                <el-form-item prop="nom" label="Entrez le nom de la simulation">
                    <el-input type="text" v-model="form.nom"/>
                </el-form-item>
            </el-form>
            <span slot="footer" class="dialog-footer">
                <el-button type="primary" @click="close">Annuler</el-button>
                <el-button type="success" @click="fusionSimulation">Valider</el-button>
            </span>
        </el-dialog>
        <progress-loading :visible="visibleProgress" :finish="finishDuplication"/>
    </div>
</template>
<script>
  import customValidator from "../../../../utils/validation-rules";
  import ProgressLoading from "../../partials/ProgressLoading";
    export default {
      name:"SimulationFusion",
      props: [
        'show',
        'close',
        'selectedSimulations',
        'getSimulations'
      ],
      components: {
        ProgressLoading
      },
      data() {
        return {
          visibleProgress: false,
          finishDuplication: false,
          rules: {
            nom: customValidator.getRule('required')
          },
          form: {}
        }
      },
      methods: {
        fusionSimulation() {
          this.$refs["fusionForm"].validate(isValid => {
            if (isValid) {
              const simulation1 = this.selectedSimulations[0];
              const simulation2 = this.selectedSimulations[1];
              this.visibleProgress = true;
              this.finishDuplication = false;
              this.$apollo
                .mutate({
                  mutation: require("../../../../graphql/simulations/fusionSimulations.gql"),
                  variables: {
                    nom: this.form.nom,
                    simulationId1: simulation1.simulation.id,
                    simulationId2: simulation2.simulation.id
                  }
                })
                .then(() => {
                  this.fusionDialogVisible = false;
                  this.finishDuplication = true;
                  this.$message({
                    message: 'la fusion a été effectuée avec succès.',
                    type: 'success'
                  })
                  setTimeout(() => {
                    this.visibleProgress = false;
                  }, 2000);
                  this.getSimulations();
                })
                .catch((err) => {
                  this.visibleProgress = false;
                  this.$message({
                    showClose: true,
                    message: error.networkError.result,
                    type: 'error',
                  });
                })
                .finally(() => {
                  this.close();
                })
            }
          });
        }
      }
    }
</script>