<?php

namespace App\Models\Entities;

use App\Models\GoodAttr;

/**
 * Class EGoodAttr
 * @package App\Models\Entities
 *
 * @property integer $good_attr_id
 * @property integer $good_id
 * @property integer $attr_id
 * @property string $type
 * @property integer $value_int
 * @property string $value_string
 * @property string $value_text
 * @property \DateTime $created_at
 */
class EGoodAttr extends Entity
{
    /** @var string */
    protected static $mapper = GoodAttr::class;
    /** @var string */
    public static $table = 'rb_goods_attrs';
    /** @var string */
    public static $idColumn = 'good_attr_id';

    /**
     * @return array
     * @throws \Exception
     */
    public static function fields()
    {
        return [
            'good_attr_id' => ['type' => 'integer', 'primary' => true, 'autoincrement' => true],
            'good_id' => ['type' => 'integer', 'required' => true],
            'attr_id' => ['type' => 'integer', 'required' => true],
            'type' => ['type' => 'string', 'required' => true],
            'value_int' => ['type' => 'integer'],
            'value_string' => ['type' => 'string'],
            'value_text' => ['type' => 'string'],
            'created_at' => ['type' => 'datetime', 'value' => new \DateTime()],
        ];
    }
}