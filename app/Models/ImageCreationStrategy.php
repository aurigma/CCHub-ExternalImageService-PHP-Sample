<?php
/**
 * ImageCreationStrategy
 */
namespace app\Models;

/**
 * ImageCreationStrategy
 * @description Available image creation strategies.
 */
class ImageCreationStrategy
{
    /**
     * Possible values of this enum
     */
    const ABORT = 'Abort';

    const SKIP = 'Skip';

    const OVERWRITE = 'Overwrite';

    const RENAME = 'Rename';

    /**
     * Gets allowable values of the enum
     * @return string[]
     */
    public static function getAllowableEnumValues()
    {
        return [
            self::ABORT,
            self::SKIP,
            self::OVERWRITE,
            self::RENAME
        ];
    }
}
