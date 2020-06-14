<?php

namespace App\Models;

use App\Models\Entities\EGood;

/**
 * Class Good
 * @package App\Models
 */
class Good extends Model
{
    /** @var string */
    public $entityName = EGood::class;

    /**
     * @param string $slug
     * @return EGood|null
     */
    public function getBySlug(string $slug): ?EGood
    {
        return $this->where(['slug' => $slug])->first() ?? null;
    }

    /**
     * @param string $text
     * @return array
     * @throws \App\Settings\Exceptions\DatabaseException
     */
    public function getByText(string $text): array
    {
        $table = $this->table();
        $query =
            "SELECT good_id, name " .
            "FROM {$table} " .
            "WHERE (name LIKE '%{$text}%' " .
                "OR slug LIKE '%{$text}%' " .
                "OR description LIKE '%{$text}%') " .
                "AND visible = 1 " .
                "AND deleted = 0;";

        $result = $this->query($query)->toArray('good_id', 'name');

        return $result;
    }
}