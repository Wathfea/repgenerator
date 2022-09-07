<template>
    <form method="POST" @submit="onSubmit">
        <div v-for="(column, key) in columns">
            <div class="sm:col-span-4 mt-2">
                <label for="username" class="block text-sm font-medium text-gray-700"> {{  getColumnName(key) }} </label>
                <div v-if="column.data.valuesGetter || column.data.values">
                    <ApiMultiselect :column="key" :set-data="column.data" :value="column.model" :force-disable="disableUpdate" @change="onSelectChange"/>
                </div>
                <div v-else-if="column.data.isAvatar" class="mt-1 mb-4 flex items-center">
                    <PhotoUpload :column="key" :originalData="column.originalData"  :data="column.model" @changed="onPhotoChanged"/>
                </div>
                <div v-else-if="column.data.isUpload" class="mt-1 mb-4 flex items-center">
                    <FileUpload :column="key" :data="column.model" @changed="onFileChanged" :disabled="!isColumnShown(column.data)"/>
                </div>
                <div v-else-if="column.data.isCheckbox" class="relative flex items-start mt-1">
                    <Switch v-model="column.model" :class="[column.model ? 'bg-vagheggi-600' : 'bg-gray-200', 'relative inline-flex flex-shrink-0 h-6 w-11 border-2 border-transparent rounded-full cursor-pointer transition-colors ease-in-out duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-vagheggi-500']">
                        <span aria-hidden="true" :class="[column.model ? 'translate-x-5' : 'translate-x-0', 'pointer-events-none inline-block h-5 w-5 rounded-full bg-white shadow transform ring-0 transition ease-in-out duration-200']" />
                    </Switch>
                </div>
                <div v-else-if="column.data.isFileManager" class="mt-1 mb-4 flex items-center">
                    <FilemanagerInput v-model="column.model" :required="column.data.required" :disabled="!isColumnShown(column.data)"/>
                </div>
                <div v-else class="mt-1 flex rounded-md shadow-sm">
                    <input v-model="column.model" :disabled="(column.data.disabled || disableUpdate) ?? false" :required="column.data.required" type="text" class="flex-1 block w-full min-w-0 rounded sm:text-sm border-gray-300" />
                </div>
            </div>
        </div>
        <Button v-if="!disableUpdate" :busy="isSubmitting" class="block mt-4 w-full min-h-[46px]">
            Mentés
        </Button>
    </form>
</template>
<script setup>
import {ref} from "vue";
import { Switch } from '@headlessui/vue'
import Button from "../Button";
import PhotoUpload from "../PhotoUpload";
import {useRoute} from "nuxt/app";
import FileUpload from "../FileUpload";
import FilemanagerInput from "../FilemanagerInput";
import ApiMultiselect from "../ApiMultiselect";
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
    },
    disableUpdate : {
        required : false,
        type: Boolean,
        default: false
    }
})
const columns = ref({});
const currentRoute = useRoute();
for ( let index in props.setColumns ) {
    let data = props.setColumns[index];
    columns.value[index] = {
        data : data,
        model : currentRoute.query.hasOwnProperty(index) ? currentRoute.query[index] : null,
        originalData : null
    }
}
if ( props.id ) {
    props.dataFunction(props.id).then((response) => {
        let data = response.data;
        for ( let index in columns.value ) {
            columns.value[index].model = columns.value[index].data.hasOwnProperty('cellGetter')
                ? columns.value[index].data.cellGetter(data) : ( data.hasOwnProperty(index) ? data[index] : null );
            columns.value[index].originalData = JSON.parse(JSON.stringify(columns.value[index].model));
        }
    })
}
const onPhotoChanged = (changeData) => {
    columns.value[changeData.column].model = changeData.photo;
}
const onSelectChange = (changeData) => {
    columns.value[changeData.column].model = changeData.value;
}
const onFileChanged = (changeData) => {
    columns.value[changeData.column].model = changeData.file;
}
const getColumnName = (key) => {
    if( columns.value[key].data.name ) {
        return columns.value[key].data.name;
    }
    return columns.value[key].data
}
const isColumnShown = (data) => {
    if ( !data.show_if ) {
        return true;
    }
}
const onSubmit = (e) => {
    e.preventDefault();
    emit('submit', columns);
}
</script>
