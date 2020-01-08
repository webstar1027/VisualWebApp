<template>
    <el-dialog
            title="Notes"
            :before-close="close"
            @close="resetNoteForm"
            class="simulation-note-dialog"
            v-if="currentSimulation"
            :visible.sync="show"
            :close-on-click-modal="false"
            width="50%"
            v-loading="isLoading"
    >
        <el-form ref="noteForm" :model="form" :rules="rules">
            <el-row :gutter="24" class="mb-2">
                <el-col :span="24">
                    <p
                            class="font-weight-bold"
                            style="word-break: initial;"
                    >{{ currentSimulation.description }}</p>
                </el-col>
            </el-row>
            <div class="simulation-notes-container list-group">
                <div
                        v-for="(elem,i) in notesList"
                        :key="i"
                        class="list-group-item list-group-item-action flex-column align-items-start"
                >
                    <a class="d-flex w-100 justify-content-between">
                        <h6 class="mb-1">
                            {{ elem.creePar.prenom }} {{ elem.creePar.nom }}
                            <span class="mx-2">&bull;</span>
                            <small>{{ elem.dateCreation | dateFR }}</small>
                        </h6>
                        <small
                                v-if="elem.dateModification"
                                class="text-muted"
                        >Edité le {{ elem.dateModification | dateFR }}</small>
                    </a>
                    <p class="simulation-note my-2">{{ elem.note }}</p>
                    <small
                            v-if="canEditNote(elem.creePar.id)"
                            class="note-action text-muted mr-3"
                            @click="handleNoteEdit(elem)"
                    >Modifier</small>
                    <small
                            v-if="canDeleteNote(elem.creePar.id)"
                            class="note-action text-muted"
                            @click="deleteSimulationNote(elem.id)"
                    >Supprimer</small>
                </div>
            </div>

            <el-row :gutter="20" class="mt-5">
                <el-col :span="20" class="mb-2">
            <span v-if="isEdit" class="font-weight-bold">
              Modification de ma note:
              <small
                      @click="resetNoteForm"
                      class="note-action text-muted ml-2"
              >Annuler</small>
            </span>
                    <span v-else class="font-weight-bold">Ajouter une note:</span>
                </el-col>
            </el-row>
            <el-row :gutter="24">
                <el-col :span="24">
                    <el-form-item prop="note">
                        <el-input
                                ref="noteFormInput"
                                type="textarea"
                                maxlength="5000"
                                :rows="4"
                                v-model="form.note"
                                :autosize="{ minRows: 2, maxRows: 4}"
                        ></el-input>
                    </el-form-item>
                </el-col>
            </el-row>
            <br />
            <el-row :gutter="24">
                <el-col :span="12" class="text-center">
                    <el-button type="info" style="width: 200px" @click="close">Annuler</el-button>
                </el-col>
                <el-col :span="12" class="text-center">
                    <el-button
                            type="primary"
                            style="width: 200px"
                            :disabled="isSubmitting"
                            @click="saveNote"
                    >Enregistrer</el-button>
                </el-col>
            </el-row>
        </el-form>
    </el-dialog>
</template>
<script>
  import customValidator from "../../../../utils/validation-rules";
  import moment from "moment";
    export default {
      name:"SimulationsNotes",
      props: [
        'currentSimulation',
        'show',
        'close'
      ],
      data () {
        return {
            form: {},
            notesList: [],
            isEdit: false,
            isSubmitting: false,
            isLoading: false,
            rules: {
                note: customValidator.getRule("requiredNoWhitespaces", "change")
            },
        }
      },
      computed: {
        loggedUser() {
          return this.$store.getters["security/id"];
        },
      },
      methods: {
        fetchNotes() {
          if (this.currentSimulation) {
            this.isLoading = true
            this.$apollo
              .query({
                query: require("../../../../graphql/simulations/notes/simulationNote.gql"),
                variables: { simulationId: this.currentSimulation.id },
                fetchPolicy: "no-cache"
              })
              .then(res => {
                if (res && res.data && res.data.simulationNotes) {
                  this.notesList = res.data.simulationNotes.items;
                  this.resetNoteForm();
                  this.scrollAtNotesBottom();
                }
              }).finally(() => {
                this.isLoading = false
            });
          }
        },
        scrollAtNotesBottom() {
          this.$nextTick(() => {
            let container = document.querySelector(".simulation-notes-container");
            container.scrollTo(0, container.scrollHeight);
          });
        },
        saveNote() {
          this.$refs["noteForm"].validate(isValid => {
            if (isValid) {
              this.isLoading = true;
              this.isSubmitting = true;
              this.$apollo
                .mutate({
                  mutation: require("../../../../graphql/simulations/notes/saveSimulationNote.gql"),
                  variables: {
                    id: this.form.id,
                    simulationId: this.currentSimulation.id,
                    note: this.form.note
                  }
                })
                .then(() => {
                  this.fetchNotes();
                  this.isSubmitting = false;
                })
                .catch(() => {
                  this.isSubmitting = false;
                 this.isLoading = false
                })
            }
          });
        },
        canDeleteNote(ownerId) {
          return (
            this.loggedUser === ownerId ||
            this.loggedUser === this.currentSimulation.utilisateur.id ||
            this.$store.getters["security/estAdministrateurSimulation"]
          );
        },
        canEditNote(ownerId) {
          return this.loggedUser === ownerId;
        },
        deleteSimulationNote(id) {
          this.isLoading = true;
          this.$apollo
            .mutate({
              mutation: require("../../../../graphql/simulations/notes/removeSimulationNote.gql"),
              variables: {
                id: id,
                simulationId: this.currentSimulation.id
              }
            })
            .then(() => {
              this.fetchNotes();
            }).catch(() => {
                this.$message({
                  showClose: true,
                  message: 'Cette note n\'a pas pu être supprimée',
                  type: 'error',
                });
                this.fetchNotes();
          })
        },
        handleNoteEdit(note) {
          this.isEdit = true;
          this.form = Object.assign(this.form, note);
          this.$refs.noteFormInput.focus();
        },
        resetNoteForm() {
          this.form = { id: "", note: null };
          this.isEdit = false;
          if (this.$refs["noteForm"]) {
            this.$refs["noteForm"].resetFields();
          }
        }
      },
      filters: {
        dateFR(value) {
          return value ? moment(String(value)).format("DD/MM/YYYY") : "";
        }
      },
      watch: {
        currentSimulation() {
          this.notesList = []
          this.fetchNotes();
        }
      }
    }
</script>