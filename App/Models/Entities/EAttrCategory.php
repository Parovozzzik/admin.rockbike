<?php

namespace App\Models\Entities;

use App\Models\AttrCategory;

/**
 * Class EAttrCategory
 * @package App\Models\Entities
 *
 * @property integer $attr_category_id
 * @property integer $attr_id
 * @property integer $category_id
 * @property integer $required
 * @property integer $created_at
 */
class EAttrCategory extends Entity
{
    /** @var string */
    protected static $mapper = AttrCategory::class;
    /** @var string */
    public static $table = 'rb_attrs_categories';
    /** @var string */
    public static $idColumn = 'attr_category_id';

    /**
     * @return array
     * @throws \Exception
     */
    public static function fields()
    {
        return [
            'attr_category_id' => ['type' => 'integer', 'primary' => true, 'autoincrement' => true],
            'attr_id' => ['type' => 'integer', 'required' => true],
            'category_id' => ['type' => 'integer', 'required' => true],
            'required' => ['type' => 'integer', 'default' => 1, 'required' => true],
            'created_at' => ['type' => 'datetime', 'value' => new \DateTime()],
        ];
    }
}