<?php

namespace App\Models\Entities;

use App\Models\Attr;

/**
 * Class EAttr
 * @package App\Models\Entities
 *
 * @property integer $attr_id
 * @property string $name
 * @property string $slug
 * @property string $description
 * @property string $type
 * @property integer $round
 * @property integer $reference_id
 * @property string $table_name
 *
 * @property integer $visible
 * @property integer $deleted
 * @property \DateTime $created_at
 * @property \DateTime $updated_at
 */
class EAttr extends Entity
{
    /** @var string */
    const ATTR_TYPE_INT = 'int';
    /** @var string */
    const ATTR_TYPE_FLOAT = 'float';
    /** @var string */
    const ATTR_TYPE_STRING = 'string';
    /** @var string */
    const ATTR_TYPE_JSON = 'json';
    /** @var string */
    const ATTR_TYPE_TEXT = 'text';
    /** @var string */
    const ATTR_TYPE_REF = 'ref';
    /** @var string */
    const ATTR_TYPE_TABLE = 'table';
    /** @var array */
    const ATTR_TYPES = [
        self::ATTR_TYPE_INT,
        self::ATTR_TYPE_FLOAT,
        self::ATTR_TYPE_STRING,
        self::ATTR_TYPE_JSON,
        self::ATTR_TYPE_TEXT,
        self::ATTR_TYPE_REF,
        self::ATTR_TYPE_TABLE,
    ];

    /** @var string */
    protected static $mapper = Attr::class;
    /** @var string */
    public static $table = 'rb_attrs';
    /** @var string */
    public static $idColumn = 'attr_id';

    /**
     * @return array
     * @throws \Exception
     */
    public static function fields()
    {
        return [
            'attr_id' => ['type' => 'integer', 'primary' => true, 'autoincrement' => true],
            'name' => ['type' => 'string', 'required' => true],
            'slug' => ['type' => 'string', 'required' => true, 'unique' => true],
            'description' => ['type' => 'string'],
            'type' => ['type' => 'string', 'required' => true],
            'round' => ['type' => 'integer', 'default' => 0, 'required' => true],
            'reference_id' => ['type' => 'integer', 'default' => null],
            'table_name' => ['type' => 'string'],
            'visible' => ['type' => 'integer', 'default' => 0, 'required' => true],
            'deleted' => ['type' => 'integer', 'default' => 0, 'required' => true],
            'created_at' => ['type' => 'datetime', 'value' => new \DateTime()],
            'updated_at' => ['type' => 'datetime', 'value' => new \DateTime()],
        ];
    }
}