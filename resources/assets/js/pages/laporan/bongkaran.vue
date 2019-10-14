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
    <!-- {{ allPol }} -->
    <v-form ref="form" v-model="valid" lazy-validation>
      <v-layout row wrap>
        <v-flex xs2>
          <v-dialog v-model="dialogBongkaran" width="1000" origin="bottom center">
            <v-btn slot="activator" color="primary" dark>Bongkaran</v-btn>
            <v-card>
              <v-card-title class="headline grey lighten-2" primary-title>
                Cari Pol
                <v-spacer></v-spacer>
                <v-icon @click="dialogBongkaran = false">close</v-icon>
              </v-card-title>
              <v-card-text>
                <v-text-field v-model="cari" label="Cari" @keyup="waitInput"></v-text-field>
                <v-data-table
                  :headers="headersBongkaran"
                  :items="allBongkaran"
                  class="elevation-1"
                  :loading="table_loading"
                >
                  <template slot="items" slot-scope="props">
                    <tr>
                      <td></td>
                      <td>{{ props.index + 1 }}</td>
                      <td>{{ props.item.operator }}</td>
                      <td>{{ props.item.pol }}</td>
                      <td>{{ props.item.lot }}</td>
                      <td>{{ props.item.shift }}</td>
                      <td>{{ props.item.jumlah }}</td>
                      <td>{{ props.item.good }}</td>
                      <td>{{ props.item.reject }}</td>

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
                  <v-pagination v-model="page" :length="pages" @input="ambilBongkaran"></v-pagination>
                </div>
              </v-card-text>
            </v-card>
          </v-dialog>
        </v-flex>
        <v-flex xs1>
          <v-text-field
            disabled
            v-model="editedItem.pol"
            label="Pol"
            :rules="[v => !!v || 'Harus di isi']"
          ></v-text-field>
        </v-flex>
        <v-flex xs2>
          <v-text-field
            disabled
            v-model="editedItem.lot"
            label="lot"
            :rules="[v => !!v || 'Harus di isi']"
          ></v-text-field>
        </v-flex>
        <v-flex xs1>
          <v-text-field
            disabled
            v-model="editedItem.jumlah"
            label="jumlah"
            :rules="[v => !!v || 'Harus di isi']"
          ></v-text-field>
        </v-flex>
        <v-flex xs1>
          <v-text-field
            disabled
            v-model="editedItem.good"
            label="good"
            :rules="[v => !!v || 'Harus di isi']"
          ></v-text-field>
        </v-flex>
        <v-flex xs1>
          <v-text-field
            disabled
            v-model="editedItem.reject"
            label="reject"
            :rules="[v => !!v || 'Harus di isi']"
          ></v-text-field>
        </v-flex>
        <v-flex xs1>
          <v-select
            :rules="[v => !!v || 'Harus di isi']"
            :items="[1,2]"
            label="shift"
            v-model="editedItem.shift"
          ></v-select>
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
            <td>{{ props.item.pol }}</td>
            <td>{{ props.item.lot }}</td>
            <td>{{ props.item.shift }}</td>
            <td>{{ props.item.jumlah }}</td>
            <td>{{ props.item.good }}</td>
            <td>{{ props.item.reject }}</td>
            <td>
              <v-icon small class="mr-2" @click.stop="editLaporanSementara(props.item)">edit</v-icon>
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
        :headers="headersSementara"
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
            <td>{{ props.item.pol }}</td>
            <td>{{ props.item.lot }}</td>
            <td>{{ props.item.shift }}</td>
            <td>{{ props.item.jumlah }}</td>
            <td>{{ props.item.good }}</td>
            <td>{{ props.item.reject }}</td>
            <td>
              <v-icon small class="mr-2" @click.stop="editLaporanSelesai(props.item)">edit</v-icon>
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
                  label="jumlah"
                  type="number"
                  v-model="editedLaporan.jumlah"
                  autofocus
                  :rules="[v => !!v || 'Harus di isi']"
                ></v-text-field>
              </v-flex>
              <v-flex xs12>
                <v-text-field label="reject" type="number" v-model="editedLaporan.reject"></v-text-field>
              </v-flex>
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
        </v-card-actions>
      </v-card>
    </v-dialog>
  </v-container>
</template>

