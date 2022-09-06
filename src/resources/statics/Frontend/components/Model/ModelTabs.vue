<template>
  <div>
    <div class="sm:hidden">
      <label for="tabs" class="sr-only">Select a tab</label>
      <select id="tabs" name="tabs" class="block w-full focus:ring-vagheggi-500 focus:border-vagheggi-500 border-gray-300 rounded-md">
        <option v-for="tab in tabs" :key="tab.name" :selected="tab.current">{{ tab.name }}</option>
      </select>
    </div>
    <div class="hidden sm:block">
      <nav class="relative z-0 rounded-lg shadow flex divide-x divide-gray-200" aria-label="Tabs">
        <a @click="onTabClicked(tab)" v-for="(tab, tabIdx) in tabs" :key="tab.name" :href="tab.href" :class="[tab.current ? 'text-gray-900' : 'text-gray-500 hover:text-gray-700', tabIdx === 0 ? 'rounded-l-lg' : '', tabIdx === tabs.length - 1 ? 'rounded-r-lg' : '', 'group relative min-w-0 flex-1 overflow-hidden bg-white py-4 px-4 text-sm font-medium text-center hover:bg-gray-50 focus:z-10']" :aria-current="tab.current ? 'page' : undefined">
         <div class="flex justify-center">
           <component :is="tab.icon" :class="[tab.current ? 'text-vagheggi-500' : 'text-gray-400 group-hover:text-gray-500', '-ml-0.5 mr-2 h-5 w-5']" aria-hidden="true" />
           <span>{{ tab.name }}</span>
           <span aria-hidden="true" :class="[tab.current ? 'bg-vagheggi-500' : 'bg-transparent', 'absolute inset-x-0 bottom-0 h-0.5']" />
         </div>
        </a>
      </nav>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  tabs : {
    required : true,
    type: Array
  }
})
const emit = defineEmits(['tabClicked']);
const onTabClicked = (tab) => {
  emit('tabClicked', tab);
}
</script>
