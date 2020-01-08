<template>
  <back-wrapper>
    <template v-slot:breadcrumb>
      <div>
        <el-breadcrumb separator-class="el-icon-arrow-right">
          <el-breadcrumb-item :to="{ path: '/administration' }">Administration</el-breadcrumb-item>
          <el-breadcrumb-item>Fusion d’entités</el-breadcrumb-item>
        </el-breadcrumb>
      </div>
    </template>
    <div class="container px-5">
      <div class="admin-content-wrap fusion-entites">
        <div class>
          <h1 class="admin-content-title">Fusion d’entités</h1>
          <el-form
            :model="entite"
            :inline="false"
            class="block-form"
            label-width="160px"
            ref="fusionEntitesForm"
            :rules="rules"
          >
            <div class="row">
              <div class="col-sm-12 col-md-4">
                <el-form-item label="SIREN" prop="siren" :inline="false">
                  <el-input
                    type="text"
                    v-model="entite.siren"
                    autocomplete="off"
                    v-mask="'### ### ###'"
                  ></el-input>
                </el-form-item>
              </div>

              <div class="col-sm-12 col-md-4">
                <el-form-item label="Nom de l'entité" prop="nom">
                  <el-input type="text" v-model="entite.nom" autocomplete="off"></el-input>
                </el-form-item>
              </div>

              <div class="col-sm-12 col-md-4">
                <el-form-item label="Code entité" prop="code">
                  <el-input type="text" v-model="entite.code" autocomplete="off"></el-input>
                </el-form-item>
              </div>

              <div class="col-sm-12 col-md-4">
                <el-form-item label="Type de l'entité" prop="type">
                  <el-select
                    v-model="entite.type"
                    placeholder="Type de l'entité"
                    @change="changeType"
                  >
                    <el-option
                      v-for="item in typeOptions"
                      :key="item.value"
                      :label="item.label"
                      :value="item.value"
                    ></el-option>
                  </el-select>
                </el-form-item>
              </div>

              <template v-if="entite.type === 'Organisme'">
                <div class="col-sm-12 col-md-4">
                  <el-form-item label="Type de l'organisme" prop="typeOrganisme">
                    <el-select v-model="entite.typeOrganisme" placeholder="Type de l'entité">
                      <el-option
                        v-for="item in typeOrganismeOptions"
                        :key="item.value"
                        :label="item.label"
                        :value="item.value"
                      ></el-option>
                    </el-select>
                  </el-form-item>
                </div>
                <div class="col-sm-12 col-md-4">
                  <el-form-item label="Code de l'organisme" prop="codeOrganisme">
                    <el-input type="text" v-model="entite.codeOrganisme" autocomplete="off"></el-input>
                  </el-form-item>
                </div>
              </template>

              <div class="col-sm-12 col-md-4">
                <el-form-item prop="entites" label="Entités à fusionner">
                  <el-select v-model="entite.entites" multiple filterable placeholder="Select">
                    <el-option
                      v-for="key in entitesList"
                      :key="key.id"
                      :label="key.nom"
                      :value="key.id"
                    ></el-option>
                  </el-select>
                </el-form-item>
              </div>
            </div>

            <div class="row justify-content-end">
              <div class="col-sm-12 col-md-4">
                <el-form-item>
                  <div class="d-flex justify-content-end">
                    <el-button
                      type="success"
                      class="btn btn-success"
                      @click="submitForm('fusionEntitesForm')"
                    >Valider</el-button>
                  </div>
                </el-form-item>
              </div>
            </div>
          </el-form>
        </div>
      </div>
    </div>
  </back-wrapper>
</template>

<script>
import customValidator from "../../utils/validation-rules";
import { mask } from "vue-the-mask";
export default {
  name: "FusionEntites",
  directives: { mask },
  data() {
    let checkEntites = (rule, value, callback) => {
      if (this.entite.entites.length < 2) {
        callback(new Error("Veuillez sélectionner au moins deux entités."));
      }
      callback();
    };
    return {
      entite: {
        nom: null,
        siren: null,
        code: null,
        type: null,
        codeOrganisme: null,
        typeOrganisme: null,
        entites: []
      },
      typeOptions: [
        { value: "Organisme" },
        { value: "Partenaire" },
        { value: "Holding" },
        { value: "Confédération" }
      ],
      typeOrganismeOptions: [
        { value: "ESH" },
        { value: "OPH" },
        { value: "Coopérative" },
        { value: "SC" },
        { value: "SVHLM" },
        { value: "SEM" },
        { value: "MOI" },
        { value: "SACICAP" },
        { value: "Autres" }
      ],
      entitesList: [],
      rules: {
        siren: customValidator.getRule("siren"),
        nom: [
          customValidator.getRule("requiredNoWhitespaces"),
          customValidator.getRule("maxVarchar")
        ],
        code: customValidator.getRule("requiredNoWhitespaces"),
        type: customValidator.getRule("required", "change"),
        typeOrganisme: [
          {
            message: "Merci de renseigner un type d'organisme",
            trigger: "change"
          }
        ],
        codeOrganisme: [
          {
            message: "Merci de renseigner un code d'organisme",
            trigger: "blur"
          }
        ],
        entites: { required: true, validator: checkEntites, trigger: "change" }
      }
    };
  },
  created() {
    this.loadEntites();
  },
  methods: {
    loadEntites() {
      this.$apollo
        .query({
          query: require("../../graphql/administration/entites/allEntites.gql")
        })
        .then(response => {
          this.entitesList = response.data.allEntites.filter(
            entite => entite.estActivee == true
          );
        });
    },
    submitForm(formName) {
      this.$confirm(
        "Voulez-vous vraiment fusionner ces deux entités?",
        "Warning",
        {
          confirmButtonText: "Oui",
          cancelButtonText: "Non",
          type: "warning"
        }
      )
        .then(() => {
          this.$refs[formName].validate(valid => {
            if (valid) {
              const loading = this.$loading({ lock: true });
              this.$apollo
                .mutate({
                  mutation: require("../../graphql/administration/entites/fusionEntites.gql"),
                  variables: {
                    nom: this.entite.nom,
                    siren: this.entite.siren,
                    type: this.entite.type,
                    code: this.entite.code,
                    typeOrganisme: this.entite.typeOrganisme,
                    codeOrganisme: this.entite.codeOrganisme,
                    entiteIds: this.entite.entites
                  }
                })
                .then(() => {
                  this.$router.push("/gestion/entites");
                  this.$router.go(0);
                })
                .catch(error => {
                  this.$message({
                    showClose: true,
                    message: error.networkError.result,
                    type: "error",
                    duration: 10000
                  });
                })
                .finally(() => {
                  loading.close();
                });
            } else {
              return false;
            }
          });
          this.$message({
            type: "success",
            message: "Fusion effectuée"
          });
        })
        .catch(() => {
          this.$message({
            type: "info",
            message: "Fusion annulée"
          });
        });
    },
    changeType(value) {
      this.rules.typeOrganisme = [
        {
          required: value === "Organisme",
          whitespace: true,
          message: "Merci de renseigner un type d'organisme",
          trigger: "change"
        }
      ];
      this.rules.codeOrganisme = [
        {
          required: value === "Organisme",
          whitespace: true,
          message: "Merci de renseigner un code d'organisme",
          trigger: "blur"
        }
      ];
    }
  }
};
</script>

<style type="text/css">
.fusion-entites .el-form-item__label {
  line-height: 40px;
}

/*
.fusion-entites .el-select {
  width: 320px; 
}
  */
</style>
