<script setup lang="ts">
import CMSDashboard from "~/components/CMSDashboard.vue";
import UserIndex from "~/components/User/UserIndex.vue";

definePageMeta({ middleware: ["auth"] });
</script>

<template>
  <NuxtLayout name="app-layout">
    <Head>
      <Title>Vagheggi CMS</Title>
    </Head>
    <CMSDashboard page="users" title="Felhasználók" :pages="[{href: '/users', name: 'Felhasználók', current: true, icon: 'UserIcon'}]">
      <UserIndex/>
    </CMSDashboard>
  </NuxtLayout>
</template>
