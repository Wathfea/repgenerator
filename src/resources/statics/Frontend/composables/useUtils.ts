export const useUtils  = () => {

    const getFilteredObject = (obj, filter) => {
        return Object.fromEntries(Object.entries(obj).filter(([key]) => !filter.includes(key)));
    }

    const hasFilter = (name, filters) => {
        for ( let index in filters ) {
            if ( filters[index].column === name ) {
                return true;
            }
        }
        return false;
    }

    return {
        hasFilter,
        getFilteredObject
    }
}