<template>
  <v-container align-center fluid>
    <v-fade-transition slot="append">
      <v-layout row wrap v-if="show">
        <v-flex xs2>
          <v-menu
            ref="menu"
            :close-on-content-click="false"
            v-model="menu"
            :nudge-right="40"
            :return-value.sync="dateAwal"
            lazy
            transition="scale-transition"
            offset-y
            full-width
          >
            <v-text-field
              label="Tanggal Awal"
              readonly
              slot="activator"
              v-model="dateAwal"
              prepend-icon="event"
            ></v-text-field>
            <v-date-picker
              format="dd/mm/yyyy"
              no-title
              v-model="dateAwal"
              @input="$refs.menu.save(dateAwal)"
            ></v-date-picker>
          </v-menu>
        </v-flex>
        <v-flex xs2 offset-xs1>
          <v-menu
            ref="menu1"
            :close-on-content-click="false"
            v-model="menu1"
            :nudge-right="40"
            :return-value.sync="dateAkhir"
            lazy
            transition="scale-transition"
            offset-y
            full-width
          >
            <v-text-field
              label="Tanggal Akhir"
              readonly
              slot="activator"
              v-model="dateAkhir"
              prepend-icon="event"
            ></v-text-field>
            <v-date-picker
              format="dd/mm/yyyy"
              no-title
              v-model="dateAkhir"
              @input="$refs.menu1.save(dateAkhir)"
            ></v-date-picker>
          </v-menu>
        </v-flex>
        <v-flex>
          <a target="_blank" :href="`api/reguler/cetak/${dateAwal}/${dateAkhir}`">
            <v-btn color="secondary">Print</v-btn>
          </a>
          <!-- <v-btn color="secondary" @click="cetak">Print</v-btn> -->
        </v-flex>
      </v-layout>
    </v-fade-transition>
  </v-container>
</template>

<script>
import axios from "axios";

export default {
  data() {
    return {
      show: false,
      menu: false,
      menu1: false,
      dateAwal: window.tanggal,
      dateAkhir: window.tanggal,
      breadcrumbs: [
        {
          text: "Home",
          disabled: true,
          href: ""
        },
        {
          text: "Admin",
          disabled: true,
          href: ""
        },
        {
          text: "Monitoring",
          disabled: true,
          href: ""
        }
      ]
    };
  },
  created() {
    setTimeout(() => {
      this.show = true;
    }, 100);
    this.$store.commit("CHANGE_BREAD", { list: this.breadcrumbs });
    this.$store.commit("SET_JUDUL", "Cetak Laporan");
  },
  methods: {
    cetak() {
      axios.get("api/reguler/cetak/" + this.dateAwal + "/" + this.dateAkhir);
    }
  }
};
</script>