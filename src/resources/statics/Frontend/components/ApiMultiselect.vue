<template>
    <Multiselect
        :classes="{
        containerActive: 'ring ring-repgenerator-500 ring-opacity-30',
        tag: 'bg-repgenerator-500 text-white text-sm font-semibold py-0.5 pl-2 rounded mr-1 mb-1 flex items-center whitespace-nowrap rtl:pl-0 rtl:pr-2 rtl:mr-0 rtl:ml-1',
        groupLabelSelected: 'bg-repgenerator-600 text-white',
        groupLabelSelectedPointed: 'bg-repgenerator-600 text-white opacity-90',
        groupLabelSelectedDisabled: 'text-repgenerator-100 bg-repgenerator-600 bg-opacity-50 cursor-not-allowed',
        optionSelected: 'text-white bg-repgenerator-500',
        optionSelectedPointed: 'text-white bg-repgenerator-500 opacity-90',
        optionSelectedDisabled: 'text-repgenerator-100 bg-repgenerator-500 bg-opacity-50 cursor-not-allowed',}"
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
    column: {
        required: true,
        type: String
    },
    forceDisable: {
        required: false,
        type: Boolean
    }
})
const model = ref({});
const onChange = (newValue) => {
    emit('change', {
        column : props.column,
        value: newValue
    });
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
