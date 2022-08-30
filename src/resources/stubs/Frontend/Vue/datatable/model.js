import { ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import {$larafetch} from "~/utils/$larafetch";
import {useNotifications} from "~/composables/useNotifications";

export default function useModel(route, setQuery = true, prefix = 'api/v1/', cacheVersion = null, localCache = false, fixedFilters = []) {
    const model = ref({})
    const models = ref([])

    const errors = ref('')
    const router = useRouter()
    const { addWarning, addSuccess, addInfo } = useNotifications();
    const baseParams  = {
        sort_by: 'id',
        sort_dir: 'asc',
        page: 1,
        per_page: 10,
    };

    const meta = ref({
        current_page : 0,
        last_page : 0,
        from: 0,
        to : 0,
        total: 0
    });

    const getBaseParamsCopy = () => {
        return JSON.parse(JSON.stringify(baseParams));
    }

    const getModels = async (page = null, perPage = null, searchParams = {}, isSearching) => {
        let setParams = Object.assign(getBaseParamsCopy(), {
            page: page,
            per_page: perPage,
        })
        delete searchParams['page'];
        delete searchParams['per_page'];
        Object.assign(setParams, searchParams);
        for (let index in setParams) {
            if (setParams[index] === null || setParams[index] === undefined) {
                delete setParams[index];
            }
        }
        if ( isSearching ) {
            isSearching.value = true;
        }
        const currentRoute = useRoute()
        if ( currentRoute && currentRoute.query !== setParams && setQuery ) {
            await router.push({path: currentRoute.path, query: setParams, hash: currentRoute.hash});
        }
        let requiresCache = false;
        if ( cacheVersion !== null ) {
            let cacheVersionKey = route+'CacheVersion';
            let currentCacheVersion = parseInt(localStorage.getItem(cacheVersionKey));
            if ( !currentCacheVersion || currentCacheVersion !== cacheVersion ) {
                localStorage.setItem(cacheVersionKey, cacheVersion);
                requiresCache = true;
            }
        }

        if ( fixedFilters ) {
            for ( let index in fixedFilters ) {
                let filter = fixedFilters[index];
                setParams[filter.column] = filter.value;
            }
        }
        let paramQuery = buildParams(setParams);
        let cacheKey = route+'Cache?' + paramQuery;
        if ( !requiresCache && localCache && localStorage.getItem(cacheKey)  ) {
            let cacheData = JSON.parse(localStorage.getItem(cacheKey));
            models.value = cacheData.data;
            models.meta = cacheData.meta;
            return cacheData
        }
        let response = await $larafetch(`${prefix}${route}?` + paramQuery, {
            method: 'get',
            cache: !requiresCache ? 'force-cache' : 'default'
        });
        if ( isSearching ) {
            isSearching.value = false;
        }
        models.value = response.data;
        meta.value = response.meta;
        if ( requiresCache && localCache ) {
            localStorage.setItem(cacheKey, JSON.stringify(response));
        }
        return response;
    }

    const buildParams = (data) => {
        const params = new URLSearchParams()
        Object.entries(data).forEach(([key, value]) => {
            if (Array.isArray(value)) {
                value.forEach(value => value && params.append(key+'[]', value.toString()))
            } else {
                params.append(key, value.toString())
            }
        });
        return params.toString()
    }

    const getModel = async (id) => {
        let response = await $larafetch(`${prefix}${route}/${id}`, {
            method: 'get'
        })
        model.value = response.data.data
        meta.value = response.meta;
        return response;
    }

    const searchTimeout = ref(null);
    const searchModel = async (params, perPage, isDelayed, isSearching) => {
        if ( searchTimeout.value ) {
            clearTimeout(searchTimeout.value);
        }
        searchTimeout.value = setTimeout(async () => {
            await getModels(1, perPage, params, isSearching)
        },isDelayed ? 500 : 0);
    }

    const convertDataToFormData = (data) => {
        const formData = new FormData()
        Object.keys(data).forEach(key => {
            if(Array.isArray(data[key])) {
                for(let i in data[key]) {
                    formData.append(key+'[]', data[key][i])
                }
            } else {
                // Temporary fix for sending booleans
                if ( typeof data[key] === 'boolean' ) {
                    data[key] = data[key] ? 1 : 0;
                }
                formData.append(key, data[key])
            }
        });
        return formData;
    }

    const storeModel = async (data) => {
        errors.value = ''
        try {
            await $larafetch(prefix + route, {
                method: 'post',
                body: convertDataToFormData(data),
            });
            await router.push({path: '/' + route });
            addSuccess('Sikeresen mentve', data.message);
        } catch (e) {
            if (e.response && e.response.status === 422) {
                let data = e.response.data || e.response._data;
                for (const key in data) {
                    errors.value += data.errors[key] + ' ';
                }
                addWarning('Sikertelen mentés', data.message);
            }
        }

    }

    const updateModel = async (id, data) => {
        errors.value = ''
        try {
            await $larafetch(prefix + route + '/' + id, {
                method: 'post',
                body: convertDataToFormData(data)
            });
            addSuccess('Sikeresen mentve', data.message);
        } catch (e) {
            if (e.response && e.response.status === 422) {
                for (const key in e.response.data.errors) {
                    errors.value += e.response.data.errors[key][0] + ' ';
                }
                addWarning('Sikertelen mentés', data.message);
            }
        }
    }

    const destroyModel = async (id) => {
        await $larafetch(`${prefix}${route}/${id}`, {
            method: 'delete'
        })
        addSuccess('Sikeresen törölve');
    }

    return {
        errors,
        model,
        models,
        meta,
        getModel,
        getModels,
        searchModel,
        storeModel,
        updateModel,
        destroyModel,
        getBaseParamsCopy
    }
}
