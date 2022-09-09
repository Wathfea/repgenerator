<script setup>
import {computed, ref, watchEffect} from "vue";
import { Buffer } from 'buffer'
import Button from "./Button";
const props = defineProps({
  data: {
    required : false,
    type: Object
  },
  originalData: {
    required : false,
    type: Object
  },
  column: {
    required: true,
    type: String
  }
})
const emit = defineEmits(['changed']);

const photoData = ref(null);
const photoUrl = ref(null);
const originalPhoto = ref(true);
const photoRules = [
  file => !file || file.size < 10_240_000 || 'Photo file size should be less than 10 MB!',
];
const hiddenFileInput = ref(null);

const onPhotoReset = () =>  {
  originalPhoto.value = true;
  let reset = props.originalData.current ? props.originalData.current : props.originalData.default;
  setPhotoFromData(reset, true);
}

const getPhotoSrc = computed(() => {
  if ( photoData.value && photoData.value.size ) {
    return URL.createObjectURL(photoData.value);
  }
  return photoUrl.value;
});

const getPhotoUrl = computed(() => {
  if ( photoUrl.value ) {
    const photoParts = photoUrl.value.split('/');
    return photoParts[photoParts.length-1];
  }
  return '';
});

const selectFile = (e) => {
  e.preventDefault();
  hiddenFileInput.value.click();
}

const onInputChanged = (e) => {
  let reader = new FileReader();
  reader.onload = function(event){
    let base64 = event.target.result.split(',')[1];
    setPhotoFromData({
      data : base64,
      url: e.target.files[0].name
    }, false)
    emit('changed', {
      column: props.column,
      photo: photoData.value
    })
    originalPhoto.value = base64 === props.originalData.current.data;
  };
  reader.readAsDataURL(e.target.files[0]);
}


const getPhotoData = (photo) => {
  if ( !photo || !photo.data ) {
    return null;
  }
  return new File([Buffer.from(photo.data, 'base64')], photo.name)
}

const setPhotoFromData = (photo, reset) => {
  photoData.value = getPhotoData(photo);
  if ( reset ) {
    emit('changed', {
      column: props.column,
      photo: photoData.value
    })
  }
  photoUrl.value = photo.url;
}

watchEffect(() => {
  if ( props.data ) {
    let photo = null;
    if ( props.data.current ) {
      photo = props.data.current;
    }
    else if ( props.data.default ) {
      photo = props.data.default;
    }
    if ( photo && photo.data ) {
      setPhotoFromData(photo);
    }
  }
})


</script>
<template>
  <div class="relative h-20 w-full">
    <div class="h-20 w-20 p-1 m-0.5 shadow absolute">
      <img v-if="getPhotoSrc" :src="getPhotoSrc" alt="" class="w-full h-full"/>
      <svg v-else class="animate-spin m-auto h-10 w-10 m-4 text-vagheggi-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
      </svg>
    </div>
    <div class="flex pl-20 ml-2">
      <input type="file" ref="hiddenFileInput" @change="onInputChanged" class="hidden"/>
      <input type="text" :readonly="true" v-model="getPhotoUrl" @click="selectFile" class="shadow-sm flex-1 block w-full min-w-0 sm:text-sm border-gray-300">
      <Button @click="onPhotoReset()" :disabled="originalPhoto">Reset</Button>
    </div>
    <div class="flex pl-20 ml-2">
      <Button @click="selectFile" class="w-full">Select</Button>
    </div>
  </div>
</template>
