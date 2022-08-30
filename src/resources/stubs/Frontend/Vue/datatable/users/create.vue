<script setup lang="ts">
import CMSDashboard from "~/components/CMSDashboard.vue";
import UserCreate from "~/components/User/UserCreate.vue";

definePageMeta({ middleware: ["auth"] });
</script>

<template>
  <NuxtLayout name="app-layout">
    <Head>
      <Title>Vagheggi CMS</Title>
    </Head>
    <CMSDashboard page="users" title="Felhasználók" :pages="[
        {href: '/users', name: 'Felhasználók', current: false, icon: 'UserIcon'},
        {href: '/users/create', name: 'Új felhasználó', current: true, icon: 'UserAddIcon'},
      ]">
      <UserCreate/>
    </CMSDashboard>
  </NuxtLayout>
</template>
