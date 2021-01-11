<?php

namespace App\Models\Entities;

use App\Models\Manufacturer;

/**
 * Class EManufacturer
 * @package App\Models\Entities
 *
 * @property integer $manufacturer_id
 * @property string $name
 * @property string $slug
 * @property string $description
 * @property integer $logo_image_id
 *
 * @property integer $visible
 * @property integer $deleted
 * @property \DateTime $created_at
 * @property \DateTime $updated_at
 */
class EManufacturer extends Entity
{
    /** @var string */
    protected static $mapper = Manufacturer::class;
    /** @var string */
    public static $table = 'rb_manufacturers';
    /** @var string */
    public static $idColumn = 'manufacturer_id';

    /**
     * @return array
     * @throws \Exception
     */
    public static function fields()
    {
        return [
            'manufacturer_id' => ['type' => 'integer', 'primary' => true, 'autoincrement' => true],
            'name' => ['type' => 'string', 'required' => true],
            'slug' => ['type' => 'string', 'required' => true, 'unique' => true],
            'description' => ['type' => 'string'],
            'logo_image_id' => ['type' => 'integer', 'default' => null],
            'visible' => ['type' => 'integer', 'default' => 0, 'required' => true],
            'deleted' => ['type' => 'integer', 'default' => 0, 'required' => true],
            'created_at' => ['type' => 'datetime', 'value' => new \DateTime()],
            'updated_at' => ['type' => 'datetime', 'value' => new \DateTime()],
        ];
    }
}