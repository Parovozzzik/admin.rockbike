<?php

namespace App\Models\Entities;

use App\Models\Gallery;

/**
 * Class EGallery
 * @package App\Models\Entities
 *
 * @property integer $gallery_id
 * @property string $name
 * @property string $slug
 * @property string $type
 * @property integer $parent_object_id
 *
 * @property integer $visible
 * @property integer $deleted
 * @property \DateTime $created_at
 * @property \DateTime $updated_at
 */
class EGallery extends Entity
{
    /** @var string */
    const GALLERY_TYPE_GOOD = 'good';
    /** @var string */
    const GALLERY_TYPE_USER = 'user';
    /** @var string */
    const GALLERY_TYPE_MANUFACTURER = 'manufacturer';
    /** @var string */
    const GALLERY_TYPE_MODEL = 'model';
    /** @var array */
    const GALLERY_TYPES = [
        self::GALLERY_TYPE_GOOD,
        self::GALLERY_TYPE_USER,
        self::GALLERY_TYPE_MANUFACTURER,
        self::GALLERY_TYPE_MODEL,
    ];

    /** @var string */
    protected static $mapper = Gallery::class;
    /** @var string */
    public static $table = 'rb_galleries';
    /** @var string */
    public static $idColumn = 'gallery_id';

    /**
     * @return array
     * @throws \Exception
     */
    public static function fields()
    {
        return [
            'gallery_id' => ['type' => 'integer', 'primary' => true, 'autoincrement' => true],
            'name' => ['type' => 'string', 'required' => true],
            'slug' => ['type' => 'string', 'required' => true, 'unique' => true],
            'type' => ['type' => 'string', 'required' => true],
            'parent_object_id' => ['type' => 'integer', 'required' => true],
            'visible' => ['type' => 'integer', 'default' => 0, 'required' => true],
            'deleted' => ['type' => 'integer', 'default' => 0, 'required' => true],
            'created_at' => ['type' => 'datetime', 'value' => new \DateTime()],
            'updated_at' => ['type' => 'datetime', 'value' => new \DateTime()],
        ];
    }
}