<template>
    <div>
        <index class="bg-white pt-6 shadow mb-5"/>
        <entry class="bg-white p-6 shadow mb-5" :id="1"/>
    </div>
</template>

<script setup>
import Index from "./crud/vue/index.vue";
import Entry from "./crud/vue/entry.vue";

const navigation = [
    { name: 'Users', href: '/users', current: true },
]
</script>
