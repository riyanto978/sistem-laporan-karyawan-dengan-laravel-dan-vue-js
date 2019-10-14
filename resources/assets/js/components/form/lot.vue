<template>
  <v-container >
    <v-layout row wrap>
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
      <v-dialog v-model="dialogDeleteLot" max-width="290">
        <v-card>
          <v-card-title class="orange"></v-card-title>
          <v-card-text>
            <div class="text-xs-center">Apakah Anda ingin menghapus??</div>
          </v-card-text>
          <v-card-actions>
            <v-btn color="blue darken-1" flat @click="deleteLot">Delete</v-btn>
            <v-spacer></v-spacer>
            <v-btn color="blue darken-1" flat @click="dialogDeleteLot = false">Cancel</v-btn>
          </v-card-actions>
        </v-card>
      </v-dialog>

      <v-dialog
        :value="value"
        @input="$emit('input')"
        fullscreen                
      >
        <v-card tile>
          <v-toolbar card dark color="primary">
            <v-btn icon dark @click.native="$emit('input')">
              <v-icon>close</v-icon>
            </v-btn>
            <v-toolbar-title>Input Lot</v-toolbar-title>
          </v-toolbar>
          <v-card-text>
            <v-layout row wrap v-if="value == true">
              <v-flex xs2>Kode POL</v-flex>
              <v-flex xs10>{{ pol.kode_pol }}</v-flex>
              <v-flex xs2>Nama Project</v-flex>
              <v-flex xs10>{{ pol.nama_pol }}</v-flex>
              <v-flex xs2>Jumlah Order</v-flex>
              <v-flex xs10>{{ pol.jumlah_order }} / {{ pol.per_iner }}</v-flex>
              <v-flex xs2>Total Barang Masuk</v-flex>
              <v-flex xs10>{{ totalLot }}</v-flex>
              <v-flex xs12>
                <v-dialog v-model="dialogTambahLot" max-width="600px">
                  <v-btn slot="activator" color="blue" dark class="mb-2">New Lot</v-btn>
                  <v-card>
                    <v-form ref="form" v-model="validLot" lazy-validation>
                      <v-card-title>
                        <span class="headline">{{ formTitleLot }}</span>
                      </v-card-title>
                      <v-card-text>
                        <v-container grid-list-md>
                          <v-layout wrap align-center>
                            <v-flex xs12 sm6 md12>
                              <v-text-field
                                required
                                :rules="[v => !!v || 'Harus Diisi']"
                                v-model="editedItemLot.no_awal"
                                label="Group Nomor"
                                autofocus
                              ></v-text-field>
                            </v-flex>
                            <v-flex xs12 sm6 md12>
                              <v-text-field
                                required
                                :rules="[v => !!v || 'Harus Diisi']"
                                v-model="editedItemLot.kd_lot"
                                label="kode Lot"
                                autofocus
                              ></v-text-field>
                            </v-flex>
                            <v-flex xs12 sm6 md12>
                              <v-text-field
                                required
                                :rules="[v => !!v || 'Harus Diisi']"
                                v-model="editedItemLot.jumlah"
                                label="jumlah"
                                autofocus
                              ></v-text-field>
                            </v-flex>
                          </v-layout>
                        </v-container>
                      </v-card-text>
                      <v-card-actions>
                        <v-spacer></v-spacer>
                        <v-btn
                          color="blue darken-1"
                          flat
                          :disabled="!validLot"
                          @click="saveLot"
                        >Save</v-btn>
                        <v-btn color="blue darken-1" flat @click="closeLot">Cancel</v-btn>
                      </v-card-actions>
                    </v-form>
                  </v-card>
                </v-dialog>
              </v-flex>
              <v-flex xs12>
                <br />
                <v-divider></v-divider>
              </v-flex>
              <v-flex xs12>
                <v-data-table
                  :headers="headersLot"
                  :items="allLot"
                  class="elevation-1"                  
                  :rows-per-page-items="[-1]"
                >
                  <template slot="items" slot-scope="props">
                    <td>{{ props.index+1 }}</td>
                    <td>{{ props.item.kd_lot }}</td>
                    <td>{{ props.item.jumlah }}</td>
                    <td>{{ props.item.reject }}</td>
                    <td>{{ props.item.good }}</td>
                    <td>{{ props.item.username }}</td>
                    <td>{{ props.item.no_awal }}</td>
                    <td>{{ getWaktu(props.item.created_at) }}</td>
                    <td>{{ getWaktu(props.item.updated_at) }}</td>
                    <td>
                      <v-icon color="primary" small class="mr-2" @click="editItemLot(props.item)">touch_app</v-icon>
                      <v-icon  v-if="$store.getters.authUser.level == 'admin'" small @click="confirmDeleteLot(props.item)">delete</v-icon>
                    </td>
                  </template>
                  <template slot="no-data">
                    <div class="text-xs-center">Tidak Ada Data</div>
                  </template>
                </v-data-table>
              </v-flex>
            </v-layout>
          </v-card-text>
        </v-card>
      </v-dialog>
    </v-layout>
  </v-container>
