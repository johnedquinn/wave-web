<template>
  <md-app>
    <md-app-content>
      <md-table v-model="users" md-card @md-selected="onSelect">
        <!--  HEADERS -->
        <!--<md-table-row v-model="users" md-card>
            <md-table-head>Avatar</md-table-head>
            <md-table-head>Name</md-table-head>
            <md-table-head>Surname</md-table-head>
            <md-table-head>Email</md-table-head>
            </md-table-row>-->

        <!--<md-table-row :key="user.id" v-for="user in users">
                <md-table-cell>H</md-table-cell>
                <md-table-cell>{{ user.name }}</md-table-cell>
                <md-table-cell>{{ user.surname }}</md-table-cell>
                <md-table-cell>{{ user.email }}</md-table-cell>
            </md-table-row>-->
        <md-table-row
          slot="md-table-row"
          slot-scope="{ item }"
          md-selectable="single"
        >
          <md-table-cell md-label="Avatar" md-sort-by="avatar">
            <md-avatar v-if="item.img && item.img != ''" class="md-avatar-icon">
              <img :src="item.img" />
            </md-avatar>
            <md-avatar v-else class="md-small">
              <md-icon>group</md-icon>
            </md-avatar>
          </md-table-cell>
          <md-table-cell md-label="Name" md-sort-by="name">{{
            item.name
          }}</md-table-cell>
          <md-table-cell md-label="Surname" md-sort-by="surname">{{
            item.surname
          }}</md-table-cell>
          <md-table-cell md-label="Email" md-sort-by="email">{{
            item.email
          }}</md-table-cell>
        </md-table-row>
      </md-table>
    </md-app-content>
  </md-app>
</template>

<script>
export default {
  name: "Users",
  props: {
    method: { type: Function }
  },
  data() {
    return {
      users: [],
      selected: {}
    };
  },
  mounted() {
    this.refresh();
  },
  methods: {
    refresh() {
      var self = this;
      this.$db.listUsers(this.$user.token, { token: this.$user.token }, (err, usrs) => {
        if (err) alert(err.message);
        else {
          console.log(usrs);
          self.users.splice(0, self.users.length);
          for (var usr of usrs) {
            self.users.push(usr);
          }
          console.log(self.users);
        }
      });
    },
    onSelect(item) {
      this.selected = item;
      console.log("Selected: " + JSON.stringify(this.selected));
      console.log("ITEM ID: " + item.id);
      this.$emit("selected", this.selected);
    }
  }
};
</script>

<style></style>
