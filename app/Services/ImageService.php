<?php

namespace app\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Request;
use app\Models\ImageInfoModel;
use app\Models\ImageFileInfoModel;
use Illuminate\Support\Str;
use App\Exceptions\ConflictException;
use App\Exceptions\FileNotFoundException;
use app\Services\ImageProcessingService;
use app\Services\CcHubSettingsService;
use App\Models\CcHubSettingsModel;


class ImageService 
{
    private ImageProcessingService $imageProcessingService;

    public function __construct(ImageProcessingService $imageProcessingService)
    {
        $this->imageProcessingService = $imageProcessingService;
    }

    public function create($file, $strategy)
    {
        $fileInputData = $this->buildFileInputData($file);
        $existingFile = $this->getFileInfo($fileInputData[0], $fileInputData[1]);

        if ($existingFile) {
            return $this->saveFileInfoWithUsingStrategy($fileInputData, $file, $strategy, $existingFile);
        } else {
            return $this->saveFileInfo($fileInputData, $file);
        }
    }

    public function getAll(string $search, int $take, int $skip)
    {
        return $this->searchFileInfos($search, $take, $skip);
    }

    public function delete($id)
    {
        $isFileDeleted = $this->deleteFile($id);
        return $isFileDeleted;
    }

    public function get($id)
    {
        $existingFileInfo = $this->getFileById($id);
        return $existingFileInfo;
    }

    public function getImageFile($id)
    {
        $fileName = ImageFileInfoModel::where('id', $id)->value('name');
        if (!isset($fileName)) {
            throw new FileNotFoundException('FileInfo is not found');
        }
        $filePath = storage_path("app/uploads/{$fileName}");
        if (!file_exists($filePath)) {
            throw new Exception('File is not found on the server');
        }
        return $filePath;
    }

    public function getPreviewFile($id)
    {
        $fileName = ImageFileInfoModel::where('id', $id)->value('name');
        if (!isset($fileName)) {
            throw new FileNotFoundException('FileInfo is not found');
        }
        $filePath = storage_path("app/uploads/{$fileName}");
        if (!file_exists($filePath)) {
            throw new Exception('File is not found on the server');
        }
        $fileNameWithoutExtension = pathinfo($fileName, PATHINFO_FILENAME);
        $previewFilePath = storage_path("app/preview/preview_{$fileNameWithoutExtension}.png");
        if (!file_exists($previewFilePath)) {
            throw new Exception('File is not found on the server');
        }
        return $previewFilePath;
    }

    private function buildFileInputData($file) 
    {
        return [
            $name = $file->getClientOriginalName(),
            $extension = $file->getClientOriginalExtension(),
            $filePath = "uploads/$name",
        ];
    }

    private function getFileInfo($name, $extension) 
    {
        return $result = ImageFileInfoModel::where([
            ['name', '=', $name],
            ['extension', '=', $extension]
        ])->first();
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
                $nameWithoutExtension = pathinfo($name, PATHINFO_FILENAME);
                $previewName = $this->imageProcessingService->create($file, $nameWithoutExtension);
                $id = Str::uuid()->toString();
                
                $createdFileInfo = $this->createFileInfoModel($id, $name, $fileInputData[1]);

                return $this->buildImageInfoModel($createdFileInfo);

            case 'Abort':
                throw new ConflictException( 'File already exists' );

            case 'Skip':
                return $this->buildImageInfoModel($existingFile);
        }
    }

    private function saveFileInfo($fileInputData, $file)
    {
        $id = Str::uuid()->toString();
        $file->storeAs('uploads', $fileInputData[0]);
        $name = $fileInputData[0];
        $nameWithoutExtension = pathinfo($name, PATHINFO_FILENAME);
        $previewName = $this->imageProcessingService->create($file, $nameWithoutExtension);
        
        $createdFileInfo = $this->createFileInfoModel($id, $fileInputData[0], $fileInputData[1]);

        return $this->buildImageInfoModel($createdFileInfo);
    }

    private function buildImageInfoModel($createdFileInfo) 
    {
        $imageInfoModel = new ImageInfoModel();
        $imageInfoModel->id = $createdFileInfo->id;
        $imageInfoModel->title = $createdFileInfo->name;
        $imageInfoModel->thumbnailUrl = url("/api/preview-image/{$imageInfoModel->id}");

        return $imageInfoModel;
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

    private function searchFileInfos($search, $take, $skip)
    {
        $query = null;

        if ($search) {
            $query = ImageFileInfoModel::where('name', 'LIKE', "%$search%");
        } else {
            $query = ImageFileInfoModel::select('*');
        }

        if ($skip) {
            $query = $query->skip($skip)->take($take);
        } else {
            $query = $query->take($take);
        }
        return $query->get();
    }

    private function deleteFile($id)
    {
        $fileName = ImageFileInfoModel::where('id', $id)->value('name');
        if (!isset($fileName)) {
            throw new FileNotFoundException('FileInfo is not found');
        }
        $filePath = storage_path("app/uploads/{$fileName}");
        if (!file_exists($filePath)) {
            throw new Exception('File is not found on the server');
        }
        $isFileDeleted = unlink($filePath);
        if ($isFileDeleted == false) {
            throw new Exception('Internal Server Error');
        }
        ImageFileInfoModel::where('id', $id)->delete();
    }

    private function getFileById($id)
    {
        $fileName = ImageFileInfoModel::where('id', $id)->value('name');
        if (!isset($fileName)) {
            throw new FileNotFoundException('FileInfo is not found');
        }
        $filePath = storage_path("app/uploads/{$fileName}");
        if (!file_exists($filePath)) {
            throw new Exception('File is not found on the server');
        }
        $fileInfo = ImageFileInfoModel::where('id', $id)->get();
        if ($fileInfo) {
            return $fileInfo;
        } else {
            throw new FileNotFoundException('File is not found');
        }
    }

}
