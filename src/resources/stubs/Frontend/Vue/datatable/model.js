import { ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import {$larafetch} from "~/utils/$larafetch";

export default function useModel(route, setQuery = true, prefix = 'api/v1/') {
    const model = ref({})
    const models = ref([])

    const errors = ref('')
    const router = useRouter()
    const currentRoute = useRoute()
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
        Object.assign(setParams, searchParams);
        for (let index in setParams) {
            if (setParams[index] === null || setParams[index] === undefined) {
                delete setParams[index];
            }
        }
        if ( isSearching ) {
            isSearching.value = true;
        }
        if ( currentRoute.query !== setParams && setQuery ) {
            await router.push({path: currentRoute.path, query: setParams});
        }
        let response = await $larafetch(`${prefix}${route}?` + buildParams(setParams), {
            method: 'get'
        });
        if ( isSearching ) {
            isSearching.value = false;
        }
        models.value = response.data;
        meta.value = response.meta;
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

    const storeModel = async (data) => {
        errors.value = ''
        try {
            let formData = new FormData()
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
            await $larafetch(route, {
                method: 'post',
                data: formData,
                headers: {
                    'Content-Type': 'multipart/form-data'
                }
            });
            await router.push({ name: `${route}.index` })
        } catch (e) {
            if (e.response.status === 422) {
                for (const key in e.response.data.errors) {
                    errors.value += e.response.data.errors[key][0] + ' ';
                }
            }
        }

    }

    const updateModel = async (id) => {
        errors.value = ''
        try {
            await $larafetch(`${prefix}${route}/${id}`, {
                method: 'patch',
                data: model.value
            });
            await router.push({ name: `${route}.index` })
        } catch (e) {
            if (e.response.status === 422) {
                for (const key in e.response.data.errors) {
                    errors.value += e.response.data.errors[key][0] + ' ';
                }
            }
        }
    }

    const destroyModel = async (id) => {
        await $larafetch(`${prefix}${route}/${id}`, {
            method: 'delete'
        })
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
