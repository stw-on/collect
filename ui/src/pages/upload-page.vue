<template>
  <v-container class="fill-height upload-page">
    <v-row>
      <v-col>
        <template v-if="!!template && !isCompleted">
          <h1 class="text-center">{{ template.name }}</h1>
          <p v-if="!!template.description" class="text-center">{{ template.description }}</p>

          <file-field
            v-for="field in template.fields"
            :key="field.id"
            :template-field="field"
            :disabled="uploading"
            v-model="fieldsFiles[field.id]"
          />

          <v-expand-transition group>
            <div v-if="hasErrors && !uploading">
              <div class="pt-3">
                <v-alert icon="mdi-alert-decagram" class="my-0" dark color="error">
                  Leider gibt es Probleme mit einigen Dateien. Bitte überprüfe diese und wähle ggf.
                  andere Dateien aus.
                </v-alert>
              </div>
            </div>
          </v-expand-transition>

          <div class="text-center">
            <v-btn :color="shouldCompleteTransfer ? 'success' : 'primary'" large class="mt-3" :loading="uploading" @click="upload" :disabled="(hasErrors && !filesChangedSinceLastUpload) || !hasAnyFiles">
              <template v-slot:loader>
                <loading-indicator light />
              </template>

              <template v-if="shouldCompleteTransfer">
                Absenden
                <v-icon right>mdi-chevron-right</v-icon>
              </template>
              <template v-else>Hochladen</template>
            </v-btn>
          </div>

          <privacy-dialog
            v-model="privacyDialogOpen"
            :template="template"
            @reject="privacyDialogOpen = false"
            @accept="onPrivacyAccepted"
          />
        </template>
        <template v-else-if="isCompleted">
          <div class="d-flex align-center flex-column">
            <v-icon size="96" color="success">mdi-cloud-check-outline</v-icon>
            <p>
              Deine Dateien wurden erfolgreich versandt.
            </p>
            <p class="text-caption grey--text">
              Transfer-ID:<br>
              {{ transferId }}
            </p>
          </div>
        </template>
      </v-col>
    </v-row>
  </v-container>
</template>

<script>
  import {axios} from '../lib/axios'
  import FileField from '../components/file-field'
  import {STATUS_DRAFT, STATUS_ERROR, STATUS_UPLOADED, STATUS_UPLOADING} from '../lib/UploadingFile'
  import LoadingIndicator from '../components/loading-indicator'
  import PrivacyDialog from '../components/privacy-dialog'
  import throttle from 'lodash.throttle'

  export default {
    name: 'upload-page',
    components: {PrivacyDialog, LoadingIndicator, FileField},
    data: () => ({
      template: null,
      transferId: null,
      uploading: false,
      fieldsFiles: {},
      key: null,
      isComplete: false,
      filesChangedSinceLastUpload: false,
      isCompleted: false,
      privacyDialogOpen: false,
      privacyAccepted: false,
    }),
    computed: {
      hasAnyFiles() {
        return Object.values(this.fieldsFiles).some(files => files.length > 0)
      },
      hasErrors() {
        return Object.values(this.fieldsFiles).some(files => files.some(file => file.status === STATUS_ERROR))
      },
      hasDraftFiles() {
        return Object.values(this.fieldsFiles).some(files => files.some(file => file.status === STATUS_DRAFT))
      },
      shouldCompleteTransfer() {
        return this.hasAnyFiles &&
          !this.filesChangedSinceLastUpload &&
          this.isComplete &&
          !this.hasErrors &&
          !this.hasDraftFiles
      }
    },
    watch: {
      fieldsFiles: {
        deep: true,
        handler() {
          this.filesChangedSinceLastUpload = true
        },
      },
    },
    async mounted() {
      const {data} = await axios.get(`/template/${this.$route.params.templateId}`)

      for (const field of data.fields) {
        this.$set(this.fieldsFiles, field.id, [])
      }

      this.template = data
    },
    methods: {
      onFilesSelected(fieldId, files) {
        this.$set(this.fieldsFiles, fieldId, files)
      },
      onPrivacyAccepted() {
        this.privacyAccepted = true
        this.privacyDialogOpen = false
        this.upload()
      },
      async upload() {
        if (this.uploading) return

        if (process.env.NODE_ENV !== 'development' && location.protocol !== 'https:') {
          alert('An https connection is required!')
          return
        }

        if (!this.privacyAccepted) {
          this.privacyDialogOpen = true
          return
        }

        this.uploading = true

        if (this.transferId === null) {
          const {data} = await axios.post(`/template/${this.$route.params.templateId}/transfer`)
          this.transferId = data.transfer.id
          this.key = data.key
          console.log(`Initiated transfer ${this.transferId}`)
        }

        for (const [fieldId, uploadingFiles] of Object.entries(this.fieldsFiles)) {
          const templateField = this.template.fields.find(f => f.id === fieldId)

          for (const uploadingFile of uploadingFiles) {
            if (uploadingFile.status !== STATUS_UPLOADED) {
              try {
                uploadingFile.status = STATUS_UPLOADING
                const formData = new FormData()
                formData.append('file', uploadingFile.file)
                formData.append('key', this.key)
                const {data} = await axios.post(`/transfer/${this.transferId}/${fieldId}/upload`, formData, {
                  headers: {'Content-Type': 'multipart/form-data'},
                  onUploadProgress: throttle(event => {
                    uploadingFile.progress = (event.loaded * 100) / event.total
                  }, 350),
                })
                uploadingFile.id = data.id
                uploadingFile.status = STATUS_UPLOADED
              } catch (e) {
                uploadingFile.progress = 0
                uploadingFile.status = STATUS_ERROR

                if ('x-collect-error' in e.response.headers) {
                  switch (e.response.headers['x-collect-error']) {
                    case 'invalid_mime':
                      uploadingFile.error = 'Ungültiges Dateiformat.'
                      break
                    case 'file_too_big':
                      uploadingFile.error = `Die Datei ist zu groß. Erlaubt sind maximal ${Math.round(templateField.max_size_kb / 10.24) / 100} MB.`
                      break
                    case 'virus':
                      uploadingFile.error = 'Die Datei enthält Schadsoftware. Dateien, die Schadsoftware enthalten, werden nicht akzeptiert.'
                      break
                  }
                } else {
                  uploadingFile.error = e.response.status
                }

                console.error(e)
              }
            }
          }
        }

        const {data: transfer} = await axios.get(`/transfer/${this.transferId}`)
        if (transfer.is_complete) {
          this.isComplete = true
        }

        if (this.shouldCompleteTransfer) {
          try {
            await axios.post(`/transfer/${this.transferId}/complete`, {
              key: this.key,
            })
            this.isCompleted = true
          } catch (e) {
            alert(`Übertragung fehlgeschlagen (${e.response.status})`)
          }
        }

        this.uploading = false
        this.filesChangedSinceLastUpload = false
      },
    },
  }
</script>

<style lang="scss" scoped>
  .upload-page {
    max-width: 1200px;
  }

  .invisible {
    visibility: hidden;
  }
</style>
