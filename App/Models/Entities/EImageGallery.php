<?php

namespace App\Models\Entities;

use App\Models\ImageGallery;

/**
 * Class EImageGallery
 * @package App\Models\Entities
 *
 * @property integer $image_gallery_id
 * @property integer $image_id
 * @property integer $gallery_id
 * @property integer $is_main
 * @property integer $priority
 *
 * @property \DateTime $created_at
 */
class EImageGallery extends Entity
{
    /** @var string */
    protected static $mapper = ImageGallery::class;
    /** @var string */
    public static $table = 'rb_images_galleries';
    /** @var string */
    public static $idColumn = 'image_gallery_id';

    /**
     * @return array
     * @throws \Exception
     */
    public static function fields()
    {
        return [
            'image_gallery_id' => ['type' => 'integer', 'primary' => true, 'autoincrement' => true],
            'image_id' => ['type' => 'integer', 'required' => true],
            'gallery_id' => ['type' => 'integer', 'required' => true],
            'is_main' => ['type' => 'integer', 'default' => 0, 'required' => true],
            'priority' => ['type' => 'integer', 'default' => 0, 'required' => true],
            'created_at' => ['type' => 'datetime', 'value' => new \DateTime()],
        ];
    }
}