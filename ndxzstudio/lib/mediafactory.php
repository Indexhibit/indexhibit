<?php

/**
 * @author LukaszMordawski <lukasz.mordawski@gmail.com>
 */

class MediaFactory
{
    const MEDIA_LEGACY = 'legacy';
    const MEDIA_IMAGICK = 'imagick';

    public function factory($mediaType)
    {
        switch ($mediaType) {
            case self::MEDIA_IMAGICK:
                load_class('mediaimagemagick', false, 'lib');
                return new MediaImageMagick('/usr/bin/convert');
            case self::MEDIA_LEGACY:
                load_class('media', true, 'lib');
                return new Media();
        }
    }
}