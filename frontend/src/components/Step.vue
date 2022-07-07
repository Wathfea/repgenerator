<script setup>
defineProps({
    index: {
        type: Number,
        required: true
    },
    title: {
        type: String,
        required: true,
    },
    current: {
        type: Boolean,
        required: false,
        default: false
    },
    complete: {
        type: Boolean,
        required: false,
        default: false
    },
    last: {
        type: Boolean,
        required: false,
        default: false
    }
})

</script>
<template>
    <li v-if="complete" class="relative md:flex-1 md:flex">
        <a class="group flex items-center w-full">
            <span class="px-6 py-4 flex items-center text-sm font-medium">
              <span
                  class="flex-shrink-0 w-10 h-10 flex items-center justify-center bg-indigo-600 rounded-full group-hover:bg-indigo-800">
                <svg aria-hidden="true" class="w-6 h-6 text-white" fill="currentColor"
                     viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                  <path clip-rule="evenodd"
                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                        fill-rule="evenodd"/>
                </svg>
              </span>
              <span class="ml-4 text-sm font-medium text-gray-900">{{ title }}</span>
            </span>
        </a>
        <div aria-hidden="true" class="hidden md:block absolute top-0 right-0 h-full w-5">
            <svg class="h-full w-full text-gray-300" fill="none" preserveAspectRatio="none" viewBox="0 0 22 80">
                <path d="M0 -2L20 40L0 82" stroke="currentcolor" stroke-linejoin="round"
                      vector-effect="non-scaling-stroke"/>
            </svg>
        </div>
    </li>
    <li v-else-if="current" class="relative md:flex-1 md:flex">
        <a id="scroll-anchor" aria-current="step" class="px-6 py-4 flex items-center text-sm font-medium" href="#">
    <span class="flex-shrink-0 w-10 h-10 flex items-center justify-center border-2 border-indigo-600 rounded-full">
      <span class="text-indigo-600">{{ index }}</span>
    </span>
            <span class="ml-4 text-sm font-medium text-indigo-600">{{ title }}</span>
        </a>
        <div v-if="!last" aria-hidden="true" class="hidden md:block absolute top-0 right-0 h-full w-5">
            <svg class="h-full w-full text-gray-300" fill="none" preserveAspectRatio="none" viewBox="0 0 22 80">
                <path d="M0 -2L20 40L0 82" stroke="currentcolor" stroke-linejoin="round"
                      vector-effect="non-scaling-stroke"/>
            </svg>
        </div>
    </li>
    <li v-else class="relative md:flex-1 md:flex">
        <a class="group flex items-center" href="#">
    <span class="px-6 py-4 flex items-center text-sm font-medium">
      <span
          class="flex-shrink-0 w-10 h-10 flex items-center justify-center border-2 border-gray-300 rounded-full group-hover:border-gray-400">
        <span class="text-gray-500 group-hover:text-gray-900">{{ index }}</span>
      </span>
      <span class="ml-4 text-sm font-medium text-gray-500 group-hover:text-gray-900">{{ title }}</span>
    </span>
        </a>
        <div v-if="!last" aria-hidden="true" class="hidden md:block absolute top-0 right-0 h-full w-5">
            <svg class="h-full w-full text-gray-300" fill="none" preserveAspectRatio="none" viewBox="0 0 22 80">
                <path d="M0 -2L20 40L0 82" stroke="currentcolor" stroke-linejoin="round"
                      vector-effect="non-scaling-stroke"/>
            </svg>
        </div>
    </li>
</template>
