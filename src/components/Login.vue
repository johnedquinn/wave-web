
<template>
    <md-app>
      
      <md-app-content v-if="mode == 'login'">
      <form class="md-layout md-alignment-center-center">
      <md-card class="md-layout-item md-size-60 md-small-size-80">
        <md-card-header>
          <div class="md-title">Wave Login</div>
        </md-card-header>

        <md-card-content>
          <div class="md-layout md-gutter">
            <div class="md-layout-item md-small-size-100">
              <md-field>
                <label>Email</label>
                <md-input v-model="user.email" autofocus></md-input>
              </md-field>
            </div>
          </div>
          <div class="md-layout md-gutter">
            <div class="md-layout-item md-small-size-100">
              <md-field>
                <label>Password</label>
                <md-input type="password" v-model="user.password"></md-input>
              </md-field>
            </div>
          </div>
        </md-card-content>

        <md-card-actions>
          <md-button type="submit" class="md-primary" @click="mode = 'register'">Register</md-button>
          <md-button type="submit" class="md-primary md-raised" @click="enter">Login</md-button>
        </md-card-actions>
      </md-card>
      </form>
      <!--</div>-->
      </md-app-content>

      </md-app-content>
      <md-app-content v-else>
      <form class="md-layout md-alignment-center-center">
      <md-card class="md-layout-item md-size-60 md-small-size-80">
        <md-card-header>
          <div class="md-title">Wave Register</div>
        </md-card-header>

        <md-card-content>
          <md-avatar class="md-avatar-icon md-medium">
            <md-icon>perm_identity</md-icon>
          </md-avatar>

          <div class="md-layout md-gutter">
            <div class="md-layout-item md-small-size-100">
              <md-field>
                <label>First Name</label>
                <md-input v-model="user.name" autofocus></md-input>
              </md-field>
            </div>
          </div>
          <div class="md-layout md-gutter">
            <div class="md-layout-item md-small-size-100">
              <md-field>
                <label>Last Name</label>
                <md-input v-model="user.surname"></md-input>
              </md-field>
            </div>
          </div>
          <div class="md-layout md-gutter">
            <div class="md-layout-item md-small-size-100">
              <md-field>
                <label>Email</label>
                <md-input v-model="user.email"></md-input>
              </md-field>
            </div>
          </div>
          <div class="md-layout md-gutter">
            <div class="md-layout-item md-small-size-100">
              <md-field>
                <label>Password</label>
                <md-input type="password" v-model="user.password"></md-input>
              </md-field>
            </div>
          </div>
        </md-card-content>

        <md-card-actions>
          <md-button class="md-primary" @click="mode = 'login'">Cancel</md-button>
          <md-button class="md-primary md-raised" @click="register">Register</md-button>
        </md-card-actions>
      </md-card>
      </form>

      </md-app-content>
    </md-app>
</template>

<script>
import Vue from 'vue';
export default {
  name: 'Login',
  data: function () {
    return {
      mode: 'login',
      user: {
        name: "",
        surname: "",
        email: "",
        img: "",
        password: ""
      },
      msg: ''
    }
  },
  methods: {
    /// @func: enter
    /// @desc: used to login the user
    enter () {
      console.log("Login Page: Enter()");
      var self = this;
      this.$db.login(this.user.email, this.user.password, function(err, token, user) {
        if (err) alert(err.message);
        else {
          Vue.set(self.$user, 'token', token);
          for (var att in user) Vue.set(self.$user, att, user[att]);
        }
      });
    },
    /// @func: register
    /// @desc: used to register the user
    register () {
      console.log("Login Page: Register()");
      var self = this;
      this.$db.addUser(this.user, function(err, user) {
        if (err) alert(err.message);
        else {
          Vue.set(self.$user, 'token', user.id);
          for (var att in user) Vue.set(self.$user, att, user[att]);
        }
      });
    }
  }
}
</script>

<style>
.screen {
  height: 100vh;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 0;
  margin: 0;
}
</style>