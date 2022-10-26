<template>
  <Editor
    api-key="no-api-key"
    :init="editorSettings[data.editorType || 'full']"
    @change="changeValue"
    @input="changeValue"
    @keyup="changeValue"
    @paste="changeValue"
    @copy="changeValue"
    v-model="value"
  />
</template>


<script setup>
import Editor from '@tinymce/tinymce-vue'
import {contentStyles} from "./ApiEditorConfigs/contentStyles";
import {dialogConfigs} from "./ApiEditorConfigs/dialogConfigs";
import {icons} from "./ApiEditorConfigs/icons";

const config = useRuntimeConfig();

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

const changeValue = (event,editor) => {
    emit('changed', {
        column : props.column,
        htmlContent: props.value
    });
}

const editorSettings = {
    full: {
        plugins: [
            'advlist autolink lists link image charmap print preview hr anchor pagebreak',
            'searchreplace wordcount visualblocks visualchars code fullscreen',
            'insertdatetime media nonbreaking save table directionality',
            'emoticons template paste textpattern'
        ],
        toolbar: 'insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image media | responsiveYoutube',
        height:500,
        extended_valid_elements : "a[*]",
        setup : function(ed) {
            //add icons
            ed.ui.registry.addIcon('repgeneratorYoutube', icons.youtube(28) );
            //add buttons
            ed.ui.registry.addButton('responsiveYoutube', {
                icon: 'repgeneratorYoutube',
                tooltip: 'Reszponzív youtube videó beágyazása',
                onAction : () => ed.windowManager.open(dialogConfigs().youtube),
            });
        },
        file_picker_callback : function(callback, value, meta) {
            var x = window.innerWidth || document.documentElement.clientWidth || document.getElementsByTagName('body')[0].clientWidth;
            var y = window.innerHeight|| document.documentElement.clientHeight|| document.getElementsByTagName('body')[0].clientHeight;

            var cmsURL = config.public.backendUrl +'/'+ config.public.publicFilemanagerPath +'?editor=' + meta.fieldname;
            if (meta.filetype == 'image') {
                cmsURL = cmsURL + "&type=Images";
            } else {
                cmsURL = cmsURL + "&type=Files";
            }

            tinyMCE.activeEditor.windowManager.openUrl({
                url : cmsURL,
                title : 'Filemanager',
                width : x * 0.8,
                height : y * 0.8,
                resizable : "yes",
                close_previous : "no",
                onMessage: (api, message) => {
                if(message.fileManagerTinymceCallback){
                    callback(message.content);
                }
                }
            });
        },
        content_style: '' + contentStyles.youtube,
    },
    lead: {
        menubar: false,
        plugins: 'emoticons',
        toolbar: 'undo redo | styleselect | bold italic  | emoticons',
        height:150,
        content_style: '',
    },
};

let data = props.setData;
</script>
