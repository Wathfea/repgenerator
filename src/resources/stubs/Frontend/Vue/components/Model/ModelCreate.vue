<template>
  <ModelForm :setColumns="columns" @submit="onSubmit" :isSubmitting="isSubmitting"/>
</template>
<script setup>
import ModelForm from "~/components/Model/ModelForm";
import {ref} from "vue";
const props = defineProps({
  columns : {
    type: Object,
    required: true
  },
  storeMethod : {
    type: Function,
    required: true,
  }
})
const isSubmitting = ref(false);
const onSubmit = (columns) => {
  let data = {};
  for ( let index in columns.value ) {
      data[index] = columns.value[index].model;
  }
  isSubmitting.value = true;
  props.storeMethod(data).then((response) => {
    isSubmitting.value = false;
  })
}
</script>