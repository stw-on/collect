<template>
  <v-card rounded class="pa-3 file-field">
    <h3 class="name">{{ templateField.name }}</h3>
    <div>
      <div v-if="!!templateField.description">{{ templateField.description }}</div>
      <input ref="fileInput" class="file-input" type="file" @change="onChange" :multiple="templateField.max_count > 1">
      <v-list class="py-0">
        <template v-if="value.length > 0">
          <v-list-item
            v-for="(uploadingFile, index) in value"
            :key="index"
            class="file"
          >
            <v-list-item-avatar class="align-start">
              <v-icon>mdi-file</v-icon>
            </v-list-item-avatar>
            <v-list-item-content class="py-1">
              <v-list-item-title class="d-flex align-center mb-0 flex-wrap">
                <div class="mr-2 text-wrap">{{ uploadingFile.file.name }}</div>
                <v-fade-transition mode="out-in">
                  <div v-if="uploadingFile.status !== 'uploading'" class="status-container">
                    <v-btn
                      v-if="uploadingFile.status !== 'uploaded'"
                      @click="value.splice(index, 1)"
                      :icon="uploadingFile.status !== 'error'"
                      :outlined="uploadingFile.status === 'error'"
                      :x-small="uploadingFile.status === 'error'"
                      :small="uploadingFile.status !== 'error'"
                      :color="uploadingFile.status === 'error' ? 'error' : ''"
                    >
                      <v-icon
                        :size="uploadingFile.status === 'error' ? 14 : 24"
                        :left="uploadingFile.status === 'error'"
                      >mdi-close-circle-outline</v-icon>
                      <template v-if="uploadingFile.status === 'error'">
                        Entfernen
                      </template>
                    </v-btn>
                    <v-icon v-else color="success">
                      mdi-check-circle-outline
                    </v-icon>
                  </div>
                  <div v-else key="loader" class="pl-2 status-container">
                    <v-progress-circular size="24" rotate="-90" :indeterminate="uploadingFile.progress === 0" :value="uploadingFile.progress" />
                  </div>
                </v-fade-transition>
              </v-list-item-title>
              <v-expand-transition>
                <v-list-item-subtitle v-if="uploadingFile.error !== null" class="upload-error text-wrap">
                  <div class="error--text">
                    <v-icon left color="error">mdi-alert-decagram</v-icon>
                    {{ uploadingFile.error }}
                  </div>
                </v-list-item-subtitle>
              </v-expand-transition>
            </v-list-item-content>
          </v-list-item>
        </template>
      </v-list>
      <v-btn outlined color="primary" v-if="value.length < templateField.max_count" class="button mt-2" @click="$refs.fileInput.click()">
        <template v-if="value.length > 0">Weitere</template>
        Datei{{ templateField.max_count - value.length > 1 ? 'en' : '' }} auswählen
      </v-btn>
    </div>
  </v-card>
</template>

<script>
  import {UploadingFile} from '@/lib/UploadingFile'

  export default {
    name: 'file-field',
    props: {
      value: Array,
      templateField: Object,
      disabled: Boolean,
    },
    methods: {
      onChange(event) {
        const selectedFilesArray = this.value

        for (const file of event.target.files) {
          if (selectedFilesArray.length >= this.templateField.max_count) {
            break
          }

          selectedFilesArray.push(
            new UploadingFile(file)
          )
        }

        this.$emit('input', selectedFilesArray)
        this.$refs.fileInput.value = null
      },
    }
  }
</script>

<style lang="scss" scoped>
  .file-input {
    display: none;
  }

  .status-container {
    min-height: 32px;
    display: flex;
    align-items: center;
  }
</style>
