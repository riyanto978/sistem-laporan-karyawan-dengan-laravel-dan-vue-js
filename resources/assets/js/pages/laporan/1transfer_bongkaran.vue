<template>
  <v-container fluid>
    <v-layout row wrap>
      <v-flex xs2>
        <input type="text" class="form-control">
      </v-flex>
      <v-flex xs2>
        <v-text-field class="form-control" ></v-text-field>
      </v-flex>
      <v-flex xs2>
        <v-select  :items="['nama_pol','kode_pol']" label="Search By" v-model="searchBy"></v-select>
      </v-flex>
      <v-flex xs8>
        <v-autocomplete
          v-model="model"
          :items="items"
          :loading="isLoading"
          :search-input.sync="search"
          chips
          cache-items
          hide-details
          hide-selected
          :item-text="searchBy"
          item-value="nama_pol"
          label="Search Pol..."
          solo
          return-object
        >
          <template slot="no-data">
            <v-list-tile>
              <v-list-tile-title>
                Cari
                <strong>Pol</strong>
              </v-list-tile-title>
            </v-list-tile>
          </template>
          <template slot="selection" slot-scope="{ item, selected }">
            <v-chip :selected="selected" color="blue-grey" class="white--text">
              <v-icon left>mdi-coin</v-icon>
              <span v-text="item.nama_pol"></span>
            </v-chip>
          </template>
          <template slot="item" slot-scope="{ item }">
            <v-list-tile>{{ item.tahun }}</v-list-tile>
            <v-list-tile-content>
              <v-list-tile-title>{{ item.kode_pol }}</v-list-tile-title>
              <v-list-tile-sub-title>{{ item.nama_pol }}</v-list-tile-sub-title>
            </v-list-tile-content>
            <v-list-tile-action>
              <v-icon>mdi-coin</v-icon>
            </v-list-tile-action>
          </template>
        </v-autocomplete>
      </v-flex>
      <v-flex xs1>
        <v-text-field v-model="model.tahun" label="tahun" disabled></v-text-field>
      </v-flex>
      <v-flex xs1>
        <v-text-field v-model="model.kode_pol" label="kode_pol" disabled></v-text-field>
      </v-flex>
      <v-flex xs2>
        <v-select :items="['jumlah','kode_pol']" label="jenis reject" v-model="searchBy"></v-select>
      </v-flex>
      <v-flex xs2>
        <v-text-field label="jumlah"></v-text-field>
      </v-flex>
    </v-layout>    
  </v-container>
</template>

<script>
import axios from "axios";
export default {
  data: () => ({
    isLoading: false,
    items: [],
    model: {},    
    search: null,
    timer: null,
    searchBy: "nama_pol"
  }),

  watch: {
    search(val) {
      // Items have already been loaded
      //if (this.model) return;
      if (this.timer) {
        clearTimeout(this.timer);
        this.timer = null;
      }
      this.timer = setTimeout(() => {
        if (this.search == "") {
          this.search = null;
        }
        this.isLoading = true;
        axios
          .get("api/pol/search/" + this.search, {
            headers: {
              Authorization: "Bearer " + this.$store.state.auth.token
            }
          })
          .then(response => {
            this.items = response.data;
            this.isLoading = false;
          });
      }, 800);
    }
  }
};
</script>

<style>
  .form-control {
  display: block;
  width: 100%;
  height: 34px;
  padding: 6px 12px;
  font-size: 14px;
  line-height: 1.42857143;
  color: #555;
  background-color: #fff;
  background-image: none;
  border: 1px solid #ccc;
  border-radius: 4px;
  -webkit-box-shadow: inset 0 1px 1px rgba(0, 0, 0, .075);
          box-shadow: inset 0 1px 1px rgba(0, 0, 0, .075);
  -webkit-transition: border-color ease-in-out .15s, -webkit-box-shadow ease-in-out .15s;
       -o-transition: border-color ease-in-out .15s, box-shadow ease-in-out .15s;
          transition: border-color ease-in-out .15s, box-shadow ease-in-out .15s;
}
.form-control:focus {
  border-color: #66afe9;
  outline: 0;
  -webkit-box-shadow: inset 0 1px 1px rgba(0,0,0,.075), 0 0 8px rgba(102, 175, 233, .6);
          box-shadow: inset 0 1px 1px rgba(0,0,0,.075), 0 0 8px rgba(102, 175, 233, .6);
}
</style>