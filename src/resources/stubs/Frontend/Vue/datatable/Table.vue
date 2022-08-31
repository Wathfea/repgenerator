<template>
  <div class="px-4 sm:px-6 lg:px-8">
    <div class="flex flex-col">
      <div class="-my-2 -mx-4 sm:-mx-6 lg:-mx-8">
        <div class="mb-4 flex justify-end">
          <NuxtLink :to="`/${route}/create`">
            <Button>Új {{ modelReadableName }}</Button>
          </NuxtLink>
        </div>
        <div class="shadow-sm bg-gray-50 rounded">
          <div class="p-3">
            <label for="email" class="block text-sm font-medium text-gray-700">Keresés</label>
            <div class="mt-1 flex rounded-md shadow-sm">
              <div class="relative flex items-stretch flex-grow focus-within:z-10">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <SearchIcon class="h-5 w-5 text-gray-400" aria-hidden="true" />
                </div>
                <input type="text" v-model="searchInput" v-on:keyup="onSearchAll" class="block w-full rounded-none rounded-l-md pl-10 sm:text-sm border-gray-300 focus:border-vagheggi-800 focus:shadow-vagheggi-800 focus:ring-0" placeholder="Keresés az összes oszlopban" />
              </div>
              <Button :disabled="!searchInput.length" @click="onSearchAllCleared" type="button" class="-ml-px relative inline-flex items-center space-x-2 px-4 py-2 border border-gray-300 text-sm font-medium rounded-r-md text-gray-700 bg-gray-50 hover:bg-gray-100">
                <XIcon :class="`h-5 w-5 text-${ searchInput.length ? 'vagheggi-800' : 'gray-400'}`" aria-hidden="true" />
              </Button>
            </div>
            <div class="mt-3">
              <SearchBadge class="mr-2" v-for="(search,key) in searchColumns" :name="getColumnName(key)" :column="key" :search="search" :data="columns[key]" @onRemove="onRemoveSearch" @openSearch="onOpenSearch"/>
            </div>
          </div>
        </div>
        <div class="overflow-x-scroll overflow-y-hidden">
          <div class="inline-block min-w-full py-2 align-middle">
            <table class="min-w-full divide-y divide-gray-300 w-max overflow-hidden">
              <thead class="bg-gray-50">
              <tr>
                <th :style="{ width: column.data.width ? column.data.width : 'auto' }" v-for="(column,key) in columns" :key="key" scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">

                  <SearchColumnPopup v-if="column.isSearchOpen" :data="column.data" :set-search="searchColumns[key] ? searchColumns[key].value : ''"
                                     :name="getColumnName(key)" @close="onCloseSearch" :column="key" @search="onSearchChanged"/>
                  <slot
                      :name="`header(${key})`"
                      :value="column.data"
                      :item="column"
                  >
                    <ColumnHeader
                        :data="column.data"
                        :search="searchColumns[key] ? searchColumns[key].value : ''"
                        :is-searching="isSearching"
                        :sort-direction="column.sortDirection"
                        :column="key"
                        @toggleSort="onSortChanged"
                        @clearSortAndSearch="onSearchAndSortCleared"
                        @openSearch="onOpenSearch"
                    />
                  </slot>
                </th>
                <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6 lg:pr-8">
                  <span class="sr-only">Edit</span>
                </th>
              </tr>
              </thead>
              <tbody class="divide-y divide-gray-200 bg-white">
              <tr v-for="model in models" :key="model.id">
                <td v-for="(column,key) in columns" :key="key" class="whitespace-nowrap px-3 py-4 text-sm text-gray-500"
                    :style="{ textAlign : column.data.align ? column.data.align : 'auto',
                    width: column.data.width ? column.data.width : 'auto'
                }">
                  <slot
                      :name="`cell(${key})`"
                      :value="getCellData(column, key, model)"
                      :item="model"
                  >
                    {{ getCellData(column, key, model) }}
                  </slot>
                </td>
                <td class="flex justify-end relative whitespace-nowrap py-4 px-3 text-sm font-medium sm:pr-6 lg:pr-8">
                  <NuxtLink :to="`/${route}/${model.id}`" class="text-vagheggi-600 hover:text-vagheggi-900">
                    <PencilIcon class="h-6 w-6" aria-hidden="true" />
                  </NuxtLink>
                  <NuxtLink @click="deleteModel(model.id)" class="text-vagheggi-600 hover:text-vagheggi-900 ml-2 cursor-pointer">
                    <TrashIcon class="h-6 w-6" aria-hidden="true" />
                  </NuxtLink>
                </td>
              </tr>
              </tbody>
            </table>
          </div>
        </div>
        <nav class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6" aria-label="Pagination">
          <div class="hidden sm:block">
            <p class="text-sm text-gray-700">
              <span class="font-medium">{{ Math.max(0,meta.from) }}</span>
              {{ ' ' }}
              és
              {{ ' ' }}
              <span class="font-medium">{{ Math.max(0,meta.to)  }}</span>
              {{ ' ' }}
              közötti sorok
              {{ ' ' }}
              <span class="font-medium">{{ meta.total }}</span>
              {{ ' ' }}
              találatból
            </p>
          </div>
          <div class="flex-1 flex justify-between sm:justify-end">
            <Button :busy="paginatingLeft && !isPreviousPageDisabled" :disabled="paginatingLeft || isPreviousPageDisabled" @click="prevPage">
              <ArrowCircleLeftIcon class="m-auto h-5 w-5" aria-hidden="true" />
            </Button>
            <div class="sm:hidden block">
              <p class="text-sm text-gray-700 pt-3">
                <span class="font-medium">{{  Math.max(0,meta.from)  }}</span>
                {{ '-' }}
                <span class="font-medium">{{ Math.max(0,meta.to) }}</span>
                {{ '/' }}
                <span class="font-medium">{{ meta.total }}</span>
              </p>
            </div>
            <Button :busy="paginatingRight && !isNextPageDisabled" :disabled="paginatingRight || isNextPageDisabled" @click="nextPage" class="ml-3">
              <ArrowCircleRightIcon class="h-5 w-5 m-auto" aria-hidden="true" />
            </Button>
          </div>
        </nav>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref, computed } from 'vue';
