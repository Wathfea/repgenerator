<script setup lang="ts">
import CMSDashboard from "~/components/CMSDashboard.vue";
import UserEdit from "~/components/User/UserEdit.vue";
import {useRoute} from "vue-router/dist/vue-router";
const currentRoute = useRoute();
definePageMeta({ middleware: ["auth"] });
</script>

<template>
  <NuxtLayout name="app-layout">
    <Head>
      <Title>Vagheggi CMS</Title>
    </Head>
    <CMSDashboard page="users" title="" :pages="[
        {href: '/users', name: 'Felhasználók', current: false, icon: 'UserIcon'},
        {href: '/users/' + currentRoute.params.id, name: 'Felhazsnáló szerkesztése', current: true, icon: 'UserCircleIcon'},
      ]">
      <UserEdit/>
    </CMSDashboard>
  </NuxtLayout>
</template>
