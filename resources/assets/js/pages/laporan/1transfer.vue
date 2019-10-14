<template>
  <v-container align-center fluid>
    {{ allTransfer }}
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
        <!-- {{ belumTransferSelesai }} -->
        <v-dialog v-model="dialogHistory" width="1200">
          <v-card>
            <v-card-title>Riwayat Transfer</v-card-title>
            <v-card-text>
              <v-text-field
                append-icon="search"
                v-model="cariHistory"
                placeholder="No Inner"
                @keyup="waitInputHistory"
              ></v-text-field>
              <v-data-table
                :headers="headersHistory"
                :items="allHistory"
                :loading="loading"
                hide-actions
                class="elevation-1"
              >
                <template slot="items" slot-scope="props">
                  <td>{{ (pageHistory-1)*5+ props.index+1 }}</td>
                  <td>{{ props.item.pol.kode_pol }}</td>
                  <td>{{ props.item.pol.nama_pol }}</td>
                  <td>{{ props.item.iner }}</td>
                  <td>{{ props.item.operator }}</td>
                  <td v-if="props.item.status == 1">Menunggu Konfirmasi</td>
                  <td v-else>Di Terima</td>
                  <td>{{ props.item.created_at }}</td>
                  <td>{{ props.item.penerima }}</td>
                  <td v-if="props.item.penerima!=null">{{ props.item.updated_at }}</td>
                </template>
                <template slot="no-data">
                  <div class="text-xs-center">Tidak Ada Data</div>
                </template>
              </v-data-table>
              <div class="text-xs-center pt-2">
                <v-pagination v-model="pageHistory" :length="pagesHistory" @input="ambilHistory"></v-pagination>
              </div>
            </v-card-text>
          </v-card>
        </v-dialog>

        <v-flex xs12>
          <v-toolbar color="white" flat>
            <v-toolbar-title>
              <v-text-field
                append-icon="search"
                v-if="step == 1"
                v-model="cari"
                placeholder="POL atau Project"
                @keyup="waitInput"
              ></v-text-field>
              <v-btn v-if="step == 2" @click="step = 1" color="orange lighten-3">
                <v-icon>arrow_back</v-icon>Daftar Pol
              </v-btn>
            </v-toolbar-title>

            <v-layout align-end justify-center row>
              <v-flex xs4>
                <v-text-field v-if="step > 1" label="POL" v-model="pol.kode_pol" readonly></v-text-field>
              </v-flex>
              <v-flex xs8>
                <v-text-field v-if="step > 1" label="Project" v-model="pol.nama_pol" readonly></v-text-field>
              </v-flex>
            </v-layout>
          </v-toolbar>
          <v-stepper v-model="step">
            <v-stepper-header>
              <v-stepper-step :complete="step > 1" step="1">Pilih Pol</v-stepper-step>
              <v-divider></v-divider>
              <v-stepper-step step="2">Transfer</v-stepper-step>
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
                    <td>{{ props.item.tipe }}</td>
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
                <v-container grid-list-xl>
                  <v-layout row wrap>
                    <v-flex xs2>
                      <v-select v-model="shift" :items="[1,2]" label="Shift"></v-select>
                    </v-flex>
                    <v-flex xs2 offset-xs8>
                      <v-btn @click="dialogHistory = true">History</v-btn>
                    </v-flex>
                    <v-flex xs6>
                      <v-card>
                        <v-card-title primary-title class="green lighten-3">
                          <h3 class="headline mb-0">Belum Transfer</h3>
                          <v-spacer></v-spacer>
                          Jumlah : {{ totalBelum }}
                        </v-card-title>
                        <v-card-text style="max-height :300px;overflow-y : auto;">
                          <v-layout row wrap>
                            <v-btn
                              v-for="(iner,index) in belumTransfer"
                              :title="iner.isi + ' PCs'"
                              small
                              :class="iner.saved==true?'primary':'green'"
                              fab
                              @click="iner.status = 1;iner.saved = !iner.saved"
                              :key="index"
                            >{{ iner.iner }}</v-btn>
                          </v-layout>
                        </v-card-text>
                      </v-card>
                    </v-flex>
                    <v-flex xs6>
                      <v-card>
                        <v-card-title primary-title class="blue lighten-3">
                          <div>
                            <h3 class="headline mb-0">Sudah Transfer</h3>
                          </div>
                          <v-spacer></v-spacer>
                          Jumlah : {{ totalSudah }}
                        </v-card-title>
                        <v-card-text style="max-height :300px;overflow-y : auto;">
                          <v-btn
                            v-for="(iner,index) in sudahTransfer"
                            :title="iner.isi + ' PCs'"
                            small
                            :class="iner.saved==true?'primary':'green'"
                            fab
                            @click="iner.status = 0;iner.saved = !iner.saved"
                            :key="index"
                          >{{ iner.iner }}</v-btn>
                        </v-card-text>
                      </v-card>
                    </v-flex>
                    <v-flex xs6>
                      <p class="text-xs-center">
                        <v-btn
                          v-if="belumTransferSementara.length > 0 "
                          @click.stop="simpanBelum"
                          color="green"
                        >Batal Transfer</v-btn>
                      </p>
                    </v-flex>
                    <v-flex xs6>
                      <p class="text-xs-center">
                        <v-btn
                          color="green"
                          v-if="belumTransferSelesai.length>0"
                          @click.stop="simpanSudah"
                        >Transfer</v-btn>
                      </p>
                    </v-flex>
                  </v-layout>
                </v-container>
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
export default {
  data() {
    return {
      show : false,
      snackbar: false,
      text: "",
      show: false,
      timeout: 4000,
      color: "",
      pol: {},
      cariHistory: "",
      allPol: [],
      totalItems: 0,
      totalItemsHistory: 0,
      allTransfer: [],
      allHistory: [],
      loading: false,
      shift: 1,
      step: 1,
      page: 1,
      pageHistory: 1,
      dialogHistory: false,
      headersPol: [
        { text: "No", value: "No", sortable: false },
        { text: "Tahun", value: "tahun", sortable: false },
        { text: "POL", value: "kode_pol", sortable: false },
        { text: "Project", value: "nama_pol", sortable: false },
        { text: "Jumlah Order", value: "jumlah_order", sortable: false },
        { text: "Total inner", value: "total_iner", sortable: false },
        { text: "Tipe", value: "Tipe", sortable: false },
        { text: "Actions", value: "name", sortable: false }
      ],
      headersHistory: [
        { text: "No", value: "No", sortable: false },
        { text: "POL", value: "kode_pol", sortable: false },
        { text: "Project", value: "Nama pol", sortable: false },
        { text: "No Inner", value: "iner", sortable: false },
        { text: "Operator", value: "operator", sortable: false },
        { text: "Status", value: "status", sortable: false },
        { text: "Di transfer", value: "dibuat_tgl ", sortable: false },
        { text: "Penerima", value: "penerima", sortable: false },
        { text: "diterima", value: "diterima", sortable: false },
      ],
      cari: "",
      breadcrumbs: [
        {
          text: "Home",
          disabled: false,
          href: ""
        },
        {
          text: "Transfer",
          disabled: false,
          href: ""
        }
      ]
    };
  },
  computed: {
    pages() {
      return Math.ceil(this.totalItems / 5);
    },
    pagesHistory() {
      return Math.ceil(this.totalItemsHistory / 5);
    },
    belumTransfer() {
      return this.allTransfer.filter(x => x.status == 0);
    },
    sudahTransfer() {
      return this.allTransfer.filter(x => x.status == 1);
    },
    belumTransferSementara() {
      return this.allTransfer.filter(x => x.status == 0 && x.saved == false);
    },
    belumTransferSelesai() {
      return this.allTransfer.filter(x => x.status == 1 && x.saved == false);
    },
    totalBelum() {
      if (this.belumTransfer.length > 0) {
        return this.belumTransfer.reduce((acc, item) => acc + item.isi, 0);
      } else {
        return 0;
      }
    },
    totalSudah() {
      if (this.sudahTransfer.length > 0) {
        return this.sudahTransfer.reduce((acc, item) => acc + item.isi, 0);
      } else {
        return 0;
      }
    }
  },
  created() {
    setTimeout(() => {
      this.show = true;
    }, 100);
    this.$store.commit("CHANGE_BREAD", { list: this.breadcrumbs });
    this.$store.commit("SET_JUDUL", "Transfer");
    this.ambilPol();
  },
  watch: {
    dialogHistory(val) {
      if (val) {
        this.ambilHistory();
      }
    },
    step(val) {
      if (val == 1) {
        this.page = 1;
        this.ambilPol();
      }
    }
  },
  methods: {
    ambilPol() {
      this.loading = true;
      if (this.cari == "") {
        var cari = null;
      } else {
        var cari = this.cari;
      }
      axios
        .get("api/data/poltransfer/" + cari + "?page=" + this.page)
        .then(response => {
          this.allPol = response.data.data;
          this.totalItems = response.data.total;
          this.loading = false;
        });
    },
    ambilHistory() {
      this.loading = true;
      if (this.cariHistory == "") {
        var cari = null;
      } else {
        var cari = this.cariHistory;
      }
      axios
        .get(
          "api/transfer/history/" +
            this.pol.id_pol +
            "/" +
            cari +
            "?page=" +
            this.pageHistory
        )
        .then(response => {
          this.allHistory = response.data.data;
          this.totalItemsHistory = response.data.total;
          this.loading = false;
        });
    },
    ambilDataPol() {
      axios
        .get("api/belumtransfer/" + this.pol.tipe + "/" + this.pol.id_pol)
        .then(response => {
          this.allTransfer = response.data;
          this.loading = false;
        });
    },
    simpanBelum() {
      axios
        .post("api/transfer/belum", {
          belum: JSON.stringify(this.belumTransferSementara),
          id_pol: this.pol.id_pol,
          operator: this.$store.state.auth.user.name,
          shift: this.shift
        })
        .then(response => {
          this.tampilAlert("Transfer Di Batalkan", "primary");
          this.ambilDataPol();
        });
    },
    simpanSudah() {
      axios
        .post("api/transfer/sudah", {
          sudah: JSON.stringify(this.belumTransferSelesai),
          id_pol: this.pol.id_pol,
          operator: this.$store.state.auth.user.name,
          shift: this.shift
        })
        .then(response => {
          this.tampilAlert("Berhasil Di Transfer", "primary");
          this.ambilDataPol();
        });
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
    waitInputHistory() {
      this.page = 1;
      this.loading = true;
      if (this.timer) {
        clearTimeout(this.timer);
        this.timer = null;
      }
      this.timer = setTimeout(() => {
        this.ambilHistory();
      }, 600);
    },
    pilihPol(item) {
      this.step = 2;
      this.pol = item;
      this.ambilDataPol();
    },
    tampilAlert(alert, color) {
      this.text = alert;
      this.color = color;
      this.snackbar = true;
    },
    alert(){
      alert('hi');
    }
  }
};
</script>
