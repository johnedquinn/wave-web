<template>
  <md-app>
    <!-- TOOLBAR -->
    <md-app-toolbar class="md-primary">
      <md-button class="md-icon-button" @click="go_to_messages">
        <md-icon>arrow_back</md-icon>
      </md-button>
      <md-avatar
        v-if="conversation.img && conversation.img != ''"
        class="md-avatar-icon"
      >
        <img :src="conversation.img" />
      </md-avatar>
      <md-avatar v-else class="md-large">
        <md-icon>perm_identity</md-icon>
      </md-avatar>
      <span class="md-title">Members</span>
      <md-button class="md-icon-button" @click="add_member">
        <md-icon>person_add</md-icon>
      </md-button>
    </md-app-toolbar>

    <md-app-content>
      <md-list class="md-double-line md-dense">
        <div :key="member.id" v-for="member in members">
          <md-list-item>
            <!-- ICON -->
            <md-avatar
              v-if="member.img && member.img != ''"
              class="md-avatar-icon"
            >
              <img :src="member.img" />
            </md-avatar>
            <md-avatar v-else class="md-large">
              <md-icon>group</md-icon>
            </md-avatar>

            <div class="md-list-item-text">
              <p>
                {{ member.name }}
                {{ member.surname }}
              </p>
            </div>
          </md-list-item>
        </div>
      </md-list>

      <!-- Dialog -->
      <md-dialog :md-active.sync="selectUser">
        <md-dialog-title>Select user</md-dialog-title>
        <!--<Users :method="joinUser"></Users>-->
        <Users @selected="joinUser"></Users>
        <md-dialog-actions>
          <md-button class="md-primary" @click="selectUser = false">
            Close
          </md-button>
        </md-dialog-actions>
      </md-dialog>
    </md-app-content>
  </md-app>
</template>

<script>
import Vue from "vue";
import Users from "@/components/Users";
export default {
  name: "Members",
  props: ["id"],
  components: {
    Users
  },
  data() {
    return {
      conversation: {},
      members: {},
      token: this.$user.token,
      selectUser: false
    };
  },
  mounted() {
    this.refresh();
  },
  methods: {
    refresh() {
      var self = this;
      this.$db.listConversations(
        this.$user.token,
        { },
        (err, convs) => {
          if (err) alert(err.message);
          else {
            for (var conv in convs) {
              console.log("conv:" + conv + "; id: " + convs[conv].id);
              if (convs[conv].id == self.id)
                for (var id in convs[conv]) {
                Vue.set(self.conversation, id, convs[conv][id]);
                }
            }
          }
        }
      );
      console.log(JSON.stringify(self.conversation["members"]));

      for (var id of self.conversation["members"]) {
        console.log(id);
        this.$db.listUsers(this.$user.token, { id: id }, (err, member) => {
          if (err) alert(err.message);
          else {
            console.log(member);
            Vue.set(self.members, member[0]["id"], member[0]);
          }
        });
      }
      console.log(JSON.stringify(this.members));
    },
    add_member() {
      this.selectUser = true;
    },
    go_to_messages() {
      this.$router.push({ path: "/conversation/" + this.id + "/messages" });
    },
    joinUser(value) {
      console.log("joinUser: ", value);
      this.$db.joinConversation(
        this.$user.token,
        this.id,
        value.id,
        (err, convs) => {
          if (err) alert("Error: " + err.message);
          else {
            this.refresh();
          }
        }
      );
    }
  }
};
</script>

<style></style>
