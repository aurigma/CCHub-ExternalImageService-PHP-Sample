<?php

namespace app\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Request;
use app\Models\ImageInfoModel;
use app\Models\ImageFileInfoModel;
use Illuminate\Support\Str;
use App\Exceptions\ConflictException;

class ImageService 
{

    public function __construct()
    {
    }

    public function create($file, $strategy)
    {
        $fileInputData = $this->buildFileInputData($file);
        $existingFile = $this->existingFileInfo($fileInputData[0], $fileInputData[1]);

        if ($existingFile) {
            return $this->saveFileInfoWithUsingStrategy($fileInputData, $file, $strategy, $existingFile);
        } else {
            return $this->saveFileInfo($fileInputData, $file);
        }
    }

    private function saveFileInfo($fileInputData, $file)
    {
        $id = Str::uuid()->toString();
        $file->storeAs('uploads', $fileInputData[0]);
        
        $existingFile = $this->createFileInfoModel($id, $fileInputData[0], $fileInputData[1]);

        return $this->buildImageInfoModel($existingFile);
    }

    private function saveFileInfoWithUsingStrategy($fileInputData, $file, $strategy, $existingFile)
    {
        switch ($strategy) {
            case 'Overwrite':
                Storage::delete($fileInputData[2]);
                $file->storeAs('uploads', $fileInputData[0]);
                $existingFile->touch();
                
                return $this->buildImageInfoModel($existingFile);

            case 'Rename':
                $name = $this->getUniqueFileName($fileInputData[0], $fileInputData[1]);
                $filePath = "uploads/$name";
                $file->storeAs('uploads', $name);
                $id = Str::uuid()->toString();
                
                $existingFile = $this->createFileInfoModel($id, $name, $fileInputData[1]);

                return $this->buildImageInfoModel($existingFile);

            case 'Abort':
                throw new ConflictException( 'File already exists' );

            case 'Skip':
                return $this->buildImageInfoModel($existingFile);
        }
    }

    private function createFileInfoModel($id, $name, $fileInputData)
    {
        $fileInfo = ImageFileInfoModel::create([
            'id' => $id,
            'name' => $name,
            'extension' => $fileInputData,
        ]);
        return $fileInfo;
    }

    private function getUniqueFileName($fileName, $extension)
    {
        $baseName = pathinfo($fileName, PATHINFO_FILENAME);
        $counter = 1;

        while (Storage::exists("uploads/{$baseName}_{$counter}.$extension")) {
            $counter++;
        }

        return "{$baseName}_{$counter}.$extension";
    }

    private function buildFileInputData($file) 
    {
        return [
            $name = $file->getClientOriginalName(),
            $extension = $file->getClientOriginalExtension(),
            $filePath = "uploads/$name",
        ];
    }

    private function existingFileInfo($name, $extension) 
    {
        return $result = ImageFileInfoModel::where([
            ['name', '=', $name],
            ['extension', '=', $extension]
        ])->first();
    }

    private function buildImageInfoModel($existingFile) 
    {
        $imageInfoModel = new ImageInfoModel();
        $imageInfoModel->id = $existingFile->id;
        $imageInfoModel->title = $existingFile->name;
        $imageInfoModel->thumbnailUrl = '';

        return $imageInfoModel;
    }

}
