<template>
  <v-container grid-list-lg fluid>
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
              @change="ambilMonitoring"
              v-model="dateAwal"
              @input="$refs.menu.save(dateAwal)"
            ></v-date-picker>
          </v-menu>
        </v-flex>

        <v-flex xs1 offset-xs1>
          <v-select @change="ambilMonitoring" :items="[1,2,'all']" label="Shift" v-model="shift"></v-select>
        </v-flex>

        <v-flex xs12>
          <v-toolbar class="green lighten-2">Reguler</v-toolbar>
          <v-data-table
            :headers="headersMonitoring"
            hide-actions
            :items="dataMonitoring"
            class="elevation-1"
          >
            <template slot="items" slot-scope="props">
              <tr>
                <td>{{ props.index + 1 }}</td>
                <td>{{ props.item.kode_pol }}</td>
                <td>{{ props.item.nama_pol }}</td>
                <td>{{ props.item.proses }}</td>
                <td>{{ props.item.dead }}</td>
                <td>{{ props.item.error }}</td>
                <td>{{ props.item.lemah }}</td>
                <td>{{ props.item.total }}</td>
                <td>{{ props.item.jmlorder }}</td>
                <td>{{ props.item.jmlorder - props.item.terproses }}</td>
              </tr>
            </template>
            <template slot="no-data">
              <div class="text-xs-center">Tidak Ada Data</div>
            </template>
          </v-data-table>
          <br />
        </v-flex>
        <v-flex xs12>
          <v-toolbar class="orange lighten-2">Memo</v-toolbar>
          <v-data-table
            :headers="headersMonitoring"
            hide-actions
            :items="dataMemo"
            class="elevation-1"
          >
            <template slot="items" slot-scope="props">
              <tr>
                <td>{{ props.index + 1 }}</td>
                <td>{{ props.item.kode_pol }}</td>
                <td>{{ props.item.nama_pol }}</td>
                <td>{{ props.item.proses }}</td>
                <td>{{ props.item.dead }}</td>
                <td>{{ props.item.error }}</td>
                <td>{{ props.item.lemah }}</td>
                <td>{{ props.item.total }}</td>
                <td>{{ props.item.jmlorder }}</td>
                <td>{{ props.item.jmlorder - props.item.terproses }}</td>
              </tr>
            </template>
            <template slot="no-data">
              <div class="text-xs-center">Tidak Ada Data</div>
            </template>
          </v-data-table>
          <br />
        </v-flex>
        <v-flex xs12>
          <v-toolbar class="blue lighten-2">Counter & Wrapping</v-toolbar>
          <v-data-table
            :headers="headersCounter"
            hide-actions
            :items="dataCounter"
            class="elevation-1"
          >
            <template slot="items" slot-scope="props">
              <tr>
                <td>{{ props.index + 1 }}</td>
                <td>{{ props.item.kode_pol }}</td>
                <td>{{ props.item.nama_pol }}</td>
                <td>{{ props.item.jumlah }}</td>
                <td>{{ props.item.iner_awal }}</td>
                <td>{{ props.item.iner_akhir }}</td>
                <td>{{ props.item.reject_plastic }}</td>
                <td>{{ props.item.wip.wip }}</td>
              </tr>
            </template>
            <template slot="no-data">
              <div class="text-xs-center">Tidak Ada Data</div>
            </template>
          </v-data-table>
          <br />
        </v-flex>
        <v-flex xs12>
          <v-toolbar class="lime lighten-2">Transfer</v-toolbar>
          <v-data-table
            :headers="headersTransfer"
            hide-actions
            :items="dataTransfer"
            class="elevation-1"
          >
            <template slot="items" slot-scope="props">
              <tr>
                <td>{{ props.index + 1 }}</td>
                <td>{{ props.item.pol.kode_pol }}</td>
                <td>{{ props.item.pol.nama_pol }}</td>
                <td>{{ props.item.counter }}</td>
                <td>{{ props.item.isi }}</td>
              </tr>
            </template>
            <template slot="no-data">
              <div class="text-xs-center">Tidak Ada Data</div>
            </template>
          </v-data-table>
          <br />
        </v-flex>
        <v-flex xs12>
          <div class="text-xs-center">Chart Laporan Regulerr</div>
        </v-flex>
        <v-flex xs6 v-for="(item,index) in data" :key="index">
          <br />
          <chart :item="item" :index="index" @ambilResume="ambilResumeHome"></chart>
        </v-flex>

        <v-dialog v-model="dialogOperator" width="1400">
          <v-card>
            <v-card-title>Laporan {{ operator }}</v-card-title>
            <v-card-text>
              <v-flex xs12 v-for="(data,index) in allLaporan" :key="index">
                <v-toolbar
                  dense
                  :color="warna(index)"
                >Pol : {{ data.pol }} | {{ data.nama_pol }} | {{ data.nama_proses }}</v-toolbar>
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
            </v-card-text>
          </v-card>
        </v-dialog>
      </v-layout>
    </v-fade-transition>
  </v-container>
