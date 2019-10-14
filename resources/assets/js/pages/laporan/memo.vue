<template>
  <v-container fluid>
    <v-fade-transition slot="append">
    <v-layout row wrap v-if="show">      
      <v-snackbar
        v-model="snackbar"
        :left="true"
        :bottom="true"
        :timeout="timeout"
        :multi-line="true"
        :color="color"
        :auto-height="true"
      >
        {{ text }}
        <v-btn color="pink" flat @click="snackbar = false">Close</v-btn>
      </v-snackbar>
      
      <!-- {{ totalItems }} -->
      <v-flex xs12 sm12 md12 lg12>
        <v-card>
          <v-layout align-end justify-center row>
            <v-flex xs3 v-if="step == 1">
              <v-text-field
                append-icon="search"
                v-model="cari"
                placeholder="POL / Project"
                @keyup="waitInput"
              ></v-text-field>
            </v-flex>
            <v-flex xs2 v-if="step == 1">
              <v-select                
                :items="combo_status"
                item-text="text"
                item-value="data"
                @change="ambilPol"
                v-model="status"
                label="Type"
              ></v-select>
            </v-flex>

            <v-btn v-if="step == 2" @click="step = 1" color="orange lighten-3">
              <v-icon>arrow_back</v-icon>Daftar Pol
            </v-btn>
            <v-btn v-if="step == 3" @click="step = 2" color="orange lighten-3">
              <v-icon>arrow_back</v-icon>Daftar Proses
            </v-btn>

            <v-flex xs2>
              <v-text-field v-if="step > 1" label="POL" v-model="pol.kode_pol" readonly></v-text-field>
            </v-flex>
            <v-flex xs6>
              <v-text-field v-if="step > 1" label="Project" v-model="pol.nama_pol" readonly></v-text-field>
            </v-flex>
            <v-flex xs3>
              <v-text-field
                v-if="step > 2"
                label="Proses"
                v-model="proses.nama_proses"
                readonly
              ></v-text-field>
            </v-flex>
            <v-flex xs1>
              <v-text-field v-if="step > 2" label="Proses Ke" v-model="proses_ke" readonly></v-text-field>
            </v-flex>
          </v-layout>
        </v-card>
        <v-stepper v-model="step">
          <v-stepper-header>
            <v-stepper-step :complete="step > 1" step="1">Pilih Pol</v-stepper-step>
            <v-divider></v-divider>
            <v-stepper-step :complete="step > 2" step="2">Pilih Proses</v-stepper-step>
            <v-divider></v-divider>
            <v-stepper-step step="3">Laporan</v-stepper-step>
          </v-stepper-header>
          <v-stepper-items>
            <v-stepper-content step="1">
              <v-data-table
                :headers="headersPol"
                :items="allPol"
                :loading="loading"
                hide-actions
                class="elevation-1"
              >
                <template slot="items" slot-scope="props">
                  <td>{{ (page-1)*5+ props.index+1 }}</td>
                  <td>{{ props.item.tahun }}</td>
                  <td>{{ props.item.kode_pol }}</td>
                  <td>{{ props.item.nama_pol }}</td>
                  <td>{{ props.item.jumlah_order }} / {{ props.item.per_iner }}</td>
                  <td>{{ Math.ceil(props.item.jumlah_order/props.item.per_iner) }}</td>
                  <td>
                    <v-btn
                      color="blue lighten-4"
                      target="blank"
                      :href="`http://qc3/qc/acuan/lampiran/${props.item.lampiran}`"
                    >Lampiran</v-btn>
                  </td>
                  <td>
                    {{ props.item.status==0?'Berjalan':'Finish'  }}
                  </td>
                  <td>
                    <v-btn color="success" @click="pilihPol(props.item)">Kerjakan</v-btn>
                  </td>
                </template>
                <template slot="no-data">
                  <div class="text-xs-center">Tidak Ada Data</div>
                </template>
              </v-data-table>
              <div class="text-xs-center pt-2">
                <v-pagination v-model="page" :length="pages" @input="ambilPol"></v-pagination>
              </div>
            </v-stepper-content>
            <v-stepper-content step="2">
              <v-btn color="primary" dark @click="dialogLot = true">Lot</v-btn>
              <Lot v-model="dialogLot" :pol="pol"></Lot>
              <v-data-table
                :headers="headersProses"
                :items="allProses"
                hide-actions
                class="elevation-1"
              >
                <template slot="items" slot-scope="props">
                  <td>{{ props.index+1 }}</td>
                  <td>{{ props.item.nama_proses }}</td>
                  <td>
                    <v-btn color="success" @click="pilihProses(props.item,props.index+1)">Pilih</v-btn>
                  </td>
                </template>
                <template slot="no-data">
                  <div class="text-xs-center">Tidak Ada Data</div>
                </template>
              </v-data-table>
            </v-stepper-content>
            <v-stepper-content step="3">
                <LaporanReguler laporan_tipe="memo" :allProses="allProses" :pol="pol" :proses="proses" :proses_ke="proses_ke" :step="step" ></LaporanReguler>
            </v-stepper-content>
          </v-stepper-items>
        </v-stepper>
      </v-flex>
    </v-layout>
    </v-fade-transition>
  </v-container>
</template>

