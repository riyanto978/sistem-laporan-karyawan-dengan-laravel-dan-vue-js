<template>
  <v-container align-center fluid>
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
        <v-dialog v-model="dialogDeleteCounter" max-width="290">
          <v-card>
            <v-card-title class="orange"></v-card-title>
            <v-card-text>
              <div class="text-xs-center">Apakah Anda ingin menghapus??</div>
            </v-card-text>
            <v-card-actions>
              <v-btn color="blue darken-1" flat @click="deleteCounter">Delete</v-btn>
              <v-spacer></v-spacer>
              <v-btn color="blue darken-1" flat @click="dialogDeleteCounter = false">Cancel</v-btn>
            </v-card-actions>
          </v-card>
        </v-dialog>
        <v-flex xs12>
          <v-toolbar color="white" flat>
            <v-text-field
              append-icon="search"
              v-model="cari"
              v-if="step == 1"
              placeholder="POL / Project"
              @keyup="waitInput"
            ></v-text-field>
            <v-select
              v-if="step == 1"
              :items="combo_status"
              item-text="text"
              item-value="data"
              @change="ambilPol"
              v-model="status"
              label="Type"
            ></v-select>
            <v-layout align-end justify-center row>
              <v-btn v-if="step == 2" @click="step = 1" color="orange lighten-3">
                <v-icon>arrow_back</v-icon>Daftar Pol
              </v-btn>
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
              <v-stepper-step step="2">Laporan Counter</v-stepper-step>
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
                <v-form
                  ref="formCounter"
                  @submit.prevent="saveCounter"
                  v-model="validCounter"
                  lazy-validation
                >
                  <v-container>
                    <v-layout row wrap>
                      <v-flex xs12>{{ formTitle }}</v-flex>
                      <v-flex class="xs12 sm6 md2">
                        <v-text-field
                          label="Nomor Lot"
                          ref="nomor_lot"
                          :rules="[v => !!v || 'Harus Diisi']"
                          v-model="editedItemCounter.lot"
                        ></v-text-field>
                      </v-flex>
                      <v-flex class="xs12 sm6 md2">
                        <v-text-field
                          type="number"
                          label="Jumlah Lot"
                          :rules="[v => !!v || 'Harus Diisi']"
                          v-model="editedItemCounter.jumlah"
                        ></v-text-field>
                      </v-flex>
                      <v-flex class="xs12 sm6 md3">
                        <v-text-field
                          label="Operator"
                          :rules="[v => !!v || 'Harus Diisi']"
                          v-model="editedItemCounter.operator"
                        ></v-text-field>
                      </v-flex>
                      <v-flex class="xs12 sm6 md1">
                        <v-select :items="[1,2]" label="Shift" v-model="editedItemCounter.shift"></v-select>
                      </v-flex>
                      <v-flex class="xs12 sm6 md2">
                        <v-text-field
                          type="number"
                          label="Reject Plastic"
                          v-model="editedItemCounter.reject_plastic"
                        ></v-text-field>
                      </v-flex>
                      <v-flex class="xs12 sm6 md2">
                        <v-btn
                          type="submit"
                          color="primary"
                          class="black--text"
                          :disabled="!validCounter"
                        >{{ formTitle }}</v-btn>
                        <v-btn
                          color="warning"
                          small
                          fab
                          v-if="editedIndexCounter > -1"
                          @click="closeCounter"
                        >X</v-btn>
                      </v-flex>
                      <v-flex xs12>
                        <v-data-table
                          :headers="headersCounter"
                          :items="allCounter"
                          :loading="loading"
                          hide-actions
                          class="elevation-1"
                        >
                          <template slot="items" slot-scope="props">
                            <td>{{ (pageCounter-1)*5+ props.index+1 }}</td>
                            <td>{{ props.item.operator }}</td>
                            <td>{{ props.item.updated_at }}</td>
                            <td>{{ props.item.reject_plastic }}</td>
                            <td>{{ props.item.shift }}</td>
                            <td>{{ props.item.lot }}</td>
                            <td>{{ props.item.jumlah }}</td>
                            <td>{{ props.item.iner_awal }} - {{ props.item.iner_akhir }}</td>
                            <td>{{ props.item.wip }}</td>
                            <td>
                              <v-icon small class="mr-2" @click="editItemCounter(props.item)">edit</v-icon>
                              <v-icon small @click="confirmDeleteCounter(props.item)">delete</v-icon>
                            </td>
                          </template>
                          <template slot="no-data">
                            <div class="text-xs-center">Tidak Ada Data</div>
                          </template>
                        </v-data-table>
                        <div class="text-xs-center pt-2">
                          <v-pagination
                            v-model="pageCounter"
                            :length="pagesCounter"
                            @input="ambilCounter"
                          ></v-pagination>
                        </div>
                      </v-flex>
                      <v-flex xs12>Total : {{ totalCounter }}</v-flex>
                    </v-layout>
                  </v-container>
                </v-form>
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
      show: false,
      snackbar: false,
      text: "",
      allCounter: [],
      timeout: 4000,
      color: "",
      pol: {},
      allPol: [],
      totalItems: 0,
      totalItemsCounter: 0,
      totalCounter: 0,
      loading: false,
      step: 1,
      page: 1,
      pageCounter: 1,
      validCounter: true,
      itemDeleteCounter: {},
      headersPol: [
        { text: "No", value: "No", sortable: false },
        { text: "Tahun", value: "tahun", sortable: false },
        { text: "Kode Pol", value: "kode_pol", sortable: false },
        { text: "Project", value: "nama_pol", sortable: false },
        { text: "Jumlah Order", value: "jumlah_order", sortable: false },
        { text: "Total inner", value: "total_iner", sortable: false },
        { text: "Lampiran", value: "lampiran", sortable: false },
        { text: "Actions", value: "name", sortable: false }
      ],
      headersCounter: [
        { text: "No", value: "No", sortable: false },
        { text: "Operator", value: "operator", sortable: false },
        { text: "Jam", value: "jam", sortable: false },
        { text: "Reject_plastic", value: "reject_plastic", sortable: false },
        { text: "Shift", value: "shift", sortable: false },
        { text: "Lot", value: "lot", sortable: false },
        { text: "Jumlah", value: "jumlah", sortable: false },
        { text: "Inner", value: "iner", sortable: false },
        { text: "WIP", value: "wip", sortable: false },
        { text: "Actions", value: "name", sortable: false }
      ],
      cari: "",
      dialogDeleteCounter: false,
      editedIndexCounter: -1,
      editedItemCounter: {
        id_counter: "",
        id_pol: "",
        lot: "",
        jumlah: "",
        operator: "",
        shift: 1,
        iner_awal: "",
        iner_akhir: "",
        reject_plastic: 0,
        created_at: "",
        updated_at: ""
      },
      defaultItemCounter: {
        id_counter: "",
        id_pol: "",
        lot: "",
        jumlah: "",
        operator: "",
        shift: 1,
        iner_awal: "",
        iner_akhir: "",
        reject_plastic: 0,
        created_at: "",
        updated_at: ""
      },
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
      breadcrumbs: [
        {
          text: "Home",
          disabled: false,
          href: ""
        },
        {
          text: "Counter & Wrapping",
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
    pagesCounter() {
      return Math.ceil(this.totalItemsCounter / 5);
    },
    formTitle() {
      return this.editedIndexCounter === -1 ? "Tambah Lot" : "Edit Lot";
    }
  },
  created() {
    //window.config.appName = 'Counter';
    setTimeout(() => {
      this.show = true;
    }, 100);
    this.$store.commit("CHANGE_BREAD", { list: this.breadcrumbs });
    this.$store.commit("SET_JUDUL", "Counter & Wrapping");
    this.ambilPol();
    this.editedItemCounter.operator = this.$store.state.auth.user.name;
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
        .get(
          "api/data/pol/counter/" +
            cari +
            "/" +
            this.status +
            "?page=" +
            this.page
        )
        .then(response => {
          this.allPol = response.data.data;
          this.totalItems = response.data.total;
          this.loading = false;
        });
    },
    ambilCounter() {
      this.loading = true;
      axios
        .get(
          "api/counter/data/" + this.pol.id_pol + "?page=" + this.pageCounter
        )
        .then(response => {
          this.allCounter = response.data.data.data;
          this.totalItemsCounter = response.data.data.total;
          this.totalCounter = response.data.total;
          this.loading = false;
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

    pilihPol(item) {
      this.editedIndexCounter = -1;
      this.step = 2;
      this.pol = item;
      this.ambilCounter();
    },
    saveCounter() {
      if (this.$refs.formCounter.validate()) {
        if (this.editedIndexCounter > -1) {
          this.snackbar = false;
          this.editedItemCounter.per_iner = this.pol.per_iner;
          this.editedItemCounter.id_pol = this.pol.id_pol;
          axios
            .put(
              "api/counter/" + this.editedItemCounter.id_counter,
              this.editedItemCounter
            )
            .then(response => {
              this.ambilCounter();
              this.tampilAlert("Berhasil di update", "blue");
              this.closeCounter();
            })
            .catch(response => {
              this.tampilAlert("Gagal di update", "red");
              this.closeCounter();
            });
        } else {
          this.snackbar = false;
          this.editedItemCounter.per_iner = this.pol.per_iner;
          this.editedItemCounter.id_pol = this.pol.id_pol;
          axios
            .post("api/counter", this.editedItemCounter)
            .then(response => {
              //this.allCounter.push(response.data);
              this.totalItemsCounter += 1;
              this.pageCounter = this.pagesCounter;
              this.ambilCounter();
              this.tampilAlert("Berhasil di simpan", "success");
              this.closeCounter();
            })
            .catch(error => {
              console.log(error);
              this.closeCounter();
            });
        }
      }
    },
    editItemCounter(item) {
      this.editedIndexCounter = this.allCounter.indexOf(item);
      this.editedItemCounter = Object.assign({}, item);
    },
    deleteCounter() {
      this.snackbar = false;
      axios
        .delete("api/counter/" + this.itemDeleteCounter.id_counter)
        .then(response => {
          this.dialogDeleteCounter = false;
          this.totalItemsCounter -= 1;
          this.pageCounter = this.pagesCounter;
          this.ambilCounter();
          this.tampilAlert("Berhasil di hapus", "red");
        });
    },
    closeCounter() {
      this.defaultItemCounter.operator = this.editedItemCounter.operator;
      this.editedItemCounter = Object.assign({}, this.defaultItemCounter);
      this.editedIndexCounter = -1;
      this.$refs.nomor_lot.focus();
    },
    confirmDeleteCounter(item) {
      this.itemDeleteCounter = item;
      this.dialogDeleteCounter = true;
    },
    tampilAlert(alert, color) {
      this.text = alert;
      this.color = color;
      this.snackbar = true;
    }
  }
};
</script>