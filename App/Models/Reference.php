<?php

namespace App\Models;

use App\Models\Entities\EReference;
use App\Models\Entities\EReferenceValue;

/**
 * Class Reference
 * @package App\Models
 */
class Reference extends Model
{
    /** @var string */
    public $entityName = EReference::class;

    /**
     * @param int $referenceId
     * @return EReference|null
     * @throws \App\Settings\Exceptions\DatabaseException
     */
    public function getByIdWithParent(int $referenceId): ?EReference
    {
        $tableReferences = self::table();
        $query =
            "SELECT r.*, pr.name as parent_reference_name " .
            "FROM {$tableReferences} r " .
            "LEFT JOIN {$tableReferences} pr ON pr.reference_id = r.parent_reference_id " .
            "WHERE r.reference_id = {$referenceId};";
        /** @var EReference $reference */
        $reference = $this->query($query)->first();

        return $reference !== false ? $reference : null;
    }

    /**
     * @param array $referencesSlugs
     * @return array
     * @throws \App\Settings\Exceptions\DatabaseException
     */
    public function getBySlugsWithValues(array $referencesSlugs): array
    {
        $tableReferences = self::table();
        $tableReferencesValues = EReferenceValue::table();
        $referencesSlugsString = "'" . implode("','", $referencesSlugs) . "'";

        $query =
            "SELECT r.reference_id, r.name, r.slug, r.type, " .
                "rv.reference_value_id, rv.value_text, rv.value_int, rv.value_string, " .
                "r2.reference_id as r2_reference_id, r2.name as r2_name, r2.slug as r2_slug, r2.type as r2_type, " .
                "rv2.reference_value_id as rv2_reference_value_id, rv2.value_text as rv2_value_text2, " .
                "rv2.value_int as rv2_value_int, rv2.value_string as rv2_value_string " .
            "FROM {$tableReferences} r " .
            "LEFT JOIN {$tableReferencesValues} rv ON rv.reference_id = r.reference_id " .
            "LEFT JOIN {$tableReferencesValues} rv2 ON rv.value_int = rv2.reference_value_id AND r.type = 'ref' " .
            "LEFT JOIN {$tableReferences} r2 ON r.parent_reference_id = r2.reference_id AND r.type = 'ref' " .
            "WHERE r.slug IN ({$referencesSlugsString});";

        $result = $this->query($query)->toArray();

        return $result;
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
            "SELECT reference_id, name " .
            "FROM {$table} " .
            "WHERE (name LIKE '%{$text}%' " .
                "OR slug LIKE '%{$text}%') " .
                "AND visible = 1 " .
                "AND deleted = 0;";

        $result = $this->query($query)->toArray('reference_id', 'name');

        return $result;
    }
}