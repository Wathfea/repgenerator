import {ref} from "vue";

type Notification = {
    id: number,
    title: string;
    description?: string;
};


// Value is initialized in: ~/plugins/auth.ts
export const useNotification = () => {
    return useState<Notification[]>("notification", () => []);
};

export const useNotifications  = () => {
    const notifications = useNotification();
    const id = ref(1);
    const removeNotification = (id) => {
        for ( let index in notifications.value ) {
            if ( notifications.value[index].id === id ) {
                // @ts-ignore
                notifications.value.splice(index, 1);
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
        }
        notifications.value.push(notificationObject);
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