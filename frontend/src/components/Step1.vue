<script setup>
import {defineEmits, ref, computed} from 'vue'

const emit = defineEmits(['nameChanged', 'iconChanged'])
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

const ucfirstName = computed({
    get() {
        return name.value;
    },
    set(ucName) {
        if(ucName.length < 1) {name.value = ''; return}
        name.value = ucName.replace(/^./, ucName[0].toUpperCase());
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
                <div class="mt-1">
                    <input id="model-name" v-focus v-model="ucfirstName" class="shadow-sm block w-full sm:text-sm border-gray-300 rounded-md" required type="text" @change="emit('nameChanged',name)">
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
