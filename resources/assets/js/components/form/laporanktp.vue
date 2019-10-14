<template>
  <v-container fluid>
    <v-layout row wrap>
      <v-flex xs12>
        <v-toolbar color="green lighten-2">
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
              <td>{{ props.index + 1 }}</td>
              <td>{{ props.item.pol.tahun }}</td>
              <td>{{ props.item.pol.kode_pol }}</td>
              <td>{{ props.item.pol.nama_pol }}</td>
              <td>Applet</td>
              <td>{{ props.item.created_at }}</td>
              <td>{{ props.item.line }}</td>
              <td>{{ props.item.id_applet }}</td>
              <td>
                <v-icon small class="mr-2" @click.stop="editSementara(props.item)">edit</v-icon>
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
              @change="ubah"
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
              <td>{{ props.index + 1 }}</td>
              <td>{{ props.item.pol.tahun }}</td>
              <td>{{ props.item.pol.kode_pol }}</td>
              <td>{{ props.item.pol.nama_pol }}</td>
              <td>Applet</td>
              <td>{{ props.item.created_at }}</td>
              <td>{{ props.item.updated_at }}</td>
              <td>{{ props.item.line }}</td>
              <td>{{ props.item.shift }}</td>
              <td>{{ props.item.id_applet }}</td>
              <td>{{ props.item.dead }}</td>
              <td>{{ props.item.lot }}</td>
              <td>
                <v-icon small class="mr-2" @click.stop="editSelesai(props.item)">edit</v-icon>
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
  name: "laporanktp",
  props: ['headersSementara','headersSelesai','allLaporanSementara','allLaporanSelesai','dateAwal'],
  data() {
    return {        
        tanggal : this.dateAwal,
        menu: false,
    };
  },
  created() {
      
  },
  methods: {
      ubah (){          
          this.$emit('ubah', this.tanggal)
      },
      editSementara(item){
          this.$emit('editSementara', item)
      },
      editSelesai(item){
          this.$emit('editSelesai', item)
      }
  },
};
</script>


