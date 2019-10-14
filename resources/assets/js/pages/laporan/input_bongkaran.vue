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
    <v-form ref="form" v-model="valid" lazy-validation @submit.prevent="save">
      <v-layout row wrap>
        <v-flex xs1>
          <v-text-field v-model="editedItem.pol" label="Pol" :rules="[v => !!v || 'Harus di isi']"></v-text-field>
        </v-flex>
        <v-flex xs3>
          <v-text-field
            v-model="editedItem.operator"
            label="operator"
            ref="operator"
            :rules="[v => !!v || 'Harus di isi']"
          ></v-text-field>
        </v-flex>
        <v-flex xs1>
          <v-text-field
            v-model="editedItem.lot"
            label="lot"
            ref="lot"
            :rules="[v => !!v || 'Harus di isi']"
          ></v-text-field>
        </v-flex>
        <v-flex xs1>
          <v-text-field
            v-model="editedItem.good"
            label="good"
            type="number"
            :rules="[v => !!v || 'Harus di isi']"
          ></v-text-field>
        </v-flex>
        <v-flex xs1>
          <v-text-field
            v-model="editedItem.reject"
            label="reject"
            type="number"
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
          <v-btn type="submit" color="blue darken-1" flat :disabled="!valid">Save</v-btn>
        </v-flex>
      </v-layout>
    </v-form>

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
            <td>{{ props.item.operator }}</td>
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
                  v-model="editedLaporan.pol"
                  label="Pol"
                  :rules="[v => !!v || 'Harus di isi']"
                ></v-text-field>
              </v-flex>
              <v-flex xs12>
                <v-text-field
                  v-model="editedLaporan.lot"
                  label="lot"                  
                  :rules="[v => !!v || 'Harus di isi']"
                ></v-text-field>
              </v-flex>
              <v-flex xs12>
                <v-text-field
                  v-model="editedLaporan.good"
                  label="good"
                  type="number"
                  :rules="[v => !!v || 'Harus di isi']"
                ></v-text-field>
              </v-flex>
              <v-flex xs12>
                <v-text-field
                  v-model="editedLaporan.reject"
                  label="reject"
                  type="number"
                  :rules="[v => !!v || 'Harus di isi']"
                ></v-text-field>
              </v-flex>
              <v-flex xs12>
                <v-select
                  :rules="[v => !!v || 'Harus di isi']"
                  :items="[1,2]"
                  label="shift"
                  v-model="editedLaporan.shift"
                ></v-select>
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
      dialogUpdateLaporan: false,
      validUpdateLaporan: true,
      allPol: [],
      log: "",
      allsam: [],
      dialogPol: false,
      allLaporanSelesai: [],
      dataSementara: [],
      tanggal: window.tanggal,
      menu: false,
      headersSelesai: [
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
        id_input: "",
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
        id_input: "",
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
        id_input: "",
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
    }
  },
  computed: {},
  created() {
    this.$store.commit("CHANGE_BREAD", { list: this.breadcrumbs });
    this.$store.commit("SET_JUDUL", "Input bongkaran");
    this.ambilLaporanSelesai();    
  },
  methods: {
    ambilLaporanSelesai() {
      axios
        .get(
          "api/input_bongkaran/selesai/" +
            this.$store.state.auth.user.id +
            "/" +
            this.dateAwal
        )
        .then(response => {
          this.allLaporanSelesai = response.data;
        });
    },
    changeTanggal(tanggal) {
      this.dateAwal = tanggal;
      this.ambilLaporanSelesai();
    },
    editLaporanSelesai(item) {
      this.editedIndex = this.allLaporanSelesai.indexOf(item);
      this.editedLaporan = Object.assign({}, item);
      this.dialogUpdateLaporan = true;
    },    
    update() {
      if (this.$refs.formUpdateLaporan.validate()) {
        this.snackbar = false;
        var index = this.editedIndex;
        axios
          .put(
            "api/input_bongkaran/" + this.editedLaporan.id_input,
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
    },
    save() {
      if (this.$refs.form.validate()) {
        this.snackbar = false;
        axios
          .post("api/input_bongkaran", this.editedItem)
          .then(response => {
            this.allLaporanSelesai.push(response.data);
            this.tampilAlert("Berhasil Disimpan", "success");
            this.$refs.operator.focus();
            this.close();
            
          })
          .catch(error => {
            console.log(error);
            this.close();
          });
      }
    },
    tampilAlert(alert, color) {
      this.text = alert;
      this.color = color;
      this.snackbar = true;
    },
    close() {
      this.defaultItem.pol = this.editedItem.pol;
      this.defaultItem.shift = this.editedItem.shift;
      this.defaultItem.line = this.editedItem.line;
      this.defaultItem.operator = this.editedItem.operator;
      this.editedItem = Object.assign({}, this.defaultItem);
      this.editedIndex = -1;
    },
    confirmDelete(item, tipe) {
      this.itemDelete = item;
      this.dialogDelete = true;
    },
    deleteLaporan() {
      this.snackbar = false;
      axios
        .delete("api/input_bongkaran/" + this.itemDelete.id_input)
        .then(response => {
          this.dialogDelete = false;
          const index = this.allLaporanSelesai.indexOf(this.itemDelete);
          this.allLaporanSelesai.splice(index, 1);
          this.tampilAlert("Berhasil di hapus", "red");
        });
    }
  }
};
</script>