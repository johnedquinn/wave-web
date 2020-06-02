<template>
  <div>
  <md-app style="height: 87vh;">

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
      <md-avatar v-else class="md-medium">
        <md-icon>perm_identity</md-icon>
      </md-avatar>
      <span class="md-title">{{ conversation.name }}</span>
      <md-button class="md-icon-button" @click="scrollToBottom">
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
      <md-list class="md-double-line md-dense msgList">
        <div :key="message.id" v-for="message in messages">
          <md-divider
            class="md-inset"
            v-if="message != messages[0]"
          ></md-divider>
          <md-list-item>
            <!-- ICON -->
            <!--<md-avatar
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
            </md-avatar>-->

            <div class="md-list-item-text">
              <span>{{ message.content }}</span>
              <!--<p v-if="members[message.author]">
                {{ members[message.author].name }}
                {{ members[message.author].surname }} {{ message.ts }}
              </p>
              <p v-else>Unknown Member {{ message.ts }}</p>-->
            </div>

            <md-menu md-size="medium" md-direction="bottom-end">
              <md-button v-if="token == message.author" class="md-icon-button" @click="deleteMessage(message.id)">
                <md-icon>delete</md-icon>
              </md-button>
            </md-menu>
          </md-list-item>
        </div>
      </md-list>



    </md-app-content>



  </md-app>
      <div class="form bottom-bar">
          <md-field>
              <label>Message</label>
              <md-input v-model="message"></md-input>
          </md-field>
    <md-button class="md-fab md-mini" @click="sendMessage">
      <md-icon>send</md-icon>
    </md-button>
      </div>
  </div>
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
      messages: [],
      token: this.$user.token,
      message: ''
    };
  },
  mounted() {
    this.refresh();
  },
  methods: {
    refresh() {
      var self = this;

      // Get Conversation
      this.$db.listConversations(
        this.$user.token,
        { },
        (err, convs) => {
          if (err) alert(err.message);
          else {
            console.log("Convs: " + JSON.stringify(convs));
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

      /*
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
      console.log(JSON.stringify(this.members));*/

      // Get Messages List
      this.$db.listMessages(this.$user.token, this.id, 0, 0, (err, msgs) => {
        if (err) alert(err.message);
        else {
            this.messages.splice(0, this.messages.length);
            console.log("Messages: " + JSON.stringify(msgs));
            msgs.forEach(msg => { self.messages.push(msg); });
        }
      });

      // Scroll to Last Message
      self.scrollToBottom();
    },
    move_down() {
      //
      this.$router.push({ path: "/conversation/" + this.id + "/members" });
    },

    edit_members() {
      this.$router.push({ path: "/conversation/" + this.id + "/members" });
    },

    deleteMessage (msgId) {
      this.$db.removeMessage(this.$user.token, this.id, msgId, (err) => {
        if (err) console.log(err.message);
      });
    },

    scrollToBottom () {
      console.log('Scroll To Bottom()');
      const el = this.$el.getElementsByClassName('msgList')[0]['childNodes'][this.messages.length - 1];

      if (el) {
        el.scrollIntoView();
      }
    },

    sendMessage () {
      var self = this;
      if (this.message == '') return;
      this.$db.addMessage(this.$user.token, this.id, this.message, (err, msg) => {
        if (err) console.log(err.message);
        else {
          self.message = '';
          self.refresh();
        }
      });
    }
  }
};
</script>

<style>
.bottom-bar {
  position: fixed;
  bottom: 0;
  background-color: white;
  height: 13vh;
  width: 100%;
  display: flex;
  flex-direction: row;
  padding: 2%;
}
</style>
