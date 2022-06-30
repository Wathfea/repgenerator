<script setup>
import { defineEmits } from 'vue'
const emit = defineEmits(['removeColumn'])
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
    data : {
        required : false,
        type : Object,
        default : () => {
            return {}
        }
    },
    columnTypes : {
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
    }
})
const isScaleSettable = () => {
    return ['decimal','double', 'float', 'unsignedDecimal'].indexOf(props.data.type) >= 0;
}
const isPrecisionSettable = () => {
    return ['dateTimeTz', 'dateTime', 'decimal', 'double', 'float', 'softDeletesTz', 'softDeletes', 'time', 'timeTz', 'timestamp', 'timestampTz', 'timestamps', 'timestampsTz', 'unsignedDecimal'].indexOf(props.data.type) >= 0;
}
const onRemoveColumn = () => {
    emit('removeColumn', props.data);
}
const onForeignChosen = (e) => {
    let setType = null;
    let foreignType = props.data.reference.columns[props.data.foreign];
    switch(foreignType) {
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
                    <input placeholder="Field name" type="text" required="required" v-model="data.name"  class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                </div>
            </div>
            <div class="sm:col-span-2">
                <div class="mt-1">
                    <select v-model="data.type" required="required" class="block focus:ring-indigo-500 focus:border-indigo-500 w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                        <option v-for="columnType in columnTypes" :value="columnType">{{ columnType }}</option>
                    </select>
                </div>
            </div>
            <div class="sm:col-span-1">
                <div class="mt-1">
                    <input type="text" placeholder="Length" v-model="data.length" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                </div>
            </div>
            <div class="sm:col-span-1">
                <div class="mt-1">
                    <input type="text" placeholder="Precision" v-model="data.precision" :disabled="!isPrecisionSettable()"  class="disabled:bg-slate-50 disabled:text-slate-500 disabled:border-slate-200 disabled:shadow-none hadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                </div>
            </div>
            <div class="sm:col-span-1">
                <div class="mt-1">
                    <input type="text" :disabled="!isScaleSettable()" placeholder="Scale" v-model="data.scale" class="disabled:bg-slate-50 disabled:text-slate-500 disabled:border-slate-200 disabled:shadow-none columnScale hadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                </div>
            </div>
            <div class="sm:col-span-1">
                <div class="mt-1">
                    <input type="text" placeholder="Default" v-model="data.default" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                </div>
            </div>
            <div class="sm:col-span-1">
                <div class="mt-3">
                    <div class="relative flex items-start">
                        <div class="flex items-center h-5">
                            <input type="checkbox" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                        </div>
                        <div class="ml-3 text-sm">
                            <label class="font-medium text-gray-700">NULL</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="sm:col-span-1">
                <div class="mt-3">
                    <div class="relative flex items-start">
                        <select v-model="data.index" class="block focus:ring-indigo-500 focus:border-indigo-500 w-full shadow-sm sm:text-sm border-gray-300 rounded-md" multiple>
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
                            <input type="checkbox" v-model="data.auto_increment" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                        </div>
                        <div class="ml-3 text-sm">
                            <label class="font-medium text-gray-700">AIC</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="sm:col-span-1">
                <div class="mt-3">
                    <div class="relative flex items-start">
                        <div class="flex items-center h-5">
                            <input type="checkbox" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                        </div>
                        <div class="ml-3 text-sm">
                            <label class="font-medium text-gray-700">Unsigned</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="mt-3 grid grid-cols-12 gap-y-6 gap-x-4 sm:grid-cols-12">
            <div class="sm:col-span-5">
                <div class="grid grid-cols-12 gap-4">
                    <div class="col-span-2 mt-1">
                        <label class="text-gray-700 ml-2">References</label>
                    </div>
                    <div class="col-span-5">
                        <select v-model="data.reference" @change="onReferenceChanged" class="block focus:ring-indigo-500 focus:border-indigo-500 w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                            <option></option>
                            <option v-for="model in models" :value="model">{{ model.name }}</option>
                        </select>
                    </div>
                    <div class="col-span-5">
                        <select :disabled="!data.reference" v-model="data.foreign" @change="onForeignChosen" class="block focus:ring-indigo-500 focus:border-indigo-500 w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                            <option></option>
                            <option v-for="(type,name) in data.reference.columns" :value="name">{{ name }}</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="sm:col-span-1">
                <div class="mt-3">
                    <div class="relative flex items-start">
                        <div class="flex items-center h-5">
                            <input v-model="data.cascade" type="checkbox" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                        </div>
                        <div class="ml-3 text-sm">
                            <label class="font-medium text-gray-700">Cascade</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="sm:col-span-1">
                <div class="mt-3">
                    <div class="relative flex items-start">
                        <div class="flex items-center h-5">
                            <input type="checkbox" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                        </div>
                        <div class="ml-3 text-sm">
                            <label class="font-medium text-gray-700">Searchable</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="sm:col-span-2">
                <div class="mt-1">
                    <input type="text" placeholder="Values (coma separated)" v-model="data.values" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                </div>
            </div>
            <div class="sm:col-span-2">
                <div class="mt-1">
                    <input type="text" placeholder="Comment" v-model="data.comment" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                </div>
            </div>
            <div class="sm:col-span-1">
                <button type="button" @click="onRemoveColumn" class="uppercase p-3 flex items-center bg-red-500 text-white max-w-max shadow-sm hover:shadow-lg rounded-full w-10 h-10">
                    <svg width="32" height="32" preserveAspectRatio="xMidYMid meet" viewBox="0 0 32 32" style="transform: rotate(360deg);"><path d="M12 12h2v12h-2z" fill="currentColor"></path><path d="M18 12h2v12h-2z" fill="currentColor"></path><path d="M4 6v2h2v20a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8h2V6zm4 22V8h16v20z" fill="currentColor"></path><path d="M12 2h8v2h-8z" fill="currentColor"></path></svg>
                </button>
            </div>
        </div>
        <div class="mt-3 grid grid-cols-12 gap-y-6 gap-x-4 sm:grid-cols-12 mb-3">
            <div class="sm:col-span-2">
                <div class="mt-3">
                    <div class="relative flex items-start">
                        <div class="flex items-center h-5">
                            <input v-model="data.show_on_table" type="checkbox" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                        </div>
                        <div class="ml-3 text-sm">
                            <label class="font-medium text-gray-700">Add to CRUD</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="sm:col-span-2">
                <div class="mt-3">
                    <div class="relative flex items-start">
                        <div class="flex items-center h-5">
                            <input type="text" placeholder="Upload file path" v-model="data.uploads_files_path" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
