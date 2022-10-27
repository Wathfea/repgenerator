<template>
  <Datepicker 
    :modelValue="value" 
    @update:modelValue="setDate" 
    :enableTimePicker="data.useTimePicker" 
    :range="data.range" 
    :multi-calendars="data.multiCalendars" 
    :format-locale="hu" 
    :format="data.format"  
    locale="hu" 
    cancelText="Mégse" 
    selectText="Kiválasztás"></Datepicker>
</template>


<script setup>
import '@vuepic/vue-datepicker/dist/main.css'
import Datepicker from '@vuepic/vue-datepicker';
import { hu } from 'date-fns/locale';

const emit = defineEmits(['changed']);
const props = defineProps({
  setData: {
    required : true,
    type: Object,
  },
  column: {
    required: true,
    type: String
  },
  value: {
    required : true
  },
});
const setDate = (newValue) => {
  emit('changed', {
    column : props.column,
    value: data.getSaveValue !== undefined ? data.getSaveValue(newValue) : getSaveValue(newValue)
  });
}
const getSaveValue =(date)=>{
    let dateStringArray = date.toISOString().split('T');
    if(data.useTimePicker !== true){
        return dateStringArray[0];
    }
    return dateStringArray[0]+' '+dateStringArray[1].substring(0,8);
}
let data = props.setData;
</script>