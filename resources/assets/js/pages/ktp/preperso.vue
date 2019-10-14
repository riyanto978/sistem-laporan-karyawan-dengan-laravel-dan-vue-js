<template>
  <v-container grid-list-xl fluid>
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
    <v-dialog v-model="dialogDelete" max-width="290">
      <v-card>
        <v-card-title class="orange"></v-card-title>
        <v-card-text>
          <div class="text-xs-center">Apakah Anda ingin menghapus??</div>
        </v-card-text>
        <v-card-actions>
          <v-btn color="blue darken-1" flat @click="deleteLaporan">Delete</v-btn>
          <v-spacer></v-spacer>
          <v-btn color="blue darken-1" flat @click="dialogDelete = false">Cancel</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <v-form ref="form" v-model="valid" lazy-validation>
      <v-layout row wrap>
        <v-flex xs1>
          <v-dialog v-model="dialogPreperso" width="1000" origin="bottom center">
            <v-btn slot="activator" round color="primary" dark>Applet</v-btn>
            <v-card>
              <v-card-title class="headline grey lighten-2" primary-title>
                Cari Pol
                <v-spacer></v-spacer>
                <v-icon @click="dialogPreperso = false">close</v-icon>
              </v-card-title>
              <v-card-text>
                <v-text-field v-model="cari" label="Cari" @keyup="waitInput"></v-text-field>
                <v-data-table
                  :headers="headersApplet"
                  :items="allApplet"
                  class="elevation-1"
                  :loading="table_loading"
                >
                  <template slot="items" slot-scope="props">
                    <tr>
                      <td>{{ props.item.id_applet }}</td>
                      <td>{{ props.item.pol.kode_pol }}</td>
                      <td>{{ props.item.pol.tahun }}</td>
                      <td>{{ props.item.pol.nama_pol }}</td>
                      <td>{{ props.item.pol.jumlah_order }}</td>
                      <td>{{ props.item.tipe_chip }}</td>

                      <td>
                        <v-btn class="success" @click="selectItem(props.item)">Pilih</v-btn>
                      </td>
                    </tr>
                  </template>
                  <template slot="no-data">
                    <div class="text-xs-center">Tidak Ada Data</div>
                  </template>
                </v-data-table>
                <div class="text-xs-center pt-2">
                  <v-pagination v-model="page" :length="pages" @input="ambilApplet"></v-pagination>
                </div>
              </v-card-text>
            </v-card>
          </v-dialog>
        </v-flex>
        <v-flex xs1 v-show="editedItem.kode_pol != ''">
          <label for>kode Pol</label>
          <v-text-field
            v-model="editedItem.kode_pol"
            readonly
            :rules="[v => !!v || 'Harus di isi']"
          ></v-text-field>
        </v-flex>
        <v-flex xs3 v-show="editedItem.kode_pol != ''">
          <label for>Nama Pol</label>
          <v-text-field
            v-model="editedItem.nama_pol"
            readonly
            :rules="[v => !!v || 'Harus di isi']"
          ></v-text-field>
        </v-flex>
        <v-flex xs1 v-show="editedItem.kode_pol != ''">
          <label for>Isi</label>
          <v-text-field v-model="editedItem.isi" readonly :rules="[v => !!v || 'Harus di isi']"></v-text-field>
        </v-flex>
        <v-flex xs1 v-show="editedItem.kode_pol != ''">
          <label for>Job Applet</label>
          <v-text-field v-model="editedItem.old" readonly :rules="[v => !!v || 'Harus di isi']"></v-text-field>
        </v-flex>
        <v-flex xs1>
          <label for>Shift</label>
          <v-select
            :rules="[v => !!v || 'Harus di isi']"
            :items="[1,2]"
            height="8"
            v-model="editedItem.shift"
            solo
          ></v-select>
        </v-flex>        
        <v-flex xs1>
          <label for>Line</label>
          <v-text-field
            :rules="[v => !!v || 'Harus di isi']"
            type="number"
            min="1"
            max="99"
            solo
            v-model="editedItem.line"
          ></v-text-field>
        </v-flex>
        <v-flex xs1>
          <v-btn color="blue darken-1" flat :disabled="!valid" @click.stop="save">Save</v-btn>
        </v-flex>
      </v-layout>
    </v-form>
    <v-flex xs12>
      <v-toolbar color="secondary">
        <v-toolbar-title>List Job Sementara</v-toolbar-title>
      </v-toolbar>
      <v-data-table
        :headers="headersSementara"
        hide-actions
        :items="allLaporanSementara"
        class="elevation-1"
      >
        <template slot="items" slot-scope="props">
          <tr>
            <td>
              <v-icon small @click="confirmDelete(props.item, 'sementara')">delete</v-icon>
            </td>
            <td>{{ props.index + 1 }}</td>
            <td>{{ props.item.pol.tahun }}</td>
            <td>{{ props.item.pol.kode_pol }}</td>
            <td>{{ props.item.pol.nama_pol }}</td>
            <td>Preperso</td>
            <td>{{ props.item.created_at }}</td>
            <td>{{ props.item.line }}</td>
            <td>{{ props.item.name }}</td>
            <td>{{ props.item.id_preperso }}</td>
            <td>
              <v-icon small class="mr-2" @click="editLaporanSementara(props.item)">edit</v-icon>
            </td>
          </tr>
        </template>
        <template slot="no-data">
          <div class="text-xs-center">Tidak Ada Data</div>
        </template>
      </v-data-table>
      <br />
    </v-flex>
    <v-flex xs12>
      <v-toolbar color="blue lighten-2">
        <v-toolbar-title>List Job Tersimpan</v-toolbar-title>
        <v-spacer></v-spacer>
        <v-menu
          ref="menu"
          :close-on-content-click="false"
          v-model="menu"
          :nudge-right="40"
          :return-value.sync="tanggal"
          lazy
          transition="scale-transition"
          offset-y
          full-width
        >
          <v-text-field
            solo
            label="Tanggal Awal"
            readonly
            slot="activator"
            v-model="tanggal"
            prepend-icon="event"
          ></v-text-field>
          <v-date-picker
            format="dd/mm/yyyy"
            no-title
            @change="changeTanggal"
            v-model="tanggal"
            @input="$refs.menu.save(tanggal)"
          ></v-date-picker>
        </v-menu>
      </v-toolbar>
      <v-data-table
        :headers="headersSelesai"
        hide-actions
        :items="allLaporanSelesai"
        class="elevation-1"
      >
        <template slot="items" slot-scope="props">
          <tr>
            <td>
              <v-icon small @click="confirmDelete(props.item, 'selesai')">delete</v-icon>
            </td>
            <td>{{ props.index + 1 }}</td>
            <td>{{ props.item.pol.tahun }}</td>
            <td>{{ props.item.pol.kode_pol }}</td>
            <td>{{ props.item.pol.nama_pol }}</td>
            <td>Preperso</td>
            <td>{{ props.item.created_at }}</td>
            <td>{{ props.item.updated_at }}</td>
            <td>{{ props.item.line }}</td>
            <td>{{ props.item.shift }}</td>
            <td>{{ props.item.id_preperso }}</td>
            <td>{{ props.item.dead }}</td>

            <td>
              <v-icon small class="mr-2" @click="editLaporanSelesai(props.item)">edit</v-icon>
            </td>
          </tr>
        </template>
        <template slot="no-data">
          <div class="text-xs-center">Tidak Ada Data</div>
        </template>
      </v-data-table>
      <br />
    </v-flex>
    <v-dialog v-model="dialogUpdateLaporan" width="500px">
      <v-card>
        <v-card-title>
          <span class="headline">Update Laporan</span>
        </v-card-title>
        <v-card-text>
          <v-form ref="formUpdateLaporan" v-model="validUpdateLaporan" lazy-validation>
            <v-layout row wrap align-center>
              <v-flex xs12>
                <v-text-field
                  min="0"
                  label="Dead"
                  type="number"
                  v-model="editedLaporan.dead"
                  autofocus
                  :rules="[v => !!v || 'Harus di isi']"
                ></v-text-field>
              </v-flex>
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
            @click="update"
          >Simpan Laporan</v-btn>
          <!-- <v-btn color="blue darken-1" flat @click="close">Cancel</v-btn> -->
        </v-card-actions>
      </v-card>
    </v-dialog>
    <!-- {{ editedIndex }}
    {{ editedLaporan }}-->
  </v-container>
