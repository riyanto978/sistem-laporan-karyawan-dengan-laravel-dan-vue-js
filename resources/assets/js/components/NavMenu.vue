<template>
  <div >
    <!-- <v-toolbar flat >
      <v-list>
        <v-list-tile>
          <v-list-tile-title class="title">
            {{ name }}
          </v-list-tile-title>
        </v-list-tile>
      </v-list>
    </v-toolbar>-->
    <!-- <v-divider></v-divider> -->
    <br />
    <v-list >
      <v-list-tile active-class="primary--text back"   v-for="(item, i) in filterMenu" :key="i" :to="item.route">
        <v-list-tile-action>
          <v-icon v-html="item.icon"></v-icon>
        </v-list-tile-action>
        <v-list-tile-content>
          <v-list-tile-title class="subheading" v-text="item.title"></v-list-tile-title>
        </v-list-tile-content>
      </v-list-tile>
    </v-list>

    <v-list v-for="(data, index) in filterListMenu" :key="index" active-class="primary--text back">
      <v-list-group prepend-icon="account_circle" class="subheading"
      >
        <v-list-tile slot="activator">
          <v-list-tile-title>{{ data.head }}</v-list-tile-title>
        </v-list-tile>

        <v-list-tile  v-for="(admin, i) in data.items" :key="i" :to="admin.route" >
          <v-list-tile-action>
            <v-icon  v-text="admin.icon" ></v-icon>
          </v-list-tile-action>
          <v-list-tile-title  v-text="admin.title" class="subheading"></v-list-tile-title>
        </v-list-tile>
      </v-list-group>
    </v-list>
  </div>
</template>

<script>
export default {
  data() {
    return {
      name: this.$t("nav_menu_title"),

      items: [
        {
          title: "Home",
          icon: "dashboard",
          route: { name: "home" },
          tipe: "reguler",
          level: ["admin", "reguler"]
        },
        // { title: 'Account', icon: 'account_box', route: { name: 'settings.profile',level: [1, 2] } },
        {
          title: "Proses",
          icon: "build",
          route: { name: "proses" },
          tipe: "reguler",
          level: ["admin"]
        },
        {
          title: "POL",
          icon: "description",
          route: { name: "pol" },
          tipe: "reguler",
          level: ["admin","ppic"]
        },
        {
          title: "User",
          icon: "account_circle",
          route: { name: "user" },
          tipe: "reguler",
          level: ["admin"]
        },
        {
          title: "Resume",
          icon: "history",
          route: { name: "resume" },
          tipe: "reguler",
          level: ["admin", "reguler"]
        },
        {
          title: "Monitoring",
          icon: "card_membership",
          route: { name: "monitoring" },
          tipe: "reguler",
          level: ["admin","ppic"]
        },
        {
          title: "Cetak Laporan",
          icon: "print",
          route: { name: "cetaklaporan" },
          tipe: "reguler",
          level: ["admin","ppic"]
        },
        {
          title: "Home eKTP",
          icon: "work",
          route: { name: "home.ktp" },
          tipe: "ktp",
          level: ["admin", "reguler"]
        },
        {
          title: "Applet",
          icon: "text_format",
          route: { name: "applet" },
          tipe: "ktp",
          level: ["admin", "reguler"]
        },
        {
          title: "Pre Perso",
          icon: "local_parking",
          route: { name: "preperso" },
          tipe: "ktp",
          level: ["admin", "reguler"]
        },
        {
          title: "Record",
          icon: "work",
          route: { name: "record" },
          tipe: "ktp",
          level: ["admin", "reguler"]
        },
        {
          title: "Periode",
          icon: "work",
          route: { name: "periode" },
          tipe: "ktp",
          level: ["admin"]
        },
        {
          title: "Kartu SAM",
          icon: "work",
          route: { name: "kartu_sam" },
          tipe: "ktp",
          level: ["admin"]
        },
        {
          title: "Packing",
          icon: "work",
          route: { name: "packing" },
          tipe: "reguler",
          level: ["packing"]
        },
      ],
      itemsList: [
        {
          head: "Report",
          tipe: "reguler",
          level: ["reguler","admin"],
          items: [
            {
              title: "Laporan Harian",
              icon: "work",
              route: { name: "laporan" },
              tipe: "reguler",
              level: ["admin", "reguler"]
            },
            {
              title: "Laporan Memo",
              icon: "work",
              route: { name: "memo" },
              tipe: "reguler",
              level: ["admin", "reguler"]
            },
            {
              title: "Counter & Wraping",
              icon: "work",
              route: { name: "counter" },
              tipe: "reguler",
              level: ["admin", "reguler"]
            },
            {
              title: "Transfer",
              icon: "swap_horiz",
              route: { name: "transfer" },
              tipe: "reguler",
              level: ["admin",'reguler']
            },
            {
              title: "Bongkaran",
              icon: "work",
              route: { name: "bongkaran" },
              tipe: "reguler",
              level: ["admin",'reguler']
            }
          ]
        },
        {
          head: "Level 2",
          tipe: "reguler",
          level: ["leveldua"],
          items: [
            {
              title: "Input Bongkaran",
              icon: "work",
              route: { name: "input_bongkaran" },
              tipe: "reguler",
            },
          ]
        },
        {
          head: "Gudang",
          tipe: "reguler",
          level: ["gudang"],
          items: [
            {
              title: "transfer Bongkaran",
              icon: "work",
              route: { name: "transfer_bongkaran" },
              tipe: "reguler",
            },
          ]
        },
      ]
    };
  },

  created() {
    //alert(this.$route.name)
  },
  computed: {
    filterMenu() {
      return this.filterType.filter(pros =>
        pros.level.includes(this.$store.state.auth.user.level)
      );
    },
    filterListMenu() {
      return this.filterTypeList.filter(pros =>
        pros.level.includes(this.$store.state.auth.user.level)
      );
    },
    filterType() {
      return this.items.filter(x => x.tipe == this.$store.state.info.tipe);
    },
    filterTypeList() {
      return this.itemsList.filter(x => x.tipe == this.$store.state.info.tipe);
    },
  },
  methods: {

  },
};
</script>


<style>
  .back{
    /* background-color : silver;     */
    border-top-right-radius: 5px;
    border-bottom-right-radius: 5px;
    margin-right: 5px;
    /* margin-left: 5px; */
    border-left: solid 5px blueviolet
  }

</style>
