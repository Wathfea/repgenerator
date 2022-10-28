<template>
    <form method="POST" @submit="onSubmit">
        <div v-for="(column, key) in columns" v-show="isColumnShown(column.data)">
            <div class="sm:col-span-4 mt-2">
                <label for="username" class="block text-sm font-medium text-gray-700"> {{  getColumnName(key) }} </label>
                <div v-if="column.data.wildcards">
                  <table v-if="column.data.wildcards.length>0" class="">
                    <caption class="text-left font-bold py-2">Használható helyettesítő karakterek</caption>
                    <thead>
                      <tr>
                        <th class="text-left font-bold pr-8 py-1">Helyettesító karakterlánc</th>
                        <th class="text-left font-bold py-1">Ez kerül a helyére</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="(wildcard,index) in column.data.wildcards" :key="'wildcard_'+index" class="border-b border-slate-400 pb-1">
                        <td class="text-red-500 pr-8 py-1">{{wildcard.key}}</td>
                        <td class="py-1">{{wildcard.label}}</td>
                      </tr>
                    </tbody>
                  </table>
                </div>
                <div v-if="column.data.valuesGetter || column.data.values">
                    <ApiMultiselect :column="key" :set-data="column.data" :value="column.model" :columns="columns" :force-disable="disableUpdate" @change="onSelectChange"/>
                </div>
                <div v-else-if="column.data.isAvatar" class="mt-1 mb-4 flex items-center">
                    <PhotoUpload :column="key" :originalData="column.originalData"  :data="column.model" @changed="onPhotoChanged"/>
                </div>
                <div v-else-if="column.data.isUpload" class="mt-1 mb-4 flex items-center">
                    <FileUpload :column="key" :data="column.model" @changed="onFileChanged" :disabled="!isColumnShown(column.data)"/>
                </div>
                <div v-else-if="column.data.isCheckbox" class="relative flex items-start mt-1">
                    <Switch v-model="column.model" :class="[column.model ? 'bg-repgenerator-600' : 'bg-gray-200', 'relative inline-flex flex-shrink-0 h-6 w-11 border-2 border-transparent rounded-full cursor-pointer transition-colors ease-in-out duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-repgenerator-500']">
                        <span aria-hidden="true" :class="[column.model ? 'translate-x-5' : 'translate-x-0', 'pointer-events-none inline-block h-5 w-5 rounded-full bg-white shadow transform ring-0 transition ease-in-out duration-200']" />
                    </Switch>
                </div>
                <div v-else-if="column.data.isFileManager" class="mt-1 mb-4 flex items-center">
                    <FileManagerInput v-model="column.model" :required="column.data.required" :disabled="!isColumnShown(column.data)"/>
                </div>
                <div v-else-if="column.data.isSeo" class="mt-1 block w-full">
                    <SeoArea :column="key" :set-data="column.model"  @changed="onSeoChange"/>
                </div>
                <div v-else-if="column.data.isDate" class="mt-1 block w-full">
                    <ApiDatepicker :column="key" :set-data="column.data" :value="column.model" @changed="onDateChange"></ApiDatepicker>
                </div>
                <div v-else-if="column.data.isEditor" class="mt-1 block w-full">
                    <ApiEditor :column="key" :set-data="column.data" :value="column.model" @changed="onEditorChange"></ApiEditor>
                </div>
                <div v-else-if="column.data.isTranslation" class="mt-1 block w-full">
                  <RadioGroup v-if="column.data.isReusableTranslation" v-model="column.data.translationSelect">
                    <div class="-space-y-px rounded-md bg-white">
                        <div class="p-2 rounded-l-md border border-r-0 border-gray-300 bg-gray-50">
                          <RadioGroupOption as="template" :key="'selectTranslation' + key" value="select" v-slot="{ checked, active }">
                            <div :class="['rounded-tl-md rounded-tr-md', checked ? 'bg-repgenerator-50 border-repgenerator-200 z-10' : 'border-gray-200', 'border p-4 cursor-pointer focus:outline-none']">
                            <span :class="['float-left',checked ? 'bg-repgenerator-600 border-transparent' : 'bg-white border-gray-300', active ? 'ring-2 ring-offset-2 ring-repgenerator-500' : '', 'mt-0.5 h-4 w-4 shrink-0 cursor-pointer rounded-full border flex items-center justify-center']" aria-hidden="true">
                              <span class="rounded-full bg-white w-1.5 h-1.5" />
                            </span>
                            <div class="ml-8 flex flex-col">
                              <RadioGroupLabel as="span" :class="[checked ? 'text-repgenerator-900' : 'text-gray-900', 'block text-sm font-medium']">Meglévő kulcs használata</RadioGroupLabel>
                              <div class="flex gap-4">
                                 <!--<ApiMultiselect :column="key" v-if="column.translationGroups" :set-data="column.translationGroups" @change="onTranslationGroupSelectChange" placeholder="Csoportra szűrés"/>-->
                                 <ApiMultiselect :column="key" v-if="column.translationKeys" :set-data="column.translationKeys" :required="column.data.translationSelect === 'select'" :value="column.model"  :force-disable="disableUpdate" @change="onTranslationKeySelectChange" placeholder="Kulcs kiválasztása"/>
                              </div>
                            </div>
                          </div>
                          </RadioGroupOption>
                          <RadioGroupOption as="template" :key="'newTranslation' + key" value="new" v-slot="{ checked, active }">
                            <div :class="['rounded-bl-md rounded-br-md',checked ? 'bg-repgenerator-50 border-repgenerator-200 z-10' : 'border-gray-200', 'border p-4 cursor-pointer focus:outline-none']">
                            <span :class="['float-left',checked ? 'bg-repgenerator-600 border-transparent' : 'bg-white border-gray-300', active ? 'ring-2 ring-offset-2 ring-repgenerator-500' : '', 'mt-0.5 h-4 w-4 shrink-0 cursor-pointer rounded-full border flex items-center justify-center']" aria-hidden="true">
                              <span class="rounded-full bg-white w-1.5 h-1.5" />
                            </span>
                            <div class="ml-8 flex flex-col">
                              <RadioGroupLabel as="span" :class="[checked ? 'text-repgenerator-900' : 'text-gray-900', 'block text-sm font-medium']">Új kulcs létrehozása</RadioGroupLabel>
                               <div class="flex gap-4">
                                 <ApiMultiselect :column="key" v-if="column.translationGroups" :set-data="column.translationGroups" @change="onTranslationGroupSelectChange" placeholder="Csoportra kiválasztása"/>
                                 <input v-model="column.translationKey" :disabled="false" :required="column.data.translationSelect === 'new'" type="text" class="block w-full min-w-0 rounded-r sm:text-sm border-gray-300" />
                               </div>
                            </div>
                            </div>
                          </RadioGroupOption>
                        </div>
                      </div>
                  </RadioGroup>
                  <div class="flex gap-4">
                    <div v-for="locale in column.data.locales" class="mt-1 flex w-full rounded-md shadow-sm">
                      <span class="inline-flex items-center rounded-l-md border border-r-0 border-gray-300 bg-gray-50 px-3 text-gray-500 sm:text-sm">{{ locale.code2 }}</span>
                      <textarea v-if="column.data.isTextarea" v-model="column.translatedModels[locale.code2]" :disabled="(column.data.disabled || disableUpdate) ?? false"
                                :required="(column.data.required && (column.data.required_local_codes && column.data.required_local_codes.indexOf(locale.code2) >= 0 )) && (column.data.translationSelect === 'new' || !column.data.isReusableTranslation)" class="flex-1 block w-full min-w-0 rounded-r sm:text-sm border-gray-300" style="height:120px">
                            </textarea>
                      <input v-else v-model="column.translatedModels[locale.code2]" :disabled="(column.data.disabled || disableUpdate) ?? false"
                             :required="(column.data.required && (column.data.required_local_codes && column.data.required_local_codes.indexOf(locale.code2) >= 0 )) && (column.data.translationSelect === 'new' || !column.data.isReusableTranslation)" type="text" class="flex-1 block w-full min-w-0 rounded-r sm:text-sm border-gray-300" />
                    </div>
                  </div>
                </div>
                <div v-else-if="column.data.isTextarea" class="mt-1 flex rounded-md shadow-sm">
                    <textarea :style="column.data.style" v-model="column.model" :disabled="(column.data.disabled || disableUpdate) ?? false"
                              :required="column.data.required" type="text" class="flex-1 block w-full min-w-0 rounded sm:text-sm border-gray-300" />
                </div>
                <div v-else-if="column.data.isSimpleText" class="mt-1 flex rounded-md shadow-sm">
                    <p>{{column.model}}</p>
                </div>
                <div v-else class="mt-1 flex rounded-md shadow-sm">
                    <input :style="column.data.style" v-model="column.model" :disabled="(column.data.disabled || disableUpdate) ?? false"
                           :required="column.data.required" type="text" class="flex-1 block w-full min-w-0 rounded sm:text-sm border-gray-300" />
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
import Button from "../Button.vue";
import PhotoUpload from "../PhotoUpload.vue";
import {useRoute} from "nuxt/app";
import FileUpload from "../FileUpload.vue";
import FileManagerInput from "../FileManagerInput.vue";
import ApiMultiselect from "../ApiMultiselect.vue";
import SeoArea from "../SeoArea.vue";
import ApiDatepicker from "../ApiDatepicker.vue";
import ApiEditor from "../ApiEditor.vue";
import { RadioGroup, RadioGroupLabel, RadioGroupOption } from '@headlessui/vue'
import {useTranslationKeys} from "~/src/Domain/TranslationKey/composables/useTranslationKeys.ts";
import {useTranslationGroups} from "~/src/Domain/TranslationGroup/composables/useTranslationGroups.ts";
import {useLocales} from "~/src/Domain/Locale/composables/useLocales.ts";
import {useNotifications} from "~/src/Abstraction/composables/useNotifications.ts";
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
const { getLocales  } = useLocales();
const needsTranslations = ref(false);
for ( let index in props.setColumns ) {
  let data = props.setColumns[index];
  if ( data.isTranslation ) {
    needsTranslations.value = true;
    break;
  }
}
const { getTranslationKeys, getTranslationKey } = useTranslationKeys();
const { getTranslationGroups, getTranslationGroup } = useTranslationGroups();
let cachedTranslationGroups = null;
let cachedTranslationKeys = null;
if ( needsTranslations.value ) {
  cachedTranslationGroups = getTranslationGroups();
  cachedTranslationKeys = getTranslationKeys(1,10);
}
for ( let index in props.setColumns ) {
    let data = props.setColumns[index];
    let setTranslatedModels = null;
    if ( data.isTranslation ) {
      setTranslatedModels = {};
      const locales = await getLocales();
      data.locales = locales.data;
      data.translationSelect = '';
      for ( let localeIndex in data.locales ) {
        setTranslatedModels[data.locales[localeIndex].code2] = ref('');
      }
      needsTranslations.value = true;
    }
    columns.value[index] = {
        data : data,
        model : currentRoute.query.hasOwnProperty(index) ? currentRoute.query[index] : null,
        translatedModels: setTranslatedModels,
        isTranslation: data.isTranslation,
        translationGroupId: null,
        translationKey : '',
        translationGroups: {
          values : [],
          label: 'name',
          searchable: true,
          valuesGetter: () => {
            return cachedTranslationGroups
          },
          valueGetter: getTranslationGroup
        },
        translationKeys: {
          values : [],
          label: 'key',
          searchable: true,
          valuesGetter: (page, perPage, query) => {
            if ( query && query.hasOwnProperty('search') ) {
              return getTranslationKeys(1, 10, query);
            }
            return cachedTranslationKeys;
          },
          valueGetter: getTranslationKey
        },
        originalData : null
    }
}
if ( props.id ) {
    props.dataFunction(props.id).then((response) => {
        let data = response.data;
        for ( let index in columns.value ) {
            let columnData = columns.value[index].data;
            if ( columnData.isTranslation ) {
              let keyForTranslations = index.split('_')[0];
              if ( keyForTranslations !== 'translation' ) {
                keyForTranslations += 'Translations';
              } else {
                keyForTranslations = 'translations';
              }
              const translationData = data[keyForTranslations];
              for ( let localeIndex in translationData ) {
                let translation = translationData[localeIndex];
                let setTranslation = ref(translation.value);
                columns.value[index].translatedModels[translation.locale.code2] = setTranslation;
                if ( translation.locale.code2 === 'en' ) {
                  columnData.translationKey = setTranslation;
                }
              }
            }
            if(columnData.isSettingValue && columnData.hasOwnProperty('getType')){
                switch(columnData.getType(data)){
                    case 'list':
                        columnData.values = data.elements;
                        columnData.label = 'label';
                        columnData.valueProp = 'value';
                        columnData.cellGetter = (model) =>{
                            return model.value;
                        }
                        break;
                    case 'checkbox':
                            columnData.isCheckbox = true;
                        break;
                    case 'full_editor':
                            columnData.isEditor = true;
                            columnData.editorType = 'full';
                            columnData.wildcards = columnData.getWildcards(data);
                        break;
                    case 'textarea':
                        columnData.isTextarea = true;
                        break;
                    case 'lead_editor':
                            columnData.isEditor = true;
                            columnData.editorType = 'lead';
                            columnData.wildcards = columnData.getWildcards(data);
                        break;
                }
            }
            columns.value[index].model = columnData.hasOwnProperty('cellGetter')
                ? columns.value[index].data.cellGetter(data) : ( data.hasOwnProperty(index) ? data[index] : null );
            if ( columnData.isTranslation && columns.value[index].model ) {
              columnData.translationSelect = 'select';
            }
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
const onTranslationKeySelectChange = (changeData) => {
    columns.value[changeData.column].model = changeData.value;
    if ( changeData.data ) {
      for ( let localeIndex in changeData.data.translations ) {
        let translation = changeData.data.translations[localeIndex];
        columns.value[changeData.column].translatedModels[translation.locale.code2] = ref(translation.value);
      }
    }
}
const onTranslationGroupSelectChange = (changeData) => {
  columns.value[changeData.column].translationGroupId = changeData.value;
}
const onFileChanged = (changeData) => {
    columns.value[changeData.column].model = changeData.file;
}
const onSeoChange = (changeData) => {
    columns.value[changeData.column].model = changeData.data;
}
const onDateChange = (changeData) => {
    columns.value[changeData.column].model = changeData.value;
}
const onEditorChange = (changeData) => {
    columns.value[changeData.column].model = changeData.htmlContent;
}
const getColumnName = (key) => {
    if( columns.value[key].data.name ) {
        return columns.value[key].data.name;
    }
    return columns.value[key].data
}
const isColumnShown = (data) => {
    if(data && data.show_if) {
        if(columns.value[data.show_if.column].model === data.show_if.value) {
            return true;
        }
    }

    if ( !data.show_if ) {
        return true;
    }
}
const { addWarning } = useNotifications();
const onSubmit = (e) => {
    e.preventDefault();
    if ( needsTranslations.value ) {
      let errorFound = false;
      for ( let index in columns.value ) {
        const column = columns.value[index];
        const columnData = column.data;
        if ( column.isTranslation && column.isReusableTranslation && columnData.required ) {
          if ( !columnData.translationSelect.length ) {
            addWarning('Hiba!', 'Kérlek válassz fordítási kulcsot a ' + (columnData.name ?? index) + ' oszlopnak!');
            errorFound = true;
          } else if ( columnData.translationSelect === 'select' && !column.model ) {
            addWarning('Hiba!', 'Kérlek válassz fordítási kulcsot a ' + (columnData.name ?? index) + ' oszlopnak!');
            errorFound = true;
          }
        }
      }
      if ( errorFound ) {
        return;
      }
    }
    emit('submit', columns);
}
</script>
