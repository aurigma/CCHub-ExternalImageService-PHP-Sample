<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CcHubSettingsModel extends Model
{
    public string $apiUrl;
    public string $clientId;
    public string $clientSecret;
}

