export const useUtils  = () => {

    const getFilteredObject = (obj, filter) => {
        return Object.fromEntries(Object.entries(obj).filter(([key]) => !filter.includes(key)));
    }

    return {
        getFilteredObject
    }
}