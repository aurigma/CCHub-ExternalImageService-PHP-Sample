<?php
/**
 * ImageStorageFeature
 */
namespace app\Models;

/**
 * ImageStorageFeature
 * @description Available image storage service features.
 */
class ImageStorageFeature
{
    /**
     * Possible values of this enum
     */
    const ALLOW_CREATE = 'AllowCreate';

    const ALLOW_DELETE = 'AllowDelete';

    const ALLOW_SEARCH = 'AllowSearch';

    /**
     * Gets allowable values of the enum
     * @return string[]
     */
    public static function getAllowableEnumValues()
    {
        return [
            self::ALLOW_CREATE,
            self::ALLOW_DELETE,
            self::ALLOW_SEARCH
        ];
    }
}
