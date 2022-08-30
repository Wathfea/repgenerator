<script setup>
import Step1 from "./Step1.vue";
import Step2 from "./Step2.vue";
import Result from "./Result.vue";
import Step from "./Step.vue";
import {ref} from "vue";
import axios from 'axios'
import Options from "./Options.vue";
import factoryImgUrl from '../assets/factory.gif'

// Wizzard
const stepNumber = ref(1);

const steps = [
    {
        title: 'Name'
    },
    {
        title: 'Columns',
    },
    {
        title: 'Review'
    },
    {
        title: 'Result'
    }
];

const step1Options = ref({
    'pivot': {
        label: 'Pivot',
        enabled: false,
        text: 'Is this a pivot model?'
    },
    'read_only': {
        label: 'Readonly',
        enabled: false,
        text: 'Is the repository readonly?'
    },
    'softDelete': {
        label: 'Soft Delete',
        enabled: true,
        text: 'Is the model use soft delete?'
    },
    'timestamps': {
        label: 'Timestamps',
        enabled: true,
        text: 'Is the model use timestamps?'
    }
});

const scrollToTop = () => {
    document.getElementById('scroll-anchor').scrollIntoView({behavior: "smooth", block: "end", inline: "nearest"});
}

const resetWizzard = () => {
    stepNumber.value = 1;
    modelName.value = '';
    icon.value = '';
    columns.value = getDefaultColumns();
    for (let index in step1Options) {
        step1Options[index].enabled = false;
    }
}

const messages = ref([]);

const generating = ref(false);

const generate = () => {
    generating.value = true;
    let payload = {
        name: modelName.value,
        icon: icon.value,
        columns: columns.value
    };
    for (let index in step1Options.value) {
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
    if (stepNumber.value === steps.length) {
        resetWizzard();
    } else if (isOverview()) {
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
    return stepNumber.value === steps.length - 1;
}

const isLastStep = () => {
    return stepNumber.value === steps.length;
}

// 1. Name
const modelName = ref('');

const icon = ref('');

const onNameChanged = (name) => {
    modelName.value = name;
}

const onIconChanged = (setIcon) => {
    icon.value = setIcon;
}

// 2. Columns
const getDefaultColumns = () => {
    return [
        {
            'name': 'id',
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
            'show_on_table': false,
            'uploads_files_path': '',
            'is_file': false,
            'is_picture': false,
            'is_hashed': false,
            'is_crypted': false,
        }
    ]
}
const columns = ref(getDefaultColumns());

const onAddColumn = () => {
    columns.value.push({
        'name': '',
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
        'show_on_table': true,
        'uploads_files_path': '',
        'is_file': false,
        'is_picture': false,
        'is_hashed': false,
        'is_crypted': false,
    });
}
const onRemoveColumn = (data) => {
    for (let index in columns.value) {
        if (columns.value[index] === data) {
            columns.value.splice(parseInt(index), 1);
            break;
        }
    }
}

const onRefreshTables = () => {
    getTables()
}

const isTableExists = ref(false);

const onTableExists = (data) => {
    isTableExists.value = data.value;
}

const onSelectOption = (data) => {
    if(data.label === "Pivot") {

    }
}

// 2. Models
const models = ref([]);

const getTables = () => {
    axios.get(import.meta.env.VITE_API_URL + '/repgenerator/tables').then((response) => {
        models.value = response.data;
    })
}

getTables()
</script>

<template>
    <div class="grid place-items-center h-screen" v-if="generating">
        <img :src="factoryImgUrl" alt="Repository Generator Factory" class="factory">
    </div>
    <form class="mt-5" @submit="onNextStep" v-else>
        <nav aria-label="Progress">
            <ol class="border border-gray-300 rounded-md divide-y divide-gray-300 md:flex md:divide-y-0" role="list">
                <Step v-for="(step,index) in steps" :complete="index+1 < stepNumber" :current="stepNumber === index+1"
                      :index="index+1" :last="index+1>=steps.length" :title="step.title"/>
            </ol>
        </nav>
        <div v-if="stepNumber === 1 || isOverview()">
            <Step1 :icon="icon" :modelName="modelName"  @iconChanged="onIconChanged" @nameChanged="onNameChanged" @tableExists="onTableExists"/>
            <Options :options="step1Options" @selectOption="onSelectOption"/>
        </div>
        <Step2 v-if="stepNumber === 2 || isOverview()" :columns="columns" :disableAdd="isOverview()" :models="models" :modelName="modelName"
               @addColumn="onAddColumn" @removeColumn="onRemoveColumn" @refreshTables="onRefreshTables"/>
        <Result v-if="isLastStep()" :messages="messages"/>

        <div v-if="!isLastStep()" class="pt-5 grid grid-cols-12 gap-4">
            <div class="col-span-6">
                <button :disabled="isPreviousDisabled()"
                        class="disabled:bg-slate-50 disabled:text-slate-500 disabled:border-slate-200 disabled:shadow-none block w-full py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-gray-400 hover:bg-gray-500"
                        type="submit"
                        @click="onPreviousStep">
                    Back
                </button>
            </div>
            <div class="col-span-6">
                <button
                    :disabled="isTableExists"
                    class="disabled:bg-slate-50 disabled:text-slate-500 disabled:border-slate-200 disabled:shadow-none block w-full py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700"
                    type="submit">
                    {{ isOverview() ? 'Finish' : 'Next' }}
                </button>
            </div>
        </div>
        <div v-else class="mt-6">
            <button :disabled="generating"
                    class="block w-full py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700"
                    type="submit">
                Restart
            </button>
        </div>
    </form>
</template>
