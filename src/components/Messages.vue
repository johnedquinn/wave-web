<template>
  <md-app>
    <!-- TOOLBAR -->
    <md-app-toolbar class="md-primary">
      <md-button class="md-icon-button" to="/conversations">
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
      <span class="md-title">{{ conversation.name }}</span>
      <md-button class="md-icon-button" @click="move_down">
        <md-icon>arrow_downward</md-icon>
      </md-button>
      <md-button class="md-icon-button" @click="refresh">
        <md-icon>refresh</md-icon>
      </md-button>
      <md-button class="md-icon-button" @click="edit_members">
        <md-icon>group</md-icon>
      </md-button>
    </md-app-toolbar>

    <md-app-content>
      <md-list class="md-double-line md-dense">
        <div :key="message.id" v-for="message in conversation['messages']">
          <md-divider
            class="md-inset"
            v-if="message != conversation['messages'][0]"
          ></md-divider>
          <md-list-item>
            <!-- ICON -->
            <md-avatar
              v-if="
                members[message.author] &&
                  members[message.author].img &&
                  members[message.author].img != ''
              "
              class="md-avatar-icon"
            >
              <img :src="members[message.author].img" />
            </md-avatar>
            <md-avatar v-else class="md-large">
              <md-icon>group</md-icon>
            </md-avatar>

            <div class="md-list-item-text">
              <span>{{ message.content }}</span>
              <p v-if="members[message.author]">
                {{ members[message.author].name }}
                {{ members[message.author].surname }} {{ message.ts }}
              </p>
              <p v-else>Unknown Member {{ message.ts }}</p>
            </div>

            <md-menu md-size="medium" md-direction="bottom-end">
              <md-button v-if="token == message.author" class="md-icon-button">
                <md-icon>delete</md-icon>
              </md-button>
            </md-menu>
          </md-list-item>
        </div>
      </md-list>
    </md-app-content>
  </md-app>
</template>

<script>
import Vue from "vue";
export default {
  name: "Messages",
  props: ["id"],
  data() {
    return {
      conversation: {},
      members: {},
      token: this.$user.token
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
        { id: self.id },
        (err, convs) => {
          if (err) alert(err.message);
          else {
            for (var id in convs[0]) {
              Vue.set(self.conversation, id, convs[0][id]);
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
    move_down() {
      //
      this.$router.push({ path: "/conversation/" + this.id + "/members" });
    },

    edit_members() {
      this.$router.push({ path: "/conversation/" + this.id + "/members" });
    }
  }
};
</script>

<style></style>
