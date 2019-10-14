<template>
  <v-app light>
    <v-navigation-drawer
          
      v-if="authenticated"
      persistent
      v-model="drawer"
      enable-resize-watcher
      app
      width="220"
      clipped
    >
      <nav-menu></nav-menu>
    </v-navigation-drawer>
    <tool-bar v-on:toggleDrawer="drawer = !drawer" :drawer="drawer"></tool-bar>
    <v-content>
      <v-toolbar class="elevation-0 transparent media-toolbar" height="50px">
        <v-layout row class="align-center">
          <div class="page-header-left" v-if="$route.name != 'login'">
            <h2 class="pr-3">{{ $store.state.info.judul }}</h2>
          </div>
          <v-spacer></v-spacer>
          <div class="page-header-right" v-if="$route.name != 'login'">
            <v-breadcrumbs :items="$store.state.info.list" class="justify-end">
              <v-icon slot="divider">chevron_right</v-icon>
            </v-breadcrumbs>
          </div>
        </v-layout>
      </v-toolbar>
      <v-divider></v-divider>
      
        <v-container fluid class="pa-0 ma-0">
          <transition name="page" mode="out-in">
            <router-view></router-view>
          </transition>
        </v-container>      
    </v-content>
    <feedback-message></feedback-message>
    <page-footer></page-footer>
  </v-app>
</template>

<script>
import { mapGetters } from "vuex";

import NavMenu from "~/components/NavMenu";
import ToolBar from "~/components/ToolBar";
import FeedbackMessage from "~/components/FeedbackMessage";
import PageFooter from "~/components/PageFooter";

export default {
  components: {
    "nav-menu": NavMenu,
    "tool-bar": ToolBar,
    "feedback-message": FeedbackMessage,
    "page-footer": PageFooter
  },

  computed: mapGetters({
    authenticated: "authCheck"
  }),

  data() {
    return {
      drawer: true
    };
  }
};
</script>

