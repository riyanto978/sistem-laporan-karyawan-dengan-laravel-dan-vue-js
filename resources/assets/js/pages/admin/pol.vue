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
        <v-dialog v-model="dialogDelete" max-width="290">
          <v-card>
            <v-card-title class="orange"></v-card-title>
            <v-card-text>
              <div class="text-xs-center">Apakah Anda ingin menghapus??</div>
            </v-card-text>
            <v-card-actions>
              <v-btn color="blue darken-1" flat @click="deleteItem">Delete</v-btn>
              <v-spacer></v-spacer>
              <v-btn color="blue darken-1" flat @click="dialogDelete = false">Cancel</v-btn>
            </v-card-actions>
          </v-card>
        </v-dialog>
        <v-dialog v-model="loader" hide-overlay persistent width="400">
          <v-card color="primary" dark>
            <v-card-text>
              Sedang Load Data QC
              <v-progress-linear indeterminate color="white" class="mb-0"></v-progress-linear>
            </v-card-text>
          </v-card>
        </v-dialog>
        <!-- {{ allproses }} -->
        <br />
        <v-flex xs12 sm12 lg12>
          <v-toolbar flat class="grey lighten-5">
            <v-flex xs2 sm2 md2 left>
              <v-select :items="combo_tahun" @change="ambil_pol" v-model="tahun" label="Tahun" ></v-select>
            </v-flex>
            <v-flex xs2 sm2 md2 left>
              <v-select
                
                :items="combo_manual"
                item-text="text"
                item-value="data"
                @change="ambil_pol"
                v-model="tipe"
                label="Type"
              ></v-select>
            </v-flex>
            <v-flex xs2>
              <v-select
                
                :items="combo_status"
                item-text="text"
                item-value="data"
                @change="ambil_pol"
                v-model="status"
                label="status"
              ></v-select>
            </v-flex>
            <v-text-field @keyup="waitInput" v-model="cari" placeholder="Cari POL / Project" solo></v-text-field>
            <v-btn
              @click="ambilDataPol"
              color="blue"
              dark
              class="mb-2"
              v-if="$store.getters.authUser.level == 'admin'"
            >Ambil Data QC</v-btn>
            <v-dialog v-model="dialog" max-width="800px">
              <!-- <v-btn slot="activator" color="primary" dark class="mb-2">New Proses</v-btn> -->
              <v-card>
                <v-form ref="form" v-model="valid" lazy-validation>
                  <v-card-title>
                    <span class="headline">{{ formTitle }}</span>
                    <v-spacer></v-spacer>
                    <a
                      class="btn yellow"
                      :href="`http://qc3/qc/acuan/lampiran/${editedItem.lampiran}`"
                      target="_blank"
                    >POL</a>
                  </v-card-title>
                  <v-card-text>
                    <v-container grid-list-md>
                      <v-layout wrap align-center>
                        <v-flex xs6>
                          <v-text-field
                            type="text"
                            v-model="editedItem.nama_pol"
                            :rules="[v => !!v || 'Harus di isi']"
                            label="Nama Project"
                          ></v-text-field>
                        </v-flex>
                        <v-flex xs6>
                          <v-text-field
                            type="text"
                            v-model="editedItem.kode_pol"
                            :rules="[v => !!v || 'Harus di isi']"
                            label="Kode POL"
                          ></v-text-field>
                        </v-flex>
                        <v-flex xs6>
                          <v-text-field
                            mask="####"
                            v-model="editedItem.tahun"
                            :rules="[v => !!v || 'Harus di isi']"
                            label="Tahun"
                          ></v-text-field>
                        </v-flex>
                        <v-flex xs6>
                          <v-select
                            :items="combo_manual"
                            item-text="text"
                            item-value="data"
                            v-model="editedItem.tipe"
                            label="Type"
                            @change="ubahProses"
                          ></v-select>
                        </v-flex>
                        <v-flex xs6>
                          <v-select
                            :items="combo_status"
                            item-text="text"
                            item-value="data"
                            v-model="editedItem.status"
                            label="status"                            
                          ></v-select>
                        </v-flex>
                        <v-flex xs6>
                          <v-select
                            :items="prosesKtp"
                            item-text="text"
                            item-value="data"
                            v-model="editedItem.proses"
                            v-if="editedItem.tipe == 'ktp'"
                          ></v-select>
                          <v-combobox
                            :readonly="readonlyProses"
                            v-if="editedItem.tipe == 'reguler' || editedItem.tipe =='counter' || editedItem.tipe =='memo' "
                            v-model="editedItem.proses"
                            item-text="nama_proses"
                            item-value="id_proses"
                            :items="filterProses"
                            label="Proses"
                            chips
                            prepend-icon="filter_list"
                            solo
                            multiple
                          >
                            <template slot="selection" slot-scope="data">
                              <v-chip :selected="data.selected" close @input="remove(data.item)">
                                <span>{{data.index+1 }}.</span>
                                <strong>{{ data.item.nama_proses }}</strong>
                              </v-chip>
                            </template>
                          </v-combobox>
                        </v-flex>
                        <v-flex xs6>
                          <v-text-field
                            type="text"
                            v-model="editedItem.jumlah_order"
                            mask="##########"
                            label="Jumlah Order"
                            reverse
                            :rules="[v => v != 0 || 'Harus di isi']"
                          ></v-text-field>
                        </v-flex>
                        <v-flex xs6>
                          <v-text-field
                            type="number"
                            min="0"
                            v-model="editedItem.per_iner"
                            required
                            label="Qty / Inner"
                            :rules="[v => !!v || 'Harus di isi']"
                          ></v-text-field>
                        </v-flex>
                        <v-flex xs6 v-if="editedItem.tipe =='ktp'">
                          <v-layout row wrap>
                            <v-flex xs6>
                              <v-select
                                :items="allperiode"
                                item-text="nama_periode"
                                item-value="id_periode"
                                v-model="editedItem.id_periode"
                                :rules="[v => !!v || 'Harus di isi']"
                                label="Periode"
                              ></v-select>
                            </v-flex>
                            <v-flex xs6>
                              <v-select
                                label="tipe chip"
                                v-model="editedItem.tipe_chip"
                                :rules="[v => !!v || 'Harus di isi']"
                                :items="['A','B']"
                              ></v-select>
                            </v-flex>
                          </v-layout>
                        </v-flex>
                      </v-layout>
                    </v-container>
                  </v-card-text>
                  <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn class="blue lighten-1" :disabled="!valid" @click="save">Save</v-btn>
                    <v-btn class="red lighten-1" @click.native="close">Cancel</v-btn>
                  </v-card-actions>
                </v-form>
              </v-card>
            </v-dialog>
          </v-toolbar>
          <br />
          <v-data-table
            :headers="headers"
            hide-actions
            :items="allpol"
            :rows-per-page-items="row"
            class="elevation-1"
            id="table1"
            :loading="table_loading"
          >
            <template slot="items" slot-scope="props">
              <tr>
                <td>{{ (page-1)*10+ props.index+1 }}</td>
                <td>{{ props.item.kode_pol }}</td>
                <td>{{ props.item.nama_pol }}</td>
                <td>{{ props.item.tahun }}</td>
                <td>{{ props.item.jumlah_order }}</td>
                <td>{{ props.item.per_iner }}</td>
                <td>
                  <v-btn small round @click="LihatPencapaian(props.item)">Pencapaian</v-btn>
                </td>
                <td>
                  <a
                    :href="`http://qc3/qc/acuan/lampiran/${props.item.lampiran}`"
                    target="_blank"
                  >Link</a>
                </td>
                <td>
                  <v-container class="pa-0">
                    <v-btn-toggle>
                      <v-btn
                        flat
                        @click="confirmDelete(props.item)"
                        v-if="$store.getters.authUser.level == 'admin'"
                      >
                        <v-icon color="primary" small>delete</v-icon>
                      </v-btn>
                      <router-link
                        v-if="props.item.tipe == 'reguler'"
                        :to="`/pol/${ props.item.id_pol }`"
                        class="white--text"
                      >
                        <v-btn flat>
                          <v-icon color="primary">business_center</v-icon>
                        </v-btn>
                      </router-link>
                      <router-link
                        v-if="props.item.tipe == 'counter'"
                        :to="`/counter/${ props.item.id_pol }`"
                        class="white--text"
                      >
                        <v-btn flat>
                          <v-icon color="primary">business_center</v-icon>
                        </v-btn>
                      </router-link>
                      <v-btn
                        flat
                        @click="editItem(props.item)"
                        v-if="$store.getters.authUser.level == 'admin'"
                      >
                        <v-icon color="primary" small class="mr-2">edit</v-icon>
                      </v-btn>
                      <v-btn
                        flat
                        @click="ubah_urgent(props.item)"
                        v-if="$store.getters.authUser.level == 'admin'"
                      >
                        <v-icon
                          flat
                          small
                          class="mr-2"
                          :color="props.item.urgent == 1?'yellow':''"
                        >stars</v-icon>
                      </v-btn>
                    </v-btn-toggle>
                  </v-container>
                </td>
                <td>{{ props.item.tipe }}</td>
              </tr>
            </template>
            <template slot="no-data">
              <div class="text-xs-center">Tidak Ada Data</div>
            </template>
          </v-data-table>
          <div class="text-xs-center pt-2">
            <v-pagination v-model="page" :length="pages" @input="ambil_pol"></v-pagination>
          </div>
        </v-flex>

        <v-dialog v-model="dialogPencapaian" width="1400">
          <v-card>
            <v-card-title class="blue lighten-3">Pencapaian {{ pol.nama_pol }} | {{ pol.kode_pol }}</v-card-title>
            <v-card-text>
              <v-data-table
                :headers="headersPencapaian"
                hide-actions
                :items="allPencapaian"
                class="elevation-1"
              >
                <template slot="items" slot-scope="props">
                  <tr>
                    <td>{{ props.index+1 }}</td>
                    <td>{{ props.item.kode_pol }}</td>
                    <td>{{ props.item.nama_pol }}</td>
                    <td>{{ props.item.nama_proses }}</td>
                    <td>{{ props.item.berjalan }}</td>
                    <td>{{ props.item.selesai }}</td>
                  </tr>
                </template>
                <template slot="no-data">
                  <div class="text-xs-center">Tidak Ada Data</div>
                </template>
              </v-data-table>
            </v-card-text>
          </v-card>
        </v-dialog>
      </v-layout>
    </v-fade-transition>
  </v-container>
