<?php

namespace App\Models\Entities\Responses;

use App\Models\Entities\Entity;

/**
 * Class Response
 * @package App\Models\Entities\Responses
 */
class Response
{
    /** @var string */
    protected $title;

    /** @var array|ResponseMessage[] */
    protected $messages = [];

    /** @var Entity|null */
    protected $model;

    /** @var Entity[]|array|null */
    protected $models;

    /** @var array */
    protected $errors;

    /** @var string|null */
    protected $view;

    /** @var int|null */
    protected $userId;

    /** @var string|null */
    protected $email;

    /**
     * @return string
     */
    public function getTitle(): string
    {
        if (!isset($this->title) || trim($this->title) === '') {
            $this->title = getenv('NAME');
        }
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    /**
     * @return array|ResponseMessage[]
     */
    public function getMessages(): array
    {
        return $this->messages;
    }

    /**
     * @param array|ResponseMessage[] $messages
     */
    public function setMessages(array $messages): void
    {
        $this->messages = $messages;
    }

    /**
     * @param ResponseMessage $message
     */
    public function addMessage(ResponseMessage $message): void
    {
        $this->messages[] = $message;
    }

    /**
     * @return Entity|null
     */
    public function getModel(): ?Entity
    {
        return $this->model;
    }

    /**
     * @param Entity|null $model
     */
    public function setModel(?Entity $model): void
    {
        $this->model = $model;
    }

    /**
     * @return Entity[]|array|null
     */
    public function getModels()
    {
        return $this->models;
    }

    /**
     * @param Entity[]|array|null $models
     */
    public function setModels($models): void
    {
        $this->models = $models;
    }

    /**
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors ?? [];
    }

    /**
     * @param array|null $errors
     */
    public function setErrors(?array $errors): void
    {
        $this->errors = $errors;
    }

    /**
     * @param $field
     * @param $message
     */
    public function addError(string $field, string $message): void
    {
        $this->errors = array_merge_recursive([$field => [$message]], $this->errors ?? []);
    }

    /**
     * @return bool
     */
    public function hasErrors(): bool
    {
        return count($this->getErrors()) > 0;
    }

    /**
     * @return string|null
     */
    public function getView(): ?string
    {
        return $this->view;
    }

    /**
     * @param string|null $view
     */
    public function setView(?string $view): void
    {
        $this->view = $view;
    }

    /**
     * @return int|null
     */
    public function getUserId(): ?int
    {
        return $this->userId;
    }

    /**
     * @param int|null $id
     */
    public function setUserId(?int $userId): void
    {
        $this->userId = $userId;
    }

    /**
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @param string|null $email
     */
    public function setEmail(?string $email): void
    {
        $this->email = $email;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $result = [];
        $properties = (new \ReflectionObject($this))->getProperties();

        foreach ($properties as $property) {
            $propertyName = $property->getName();
            if (is_callable($this->$propertyName)) {
                continue;
            }
            $getter = 'get' . ucfirst($propertyName);

            if (method_exists($this, $getter)) {
                if (is_object($this->$getter())) {
                    $obj = $this->$getter();
                    if ($obj instanceof \DateTime) {
                        $value = $obj->format('c');
                    } else {
                        $value = $obj->toArray();
                    }
                    $result[$propertyName] = $value;
                } else {
                    $result[$propertyName] = $this->$getter();
                }
            } else {
                $result[$propertyName] = $this->$propertyName;
            }
        }

        return $result;
    }
}