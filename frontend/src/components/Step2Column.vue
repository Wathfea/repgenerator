<script setup>
import {defineEmits, ref} from 'vue'

const emit = defineEmits(['removeColumn', 'refreshTables'])

const columnTypes = [
    'id',
    'integer',
    'string',
    'text',
    'boolean',
    'bigIncrements',
    'bigInteger',
    'binary',
    'char',
    'dateTimeTz',
    'dateTime',
    'date',
    'decimal',
    'double',
    'enum',
    'float',
    'foreignId',
    'foreignIdFor',
    'foreignUuid',
    'geometryCollection',
    'geometry',
    'increments',
    'ipAddress',
    'json',
    'jsonb',
    'lineString',
    'longText',
    'macAddress',
    'mediumIncrements',
    'mediumInteger',
    'mediumText',
    'morphs',
    'multiLineString',
    'multiPoint',
    'multiPolygon',
    'nullableTimestamps',
    'nullableMorphs',
    'nullableUuidMorphs',
    'point',
    'polygon',
    'rememberToken',
    'set',
    'smallIncrements',
    'smallInteger',
    'softDeletesTz',
    'softDeletes',
    'timeTz',
    'time',
    'timestampTz',
    'timestamp',
    'timestampsTz',
    'timestamps',
    'tinyIncrements',
    'tinyInteger',
    'tinyText',
    'unsignedBigInteger',
    'unsignedDecimal',
    'unsignedInteger',
    'unsignedMediumInteger',
    'unsignedSmallInteger',
    'unsignedTinyInteger',
    'uuidMorphs',
    'uuid',
    'year'
];

const props = defineProps({
    modelName: {
        type: String,
        required: false,
    },
    data: {
        required: false,
        type: Object,
        default: () => {
            return {}
        }
    },
    columnTypes: {
        required: false,
        type: Array,
        default: () => {
            return []
        }
    },
    models: {
        required: false,
        type: Array,
        default: () => {
            return []
        }
    }
})

const isScaleSettable = () => {
    return ['decimal', 'double', 'float', 'unsignedDecimal'].indexOf(props.data.type) >= 0;
}

const isUnsignedSettable = () => {
    return ['unsignedBigInteger', 'unsignedDecimal', 'unsignedInteger', 'unsignedMediumInteger', 'unsignedSmallInteger', 'unsignedTinyInteger'].indexOf(props.data.type) <= 0;
}

const isPrecisionSettable = () => {
    return ['dateTimeTz', 'dateTime', 'decimal', 'double', 'float', 'softDeletesTz', 'softDeletes', 'time', 'timeTz', 'timestamp', 'timestampTz', 'timestamps', 'timestampsTz', 'unsignedDecimal'].indexOf(props.data.type) >= 0;
}

const onRemoveColumn = () => {
    emit('removeColumn', props.data);
}

const onRefreshTables = () => {
    emit('refreshTables');
}

const hasOne = ['BelongsTo'];
const hasMany = ['BelongsTo'];
const belongsTo = ['HasOne', 'HasMany'];
const belongsToMany = ['BelongsToMany'];
const relationTypeTable1 = ref('')
const relationTypeTable2 = ref('')
const relationTypeOptions = ref([])
const showRelationTypeSelector = ref(false)

const onRelation1TypeChosen = () => {
    props.data.foreignRelationType = relationTypeTable1.value;

    switch (relationTypeTable1.value) {
        case 'HasOne':
            relationTypeOptions.value.length = 0;
            for(let i=0; i < hasOne.length; i++) {
                relationTypeOptions.value.push(hasOne[i]);
            }
            break;
        case 'HasMany':
            relationTypeOptions.value.length = 0;
            for(let i=0; i < hasMany.length; i++) {
                relationTypeOptions.value.push(hasMany[i]);
            }
            break;
        case 'BelongsTo':
            relationTypeOptions.value.length = 0;
            for(let i=0; i < belongsTo.length; i++) {
                relationTypeOptions.value.push(belongsTo[i]);
            }
            break;
        case 'BelongsToMany':
            relationTypeOptions.value.length = 0;
            for(let i=0; i < belongsToMany.length; i++) {
                relationTypeOptions.value.push(belongsToMany[i]);
            }
            break;
    }
}

