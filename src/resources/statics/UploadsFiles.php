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

    private array $filesLocation = [];

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
     * @param $field
     * @return string
     */
    public function getFilesLocation($field): string
    {
        return array_key_exists($field, $this->filesLocation) ? $this->filesLocation[$field] : '';
    }

    /**
     * @param  array  $filesLocation
     * @return mixed
     */
    public function setFilesLocation(array $filesLocation): mixed
    {
        $this->filesLocation = $filesLocation;
        return $this;
    }

    /**
     * @param  Model  $model
     * @param  string  $field
     * @param  Model  $file
     * @return string
     */
    public function getDocumentPath(Model $model, string $field, Model $file): string
    {
        return $this->getDocumentDirectory($model, $field).'/'.$file->getAttribute('name');
    }

    /**
     * @param  Model  $model
     * @param  string  $field
     * @return string
     */
    public function getDocumentDirectory(Model $model, string $field): string
    {
        return 'public/files/'.$this->getFilesLocation($field).'/'.$model->getAttribute('id');
    }

    /**
     * @param  Model  $model
     * @param  string  $field
     * @param  Model  $file
     * @return string
     */
    public function getDocumentStoragePath(Model $model, string $field, Model $file): string
    {
        return $this->getDocumentStorageDirectory($model, $field).'/'.$file->getAttribute('name');
    }

    /**
     * @param  Model  $model
     * @param  string  $field
     * @return string
     */
    public function getDocumentStorageDirectory(Model $model, string $field): string
    {
        return 'storage/files/'.$this->getFilesLocation($field).'/'.$model->getAttribute('id');
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
        array $keys = ['files'],
        string $relationshipName = 'files'
    ): bool {
        $saved = true;
        $removed = true;
        $removedAny = true;

        foreach ($keys as $key) {
            $updatingDocuments = key_exists($key, $data) && !empty($data[$key]);
            $uploadingDocuments = $updatingDocuments && count(array_filter($data[$key], function ($file) {
                    return $file->getSize() > 0;
                }));

            if ($uploadingDocuments) {
                $saved = $this->saveFiles($model, $key, $data[$key], $relationshipName);
            } else {
                if (!$updatingDocuments) {
                    //TODO Tomival megnézni
                    //$removed = $this->removeFiles($model, $key, $relationshipName);
                } else {
                    $removingDocuments = count($data[$key]) !== $model->$relationshipName()->count();
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
                                $this->removeFile($model, $key, $file);
                            }
                        }
                    }
                }
            }
        }

        return ($saved || $removed || $removedAny);
    }

    /**
     * @param  Model  $model
     * @param  string  $field
     * @param  array  $files
     * @param  string  $relationshipName
     * @return boolean
     */
    public function saveFiles(Model $model, string $field, array $files, string $relationshipName = 'files'): bool
    {
        $filesBefore = $model->$relationshipName()->where('field', $field)->get();
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
                $this->removeFile($model, $field, $fileBefore);
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
                if ($file->storeAs($this->getDocumentDirectory($model, $field), $fileName)) {
                    $data = [
                        'name' => $fileName,
                        'field' => $field
                    ];
                    $model->$relationshipName()->create($data);
                }
            }
        }
        return true;
    }

    /**
     * @param  Model  $model
     * @param  string  $field
     * @param  Model  $file
     * @return bool
     */
    public function removeFile(Model $model, string $field, Model $file): bool
    {
        Storage::delete($this->getDocumentPath($model, $field, $file));
        $file->delete();
        return true;
    }

    /**
     * @param  Model  $model
     * @param  string  $field
     * @param  string  $relationshipName
     * @return bool
     */
    public function removeFiles(Model $model, string $field, string $relationshipName = 'files'): bool
    {
        foreach ($model->$relationshipName()->get() as $file) {
            $this->removeFile($model, $field, $file);
        }
        return true;
    }
}
