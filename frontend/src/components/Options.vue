<script setup>
const props = defineProps({
    options: {
        type: Object,
        required: false,
        default: () => {
            return {}
        }
    }
})
</script>
<template>
    <div class="mt-3">
        <div v-for="option in options" class="mt-3 text-sm">
            <div class="relative flex items-start">
                <div class="flex items-center h-5">
                    <input v-model="option.enabled" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded"
                           type="checkbox">
                </div>
                <div class="ml-3 text-sm">
                    <label class="font-medium text-gray-700">{{ option.label }}</label>
                    <p class="text-gray-500">{{ option.text }}</p>
                </div>
            </div>
        </div>
    </div>
</template>
