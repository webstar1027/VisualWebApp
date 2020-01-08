<template>
    <div>
        <el-dialog title="Duplication" :visible.sync="show" width="45%" :before-close="close">
            <el-form ref="duplicationForm" :model="form" :rules="rules">
                <el-form-item prop="nom" label="Entrez le nom de la simulation">
                    <el-input type="text" v-model="form.nom"/>
                </el-form-item>
            </el-form>
            <span slot="footer" class="dialog-footer">
        <el-button type="primary" @click="close">Annuler</el-button>
        <el-button type="success" @click="copySimulation">Valider</el-button>
      </span>
        </el-dialog>
        <progress-loading :visible="visibleProgress" :finish="finishDuplication"/>
    </div>
</template>
<script>
  import customValidator from '../../../../utils/validation-rules'
  import ProgressLoading from '../../partials/ProgressLoading'

  export default {
    name: 'SimulationsCopie',
    props: [
      'show',
      'close',
      'simulationId',
      'getSimulations'
    ],
    components: {
      ProgressLoading
    },
    data () {
      return {
        form: {},
        rules: {
          nom: customValidator.getRule('required')
        },
        visibleProgress: false,
        finishDuplication: false,
      }
    },
    methods: {
      copySimulation () {
        this.$refs['duplicationForm'].validate(isValid => {
          if (isValid) {
            this.visibleProgress = true
            this.finishDuplication = false
            this.$apollo
              .mutate({
                mutation: require('../../../../graphql/simulations/copySimulation.gql'),
                variables: {
                  id: this.simulationId,
                  nom: this.form.nom
                }
              })
              .then(() => {
                this.duplicationDialogVisible = false
                this.finishDuplication = true

                this.$message({
                  message: 'La duplication a été effectuée avec succès.',
                  type: 'success'
                })
                this.getSimulations()
                setTimeout(() => {
                  this.visibleProgress = false;
                }, 2000)
              })
              .catch(() => {
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
        })
      }
    }
  }
</script>