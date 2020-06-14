<?php

namespace App\Models\Entities;

use App\Models\ReferenceValue;

/**
 * Class EReferenceValue
 * @package App\Models
 *
 * @property integer $reference_value_id
 * @property integer $reference_id
 * @property integer $value_int
 * @property string $value_string
 * @property string $value_text
 *
 * @property integer $visible
 * @property integer $deleted
 * @property integer $created_at
 * @property integer $updated_at
 */
class EReferenceValue extends Entity
{
    /** @var string */
    protected static $mapper = ReferenceValue::class;
    /** @var string */
    public static $table = 'rb_references_values';
    /** @var string */
    public static $idColumn = 'reference_value_id';

    /**
     * @return array
     * @throws \Exception
     */
    public static function fields()
    {
        return [
            'reference_value_id' => ['type' => 'integer', 'primary' => true, 'autoincrement' => true],
            'reference_id' => ['type' => 'integer', 'required' => true],
            'value_int' => ['type' => 'integer'],
            'value_string' => ['type' => 'string'],
            'value_text' => ['type' => 'string'],
            'visible' => ['type' => 'integer', 'default' => 0, 'required' => true],
            'deleted' => ['type' => 'integer', 'default' => 0, 'required' => true],
            'created_at' => ['type' => 'datetime', 'value' => new \DateTime()],
            'updated_at' => ['type' => 'datetime', 'value' => new \DateTime()],
        ];
    }
}