</template>

<script>
import axios from "axios";
import chart from "~/components/form/chart";

export default {
  components: {
    chart
  },
  data() {
    return {
      show: false,
      menu: false,
      shift: 1,
      dialogOperator: false,
      dataMonitoring: [],
      dataCounter: [],
      dataTransfer: [],
      dataMemo:[],
      headersMonitoring: [
        { text: "No", value: "no", sortable: false, width: "5%" },
        { text: "Kode POL", value: "kode_pol", sortable: true, width: "15%" },
        {
          text: "Project",
          value: "nama_pol",
          sortable: true,
          width: "15%"
        },
        { text: "Proses", value: "proses", sortable: true, width: "5%" },
        { text: "Dead", value: "dead", sortable: true, width: "5%" },
        { text: "Error", value: "error", sortable: true, width: "5%" },
        { text: "Lemah", value: "lemah", sortable: true, width: "15%" },
        { text: "Total / Shift", value: "total", sortable: true, width: "5%" },
        {
          text: "Jumlah Order",
          value: "jmlorder",
          sortable: true,
          width: "5%"
        },
        { text: "Kekurangan", value: "kurang", sortable: true }
      ],
      headersCounter: [
        { text: "No", value: "no", sortable: false, width: "5%" },
        { text: "Kode POL", value: "kode_pol", sortable: true, width: "15%" },
        { text: "Project", value: "nama_pol" },
        { text: "Jumlah", value: "jumlah", sortable: true, width: "5%" },
        { text: "Inner Awal", value: "iner_awal", sortable: true, width: "5%" },
        {
          text: "Inner Akhir",
          value: "iner_akhir",
          sortable: true,
          width: "5%"
        },
        {
          text: "Reject Plastic",
          value: "reject_plastic",
          sortable: true,
          width: "15%"
        },
        {
          text: "WIP Terakhir",
          value: "wip terakhir",
          sortable: true,
          width: "15%"
        }
      ],
      headersTransfer: [
        { text: "No", value: "no", sortable: false, width: "5%" },
        { text: "Kode POL", value: "kode_pol", sortable: true, width: "15%" },
        { text: "Project", value: "nama_pol" },
        {
          text: "Jumlah Inner",
          value: "jumlah",
          sortable: true,
          width: "5%"
        },
        {
          text: "Total",
          value: "total",
          sortable: true,
          width: "5%"
        }
      ],
      headersLaporanSaved: [
        { text: "No", value: "no", sortable: false },
        { text: "Proses", value: "proses", sortable: false },
        { text: "Inner", value: "iner", sortable: true },
        { text: "Shift", value: "shift", sortable: true },
        { text: "Line", value: "line", sortable: true },
        { text: "Isi", value: "isi", sortable: true },
        { text: "Mulai", value: "created_at", sortable: true },
        { text: "Selesai", value: "updated_at", sortable: true },
        { text: "Reject", value: "reject", sortable: true },
        { text: "Keterangan", value: "keterangan", sortable: true },
        { text: "Status", value: "status", sortable: false }
      ],
      //   colors : ['#ca8622', '#bda29a','#6e7074', '#546570', '#c4ccd3'],
      color: ["blue lighten-2", "green lighten-2", "orange lighten-2"],
      dateAwal: "",
      data: [],
      operator: "",
      chartEvents: {},
      allLaporan: [],
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
    this.$store.commit("SET_JUDUL", "Monitoring");
    // this.dateAwal = new Date(new Date() * 1 + 1000 * 3600 * 7)
    //   .toISOString()
    //   .substr(0, 10);
    this.dateAwal = window.tanggal;
    this.ambilMonitoring();
    // var self = this;
    // this.chartEvents = {
    //   click: function(e) {
    //     // self.operator = e.name;
    //     self.ambilResumeHome(e.name);
    //   }
    // };
  },
  methods: {
    ambilMonitoring() {
      axios
        .get(
          "api/laporan/" + this.shift + "/" + this.dateAwal + "/monitoring",
          {
            headers: {
              Authorization: "Bearer " + this.$store.state.auth.token
            }
          }
        )
        .then(response => {
          this.dataMonitoring = response.data.data;
          this.dataMemo = response.data.memo;
          this.dataCounter = response.data.counter;
          this.dataTransfer = response.data.transfer;
          this.data = response.data.total;
        })
        .catch(err => {});
    },
    warna(index) {
      if (index > 2) {
        var ind = index - 3;
      } else {
        var ind = index;
      }
      return this.color[ind];
    },
    ambilResumeHome(operator, id_pol, id_proses) {
      this.operator = operator;
      axios
        .get(
          "api/laporan/" +
            operator +
            "/" +
            this.dateAwal +
            "/" +
            id_proses +
            "/" +
            id_pol +
            "/resumeMonitoring",
          {
            headers: {
              Authorization: "Bearer " + this.$store.state.auth.token
            }
          }
        )
        .then(response => {
          this.allLaporan = response.data;
          this.dialogOperator = true;
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
    }
  }
};
</script>