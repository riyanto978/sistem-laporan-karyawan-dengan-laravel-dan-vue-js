<template>
  <v-layout align-start>
    <v-flex xs3>
      <v-item-group v-model="window" mandatory>
        <!-- <v-item v-for="(item, index) in length" :key="index">
          <v-btn icon @click="window = item">
            <v-icon>person</v-icon>
            {{ item }}
          </v-btn>
        </v-item>-->

        <v-list>
          <v-list-tile  v-for="(item, index) in list" :key="index" @click="window = item.header">
            <v-list-tile-action>
              <v-icon>person</v-icon>
            </v-list-tile-action>
            <v-list-tile-content>
              <v-list-tile-title light v-text="item.header"></v-list-tile-title>
            </v-list-tile-content>
          </v-list-tile>
        </v-list>
      </v-item-group>
    </v-flex>
    <v-flex xs9>
      <v-window v-model="window" class="elevation-1" vertical>
        <v-window-item v-for="(item, index) in list" :key="index" :value="item.header">
          <v-card flat>
            <v-card-text>
              <v-layout align-center mb-3>
                <strong class="title">Proses {{ item.header }}</strong>
                <v-spacer></v-spacer>
                <v-btn icon>
                  <v-icon>person</v-icon>
                </v-btn>
              </v-layout>

              <table class="table" border="1" width="50%">
                <thead>
                  <th>No</th>
                  <th>Operator</th>
                  <th>Jumlah</th>
                </thead>
                <tbody>
                  <tr v-for="(list, index) in item.data" :key="index">
                    <td>{{ index+1 }}</td>
                    <td>{{ list.name }}</td>
                    <td>{{ list.isi }}</td>
                  </tr>
                </tbody>
              </table>
            </v-card-text>
          </v-card>
        </v-window-item>
      </v-window>
    </v-flex>
  </v-layout>
</template>

<script>
export default {
  name: "listOperator",
  props: ["list",'window'],
  data: () => ({
    
  })
};
</script>

<style>
   
</style>
