<template>
  <v-container align-center grid-list-lg fluid>
    <v-fade-transition slot="append">
      <v-layout row wrap v-if="show">
        <v-flex xs12>
          <!-- <a target="_blank" href="https://persopaperless.cardtech.co.id/pdf/(POL)_1182_laminasi_-_kartu_brizzi_reguler_(tahap_1)_-_prelam_cardtum_150.000_pcs.pdf">sssss</a> -->
          <button @click="print"></button>
          <v-card>
            <v-card-title class="justify-center info" primary-title>
              <div class="white--text">Urgent</div>
            </v-card-title>
          </v-card>
          <v-data-table :headers="headers" :items="allpol" class="elevation-1" id="table1">
            <template slot="items" slot-scope="props">
              <tr>
                <td>{{ props.index + 1 }}</td>
                <td>{{ props.item.nama_pol }}</td>
                <td>{{ props.item.kode_pol }}</td>
                <td>{{ props.item.tahun }}</td>
                <td>{{ props.item.jumlah_order }}</td>
                <td>{{ props.item.per_iner }}</td>
                <td>{{ props.item.tipe }}</td>
                <td>
                  <a
                    :href="`http://qc3/qc/acuan/lampiran/${props.item.lampiran}`"
                    target="_blank"
                  >link</a>
                </td>
              </tr>
            </template>
            <template slot="no-data">
              <div class="text-xs-center">Tidak Ada Data</div>
            </template>
          </v-data-table>
        </v-flex>
        <v-divider></v-divider>
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
              slot="activator"
              v-model="dateAwal"
              prepend-icon="event"
              readonly
            ></v-text-field>
            <v-date-picker
              format="dd/mm/yyyy"
              no-title
              @change="ambilResumeHome"
              v-model="dateAwal"
              @input="$refs.menu.save(dateAwal)"
            ></v-date-picker>
          </v-menu>
        </v-flex>
        <br />
        <v-flex xs12 v-for="(data,index) in allLaporan" :key="index">
          <!-- {{data.data}} -->
          <v-toolbar dense :color="warna(index)">Pol : {{ data.pol }} | {{ data.nama_pol }}</v-toolbar>
          <v-data-table
            :headers="headersLaporanSaved"
            hide-actions
            :items="data.data"
            class="elevation-1"
          >
            <template slot="items" slot-scope="props">
              <td>{{ props.index+1 }}</td>
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
      headersLaporanSaved: [
        { text: "No", value: "no", sortable: false },
        { text: "Proses", value: "proses", sortable: false },
        { text: "Inner", value: "iner", sortable: true },
        { text: "Shift", value: "shift", sortable: true },
        { text: "Line", value: "line", sortable: true },
        { text: "Isi", value: "isi", sortable: true },
        { text: "Jam Mulai", value: "jam_mulai", sortable: true },
        { text: "Jam Selesai", value: "jam_selesai", sortable: true },
        { text: "Reject", value: "reject", sortable: true },
        { text: "Keterangan", value: "keterangan", sortable: true },
        { text: "Status", value: "status", sortable: false }
      ],
      headers: [
        { text: "No", value: "No", sortable: false },
        {
          text: "Nama Project",
          align: "left",
          sortable: true,
          value: "nama_pol",
          sortable: true
        },
        { text: "Kode POL", value: "kode_pol", sortable: true },
        { text: "Tahun", value: "tahun", sortable: true },
        { text: "Jumlah Order", value: "jumlah_order", sortable: true },
        { text: "Qty / Inner", value: "per_iner", sortable: true },
        { text: "Type", value: "tipe", sortable: true },
        { text: "Acuan", value: "link", sortable: true }
      ],
      allLaporan: [],
      allpol: [],
      color: ["blue lighten-4", "green lighten-4", "orange lighten-4"],
      dateAwal: "",
      breadcrumbs: [
        {
          text: "Home",
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
    this.$store.commit("SET_JUDUL", "Beranda");
    // this.dateAwal = new Date(new Date() * 1 + 1000 * 3600 * 7)
    //   .toISOString()
    //   .substr(0, 10);
    this.dateAwal = window.tanggal;
    this.ambilResumeHome();
    this.ambil_pol();
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
    ambil_pol() {
      this.table_loading = true;
      axios
        .get("api/ambilpol/urgent", {
          headers: {
            Authorization: "Bearer " + this.$store.state.auth.token
          }
        })
        .then(response => {
          this.allpol = response.data;
        })
        .catch(function(error) {});
    },
    ambilResumeHome() {
      axios
        .get(
          "api/laporan/" +
            this.$store.state.auth.user.username +
            "/" +
            this.dateAwal +
            "/resumeHome",
          {
            headers: {
              Authorization: "Bearer " + this.$store.state.auth.token
            }
          }
        )
        .then(response => {
          this.allLaporan = response.data;
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
      //  return d.getFullYear()+'-'+d.getMonth()+'-'+d.getDay()+' '+d.getHours() + ':' + d.getMinutes() + ':'+d.getSeconds()
      return d.toLocaleString("en-gb");
    },
    print() {
      window.print();
    }
  }
};
</script>
