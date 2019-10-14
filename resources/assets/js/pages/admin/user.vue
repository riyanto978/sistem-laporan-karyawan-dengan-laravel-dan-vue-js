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
      <!-- {{ totalItems }} -->
      <v-flex xs12 sm12 lg12>
        <v-toolbar flat color="white">
          <v-toolbar-title>Daftar User</v-toolbar-title>
          <v-divider class="mx-2" inset vertical></v-divider>

          <v-spacer></v-spacer>
          <v-flex xs2>
            {{ totalItems }} user
            <span>{{ cari != ''?'disaring':'total ' }}</span>
          </v-flex>

          <v-text-field
            append-icon="search"
            v-model="cari"
            placeholder="Cari User"
            @keyup="waitInput"
          ></v-text-field>
          <v-dialog v-model="dialog" max-width="600px">
            <v-btn slot="activator" color="blue" dark class="mb-2">New User</v-btn>
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
                          v-model="editedItem.name"
                          :rules="[v=> !!v || 'Harus di isi']"
                          label="Nama"
                        ></v-text-field>

                        <v-text-field
                          required
                          v-model="editedItem.username"
                          :rules="[v=> !!v || 'Harus di isi']"
                          label="Username"
                        ></v-text-field>
                        <v-text-field
                          required
                          type="password"
                          v-model="editedItem.password"
                          :rules="editedIndex == -1 ? rules : []"
                          label="Password"
                        ></v-text-field>
                        <input type="file" id="file" ref="file" accept="*.jpg, *.png" />
                        <v-select
                          v-model="editedItem.level"
                          :rules="[v=> !!v || 'Harus di isi']"
                          :items="combo_level"
                          label="Level"
                        ></v-select>
                      </v-flex>
                    </v-layout>
                  </v-container>
                </v-card-text>
                <v-card-actions>
                  <v-spacer></v-spacer>
                  <v-btn color="blue darken-1" flat :disabled="!valid" @click="save">SIMPAN</v-btn>
                  <v-btn color="blue darken-1" flat @click="close">BATAL</v-btn>
                </v-card-actions>
              </v-form>
            </v-card>
          </v-dialog>
        </v-toolbar>
        <!-- {{ editedItem }} -->
        <v-data-table
          :headers="headers"
          :items="allUser"
          hide-actions
          class="elevation-1"
          :loading="loading"
        >
          <template slot="items" slot-scope="props">
            <td>{{ (page-1)*5+ props.index+1 }}</td>
            <td>{{ props.item.name }}</td>
            <td>{{ props.item.username }}</td>
            <td>
              <img
                :src="`public/foto/${props.item.foto}`"
                alt="Vue Material Admin"
                width="100"
                height="100"
              />
            </td>
            <td>{{ props.item.level }}</td>
            <td>
              <v-icon small class="mr-2" @click="editItem(props.item)">edit</v-icon>
              <v-icon small @click="confirmDelete(props.item)">delete</v-icon>
            </td>
          </template>
          <template slot="no-data">
            <div class="text-xs-center">Tidak Ada Data</div>
          </template>
        </v-data-table>
        <div class="text-xs-center pt-2">
          <v-pagination v-model="page" :length="pages" @input="ambilPage"></v-pagination>
        </div>
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
      Judul: "Tambah User",
      valid: true,
      snackbar: false,
      text: "",
      timeout: 5000,
      color: "",
      loader: false,
      dialog: false,
      file: "",
      page: 1,
      rules: [v => !!v || "Harus di isi"],
      allUser: [],
      totalItems: 0,
      user: [],
      cari: "",
      loading: false,
      row: [
        10,
        25,
        { text: "$vuetify.dataIterator.rowsPerPageAll", value: -1 }
      ],
      alert: false,
      dialogDelete : false,
      itemDelete : {},
      headers: [
        { text: "No", value: "No" },
        {
          text: "Nama",
          align: "left",
          sortable: true,
          value: "name"
        },
        { text: "User name", value: "username" },
        { text: "Foto", value: "foto" },
        { text: "Level", value: "level" },
        { text: "Actions", value: "name", sortable: false }
      ],
      combo_level: ["admin", "reguler","packing","ppic","leveldua",'gudang'],
      editedIndex: -1,
      editedItem: {
        id: "",
        name: "",
        username: "",
        foto: null,
        updated_at: "",
        created_at: "",
        level: "reguler"
      },
      defaultItem: {
        id: "",
        name: "",
        username: "",
        foto: null,
        updated_at: "",
        created_at: "",
        level: "reguler"
      },
      breadcrumbs: [
        {
          text: "Home",
          disabled: false,
          href: "/home"
        },
        {
          text: "User",
          disabled: true,
          href: ""
        }
      ]
    };
  },
  computed: {
    formTitle() {
      return this.editedIndex === -1 ? "New User" : "Edit User";
    },
    pages() {
      return Math.ceil(this.totalItems / 5);
    }
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
    this.$store.commit('CHANGE_BREAD', { list: this.breadcrumbs })
    this.$store.commit('SET_JUDUL', 'Tambah User')
    this.ambilUser();

  },

  methods: {
    ambilUser() {
      if (this.cari == "") {
        var cari = null;
      } else {
        var cari = this.cari;
      }
      axios
        .get("api/userAll/" + cari, {
          headers: {
            Authorization: "Bearer " + this.$store.state.auth.token
          }
        })
        .then(response => {
          this.totalItems = response.data.total;
          this.allUser = response.data.data;
          this.loading = false;
        })
        .catch(error => {
          console.log(error);
          this.loading = false;
        });
    },
    ambilPage() {
      this.loading = true;
      if (this.cari == "") {
        var cari = null;
      } else {
        var cari = this.cari;
      }
      axios
        .get("api/userAll/" + cari + "?page=" + this.page, {
          headers: {
            Authorization: "Bearer " + this.$store.state.auth.token
          }
        })
        .then(response => {
          this.totalItems = response.data.total;
          this.allUser = response.data.data;
          this.loading = false;
        });
    },
    tampilAlert(alert, color) {
      this.text = alert;
      this.color = color;
      this.snackbar = true;
    },
    editItem(item) {
      this.editedIndex = this.allUser.indexOf(item);
      this.editedItem = Object.assign({}, item);
      this.dialog = true;
    },
    confirmDelete(item){
        this.itemDelete = item;
        this.dialogDelete = true;
    },
    deleteItem() {
      this.snackbar = false;
      const index = this.user.indexOf(this.itemDelete);

      axios
        .delete("api/user/" + this.itemDelete.id, {
          headers: {
            Authorization: "Bearer " + this.$store.state.auth.token,
            "Content-Type": "multipart/form-data"
          }
        })
        .then(response => {
          this.allUser.splice(index, 1);
          this.tampilAlert("Berhasil di hapus", "red");
          this.dialogDelete = false
        });
    },

    close() {
      this.$refs.form.reset();
      this.dialog = false;
      this.editedItem = Object.assign({}, this.defaultItem);
      this.editedIndex = -1;
    },
    save() {
      if (this.$refs.form.validate()) {
        if (this.editedIndex == -1) {
          var log = "this.$refs.file.files[0] == undefined";
        } else {
          var log = "false";
        }
        if (eval(log)) {
          alert("Foto belum di pilih");
        } else {
          if (this.editedIndex > -1) {
            var file = this.$refs.file.files[0];
            this.snackbar = false;
            var id = this.editedItem.id;
            var index = this.editedIndex;
            let formData = new FormData();
            formData.append("foto", file);
            formData.append("name", this.editedItem.name);
            formData.append("username", this.editedItem.username);
            formData.append("level", this.editedItem.level);
            formData.append("password", this.editedItem.password);
            formData.append("_method", "put");
            axios
              .post("api/user/" + id, formData, {
                headers: {
                  Authorization: "Bearer " + this.$store.state.auth.token,
                  "Content-Type": "multipart/form-data"
                }
              })
              .then(response => {
                Object.assign(this.allUser[index], response.data);
                this.tampilAlert("Berhasil di update", "blue darken-2");
                this.close();
              })
              .catch(error => {
                this.close();
              });
          } else {
            this.snackbar = false;
            let formData = new FormData();
            var file = this.$refs.file.files[0];
            formData.append("foto", file);
            formData.append("name", this.editedItem.name);
            formData.append("username", this.editedItem.username);
            formData.append("level", this.editedItem.level);
            formData.append("password", this.editedItem.password);
            axios
              .post("api/user", formData, {
                headers: {
                  Authorization: "Bearer " + this.$store.state.auth.token,
                  "Content-Type": "multipart/form-data"
                }
              })
              .then(response => {
                this.allUser.push(response.data);
                this.tampilAlert("Berhasil di simpan", "success");
                this.close();
              })
              .catch(error => {
                console.log(error);
                this.close();
              });

            //this.user.push(this.editedItem)
          }
        }
      }
    },
    waitInput() {
      this.page = 1;
      this.loading = true;
      if (this.timer) {
        clearTimeout(this.timer);
        this.timer = null;
      }
      this.timer = setTimeout(() => {
        this.ambilUser();
      }, 800);
    }
  }
};
</script>
