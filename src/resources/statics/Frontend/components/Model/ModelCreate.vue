<template>
  <ModelForm :setColumns="columns" @submit="onSubmit" :isSubmitting="isSubmitting"/>
</template>
<script setup>
import ModelForm from "./ModelForm.vue";
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
    let value = columns.value[index];
    if ( value.translatedModels ) {
      if (!data.translatables) {
        data.translatables = [];
      }
      let translationData = {
        translations: value.translatedModels
      }
      if ( value.model && value.data.translationSelect === 'select'  ) {
        translationData.key_id = value.model;
      }
      else if ( value.translationKey) {
        translationData.key = value.translationKey;
      }
      translationData.column = index;
      translationData.group_id = value.translationGroupId !== null ? value.translationGroupId : value.data.translationGroupId;
      if ( !translationData.key_id && !translationData.key && !value.data.isReusableTranslation ) {
        translationData.key = (value.data.translationPrefix ?? '' ) + translationData.translations['en'].toUpperCase().replace(/\s+/g, '_');
      }
      if ( translationData.key_id || translationData.key) {
        data.translatables.push(JSON.stringify(translationData));
      }
    } else {
      data[index] = columns.value[index].model;
    }
  }
  isSubmitting.value = true;
  props.storeMethod(data).then((response) => {
    isSubmitting.value = false;
  })
}
</script>
