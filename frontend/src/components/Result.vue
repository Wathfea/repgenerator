<script setup>
const props = defineProps({
    messages : {
        type : Array,
        required : false,
        default : () => {
            return []
        }
    }
})
</script>

<template>
    <div class="bg-white shadow overflow-hidden sm:rounded-md mt-6">
        <ul role="list" class="divide-y divide-gray-200">
            <li v-for="message in messages">
                <span class="block hover:bg-gray-50">
                    <div class="px-4 py-4 sm:px-6">
                        <div class="flex items-center justify-between">
                            <code v-if="message['code'] !== undefined" class="block whitespace-pre overflow-x-scroll text-white p-3 bg-gray-700">
                                {{ message['code'] }}
                            </code>
                            <p v-else class="text-sm font-medium text-indigo-600 truncate">
                                {{ message }}
                            </p>
                        </div>
                    </div>
                </span>
            </li>
        </ul>
    </div>
</template>
