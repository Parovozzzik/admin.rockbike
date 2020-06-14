<?php

namespace App\Models;

use App\Models\Entities\EAttr;
use App\Models\Entities\EGoodAttr;
use App\Models\Entities\EReference;
use App\Models\Entities\EReferenceValue;

/**
 * Class ReferenceValue
 * @package App\Models
 */
class ReferenceValue extends Model
{
    /** @var string */
    public $entityName = EReferenceValue::class;

    /**
     * @param int $referenceId
     * @return array
     * @throws \App\Settings\Exceptions\DatabaseException
     */
    public function getByReferenceId(int $referenceId): array
    {
        $tableReferences = EReference::table();
        $tableReferencesValues = EReferenceValue::table();
        $referenceTypeRef = EReference::REF_TYPE_REF;

        $query =
            "SELECT " .
                "rv.*, " .
                "r.type as r_type, r2.type as r2_type, " .
                "rv2.reference_value_id as rv2_reference_value_id, rv2.value_int as rv2_value_int, " .
                "rv2.value_string as rv2_value_string, rv2.value_text as rv2_value_text, " .
                "rv2.visible as rv2_visible, rv2.deleted as rv2_deleted " .
            "FROM {$tableReferencesValues} rv " .
            "JOIN {$tableReferences} r ON rv.reference_id = r.reference_id " .
            "LEFT JOIN {$tableReferencesValues} rv2 ON rv.value_int = rv2.reference_value_id AND r.type = '{$referenceTypeRef}' " .
            "LEFT JOIN {$tableReferences} r2 ON r.parent_reference_id = r2.reference_id AND r.type = '{$referenceTypeRef}' " .
            "WHERE r.reference_id = {$referenceId};";

        $result = $this->query($query)->toArray();

        return $result;
    }

    /**
     * @param int $referenceValueId
     * @return EReferenceValue|null
     * @throws \App\Settings\Exceptions\DatabaseException
     */
    public function getByIdWithParent(int $referenceValueId): ?EReferenceValue
    {
        $tableReferences = EReference::table();
        $tableReferencesValues = EReferenceValue::table();
        $referenceTypeRef = EReference::REF_TYPE_REF;

        $query =
            "SELECT " .
            "rv.*, " .
            "r.type as r_type, r.name as r_name, r2.type as r2_type, r2.name as r2_name, " .
            "rv2.reference_value_id as rv2_reference_value_id, rv2.value_int as rv2_value_int, " .
            "rv2.value_string as rv2_value_string, rv2.value_text as rv2_value_text, " .
            "rv2.visible as rv2_visible, rv2.deleted as rv2_deleted " .
            "FROM {$tableReferencesValues} rv " .
            "JOIN {$tableReferences} r ON rv.reference_id = r.reference_id " .
            "LEFT JOIN {$tableReferencesValues} rv2 ON rv.value_int = rv2.reference_value_id AND r.type = '{$referenceTypeRef}' " .
            "LEFT JOIN {$tableReferences} r2 ON r.parent_reference_id = r2.reference_id AND r.type = '{$referenceTypeRef}' " .
            "WHERE rv.reference_value_id = {$referenceValueId};";
        /** @var EReferenceValue $referenceValue */
        $referenceValue = $this->query($query)->first();

        return $referenceValue !== false ? $referenceValue : null;
    }

    /**
     * @param int $page
     * @param int $limit
     * @param array $filters
     * @return array
     * @throws \App\Settings\Exceptions\DatabaseException
     */
    public function getList(int $page = 0, int $limit = 25, array $filters = []): array
    {
        $page = $page <= 0 ? 0 : $page;
        $offset = $limit * ($page <= 0 ? 0 : $page - 1);

        /**
         * TODO: need processing of filters
         */

        $tableReferences = EReference::table();
        $tableReferencesValues = EReferenceValue::table();
        $referenceTypeRef = EReference::REF_TYPE_REF;

        $queryCount =
            "SELECT COUNT(reference_value_id) as count " .
            "FROM {$tableReferencesValues};";
        $count = $this->query($queryCount)->toArray('count')[0];

        $query =
            "SELECT " .
                "rv.*, " .
                "r.type as r_type, r.name as r_name, r2.type as r2_type, r2.name as r2_name, " .
                "rv2.reference_value_id as rv2_reference_value_id, rv2.value_int as rv2_value_int, " .
                "rv2.value_string as rv2_value_string, rv2.value_text as rv2_value_text, " .
                "rv2.visible as rv2_visible, rv2.deleted as rv2_deleted " .
            "FROM {$tableReferencesValues} rv " .
            "JOIN {$tableReferences} r ON rv.reference_id = r.reference_id " .
            "LEFT JOIN {$tableReferencesValues} rv2 ON rv.value_int = rv2.reference_value_id AND r.type = '{$referenceTypeRef}' " .
            "LEFT JOIN {$tableReferences} r2 ON r.parent_reference_id = r2.reference_id AND r.type = '{$referenceTypeRef}'" .
            "LIMIT {$limit} " .
            "OFFSET {$offset};";
        $data = $this->query($query);
        $pager = Model::buildPager($page, $count, $limit);

        return [
            'data' => $data->toArray(),
            'pager' => $pager
        ];
    }

    /**
     * @param int $referenceValueId
     * @return bool
     * @throws \App\Settings\Exceptions\DatabaseException
     */
    public function isUsed(int $referenceValueId): bool
    {
        $tableAttrs = EAttr::table();
        $tableGoodsAttrs = EGoodAttr::table();
        $tableReferences = EReference::table();
        $tableReferencesValues = EReferenceValue::table();
        $attrRefType = EAttr::ATTR_TYPE_REF;
        $goodAttrTypeInt = EAttr::ATTR_TYPE_INT;

        $query =
            "SELECT COUNT(rv.reference_value_id) as count " .
            "FROM {$tableReferencesValues} rv " .
            "JOIN {$tableReferences} r ON rv.reference_id = r.reference_id " .
            "JOIN {$tableAttrs} a ON r.reference_id = a.reference_id " .
            "JOIN {$tableGoodsAttrs} ga ON a.attr_id = ga.attr_id AND ga.value_int = rv.reference_value_id " .
            "WHERE a.type = '{$attrRefType}' " .
                "AND ga.type = '{$goodAttrTypeInt}' " .
                "AND rv.reference_value_id = {$referenceValueId};";
        $count = (int)$this->query($query)->toArray('count')[0];

        return $count > 0;
    }
}