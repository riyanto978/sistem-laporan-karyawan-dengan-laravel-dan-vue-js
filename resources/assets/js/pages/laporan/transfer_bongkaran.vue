<template>
  <v-container fluid grid-list-xl>
    <!-- {{ selected }} -->
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
    <v-layout row wrap>
      <v-flex xs7>
        <v-btn v-if="selected.length > 0" @click="save">Simpan</v-btn>
      </v-flex>
      <v-flex xs5>
        <v-text-field v-model="search" append-icon="search" label="Search" single-line hide-details></v-text-field>
      </v-flex>

      <v-flex xs12>
        <v-data-table
          v-model="selected"
          :headers="headers"
          :items="belumTransfer"
          :search="search"
          item-key="id_bongkaran"
          select-all
          class="elevation-1"
        >
          <template slot="items" slot-scope="props">
            <td>
              <v-checkbox v-model="props.selected" primary hide-details></v-checkbox>
            </td>
            <td>{{ props.index + 1 }}</td>
            <td>{{ props.item.pol }}</td>
            <td>{{ props.item.lot }}</td>
            <td>{{ props.item.shift }}</td>
            <td>{{ props.item.jumlah }}</td>
            <td>{{ props.item.good }}</td>
            <td>{{ props.item.reject }}</td>
            <td>{{ props.item.user.name }}</td>
          </template>
        </v-data-table>
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
              @change="ambilSudahTransfer"
              v-model="tanggal"
              @input="$refs.menu.save(tanggal)"
            ></v-date-picker>
          </v-menu>
        </v-toolbar>
        <v-data-table :headers="headers" hide-actions :items="sudahTransfer" class="elevation-1">
          <template slot="items" slot-scope="props">
            <tr>
              <td>{{ props.index + 1 }}</td>
              <td>{{ props.item.pol }}</td>
              <td>{{ props.item.lot }}</td>
              <td>{{ props.item.shift }}</td>
              <td>{{ props.item.jumlah }}</td>
              <td>{{ props.item.good }}</td>
              <td>{{ props.item.reject }}</td>
              <td>{{ props.item.user.name }}</td>
              <td>
                <v-icon small class="mr-2" @click.stop="update(props.item)">edit</v-icon>
              </td>
            </tr>
          </template>
          <template slot="no-data">
            <div class="text-xs-center">Tidak Ada Data</div>
          </template>
        </v-data-table>
        <br />
      </v-flex>
    </v-layout>
  </v-container>
</template>

<script>
import axios from "axios";
export default {
  data() {
    return {
      snackbar: false,
      text: "",
      timeout: 5000,
      color: "",
      menu : false,
      search: "",
      selected: [],
      headers: [
        { text: "No", sortable: false },
        { text: "pol", sortable: false },
        { text: "lot", sortable: false },
        { text: "shift", sortable: false },
        { text: "jumlah", sortable: false },
        { text: "good", sortable: false },
        { text: "reject", sortable: false },
        { text: "operator", sortable: false }
      ],
      tanggal: window.tanggal,
      belumTransfer: [],
      sudahTransfer: []
    };
  },
  created() {
    this.$store.commit("CHANGE_BREAD", { list: this.breadcrumbs });
    this.$store.commit("SET_JUDUL", "Transfer bongkaran");
    this.ambilBelumTransfer();
    this.ambilSudahTransfer();
  },
  methods: {
    ambilBelumTransfer() {
      axios.get("api/transfer_bongkaran/belumTransfer").then(response => {
        this.belumTransfer = response.data;
      });
    },
    ambilSudahTransfer() {
      axios
        .get("api/transfer_bongkaran/SudahTransfer/" + this.tanggal)
        .then(response => {
          this.sudahTransfer = response.data;
        });
    },
    tampilAlert(alert, color) {
      this.text = alert;
      this.color = color;
      this.snackbar = true;
    },
    save() {
      this.snackbar = false;
      var data = JSON.stringify(this.selected);
      axios
        .post("api/transfer_bongkaran/SimpanTransfer", {
          data: data
        })
        .then(response => {
          this.ambilBelumTransfer();
          this.ambilSudahTransfer();
          this.tampilAlert("Berhasil Disimpan", "success");
        });
    },
    update(item) {
      this.snackbar = false;
      var index = this.sudahTransfer.indexOf(item);
      axios
        .put(
          "api/transfer_bongkaran/UpdateTransfer/" + item.id_bongkaran
        )
        .then(response => {
            this.ambilBelumTransfer();
            this.sudahTransfer.splice(index, 1);
            this.tampilAlert("Berhasil Diupdate", "success");
        })
    }
  }
};
</script>