<script>
import axios from "axios";
import Lot from "~/components/form/lot";
import LaporanReguler from "~/components/form/LaporanReguler";
export default {
  components: {
    Lot,
    LaporanReguler
  },
  data() {
    return {      
      show : false,
      snackbar: false,
      text: "",
      timeout: 4000,
      color: "",
      step: 1,
      cari: "",                  
      dialogLot: false,      
      log: "",
      status : 0,
      combo_status: [
        {
          data: 0,
          text: "Berjalan"
        },
        {
          data: 1,
          text: "Finish"
        },        
      ],
      itemDeleteLaporan: {},      
      headersProses: [
        { text: "No", value: "No", sortable: false },
        {
          text: "Nama proses",
          value: "nama_proses",
          sortable: false,
          width: "600px"
        },
        { text: "Actions", value: "name", sortable: false }
      ],
      headersPol: [
        { text: "No", value: "No", sortable: false },
        { text: "Tahun", value: "tahun", sortable: false },
        { text: "Kode POL", value: "kode_pol", sortable: false },
        { text: "Nama Pol", value: "nama_pol", sortable: false },
        { text: "Jumlah Order", value: "jumlah_order", sortable: false },
        { text: "Total inner", value: "total_iner", sortable: false },
        { text: "Lampiran", value: "lampiran", sortable: false },
        { text: "status", value: "status", sortable: false },
        { text: "Actions", value: "name", sortable: false }
      ],            
      loading: false,
      allPol: [],
      allProses: [],
      allLaporan: [],      
      cariIner: "",
      proses: {},
      pol: {},
      proses_ke: null,
      page: 1,
      totalItems: 0,
      timer: 0,      
      laporanReguler: {
        id_laporan: "",
        id_pol: "",
        iner: "",
        isi: "",
        id_proses: "",
        proses_ke: "",
        shift: "",
        line: "",
        operator: "",
        id_lot: "",
        dead: "",
        chip_lemah: "",
        card_body: "",
        chip_error: "",
        log: "",
        old: "",
        status: "",
        serial_awal: "",
        serial_akhir: "",
        keterangan: "",
        created_at: "",
        updated_at: ""
      },
      DefaultlaporanReguler: {
        id_laporan: "",
        id_pol: "",
        iner: "",
        isi: "",
        id_proses: "",
        proses_ke: "",
        shift: "",
        line: "",
        operator: "",
        id_lot: "",
        dead: "",
        chip_lemah: "",
        card_body: "",
        chip_error: "",
        log: "",
        old: "",
        status: "",
        serial_awal: "",
        serial_akhir: "",
        keterangan: "",
        created_at: "",
        updated_at: ""
      },        
      allLot: [],
      breadcrumbs: [
        {
          text: "Home",
          disabled: false,
          href: ""
        },
        {
          text: "Memo",
          disabled: false,
          href: ""
        }
      ]
    };
  },
  computed: {    
    // filterPol() {
    //   return this.allPol.filter(pros => {
    //     return pros.kode_pol.toLowerCase().match(this.cari)
    //   })
    // },
    pages() {
      return Math.ceil(this.totalItems / 5);
    },
   
  },
  mounted() {
    
  },
  watch: {
    
    // dialogLot(val) {
    //   this.ambilLot();
    // }
  },
  created() {
    // window.config.appName = 'Laporan';
    setTimeout(() => {
      this.show = true;
    }, 100);
    this.$store.commit("CHANGE_BREAD", { list: this.breadcrumbs });
    this.$store.commit("SET_JUDUL", "Memo");
    this.ambilPol();
    
  },
  methods: {
    ambilLot() {
      axios.get("api/lot/data/" + this.pol.id_pol).then(response => {
        this.allLot = response.data;
      });
    },
    ambilPol() {
      this.loading = true;
      if (this.cari == "") {
        var cari = null;
      } else {
        var cari = this.cari;
      }
      axios.get("api/data/pol/memo/" + cari+'/'+this.status+ "?page=" + this.page).then(response => {
        this.allPol = response.data.data;
        this.totalItems = response.data.total;
        this.loading = false;
      });
    },    
    tampilAlert(alert, color) {
      this.text = alert;
      this.color = color;
      this.snackbar = true;
    },
    waitInput() {
      this.page = 1;
      this.loading = true;
      if (this.timer) {
        clearTimeout(this.timer);
        this.timer = null;
      }
      this.timer = setTimeout(() => {
        this.ambilPol();
      }, 600);
    },
    ambilProses(id_pol) {
      axios.get("api/pol/resume/" + id_pol).then(res => {
        this.allProses = res.data;
      });
    },    
    pilihPol(item) {
      this.step = 2;
      this.allProses = JSON.parse(item.proses);
      this.pol = item;
    },
    pilihProses(item, ke) {
      this.proses = item;
      this.proses_ke = ke;
      this.step = 3;
      // this.ambilLaporanReguler();
    },        
    getWaktu(date) {
      var d = new Date(new Date(date) * 1 + 1000 * 3600 * 7);
      //  return d.getFullYear()+'-'+d.getMonth()+'-'+d.getDay()+' '+d.getHours() + ':' + d.getMinutes() + ':'+d.getSeconds()
      
      return d.toLocaleString("en-gb");
    }
  }
};
</script>

