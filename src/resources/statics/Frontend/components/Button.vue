<template>
  <button
    :class="`${noOpacity ? 'noOpacity' : ''} px-4 py-3 bg-vagheggi-800 border border-transparent font-semibold text-xs text-white uppercase tracking-widest hover:bg-vagheggi-700 disabled:opacity-${noOpacity ? 100 : 50} transition ease-in-out duration-150`"
    :disabled="disabled || busy"
  >
    <svg v-if="busy" :class="`animate-spin m-auto h-${loaderSize} w-${loaderSize} ${ noOpacity ? `text-vagheggi-600` : `text-white`}`" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
    </svg>
    <slot v-else/>
  </button>
</template>
<script setup>
const props = defineProps({
  busy: {
    required: false,
    type: Boolean,
    default: false
  },
  disabled: {
    required: false,
    type: Boolean,
    default: false
  },
  loaderSize : {
    required: false,
    type: String,
    default: '5'
  },
  noOpacity : {
    required: false,
    type: Boolean,
    default: false
  }
})
</script>
<style>
  button.noOpacity[disabled] svg {
    opacity:0.5;
  }
</style>
