<template>
  <a @click="download" class="pa-2 d-inline-flex align-center text--primary">
    <v-icon class="mr-2">mdi-file</v-icon>
    <div class="filename">
      <div>{{ file.filename }}</div>
      <v-expand-transition>
        <div v-if="loading">
          <div class="pt-1">
            <v-progress-linear :value="downloadProgress" :indeterminate="downloadIndeterminating" />
          </div>
        </div>
      </v-expand-transition>
    </div>

    <v-dialog v-model="keyDialogOpen" max-width="450">
      <v-card>
        <v-card-title>
          Schlüssel erforderlich
        </v-card-title>
        <v-card-text>
          <v-text-field ref="keyTextField" autofocus label="Schlüssel" v-model="key" :error-messages="keyInvalid ? ['Der Schlüssel ist ungültig'] : []" />

          <div class="mt-5 d-flex">
            <v-spacer />
            <v-btn text @click="keyDialogOpen = false" :disabled="loading">Abbrechen</v-btn>
            <v-btn depressed color="primary" class="ml-2" @click="download" :disabled="loading">Entschlüsseln</v-btn>
          </div>
        </v-card-text>
      </v-card>
    </v-dialog>
  </a>
</template>

<script>
  import {axios} from '../lib/axios'
  import throttle from 'lodash.throttle'

  export default {
    name: 'file-download-field',
    props: {
      file: Object,
      accessToken: String,
      downloadKey: {
        type: String,
        required: false,
      }
    },
    data: vm => ({
      loading: false,
      downloadProgress: 0,
      downloadIndeterminating: false,
      keyDialogOpen: false,
      key: vm.downloadKey,
      keyInvalid: false,
    }),
    methods: {
      async download() {
        if (this.loading) {
          return
        }

        if (!this.key) {
          if (this.downloadKey) {
            this.key = this.downloadKey
          } else {
            this.keyDialogOpen = true
            return
          }
        }

        this.downloadProgress = 0
        this.loading = true

        const closeTimeout = setTimeout(() => {
          this.keyDialogOpen = false
        }, 1000)

        let indeterminateTimeout = setTimeout(() => {
          this.downloadIndeterminating = true
        }, 1000)

        try {
          const {data} = await axios.get(`/transfer/${this.file.transfer_id}/${this.accessToken}/${this.file.id}/download`, {
            responseType: 'blob',
            params: {
              key: this.key,
            },
            onDownloadProgress: throttle(event => {
              this.downloadProgress = event.loaded * 100 / Number(event.target.getResponseHeader('x-collect-file-size'))
              if (this.downloadProgress >= 1 && indeterminateTimeout !== null) {
                clearInterval(indeterminateTimeout)
                indeterminateTimeout = null
                this.downloadIndeterminating = false
              }
            }, 350),
          })

          // Somewhat hilarious but hmm
          const url = window.URL.createObjectURL(new Blob([data]))
          const link = document.createElement('a')
          link.href = url
          link.setAttribute('download', this.file.filename)
          document.body.appendChild(link)
          link.click()
          document.body.removeChild(link)

          clearInterval(closeTimeout)
          this.keyDialogOpen = false
          this.$emit('update:downloadKey', this.key)
        } catch (e) {
          this.keyInvalid = true

          clearTimeout(closeTimeout)
          this.keyDialogOpen = true

          setTimeout(() => {
            this.$refs.keyTextField.focus()
          }, 100)

          console.error(e)
        }

        setTimeout(() => this.loading = false, 500)
      },
    }
  }
</script>

<style scoped>

</style>
