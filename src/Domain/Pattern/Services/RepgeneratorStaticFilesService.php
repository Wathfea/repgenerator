<?php

namespace Pentacom\Repgenerator\Domain\Pattern\Services;

use Pentacom\Repgenerator\Domain\Pattern\Adapters\RepgeneratorStaticFileAdapter;

/**
 * Class RepgeneratorStaticFilesService
 */
class RepgeneratorStaticFilesService
{
    public function __construct(protected string $staticFilesLocation, protected string $staticFrontendFilesLocation)
    {

    }

    public function copyFilesToGivenLocation(array $files, string $framework, string $folderPath  = 'js/Abstraction/'): array
    {
        $generatedFiles = [];
        foreach ($files as $fileOriginal) {
            $fileParts = explode('/', $fileOriginal);
            foreach ( $fileParts as $index => $filePart ) {
                if ( end($fileParts) ==  $filePart ) {
                    continue;
                }
                $partsSoFar = implode('/',array_slice($fileParts,0, $index+1));
                if ( !is_dir(resource_path($folderPath . $partsSoFar))) {
                    mkdir(resource_path($folderPath . $partsSoFar), 0777, true);
                }
            }
            $nameWithExtension = end($fileParts);
            $file = $this->getFrontendStatic($fileOriginal);

            $frontendReplacer = app(RepgeneratorFrontendFrameworkHandlerService::class);
            $file = $frontendReplacer->replaceForFramework($framework, $file);

            if (!file_exists($path = resource_path($folderPath. $fileOriginal))) {
                file_put_contents($path, $file);
                $generatedFiles[] = new RepgeneratorStaticFileAdapter($nameWithExtension, $path);
            }
        }
        return $generatedFiles;
    }

    public function copyStaticFrontendFiles(string $framework, string $folderPath = 'js/Abstraction/'): array {
        $files = [
            "components/DataTable/ColumnHeader.vue",
            "components/DataTable/SearchBadge.vue",
            "components/DataTable/SimpleColumnHeader.vue",
            "components/DataTable/SearchColumnPopup.vue",
            "components/DataTable/Table.vue",
            "components/Model/ModelCreate.vue",
            "components/Model/ModelEdit.vue",
            "components/Model/ModelForm.vue",
            "components/Model/ModelTabs.vue",
            "components/Notification/Notification.vue",
            "components/Notification/Notifications.vue",
            "components/Button.vue",
            "components/PhotoUpload.vue",
            "components/ApiMultiselect.vue",
            "components/FileManagerInput.vue",
            "components/FileUpload.vue",
            "composables/model.js",
            "composables/useNotifications.ts",
            "composables/useUtils.ts",
            "composables/useLocales.ts",
            "router/router.js"
        ];

        $generatedFiles = [];
        foreach ($files as $fileOriginal) {
            $fileParts = explode('/', $fileOriginal);
            foreach ( $fileParts as $index => $filePart ) {
                if ( end($fileParts) ==  $filePart ) {
                    continue;
                }
                $partsSoFar = implode('/',array_slice($fileParts,0, $index+1));
                if ( !is_dir(resource_path($folderPath . $partsSoFar))) {
                    mkdir(resource_path($folderPath . $partsSoFar), 0777, true);
                }
            }
            $nameWithExtension = end($fileParts);
            $file = $this->getFrontendStatic($fileOriginal);

            $frontendReplacer = app(RepgeneratorFrontendFrameworkHandlerService::class);
            $file = $frontendReplacer->replaceForFramework($framework, $file);
            $path = resource_path($folderPath. $fileOriginal);

            file_put_contents($path, $file);
            $generatedFiles[] = new RepgeneratorStaticFileAdapter($nameWithExtension, $path);
        }
        return $generatedFiles;
    }

    /**
     * @return RepgeneratorStaticFileAdapter[]
     */
    public function copyStaticFiles(): array
    {
        $files = [
            "Abstraction/Models/BaseModel.php",
            "Abstraction/Filter/BaseQueryFilter.php",

            "Abstraction/Repository/HasRepositoryService.php",
            "Abstraction/Repository/HasModelRepositoryService.php",
            "Abstraction/Repository/HasPivotRepositoryService.php",
            "Abstraction/Repository/AbstractModelRepositoryService.php",
            "Abstraction/Repository/AbstractPivotRepositoryService.php",
            "Abstraction/Repository/AbstractRepositoryService.php",
            "Abstraction/Repository/ModelRepositoryServiceInterface.php",
            "Abstraction/Repository/PivotRepositoryServiceInterface.php",
            "Abstraction/Repository/RepositoryServiceInterface.php",

            "Abstraction/Controllers/BaseTransactionController.php",
            "Abstraction/Controllers/AbstractController.php",
            "Abstraction/Controllers/ControllerInterface.php",
            "Abstraction/Controllers/ReadOnlyControllerInterface.php",
            "Abstraction/Controllers/ReadWriteControllerInterface.php",

            "Abstraction/Controllers/ARUD/AbstractApiReadOnlyARUDController.php",
            "Abstraction/Controllers/ARUD/AbstractApiReadWriteARUDController.php",
            "Abstraction/Controllers/ARUD/ApiReadOnlyARUDControllerInterface.php",
            "Abstraction/Controllers/ARUD/ApiReadWriteARUDControllerInterface.php",
            "Abstraction/Controllers/ARUD/ARUDControllerInterface.php",
            "Abstraction/Controllers/CRUD/AbstractApiReadOnlyCRUDController.php",
            "Abstraction/Controllers/CRUD/AbstractApiReadWriteCRUDController.php",
            "Abstraction/Controllers/CRUD/ApiReadOnlyCRUDControllerInterface.php",
            "Abstraction/Controllers/CRUD/ApiReadWriteCRUDControllerInterface.php",
            "Abstraction/Controllers/CRUD/CRUDControllerInterface.php",

            "Abstraction/Traits/UploadsFiles.php",
            "Abstraction/Cache/CacheGroup.php",
            "Abstraction/Cache/CacheGroupService.php",
        ];

        $generatedFiles = [];
        foreach ($files as $fileOriginal) {
            $fileParts = explode('/', $fileOriginal);
            $nameWithExtension = end($fileParts);
            $name = explode('.', $nameWithExtension)[0];
            $file = $this->getStatic($name);

            $path = app_path($fileOriginal);
            file_put_contents($path, $file);
            $generatedFiles[] = new RepgeneratorStaticFileAdapter($nameWithExtension, $path);
        }
        return $generatedFiles;
    }

    /**
     * @param  string  $name
     * @return false|string
     */
    public function getStatic(string $name): bool|string
    {
        return file_get_contents($this->staticFilesLocation.$name.".php");
    }

    /**
     * @param  string  $name
     * @return false|string
     */
    public function getFrontendStatic(string $name): bool|string
    {
        return file_get_contents($this->staticFrontendFilesLocation.$name);
    }
}