import {
  PencilIcon,
  TrashIcon,
  ArrowCircleLeftIcon,
  ArrowCircleRightIcon,
  SearchIcon,
  XIcon
} from '@heroicons/vue/outline'
import Button from "~/components/Button";
import ColumnHeader from "~/components/DataTable/ColumnHeader";
import useModel from "~/components/model";
import {useRoute} from "vue-router/dist/vue-router";
import SearchBadge from "~/components/DataTable/SearchBadge";
import SearchColumnPopup from "~/components/DataTable/SearchColumnPopup";

const currentRoute = useRoute()
const props = defineProps({
  modelReadableName : {
    type: String,
    required: false,
    default: 'bemenet'
  },
  setColumns: {
    type: Object,
    required: true
  },
  route: {
    type: String,
    required: true
  },
  fixedFilters: {
    required: false,
    type: Array,
    default : () => {
      return []
    }
  }
});
const { models, meta, getModels, searchModel, destroyModel, getBaseParamsCopy } = useModel(props.route, true, 'api/v1/', false, false, props.fixedFilters);


const columns = ref({});
const baseParams = getBaseParamsCopy();
let setQuery = JSON.parse(JSON.stringify(currentRoute.query));
setQuery = Object.assign(getBaseParamsCopy(), setQuery);
let setSearchColumns = {};
let columnSearch, searchKey, searchValue, searchColumnData, searchColumn;
if ( !Array.isArray(setQuery['searchColumns']) ) {
  setQuery['searchColumns'] = [setQuery['searchColumns']];
}
for ( let index in setQuery['searchColumns'] ) {
  if ( setQuery['searchColumns'].hasOwnProperty(index) && setQuery['searchColumns'][index]) {
    columnSearch = setQuery['searchColumns'][index].split(':');
    searchColumnData = columnSearch[0].indexOf('.') > 0 ? columnSearch[0].split('.') : null;
    searchKey = searchColumnData ? searchColumnData[0] : columnSearch[0];
    searchColumn = searchColumnData ? '.' + searchColumnData[1] : '';
    searchValue = columnSearch[1];
    setSearchColumns[searchKey] = {
      value: searchValue,
      column: searchColumn
    };
  }
}
const searchColumns = ref(setSearchColumns ?? {});
const searchParams = ref({});
for ( let index in props.setColumns ) {
  let data = props.setColumns[index];
  if ( data.valuesGetter ) {
    data.valuesLoading = ref(true);
    data.values = ref([]);
    data.valuesGetter().then((response) => {
      data.values.value = response.data;
      data.valuesLoading.value = false;
    })
  }
  columns.value[index] = {
    data : data,
    sortDirection : setQuery.sort_by === index ? setQuery.sort_dir : null,
    isSearchOpen: false
  }
}
const currentPage = ref(1);
const perPage = 10;

