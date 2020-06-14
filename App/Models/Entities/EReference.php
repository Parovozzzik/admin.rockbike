<?php

namespace App\Models\Entities;

use App\Models\Reference;

/**
 * Class EReference
 * @package App\Models\Entities
 *
 * @property integer $reference_id
 * @property string $name
 * @property string $slug
 * @property string $type
 * @property integer $parent_reference_id
 *
 * @property integer $visible
 * @property integer $deleted
 * @property integer $created_at
 * @property integer $updated_at
 */
class EReference extends Entity
{
    /** @var string */
    const REF_TYPE_INT = 'int';
    /** @var string */
    const REF_TYPE_REF = 'ref';
    /** @var string */
    const REF_TYPE_STRING = 'string';
    /** @var string */
    const REF_TYPE_TEXT = 'text';
    /** @var array */
    const REF_TYPES = [
        self::REF_TYPE_INT,
        self::REF_TYPE_REF,
        self::REF_TYPE_STRING,
        self::REF_TYPE_TEXT,
    ];

    /** @var string */
    protected static $mapper = Reference::class;
    /** @var string */
    public static $table = 'rb_references';
    /** @var string */
    public static $idColumn = 'reference_id';

    /**
     * @return array
     * @throws \Exception
     */
    public static function fields()
    {
        return [
            'reference_id' => ['type' => 'integer', 'primary' => true, 'autoincrement' => true],
            'name' => ['type' => 'string', 'required' => true, 'unique' => true],
            'slug' => ['type' => 'string', 'required' => true, 'unique' => true],
            'type' => ['type' => 'string', 'required' => true],
            'parent_reference_id' => ['type' => 'integer'],
            'visible' => ['type' => 'integer', 'default' => 0, 'required' => true],
            'deleted' => ['type' => 'integer', 'default' => 0, 'required' => true],
            'created_at' => ['type' => 'datetime', 'value' => new \DateTime()],
            'updated_at' => ['type' => 'datetime', 'value' => new \DateTime()],
        ];
    }
}