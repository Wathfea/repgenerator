<script setup>
import {defineEmits, ref} from 'vue'

const emit = defineEmits(['nameChanged'])
const props = defineProps({
    modelName: {
        type: String,
        required: false,
        default: null
    }
})
let name = ref(props.modelName);
</script>

<template>
    <div class="space-y-8 divide-y divide-gray-200">
        <div class="mt-6 grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
            <div class="sm:col-span-12">
                <label class="block text-sm font-medium text-gray-700" for="model-name">
                    Model Name (Singular - Ex. Dog )
                </label>
                <div class="mt-1">
                    <input id="model-name" v-model="name" class="shadow-sm block w-full sm:text-sm border-gray-300 rounded-md" required type="text"
                           @change="emit('nameChanged',name)">
                </div>
            </div>
        </div>
    </div>
</template>
