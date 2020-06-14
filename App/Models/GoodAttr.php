<?php

namespace App\Models;

use App\Models\Entities\EAttr;
use App\Models\Entities\EGood;
use App\Models\Entities\EGoodAttr;
use App\Models\Entities\EReference;
use App\Models\Entities\EReferenceValue;

/**
 * Class GoodAttr
 * @package App\Models
 */
class GoodAttr extends Model
{
    /** @var string */
    public $entityName = EGoodAttr::class;

    /**
     * @param int $page
     * @param int $limit
     * @param array $filters
     * @return array
     * @throws \App\Settings\Exceptions\DatabaseException
     */
    public function getList(int $page = 0, int $limit = 25, array $filters = []): array
    {
        $tableGoods = EGood::table();
        $tableAttrs = EAttr::table();
        $tableReferences = EReference::table();
        $tableReferencesValues = EReferenceValue::table();
        $tableGoodsAttrs = $this->table();

        $queryCount =
            "SELECT COUNT(good_attr_id) as count " .
            "FROM {$tableGoodsAttrs};";
        $count = $this->query($queryCount)->toArray('count')[0];

        $query =
            "SELECT ga.*, " .
                "g.name as good_name, g.slug as good_slug, " .
                "a.name as attr_name, a.type as attr_type, a.reference_id as attr_ref_id, a.table_name as attr_table_name, " .
                "ref.name as ref_name, ref.type as ref_type, ref.parent_reference_id as ref_parent_id, " .
                "ref_value.reference_value_id as ref_value_id, ref_value.value_int as ref_value_int, " .
                "ref_value.value_string as ref_value_string, ref_value.value_text as ref_value_text, " .
                "parent_ref.name as parent_ref_name, parent_ref.type as parent_ref_type, " .
                "parent_ref_value.reference_value_id as parent_ref_value_id, parent_ref_value.value_int as parent_ref_value_int, " .
                "parent_ref_value.value_string as parent_ref_value_string, parent_ref_value.value_text as parent_ref_value_text " .
            "FROM {$tableGoodsAttrs} ga " .
            "JOIN {$tableAttrs} a ON a.attr_id = ga.attr_id " .
            "JOIN {$tableGoods} g ON g.good_id = ga.good_id " .
            "LEFT JOIN {$tableReferences} ref on a.reference_id = ref.reference_id " .
            "LEFT JOIN {$tableReferencesValues} ref_value on ref.reference_id = ref_value.reference_id " .
                "AND ga.value_int = ref_value.reference_value_id " .
            "LEFT JOIN {$tableReferences} parent_ref ON ref.parent_reference_id = parent_ref.reference_id " .
                "AND ref.type = 'ref' " .
            "LEFT JOIN {$tableReferencesValues} parent_ref_value ON ref_value.value_int = parent_ref_value.reference_value_id " .
                "AND ref.type = 'ref';";
        $data = $this->query($query);
        $pager = Model::buildPager($page, $count, $limit);

        return [
            'data' => $data->toArray(),
            'pager' => $pager
        ];
    }

    /**
     * @param int $goodAttrrId
     * @return EGoodAttr|null
     * @throws \App\Settings\Exceptions\DatabaseException
     */
    public function getByIdWithReference(int $goodAttrrId): ?EGoodAttr
    {
        $tableGoods = EGood::table();
        $tableAttrs = EAttr::table();
        $tableReferences = EReference::table();
        $tableReferencesValues = EReferenceValue::table();
        $tableGoodsAttrs = $this->table();

        $query =
            "SELECT ga.*, " .
                "g.name as good_name, g.slug as good_slug, " .
                "a.name as attr_name, a.type as attr_type, a.reference_id as attr_ref_id, " .
                "ref.name as ref_name, ref.type as ref_type, ref.parent_reference_id as ref_parent_id, " .
                "ref_value.reference_value_id as ref_value_id, ref_value.value_int as ref_value_int, " .
                "ref_value.value_string as ref_value_string, ref_value.value_text as ref_value_text, " .
                "parent_ref.name as parent_ref_name, parent_ref.type as parent_ref_type, " .
                "parent_ref_value.reference_value_id as parent_ref_value_id, parent_ref_value.value_int as parent_ref_value_int, " .
                "parent_ref_value.value_string as parent_ref_value_string, parent_ref_value.value_text as parent_ref_value_text " .
            "FROM {$tableGoodsAttrs} ga " .
            "JOIN {$tableAttrs} a ON a.attr_id = ga.attr_id " .
            "JOIN {$tableGoods} g ON g.good_id = ga.good_id " .
            "LEFT JOIN {$tableReferences} ref on a.reference_id = ref.reference_id " .
            "LEFT JOIN {$tableReferencesValues} ref_value on ref.reference_id = ref_value.reference_id " .
                "AND ga.value_int = ref_value.reference_value_id " .
            "LEFT JOIN {$tableReferences} parent_ref ON ref.parent_reference_id = parent_ref.reference_id " .
                "AND ref.type = 'ref' " .
            "LEFT JOIN {$tableReferencesValues} parent_ref_value ON ref_value.value_int = parent_ref_value.reference_value_id " .
                "AND ref.type = 'ref'
            WHERE ga.good_attr_id = {$goodAttrrId};";

        /** @var EGoodAttr $goodAttr */
        $goodAttr = $this->query($query)->first();

        return $goodAttr !== false ? $goodAttr : null;
    }
}