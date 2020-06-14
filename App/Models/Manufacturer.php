<?php

namespace App\Models;

use App\Models\Entities\EManufacturer;

/**
 * Class Manufacturer
 * @package App\Models
 */
class Manufacturer extends Model
{
    /** @var string */
    public $entityName = EManufacturer::class;

    /**
     * @param string $text
     * @return array
     * @throws \App\Settings\Exceptions\DatabaseException
     */
    public function getByText(string $text): array
    {
        $table = $this->table();
        $query =
            "SELECT manufacturer_id, name " .
            "FROM {$table} " .
            "WHERE (name LIKE '%{$text}%' " .
                "OR slug LIKE '%{$text}%' " .
                "OR description LIKE '%{$text}%') " .
                "AND visible = 1 " .
                "AND deleted = 0;";

        $result = $this->query($query)->toArray('manufacturer_id', 'name');

        return $result;
    }
}