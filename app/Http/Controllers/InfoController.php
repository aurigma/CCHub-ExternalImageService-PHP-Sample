<?php

/**
 * External Storage API
 * No description provided (generated by Openapi Generator https://github.com/openapitools/openapi-generator)
 * PHP version 7.2.5
 *
 * The version of the OpenAPI document: v1
 * 
 *
 * NOTE: This class is auto generated by OpenAPI-Generator
 * https://openapi-generator.tech
 * Do not edit the class manually.
 *
 * Source files are located at:
 *
 * > https://github.com/OpenAPITools/openapi-generator/blob/master/modules/openapi-generator/src/main/resources/php-laravel/
 */


namespace App\Http\Controllers;

use Illuminate\Support\Facades\Request;
use app\Models\ImageStorageInfoModel;
use app\Models\ImageStorageFeature;

class InfoController extends Controller
{
    /**
     * Constructor
     */
    public function __construct()
    {
    }

    /**
     * Operation infoGetInfo
     *
     * Returns external storage features..
     *
     *
     * @return Http response
     */
    public function infoGetInfo()
    {
        $imageStorageInfoModel = new ImageStorageInfoModel();

        $imageStorageInfoModel->name = 'Image source';
        $imageStorageInfoModel->version = '0.0.1';
        $imageStorageInfoModel->features = [
            ImageStorageFeature::ALLOW_CREATE,
            ImageStorageFeature::ALLOW_DELETE,
            ImageStorageFeature::ALLOW_SEARCH
        ];

        return response()->json($imageStorageInfoModel, 200);
    }

}
