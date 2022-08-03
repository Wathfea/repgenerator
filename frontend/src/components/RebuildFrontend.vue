<template>
    <form @submit="generateFromDB" class="bg-white p-10 shadow max-w-screen-2xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
        <div class="space-y-8 divide-y divide-gray-200">
            <div class="mt-6">
                <div class="mt-3 grid grid-cols-12 gap-y-6 gap-x-4 sm:grid-cols-12">
                    <div class="sm:col-span-12">
                        <div class="grid grid-cols-12 gap-4">
                            <div class="col-span-2 mt-1">
                                <label class="text-gray-700 ml-2">Choose which tables you want to rebuild</label>
                            </div>
                            <div class="col-span-10">
                                <select required v-model="whichDomains" class="block focus:ring-indigo-500 focus:border-indigo-500 w-full shadow-sm sm:text-sm border-gray-300 rounded-md" multiple>
                                    <option v-for="model in domains" :value="model.meta">{{ model.model }}</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="pt-5 grid grid-cols-12 gap-4">
            <div class="col-span-6">
                <button
                        class="disabled:bg-slate-50 disabled:text-slate-500 disabled:border-slate-200 disabled:shadow-none block w-full py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-gray-400 hover:bg-gray-500"
                        type="submit"
                        @click="onMainPage">
                    Back
                </button>
            </div>
            <div class="col-span-6">
                <button
                    class="block w-full py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700"
                    type="submit">
                    Rebuild
                </button>
            </div>
        </div>
    </form>

    <Result v-if="generated" :messages="messages"/>
</template>

<script setup>
import Result from "./Result.vue";
import {ref} from "vue";
import axios from "axios";

const whichDomains = ref([]);
const messages = ref([]);
const domains = ref([]);
const generated = ref(false);

axios.get(import.meta.env.VITE_API_URL + '/repgenerator/getGeneratedDomains').then((response) => {
    domains.value = response.data;
})

const onMainPage = () => {
    location.reload()
}

const generateFromDB = (e) => {
    e.preventDefault()

    confirm('All files for the generated fronted will be deleted. Are you sure?')

    let payload = {
        domains: whichDomains.value,
    };

    axios.post(import.meta.env.VITE_API_URL + '/repgenerator/reGenerate', payload).then((response) => {
        messages.value = response.data;
        generated.value = true;
    }).finally(() => {
        location.reload()
    })
}
</script>
