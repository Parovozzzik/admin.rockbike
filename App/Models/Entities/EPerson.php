<?php

namespace App\Models\Entities;

use App\Models\Person;

/**
 * Class EPerson
 * @package App\Models\Entities
 *
 * @property integer $person_id
 * @property integer $user_id
 * @property string $first_name
 * @property string $last_name
 * @property integer $date_birth
 * @property string $gender
 * @property string $hobbies
 *
 * @property integer $deleted
 * @property integer $created_at
 * @property integer $updated_at
 */
class EPerson extends Entity
{
    /** @var array */
    const GENDERS = [
        'male',
        'female',
    ];

    /** @var string */
    protected static $mapper = Person::class;
    /** @var string */
    public static $table = 'rb_persons';
    /** @var string */
    public static $idColumn = 'person_id';

    /**
     * @return array
     * @throws \Exception
     */
    public static function fields()
    {
        return [
            'person_id' => ['type' => 'integer', 'primary' => true, 'autoincrement' => true],
            'user_id' => ['type' => 'integer'],
            'first_name' => ['type' => 'string'],
            'last_name' => ['type' => 'string'],
            'date_birth' => ['type' => 'date'],
            'gender' => ['type' => 'string'],
            'hobbies' => ['type' => 'string'],
            'deleted' => ['type' => 'integer'],
            'created_at' => ['type' => 'datetime', 'value' => new \DateTime()],
            'updated_at' => ['type' => 'datetime', 'value' => new \DateTime()],
        ];
    }
}