let paginatingLeft = ref(false);
let paginatingRight = ref(false);
let searchInput = ref(currentRoute.query.search ?? '');
let user = reactive({
  id: 0,
})

const getCellData = (column, key, model) => {
  if ( column.data.cellGetter ) {
    return column.data.cellGetter(model);
  }
  return model[key];
}

const prevPage = async () => {
  paginatingLeft.value = true;
  await getModels(--currentPage.value, perPage, currentRoute.query);
  paginatingLeft.value = false;
}

const nextPage = async () => {
  paginatingRight.value = true;
  await getModels(++currentPage.value, perPage, currentRoute.query);
  paginatingRight.value = false;
}

const isPreviousPageDisabled =  computed(()  => {
  return meta.value.current_page <= 1;
});
const isNextPageDisabled = computed(() => {
  return meta.value.current_page === meta.value.last_page;
});

const onSortChanged = (column) => {
  let setSort;
  switch(columns.value[column].sortDirection) {
    case 'desc' :
    case null:
      setSort = 'asc';
      break;
    default:
      setSort = 'desc'
      break;
  }
  for ( let index in columns.value ) {
    columns.value[index].sortDirection = null;
  }
  columns.value[column].sortDirection = setSort;
  searchParams.value.sort_by = column;
  searchParams.value.sort_dir = setSort;
  refreshSearch(false);
}
const onSearchAndSortCleared = (column) => {
  columns.value[column].sortDirection = null;
  searchParams.value['sort_by'] = baseParams['sort_by'];
  searchParams.value['sort_dir'] = baseParams['sort_dir'];
  columns.value[baseParams['sort_by']].sortDirection = baseParams['sort_dir'];
  onSearchChanged({
    name: column,
    value: ''
  })
}
const onSearchChanged = (searchData) => {
  let columnData = columns.value[searchData.name].data;
  let searchOnColumn =  columnData.valuesGetter ? (columnData.column ? ('.' + columnData.column) : '') : '';
  if ( !searchData.value ) {
    delete searchColumns.value[searchData.name];
  } else {
    if ( !searchColumns.value[searchData.name] ||
        !searchColumns.value[searchData.name].hasOwnProperty('value') ) {
      searchColumns.value[searchData.name] = {
        value : null,
        column: searchOnColumn
      }
    }
    searchColumns.value[searchData.name].value = searchData.value;
  }
  let setSearchColumns = [];
  for ( let index in searchColumns.value ) {
    if ( searchColumns.value[index] ) {
      setSearchColumns.push(index+searchColumns.value[index].column+':'+searchColumns.value[index].value);
    }
  }
  searchParams.value.searchColumns = setSearchColumns
  refreshSearch(searchData.value !== '');
}
const onCloseSearch = (column) => {
  columns.value[column].isSearchOpen = false;
}
const isSearching = ref(false);
const refreshSearch = (isDelayed = true) => {
  searchModel(searchParams.value, perPage, isDelayed, isSearching)
}

const onSearchAll = (e) => {
  searchParams.value.search = searchInput.value;
  refreshSearch();
}
const onSearchAllCleared = () => {
  searchParams.value.search = searchInput.value = '';
  refreshSearch(false);
}

const deleteModel = async (id) => {
  if (!window.confirm('Biztos törli?')) {
    return
  }

  await destroyModel(id)
  await getModels(1, perPage, currentRoute.query)
}

const getColumnName = (key) => {
  if( props.setColumns[key].name ) {
    return props.setColumns[key].name;
  }
  return props.setColumns[key];
}

const onRemoveSearch = (column) => {
  onSearchChanged({
    name: column,
    value: ''
  })
}
const onOpenSearch = (column) => {
  columns.value[column].isSearchOpen = true;
}

getModels(currentRoute.query.page ?? 1, currentRoute.query.per_page ?? perPage, currentRoute.query)
</script>
