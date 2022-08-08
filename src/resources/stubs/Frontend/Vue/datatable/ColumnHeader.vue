<template>
  <div>
    <SearchColumnPopup v-if="setSearchOpen" :data="data" :icon="icon" :set-search="search" :name="getName()" @close="onCloseSearch" @search="onSearch"/>
    <label for="email" class="block text-sm font-medium text-gray-700">{{ getName() }}</label>
    <div class="mt-1 rounded-md">
        <Button @click="onOpenSearch" class="-ml-px relative inline-flex items-center space-x-2 px-1 py-2 rounded-l-md border border-gray-300 text-sm font-medium text-gray-700 bg-gray-50 hover:bg-gray-100">
          <SearchCircleOutlineIcon v-if="!search" class="h-5 w-5 text-gray-400" aria-hidden="true"/>
          <SearchCircleIcon v-else class="h-5 w-5 text-vagheggi-800" aria-hidden="true"/>
        </Button>
        <Button :disabled="!search && sortDirection === null" :no-opacity="true" :busy="data.isSearching"  @click="onSearchAndSortCleared" class="-ml-px relative inline-flex items-center space-x-2 px-1 py-2 border border-gray-300 text-sm font-medium text-gray-700 bg-gray-50 hover:bg-gray-100">
          <XIcon :class="`h-5 w-5 text-${search !== '' || sortDirection !== null ? 'vagheggi-800' : 'gray-400'}`" aria-hidden="true"/>
        </Button>
        <Button @click="onSortChanged" type="button" class="-ml-px relative inline-flex items-center space-x-2 px-1 rounded-r-md py-2 border border-gray-300 text-sm font-medium text-gray-700 bg-gray-50 hover:bg-gray-100">
          <SortAscendingIcon v-if="sortDirection === 'asc'" class="h-5 w-5 text-vagheggi-800" aria-hidden="true" />
          <SortDescendingIcon v-else :class="`h-5 w-5 text-${sortDirection === 'desc' ? 'vagheggi-800' : 'gray-400'}`" aria-hidden="true" />
        </Button>
    </div>
  </div>
</template>

<script setup>
import { SortAscendingIcon, SortDescendingIcon, XIcon, SearchIcon, SearchCircleIcon } from '@heroicons/vue/solid'
import { SearchCircleIcon as SearchCircleOutlineIcon }  from '@heroicons/vue/outline'
import Button from "~/components/Button";
import SearchColumnPopup from "~/components/DataTable/SearchColumnPopup";
const emit = defineEmits(["changeSort", "changeSearch", "sortCleared", "closeSearch", "openSearch"]);
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
  icon : {
    required : false,
    type: String,
    default: 'SearchIcon'
  },
  sortDirection : {
    required : false,
    default: null
  },
  column : {
    required: true,
    type: String
  },
  setSearchOpen : {
    required: false,
    type: Boolean,
  }
})
const onSortChanged = () => {
  emit('changeSort', props.column);
}
const onSearchAndSortCleared = (e) => {
  emit('sortCleared', props.column);
  setSearch('');
}
const getName = () => {
  if ( props.data.name ) {
    return props.data.name;
  }
  return props.data;
}

const onSearch = (value) => {
  setSearch(value);
  onCloseSearch();
}
const onCloseSearch = () => {
  emit('closeSearch', props.column);
}
const onOpenSearch = () => {
  emit('openSearch', props.column);
}
const setSearch = (set) => {
  emit('changeSearch', {
    name: props.column,
    column: (props.data.valuesGetter ? (props.data.column ? ('.' + props.data.column) : '') : ''),
    value: set
  });
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