<?php

namespace Pentacom\Repgenerator\Domain\Pattern\Services;

use Pentacom\Repgenerator\Domain\Pattern\Adapters\RepgeneratorStaticFileAdapter;

/**
 * Class RepgeneratorStaticFilesService
 */
class RepgeneratorStaticFilesService
{
    public function __construct(protected string $staticFilesLocation) {

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
            "Abstraction/Controllers/AbstractApiReadOnlyCRUDController.php",
            "Abstraction/Controllers/AbstractApiReadWriteCRUDController.php",
            "Abstraction/Controllers/AbstractCRUDController.php",
            "Abstraction/Controllers/CRUDControllerInterface.php",
            "Abstraction/Controllers/ApiCRUDControllerReadOnlyInterface.php",
            "Abstraction/Controllers/ApiCRUDControllerReadWriteInterface.php",
        ];

        $generatedFiles = [];
        foreach ( $files as $fileOriginal ) {
            $fileParts = explode('/', $fileOriginal);
            $nameWithExtension = end($fileParts);
            $name = explode('.', $nameWithExtension)[0];
            $file = $this->getStatic($name);

            if (!file_exists($path = app_path($fileOriginal))) {
                file_put_contents($path, $file);

                $generatedFiles[] = new RepgeneratorStaticFileAdapter($nameWithExtension, $path);
            }
        }
        return $generatedFiles;
    }

    /**
     * @param  string  $name
     * @return false|string
     */
    public function getStatic(string $name): bool|string
    {
        return file_get_contents($this->staticFilesLocation . $name . ".php");
    }
}