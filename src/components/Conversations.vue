<template>
    <md-app>
      <md-app-toolbar class="md-primary">
        <h3 class="md-title">Conversations</h3>
        <md-button class="md-icon-button" @click="refresh">
            <md-icon>refresh</md-icon>
        </md-button>
        <md-button class="md-icon-button" to="/profile">
            <md-icon>account_circle</md-icon>
        </md-button>
        <md-button class="md-icon-button" @click="logout">
            <md-icon>logout</md-icon>
        </md-button>
      </md-app-toolbar>

      <md-app-content>
      <md-list class="md-double-line md-dense">
        <div :key="conv.id" v-for="conv in conversations">
        <md-divider class="md-inset" v-if="conv != conversations[0]"></md-divider>
        <md-list-item>

            <!-- ICON -->
            <md-avatar v-if="conv.img && conv.img != ''" class="md-avatar-icon">
              <img :src="conv.img" />
            </md-avatar>
            <md-avatar v-else class="md-large">
              <md-icon>group</md-icon>
            </md-avatar>

            <div class="md-list-item-text" @click="open(conv.id)">
                <span>{{ conv.name }}</span>
                <p v-if="conv.messages[conv.messages.length - 1].content.length > 35">
                    {{ conv.messages[conv.messages.length - 1].content.substring(0, 35) + '...'}}
                </p>
                <p v-else>
                    {{ conv.messages[conv.messages.length - 1].content }}
                </p>
            </div>

    <md-menu md-size="medium" md-direction="bottom-end">
      <md-button class="md-icon-button" md-menu-trigger>
        <md-icon>more_vert</md-icon>
      </md-button>

      <md-menu-content>
        <md-menu-item @click="edit(conv.id)">
          <span>Edit</span>
        </md-menu-item>
        <md-menu-item @click="leave(conv.id)">
          <span>Leave</span>
        </md-menu-item>
      </md-menu-content>
    </md-menu>
        </md-list-item>
        </div>
      </md-list>
      </md-app-content>

    </md-app>
</template>

<script>
import Vue from 'vue';
export default {
    name: 'Conversations',
    data() {
        return {
            conversations: [],
            toggleCard: false
        };
    },
    mounted() {
        this.refresh();
    },
    methods: {
        refresh() {
            console.log("Conversations File: Refresh()");
            var self = this;
            this.conversations.splice(0, this.conversations.length);
            this.$db.listConversations(this.$user.token, { members: this.$user.token }, (err, convs) => {
                if (err) alert("Error: " + err.message);
                else {
                    convs.forEach(conv => { self.conversations.push(conv); });
                }
            });
        },
	    open_profile () {
			console.log("Opening Profile()");
			var self = this;
		},
        logout () {
            console.log("Conversations File: Logout()");
            console.log("User = " + JSON.stringify(this.$user));
            Vue.delete(this.$user, 'token');
            console.log("User = " + JSON.stringify(this.$user));
        },
        toggle () {
            this.toggleCard = !this.toggleCard
        },
        open (id) {
            console.log("Conversations File: Open(" + id + ")");
            this.$router.push({path: '/conversation/' + id + '/messages'});
        },
        edit (id) {
            console.log("Conversations File: Edit(" + id + ")");
            this.$router.push({path: '/conversation/' + id });
        },
        leave (id) {
            console.log("Conversations File: Leave(" + id + ")");
        }
    }
}
</script>

<style>
</style>
