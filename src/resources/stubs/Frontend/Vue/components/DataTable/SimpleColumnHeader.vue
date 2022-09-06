<template>
  <div class="group inline-flex">
    {{ getName() }}
    <span class="ml-2 flex-none rounded bg-gray-200 text-gray-900 group-hover:bg-gray-300">
      <ChevronDownIcon class="h-5 w-5" aria-hidden="true" />
    </span>
  </div>
</template>
<script setup>
import { ChevronDownIcon } from '@heroicons/vue/solid'
const props = defineProps({
  data : {
    required : true,
  },
  icon : {
    required : false,
    type: String,
    default: 'SearchIcon'
  },
  sortAscending : {
    required : false,
    type: Boolean,
    default: false
  },
  column : {
    required: true,
    type: String
  }
})
const getName = () => {
  if ( props.data.name ) {
    return props.data.name;
  }
  return props.data;
}
</script>