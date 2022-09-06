<template>
  <form method="POST" @submit="onSubmit">
    <div v-for="(column, key) in columns">
      <div class="sm:col-span-4 mt-2">
        <label for="username" class="block text-sm font-medium text-gray-700"> {{  getColumnName(key) }} </label>
        <div v-if="column.data.valuesGetter">
          <Multiselect
              v-model="column.model"
              :mode="column.data.multi ? 'tags' : 'single'"
              v-if="column.data.valuesGetter"
              :options="column.data.values"
              :loading="column.data.valuesLoading"
              :label="column.data.label ?? 'name'"
              :searchable="column.data.searchable"
              value-prop="id"
              class="column-multiselect"
              :required="column.data.required"
          />
        </div>
        <div v-else-if="column.data.isAvatar" class="mt-1 mb-4 flex items-center">
          <PhotoUploadV2 :column="key" :data="column.model" @changed="onPhotoChanged"/>
        </div>
        <div v-else-if="column.data.isUpload" class="mt-1 mb-4 flex items-center">
          <span class="inline-block h-12 w-12 rounded-full overflow-hidden bg-gray-100 border-2">
            <svg class="h-full w-full text-gray-300" fill="currentColor" viewBox="0 0 24 24">
              <path d="M24 20.993V24H0v-2.996A14.977 14.977 0 0112.004 15c4.904 0 9.26 2.354 11.996 5.993zM16.002 8.999a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
          </span>
          <button type="button" class="ml-5 bg-white py-2 px-3 border border-gray-300 rounded-md shadow-sm text-sm leading-4 font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-vagheggi-500">Módosítás</button>
        </div>
        <div v-else-if="column.data.isCheckbox" class="relative flex items-start mt-1">
          <Switch v-model="column.model" :class="[column.model ? 'bg-vagheggi-600' : 'bg-gray-200', 'relative inline-flex flex-shrink-0 h-6 w-11 border-2 border-transparent rounded-full cursor-pointer transition-colors ease-in-out duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-vagheggi-500']">
            <span aria-hidden="true" :class="[column.model ? 'translate-x-5' : 'translate-x-0', 'pointer-events-none inline-block h-5 w-5 rounded-full bg-white shadow transform ring-0 transition ease-in-out duration-200']" />
          </Switch>
        </div>
        <div v-else class="mt-1 flex rounded-md shadow-sm">
          <input v-model="column.model" :required="column.data.required" type="text" class="flex-1 block w-full min-w-0 rounded sm:text-sm border-gray-300" />
        </div>
      </div>
    </div>
    <Button :busy="isSubmitting" class="block mt-4 w-full min-h-[46px]">
      Mentés
    </Button>
  </form>
</template>
<script setup>
import {ref} from "vue";
import { Switch } from '@headlessui/vue'
import Multiselect from '@vueform/multiselect';
import Button from "~/components/Button";
import PhotoUploadV2 from "~/components/PhotoUploadV2";
import {useRoute} from "nuxt/app";
const emit = defineEmits(['submit']);
const props = defineProps({
  id : {
    required : false,
    type: Number
  },
  setColumns : {
    type: Object,
    required: true
  },
  dataFunction: {
    required: false,
    type: Function
  },
  isSubmitting : {
    required : false,
    type: Boolean,
    default: false
  }
})
const columns = ref({});
const currentRoute = useRoute();
for ( let index in props.setColumns ) {
  let data = props.setColumns[index];
  if ( data.valuesGetter && !data.values ) {
    data.valuesLoading = true;
    const response = await data.valuesGetter();
    data.values = response.data;
    data.valuesLoading = false;
  }
  columns.value[index] = {
    data : data,
    model : currentRoute.query.hasOwnProperty(index) ? currentRoute.query[index] : null
  }
}
if ( props.id ) {
  props.dataFunction(props.id).then((response) => {
    let data = response.data;
    for ( let index in columns.value ) {
        columns.value[index].model = columns.value[index].data.hasOwnProperty('cellGetter')
            ? columns.value[index].data.cellGetter(data) : ( data.hasOwnProperty(index) ? data[index] : null );
    }
  })
}
const onPhotoChanged = (changeData) => {
  columns.value[changeData.column].model = changeData.photo;
}
const getColumnName = (key) => {
  if( columns.value[key].data.name ) {
    return columns.value[key].data.name;
  }
  return columns.value[key].data
}
const onSubmit = (e) => {
  e.preventDefault();
  emit('submit', columns);
}
</script>
