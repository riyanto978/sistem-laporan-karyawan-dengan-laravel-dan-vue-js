<template>
  <v-container grid-list-xl>
    <!-- {{ allLaporanTemp }} -->
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
    <v-dialog v-model="dialogDeleteLaporan" max-width="290">
      <v-card>
        <v-card-title class="orange"></v-card-title>
        <v-card-text>
          <div class="text-xs-center">Apakah Anda ingin menghapus??</div>
        </v-card-text>
        <v-card-actions>
          <v-btn color="blue darken-1" flat @click="deleteLaporan">Delete</v-btn>
          <v-spacer></v-spacer>
          <v-btn color="blue darken-1" flat @click="dialogDeleteLaporan = false">Cancel</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
    <!-- {{ arrayEvents }} -->
    <v-form ref="formLaporan" v-model="validLaporan" lazy-validation>
      <v-layout row wrap>
        <v-flex xs2 md2 sm2 lg2>
          <v-dialog
            v-if="proses_ke == 1"
            v-model="dialogInerPertama"
            max-width="800px"
            align-start
            fill-height
          >
            <v-btn slot="activator" color="green white--text" dark>Inner</v-btn>
            <v-card v-if="loadingIner == false">
              <v-card-title
                class="headline"
                primary-title
              >Inner {{ pol.kode_pol }} {{ pol.nama_pol }} {{ proses.nama_proses }}</v-card-title>
              <v-card-text>
                <v-divider></v-divider>
                <v-text-field v-model="cariIner" label="Cari Inner"></v-text-field>
                <v-btn
                  v-for="(iner,index) in filterIner"
                  :title="iner.isi + ' PCs'"
                  small
                  :class="iner.isi==250?'primary':'green'"
                  fab
                  @click="pilihIner(iner)"
                  :key="index"
                >{{ iner.iner }}</v-btn>
              </v-card-text>
            </v-card>
            <v-card v-if="loadingIner==true">
              <v-card-text>
                <v-layout align-center justify-center>
                  <v-progress-circular :size="70" :width="7" indeterminate color="primary"></v-progress-circular>
                </v-layout>
              </v-card-text>
            </v-card>
          </v-dialog>
          <v-dialog v-else v-model="dialogInerDst" align-start fill-height max-width="1000">
            <v-btn slot="activator" color="green white--text" dark>Inner</v-btn>
            <v-card v-if="loadingIner==false">
              <v-card-title
                class="headline"
                primary-title
                style="border-bottom : 0.1px solid grey;"
              >
                {{ pol.kode_pol }} {{ pol.nama_pol }}
                <v-spacer></v-spacer>
                <v-icon @click="dialogInerDst = false">close</v-icon>
              </v-card-title>
              <v-card-text style="min-height : 400px;">
                <!-- {{ allProses[proses_ke-1].nama_proses }} -->
                <v-text-field v-model="cariIner" label="cari"></v-text-field>
                <v-data-table
                  :headers="headersInerDst"
                  :items="listIner"
                  :search="cariIner"
                  class="elevation-1"
                >
                  <template slot="items" slot-scope="props">
                    <tr>
                      <td>{{ allProses[proses_ke-2].nama_proses }}</td>
                      <td>{{ props.item.iner }}</td>
                      <td>{{ props.item.isi }}</td>
                      <td>{{ props.item.user.username }}</td>
                      <td>{{ getDate(props.item.created_at) }}</td>
                      <td>{{ getTime(props.item.created_at) }}</td>
                      <td>{{ getTime(props.item.updated_at) }}</td>
                      <td>
                        <v-btn color="primary" @click="pilihIner(props.item)">
                          <v-icon small class="mr-2">check_circle</v-icon>pilih
                        </v-btn>
                      </td>
                    </tr>
                  </template>
                  <template slot="no-data">
                    <div class="text-xs-center">Tidak Ada Data</div>
                  </template>
                </v-data-table>
              </v-card-text>
            </v-card>
            <v-card v-if="loadingIner==true">
              <v-card-text>
                <v-layout align-center justify-center>
                  <v-progress-circular :size="70" :width="7" indeterminate color="primary"></v-progress-circular>
                </v-layout>
              </v-card-text>
            </v-card>
          </v-dialog>
        </v-flex>
        <v-flex xs1 md1 sm1 lg1>
          <v-text-field
            :rules="[v => !!v || 'Harus di isi']"
            label="Inner"
            disabled="disabled"
            v-model="laporanReguler.iner"
          ></v-text-field>
        </v-flex>

        <v-flex xs1 sm1 md1 lg1>
          <v-text-field
            label="Isi"
            value="500"
            v-model="laporanReguler.isi"
            :rules="[v => !!v || 'Harus di isi']"
          ></v-text-field>
        </v-flex>
        <v-flex xs1 sm1 md1 lg1>
          <v-select
            :rules="[v => !!v || 'Harus di isi']"
            :items="[1,2]"
            label="Shift"
            v-model="laporanReguler.shift"
          ></v-select>
        </v-flex>
        <v-flex xs1 sm1 md1 lg1>
          <v-select
            :rules="[v => !!v || 'Harus di isi']"
            :items="itemLine"
            label="Line"
            v-model="laporanReguler.line"
          ></v-select>
        </v-flex>
        <v-flex xs2 sm2 md2 lg2>
          <v-btn
            color="primary"
            class="black--text"
            :disabled="!validLaporan"
            @click="saveLaporan"
          >Mulai</v-btn>
        </v-flex>
        <v-flex xs12>
          <v-toolbar color="blue lighten-4">Input Laporan Proses {{ proses.nama_proses }}</v-toolbar>
          <v-data-table
            :headers="headersLaporanTemp"
            hide-actions
            :items="allLaporanTemp"
            class="elevation-1"
          >
            <template slot="items" slot-scope="props">
              <td>
                <v-icon small @click.stop="confirmDeleteLaporan(props.item,'sementara')">delete</v-icon>
              </td>
              <td>{{ props.item.iner }}</td>
              <td>{{ props.item.shift }}</td>
              <td>{{ props.item.line }}</td>
              <td>{{ props.item.isi }}</td>
              <td>{{ props.item.created_at }}</td>
              <td>{{ props.item.oplama }}</td>
              <td>{{ props.item.keterangan }}</td>
              <td>
                <!-- <v-btn color="primary" class="black--text" @click.stop="editLaporan(props.item)">....</v-btn> -->
                <v-icon
                  color="primary"
                  small
                  @click.stop="editLaporan(props.item,'sementara')"
                >check_circle</v-icon>
              </td>
            </template>
            <template slot="no-data">
              <div class="text-xs-center">Tidak Ada Data</div>
            </template>
          </v-data-table>
        </v-flex>
        <v-flex xs12>
          <v-toolbar color="green lighten-4">
            Laporan Selesai Proses {{ proses.nama_proses }}
            <v-spacer></v-spacer>
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
                solo
                height="5px"
                label="Tanggal Awal"
                readonly
                slot="activator"
                v-model="dateAwal"
                prepend-icon="event"
              ></v-text-field>
              <v-date-picker
                :events="arrayEvents"
                event-color="green lighten-1"
                format="dd/mm/yyyy"
                no-title
                @change="ambilLaporanSaved"
                v-model="dateAwal"
                @input="$refs.menu.save(dateAwal)"
              ></v-date-picker>
            </v-menu>
          </v-toolbar>
          <v-data-table
            :headers="headersLaporanSaved"
            hide-actions
            :items="allLaporanSaved"
            class="elevation-1"
          >
            <template slot="items" slot-scope="props">
              <td>
                <v-icon small @click.stop="confirmDeleteLaporan(props.item,'tersimpan')">delete</v-icon>
              </td>
              <td>{{ props.item.iner }}</td>
              <td>{{ props.item.shift }}</td>
              <td>{{ props.item.line }}</td>
              <td>{{ props.item.isi }}</td>
              <td>{{ props.item.created_at }}</td>
              <td>{{ props.item.updated_at }}</td>
              <td>{{ parseInt(props.item.dead) + parseInt(props.item.chip_error) + parseInt(props.item.chip_lemah) + parseInt(props.item.card_body) + parseInt(props.item.miss_laser)+ parseInt(props.item.miss_print) }}</td>
              <td>{{ props.item.oplama }}</td>
              <td>{{ props.item.keterangan }}</td>
              <td>
                <!-- <v-btn color="secondary" class="black--text" @click.stop="editLaporan(props.item)">Edit</v-btn> -->
                <v-icon
                  small
                  class="mr-2"
                  color="secondary"
                  @click.stop="editLaporan(props.item,'tersimpan')"
                >settings_backup_restore</v-icon>
              </td>
            </template>
            <template slot="no-data">
              <div class="text-xs-center">Tidak Ada Data</div>
            </template>
          </v-data-table>
        </v-flex>
      </v-layout>
      <v-layout>
        <v-dialog v-model="dialogUpdateLaporan" width="500px">
          <v-card>
            <v-card-title>
              <span class="headline">Update Laporan</span>
            </v-card-title>
            <v-card-text>
              <v-form ref="formUpdateLaporan" v-model="validUpdateLaporan" lazy-validation>
                <v-layout row wrap align-center>
                  <v-flex xs12 sm12 md12 v-if="proses_ke == 1 && tipe=='sementara'">
                    <v-select
                      required
                      min="0"
                      :items="allLot"
                      item-text="kd_lot"
                      item-value="id_lot"
                      label="Kode Lot"
                      v-model="laporanReguler.id_lot"
                      :rules="[v => !!v || 'Mohon di isi']"
                    ></v-select>
                  </v-flex>
                  <v-flex xs5>
                    <v-text-field
                      min="0"
                      label="Dead"
                      type="number"
                      v-model="laporanReguler.dead"
                      autofocus
                    ></v-text-field>
                  </v-flex>
                  <v-flex xs5 offset-xs2>
                    <v-text-field
                      min="0"
                      label="Chip Lemah"
                      type="number"
                      v-model="laporanReguler.chip_lemah"
                      autofocus
                    ></v-text-field>
                  </v-flex>
                  <v-flex xs5>
                    <v-text-field
                      min="0"
                      label="Card Body"
                      type="number"
                      v-model="laporanReguler.card_body"
                      autofocus
                    ></v-text-field>
                  </v-flex>
                  <v-flex xs5 offset-xs2>
                    <v-text-field
                      min="0"
                      label="Chip Error"
                      type="number"
                      v-model="laporanReguler.chip_error"
                      autofocus
                    ></v-text-field>
                  </v-flex>
                  <v-flex xs5>
                    <v-text-field
                      min="0"
                      label="miss laser"
                      type="number"
                      v-model="laporanReguler.miss_laser"
                      autofocus
                    ></v-text-field>
                  </v-flex>
                  <v-flex xs5 offset-xs2>
                    <v-text-field
                      min="0"
                      label="miss print"
                      type="number"
                      v-model="laporanReguler.miss_print"
                      autofocus
                    ></v-text-field>
                  </v-flex>
                  <v-flex xs5>
                    <v-text-field
                      label="Serial awal"
                      v-model="laporanReguler.serial_awal"
                      autofocus
                    ></v-text-field>
                  </v-flex>
                  <v-flex xs5 offset-xs2>
                    <v-text-field
                      min="0"
                      label="Serial akhir"
                      v-model="laporanReguler.serial_akhir"
                      autofocus
                    ></v-text-field>
                  </v-flex>
                  <v-flex xs12>
                    <v-textarea rows="2" v-model="laporanReguler.keterangan" label="Keterangan"></v-textarea>
                  </v-flex>
                  <v-checkbox label="Log ?" v-model="checkbox"></v-checkbox>
                  <input type="file" ref="log" accept=".txt" required />
                </v-layout>
              </v-form>
            </v-card-text>
            <v-card-actions>
              <v-spacer></v-spacer>
              <v-btn
                color="blue darken-1"
                flat
                :disabled="!validUpdateLaporan"
                @click="updateLaporan"
              >Simpan Laporan</v-btn>
              <!-- <v-btn color="blue darken-1" flat @click="close">Cancel</v-btn> -->
            </v-card-actions>
          </v-card>
        </v-dialog>
      </v-layout>
    </v-form>
  </v-container>
