<template>
  <v-container grid-list-lg fluid>
    <v-layout row wrap>            
      <v-flex xs12>
        <v-card>
          <v-card-title>Resume POL</v-card-title>
          <v-card-text>
            <v-layout>
              <v-flex xs3>Jumlah Lot Masuk : {{ lot_count }}</v-flex>
              <v-flex xs3>Total Barang Masuk : {{ lot_jumlah }}</v-flex>              
              <v-flex xs3>wip : {{ wip.wip }}</v-flex>
            </v-layout>
          </v-card-text>
        </v-card>
      </v-flex>      
      <v-dialog v-model="dialogOperator">
        <v-card>
          <v-card-title>Rincian Laporan {{ tanggal }}</v-card-title>
          <v-card-text>
            <v-flex xs12 v-for="(data,index) in allLaporan" :key="index">
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
            </v-flex>
          </v-card-text>
        </v-card>
      </v-dialog>
    </v-layout>
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
      pol : {},
      lot_count: 0,
      lot_jumlah: 0,      
      wip : {},      
      data_grafik: [],
      tanggal: "",
      dialogOperator: false,
      allLaporan: [],
      jawaban: [
        {
          id: 1
        },
         {
          id: 2
        },
         {
          id: 3
        }
      ],
      color: ["blue lighten-4", "green lighten-4", "orange lighten-4"],
      chartEvents: {},
      headersMonitoring: [
        { text: "No", value: "no", sortable: false, width: "2%" },
        { text: "POL", value: "kode_pol", sortable: true, width: "1%" },
        { text: "Project", value: "nama_pol", sortable: true, width: "15%" },
        { text: "Proses", value: "proses", sortable: true, width: "5%" },
        { text: "Order", value: "order", sortable: true, width: "5%" },
        { text: "Total Good", value: "total", sortable: true, width: "5%" },
        { text: "Dead", value: "dead", sortable: true, width: "5%" },
        { text: "Error", value: "error", sortable: true, width: "5%" },
        { text: "Chip Lemah", value: "lemah", sortable: true, width: "5%" },
        { text: "Card Body", value: "card_body", sortable: true, width: "5%" },
      ],
      headersResume : [
        { text : "No" },
        { text : 'POL'},
        { text : 'Project'},
        { text : 'Proses'},
        { text : "Shift" },        
        { text : "Good" },
        { text : "Dead" },
        { text : "Error" },
        { text : "Chip Lemah" },
        { text : "Card Body" },
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
      ]
    };
  },
  computed: {},

  watch: {},

  created() {
    this.ambilMonitoring();
    //var self = this;
    // this.chartEvents = {
    //   click: function(e) {
    //     // self.operator = e.name;
    //     console.log(e.id_pol);
    //     self.ambilResumeTanggal(e.name);
    //   }
    // };
  },

  methods: {
    
    ambilMonitoring() {
      axios
        .get("../api/laporan/resumecounter/" + this.$route.params.id_pol)
        .then(response => {
          this.pol = response.data.pol;          
          this.lot_count = response.data.lot_count;          
          this.lot_jumlah = response.data.lot_jumlah;          
          this.wip = response.data.wip;          
          this.$store.commit("SET_JUDUL", response.data.pol.nama_pol);
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
    ambilResumeTanggal(tanggal) {
      this.tanggal = tanggal;
      axios
        .get(
          "../api/laporan/resume/" + this.$route.params.id_pol + "/" + tanggal,
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
    ambilResumeHome(operator, id_pol, id_proses) {
      this.tanggal = operator;
      axios
        .get(
          "../api/laporan/all/" +
            operator +
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
        stat = "temporary";
      } else if (status == 2) {
        stat = "Tersimpan";
      } else {
        stat = "Digunakan";
      }
      return stat;
    }
  }
};
</script>

<style scope>
#table1 {
  border-collapse: separate;
  border-spacing: 15px 50px;
}
</style>



