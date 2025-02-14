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
use App\Services\AuthService;
use Exception;


class ImageService 
{
    private ImageProcessingService $imageProcessingService;
    private AuthService $authService;

    public function __construct(ImageProcessingService $imageProcessingService, AuthService $authService)
    {
        $this->imageProcessingService = $imageProcessingService;
        $this->authService = $authService;
    }

    public function create($file, $strategy)
    {
        $userId = $this->getUserId();
        $fileInputData = $this->buildFileInputData($file, $userId);
        $existingFile = $this->getFileInfo($fileInputData[0], $fileInputData[1], $fileInputData[3]);
        

        if ($existingFile) {
            return $this->saveFileInfoWithUsingStrategy($userId, $fileInputData, $file, $strategy, $existingFile);
        } else {
            return $this->saveFileInfo($userId, $fileInputData, $file);
        }
    }

    public function getAll(string $search, int $take, int $skip)
    {
        $userId = $this->getUserId();
        return $this->searchFileInfos($userId, $search, $take, $skip);
    }

    public function delete($id)
    {
        $userId = $this->getUserId();
        $isFileDeleted = $this->deleteFile($userId, $id);
        return $isFileDeleted;
    }

    public function get($id)
    {
        $userId = $this->getUserId();
        $existingFileInfo = $this->getFileById($userId, $id);
        return $existingFileInfo;
    }

    public function getImageFile($id)
    {
        $userId = $this->getUserId();
        $fileName = ImageFileInfoModel::where([
            ['id', '=', $id],
            ['userId', '=', $userId]
        ])->value('name');
        if (!isset($fileName)) {
            throw new FileNotFoundException('FileInfo is not found');
        }
        $filePath = storage_path("app/$userId/uploads/{$fileName}");
        if (!file_exists($filePath)) {
            throw new Exception('File is not found on the server');
        }
        return $filePath;
    }

    public function getFreeImageFile($id)
    {
        $fileName = ImageFileInfoModel::where('id', $id)->value('name');
        if (!isset($fileName)) {
            throw new FileNotFoundException('FileInfo is not found');
        }
        $userId = ImageFileInfoModel::where('id', $id)->value('userId');
        $filePath = storage_path("app/$userId/uploads/{$fileName}");
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
        $userId = ImageFileInfoModel::where('id', $id)->value('userId');
        $filePath = storage_path("app/$userId/uploads/{$fileName}");
        if (!file_exists($filePath)) {
            throw new Exception('File is not found on the server');
        }
        $previewNameWithoutExtension = pathinfo($fileName, PATHINFO_FILENAME);
        $previewFilePath = storage_path("app/$userId/preview/preview_{$previewNameWithoutExtension}.png");
        if (!file_exists($previewFilePath)) {
            throw new Exception('File is not found on the server');
        }
        return $previewFilePath;
    }

    private function buildFileInputData($file, $userId) 
    {
        return [
            $name = $file->getClientOriginalName(),
            $extension = $file->getClientOriginalExtension(),
            $filePath = "$userId/uploads/$name",
            $userId = $userId,
        ];
    }

    private function getFileInfo($name, $extension, $userId) 
    {
        return $result = ImageFileInfoModel::where([
            ['name', '=', $name],
            ['extension', '=', $extension],
            ['userId', '=', $userId]
        ])->first();
    }

    private function saveFileInfoWithUsingStrategy($userId, $fileInputData, $file, $strategy, $existingFile)
    {
        switch ($strategy) {
            case 'Overwrite':
                Storage::delete($fileInputData[2]);
                $file->storeAs("$userId/uploads", $fileInputData[0]);
                $existingFile->touch();
                $nameWithoutExtension = pathinfo($fileInputData[0], PATHINFO_FILENAME);
                Storage::delete("preview_{$nameWithoutExtension}.png");
                $previewName = $this->imageProcessingService->create($userId, $file, $nameWithoutExtension);
                
                return $this->buildImageInfoModel($existingFile);

            case 'Rename':
                $name = $this->getUniqueFileName($userId, $fileInputData[0], $fileInputData[1]);
                $filePath = "$userId/uploads/$name";
                $file->storeAs("$userId/uploads", $name);
                $nameWithoutExtension = pathinfo($name, PATHINFO_FILENAME);
                $previewName = $this->imageProcessingService->create($userId, $file, $nameWithoutExtension);
                $id = Str::uuid()->toString();
                
                $createdFileInfo = $this->createFileInfoModel($id, $name, $fileInputData[1], $userId);

                return $this->buildImageInfoModel($createdFileInfo);

            case 'Abort':
                throw new ConflictException( 'File already exists' );

            case 'Skip':
                return $this->buildImageInfoModel($existingFile);
        }
    }

