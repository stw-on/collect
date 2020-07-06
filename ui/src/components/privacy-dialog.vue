<template>
  <v-dialog
    :value="value"
    @input="v => $emit('input', v)"
    scrollable
    max-width="650"
  >
    <v-card>
      <v-card-title>
        Speicherung & Datenschutz
      </v-card-title>
      <v-card-text>
        Wir nehmen den Schutz Deiner Daten sehr ernst. Daher werden alle Dateien, die Du über dieses Formular
        hochlädst, verschlüsselt übertragen, gespeichert und spätestens {{ rententionTimeString }} gelöscht.<br>
        <br>
        Um fortzufaren, musst Du unsere
        <a href="https://stw-on.de/datenschutz" target="_blank">Datenschutzerklärung</a>
        lesen und akzeptieren.

        <div class="mt-5 d-flex">
          <v-spacer />
          <v-btn text @click="$emit('reject')">Ablehnen</v-btn>
          <v-btn depressed color="primary" class="ml-2" @click="$emit('accept')">Akzeptieren</v-btn>
        </div>
      </v-card-text>
    </v-card>
  </v-dialog>
</template>

<script>
  import {formatDistanceToNow, addMinutes} from 'date-fns'
  import {de} from 'date-fns/locale'

  export default {
    name: 'privacy-dialog',
    props: {
      value: Boolean,
      template: Object,
    },
    computed: {
      rententionTimeString() {
        return formatDistanceToNow(addMinutes(new Date(), this.template.retention_minutes), {
          locale: de,
          addSuffix: true,
        })
      }
    }
  }
</script>

<style scoped>

</style>
