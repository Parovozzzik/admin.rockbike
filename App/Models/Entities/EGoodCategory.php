<?php

namespace App\Models\Entities;

use App\Models\GoodCategory;

/**
 * Class EGoodCategory
 * @package App\Models\Entities
 *
 * @property integer $good_category_id
 * @property integer $good_id
 * @property integer $category_id
 * @property integer $is_main
 * @property \DateTime $created_at
 */
class EGoodCategory extends Entity
{
    /** @var int */
    const GOOD_CATEGORY_IS_MAIN = 1;

    /** @var string */
    protected static $mapper = GoodCategory::class;
    /** @var string */
    public static $table = 'rb_goods_categories';
    /** @var string */
    public static $idColumn = 'good_category_id';

    /**
     * @return array
     * @throws \Exception
     */
    public static function fields()
    {
        return [
            'good_category_id' => ['type' => 'integer', 'primary' => true, 'autoincrement' => true],
            'good_id' => ['type' => 'integer', 'required' => true],
            'category_id' => ['type' => 'integer', 'required' => true],
            'is_main' => ['type' => 'integer', 'required' => true, 'default' => 1],
            'created_at' => ['type' => 'datetime', 'value' => new \DateTime()],
        ];
    }
}