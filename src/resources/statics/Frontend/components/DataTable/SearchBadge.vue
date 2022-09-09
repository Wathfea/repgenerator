<template>
  <span class="inline-flex rounded-lg items-center px-2 pr-1.5 py-1 text-sm font-medium bg-white border-2 border-vagheggi-800 text-vagheggi-700" :style="{ lineHeight: '21px'}">
    <SearchCircleIcon @click="onOpenSearch" class="h-5 w-5 text-vagheggi-800 mr-1 hover:text-vagheggi-700 cursor-pointer" aria-hidden="true"/>
    <b class="pr-1">{{ name + ":" }}</b>
    <svg v-if="Array.isArray(data.data.values) && !data.data.values.length" class="animate-spin m-auto h-4 w-4 text-vagheggi-800" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
    </svg>
    <span v-else>{{ getValue(search.value) }}</span>
    <Button @click="onRemove" class="pl-1 pr-0 py-0 bg-transparent hover:bg-transparent">
       <XIcon class="h-4 w-4 text-vagheggi-800 hover:text-vagheggi-700" aria-hidden="true"/>
    </Button>
  </span>
</template>
<script setup>
  import Button from "../Button";
  import { SearchCircleIcon, XIcon } from '@heroicons/vue/solid'
  import {computed} from "vue";
  const emit = defineEmits(['onRemove', 'openSearch'])
  const props = defineProps({
    name: {
      required: true,
      type: String
    },
    data: {
      required: false,
    },
    column: {
      required: true,
      type: String
    },
    search: {
      required: true,
    }
  })
  const onRemove = () => {
    emit('onRemove', props.column);
  }
  const onOpenSearch = () => {
    emit('openSearch', props.column);
  }
  let columnName = 'id';
  if ( props.search.column ) {
    let columnData = props.search.column.split('.');
    if ( columnData.length > 1 ) {
      columnName = columnData[1];
    }
  }
  const getValue = computed(() => (value) => {
    if ( props.data.data.values ) {
      let setValues = Array.isArray(props.search.value) ? props.search.value : props.search.value.split(',');
      let returnValues = [];
      for ( let key in setValues ) {
        for ( let index in props.data.data.values ) {
          let compareValue = props.data.data.values[index][columnName];
          let setValue = parseInt(setValues[key]);
          if ( props.data.data.values[index].hasOwnProperty(columnName) &&  compareValue === setValue ) {
            returnValues.push(props.data.data.values[index].name);
          }
        }
      }
      return returnValues.join(', ');
    }
    return value;
  })
</script>
