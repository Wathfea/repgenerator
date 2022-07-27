<?php

namespace App\Abstraction\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use ZipArchive;

trait UploadsFiles
{

    private string $filesLocation = '';

    /**
     * @param  Model  $model
     * @param  string  $zipName
     * @param  string  $relationshipName
     * @return BinaryFileResponse|StreamedResponse|null
     */
    public function downloadFiles(
        Model $model,
        string $zipName,
        string $relationshipName = 'files'
    ): BinaryFileResponse|StreamedResponse|null {
        $files = $model->$relationshipName()->get();
        if (!$files->count()) {
            return null;
        }
        if ($files->count() > 1) {
            $zip = new ZipArchive();
            $zipLocation = storage_path('app/'.$this->getDocumentDirectory($model).'/'.$zipName.'.zip');
            if ($zip->open($zipLocation, ZipArchive::CREATE | ZipArchive::OVERWRITE)) {
                foreach ($files as $file) {
                    $fileLocation = storage_path('app/'.$this->getDocumentPath($model, $file));
                    if (File::exists($fileLocation)) {
                        $zip->addFile($fileLocation, $file->name);
                    }
                }
                $zip->close();
            }
            return response()->download($zipLocation);
        } else {
            return Storage::download($this->getDocumentPath($model, $files->first()));
        }
    }


    /**
     * @return string
     */
    public function getFilesLocation(): string
    {
        return $this->filesLocation;
    }

    /**
     * @param  string  $filesLocation
     * @return mixed
     */
    public function setFilesLocation(string $filesLocation): mixed
    {
        $this->filesLocation = $filesLocation;
        return $this;
    }

    /**
     * @param  Model  $model
     * @param  Model  $file
     * @return string
     */
    public function getDocumentPath(Model $model, Model $file): string
    {
        return $this->getDocumentDirectory($model).'/'.$file->getAttribute('name');
    }

    /**
     * @param  Model  $model
     * @return string
     */
    public function getDocumentDirectory(Model $model): string
    {
        return 'public/imgs/'.$this->getFilesLocation().'/'.$model->getAttribute('id');
    }

    /**
     * @param  Model  $model
     * @param  Model  $file
     * @return string
     */
    public function getDocumentStoragePath(Model $model, Model $file): string
    {
        return $this->getDocumentStorageDirectory($model).'/'.$file->getAttribute('name');
    }

    /**
     * @param  Model  $model
     * @return string
     */
    public function getDocumentStorageDirectory(Model $model): string
    {
        return 'storage/imgs/'.$this->getFilesLocation().'/'.$model->getAttribute('id');
    }

    /**
     * @param  Model  $model
     * @param  array  $data
     * @param  string  $key
     * @param  string  $relationshipName
     * @return bool
     */
    public function uploadFiles(
        Model $model,
        array $data,
        string $key = 'files',
        string $relationshipName = 'files'
    ): bool {
        $updatingDocuments = key_exists($key, $data) && !empty($data[$key]);
        $uploadingDocuments = $updatingDocuments && count(array_filter($data[$key], function ($file) {
                return $file->getSize() > 0;
            }));
        if ($uploadingDocuments) {
            return $this->saveFiles($model, $data[$key], $relationshipName);
        } else {
            if (!$updatingDocuments) {
                return $this->removeFiles($model, $relationshipName);
            } else {
                $removingDocuments = count($data[$key]) !== $model->$relationshipName()->count();
                $removedAny = false;
                if ($removingDocuments) {
                    foreach ($model->$relationshipName()->get() as $file) {
                        $found = false;
                        /** @var UploadedFile $keptFile */
                        foreach ($data[$key] as $keptFile) {
                            if ($file->name == $keptFile->getClientOriginalName()) {
                                $found = true;
                                break;
                            }
                        }
                        if (!$found) {
                            $removedAny = true;
                            $this->removeFile($model, $file);
                        }
                    }
                }
                return $removedAny;
            }
        }
    }

    /**
     * @param  Model  $model
     * @param  array  $files
     * @param  string  $relationshipName
     * @return boolean
     */
    public function saveFiles(Model $model, array $files, string $relationshipName = 'files'): bool
    {
        $filesBefore = $model->$relationshipName()->get();
        foreach ($filesBefore as $fileBefore) {
            $found = false;
            /** @var UploadedFile $file */
            foreach ($files as $file) {
                if ($fileBefore->name == $file->getClientOriginalName()) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $this->removeFile($model, $fileBefore);
            }
        }

        $filesNow = $model->$relationshipName()->get();

        /** @var UploadedFile $file */
        foreach ($files as $file) {
            $found = false;
            foreach ($filesNow as $fileNow) {
                if ($fileNow->name == $file->getClientOriginalName()) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $fileName = $file->getClientOriginalName();
                if ($file->storeAs($this->getDocumentDirectory($model), $fileName)) {
                    $model->$relationshipName()->create([
                        'name' => $fileName
                    ]);
                }
            }
        }
        return true;
    }

    /**
     * @param  Model  $model
     * @param  Model  $file
     * @return bool
     */
    public function removeFile(Model $model, Model $file): bool
    {
        Storage::delete($this->getDocumentPath($model, $file));
        $file->delete();
        return true;
    }

    /**
     * @param  Model  $model
     * @param  string  $relationshipName
     * @return bool
     */
    public function removeFiles(Model $model, string $relationshipName = 'files'): bool
    {
        foreach ($model->$relationshipName()->get() as $file) {
            $this->removeFile($model, $file);
        }
        return true;
    }
}
