<script setup>
const props = defineProps({
    colors: {
        type: Array,
        required: false,
        default: () => {
            return []
        }
    }
})
</script>

<template>
    <div class="bg-white p-10 shadow max-w-screen-2xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
        <p>Add this block to you tailwind.config.js</p><br />
        <code class="block whitespace-pre">
            colors : {<br />
            &emsp;repgenerator : {<br />
                    <span v-for="(color, key) in colors">
                        &emsp;&emsp;{{ 100*(key+1) }} : '{{color}}',<br />
                    </span>
                     &emsp;&emsp;bg : '#04040C',<br />
            &emsp;}<br />
            }<br />
        </code>
    </div>
</template>
