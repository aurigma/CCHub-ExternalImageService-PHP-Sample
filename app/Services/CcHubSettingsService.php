<?php

namespace app\Services;

use App\Models\CcHubSettingsModel;

class CcHubSettingsService
{
    public function getSettings()
    {
        $settings = new CcHubSettingsModel();
        $settings->apiUrl = env('CC_HUB_API_URL');
        $settings->clientId = env('CC_HUB_CLIENT_ID');
        $settings->clientSecret = env('CC_HUB_CLIENT_SECRET');
        return $settings;
    }
}