<script setup>
import Button from "Button";
import {ref, watchEffect} from "vue";
const props = defineProps({
  data: {
    required : false,
    type: Object
  },
  column: {
    required: true,
    type: String
  },
  disabled: {
    required: false,
    type: Boolean,
    default: false
  }
})
const emit = defineEmits(['changed']);
const isDownloadable = ref(false);
const newFile = ref({});
const hiddenFileInput = ref(null);
const selectFile = (e) => {
  e.preventDefault();
  hiddenFileInput.value.click();
}

const onInputChanged = (e) => {
  const file = e.target.files[0];
  newFile.name = file.name;
  isDownloadable.value = false;
  emit('changed', {
    column: props.column,
    file: file
  })
}

const getFileName = () => {
  if ( newFile && newFile.name ) {
    return newFile.name;
  }
  if ( props.data && props.data.name ) {
    return props.data.name;
  }
  return '';
}

const onDownloadFile = (e) => {
  e.preventDefault();
  let isPDF = false;
  let blob = new Blob([props.data.data], isPDF ? {
    type: 'application/pdf;base64'
  } : {});
  let fileURL = (window.URL || window['webkitURL']).createObjectURL(blob);
  let fileLink = document.createElement('a');
  fileLink.href = fileURL;
  fileLink.download = props.data.name;
  fileLink.click();
}

watchEffect(() => {
  if ( props.data ) {
    isDownloadable.value = props.data && props.data.data !== null;
  }
})


</script>
<template>
  <div class="relative w-full">
    <div class="flex">
      <input type="file" ref="hiddenFileInput" @change="onInputChanged" class="hidden"/>
      <input type="text" :readonly="true" :value="getFileName()" @click="selectFile" class="shadow-sm flex-1 block w-full min-w-0 sm:text-sm border-gray-300">
      <Button @click="onDownloadFile" :disabled="!isDownloadable || disabled">Download</Button>
    </div>
  </div>
</template>
