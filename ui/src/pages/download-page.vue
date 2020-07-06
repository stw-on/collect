<template>
  <v-container class="fill-height">
    <v-row>
      <v-col>
        <div class="d-flex align-center flex-column">
          <template v-if="!!transfer">
            <v-icon size="96" color="info">mdi-cloud-lock-outline</v-icon>

            <p class="text-caption grey--text">
              Transfer-ID:<br>
              {{ transfer.id }}
            </p>

            <v-card
              v-for="field in transfer.template.fields"
              :key="field.id"
              class="pa-3 mb-2"
            >
              <h3>{{ field.name }}</h3>

              <template v-if="field.id in fileGroups">
                <div
                  v-for="file in fileGroups[field.id]"
                  :key="file.id"
                >
                  <file-download-field
                    :file="file"
                    :access-token="$route.params.accessToken"
                    :download-key.sync="key"
                  />
                </div>
              </template>
              <template v-else>
                Keine Dateien für dieses Feld hochgeladen
              </template>
            </v-card>
          </template>
          <template v-else>
            <v-progress-circular indeterminate />
          </template>
        </div>
      </v-col>
    </v-row>
  </v-container>
</template>

<script>
  import {axios} from '../lib/axios'
  import FileDownloadField from '../components/file-download-field'
  import groupBy from 'lodash.groupby'

  export default {
    name: 'download-page',
    components: {FileDownloadField},
    data: () => ({
      transfer: null,
      key: '',
    }),
    computed: {
      fileGroups() {
        return groupBy(this.transfer.files, 'transfer_template_field_id')
      }
    },
    async mounted() {
      const {transferId, accessToken} = this.$route.params
      const {data: transfer} = await axios.get(`/transfer/${transferId}/${accessToken}`)
      this.transfer = transfer
    },
  }
</script>

<style class="text-caption" scoped>

</style>
