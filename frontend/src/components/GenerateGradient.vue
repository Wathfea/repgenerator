<template>
    <form @submit="generateGradient" class="bg-white p-10 shadow max-w-screen-2xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
        <div class="space-y-8 divide-y divide-gray-200">
            <div class="mt-6">
                <div class="mt-3 grid grid-cols-12 gap-y-6 gap-x-4 sm:grid-cols-12">
                    <div class="sm:col-span-12">
                        <div class="grid grid-cols-12 gap-4">
                            <div class="col-span-6 mt-1">
                                <label class="text-gray-700 ml-2">Choose the start and end colors</label>
                            </div>
                            <div class="col-span-6">
                                <div>
                                    <input type="color" name="hexFrom" v-model="hexFrom">
                                    <label for="head"> Hex From</label>
                                </div>

                                <div>
                                    <input type="color" name="hexTo" v-model="hexTo">
                                    <label for="body"> Hex To</label>
                                </div>
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
                    Generate
                </button>
            </div>
        </div>
    </form>

    <GradientResult v-if="generated" :colors="colors"/>
</template>

<script setup>
import GradientResult from "./GradientResult.vue";
import {ref} from "vue";
import axios from "axios";

const hexFrom = ref('#BC61F3');
const hexTo = ref('#5C43F6');

const colors = ref([]);
const generated = ref(false);

const onMainPage = () => {
    location.reload()
}

const generateGradient = (e) => {
    e.preventDefault()

    let payload = {
        hexFrom: hexFrom.value,
        hexTo: hexTo.value,
    };

    axios.post(import.meta.env.VITE_API_URL + '/repgenerator/generateGradient', payload).then((response) => {
        colors.value = response.data;
        generated.value = true;
    });
}
</script>
