<script setup>
import Button from "./Button.vue";

defineProps(["modelValue"]);
defineEmits(["update:modelValue"]);
const inputId = 'filemanager_input_'+Math.random().toString(36).slice(2);
const isImage = (src)=>{
  return src!== null && src.length>0 && src.match(/\.(jpeg|jpg|gif|png|webp)$/) != null;
}
</script>

<template>
  <div class="relative w-full">
    <div class="flex">
      <input type="text" :id="inputId" :value="modelValue" @input="$emit('update:modelValue', $event.target.value)" class="shadow-sm flex-1 block w-full min-w-0 sm:text-sm border-gray-300" />
      <Button class="file-manager-open" :data-target="inputId">Select</Button>
      <img v-if="isImage(modelValue)" :src="modelValue" alt="" class="w-20 h-20" />
    </div>
  </div>
</template>
