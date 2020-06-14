<?php

namespace App\Models\Entities;

use App\Models\User;

/**
 * Class EUser
 * @package App\Models\Entities
 *
 * @property integer $user_id
 * @property string $email
 * @property string $password
 *
 * @property integer $email_confirm
 * @property string $email_confirm_code
 * @property integer $email_confirmed_at
 *
 * @property integer $deleted
 * @property integer $created_at
 * @property integer $updated_at
 */
class EUser extends Entity
{
    /** @var int */
    const USER_DELETED = 1;
    /** @var int */
    const USER_NOT_DELETED = 0;

    /** @var string */
    protected static $mapper = User::class;
    /** @var string */
    public static $table = 'rb_users';
    /** @var string */
    public static $idColumn = 'user_id';

    /**
     * @return array
     * @throws \Exception
     */
    public static function fields()
    {
        return [
            'user_id' => ['type' => 'integer', 'primary' => true, 'autoincrement' => true],
            'email' => ['type' => 'string'],
            'password' => ['type' => 'string'],
            'email_confirm' => ['type' => 'integer'],
            'email_confirm_code' => ['type' => 'string'],
            'email_confirmed_at' => ['type' => 'datetime'],
            'deleted' => ['type' => 'integer'],
            'created_at' => ['type' => 'datetime', 'value' => new \DateTime()],
            'updated_at' => ['type' => 'datetime', 'value' => new \DateTime()],
        ];
    }
}