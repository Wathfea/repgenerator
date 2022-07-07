<script setup>
import Step1 from "./Step1.vue";
import Step2 from "./Step2.vue";
import Result from "./Result.vue";
import Step from "./Step.vue";
import {ref} from "vue";
import axios from 'axios'
import Options from "./Options.vue";

// Wizzard
const stepNumber = ref(1);
const steps = [
    {
        title : 'Name'
    },
    {
        title : 'Columns',
    },
    {
        title : 'Review'
    },
    {
        title : 'Result'
    }
];
const step1Options = ref({
    'pivot': {
        label : 'Pivot',
        enabled : false,
        text: 'Is this a pivot model?'
    },
    'read_only': {
        label: 'Readonly',
        enabled: false,
        text: 'Is the repository readonly?'
    }
});
const scrollToTop = () => {
    document.getElementById('scroll-anchor').scrollIntoView({behavior: "smooth", block: "end", inline: "nearest"});
}
const resetWizzard = () => {
    stepNumber.value = 1;
    modelName.value = '';
    columns.value = getDefaultColumns();
    for ( let index in step1Options ) {
        step1Options[index].enabled = false;
    }
}
const messages = ref([]);
const generating = ref(false);
const generate = () => {
    generating.value = true;
    let payload = {
        name : modelName.value,
        columns : columns.value
    };
    for ( let index in step1Options.value ) {
        payload[index] = step1Options.value[index].enabled;
    }
    payload['model'] = true;

    axios.post(import.meta.env.VITE_API_URL + '/repgenerator/generate', payload).then((response) => {
        messages.value = response.data;
    }).finally(() => {
        ++stepNumber.value;
        generating.value = false;
    })
}
const onNextStep = (e) => {
    e.preventDefault();
    if ( stepNumber.value === steps.length ) {
        resetWizzard();
    } else if ( isOverview() ) {
        generate();
    } else {
        scrollToTop();
        ++stepNumber.value;
    }
}
const onPreviousStep = (e) => {
    e.preventDefault();
    stepNumber.value = stepNumber.value - 1;
}
const isPreviousDisabled = () => {
    return stepNumber.value <= 1;
}
const isOverview = () => {
    return stepNumber.value === steps.length-1;
}
const isLastStep = () => {
    return stepNumber.value === steps.length;
}

// 1. Name
const modelName = ref('');
const onNameChanged = (name) => {
    modelName.value = name;
}

// 2. Columns
const getDefaultColumns = () => {
    return [
        {
            'name' : 'id',
            'type': 'id',
            'length': '',
            'precision': '',
            'scale': '',
            'default': '',
            'auto_increment': true,
            'nullable': false,
            'reference': '',
            'foreign': '',
            'cascade': '',
            'searchable': '',
            'values': '',
            'comment': '',
            'unsigned': false,
            'index': [],
            'show_on_table' : false,
            'uploads_files_path' : '',
        },
        {
            'name' : 'created_at',
            'type': 'timestamp',
            'length': '',
            'precision': '',
            'scale': '',
            'default': '',
            'auto_increment': false,
            'nullable': false,
            'reference': '',
            'foreign': '',
            'cascade': '',
            'searchable': '',
            'values': '',
            'comment': '',
            'unsigned': false,
            'index': [],
            'show_on_table' : false,
            'uploads_files_path' : '',
        },
        {
            'name' : 'updated_at',
            'type': 'timestamp',
            'length': '',
            'precision': '',
            'scale': '',
            'default': '',
            'auto_increment': false,
            'nullable': false,
            'reference': '',
            'foreign': '',
            'cascade': '',
            'searchable': '',
            'values': '',
            'comment': '',
            'unsigned': false,
            'index': [],
            'show_on_table' : false,
            'uploads_files_path' : '',
        }
    ]
}
const columns = ref(getDefaultColumns());
const onAddColumn = () => {
    columns.value.push({
        'name' : '',
        'type': '',
        'length': '',
        'precision': '',
        'scale': '',
        'default': '',
        'auto_increment': false,
        'nullable': false,
        'reference': '',
        'foreign': '',
        'cascade': '',
        'searchable': '',
        'values': '',
        'comment': '',
        'unsigned': false,
        'index': [],
        'show_on_table' : true,
        'uploads_files_path' : ''
    });
}
const onRemoveColumn = (data) => {
    for ( let index in columns.value ) {
        if ( columns.value[index] === data ) {
            columns.value.splice(parseInt(index),1);
            break;
        }
    }
}
// 2. Models
const models = ref([]);
axios.get(import.meta.env.VITE_API_URL + '/repgenerator/tables').then((response) => {
    models.value = response.data;
})
</script>

<template>
    <form @submit="onNextStep">
        <nav aria-label="Progress">
            <ol role="list" class="border border-gray-300 rounded-md divide-y divide-gray-300 md:flex md:divide-y-0">
                <Step v-for="(step,index) in steps" :index="index+1" :complete="index+1 < stepNumber" :last="index+1>=steps.length" :current="stepNumber === index+1" :title="step.title"/>
            </ol>
        </nav>
        <div v-if="stepNumber === 1 || isOverview()" >
            <Step1 :modelName="modelName" @nameChanged="onNameChanged"/>
            <Options :options="step1Options"/>
        </div>
        <Step2 v-if="stepNumber === 2 || isOverview()" :disableAdd="isOverview()" :columns="columns" :models="models" @addColumn="onAddColumn" @removeColumn="onRemoveColumn"/>
        <Result v-if="isLastStep()" :messages="messages"/>

        <div class="pt-5 grid grid-cols-12 gap-4" v-if="!isLastStep()">
            <div class="col-span-6">
                <button :disabled="isPreviousDisabled()" @click="onPreviousStep" type="submit" class="disabled:bg-slate-50 disabled:text-slate-500 disabled:border-slate-200 disabled:shadow-none block w-full py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-gray-400 hover:bg-gray-500">
                    Back
                </button>
            </div>
            <div class="col-span-6">
                <button type="submit" class="block w-full py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                    {{ isOverview() ? 'Finish' : 'Next' }}
                </button>
            </div>
        </div>
        <div v-else class="mt-6">
            <button :disabled="generating" type="submit" class="block w-full py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                Restart
            </button>
        </div>
    </form>
</template>
