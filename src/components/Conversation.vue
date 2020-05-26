<template>
    <md-app>
      <md-app-toolbar class="md-primary">
        <span class="md-title">Conversation</span>
      </md-app-toolbar>
      <md-app-content>
        <h1>Conversation #{{ id }}</h1>
        <md-button class="md-raised" @click="login">Login</md-button>
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
 this.$db.listConversations(
 this.$user.token,
 { id: this.id },
 (err, convs) => {
 if (err) alert(err.message);
 else {
 for (var id in convs[0]) {
 Vue.set(this.conversation, id, convs[0][id]);
 }
 }
 }
 );
 }
 },
  methods: {
    login() {
      this.$db.login('john', 'john', (err, token, user) => {
        if (err) this.msg = err.message;
        else this.msg = JSON.stringify(user);
      });
    }
  }
}
</script>

<style>
</style>