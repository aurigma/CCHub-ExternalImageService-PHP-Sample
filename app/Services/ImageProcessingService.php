<?php

namespace app\Services;

use app\Services\CcHubSettingsService;
use app\Services\CcHubTokenService;
use GuzzleHttp\Client;
use Aurigma\DesignAtoms\HeaderSelector;
use Aurigma\DesignAtoms\Configuration;
use Aurigma\DesignAtoms\Api\DesignAtomsImagesApi;
use Aurigma\DesignAtoms\Model\RenderDesignPreviewModel;
use App\Models\CcHubSettingsModel;
use SplFileObject;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\File;
use Aurigma\DesignAtoms\Model\ImagePreviewFormat;
use App\Services\PreviewSettingsService;
use App\Models\PreviewSettingsModel;


class ImageProcessingService
{
    private CcHubSettingsModel $ccHubSettingsModel;
    private PreviewSettingsModel $previewSettingsModel;
    private CcHubTokenService $tokenService;
        
    public function __construct(CcHubSettingsService $ccHubSettingsService, PreviewSettingsService $previewSettingsService, CcHubTokenService $tokenService)
    {
        $this->ccHubSettingsModel = $ccHubSettingsService->getSettings();
        $this->previewSettingsModel = $previewSettingsService->getSettings();
        $this->tokenService = $tokenService;
    }

    public function create($userId, $file, $name)
    {
        $designAtomsServiceApi = $this->getDesignAtomsImageApi();
        $splFile = $this->convertUploadedFileToSpl($userId, $file);
        $response = $designAtomsServiceApi->designAtomsImagesRenderImagePreviewFromFile(
            $attachment = null, $tenantId = null, $source_file = $splFile, $mockup_owner_id = null, 
            $mockup_id = null, $width = $this->previewSettingsModel->width, $height = $this->previewSettingsModel->height, ImagePreviewFormat::PNG, $fit_mode = null);
        $previewFileName = $this->savePreview($userId, $response, $name);

        return $previewFileName;
    }

    private function convertUploadedFileToSpl($userId, $file)
    {
        $fileName = $file->getClientOriginalName();
        $filePath = storage_path("app/$userId/uploads/$fileName");
        $file = new SplFileObject($filePath, 'r');
        
        return $file;
    }

    private function savePreview($userId, $response, $name)
    {
        $previewPath = $response->getPathname();
        $content = file_get_contents($previewPath);
        $previewDirectory = storage_path("app/$userId/preview");
        if (!is_dir($previewDirectory)) {
            mkdir($previewDirectory, 0777, true);
        }
        $previewFileName = "preview_{$name}.png";
        $previewFullPath = "$previewDirectory/$previewFileName";
        file_put_contents($previewFullPath, $content);

        return $previewFileName;
    }

    private function getDesignAtomsImageApi()
    {
        $apiUrl = rtrim($this->ccHubSettingsModel->apiUrl, "/");

        $client = new Client([
            // Base URI is used with relative requests
            'base_uri' => $apiUrl,
            // You can set any number of default request options.
            'timeout'  => 60.0,
            'defaults' => array('verify' => false,),
        ]);
        $selector = new HeaderSelector();
        $config = new Configuration();
        $config->setAccessToken($this->tokenService->getAccessToken());
        $config->setHost($apiUrl);
        
        return new DesignAtomsImagesApi($client, $config, $selector);
    }
}