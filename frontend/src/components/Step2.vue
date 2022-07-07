<script setup>
import Step2Column from "./Step2Column.vue";
import {onMounted, defineEmits, reactive, ref} from 'vue'
const emit = defineEmits(['removeColumn', 'addColumn'])
const props = defineProps({
    columns : {
        required : false,
        type : Array,
        default : () => {
            return []
        }
    },
    models : {
        required : false,
        type : Array,
        default : () => {
            return []
        }
    },
    disableAdd : {
        required : false,
        type: Boolean,
        default : false
    }
})

onMounted(() => {
    window.addEventListener('keydown', handleKeyPress);
})

const handleKeyPress = (e) => {
    if (e.ctrlKey && e.keyCode === 65) { // Ctrl+A
        onAddColumn();
    }
}

const onRemoveColumn = (data) => {
    emit('removeColumn', data);
}
const onAddColumn = () => {
    emit('addColumn');
}
</script>

<template>
    <div class="space-y-8 divide-y divide-gray-200">
        <div class="mt-6">
            <Step2Column v-for="column in columns" :data="column" :models="models" @removeColumn="onRemoveColumn"/>
        </div>
        <button v-if="!disableAdd" @click="onAddColumn" type="button" class="inline-flex w-full justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-gray-600 hover:bg-gray-700">
            Add column (ctrl+A)
        </button>
    </div>
</template>
