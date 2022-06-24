<script setup>
import Wizzard from "./components/Wizzard.vue";
import Dashboard from "./components/Dashboard.vue";
import {ref} from "vue";
const choosing = ref(true);
const usingWizzard = ref(false);
const usingDashboard = ref(false);
const chooseWizzard = () => {
    usingWizzard.value = true;
    choosing.value = false;
}
const chooseDashboard = () => {
    usingDashboard.value = true;
    choosing.value = false;
}

import imgUrl from './assets/banner.jpg'

</script>
<template>
    <div>
        <div v-if="choosing">
            <header class="p-5 mb-5 bg-gray-700 text-white text-left">
                <h1 class="navbar-brand">Repository Generator</h1>
            </header>
            <div class="grid grid-cols-12 gap-4 px-5">
                <div class="col-span-6">
                    <img :src="imgUrl" class="banner" alt="Repository Generator Factory" >
                </div>
                <div class="col-span-6">
                    <button @click="chooseDashboard" class="disabled:bg-slate-50 disabled:text-slate-500 disabled:border-slate-200 disabled:shadow-none block w-full py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-gray-400 hover:bg-gray-500">
                        Dashboard
                    </button>

                    <button @click="chooseWizzard" class="mt-3 block w-full py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                        Wizzard
                    </button>
                </div>
            </div>
        </div>
        <div v-else>
            <Wizzard  v-if="usingWizzard" class="bg-white p-10 shadow max-w-screen-2xl mx-auto py-12 px-4 sm:px-6 lg:px-8"/>
            <Dashboard v-else-if="usingDashboard"/>
        </div>
    </div>
</template>