<script>
import axios from "axios";
export default {
  data() {
    return {
      cari: "",
      snackbar: false,
      text: "",
      timeout: 5000,
      color: "",
      valid: true,
      table_loading: false,
      dialogUpdateLaporan: false,
      validUpdateLaporan: true,
      allPol: [],
      log: "",
      dialogBongkaran: false,
      allsam: [],
      allBongkaran: [],
      dialogPol: false,
      allLaporanSementara: [],
      allLaporanSelesai: [],
      dataSementara: [],
      tanggal: window.tanggal,
      menu: false,
      headersSementara: [
        { text: "", sortable: false },
        { text: "No", sortable: false },
        { text: "pol", sortable: false },
        { text: "lot", sortable: false },
        { text: "shift", sortable: false },
        { text: "jumlah", sortable: false },
        { text: "good", sortable: false },
        { text: "reject", sortable: false },
        { text: "Aksi", sortable: false }
      ],
      headersBongkaran: [
        { text: "", sortable: false },
        { text: "No", sortable: false },
        { text: "operator", sortable: false },
        { text: "pol", sortable: false },
        { text: "lot", sortable: false },
        { text: "shift", sortable: false },
        { text: "jumlah", sortable: false },
        { text: "good", sortable: false },
        { text: "reject", sortable: false },
        { text: "Aksi", sortable: false }
      ],
      editedIndex: -1,
      tipeLaporan: "",
      editedItem: {
        id_bongkaran: "",
        pol: "",
        lot: "",
        shift: 1,
        jumlah: 0,
        good: 0,
        reject: 0,
        operator: "",
        status: "",
        created_at: "",
        updated_at: ""
      },
      editedLaporan: {
        id_bongkaran: "",
        pol: "",
        lot: "",
        shift: 1,
        jumlah: 0,
        good: 0,
        reject: 0,
        operator: "",
        status: "",
        created_at: "",
        updated_at: ""
      },
      defaultItem: {
        id_bongkaran: "",
        pol: "",
        lot: "",
        shift: 1,
        jumlah: 0,
        good: 0,
        reject: 0,
        operator: "",
        status: "",
        created_at: "",
        updated_at: ""
      },
      page: 1,
      totalBongkaran: 0,
      dateAwal: window.tanggal,
      itemDelete: {},
      dialogDelete: false,
      breadcrumbs: [
        {
          text: "home",
          disabled: true,
          href: ""
        },
        {
          text: "Bongkaran",
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

    dialogBongkaran(val) {
      this.allBongkaran = [];
      this.ambilBongkaran();
    }
  },
  computed: {
    pages() {
      return Math.ceil(this.totalBongkaran / 5);
    }
  },
  created() {
    this.$store.commit("CHANGE_BREAD", { list: this.breadcrumbs });
    this.$store.commit("SET_JUDUL", "Laporan bongkaran");
    this.ambilLaporanSementara();
    this.ambilLaporanSelesai();
    this.editedItem.operator = this.$store.state.auth.user.id;
  },
  methods: {
    ambilLaporanSementara() {
      axios
        .get("api/bongkaran/sementara/" + this.$store.state.auth.user.id)
        .then(response => {
          this.allLaporanSementara = response.data;
        });
    },
    ambilLaporanSelesai() {
      axios
        .get(
          "api/bongkaran/selesai/" +
            this.$store.state.auth.user.id +
            "/" +
            this.dateAwal
        )
        .then(response => {
          this.allLaporanSelesai = response.data;
        });
    },
    ambilBongkaran() {
      if (this.cari == "") {
        var cari = null;
      } else {
        var cari = this.cari;
      }
      axios
        .get("api/input_bongkaran/" + cari + "/databongkaran?page=" + this.page)
        .then(response => {
          this.allBongkaran = response.data.data;
          this.totalBongkaran = response.data.total;
          this.table_loading = false;
        });
    },
    changeTanggal(tanggal) {
      this.dateAwal = tanggal;
      this.ambilLaporanSelesai();
    },
    editLaporanSementara(item) {
      this.tipeLaporan = "sementara";
      this.editedIndex = this.allLaporanSementara.indexOf(item);
      this.editedLaporan = Object.assign({}, item);
      this.dialogUpdateLaporan = true;
    },
    editLaporanSelesai(item) {
      this.tipeLaporan = "selesai";
      this.editedIndex = this.allLaporanSelesai.indexOf(item);
      this.editedLaporan = Object.assign({}, item);
      this.dialogUpdateLaporan = true;
    },
    selectItem(item) {
      this.editedItem = item;
      this.editedItem.operator = this.$store.state.auth.user.id;
      this.dialogBongkaran = false;
    },
    update() {
      if (this.$refs.formUpdateLaporan.validate()) {
        if (this.editedLaporan.reject > this.editedLaporan.jumlah) {
          this.tampilAlert("Rejeck tidak boleh lebih besar", "red");
        } else {
          this.snackbar = false;
          var index = this.editedIndex;
          axios
            .put(
              "api/bongkaran/" + this.editedLaporan.id_bongkaran,
              this.editedLaporan
            )
            .then(response => {
              if (this.tipeLaporan == "sementara") {
                this.allLaporanSementara = [];
                this.allLaporanSelesai.push(response.data);
              } else {
                Object.assign(this.allLaporanSelesai[index], response.data);
              }
              this.tampilAlert("Job berhasil di Simpan", "blue");
              this.dialogUpdateLaporan = false;
            })
            .catch(error => {
              this.tampilAlert("Gagal 2", "red");
            });
        }
      }
    },
    save() {
      if (this.$refs.form.validate()) {
        if (this.allLaporanSementara.length > 0) {
          this.tampilAlert("Selesaikan yang sedang berjalan", "info");
        } else {
          this.snackbar = false;
          axios
            .post("api/bongkaran", this.editedItem)
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
    tampilAlert(alert, color) {
      this.text = alert;
      this.color = color;
      this.snackbar = true;
    },
    close() {
      this.defaultItem.shift = this.editedItem.shift;
      this.defaultItem.operator = this.editedItem.operator;
      this.editedItem = Object.assign({}, this.defaultItem);
      this.editedIndex = -1;
    },
    waitInput() {
      this.page = 1;
      this.table_loading = true;
      if (this.timer) {
        clearTimeout(this.timer);
        this.timer = null;
      }
      this.timer = setTimeout(() => {
        this.ambilBongkaran();
      }, 600);
    },
    confirmDelete(item, tipe) {
      this.tipeLaporan = tipe;
      this.itemDelete = item;
      this.dialogDelete = true;
    },
    deleteLaporan() {
      this.snackbar = false;

      axios
        .delete("api/bongkaran/" + this.itemDelete.id_bongkaran)
        .then(response => {
          this.dialogDelete = false;
          if (this.tipeLaporan == "sementara") {
            const index = this.allLaporanSementara.indexOf(this.itemDelete);
            this.allLaporanSementara.splice(index, 1);
          } else {
            const index = this.allLaporanSelesai.indexOf(this.itemDelete);
            this.allLaporanSelesai.splice(index, 1);
          }
          this.tampilAlert("Berhasil di hapus", "red");
        });
    }
  }
};
</script>