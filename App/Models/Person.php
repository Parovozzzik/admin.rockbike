<?php

namespace App\Models;

use App\Models\Entities\EPerson;
use App\Models\Entities\Responses\Response;
use App\Models\Entities\Responses\ResponseMessage;

/**
 * Class Person
 * @package App\Models
 */
class Person extends Model
{
    /** @var string */
    public $entityName = EPerson::class;

    /**
     * @param int $userId
     * @return EPerson|null
     * @throws \App\Settings\Exceptions\DatabaseException
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getByUserId(int $userId): ?EPerson
    {
        $query = $this->connection()->prepare(
            "SELECT * 
            FROM {$this->table()} 
            WHERE user_id = ?;");
        $query->bindParam(1, $userId);
        $query->execute();

        $result = $query->fetch(\PDO::FETCH_ASSOC);

        return $result !== false ? new EPerson($result) : null;
    }

    /**
     * @param EPerson $person
     * @return Response
     * @throws \App\Settings\Exceptions\DatabaseException
     * @throws \Doctrine\DBAL\DBALException
     */
    public function saveByEPerson(EPerson $person)
    {
        $response = $this->validateByEPerson($person);

        if ($response->hasErrors() === false) {
            if ($person->person_id === null) {
                $this->insertByEPerson($person);
            } else {
                $this->updateByEPerson($person);
            }
            $response->setModel($person);
            $response->addMessage(new ResponseMessage(
                'Персональные данные успешно сохранены!',
                    ResponseMessage::STATUS_SUCCESS,
                    ResponseMessage::ICON_SUCCESS)
            );
        }

        return $response;
    }

    /**
     * @param EPerson $person
     * @return Response
     * @throws \Exception
     */
    protected function validateByEPerson(EPerson $person): Response
    {
        $response = new Response();
        if (trim($person->first_name) === '') {
            $response->addError('first_name', 'Поле firstName обязательно для заполнения!');
        }
        if (trim($person->last_name) === '') {
            $response->addError('last_name', 'Поле lastName обязательно для заполнения!');
        }
        if (!in_array($person->gender, EPerson::GENDERS)) {
            $response->addError('gender', 'Поле Gender обязательно для заполнения!');
        }
        $interval = new \DateInterval('P18Y');
        $interval->invert = 1;
        $date18 = (new \DateTime())->add($interval);
        $dateBirth = (new \DateTime())::createFromFormat('Y.m.d', $person->date_birth);
        if ($dateBirth->diff($date18)->invert === 1) {
            $response->addError('date_birth', '18+');
        }

        return $response;
    }

    /**
     * @param EPerson $person
     * @return int|null
     * @throws \App\Settings\Exceptions\DatabaseException
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function insertByEPerson(EPerson $person): bool
    {
        $query = $this->connection()->prepare(
            "INSERT INTO {$this->table()} 
                (user_id, first_name, last_name, date_birth, gender, hobbies) 
                VALUES (?, ?, ?, ?, ?, ?);"
        );
        $dateBirth = (new \DateTime())::createFromFormat('Y.m.d', $person->date_birth)->format('Y-m-d');
        $query->bindParam(1, $person->user_id);
        $query->bindParam(2, $person->first_name);
        $query->bindParam(3, $person->last_name);
        $query->bindParam(4, $dateBirth);
        $query->bindParam(5, $person->gender);
        $query->bindParam(6, $person->hobbies);

        return $query->execute();
    }

    /**
     * @param EPerson $person
     * @return bool
     * @throws \App\Settings\Exceptions\DatabaseException
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function updateByEPerson(EPerson $person): bool
    {
        $query = $this->connection()->prepare(
            "UPDATE {$this->table()} 
                SET first_name = ?, last_name = ?, date_birth = ?, gender = ?, hobbies = ? 
                WHERE person_id = ?;"
        );
        $dateBirth = (new \DateTime())::createFromFormat('Y.m.d', $person->date_birth)->format('Y-m-d');
        $query->bindParam(1, $person->first_name);
        $query->bindParam(2, $person->last_name);
        $query->bindParam(3, $dateBirth);
        $query->bindParam(4, $person->gender);
        $query->bindParam(5, $person->hobbies);
        $query->bindParam(6, $person->person_id);

        return $query->execute();
    }
}