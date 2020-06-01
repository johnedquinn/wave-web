<template>
    <md-app>
      
      <md-app-toolbar class="md-primary">
        <md-button class="md-icon-button" to="/conversations">
            <md-icon>arrow_back</md-icon>
        </md-button>
        <md-avatar v-if="conversation.img && conversation.img != ''" class="md-avatar-icon">
          <img :src="conversation.img" />
        </md-avatar>
        <md-avatar v-else class="md-large">
          <md-icon>perm_identity</md-icon>
        </md-avatar>
        <span class="md-title">Conversation</span>
      </md-app-toolbar>

      <md-app-content>
        <div class="form" style="padding: 2em;">
          <md-field>
              <label>Name</label>
              <md-input v-model="conversation.name"></md-input>
          </md-field>
          <md-avatar v-if="conversation.img && conversation.img != ''" class="md-avatar-icon">
            <img :src="conversation.img" />
          </md-avatar>
          <input id="file" @change="saveImage" type="file" accept="image/*" style="display:none;" />
          <md-button class="md-primary" @click="openImage">Set Image</md-button>
          <md-button class="md-primary" @click="accept">Accept</md-button>
          <md-button class="md-primary" to="/conversations">Cancel</md-button>
        </div>
      </md-app-content>
    </md-app>
</template>

<script>
import Vue from 'vue';
export default {
  name: 'Conversation',
  props: ["id"],
  data: function () {
    return { 
      conversation: {}
    }
  },
  mounted() {
    if (this.id) {
      this.getConvInfo();
    }
 },
  methods: {
    getConvInfo () {
      this.$db.listConversations(
        this.$user.token,
        { id: this.id },
        (err, convs) => {
          if (err) alert(err.message);
          else {
            for (var id in convs[0]) {
              console.log("RECEIVED FROM LIST CONVS: " + JSON.stringify(convs));
              Vue.set(this.conversation, id, convs[0][id]);
            }
          }
        }
      );
    },
    accept() {
            console.log("Conversation.accept()");
            this.$db.updateConversation(this.$user.token, this.conversation, (err, conv) => {
                if (err) alert(err.message);
                else {
                    this.$router.back();
                }
            });
        },
        openImage(event) {
            console.log("Conversation.openImage()");
            document.getElementById("file").click();
        },
        saveImage() {
            console.log("Conversation.saveImage()");
            var self = this;
            var element = document.getElementById("file");
            var file = element.files[0];
            var reader = new FileReader();
            reader.onloadend = function() {
                Vue.set(self.conversation, "img", reader.result);
            };
            reader.readAsDataURL(file);
        }
  }
}
</script>

<style>
</style>