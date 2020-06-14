<?php

namespace App\Models\Entities;

use App\Models\Good;

/**
 * Class EGood
 * @package App\Models\Entities
 *
 * @property integer $good_id
 * @property string $name
 * @property string $slug
 * @property string $description
 * @property integer $price
 *
 * @property integer $visible
 * @property integer $deleted
 * @property integer $created_at
 * @property integer $updated_at
 */
class EGood extends Entity
{
    /** @var string */
    protected static $mapper = Good::class;
    /** @var string */
    public static $table = 'rb_goods';
    /** @var string */
    public static $idColumn = 'good_id';

    /**
     * @return array
     * @throws \Exception
     */
    public static function fields()
    {
        return [
            'good_id' => ['type' => 'integer', 'primary' => true, 'autoincrement' => true],
            'name' => ['type' => 'string', 'required' => true],
            'slug' => ['type' => 'string', 'required' => true, 'unique' => true],
            'description' => ['type' => 'string'],
            'price' => ['type' => 'integer', 'default' => null],
            'visible' => ['type' => 'integer', 'default' => 0, 'required' => true],
            'deleted' => ['type' => 'integer', 'default' => 0, 'required' => true],
            'created_at' => ['type' => 'datetime', 'value' => new \DateTime()],
            'updated_at' => ['type' => 'datetime', 'value' => new \DateTime()],
        ];
    }
}