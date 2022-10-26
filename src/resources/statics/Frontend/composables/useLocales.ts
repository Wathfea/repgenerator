import useModel from "./model.js";
import {useUtils} from "./useUtils.ts";

export const useLocales  = () => {
    const getRoute = () => {
        return 'locales';
    }
    const getEditableColumns = () => {
        return {
            'name': {
                name: 'Név',
            },
            'native_name' : {
                name: 'Natív név',
                required: false
            },
            'code2': {
                name: 'Code 2',
            },
            'status': {
                name: 'Status',
                isCheckbox: true,
                cellGetter: (model) => {
                    return model.status;
                },
                column: 'status'
            },
        }
    }
    const getDataTableColumns = () => {
        const { getFilteredObject } = useUtils();
        return  {
            'id': {
                name: 'ID',
                width: '120px',
                align: 'right',
                isSorting: true,
            },
            ...getFilteredObject(getEditableColumns(), [
                'native_name'
            ]),
            'created_at': {
                name: 'Létrehozva',
                isDate: true
            }
        }
    }
    const getColumns = () => {
        return getEditableColumns();
    }
    const { getModels: getLocales, getModel: getLocale, updateModel: updateLocale, storeModel: storeLocale } = useModel('locales', false, 'api/v1/', 1, true);
    return {
        getColumns,
        getLocales,
        getLocale,
        storeLocale,
        updateLocale,
        getDataTableColumns,
        getRoute
    }
}
