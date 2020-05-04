<template>
  <div id="app" v-if="user.token">
    <router-view/>
  </div>
  <div id="app" v-else>
    <Login></Login>
  </div>
</template>

<script>
import Login from '@/components/Login';
export default {
  name: 'App',
  components: {
    Login
  },
  data: function () {
    return {
      user: this.$user,
    }
  }
}
</script>

<style>
#app {
  font-family: 'Avenir', Helvetica, Arial, sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  text-align: center;
  color: #2c3e50;
  /*margin-top: 60px;*/
}
</style>
