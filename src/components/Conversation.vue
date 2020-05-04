<template>
    <md-app>
      <md-app-toolbar class="md-primary">
        <span class="md-title">Conversation</span>
      </md-app-toolbar>
      <md-app-content>
        <h1>Hello, World!</h1>
        <md-button class="md-raised" @click="login">Login</md-button>
        <p>{{ msg }}</p>
      </md-app-content>
    </md-app>
</template>

<script>
export default {
  name: 'Conversation',
  data: function () {
    return {
        msg: ''
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