    private function saveFileInfo($userId, $fileInputData, $file)
    {
        $id = Str::uuid()->toString();
        $file->storeAs("$userId/uploads", $fileInputData[0]);
        $name = $fileInputData[0];
        $nameWithoutExtension = pathinfo($name, PATHINFO_FILENAME);
        $previewName = $this->imageProcessingService->create($userId, $file, $nameWithoutExtension);
        
        $createdFileInfo = $this->createFileInfoModel($id, $fileInputData[0], $fileInputData[1], $userId);

        return $this->buildImageInfoModel($createdFileInfo);
    }

    private function buildImageInfoModel($fileInfo) 
    {
        $imageInfoModel = new ImageInfoModel();
        $imageInfoModel->id = $fileInfo->id;
        $imageInfoModel->title = $fileInfo->name;
        $imageInfoModel->thumbnailUrl = url("/api/preview-image/{$imageInfoModel->id}");

        return $imageInfoModel;
    }

    private function createFileInfoModel($id, $name, $fileInputData, $userId)
    {
        $fileInfo = ImageFileInfoModel::create([
            'id' => $id,
            'name' => $name,
            'extension' => $fileInputData,
            'userId' => $userId,
        ]);
        return $fileInfo;
    }

    private function getUniqueFileName($userId, $fileName, $extension)
    {
        $baseName = pathinfo($fileName, PATHINFO_FILENAME);
        $counter = 1;

        while (Storage::exists("$userId/uploads/{$baseName}_{$counter}.$extension")) {
            $counter++;
        }

        return "{$baseName}_{$counter}.$extension";
    }

    private function searchFileInfos($userId, $search, $take, $skip)
    {
        $query = null;

        if ($search) {
            $query = ImageFileInfoModel::where([
                ['name', 'LIKE', "%$search%"],
                ['userId', '=', $userId]
            ]);
        } else {
            $query = ImageFileInfoModel::where('userId', '=', $userId);
        }

        if ($skip) {
            $query = $query->skip($skip)->take($take);
        } else {
            $query = $query->take($take);
        }
        $result = $query->get();

        for ($i = 0; $i < count($result); $i++)
        {
            $fileInfos[] = $this->buildImageInfoModel($result[$i]);
        }

        return $fileInfos;
    }

    private function deleteFile($userId, $id)
    {
        $fileName = ImageFileInfoModel::where([
            ['id', '=', $id],
            ['userId', '=', $userId]
            ])->value('name');
        if (!isset($fileName)) {
            throw new FileNotFoundException('FileInfo is not found');
        }
        $filePath = storage_path("app/$userId/uploads/{$fileName}");
        $fileNameWithoutExtension = pathinfo($fileName, PATHINFO_FILENAME);
        $previewPath = storage_path("app/$userId/preview/preview_{$fileNameWithoutExtension}.png");
        if (!file_exists($filePath)) {
            throw new Exception('File is not found on the server');
        }
        $isPreviewDeleted = unlink($previewPath);
        $isFileDeleted = unlink($filePath);
        if ($isFileDeleted == false) {
            throw new Exception('Internal Server Error');
        }
        ImageFileInfoModel::where('id', $id)->delete();
    }

    private function getFileById($userId, $id)
    {
        $fileName = ImageFileInfoModel::where([
            ['id', '=', $id],
            ['userId', '=', $userId]
        ])->value('name');
        if (!isset($fileName)) {
            throw new FileNotFoundException('FileInfo is not found');
        }
        $filePath = storage_path("app/$userId/uploads/{$fileName}");
        if (!file_exists($filePath)) {
            throw new Exception('File is not found on the server');
        }
        $fileInfo = ImageFileInfoModel::where([
            ['id', '=', $id],
            ['userId', '=', $userId]
        ])->first();
        if ($fileInfo) {
            return $this->buildImageInfoModel($fileInfo);
        } else {
            throw new FileNotFoundException('File is not found');
        }
    }

    private function getUserId()
    {
        return $this->authService->getUserId();
    }

}
