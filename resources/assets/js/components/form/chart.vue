<template>
  <v-card>
    <v-toolbar
      dense
      :color="warna(index)"
    >Pol : {{ item.header.kode_pol }} {{ item.header.nama_pol }} | {{ item.header.nama_proses }}</v-toolbar>
    <v-card-text>
      <ve-histogram :data="item.line" :events="chartEvents"></ve-histogram>
    </v-card-text>
  </v-card>
  
</template>

<script>
export default {
  name: "chart",
  props: ["item", "index"],
  data() {
    return {
      color: ["blue lighten-4", "green lighten-4", "orange lighten-4"],
      chartEvents: {}
    };
  },
  created() {
    var self = this;
    this.chartEvents = {
      click: function(e) {
        // self.operator = e.name;
        //self.ambilResumeHome(e.name);
        self.$emit('ambilResume',e.name,self.item.header.id_pol,self.item.header.id_proses)
      }
    };
  },
  methods: {
    warna(index) {
      if (index > 2) {
        var ind = index - 3;
      } else {
        var ind = index;
      }
      return this.color[ind];
    }
  }
};
</script>