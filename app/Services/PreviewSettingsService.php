<?php

namespace App\Services;

use App\Models\PreviewSettingsModel;

class PreviewSettingsService
{
    public function getSettings()
    {
        $settings = new PreviewSettingsModel;
        $settings->width = env('PREVIEW_WIDTH');
        $settings->height = env('PREVIEW_HEIGHT');
        return $settings;
    }
}