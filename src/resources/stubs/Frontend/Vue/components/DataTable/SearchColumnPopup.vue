<template>
  <TransitionRoot as="template" :show="true">
    <Dialog as="div" class="relative z-10" @close="onClose">
      <TransitionChild as="template" enter="ease-out duration-300" enter-from="opacity-0" enter-to="opacity-100" leave="ease-in duration-200" leave-from="opacity-100" leave-to="opacity-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" />
      </TransitionChild>

      <div class="fixed z-10 inset-0 overflow-y-auto">
        <div class="flex items-end sm:items-center justify-center min-h-full p-4 text-center sm:p-0">
          <TransitionChild as="template" enter="ease-out duration-300" enter-from="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" enter-to="opacity-100 translate-y-0 sm:scale-100" leave="ease-in duration-200" leave-from="opacity-100 translate-y-0 sm:scale-100" leave-to="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
            <DialogPanel class="overflow-visible relative bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:max-w-sm sm:w-full sm:p-6">
              <div class="hidden sm:block absolute top-0 right-0 pt-4 pr-4">
                <button type="button" class="bg-white rounded-md text-gray-400 hover:text-gray-500" @click="onClose">
                  <span class="sr-only">Close</span>
                  <XIcon class="h-6 w-6" aria-hidden="true" />
                </button>
              </div>
              <div>
                <div v-if="data.isDate">
                  <label class="block text-sm font-medium text-gray-700">{{ name }}</label>
                  <Datepicker :modelValue="date" @update:modelValue="setDate" :enableTimePicker="false" :format-locale="hu" format="Y-MM-dd" range multiCalendars locale="hu" cancelText="Mégse" selectText="Kiválasztás"></Datepicker>
                </div>
                <div v-else>
                  <label class="block text-sm font-medium text-gray-700">{{ name }}</label>
                  <div class="mt-1 relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                      <component v-if="icon" :is="heroIcons[icon]" class="mr-4 flex-shrink-0 h-6 w-6 text-gray-300" aria-hidden="true" />
                    </div>
                    <ApiMultiselect class="column-multiselect" v-if="data.valuesGetter" :set-data="data" :value="search" @change="onSearchChange"/>
                    <input v-else autocomplete="off" v-model="search" type="email" name="email" id="email" class="focus:ring-vagheggi-500 focus:border-vagheggi-500 block w-full pl-10 sm:text-sm border-gray-300"/>
                  </div>
                </div>
              </div>
              <div class="mt-2 sm:mt-3">
                <Button class="inline-flex justify-center w-full" @click="onSearch">
                  Szűrés
                </Button>
              </div>
            </DialogPanel>
          </TransitionChild>
        </div>
      </div>
    </Dialog>
  </TransitionRoot>
</template>

<script setup>
import {ref} from 'vue'
import { Dialog, DialogPanel, TransitionChild, TransitionRoot } from '@headlessui/vue'
import '@vuepic/vue-datepicker/dist/main.css'
import Datepicker from '@vuepic/vue-datepicker';
import * as heroIcons from "@heroicons/vue/solid";
import { XIcon } from '@heroicons/vue/outline'
import Button from "~/components/Button";
const emit = defineEmits(['close', 'search']);
import { hu } from 'date-fns/locale';
import ApiMultiselect from "~/components/ApiMultiselect";
const props = defineProps({
  data: {
    required: true,
  },
  name: {
    required: true,
    type: String
  },
  icon : {
    required : false,
    type: String,
    default: 'SearchIcon'
  },
  setSearch : {
    required : false,
    default: ''
  },
  column : {
    required: true,
    type: String
  },
});
let setSearch = props.setSearch ? props.setSearch : ( props.data.valuesGetter ? [] : '' );
setSearch = !Array.isArray(setSearch) && setSearch.indexOf(',') ? setSearch.split(',') : setSearch;
const search = ref(setSearch);
const date = ref(setSearch);
const onSearch = () => {
  emit('search', {
    name: props.column,
    value: search
  });
  onClose();
}
const onSearchChange = (value) => {
  search.value = value;
}
const onClose = () => {
  emit('close', props.column);
}
const setDate = (value) => {
  if ( Array.isArray(value) ) {
    for ( let i = 0 ; i < value.length ; ++i ) {
      if ( value[i] !== null ) {
        value[i] = getLocalDate(value[i]);
      }
    }
  } else {
    value = getLocalDate(value);
  }
  search.value = value;
  onSearch();
}
const getLocalDate = (string) => {
  return new Date(string).toISOString().split('T')[0];
}
</script>
<style>
.dp__select {
  color:rgb(47 136 129);
}
.dp__select:hover {
  color:rgb(59 150 142);
}
.multiselect-tags-search-wrapper, .multiselect-tags-search {
  outline:none !Important;
  border:none !Important;
  box-shadow: none !important;
}
.column-multiselect{
  border-radius: 0 !Important;
}
</style>