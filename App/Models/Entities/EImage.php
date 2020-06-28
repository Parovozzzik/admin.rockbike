<?php

namespace App\Models\Entities;

use App\Models\Image;

/**
 * Class EImage
 * @package App\Models\Entities
 *
 * @property integer $image_id
 * @property string $path
 * @property string $description
 *
 * @property integer $visible
 * @property integer $deleted
 * @property \DateTime $created_at
 * @property \DateTime $updated_at
 */
class EImage extends Entity
{
    /** @var string */
    protected static $mapper = Image::class;
    /** @var string */
    public static $table = 'rb_images';
    /** @var string */
    public static $idColumn = 'image_id';

    /**
     * @return array
     * @throws \Exception
     */
    public static function fields()
    {
        return [
            'image_id' => ['type' => 'integer', 'primary' => true, 'autoincrement' => true],
            'path' => ['type' => 'string', 'required' => true],
            'description' => ['type' => 'string'],
            'visible' => ['type' => 'integer', 'default' => 0, 'required' => true],
            'deleted' => ['type' => 'integer', 'default' => 0, 'required' => true],
            'created_at' => ['type' => 'datetime', 'value' => new \DateTime()],
            'updated_at' => ['type' => 'datetime', 'value' => new \DateTime()],
        ];
    }
}