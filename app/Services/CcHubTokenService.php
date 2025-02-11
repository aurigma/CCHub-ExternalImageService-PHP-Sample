<?php

namespace app\Services;

use Jumbojett\OpenIDConnectClient;
use app\Models\CcHubSettingsModel;
use app\Services\CcHubSettingsService;
use Throwable;

class CcHubTokenService
{
    private $oidc;
    private CcHubSettingsModel $settingsModel;

    public function __construct(CcHubSettingsService $settingsService)
    {
        $this->settingsModel = $settingsService->getSettings();
        $this->oidc = new OpenIDConnectClient($this->settingsModel->apiUrl, $this->settingsModel->clientId, $this->settingsModel->clientSecret);
        $this->oidc->providerConfigParam(array('token_endpoint'=> $this->settingsModel->apiUrl.'connect/token'));
    }

    public function getAccessToken()
    {
        try {
            return $this->oidc->requestClientCredentialsToken()->access_token;
        } catch (\Throwable $ex) {
            echo 'Back office token was not gotten.', __METHOD__, $ex;
            throw $ex;
        }
    }
}