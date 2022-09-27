<script setup>
import {defineEmits, ref, computed} from 'vue'
import axios from "axios";

const emit = defineEmits(['nameChanged', 'iconChanged', 'urlPrefixChanged', 'isValidTable', 'newGroupNameChanged', 'newGroupIconChanged', 'chosenMenuGroupChanged', 'chosenOutputFramework'])

const props = defineProps({
    modelName: {
        type: String,
        required: false,
        default: null
    },
    icon: {
        type: String,
        required: false,
        default: null
    },
    urlPrefix: {
        type: String,
        required: false,
        default: null
    },
    menuGroups: {
        type: Array,
        required: false,
        default: () => {
            return []
        }
    },
    setMenuGroupId: {
        type: Number,
        required: false,
        default: null
    },
    setNewMenuGroupName: {
        type: String,
        required: false,
        default: null
    },
    setNewMenuGroupIcon: {
        type: String,
        required: false,
        default: null
    },
    setChosenOutputFramework: {
        type: String,
        required: false,
        default: null
    },
})

let name = ref(props.modelName);
let icon = ref(props.icon);
let prefix = ref(props.urlPrefix);

const isValidTable = ref(true);

const checkTable = () => {
    emit('nameChanged',name.value)

    axios.get(import.meta.env.VITE_API_URL + '/repgenerator/validateTable/'+name.value).then((response) => {
        isValidTable.value = response.data;

        emit('isValidTable', isValidTable)
    })
}

const ucfirstName = computed({
    get() {
        return name.value;
    },
    set(ucName) {
        if(ucName.length < 1) {name.value = ''; return}
        name.value = ucName.replace(/^./, ucName[0].toUpperCase()).replace(/[^A-Za-z] /, '');
    }
})
const chosenMenuGroup = ref(props.setMenuGroupId);
const newMenuGroupName = ref(props.setNewMenuGroupName);
const newMenuGroupIcon = ref(props.setNewMenuGroupIcon);
const chosenOutputFramework = ref(props.setChosenOutputFramework);
</script>

<template>
    <div class="space-y-8 divide-y divide-gray-200">
        <div class="mt-6 grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
            <div class="sm:col-span-12">
                <label class="block text-sm font-medium text-gray-700" for="model-name">
                    Model Name (Singular with spaces - Ex. Booked Appointment, this will be transformed by the generator to BookedAppointment)
                </label>
                <p v-if="!isValidTable" class="mt-2 text-sm text-red-600" id="table-error">This table name is already exists or the name is not singular</p>
                <div class="mt-1">
                    <input id="model-name" v-focus v-model="ucfirstName" class="shadow-sm block w-full sm:text-sm  rounded-md" :class="tableExists ? 'border-red-300 text-red-900 placeholder-red-300 focus:outline-none focus:ring-red-500 focus:border-red-500' : 'border-gray-300' " required type="text" @change="checkTable">
                </div>

                <label class="block text-sm font-medium text-gray-700 mt-1" for="model-name">
                    Hero icon <a target="_blank" href="https://heroicons.com/">https://heroicons.com/</a>
                </label>
                <div class="mt-1">
                    <input id="icon-name" v-model="icon" class="shadow-sm block w-full sm:text-sm border-gray-300 rounded-md" required type="text" @change="emit('iconChanged',icon)">
                </div>

                <label class="block text-sm font-medium text-gray-700 mt-1" for="model-name">
                    Menu group
                </label>
                <div class="mt-1">
                    <select @change="emit('chosenMenuGroupChanged',chosenMenuGroup)" v-model="chosenMenuGroup" class="block focus:ring-indigo-500 focus:border-indigo-500 w-full shadow-sm sm:text-sm border-gray-300 rounded-md" required="required">
                        <option v-for="menuGroup in menuGroups" :value="menuGroup.id">{{ menuGroup.name }}</option>
                        <option :value="0">-= New menu group =-</option>
                    </select>
                </div>

                <div v-if="chosenMenuGroup === 0">
                    <label class="block text-sm font-medium text-gray-700 mt-1" for="model-name">
                        New menu group name
                    </label>
                    <div class="mt-1">
                        <input id="group-name" v-model="newMenuGroupName" class="shadow-sm block w-full sm:text-sm border-gray-300 rounded-md" required type="text" @change="emit('newGroupNameChanged',newMenuGroupName)">
                    </div>
                    <label class="block text-sm font-medium text-gray-700 mt-1" for="model-name">
                        New menu group Hero icon <a target="_blank" href="https://heroicons.com/">https://heroicons.com/</a>
                    </label>
                    <div class="mt-1">
                        <input id="group-icon" v-model="newMenuGroupIcon" class="shadow-sm block w-full sm:text-sm border-gray-300 rounded-md" required type="text" @change="emit('newGroupIconChanged',newMenuGroupIcon)">
                    </div>
                </div>

                <label class="block text-sm font-medium text-gray-700 mt-1" for="model-name">
                    Output framewwork
                </label>
                <div class="mt-1">
                    <select @change="emit('chosenOutputFramework',chosenOutputFramework)" v-model="chosenOutputFramework" class="block focus:ring-indigo-500 focus:border-indigo-500 w-full shadow-sm sm:text-sm border-gray-300 rounded-md" required="required">
                        <option value="nuxt">Nuxt3</option>
                        <option value="vue">Vue3</option>
                    </select>
                </div>

                <label class="block text-sm font-medium text-gray-700" for="url-prefix">
                    CRUD Page url prefix
                </label>
                <div class="mt-1">
                    <input id="url-prefix"  v-model="prefix" class="shadow-sm block w-full sm:text-sm border-gray-300 rounded-md" type="text" @change="emit('urlPrefixChanged',prefix)">
                </div>
            </div>
        </div>
    </div>
</template>
