<template>
  <div class="relative w-full" v-if="data">
    <hr />
    <div class="sm:col-span-4 mt-2">
        <label class="block text-sm font-medium text-gray-700"> Url </label>
        <div class="mt-1 flex rounded-md shadow-sm">
            <input v-model="data.slug" @input="onInputUpdate" required="true" type="text" class="flex-1 block w-full min-w-0 rounded sm:text-sm border-gray-300" />
        </div>
    </div>
    <div class="sm:col-span-4 mt-2">
        <label class="block text-sm font-medium text-gray-700"> Cím </label>
        <div class="mt-1 flex rounded-md shadow-sm">
            <input v-model="data.title" @input="onInputUpdate" required="true" type="text" class="flex-1 block w-full min-w-0 rounded sm:text-sm border-gray-300" />
        </div>
    </div>
    <div class="sm:col-span-4 mt-2">
        <label class="block text-sm font-medium text-gray-700"> Leírás </label>
        <div class="mt-1 flex rounded-md shadow-sm">
            <input v-model="data.description" @input="onInputUpdate" type="text" class="flex-1 block w-full min-w-0 rounded sm:text-sm border-gray-300" />
        </div>
    </div>
    <div class="sm:col-span-4 mt-2">
        <label class="block text-sm font-medium text-gray-700"> Kép </label>
        <div class="mt-1 mb-4 flex items-center">
            <FileManagerInput v-model="data.image" @change="onInputUpdate"/>
        </div>
    </div>
    <div class="sm:col-span-4 mt-2">
        <label class="block text-sm font-medium text-gray-700"> Kanonikus url </label>
        <div class="mt-1 flex rounded-md shadow-sm">
            <input v-model="data.canonical_url" @input="onInputUpdate" type="text" class="flex-1 block w-full min-w-0 rounded sm:text-sm border-gray-300" />
        </div>
    </div>
  </div>
</template>

<script setup>
import FileManagerInput from "./FileManagerInput.vue";
import {watchEffect} from "vue";

const emit = defineEmits(['changed']);
const props = defineProps({
  setData: {
    required : true,
    type: Object,
  },
  column: {
    required: true,
    type: String
  },
});

const onInputUpdate = (newValue) => {
    emit('changed', {
        column : props.column,
        data: data
    });
}

let data = {
            slug:'',
            title:'',
            description:'',
            image:'',
            canonical_url:'',
        };

const updateData = (newData)=>{
    data.slug = newData.slug;
    data.title = newData.title;
    data.description = newData.description;
    data.image = newData.image;
    data.canonical_url = newData.canonical_url;
}

watchEffect(() => {
    if(props.setData){
        updateData(props.setData);
    }
})

</script>
