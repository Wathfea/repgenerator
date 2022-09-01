<script setup>
import {defineEmits, ref, computed} from 'vue'
import axios from "axios";

const emit = defineEmits(['nameChanged', 'iconChanged', 'isValidTable'])
const props = defineProps({
    modelName: {
        type: String,
        required: false,
        default: null
    },
    icon: {
        type: String,
        required: false,
        default: null
    }
})
let name = ref(props.modelName);
let icon = ref(props.icon);

const isValidTable = ref(true);

const checkTable = () => {
    emit('nameChanged',name.value)

    axios.get(import.meta.env.VITE_API_URL + '/repgenerator/validateTable/'+name.value).then((response) => {
        isValidTable.value = response.data;

        emit('isValidTable', isValidTable)
    })
}

const ucfirstName = computed({
    get() {
        return name.value;
    },
    set(ucName) {
        if(ucName.length < 1) {name.value = ''; return}
        name.value = ucName.replace(/^./, ucName[0].toUpperCase()).replace(/[^A-Za-z]/, '');
    }
})
</script>

<template>
    <div class="space-y-8 divide-y divide-gray-200">
        <div class="mt-6 grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
            <div class="sm:col-span-12">
                <label class="block text-sm font-medium text-gray-700" for="model-name">
                    Model Name (Singular - Ex. Dog )
                </label>
                <p v-if="!isValidTable" class="mt-2 text-sm text-red-600" id="table-error">This table name is already exists or the name is not singular</p>
                <div class="mt-1">
                    <input id="model-name" v-focus v-model="ucfirstName" class="shadow-sm block w-full sm:text-sm  rounded-md" :class="tableExists ? 'border-red-300 text-red-900 placeholder-red-300 focus:outline-none focus:ring-red-500 focus:border-red-500' : 'border-gray-300' " required type="text" @change="checkTable">
                </div>
                <label class="block text-sm font-medium text-gray-700 mt-1" for="model-name">
                    Hero icon <a target="_blank" href="https://heroicons.com/">https://heroicons.com/</a>
                </label>
                <div class="mt-1">
                    <input id="icon-name" v-model="icon" class="shadow-sm block w-full sm:text-sm border-gray-300 rounded-md" required type="text" @change="emit('iconChanged',icon)">
                </div>
            </div>
        </div>
    </div>
</template>
