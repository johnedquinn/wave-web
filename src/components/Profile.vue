<template>
<div>
<md-toolbar class="md-primary">
    <md-button to="/conversations"><md-icon>arrow_back</md-icon></md-button>
    <md-avatar><md-icon>person</md-icon></md-avatar>
    <span class="md-title">Profile</span>
    <div class="md-toolbar-section-end"></div>
</md-toolbar>
<div class="form" style="padding: 2em;">
    <md-field>
        <label>Name</label>
        <md-input v-model="user.name"></md-input>
    </md-field>
    <md-field>
        <label>Surname</label>
        <md-input v-model="user.surname"></md-input>
    </md-field>
    <md-field>
        <label>Email</label>
        <md-input v-model="user.email"></md-input>
    </md-field>
    <md-field>
        <label>Password</label>
        <md-input type="password" v-model="user.password"></md-input>
    </md-field>

    <div>
        <md-avatar v-if="user.img" class="md-large">
        <img :src="user.img" />
        </md-avatar>
    </div>

    <input id="file" @change="saveImage" type="file" accept="image/*" style="display:none;" />
    <md-button class="md-primary" @click="openImage">Set Image</md-button>
    <md-button class="md-primary" @click="accept">Accept</md-button>
    <md-button class="md-primary" to="/conversations">Cancel</md-button>
</div>

</div>
</template>

<script>
import Vue from "vue";
export default {
    name: 'Profile',
    data() {
        return {
            user: {
                id: this.$user.id,
                email: this.$user.email,
                name: this.$user.name,
                surname: this.$user.surname,
                img: this.$user.img
            }
        };
    },
    methods: {
        accept() {
            console.log("Profile.accept()");
            this.$db.updateUser(this.$user.token, this.user, (err, usr) => {
                if (err) alert(err.message);
                else {
                    this.$user.email = usr.email;
                    this.$user.name = usr.name;
                    this.$user.surname = usr.surname;
                    this.$user.img = usr.img;
                    this.$router.back();
                }
            });
        },
        openImage(event) {
            console.log("Profile.openImage()");
            document.getElementById("file").click();
        },
        saveImage() {
            console.log("Profile.saveImage()");
            var self = this;
            var element = document.getElementById("file");
            var file = element.files[0];
            var reader = new FileReader();
            reader.onloadend = function() {
                Vue.set(self.user, "img", reader.result);
            };
            reader.readAsDataURL(file);
        }
    }
}
</script>

<style>
</style>