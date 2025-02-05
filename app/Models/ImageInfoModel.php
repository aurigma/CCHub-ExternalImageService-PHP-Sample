<?php
/**
 * ImageInfoModel
 */
namespace app\Models;

/**
 * ImageInfoModel
 * @description Describes an image in image storage.
 */
class ImageInfoModel {

    /** @var string|null $id Image ID in image storage.*/
    public $id = null;

    /** @var string|null $title Image title.*/
    public $title = null;

    /** @var string|null $thumbnailUrl Image thumbnail URL.*/
    public $thumbnailUrl = null;

}
