<?php

namespace App\Models;

use App\Helpers\Helper;
use App\Models\Entities\EUser;

/**
 * Class User
 * @package App\Models
 */
class User extends Model
{
    /** @var string */
    public $entityName = EUser::class;

    /**
     * @param string $email
     * @param string $password
     * @param string $confirmCode
     * @return bool
     * @throws \App\Settings\Exceptions\DatabaseException
     * @throws \Doctrine\DBAL\DBALException
     */
    public function registration(string $email, string $password, string $confirmCode): bool
    {
        $query = $this->connection()->prepare(
        "INSERT INTO {$this->table()} 
            (email, password, email_confirm_code) 
            VALUES (?, ?, ?);"
        );
        $passwordHash = Helper::passwordHash($password);
        $confirmCodeHash = Helper::passwordHash($confirmCode);
        $query->bindParam(1, $email);
        $query->bindParam(2, $passwordHash);
        $query->bindParam(3, $confirmCodeHash);

        return $query->execute();
    }

    /**
     * @param int $userId
     * @param string $password
     * @return bool
     * @throws \App\Settings\Exceptions\DatabaseException
     * @throws \Doctrine\DBAL\DBALException
     */
    public function changePassword(int $userId, string $password): bool
    {
        $query = $this->connection()->prepare(
        "UPDATE {$this->table()} 
            SET password = ? 
            WHERE user_id = ?;"
        );
        $passwordHash = Helper::passwordHash($password);
        $query->bindParam(1, $passwordHash);
        $query->bindParam(2, $userId);

        return $query->execute();
    }

    /**
     * @param string $email
     * @return bool
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Exception
     */
    public function existsByEmail(string $email): bool
    {
        $query = $this->connection()->prepare(
            "SELECT COUNT(*) 
            FROM {$this->table()} 
            WHERE email = ?;"
        );
        $query->bindParam(1, $email);
        $query->execute();
        $count = $query->fetchColumn();

        return $count > 0;
    }

    /**
     * @param string $email
     * @return EUser|null
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Exception
     */
    public function getByEmail(string $email): ?EUser
    {
        $query = $this->connection()->prepare(
            "SELECT * 
            FROM {$this->table()} 
            WHERE email = ?;"
        );
        $query->bindParam(1, $email);
        $query->execute();

        $result = $query->fetch(\PDO::FETCH_ASSOC);

        return $result !== false ? new EUser($result) : null;
    }

    /**
     * @param int $userId
     * @return bool
     * @throws \App\Settings\Exceptions\DatabaseException
     * @throws \Doctrine\DBAL\DBALException
     */
    public function confirmEmail(int $userId): bool
    {
        $query = $this->connection()->prepare(
            "UPDATE {$this->table()} 
            SET email_confirm = 1, 
                email_confirm_code = null,
                email_confirmed_at = CURRENT_TIMESTAMP()
            WHERE user_id = ?;"
        );
        $query->bindParam(1, $userId);

        return $query->execute();
    }

    /**
     * @param int $userId
     * @param string $confirmCode
     * @return bool
     * @throws \App\Settings\Exceptions\DatabaseException
     * @throws \Doctrine\DBAL\DBALException
     */
    public function resendConfirmEmail(int $userId, string $confirmCode): bool
    {
        $query = $this->connection()->prepare(
            "UPDATE {$this->table()} 
            SET email_confirm_code = ?,
                updated_at = CURRENT_TIMESTAMP()
            WHERE user_id = ?;"
        );
        $confirmCodeHash = Helper::passwordHash($confirmCode);
        $query->bindParam(1, $confirmCodeHash);
        $query->bindParam(2, $userId);

        return $query->execute();
    }
}