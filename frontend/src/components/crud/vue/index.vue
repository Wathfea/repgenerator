<!-- This example requires Tailwind CSS v2.0+ -->
<template>
    <div class="px-4 sm:px-6 lg:px-8">
        <div class="sm:flex sm:items-center">
            <div class="sm:flex-auto">
                <h1 class="text-xl font-semibold text-gray-900">Users</h1>
            </div>
            <div class="mt-4 sm:mt-0 sm:ml-16 sm:flex-none">
                <button class="inline-flex items-center justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 sm:w-auto"
                        type="button">
                    Add user
                </button>
            </div>
        </div>
        <div class="mt-8 flex flex-col">
            <div class="-my-2 -mx-4 overflow-x-auto sm:-mx-6 lg:-mx-8">
                <div class="inline-block min-w-full py-2 align-middle">
                    <div class="overflow-hidden shadow-sm ring-1 ring-black ring-opacity-5">
                        <table class="min-w-full divide-y divide-gray-300">
                            <thead class="bg-gray-50">
                            <tr>
                                <th v-for="(column,key) in columns" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900"
                                    scope="col">
                                    {{ key }}
                                </th>
                                <th class="relative py-3.5 pl-3 pr-4 sm:pr-6 lg:pr-8" scope="col">
                                    <span class="sr-only">Edit</span>
                                </th>
                            </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                            <tr v-for="model in models" :key="model.id">
                                <td v-for="(column,key) in columns"
                                    class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                    {{ model[column] }}
                                </td>
                                <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6 lg:pr-8">
                                    <a class="text-indigo-600 hover:text-indigo-900" href="#"
                                    >Edit<span class="sr-only">, {{ model.name }}</span></a
                                    >
                                </td>
                            </tr>
                            </tbody>
                        </table>
                        <nav
                            aria-label="Pagination"
                            class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
                            <div class="hidden sm:block">
                                <p class="text-sm text-gray-700">
                                    Showing
                                    {{ ' ' }}
                                    <span class="font-medium">{{ meta.from }}</span>
                                    {{ ' ' }}
                                    to
                                    {{ ' ' }}
                                    <span class="font-medium">{{ meta.to }}</span>
                                    {{ ' ' }}
                                    of
                                    {{ ' ' }}
                                    <span class="font-medium">{{ meta.total }}</span>
                                    {{ ' ' }}
                                    results
                                </p>
                            </div>
                            <div class="flex-1 flex justify-between sm:justify-end">
                                <button :disabled="meta.current_page <= 1" class="disabled:opacity-50 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50" type="button"
                                        @click="prevPage">
                                    Previous
                                </button>
                                <button :disabled="meta.current_page === meta.last_page" class="disabled:opacity-50 ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50" type="button"
                                        @click="nextPage">
                                    Next
                                </button>
                            </div>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import axios from 'axios'
import {ref} from "vue";

const currentPage = ref(1);
const perPage = 5;
const models = ref([]);
const meta = ref({
    current_page: 0,
    last_page: 0,
    from: 0,
    to: 0,
    total: 0
});
const columns = {
    'Id': 'id',
    'Name': 'name',
}
const getPage = (page) => {
    const params = new URLSearchParams({
        page: page,
        per_page: perPage,
    });
    axios.get(import.meta.env.VITE_API_URL + '/api/v1/places?' + params).then((response) => {
        models.value = response.data.data;
        meta.value = response.data.meta;
    })
}
const prevPage = () => {
    getPage(--currentPage.value);
}
const nextPage = () => {
    getPage(++currentPage.value);
}
getPage(1);

</script>
