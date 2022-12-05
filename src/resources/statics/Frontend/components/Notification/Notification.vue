<template>
  <div aria-live="assertive" class="inset-0 flex items-end px-4 py-2 pointer-events-none sm:p-6 sm:items-start w-full">
    <div class="w-full flex flex-col items-center space-y-4 sm:items-end">
      <transition enter-active-class="transform ease-out duration-300 transition" enter-from-class="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2" enter-to-class="translate-y-0 opacity-100 sm:translate-x-0" leave-active-class="transition ease-in duration-100" leave-from-class="opacity-100" leave-to-class="opacity-0">
        <div v-if="show" class="max-w-sm w-full bg-white shadow-lg rounded-lg pointer-events-auto ring-1 ring-black ring-opacity-5 overflow-hidden">
          <div class="p-4">
            <div class="flex items-start">
              <div class="flex-shrink-0">
                <CheckCircleIcon v-if="data.type === 'success'" class="h-6 w-6 text-green-400" aria-hidden="true" />
                <ExclamationCircleIcon v-else-if="data.type === 'warning'" class="h-6 w-6 text-orange-400" aria-hidden="true" />
                <InformationCircleIcon v-else-if="data.type === 'info'" class="h-6 w-6 text-blue-400" aria-hidden="true" />
              </div>
              <div class="ml-3 w-0 flex-1 pt-0.5">
                <p class="text-sm font-medium text-gray-900">{{ data.title }}</p>
                <p v-if="data.description" class="mt-1 text-sm text-gray-500">{{data.description }}</p>
              </div>
              <div class="ml-4 flex-shrink-0 flex">
                <button type="button" @click="show = false" class="bg-white rounded-md inline-flex text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                  <span class="sr-only">Close</span>
                  <XIcon class="h-5 w-5" aria-hidden="true" />
                </button>
              </div>
            </div>
          </div>
        </div>
      </transition>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { CheckCircleIcon, ExclamationCircleIcon, InformationCircleIcon } from '@heroicons/vue/outline'
import { XIcon } from '@heroicons/vue/solid'
const show = ref(true)
const props = defineProps({
  data : {
    required : true,
    type: Object
  }
})
</script>
