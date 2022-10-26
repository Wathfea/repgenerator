<template>
  <Multiselect
      :classes="{
        containerActive: 'ring ring-repgenerator-500 ring-opacity-30',
        tag: 'bg-repgenerator-500 text-white text-sm font-semibold py-0.5 pl-2 rounded mr-1 mb-1 flex items-center whitespace-nowrap rtl:pl-0 rtl:pr-2 rtl:mr-0 rtl:ml-1',
        groupLabelSelected: 'bg-repgenerator-600 text-white',
        groupLabelSelectedPointed: 'bg-repgenerator-600 text-white opacity-90',
        groupLabelSelectedDisabled: 'text-repgenerator-100 bg-repgenerator-600 bg-opacity-50 cursor-not-allowed',
        optionSelected: 'text-white bg-repgenerator-500',
        optionSelectedPointed: 'text-white bg-repgenerator-500 opacity-90',
        optionSelectedDisabled: 'text-repgenerator-100 bg-repgenerator-500 bg-opacity-50 cursor-not-allowed',}"
      @change="onChange"
      @search-change="onSearchChange"
      :value="data.value"
      :mode="data.multi ? 'tags' : 'single'"
      :create-option="data.createOption || false"
      :options="data.values"
      :loading="data.valuesLoading"
      :label="data.label ?? 'name'"
      :searchable="data.searchable"
      :clearable="true"
      :disabled="(data.disabled || forceDisable) ?? false"
      :value-prop="data.valueProp ?? 'id'"
      :placeholder="placeholder"
      :required="props.required !== null ? props.required : data.required"
  >
    <template v-if="data.customLabel" v-slot:option="{ option }">
      {{ data.customLabel(option) }}
    </template>
  </Multiselect>
</template>
<script setup>
import Multiselect from '@vueform/multiselect';
import {ref, watchEffect, watch} from "vue";
const emit = defineEmits(['change']);
const props = defineProps({
  setData: {
    required : true,
    type: Object
  },
  value: {
    required : false
  },
  column: {
    required: true,
    type: String
  },
  columns: {
    required: false,
  },
  forceDisable: {
    required: false,
    type: Boolean
  },
  placeholder: {
    required: false,
    type: String
  },
  required: {
    required: false,
    type: Boolean
  }
})
let data = null;
const model = ref({});
const onChange = (newValue) => {
  let newData = null;
  for ( let index in data.values ) {
    if ( data.values[index].id === newValue ) {
      newData = data.values[index];
      break;
    }
  }
  emit('change', {
    column : props.column,
    value: newValue,
    data: newData
  });
}
const onSearchChange = (search) => {
  if(data.hasOwnProperty('valuesGetter')){
    getValues(search)
  }
}
const getValues = async (search = '') => {
  data.valuesLoading = true;
  let searchQuery = {};
  if ( data.hasOwnProperty('valuesFilters') ) {
    for ( let filterIndex in data.valuesFilters ) {
      const filter = data.valuesFilters[filterIndex];
      if ( props.columns.hasOwnProperty(filter) ) {
        const filterData = props.columns[filter];
        if ( filterData.hasOwnProperty('model') ) {
          searchQuery[filter] = filterData.model;
        } else if ( filterData.hasOwnProperty('data') && filterData.data.hasOwnProperty('value') ) {
          searchQuery[filter] = filterData.data.value;
        }
      }
    }
  }
  if ( search.length ) {
    searchQuery.search = search;
  }
  const response = await data.valuesGetter(null, null, searchQuery);
  data.values = ref(response.data);
  data.valuesLoading = false;
}
data = props.setData;
if ( data.valuesGetter && ( !data.values || !data.values.length) ) {
  requestAnimationFrame(getValues);
  if ( data.hasOwnProperty('valuesFilters') ) {
    for ( let index in data.valuesFilters ) {
      const filter = data.valuesFilters[index];
      watch(() => props.columns[filter].model, () => {
        getValues();
      });
    }
  }
}
const valueCheck = ref(false);
watchEffect(async () => {
  if (props.value && !valueCheck.value) {
    valueCheck.value = true;
    let foundInArray = false;
    for (let index in data.values) {
      if (data.values[index].id === props.value) {
        foundInArray = true;
        break;
      }
    }
    if (!foundInArray && data.valueGetter) {
      const value = await data.valueGetter(props.value);
      data.values.unshift(value.data);
    }
    data.value = props.value;
  }
})
</script>
