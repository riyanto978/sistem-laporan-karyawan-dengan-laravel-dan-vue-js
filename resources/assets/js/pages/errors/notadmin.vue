<template>
  <main>
    <v-content>
      <v-container fluid>
        <v-layout column align-center>
          <div class="display-3 grey--text mt-5">{{ 'Tidak boleh masuk' }}</div>
          <div class="body-2 my-3">
            <router-link :to="{ name: 'welcome' }">
              {{ $t('go_home') }}
            </router-link>
          </div>
        </v-layout>
      </v-container>
    </v-content>
  </main>
</template>

<script>
export default {
  name: 'not-admin',
  layout: 'default'
}
</script>
