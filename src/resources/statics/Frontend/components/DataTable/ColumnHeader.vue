<template>
  <div>
    <label class="block text-sm font-medium text-gray-700">{{ getName() }}</label>
    <div class="mt-1 rounded-md">
        <Button @click="onOpenSearch" class="-ml-px relative inline-flex items-center space-x-2 px-1 py-2 rounded-l-md border border-gray-300 text-sm font-medium text-gray-700 bg-gray-50 hover:bg-gray-100">
          <SearchCircleOutlineIcon v-if="!search" class="h-5 w-5 text-gray-400" aria-hidden="true"/>
          <SearchCircleIcon v-else class="h-5 w-5 text-repgenerator-800" aria-hidden="true"/>
        </Button>
        <Button :disabled="!search && sortDirection === null" :no-opacity="true" :busy="isSearching"  @click="onSearchAndSortCleared" class="-ml-px relative inline-flex items-center space-x-2 px-1 py-2 border border-gray-300 text-sm font-medium text-gray-700 bg-gray-50 hover:bg-gray-100">
          <XIcon :class="`h-5 w-5 text-${search !== '' || sortDirection !== null ? 'repgenerator-800' : 'gray-400'}`" aria-hidden="true"/>
        </Button>
        <Button @click="onSortChanged" type="button" class="-ml-px relative inline-flex items-center space-x-2 px-1 rounded-r-md py-2 border border-gray-300 text-sm font-medium text-gray-700 bg-gray-50 hover:bg-gray-100">
          <SortAscendingIcon v-if="sortDirection === 'asc'" class="h-5 w-5 text-repgenerator-800" aria-hidden="true" />
          <SortDescendingIcon v-else :class="`h-5 w-5 text-${sortDirection === 'desc' ? 'repgenerator-800' : 'gray-400'}`" aria-hidden="true" />
        </Button>
    </div>
  </div>
</template>

<script setup>
import { SortAscendingIcon, SortDescendingIcon, XIcon, SearchCircleIcon } from '@heroicons/vue/solid'
import { SearchCircleIcon as SearchCircleOutlineIcon }  from '@heroicons/vue/outline'
import Button from "../Button.vue";
const emit = defineEmits(["toggleSort", "openSearch", "clearSortAndSearch"]);
let props = defineProps({
  data : {
    required : true,
  },
  search : {
    required : false,
    default: ''
  },
  isSearching : {
    required: false,
    type: Boolean,
    default: false
  },
  sortDirection : {
    required : false,
    default: null
  },
  column : {
    required: true,
    type: String
  },
})
const onSortChanged = () => {
  emit('toggleSort', props.column);
}
const onOpenSearch = () => {
  emit('openSearch', props.column);
}
const onSearchAndSortCleared = (e) => {
  emit('clearSortAndSearch', props.column);
}
const getName = () => {
  if ( props.data.name ) {
    return props.data.name;
  }
  return props.data;
}
</script>
<style src="@vueform/multiselect/themes/default.css"></style>
<style>
  .column-multiselect {
    border-bottom-right-radius: 0 !Important;
    border-top-right-radius: 0 !Important;
  }
  .multiselect-tag, .multiselect-spinner{
    background:#3b968e;
    color:white;
  }
</style>
