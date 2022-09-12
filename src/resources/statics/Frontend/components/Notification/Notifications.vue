<template>
  <div class="w-full z-50 fixed top-0 right-0">
    <Notification :data="data" v-for="data in notifications" class="animate-notification"/>
  </div>
</template>
<script setup>
    import {useNotifications} from "../../composables/useNotifications.ts";
    import Notification from "./Notification.vue";
    const { notifications } = useNotifications();
</script>
