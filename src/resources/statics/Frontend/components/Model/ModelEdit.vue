<template>
  <div>
    <ModelTabs :tabs="tabs" class="mb-6" @tabClicked="onTabClicked"/>
    <ModelForm v-if="currentTab === '#details'" :id="id" :setColumns="columns" :data-function="dataFunction"
               :disable-update="!updateMethod" @submit="onSubmit" :is-submitting="isSubmitting"/>
    <slot/>
  </div>
</template>
<script setup>
import ModelForm from "./ModelForm.vue";
import ModelTabs from "./ModelTabs.vue";
import {ref} from "vue";
import {useRoute} from "nuxt/app";
import {useNotifications} from "../../composables/useNotifications.ts";
import {useRouter} from "vue-router";

const emit = defineEmits(['currentTabChanged']);
const router = useRouter();
const currentRoute = useRoute();
const isSubmitting = ref(false);
const props = defineProps({
  columns: {
    type: Object,
    required: true
  },
  id: {
    type: Number,
    required: true
  },
  dataFunction: {
    required: true,
    type: Function
  },
  addTabs: {
    required: false,
    type: Array,
    default: () => {
      return []
    }
  },
  setCurrentTab: {
    required: false,
    type: String,
    default: '#details'
  },
  updateMethod: {
    type: Function,
    required: false,
  }
})
const currentTab = ref(currentRoute.hash.length ? currentRoute.hash : props.setCurrentTab);
const tabs = ref([
  {name: 'Adatok', href: '#details', icon: 'Database', current: currentTab.value === '#details'}
]);
for (let index in props.addTabs) {
  let addTab = props.addTabs[index];
  addTab.current = addTab.href === currentTab.value;
  tabs.value.push(addTab);
}
const onTabClicked = async (tab) => {
  for (let index in tabs.value) {
    tabs.value[index].current = tabs.value[index].name === tab.name;
  }
  currentTab.value = tab.href;
  emit('currentTabChanged', currentTab.value);
  await router.push({path: currentRoute.path, query: {}, hash: currentTab.value, force: true});
}
const {onSuccess} = useNotifications();
const onSubmit = (columns) => {
  let data = {};
  for (let index in columns.value) {
    let value = columns.value[index];
    if ( value.translatedModels ) {
      if ( !data.translatables ) {
        data.translatables = [];
      }
      let translationData = {
        translations: value.translatedModels
      }
      if ( value.model && value.data.translationSelect === 'select' ) {
        translationData.key_id = value.model;
      }
      else if ( value.translationKey ) {
        translationData.key = value.translationKey;
      }
      translationData.column = index;
      translationData.group_id = value.translationGroupId !== null ? value.translationGroupId : value.data.translationGroupId;
      if ( !translationData.key_id && !translationData.key && !value.data.isReusableTranslation ) {
        translationData.key = (value.data.translationPrefix ?? '' ) + translationData.translations['en'].toUpperCase().replace(/\s+/g, '_');
      }
      if (  translationData.key_id || translationData.key ) {
        data.translatables.push(JSON.stringify(translationData));
      }
    } else {
      data[index] = value.model;
    }
  }
  isSubmitting.value = true;
  props.updateMethod(props.id, data).then((response) => {
    isSubmitting.value = false;
  });
}
</script>