const onRelation2TypeChosen = () => {
    props.data.reference.relationType = relationTypeTable2.value;
}

const onForeignChosen = () => {
    let setType = null;
    let foreignType = props.data.reference.columns[props.data.foreign];
    switch (foreignType) {
        case 'bigint' :
            setType = 'unsignedBigInteger';
            break;
        case 'integer' :
            setType = 'unsignedInteger';
            break;
        case 'datetime' :
            setType = 'dateTime';
            break;
        case 'string':
        case 'decimal':
        case 'boolean':
            setType = foreignType;
            break;
        default:
            console.log("Add support for: " + foreignType);
            break;
    }
    props.data.type = setType;
    showRelationTypeSelector.value = true;
}

const onReferenceChanged = () => {
    props.data.foreign = null;
}
</script>
<template>
    <div class="migration-line border border-gray-300 rounded-md p-2 mb-10 bg-gray-50">
        <div class="grid grid-cols-12 gap-y-6 gap-x-4 sm:grid-cols-12">
            <div class="sm:col-span-2">
                <div class="mt-1">
                    <input v-focus v-model="data.name" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md" placeholder="Field name" required="required"
                           type="text">
                </div>
            </div>
            <div class="sm:col-span-2">
                <div class="mt-1">
                    <select v-model="data.type" class="block focus:ring-indigo-500 focus:border-indigo-500 w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                            required="required">
                        <option v-for="columnType in columnTypes" :value="columnType">{{ columnType }}</option>
                    </select>
                </div>
            </div>
            <div class="sm:col-span-1">
                <div class="mt-1">
                    <input v-model="data.length" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md" placeholder="Length"
                           type="text">
                </div>
            </div>
            <div class="sm:col-span-1">
                <div class="mt-1">
                    <input v-model="data.precision" :disabled="!isPrecisionSettable()" class="disabled:bg-slate-50 disabled:text-slate-500 disabled:border-slate-200 disabled:shadow-none hadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"
                           placeholder="Precision"
                           type="text">
                </div>
            </div>
            <div class="sm:col-span-1">
                <div class="mt-1">
                    <input v-model="data.scale" :disabled="!isScaleSettable()" class="disabled:bg-slate-50 disabled:text-slate-500 disabled:border-slate-200 disabled:shadow-none columnScale hadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md" placeholder="Scale"
                           type="text">
                </div>
            </div>
            <div class="sm:col-span-1">
                <div class="mt-1">
                    <input v-model="data.default" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md" placeholder="Default"
                           type="text">
                </div>
            </div>
            <div class="sm:col-span-1">
                <div class="mt-3">
                    <div class="relative flex items-start">
                        <div class="flex items-center h-5">
                            <input v-model="data.nullable" id="nullable" aria-describedby="nullable-description" name="nullable" type="checkbox" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded" />
                        </div>
                        <div class="ml-3 text-sm">
                            <label for="nullable" class="font-medium text-gray-700">NULL</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="sm:col-span-1">
                <div class="mt-3">
                    <div class="relative flex items-start">
                        <select v-model="data.index"
                                class="block focus:ring-indigo-500 focus:border-indigo-500 w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                                multiple>
                            <option value="primary">PRIMARY</option>
                            <option value="unique">UNIQUE</option>
                            <option value="index">INDEX</option>
                            <option value="fulltext">FULLTEXT</option>
                            <option value="spatial">SPATIAL</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="sm:col-span-1">
                <div class="mt-3">
                    <div class="relative flex items-start">
                        <div class="flex items-center h-5">
                            <input v-model="data.auto_increment" id="aic" aria-describedby="aic-description" name="nullable" type="checkbox" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded" />
                        </div>
                        <div class="ml-3 text-sm">
                            <label for="aic" class="font-medium text-gray-700">AIC</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="sm:col-span-1">
                <div class="mt-3">
                    <div class="relative flex items-start">
                        <div class="flex items-center h-5">
                            <input v-model="data.unsigned" :disabled="!isUnsignedSettable()" id="unsigned" aria-describedby="unsigned-description" name="nullable" type="checkbox" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded" />
                        </div>
                        <div class="ml-3 text-sm">
                            <label for="unsigned" class="font-medium text-gray-700">Unsigned</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="mt-3 grid grid-cols-12 gap-y-6 gap-x-4 sm:grid-cols-12">
            <div class="sm:col-span-4">
                <div class="grid grid-cols-12 gap-4">
                    <div class="col-span-3 mt-1">
                        <label class="text-gray-700 ml-2">References</label>
                    </div>
                    <div class="col-span-4">
                        <select v-model="data.reference" class="block focus:ring-indigo-500 focus:border-indigo-500 w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                                @change="onReferenceChanged">
                            <option></option>
                            <option v-for="model in models" :value="model">{{ model.name }}</option>
                        </select>
                    </div>
                    <div class="col-span-4">
                        <select v-model="data.foreign" :disabled="!data.reference" class="block focus:ring-indigo-500 focus:border-indigo-500 w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                                @change="onForeignChosen">
                            <option></option>
                            <option v-for="(type,name) in data.reference.columns" :value="name">{{ name }}</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="sm:col-span-1">
                <button class="uppercase p-3 flex items-center bg-grey-500 text-white max-w-max shadow-sm hover:shadow-lg rounded-full w-10 h-10" type="button"
                        @click="onRefreshTables">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path d="M13.5 2c-5.621 0-10.211 4.443-10.475 10h-3.025l5 6.625 5-6.625h-2.975c.257-3.351 3.06-6 6.475-6 3.584 0 6.5 2.916 6.5 6.5s-2.916 6.5-6.5 6.5c-1.863 0-3.542-.793-4.728-2.053l-2.427 3.216c1.877 1.754 4.389 2.837 7.155 2.837 5.79 0 10.5-4.71 10.5-10.5s-4.71-10.5-10.5-10.5z"/></svg>
                </button>
            </div>
            <div class="sm:col-span-1">
                <div class="mt-3">
                    <div class="relative flex items-start">
                        <div class="flex items-center h-5">
                            <input v-model="data.cascade" id="cascade" aria-describedby="cascade-description" name="nullable" type="checkbox" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded" />
                        </div>
                        <div class="ml-3 text-sm">
                            <label for="cascade" class="font-medium text-gray-700">Cascade</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="sm:col-span-1">
                <div class="mt-3">
                    <div class="relative flex items-start">
                        <div class="flex items-center h-5">
                            <input v-model="data.searchable" id="searchable" aria-describedby="searchable-description" name="nullable" type="checkbox" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded" />
                        </div>
                        <div class="ml-3 text-sm">
                            <label for="searchable" class="font-medium text-gray-700">Searchable</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="sm:col-span-2">
                <div class="mt-1">
                    <input v-model="data.values" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md" placeholder="Values (coma separated)"
                           type="text">
                </div>
            </div>
            <div class="sm:col-span-2">
                <div class="mt-1">
                    <input v-model="data.comment" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md" placeholder="Comment"
                           type="text">
                </div>
            </div>
            <div class="sm:col-span-1">
                <button class="uppercase p-3 flex items-center bg-red-500 text-white max-w-max shadow-sm hover:shadow-lg rounded-full w-10 h-10" type="button"
                        @click="onRemoveColumn">
                    <svg height="32" preserveAspectRatio="xMidYMid meet" style="transform: rotate(360deg);" viewBox="0 0 32 32"
                         width="32">
                        <path d="M12 12h2v12h-2z" fill="currentColor"></path>
                        <path d="M18 12h2v12h-2z" fill="currentColor"></path>
                        <path d="M4 6v2h2v20a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8h2V6zm4 22V8h16v20z"
                              fill="currentColor"></path>
                        <path d="M12 2h8v2h-8z" fill="currentColor"></path>
                    </svg>
                </button>
            </div>
        </div>
        <div class="mt-3 grid grid-cols-12 gap-y-6 gap-x-4 sm:grid-cols-12" v-if="showRelationTypeSelector">
            <div class="sm:col-span-12">
                <div class="grid grid-cols-12 gap-4">
                    <div class="col-span-4">
                        <label for="test">{{ modelName }}</label>
                        <select v-model="relationTypeTable1"  @change="onRelation1TypeChosen" class="block focus:ring-indigo-500 focus:border-indigo-500 w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                            <option></option>
                            <option value="HasOne">hasOne</option>
                            <option value="HasMany">hasMany</option>
                            <option value="BelongsTo">belongsTo</option>
                            <option value="BelongsToMany">belongsToMany</option>
                        </select>
                    </div>
                    <div class="col-span-4" v-if="relationTypeTable1">
                        <label for="test" v-if="data.reference.name">{{ data.reference.name[0].toUpperCase() + data.reference.name.slice(1) }}</label>
                        <select v-model="relationTypeTable2" @change="onRelation2TypeChosen" class="block focus:ring-indigo-500 focus:border-indigo-500 w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                            <option v-for="option in relationTypeOptions">{{option}}</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        <div class="mt-3 grid grid-cols-12 gap-y-6 gap-x-4 sm:grid-cols-12 mb-3">
            <div class="sm:col-span-2">
                <div class="mt-3">
                    <div class="relative flex items-start">
                        <div class="flex items-center h-5">
                            <input v-model="data.show_on_table" id="show_on_table" aria-describedby="show_on_table-description" name="nullable" type="checkbox" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded" />
                        </div>
                        <div class="ml-3 text-sm">
                            <label for="show_on_table" class="font-medium text-gray-700">Add to CRUD</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="mt-3 grid grid-cols-12 gap-y-6 gap-x-4 sm:grid-cols-12 mb-3">
            <div class="sm:col-span-8">
                <div>
                    <div class="mt-1 flex rounded-md shadow-sm">
                        <span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-500 sm:text-sm"> storage/app/public/files/{{ modelName }}file </span>
                        <input type="text" v-model="data.uploads_files_path" class="flex-1 min-w-0 block w-full px-3 py-2 rounded-none rounded-r-md focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm border-gray-300" placeholder="Upload file path" />
                    </div>
                </div>
            </div>
        </div>


        <div class="mt-3 grid grid-cols-12 gap-y-6 gap-x-4 sm:grid-cols-12 mb-3">
            <div class="sm:col-span-6">
                <div class="mt-3">
                    <label class="text-base font-medium text-gray-900">Choose file type</label>
                    <p class="text-sm leading-5 text-gray-500">What king od items you will upload?</p>
                    <fieldset class="mt-4">
                        <legend class="sr-only">Choose file type</legend>
                        <div class="space-y-4 sm:flex sm:items-center sm:space-y-0 sm:space-x-10">
                            <div class="flex items-center">
                                <input v-model="data.is_file" id="fileType" name="fileTypeSelecter" type="radio" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300" />
                                <label for="fileType" class="ml-3 block text-sm font-medium text-gray-700">
                                    File
                                </label>
                            </div>
                            <div class="flex items-center">
                                <input v-model="data.is_picture" id="pictureType" name="fileTypeSelecter" type="radio" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300" />
                                <label for="pictureType" class="ml-3 block text-sm font-medium text-gray-700">
                                    Picture
                                </label>
                            </div>
                        </div>
                    </fieldset>
                </div>
            </div>
        </div>


        <div class="mt-3 grid grid-cols-12 gap-y-6 gap-x-4 sm:grid-cols-12 mb-3">
            <div class="sm:col-span-6">
                <div class="mt-3">
                    <label class="text-base font-medium text-gray-900">Choose encryption type</label>
                    <p class="text-sm leading-5 text-gray-500">If the field is encrypted, how? </p>
                    <fieldset class="mt-4">
                        <legend class="sr-only">Choose encryption type</legend>
                        <div class="space-y-4 sm:flex sm:items-center sm:space-y-0 sm:space-x-10">
                            <div class="flex items-center">
                                <input v-model="data.is_hashed" id="hashType" name="cryptTypeSelecter" type="radio" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300" />
                                <label for="hashType" class="ml-3 block text-sm font-medium text-gray-700">
                                    Hashed
                                </label>
                            </div>
                            <div class="flex items-center">
                                <input v-model="data.is_crypted" id="cryptType" name="cryptTypeSelecter" type="radio" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300" />
                                <label for="cryptType" class="ml-3 block text-sm font-medium text-gray-700">
                                    Crypted
                                </label>
                            </div>
                        </div>
                    </fieldset>
                </div>
            </div>
        </div>

    </div>
</template>
