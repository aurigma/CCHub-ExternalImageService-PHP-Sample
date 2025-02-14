<?php
/**
 * ImageStorageInfoModel
 */
namespace app\Models;

/**
 * ImageStorageInfoModel
 * @description Describes image storage service and its features.
 */
class ImageStorageInfoModel {

    /** @var string|null $name Image storage service name.*/
    public $name = null;

    /** @var string|null $version Image storage service version.*/
    public $version = null;

    /** @var \app\Models\ImageStorageFeature[]|null $features Image storage service features.*/
    public $features = null;

}
