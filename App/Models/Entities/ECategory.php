<?php

namespace App\Models\Entities;

use App\Models\Category;

/**
 * Class ECategory
 * @package App\Models\Entities
 *
 * @property integer $category_id
 * @property string $name
 * @property string $slug
 * @property string $description
 * @property string $parent_category_id
 *
 * @property integer $visible
 * @property integer $deleted
 * @property integer $created_at
 * @property integer $updated_at
 */
class ECategory extends Entity
{
    /** @var string */
    protected static $mapper = Category::class;
    /** @var string */
    public static $table = 'rb_categories';
    /** @var string */
    public static $idColumn = 'category_id';

    /**
     * @return array
     * @throws \Exception
     */
    public static function fields()
    {
        return [
            'category_id' => ['type' => 'integer', 'primary' => true, 'autoincrement' => true],
            'name' => ['type' => 'string', 'required' => true],
            'slug' => ['type' => 'string', 'required' => true],
            'description' => ['type' => 'string'],
            'parent_category_id' => ['type' => 'integer', 'default' => null],
            'visible' => ['type' => 'integer', 'default' => 0, 'required' => true],
            'deleted' => ['type' => 'integer', 'default' => 0, 'required' => true],
            'created_at' => ['type' => 'datetime', 'value' => new \DateTime()],
            'updated_at' => ['type' => 'datetime', 'value' => new \DateTime()],
        ];
    }
}