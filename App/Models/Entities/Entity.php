<?php

namespace App\Models\Entities;

/**
 * Class Entity
 * @package App\Models\Entities
 */
class Entity extends \Spot\Entity
{
    /** @var string */
    public static $table;
    /** @var string */
    public static $idColumn;

    /**
     * unmodify externals not in db field
     * @param string $field
     */
    public function unmodify($field)
    {
        if (array_key_exists($field, $this->_dataModified)) {
            unset($this->_dataModified[$field]);
        }
    }

    /**
     * return flat list of entity errors during update or create
     *
     * @return array
     */
    public function listErrors()
    {
        return array_reduce(
            $this->errors(),
            function ($r, $l) {
                return array_merge($r, $l);
            },
            []
        );
    }

    /**
     * Set all field values to their defaults or null
     */
    public function __wakeup(): void
    {
        $this->initFields();
    }

    /**
     * return not empty data for less dumps
     *
     * @return array
     */
    public function notEmptyData(): array
    {
        return array_filter(
            $this->data(),
            function ($e) {
                return is_numeric($e) ? true : !empty($e);
            }
        );
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $result = parent::toArray();

        $result = array_map(function ($item) {
            if ($item instanceof \DateTime) {
                return $item->format('c');
            } else {
                return $item;
            }
        }, $result);

        return $result;
    }
}