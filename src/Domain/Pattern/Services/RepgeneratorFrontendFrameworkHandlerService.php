<?php

namespace Pentacom\Repgenerator\Domain\Pattern\Services;


/**
 * Class RepgeneratorStaticFilesService
 */
class RepgeneratorFrontendFrameworkHandlerService
{

    public function replaceForFramework(string $framework, $fileContent) {
        $replacements = [];
        switch($framework) {
            case 'vue':
                $replacements =  [
                    'import {useRoute} from "nuxt/app";' => 'import {useRoute} from "vue-router";',
                    'NuxtLink' => 'RouterLink'
                ];
                break;
        }
        foreach ( $replacements as $find => $replacement ) {
            $fileContent = str_replace($find, $replacement, $fileContent);
        }
        return $fileContent;
    }
}