</template>

<script>
import axios from "axios";
export default {
  data() {
    return {
      cari: "",
      table_loading: false,
      snackbar: false,
      text: "",
      timeout: 5000,
      color: "",
      valid: true,
      dialogUpdateLaporan: false,
      validUpdateLaporan: true,
      allApplet: [],
      log: "",
      allsam: [],
      dialogPreperso: false,
      allLaporanSementara: [],
      allLaporanSelesai: [],
      dataSementara: [],
      tanggal: window.tanggal,
      menu: false,
      headersSementara: [
        { text: "", sortable: false },
        { text: "No", sortable: false },
        { text: "Tahun", sortable: false },
        { text: "Kode Pol", sortable: false },
        { text: "Nama Pol", sortable: false, width: "500" },
        { text: "Proses", sortable: false },
        { text: "Jam Mulai", sortable: false },
        { text: "Line", sortable: false },
        { text: "op_lama", sortable: false },
        { text: "Iner", sortable: false },
        { text: "Aksi", sortable: false }
      ],
      headersSelesai: [
        { text: "", sortable: false },
        { text: "No", sortable: false },
        { text: "Tahun", sortable: false },
        { text: "Kode Pol", sortable: false },
        { text: "Nama Pol", sortable: false, width: "500" },
        { text: "Proses", sortable: false },
        { text: "Jam Mulai", sortable: false },
        { text: "Jam Selesai", sortable: false },
        { text: "Line", sortable: false },
        { text: "Shift", sortable: false },
        { text: "Iner", sortable: false },
        { text: "Dead", sortable: false },
        { text: "Aksi", sortable: false }
      ],
      headersApplet: [
        { text: "Inner", sortable: false },
        { text: "Kode Pol", sortable: false },
        { text: "Tahun", sortable: false },
        { text: "Nama Pol", sortable: false, width: "300px" },
        { text: "Jmlorder", sortable: false },
        { text: "Tipe Chip", sortable: false },
        { text: "Aksi", sortable: false }
      ],
      editedIndex: -1,
      tipeLaporan: "",
      editedItem: {
        id_preperso: "",
        id_pol: "",
        kode_pol: "",
        iner: "",
        operator: "",
        line: "",
        shift: "",
        isi: "",
        dead: "",
        grup: "",
        status: "",
        old: "",
        created_at: "",
        updated_at: ""
      },
      editedLaporan: {
        id_preperso: "",
        id_pol: "",
        kode_pol: "",
        iner: "",
        operator: "",
        line: "",
        shift: "",
        isi: "",
        dead: "",
        grup: "",
        status: "",
        old: "",
        created_at: "",
        updated_at: ""
      },
      page: 1,
      defaultItem: {
        id_preperso: "",
        id_pol: "",
        kode_pol: "",
        iner: "",
        operator: "",
        line: "",
        shift: "",
        isi: "",
        dead: "",
        grup: "",
        status: "",
        old: "",
        created_at: "",
        updated_at: ""
      },
      dateAwal: window.tanggal,
      itemDelete: {},
      totalItems: 0,
      dialogDelete: false,
      breadcrumbs: [
        {
          text: "ktp",
          disabled: true,
          href: ""
        },
        {
          text: "Preperso",
          disabled: true,
          href: ""
        }
      ]
    };
  },
  watch: {
    dialogUpdateLaporan(val) {
      if (val == false) {
        this.close();
      }
    },
    dialogPreperso(val) {
      this.ambilApplet();
    }
  },
  computed: {
    pages() {
      return Math.ceil(this.totalItems / 5);
    }
  },
  created() {
    this.$store.commit("CHANGE_BREAD", { list: this.breadcrumbs });
    this.$store.commit("SET_JUDUL", "Preperso");
    this.editedItem.operator = this.$store.state.auth.user.id;
    this.ambilLaporanSelesai();
    this.ambilLaporanSementara();
  },
  methods: {
    ambilLaporanSementara() {
      axios
        .get("api/preperso/sementara/" + this.$store.state.auth.user.id)
        .then(response => {
          this.allLaporanSementara = response.data;
        });
    },
    ambilLaporanSelesai() {
      axios
        .get(
          "api/preperso/selesai/" +
            this.$store.state.auth.user.id +
            "/" +
            this.dateAwal
        )
        .then(response => {
          this.allLaporanSelesai = response.data;
        });
    },
    ambilApplet() {
      if (this.cari == "") {
        var cari = null;
      } else {
        var cari = this.cari;
      }
      axios
        .get("api/applet/" + cari + "/AppletDataPreperso?page=" + this.page)
        .then(response => {
          this.allApplet = response.data.data;
          this.totalItems = response.data.total;
          this.table_loading = false;
        });
    },
    waitInput() {
      this.page = 1;
      this.table_loading = true;
      if (this.timer) {
        clearTimeout(this.timer);
        this.timer = null;
      }
      this.timer = setTimeout(() => {
        this.ambilApplet();
      }, 600);
    },
    selectItem(item) {
      this.editedItem.id_pol = item.id_pol;
      this.editedItem.kode_pol = item.pol.kode_pol;
      this.editedItem.nama_pol = item.pol.nama_pol;
      this.editedItem.isi = item.pol.per_iner;
      this.editedItem.old = item.id_applet;
      this.dialogPreperso = false;
    },
    tampilAlert(alert, color) {
      this.text = alert;
      this.color = color;
      this.snackbar = true;
    },
    close() {
      //this.$refs.form.reset();
      this.defaultItem.shift = this.editedItem.shift;
      this.defaultItem.line = this.editedItem.line;
      this.defaultItem.id_sam = this.editedItem.id_sam;
      this.defaultItem.operator = this.editedItem.operator;
      this.editedItem = Object.assign({}, this.defaultItem);
      this.editedIndex = -1;
    },
    editLaporanSementara(item) {
      this.tipeLaporan = "sementara";
      //this.editedIndex = this.allLaporanSementara.indexOf(item);

      this.editedIndex = 4;
      // this.editedIndex = this.allLaporanSementara.findIndex(
      //   x => x.id_preperso == item.id_preperso
      // );
      this.editedLaporan = Object.assign({}, item);
      this.dialogUpdateLaporan = true;
    },
    editLaporanSelesai(item) {
      this.tipeLaporan = "selesai";
      this.editedIndex = this.allLaporanSelesai.indexOf(item);
      // this.editedIndex = this.allLaporanSelesai.findIndex(
      //   x => x.id_preperso == item.id_preperso
      // );
      this.dialogUpdateLaporan = true;
      this.editedLaporan = Object.assign({}, item);
    },
    confirmDelete(item, tipe) {
      this.tipeLaporan = tipe;
      this.itemDelete = item;
      this.dialogDelete = true;
    },
    deleteLaporan() {
      this.snackbar = false;
      axios
        .delete("api/preperso/" + this.itemDelete.id_preperso)
        .then(response => {
          this.dialogDelete = false;
          if (this.tipeLaporan == "sementara") {
            var index = this.allLaporanSementara.indexOf(this.itemDelete);
            this.allLaporanSementara.splice(index, 1);
          } else {
            var index = this.allLaporanSelesai.indexOf(this.itemDelete);
            this.allLaporanSelesai.splice(index, 1);
          }
          this.tampilAlert("Berhasil di hapus", "red");
        });
    },
    save() {
      if (this.$refs.form.validate()) {
        if (this.allLaporanSementara.length > 0) {
          this.tampilAlert("Selesaikan yang sedang berjalan", "info");
        } else {
          this.snackbar = false;
          axios
            .post("api/preperso", this.editedItem)
            .then(response => {
              this.allLaporanSementara.push(response.data);
              this.tampilAlert("Berhasil Disimpan", "success");
              this.close();
            })
            .catch(error => {
              console.log(error);
              this.close();
            });
        }
      }
    },
    changeTanggal(tanggal) {
      this.dateAwal = tanggal;
      this.ambilLaporanSelesai();
    },
    update() {
      if (this.$refs.formUpdateLaporan.validate()) {
        this.snackbar = false;
        var id_preperso = this.editedLaporan.id_preperso;

        this.log = this.$refs.log.files[0];
        let formData = new FormData();
        formData.append("id_preperso", this.editedLaporan.id_preperso);
        formData.append("dead", this.editedLaporan.dead);
        formData.append("_method", "PUT");
        formData.append("log", this.log);

        axios
          .post("api/preperso/" + this.editedLaporan.id_preperso, formData, {
            headers: {
              "Content-Type": "multipart/form-data"
            }
          })
          .then(response => {
            if (response.data == "error") {
              this.tampilAlert("Log tidak Sama", "red");
            } else {
              if (this.tipeLaporan == "sementara") {
                this.allLaporanSementara = [];
                var data = response.data.created_at.split(" ");
                if (this.dateAwal == data[0]) {
                  this.allLaporanSelesai.push(response.data);
                }
              } else {
                // console.log(this.editedIndex);
                Object.assign(
                  this.allLaporanSelesai[this.editedIndex],
                  response.data
                );
                //this.ambilLaporanSelesai();
              }
              this.tampilAlert("Job berhasil di Simpan", "blue");
              // this.close();
            }
            this.dialogUpdateLaporan = false;
          })
          .catch(error => {
            this.tampilAlert("Gagal 2", "red");
          });
      }
    }
  }
};
</script>