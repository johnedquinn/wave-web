import Vue from 'vue'
import Router from 'vue-router'
import Login from '@/components/Login'
import Conversations from '@/components/Conversations'
import Conversation from '@/components/Conversation'
import Messages from '@/components/Messages'
import Members from '@/components/Members'
import Profile from '@/components/Profile'

Vue.use(Router)

export default new Router({
  routes: [
    {
      path: '/',
      name: 'Conversations_Root',
      component: Conversations
    },
    {
      path: '/conversations',
      name: 'Conversations',
      component: Conversations
    },

    {
      path: '/conversation',
      name: 'Conversation',
      component: Conversation
    },
    {
      path: '/conversation/:id',
      name: 'Conversation_id',
      component: Conversation
    },
    {
      path: '/conversation/:id/messages',
      name: 'Messages',
      component: Messages
    },
    {
      path: '/conversation/:id/members',
      name: 'Members',
      component: Members
    },
    {
      path: '/profile',
      name: 'Profile',
      component: Profile
    },
    {
      path: '/login',
      name: 'Login',
      component: Login
    },
    {
      path: '/*',
      name: 'Conversations_Wildcard',
      component: Conversations
    },
  ]
})
