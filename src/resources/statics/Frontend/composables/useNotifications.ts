import {ref, reactive} from "vue";

type Notification = {
    id: number,
    title: string;
    description?: string;
};

export const useNotifications  = () => {
    const notifications = reactive([])
    const id = ref(1);
    const removeNotification = (id) => {
        for ( let index in notifications ) {
            if ( notifications[index].id === id ) {
                // @ts-ignore
                notifications.splice(index, 1);
                break;
            }
        }
    }
    const addWarning = (title, description, seconds) => {
        addNotification('warning', title, description, seconds);
    }
    const addSuccess = (title, description, seconds) => {
        addNotification('success', title, description, seconds);
    }
    const addInfo = (title, description, seconds) => {
        addNotification('info', title, description, seconds);
    }
    const addNotification = (type, title, description, seconds = 10) => {

        let setId = id.value;
        ++id.value;
        let notificationObject = {
            id: setId,
            type: type,
            title: title,
            description: description,
        } as Notification

        notifications.push(notificationObject);
        setTimeout(() => {
            removeNotification(setId);
        }, seconds * 1000)
    }
    return {
        notifications,
        addSuccess,
        addWarning,
        addInfo,
    }
}
