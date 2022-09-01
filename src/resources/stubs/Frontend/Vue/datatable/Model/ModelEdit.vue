<template>
  <div class="">
    <ModelTabs :tabs="tabs" class="mb-6" @tabClicked="onTabClicked"/>
    <ModelForm v-if="currentTab === '#adatok'" :id="id" :setColumns="columns" :data-function="dataFunction" @submit="onSubmit" :is-submitting="isSubmitting"/>
    <slot/>
  </div>
</template>
<script setup>
  import ModelForm from "~/components/Model/ModelForm";
  import useModel from "~/components/model";
  import ModelTabs from "~/components/Model/ModelTabs";
  import {ref} from "vue";
  import { DatabaseIcon } from '@heroicons/vue/solid'
  import {useRouter} from "vue-router";
  import {useRoute} from "vue-router/dist/vue-router";
  import {useNotifications} from "~/composables/useNotifications";
  const emit = defineEmits(['currentTabChanged']);
  const router = useRouter();
  const currentRoute = useRoute();
  const isSubmitting = ref(false);
  const props = defineProps({
      columns : {
        type: Object,
        required: true
      },
      id : {
        type: Number,
        required: true
      },
      dataFunction: {
        required: true,
        type: Function
      },
      addTabs : {
        required: false,
        type: Array,
        default : () => {
          return []
        }
      },
      setCurrentTab : {
        required : false,
        type: String,
        default: '#adatok'
      },
      updateMethod : {
        type: Function,
        required: true,
      }
  })
  const currentTab = ref(currentRoute.hash.length ? currentRoute.hash : props.setCurrentTab);
  const tabs = ref([
    { name: 'Adatok', href: '#adatok', icon: DatabaseIcon, current: currentTab.value === '#adatok' }
  ]);
  for ( let index in props.addTabs ) {
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
    await router.push({ path: currentRoute.path, query : {}, hash: currentTab.value, force: true});
  }
  const { onSuccess } = useNotifications();
  const onSubmit = (columns) => {
    let data = {};
    for ( let index in columns.value ) {
      data[index] = columns.value[index].model;
    }
    isSubmitting.value = true;
    props.updateMethod(props.id, data).then((response) => {
      isSubmitting.value = false;
    });
}
</script>