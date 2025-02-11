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


class ImageProcessingService
{
    private CcHubSettingsModel $settingsModel;
    private CcHubTokenService $tokenService;
        
    public function __construct(CcHubSettingsService $settingsService, CcHubTokenService $tokenService)
    {
        $this->settingsModel = $settingsService->getSettings();
        $this->tokenService = $tokenService;
    }

    public function create($file, $name)
    {
        $designAtomsServiceApi = $this->getDesignAtomsImageApi();
        $splFile = $this->convertUploadedFileToSpl($file);
        $response = $designAtomsServiceApi->designAtomsImagesRenderImagePreviewFromFile(
            $attachment = null, $tenantId = null, $source_file = $splFile, $mockup_owner_id = null, 
            $mockup_id = null, $width = 150, $height = 150, ImagePreviewFormat::PNG, $fit_mode = null);
        $previewFileName = $this->savePreview($response, $name);

        return $previewFileName;
    }

    private function convertUploadedFileToSpl($file)
    {
        $fileName = $file->getClientOriginalName();
        $filePath = storage_path("app/uploads/$fileName");
        $file = new SplFileObject($filePath, 'r');
        
        return $file;
    }

    private function savePreview($response, $name)
    {
        $previewPath = $response->getPathname();
        $content = file_get_contents($previewPath);
        $previewDirectory = storage_path('app/preview');
        $previewFileName = "preview_{$name}.png";
        $previewFullPath = "$previewDirectory/$previewFileName";
        file_put_contents($previewFullPath, $content);

        return $previewFileName;
    }

    private function getDesignAtomsImageApi()
    {
        $apiUrl = rtrim($this->settingsModel->apiUrl, "/");

        $client = new Client([
            // Base URI is used with relative requests
            'base_uri' => $apiUrl,
            // You can set any number of default request options.
            'timeout'  => 60.0,
        ]);
        $selector = new HeaderSelector();
        $config = new Configuration();
        $config->setAccessToken($this->tokenService->getAccessToken());
        $config->setHost($apiUrl);
        
        return new DesignAtomsImagesApi($client, $config, $selector);
    }
}