<template>
  <v-container align-center fluid>
        <v-fade-transition slot="append">
    <v-layout row wrap v-if="show">
      <!-- {{ allCounter }} -->
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
            @change="ambilResume"
            v-model="dateAwal"
            @input="$refs.menu.save(dateAwal)"
          ></v-date-picker>
        </v-menu>
      </v-flex>
      <v-flex xs1 offset-xs1>
        <v-select @change="ambilResume" :items="[1,2]" label="Shift" v-model="shift"></v-select>
      </v-flex>
      <br />
      <v-flex xs12 v-for="(data,index) in allLaporan" :key="index">
        <v-toolbar dense :color="warna(index)">POL : {{ data.pol }} | {{ data.nama_pol }}</v-toolbar>
        <v-data-table
          :headers="headersLaporanSaved"
          hide-actions
          :items="data.data"
          class="elevation-1"
        >
          <template slot="items" slot-scope="props">
            <td>{{ props.index+1 }}</td>
            <td>{{ props.item.username }}</td>
            <td>{{ props.item.nama_proses }}</td>
            <td>{{ props.item.iner }}</td>
            <td>{{ props.item.shift }}</td>
            <td>{{ props.item.line }}</td>
            <td>{{ props.item.isi }}</td>
            <td>{{ props.item.created_at }}</td>
            <td>{{ props.item.updated_at }}</td>
            <td>{{ parseInt(props.item.dead) + parseInt(props.item.chip_error) + parseInt(props.item.chip_lemah) + parseInt(props.item.card_body)}}</td>
            <td>{{ props.item.keterangan }}</td>
            <td>{{ status(props.item.status) }}</td>
          </template>
          <template slot="no-data">
            <div class="text-xs-center">Tidak Ada Data</div>
          </template>
        </v-data-table>
        <br />
      </v-flex>
      <v-flex xs12 v-for="(data,index) in allCounter" :key="index">
        <v-toolbar dense :color="warna(index)">Counter -- Pol : {{ data.pol }} | {{ data.nama_pol }}</v-toolbar>
        <v-data-table :headers="headersCounter" hide-actions :items="data.data" class="elevation-1">
          <template slot="items" slot-scope="props">
            <td>{{ props.index+1 }}</td>
            <td>{{ props.item.lot }}</td>
            <td>{{ props.item.jumlah }}</td>
            <td>{{ props.item.operator }}</td>
            <td>{{ props.item.updated_at }}</td>
            <td>{{ props.item.iner_awal }} - {{ props.item.iner_akhir }}</td>
            <td>{{ props.item.reject_plastic }}</td>
            <td>{{ props.item.wip }}</td>
          </template>
          <template slot="no-data">
            <div class="text-xs-center">Tidak Ada Data</div>
          </template>
        </v-data-table>
        <br />
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
      show : false,
      menu: false,
      menu1: false,
      shift: 1,
      headersLaporanSaved: [
        { text: "No", value: "no", sortable: false, width: "5%" },
        { text: "Operator", value: "operator", sortable: false, width: "15%" },
        { text: "Proses", value: "proses", sortable: false, width: "15%" },
        { text: "Inner", value: "iner", sortable: true, width: "5%" },
        { text: "Shift", value: "shift", sortable: true, width: "5%" },
        { text: "Line", value: "line", sortable: true, width: "5%" },
        { text: "Isi", value: "isi", sortable: true, width: "5%" },
        {
          text: "Mulai",
          value: "created_at",
          sortable: true,
          width: "15%"
        },
        {
          text: "Selesai",
          value: "updated_at",
          sortable: true,
          width: "15%"
        },
        { text: "Reject", value: "reject", sortable: true, width: "5%" },
        { text: "Keterangan", value: "keterangan", sortable: true },
        { text: "Status", value: "status", sortable: false, width: "10%" }
      ],
      headersCounter: [
        { text: "No", value: "No", sortable: false },
        { text: "Lot", value: "lot", sortable: false },
        { text: "Jumlah", value: "jumlah", sortable: false },
        { text: "Operator", value: "operator", sortable: false },
        { text: "Jam", value: "jam", sortable: false },
        { text: "Inner", value: "iner", sortable: false },
        { text: "Reject Plastic", value: "reject_plastic", sortable: false },
        { text: "WIP", value: "wip", sortable: false },
      ],
      allLaporan: [],
      allCounter: [],
      color: ["blue lighten-4", "green lighten-4", "orange lighten-4"],
      dateAwal: false,
      breadcrumbs: [
        {
          text: "Home",
          disabled: true,
          href: ""
        },
        {
          text: "Resume",
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
    this.$store.commit("SET_JUDUL", "Resume");
    this.dateAwal = window.tanggal;
    this.ambilResume();
  },
  methods: {
    warna(index) {
      if (index > 2) {
        var ind = index - 3;
      } else {
        var ind = index;
      }
      return this.color[ind];
    },
    ambilResume() {
      axios
        .get("api/laporan/" + this.shift + "/" + this.dateAwal + "/resume", {
          headers: {
            Authorization: "Bearer " + this.$store.state.auth.token
          }
        })
        .then(response => {
          this.allLaporan = response.data.reguler;
          this.allCounter = response.data.counter;
        })
        .catch(err => {});
    },
    status(status) {
      var stat = null;
      if (status == 1) {
        stat = "Running";
      } else if (status == 2) {
        stat = "Tersedia";
      } else {
        stat = "Next_Proses";
      }
      return stat;
    },
    getWaktu(date) {
      var d = new Date(new Date(date) * 1 + 1000 * 3600 * 7);
    }
  }
};
</script>