</template>

<script>
import axios from "axios";
export default {
  middleware: "authLogin",
  data() {
    return {
      show: false,
      valid: true,
      snackbar: false,
      text: "",
      timeout: 5000,
      color: "",
      loader: false,
      page: 1,
      dialogPencapaian: false,
      status: 0,
      combo_status: [
        {
          data: 0,
          text: "Berjalan"
        },
        {
          data: 1,
          text: "Finish"
        }
      ],
      combo_tahun: [
        "2017",
        "2018",
        "2019",
        "2020",
        "2021",
        "2022",
        "2023",
        "2024",
        "2025",
        "2026"
      ],
      tahun: "2019",
      chips: [],
      table_loading: false,
      dialog: false,
      cari: "",
      allpol: [],
      pol: {},
      allPencapaian: [],
      allproses: [],
      allperiode: [],
      loader: false,
      combo_manual: [
        {
          data: "reguler",
          text: "Reguler"
        },
        {
          data: "counter",
          text: "Counter&Wrapping"
        },
        {
          data: "ktp",
          text: "ktp"
        },
        {
          data: "memo",
          text: "memo"
        },
        {
          data: "none",
          text: "all"
        }
      ],
      prosesKtp: [
        {
          data: 0,
          text: "Selesai"
        },
        {
          data: 1,
          text: "Applet dahulu"
        },
        {
          data: 2,
          text: "Preperso dahulu"
        },
        {
          data: 3,
          text: "Record dahulu"
        }
      ],
      tipe: "reguler",
      row: [25, { text: "$vuetify.dataIterator.rowsPerPageAll", value: -1 }],
      alert: false,
      readonlyProses: false,
      headers: [
        { text: "No", value: "No", sortable: false },
        { text: "Kode POL", value: "kode_pol", sortable: true },
        {
          text: "Nama POL",
          align: "left",
          sortable: true,
          value: "nama_pol",
          sortable: true,
          width: "20%"
        },
        { text: "Tahun", value: "tahun", sortable: true },
        { text: "Jumlah Order", value: "jumlah_order", sortable: true },
        { text: "Qty / Inner", value: "per_iner", sortable: true },
        { text: "Pencapaian", value: "pencapaian", sortable: true },
        { text: "Acuan", value: "link", sortable: true },
        { text: "", value: "", sortable: false, width: "5%" },
        { text: "Type", value: "tipe", sortable: true }
      ],
      headersPencapaian: [
        { text: "No", sortable: false },
        { text: "Kode Pol", sortable: false },
        { text: "Project", sortable: false, width: "500" },
        { text: "Proses", sortable: false },
        { text: "Berjalan", sortable: false },
        { text: "Selesai", sortable: false }
      ],
      itemDelete: {},
      ipqc: "10.10.53.101",
      dialogDelete: false,
      editedIndex: -1,
      editedItem: {
        id_pol: "",
        kode_pol: "",
        tahun: "",
        nama_pol: "",
        jumlah_order: "",
        per_iner: "",
        tipe: "none",
        lampiran: "",
        proses: "",
        id_periode: "",
        tipe_chip: "",
        status : '',
        created_at: "",
        updated_at: ""
      },
      defaultItem: {
        id_pol: "",
        kode_pol: "",
        tahun: "",
        nama_pol: "",
        jumlah_order: "",
        per_iner: "",
        tipe: "none",
        lampiran: "",
        proses: "",
        id_periode: "",
        tipe_chip: "",
        status : '',
        created_at: "",
        updated_at: ""
      },
      totalItems: 0,
      breadcrumbs: [
        {
          text: "Home",
          disabled: false,
          href: "/"
        },
        {
          text: "POL",
          disabled: false,
          href: "/POL"
        }
      ]
    };
  },
  computed: {
    pages() {
      return Math.ceil(this.totalItems / 10);
    },
    formTitle() {
      return this.editedIndex === -1 ? "New Item" : "Edit POL";
    },
    filterProses() {
      return this.allproses.filter(pros => {
        return pros.nama_proses != "counter";
      });
    }
    // filterPol() {
    //   return this.allpol.filter(pros => {
    //     return (
    //       pros.nama_pol.toLowerCase().match(this.cari) ||
    //       pros.kode_pol.match(this.cari)
    //     );
    //   });
    // }
  },

  watch: {
    dialog(val) {
      val || this.close();
    }
  },

  created() {
    setTimeout(() => {
      this.show = true;
    }, 100);
    this.ambil_pol();
    this.ambil_proses();
    this.ambil_periode();
    this.$store.commit("CHANGE_BREAD", { list: this.breadcrumbs });
    this.$store.commit("SET_JUDUL", "POL");
  },
  methods: {
    tampilAlert(alert, color) {
      this.text = alert;
      this.color = color;
      this.snackbar = true;
    },
    editItem(item) {
      this.editedIndex = this.allpol.indexOf(item);
      this.editedItem = Object.assign({}, item);
      this.editedItem.proses = JSON.parse(item.proses);
      this.dialog = true;
    },
    ubahProses() {
      if (this.editedItem.tipe == "reguler") {
        this.editedItem.proses = null;
        this.readonlyProses = false;
        this.editedItem.per_iner = 250;
      } else if (this.editedItem.tipe == "counter") {
        var proses = this.allproses.filter(x => x.nama_proses == "counter");
        this.editedItem.proses = proses;
        this.readonlyProses = true;
        this.editedItem.per_iner = 250;
      } else if (this.editedItem.tipe == "ktp") {
        this.editedItem.proses = 1;
        this.editedItem.per_iner = 500;
      }
    },
    confirmDelete(item) {
      this.itemDelete = item;
      this.dialogDelete = true;
    },
    deleteItem() {
      this.snackbar = false;
      const index = this.allpol.indexOf(this.itemDelete);
      var self = this;
      axios
        .delete("api/pol/" + this.itemDelete.id_pol)
        .then(function(response) {
          self.allpol.splice(index, 1);
          self.tampilAlert("Berhasil Dihapus", "red");
          self.dialogDelete = false;
        });
    },
    ambilDataPol() {
      this.loader = true;
      axios
        .get("api/qc/" + this.ipqc, {
          headers: {
            Authorization: "Bearer " + this.$store.state.auth.token
          }
        })
        .then(response => {
          this.ambil_pol();
          this.loader = false;
          this.tampilAlert(`${response.data.total} data baru`, "blue");
        })
        .catch(error => {
          console.log(error);
          this.loader = false;
        });
    },
    ambil_pol() {
      this.table_loading = true;
      if (this.cari == "") {
        var cari = null;
      } else {
        var cari = this.cari;
      }
      axios
        .get(
          "api/ambilpol/" +
            this.tahun +
            "/" +
            this.tipe +
            "/" +
            cari +
            "/" +
            this.status +
            "?page=" +
            this.page
        )
        .then(response => {
          this.allpol = response.data.data;
          this.totalItems = response.data.total;
          this.table_loading = false;
        })
        .catch(function(error) {
          this.table_loading = false;
        });
    },
    ambil_proses() {
      axios
        .get("api/ambilproses")
        .then(response => {
          this.allproses = response.data;
        })
        .catch(function(error) {});
    },
    ambil_periode() {
      axios
        .get("api/periode")
        .then(response => {
          this.allperiode = response.data;
        })
        .catch(function(error) {});
    },
    LihatPencapaian(item) {
      this.pol = item;

      axios
        .get("api/ambilpencapaian/" + item.tipe + "/" + item.id_pol)
        .then(response => {
          this.allPencapaian = response.data;
          this.dialogPencapaian = true;
        });
    },
    close() {
      this.$refs.form.reset();
      this.dialog = false;
      setTimeout(() => {
        this.editedItem = Object.assign({}, this.defaultItem);
        this.editedIndex = -1;
      }, 300);
    },
    getTime(time) {
      var d = new Date(time);
      return d.getHours() + ":" + d.getMinutes() + ":" + d.getSeconds();
    },

    waitInput() {
      this.page = 1;
      this.table_loading = true;
      if (this.timer) {
        clearTimeout(this.timer);
        this.timer = null;
      }
      this.timer = setTimeout(() => {
        this.ambil_pol();
      }, 600);
    },
    ubah_urgent(item) {
      var index = this.allpol.indexOf(item);
      axios.put("api/pol/urgent/" + item.id_pol).then(response => {
        Object.assign(this.allpol[index], response.data);
        //this.ambil_pol();
        this.tampilAlert("Berhasil Diupdate", "blue");
        this.close();
      });
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
    waktu() {
      var d = new Date(new Date() * 1 - 1000 * 3600 * 7);
      return (
        d.toLocaleDateString("en-gb") + " " + d.toLocaleTimeString("en-gb")
      );
    },
    save() {
      if (this.$refs.form.validate()) {
        if (this.editedIndex > -1) {
          this.snackbar = false;
          var id_pol = this.editedItem.id_pol;
          var index = this.editedIndex;
          axios
            .put("api/pol/" + id_pol, {
              id_pol: this.editedItem.id_pol,
              kode_pol: this.editedItem.kode_pol,
              id_pol: this.editedItem.tahun,
              nama_pol: this.editedItem.nama_pol,
              jumlah_order: this.editedItem.jumlah_order,
              tahun: this.editedItem.tahun,
              per_iner: this.editedItem.per_iner,
              tipe: this.editedItem.tipe,
              proses: JSON.stringify(this.editedItem.proses),
              id_periode: this.editedItem.id_periode,
              tipe_chip: this.editedItem.tipe_chip,
              status : this.editedItem.status
            })
            .then(response => {
              //Object.assign(this.allpol[index], response.data);
              this.ambil_pol();
              this.tampilAlert("Berhasil Diupdate", "blue");
              this.close();
            });
        }
      }
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



