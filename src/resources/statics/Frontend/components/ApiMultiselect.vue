<template>
  <Multiselect
      @change="onChange"
      @search-change="onSearchChange"
      :value="value"
      :mode="data.multi ? 'tags' : 'single'"
      :options="data.values"
      :loading="data.valuesLoading"
      :label="data.label ?? 'name'"
      :searchable="data.searchable"
      :disabled="(data.disabled || forceDisable) ?? false"
      value-prop="id"
      :required="data.required"
  >
    <template v-if="data.customLabel" v-slot:option="{ option }">
      {{ data.customLabel(option) }}
    </template>
  </Multiselect>
</template>
<script setup>
import Multiselect from '@vueform/multiselect';
import {ref} from "vue";
const emit = defineEmits(['change']);
const props = defineProps({
  setData: {
    required : true,
    type: Object
  },
  value: {
    required : true
  },
  forceDisable: {
    required: false,
    type: Boolean
  }
})
const model = ref({});
const onChange = (value) => {
  emit('change', value);
}
const onSearchChange = (search) => {
  getValues(search)
}
const getValues = async (search = '') => {
  data.valuesLoading = true;
  const response = await data.valuesGetter(null, null, search.length ? {
    search: search
  } : {});
  data.values = response.data;
  data.valuesLoading = false;
}
let data = props.setData;
if ( data.valuesGetter && !data.values  ) {
  getValues();
}
</script>