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
            <md-avatar class="md-avatar-icon" v-if="conv.img == ''">
                <md-icon>group</md-icon>
            </md-avatar>
            <md-avatar v-else>
                <img src="" alt="Conversation Image">
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

            <!--<md-button class="md-icon-button md-list-action">
                <md-icon class="md-primary" style="color: black">more_vert</md-icon>
            </md-button>-->
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
            this.$db.listConversations(this.$user.token, {}, (err, convs) => {
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
            //Vue.set(self.$user, 'token', null);
            Vue.delete(this.$user, 'token');
            //this.$user = {};
            console.log("User = " + JSON.stringify(this.$user));
        },
        toggle () {
            this.toggleCard = !this.toggleCard
        },
        open (id) {
            console.log("Conversations File: Open(" + id + ")");
        },
        edit (id) {
            console.log("Conversations File: Edit(" + id + ")");
        },
        leave (id) {
            console.log("Conversations File: Leave(" + id + ")");
        }
    }
}
</script>

<style>
</style>
