<template>
  <v-container>
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
      <!-- {{ periode }} -->
      <br />

      <v-flex xs12 sm12 lg12>
        <v-toolbar flat class="grey lighten-5">
          <v-toolbar-title>Daftar periode</v-toolbar-title>
          <v-spacer></v-spacer>
          <v-text-field v-model="cari" placeholder="Cari"></v-text-field>
          <v-dialog v-model="dialog" max-width="600px">
            <v-btn slot="activator" color="blue" dark class="mb-2">New periode</v-btn>
            <v-card>
              <v-form ref="form" v-model="valid" lazy-validation>
                <v-card-title>
                  <span class="headline">{{ formTitle }}</span>
                </v-card-title>
                <v-card-text>
                  <v-container grid-list-md>
                    <v-layout wrap align-center>
                      <v-flex xs12 sm6 md12>
                        <v-text-field
                          required
                          :rules="[v => !!v || 'Nama harus di isi']"
                          v-model="editedItem.nama_periode"
                          label="Nama periode"
                          autofocus
                        ></v-text-field>

                        <v-text-field
                          required
                          type="number"
                          :rules="[v => !!v || 'Harus di isi']"
                          v-model="editedItem.jmlorder"
                          label="Jumlah Order"
                        ></v-text-field>
                      </v-flex>
                    </v-layout>
                  </v-container>
                </v-card-text>
                <v-card-actions>
                  <v-spacer></v-spacer>
                  <v-btn color="blue darken-1" flat :disabled="!valid" @click="save">Save</v-btn>
                  <v-btn color="blue darken-1" flat @click="close">Cancel</v-btn>
                </v-card-actions>
              </v-form>
            </v-card>
          </v-dialog>

          <v-dialog v-model="dialogDelete" max-width="290">
            <v-card>
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
        </v-toolbar>
        <br />
        <!-- :style="{backgroundColor: (props.item.tipe == 'reguler' ? 'blue lighten-1' : 'transparent' ) }" -->
        <!-- :class="props.item.tipe == 'reguler' ? 'green lighten-4' : 'transparent'" -->
        <v-data-table
          :headers="headers"
          :items="filterperiode"
          :rows-per-page-items="row"
          class="elevation-1"
          id="table1"
        >
          <template slot="items" slot-scope="props">
            <tr>
              <td>{{ props.index + 1 }}</td>
              <td>{{ props.item.nama_periode }}</td>
              <td>{{ props.item.jmlorder }}</td>
              <!-- <td>{{ props.item.tipe }}</td> -->
              <td>{{ props.item.created_at }}</td>
              <td>{{ props.item.updated_at }}</td>
              <td>
                <v-icon small class="mr-2" @click="editItem(props.item)">edit</v-icon>
                <v-icon small @click="confirmDelete(props.item)">delete</v-icon>
              </td>
            </tr>
          </template>
          <template slot="no-data">
            <div class="text-xs-center">Tidak Ada Data</div>
          </template>
        </v-data-table>
      </v-flex>
    </v-layout>
  </v-container>
</template>

<script>
import axios from "axios";
// import { mapGetters } from "vuex";

export default {
  data() {
    return {
      valid: true,
      snackbar: false,
      text: "",
      timeout: 5000,
      color: "",
      loader: false,
      dialog: false,
      cari: "",
      combo_manual: ["Manual PC", "Checker"],
      combo_tipe: ["tombol", "upload_file"],
      row: [25, { text: "$vuetify.dataIterator.rowsPerPageAll", value: -1 }],
      alert: false,
      periode: [],
      headers: [
        { text: "No", value: "No", sortable: false },
        {
          text: "Nama periode",
          align: "left",
          sortable: true,
          value: "nama_periode",
          sortable: true
        },
        { text: "Jml order", value: "tanggal", sortable: true },
        { text: "Dibuat Tgl", value: "created_at", sortable: true },
        { text: "Diupdate Tgl", value: "updated_at", sortable: true },
        { text: "Actions", value: "name", sortable: false }
      ],

      editedIndex: -1,
      dialogDelete: false,
      itemDelete: {},
      editedItem: {
        id_periode: "",
        nama_periode: null,
        jmlorder: 0,
        updated_at: "",
        created_at: ""
      },
      defaultItem: {
        id_periode: "",
        nama_periode: null,
        jmlorder: 0,
        updated_at: "",
        created_at: ""
      },
      breadcrumbs: [
        {
          text: "Home",
          disabled: true,
          href: "/"
        },
        {
          text: "periode",
          disabled: true,
          href: "/periode"
        }
      ]
    };
  },
  computed: {
    formTitle() {
      return this.editedIndex === -1 ? "New periode" : "Edit periode";
    },
    filterperiode() {
      return this.periode.filter(pros => {
        return pros.nama_periode.toLowerCase().match(this.cari);
      });
    }
  },

  watch: {
    dialog(val) {
      val || this.close();
    }
  },

  created() {
    this.$store.commit("CHANGE_BREAD", { list: this.breadcrumbs });
    this.$store.commit("SET_JUDUL", "periode");
    this.ambil_periode();
  },

  methods: {
    ambil_periode() {
      axios.get("api/periode").then(response => {
        this.periode = response.data;
      });
    },
    tampilAlert(alert, color) {
      this.text = alert;
      this.color = color;
      this.snackbar = true;
    },
    editItem(item) {
      this.editedIndex = this.periode.indexOf(item);
      this.editedItem = Object.assign({}, item);
      this.dialog = true;
    },
    confirmDelete(item) {
      this.itemDelete = item;
      this.dialogDelete = true;
    },
    deleteItem() {
      this.snackbar = false;
      const index = this.periode.indexOf(this.itemDelete);
      var self = this;

      axios
        .delete("api/periode/" + this.itemDelete.id_periode)
        .then(function(response) {
          self.periode.splice(index, 1);
          self.tampilAlert("Berhasil Dihapus", "red");
          self.dialogDelete = false;
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
    save() {
      if (this.$refs.form.validate()) {
        if (this.editedIndex > -1) {
          this.snackbar = false;
          var id_periode = this.editedItem.id_periode;
          var index = this.editedIndex;

          axios
            .put("api/periode/" + id_periode, {
              nama_periode: this.editedItem.nama_periode,
              jmlorder: this.editedItem.jmlorder
            })
            .then(response => {
              Object.assign(this.periode[index], response.data);
              this.tampilAlert("Berhasil Diupdate", "blue");
              this.close();
            });
        } else {
          this.snackbar = false;
          axios
            .post("api/periode", {
              nama_periode: this.editedItem.nama_periode,
              jmlorder: this.editedItem.jmlorder              
            })
            .then(response => {
              this.periode.push(response.data);
              this.tampilAlert("Berhasil Disimpan", "success");
              this.close();
            })
            .catch(error => {
              console.log(error);
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



