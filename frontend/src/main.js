import {createApp} from 'vue'
import App from './App.vue'
import './assets/app.css'

const app = createApp(App)

// Register a global custom directive called `v-focus`
app.directive('focus', {
    // When the bound element is mounted into the DOM...
    mounted(el) {
        // Focus the element
        el.focus()
    }
})

app.mount('#app')
