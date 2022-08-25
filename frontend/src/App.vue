<script setup>
import Wizzard from "./components/Wizzard.vue";
import RebuildFrontend from "./components/RebuildFrontend.vue";

import {ref} from "vue";
import imgUrl from './assets/banner.png'

const develop = ref(false);
const choosing = ref(true);

const usingWizzard = ref(false);
const usingRebuildFrontend = ref(false);

const chooseWizzard = () => {
    usingWizzard.value = true;
    choosing.value = false;
}


const chooseRebuildFrontend = () => {
    usingRebuildFrontend.value = true;
    choosing.value = false;
}
</script>
<template>
    <div>
        <div v-if="choosing">
            <header class="p-5 mb-5 bg-[#272822] text-white text-left">
                <h1 class="navbar-brand">
                    <span class="font-bold text-[#F92672]">class</span>
                    Repository
                    <span class="font-bold text-[#F92672]">extends</span>
                    Generator
                </h1>
            </header>
            <div class="grid grid-cols-12 gap-4 px-5">
                <div class="col-span-6">
                    <img :src="imgUrl" alt="Repository Generator Factory" class="banner">
                </div>
                <div class="col-span-6">
                    <div class="code-menu hide-scrollbar z-10 mt-[50vw] h-auto w-full overflow-auto rounded-xl bg-[#272822] px-3 py-6 font-bold text-white lg:relative lg:right-0 lg:ml-auto lg:mt-[80px] lg:w-[385px] xl:right-[5%] 2xl:right-[10%] widescreen:right-[18%]" >
                        <div class="font-bold">
                            <span class="font-bold text-[#F92672]">protected </span><span class="font-bold text-white">$menu </span>
                            <span class="font-bold text-[#F92672]">=</span> [
                            <a class="code-link relative my-2 block rounded border-none pl-4 text-left text-blue hover:bg-blue hover:text-white" style="margin-left: 10px; width: calc(100% - 10px);" href="#" @click="chooseWizzard">
                                <div class="text-[.7rem] font-normal text-white/50">// lets start</div>
                                <span class="font-bold text-[#E6DB74]">"generate" </span>
                                <span class="font-bold text-[#F92672]">=&gt;</span>
                                <span class="font-bold text-[#E6DB74]">"New Domain"</span>
                                <span class="text-white">,</span>
                            </a>

                            <a class="code-link relative my-2 block rounded border-none pl-4 text-left text-blue hover:bg-blue hover:text-white" style="margin-left: 10px; width: calc(100% - 10px);" href="#" @click="chooseRebuildFrontend">
                                <div class="text-[.7rem] font-normal text-white/50">// if need to fix</div>
                                <span class="font-bold text-[#E6DB74]">"re_generate" </span>
                                <span class="font-bold text-[#F92672]">=&gt;</span>
                                <span class="font-bold text-[#E6DB74]">"Old Domain"</span>
                                <span class="text-white">,</span>
                            </a>

                            <a class="code-link relative my-2 block rounded border-none pl-4 text-left text-blue hover:bg-blue hover:text-white" style="margin-left: 10px; width: calc(100% - 10px);" href="#">
                                <div class="text-[.7rem] font-normal text-white/50">// read and learn</div>
                                <span class="font-bold text-[#E6DB74]">"help" </span>
                                <span class="font-bold text-[#F92672]">=&gt;</span>
                                <span class="font-bold text-[#E6DB74]">"How it works"</span>
                                <span class="text-white">,</span>
                            </a>
                            <span class="font-bold">],</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div v-else>
            <Wizzard v-if="usingWizzard"
                     class="bg-white p-10 shadow max-w-screen-2xl mx-auto py-12 px-4 sm:px-6 lg:px-8"/>
            <RebuildFrontend v-else-if="usingRebuildFrontend" class="bg-white p-10 shadow max-w-screen-2xl mx-auto py-12 px-4 sm:px-6 lg:px-8" />
        </div>
    </div>
</template>