</template>

<script>
import axios from "axios";
export default {
  name: "LaporanReguler",
  props: ["laporan_tipe","pol", "proses_ke", "step", "proses", "allProses"],
  data() {
    return {
      snackbar: false,
      text: "",
      timeout: 4000,
      color: "",
      menu: false,
      dateAwal: "",
      itemLine: [
        1,
        2,
        3,
        4,
        5,
        6,
        7,
        8,
        9,
        10,
        11,
        12,
        13,
        14,
        15,
        16,
        17,
        18,
        19,
        20
      ],
      dialogDeleteLaporan: false,
      dialogUpdateLaporan: false,
      validUpdateLaporan: true,
      allLaporanTemp: [],
      allLaporanSaved: [],
      allLot: [],
      validLaporan: true,
      dialogInerDst: false,
      loadingIner: false,
      checkbox: true,
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
        dead: 0,
        chip_lemah: 0,
        card_body: 0,
        chip_error: 0,
        miss_laser : 0,
        miss_print : 0,
        log: "",
        old: "",
        status: "",
        serial_awal: "",
        serial_akhir: "",
        keterangan: "",
        created_at: "",
        updated_at: ""
      },
      arrayEvents: [],
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
        dead: 0,
        chip_lemah: 0,
        card_body: 0,
        chip_error: 0,
        miss_laser : 0,
        miss_print : 0,
        log: "",
        old: "",
        status: "",
        serial_awal: "",
        serial_akhir: "",
        keterangan: "",
        created_at: "",
        updated_at: ""
      },
      cariIner: "",
      tipe: "",
      dialogInerPertama: false,
      headersInerDst: [
        { text: "Nama Proses", value: "nama_proses", sortable: false },
        { text: "Inner", value: "iner", sortable: true },
        { text: "Isi", value: "Isi", sortable: true },
        { text: "Operator", value: "operator", sortable: true },
        { text: "Tanggal", value: "tanggal", sortable: true },
        { text: "Jam Mulai", value: "jam_mulai", sortable: true },
        { text: "Jam Selesai", value: "jam_selesai", sortable: true },
        { text: "Actions", value: "action", sortable: false }
      ],
      listIner: [],
      headersLaporanTemp: [
        { text: "", value: "", sortable: false },
        { text: "Inner", value: "iner", sortable: true },
        { text: "Shift", value: "shift", sortable: true },
        { text: "Line", value: "line", sortable: true },
        { text: "Isi", value: "isi", sortable: true },
        { text: "Mulai", value: "mulai", sortable: true },
        { text: "Prev Operator", value: "op_lama", sortable: true },
        { text: "Keterangan", value: "keterangan", sortable: true },
        { text: "Actions", value: "action", sortable: false }
      ],
      headersLaporanSaved: [
        { text: "", value: "", sortable: false },
        { text: "Inner", value: "iner", sortable: true },
        { text: "Shift", value: "shift", sortable: true },
        { text: "Line", value: "line", sortable: true },
        { text: "Isi", value: "isi", sortable: true },
        { text: "Mulai", value: "mulai", sortable: true },
        { text: "Selesai", value: "selesai", sortable: true },
        { text: "Reject", value: "reject", sortable: true },
        { text: "Prev Operator", value: "op_lama", sortable: true },
        { text: "Keterangan", value: "keterangan", sortable: true },
        { text: "Actions", value: "action", sortable: false }
      ]
    };
  },
  created() {
    //this.ambilLaporanReguler();
    // this.dateAwal = new Date(new Date() * 1 + 1000 * 3600 * 7)
    //   .toISOString()
    //   .substr(0, 10);

    this.dateAwal = window.tanggal;

    //   this.arrayEvents = [...Array(6)].map(() => {
    //     const day = Math.floor(Math.random() * 30)
    //     const d = new Date()
    //     d.setDate(day)
    //     return d.toISOString().substr(0, 10)
    //   })
  },
  computed: {
    filterIner() {
      return this.listIner.filter(kd =>
        kd.iner.toString().match(this.cariIner)
      );
    }
    // allLaporanSaved() {
    //   return this.allLaporan.filter(kd => kd.status > 1);
    // },
    // allLaporanTemp() {
    //   return this.allLaporan.filter(kd => kd.status == 1);
    // }
  },
  watch: {
    step(val) {
      if (val == 3) {
        this.ambilLaporanTemp();
        this.ambilLaporanSaved();
        this.ambilLaporanTanggal();
      }
    },
    dialogInerPertama(val) {
      if (val) {
        this.ambilInerPertama();
      }
      this.closeIner();
    },
    dialogInerDst(val) {
      if (val) {
        this.ambilInerDst();
      }
      this.closeIner();
    }
  },
  methods: {
    ambilLaporanTemp() {
      axios
        .get(
          "api/laporan/reguler/" +
            this.pol.id_pol +
            "/" +
            this.proses_ke +
            "/" +
            this.$store.state.auth.user.id +
            "/sementara/null"
        )
        .then(response => {
          this.allLaporanTemp = response.data;
        });
    },
    ambilLaporanSaved() {
      axios
        .get(
          "api/laporan/reguler/" +
            this.pol.id_pol +
            "/" +
            this.proses_ke +
            "/" +
            this.$store.state.auth.user.id +
            "/tersimpan/" +
            this.dateAwal
        )
        .then(response => {
          this.allLaporanSaved = response.data;
        });
    },
    ambilLaporanTanggal() {
      axios
        .get(
          "api/laporan/tanggal/" +
            this.pol.id_pol +
            "/" +
            this.proses_ke +
            "/" +
            this.$store.state.auth.user.id
        )
        .then(response => {
          this.arrayEvents = response.data;
        });
    },
    ambilInerPertama() {
      this.loadingIner = true;
      axios
        .get("api/laporan/iner/reguler/pertama/" + this.pol.id_pol)
        .then(response => {
          this.listIner = response.data;
          this.loadingIner = false;
        });
    },
    ambilInerDst() {
      this.loadingIner = true;
      var ke = parseInt(this.proses_ke) - 1;
      axios
        .get("api/laporan/iner/reguler/" + ke + "/" + this.pol.id_pol)
        .then(res => {
          this.listIner = res.data;
          this.loadingIner = false;
        });
    },
    closeIner() {
      this.listIner = [];
      this.cariIner = "";
    },
    saveLaporan() {
      if (this.$refs.formLaporan.validate()) {
        // var cek = this.allLaporanTemp.filter(kd => kd.status == 1);

        // if (cek.length > 0) {
        //   this.tampilAlert("Selesaikan yang sedang berjalan", "info");
        // } else {
        this.laporanReguler.status = 1;
        this.laporanReguler.laporan_tipe = this.laporan_tipe;
        this.snackbar = false;
        axios
          .post("api/reguler", this.laporanReguler)
          .then(response => {
            this.allLaporanTemp.push(response.data);
            this.tampilAlert("Selamat mengerjakan", "success");
            this.closeLaporan();
          })
          .catch(error => {
            this.tampilAlert("Inner Sudah Pernah di input", "red");
            this.closeLaporan();
          });
      }
    },
    closeLaporan() {
      this.DefaultlaporanReguler.shift = this.laporanReguler.shift;
      this.DefaultlaporanReguler.line = this.laporanReguler.line;
      this.laporanReguler = Object.assign({}, this.DefaultlaporanReguler);
    },
    editLaporan(item, tipe) {
      this.tipe = tipe;
      this.ambilLot();
      this.laporanReguler = Object.assign({}, item);
      this.dialogUpdateLaporan = true;
    },
    pilihIner(iner) {
      this.laporanReguler.iner = iner.iner;
      this.laporanReguler.isi = iner.isi;
      this.laporanReguler.old = iner.id_laporan || "";
      this.laporanReguler.proses_ke = this.proses_ke;
      this.laporanReguler.id_pol = this.pol.id_pol;
      this.laporanReguler.id_proses = this.proses.id_proses;
      this.laporanReguler.operator = this.$store.state.auth.user.id;
      this.dialogInerPertama = false;
      this.dialogInerDst = false;
    },
    updateLaporan() {
      if (this.$refs.formUpdateLaporan.validate()) {
        if (this.checkbox == true) {
          var log = "this.$refs.log.files[0] == undefined";
        } else {
          var log = "false";
        }

        if (eval(log)) {
          alert("Masukkan log terlebih dahulu...");
        } else {
          this.log = this.$refs.log.files[0];
          let formData = new FormData();
          formData.append("id_lot", this.laporanReguler.id_lot);
          formData.append("dead", this.laporanReguler.dead);
          formData.append("chip_lemah", this.laporanReguler.chip_lemah);
          formData.append("chip_error", this.laporanReguler.chip_error);
          formData.append("card_body", this.laporanReguler.card_body);
          formData.append("miss_laser", this.laporanReguler.miss_laser);
          formData.append("miss_print", this.laporanReguler.miss_print);
          formData.append("serial_awal", this.laporanReguler.serial_awal);
          formData.append("serial_akhir", this.laporanReguler.serial_akhir);
          formData.append("keterangan", this.laporanReguler.keterangan);
          formData.append("_method", "PUT");
          formData.append("log", this.log);

          axios
            .post("api/reguler/" + this.laporanReguler.id_laporan, formData, {
              headers: {
                Authorization: "Bearer " + this.$store.state.auth.token,
                "Content-Type": "multipart/form-data"
              }
            })
            .then(response => {
              if (this.tipe == "sementara") {
                var index = this.allLaporanTemp.findIndex(
                  x => x.id_laporan == this.laporanReguler.id_laporan
                );

                this.allLaporanTemp.splice(index, 1);
                this.allLaporanSaved.push(response.data[0]);
              } else {
                var index = this.allLaporanSaved.findIndex(
                  x => x.id_laporan == this.laporanReguler.id_laporan
                );
                Object.assign(this.allLaporanSaved[index], response.data[0]);
              }
              this.tampilAlert("Job berhasil di Simpan", "blue");
              this.closeLaporan();
              this.dialogUpdateLaporan = false;
            })
            .catch(response => {
              this.tampilAlert("Gagal", "red");
            });
        }
      }
    },
    ambilLot() {
      axios.get("api/lot/laporan/" + this.pol.id_pol).then(response => {
        this.allLot = response.data;
      });
    },
    tampilAlert(alert, color) {
      this.text = alert;
      this.color = color;
      this.snackbar = true;
    },
    confirmDeleteLaporan(item, tipe) {
      this.tipe = tipe;
      this.itemDeleteLaporan = item;
      this.dialogDeleteLaporan = true;
    },
    getTime(time) {
      var d = new Date(time);
      return d.getHours() + ":" + d.getMinutes() + ":" + d.getSeconds();
    },
    getDate(time) {
      var d = new Date(time);
      var month = new Array();
      month[0] = "Januari";
      month[1] = "Februari";
      month[2] = "Maret";
      month[3] = "April";
      month[4] = "Mei";
      month[5] = "Juni";
      month[6] = "Juli";
      month[7] = "Agustus";
      month[8] = "September";
      month[9] = "Oktober";
      month[10] = "November";
      month[11] = "Desember";
      return d.getDay() + " " + month[d.getMonth()] + " " + d.getFullYear();
    },
    deleteLaporan() {
      this.snackbar = false;
      if (this.tipe == "sementara") {
        var index = this.allLaporanTemp.indexOf(this.itemDeleteLaporan);
      } else {
        var index = this.allLaporanSaved.indexOf(this.itemDeleteLaporan);
      }

      axios
        .delete("api/reguler/" + this.itemDeleteLaporan.id_laporan)
        .then(response => {
          this.dialogDeleteLaporan = false;
          if (this.tipe == "sementara") {
            this.allLaporanTemp.splice(index, 1);
          } else {
            this.allLaporanSaved.splice(index, 1);
          }

          this.tampilAlert("Job berhasil di hapus", "red");
        });
    }
  }
};
</script>