</template>

<script>
import axios from "axios";
export default {
  name: "Lot",
  props: ["value", "pol"],
  data() {
    return {
      snackbar: false,
      text: "",
      timeout: 4000,
      color: "",
      itemDeleteLot: {},
      dialogDeleteLot: false,
      validLot: true,      
      dialogTambahLot: false,
      headersLot: [
        { text: "No", value: "No", sortable: false },
        { text: "Kode Lot", value: "kode_lot", sortable: false },
        { text: "Total", value: "jumlah", sortable: false },
        { text: "Reject", value: "reject", sortable: true },
        { text: "Good", value: "goodt", sortable: false },
        { text: "Operator", value: "operator", sortable: false },
        { text: "Group No", value: "no_awal", sortable: false },
        { text: "Di buat", value: "created_at", sortable: false },
        { text: "Di ubah", value: "updated_at", sortable: false },
        { text: "Actions", value: "action", sortable: false }
      ],
      allLot: [],
      editedIndexLot: -1,
      editedItemLot: {
        id_lot: "",
        id_pol: "",
        kd_lot: "",
        jumlah: "",
        reject: "",
        good: "",
        no_awal: 1,
        created_at: "",
        updated_at: ""
      },
      defaultItemLot: {
        id_lot: "",
        id_pol: "",
        kd_lot: "",
        jumlah: "",
        reject: "",
        good: "",
        no_awal: 1,
        created_at: "",
        updated_at: ""
      }
    };
  },
  computed: {
    formTitleLot() {
      return this.editedIndexLot === -1 ? "New Lot" : "Edit Lot";
    },
    jumlahLot() {
      return this.allLot.length();
    },
    totalLot() {
      if (this.allLot.length > 0) {
        return this.allLot.reduce((acc, item) => acc + item.jumlah, 0);
      } else {
        return 0;
      }
    }
  },
  watch: {
    value(val) {
      this.ambilLot();
    }
  },
  methods: {
    ambilLot() {
      axios.get("api/lot/data/" + this.pol.id_pol).then(response => {
        this.allLot = response.data;
      });
    },
    editItemLot(item) {
      this.editedIndexLot = this.allLot.indexOf(item);
      this.editedItemLot = Object.assign({}, item);
      this.dialogTambahLot = true;
    },
    confirmDeleteLot(item) {
      this.itemDeleteLot = item;
      this.dialogDeleteLot = true;
    },
    closeLot() {
      // this.$refs.form.reset()
      this.dialogTambahLot = false;
      this.defaultItemLot.no_awal = this.editedItemLot.no_awal;
      this.editedItemLot = Object.assign({}, this.defaultItemLot);
      this.editedIndexLot = -1;
    },
    saveLot() {
      if (this.$refs.form.validate()) {
        if (this.editedIndexLot > -1) {
          this.snackbar = false;
          var index = this.editedIndexLot;
          axios
            .put("api/lot/" + this.editedItemLot.id_lot, {
              kd_lot: this.editedItemLot.kd_lot,
              jumlah: this.editedItemLot.jumlah,
              operator: this.$store.state.auth.user.id,
              no_awal: this.editedItemLot.no_awal
            })
            .then(response => {
              Object.assign(this.allLot[index], response.data);
              this.tampilAlert("Berhasil Diupdate", "blue");
              this.closeLot();
            })
            .catch(response => {
              this.tampilAlert("gagal di update", "red");
              this.closeLot();
            });
        } else {
          this.snackbar = false;
          axios
            .post("api/lot", {
              id_pol: this.pol.id_pol,
              kd_lot: this.editedItemLot.kd_lot,
              jumlah: this.editedItemLot.jumlah,
              reject: 0,
              good: this.editedItemLot.jumlah,
              operator: this.$store.state.auth.user.id,
              no_awal: this.editedItemLot.no_awal
            })
            .then(response => {
              this.allLot.push(response.data);
              this.tampilAlert("Berhasil di simpan", "success");
              this.closeLot();
            })
            .catch(error => {
              console.log(error);
              this.closeLot();
            });
        }
      }
    },
    deleteLot() {
      this.snackbar = false;
      const index = this.allLot.indexOf(this.itemDeleteLot);
      axios.delete("api/lot/" + this.itemDeleteLot.id_lot).then(response => {
        this.dialogDeleteLot = false;
        this.allLot.splice(index, 1);
        this.tampilAlert("Berhasil di hapus", "red");
      });
    },
    getWaktu(date) {
      var d = new Date(new Date(date) * 1 + 1000 * 3600 * 7);
      //  return d.getFullYear()+'-'+d.getMonth()+'-'+d.getDay()+' '+d.getHours() + ':' + d.getMinutes() + ':'+d.getSeconds()
      return d.toLocaleString("en-gb");
    },
    tampilAlert(alert, color) {
      this.text = alert;
      this.color = color;
      this.snackbar = true;
    }
  }
};
</script>


