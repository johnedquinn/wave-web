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
      <span class="md-title">Members</span>
      <md-button class="md-icon-button" @click="add_member">
        <md-icon>person_add</md-icon>
      </md-button>
    </md-app-toolbar>

    <md-app-content>
        
      <md-list class="md-double-line md-dense">
        <div :key="member.id" v-for="member in members">
          <!--<md-divider
            class="md-inset"
            v-if="message != conversation['messages'][0]"
          ></md-divider>-->
          <md-list-item>
            <!-- ICON -->
            <md-avatar
              v-if="
                  member.img &&
                  member.img != ''
              "
              class="md-avatar-icon"
            >
              <img :src="member.img" />
            </md-avatar>
            <md-avatar v-else class="md-large">
              <md-icon>group</md-icon>
            </md-avatar>

            <div class="md-list-item-text">
              <!--<span>{{ message.content }}</span>-->
              <p>
                {{ member.name }}
                {{ member.surname }}
              </p>
            </div>

            <!--<md-menu md-size="medium" md-direction="bottom-end">
              <md-button v-if="token == message.author" class="md-icon-button">
                <md-icon>delete</md-icon>
              </md-button>
            </md-menu>-->
          </md-list-item>
        </div>
      </md-list>
    </md-app-content>
  </md-app>
</template>

<script>
import Vue from "vue";
export default {
  name: "Members",
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
        { id: self.token },
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
    add_member() {}
  }
};
</script>

<style></style>
