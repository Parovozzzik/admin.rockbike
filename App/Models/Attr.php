<?php

namespace App\Models;

use App\Models\Entities\EAttr;
use App\Models\Entities\EAttrCategory;
use App\Models\Entities\ECategory;
use App\Models\Entities\EGood;
use App\Models\Entities\EGoodAttr;
use App\Models\Entities\EGoodCategory;
use App\Models\Entities\EReference;
use App\Models\Entities\EReferenceValue;

/**
 * Class Attr
 * @package App\Models
 */
class Attr extends Model
{
    /** @var string */
    public $entityName = EAttr::class;

    /**
     * @param int $goodId
     * @return array
     * @throws \App\Settings\Exceptions\DatabaseException
     */
    public function getByGoodId(int $goodId): array
    {
        $tableGoods = EGood::table();
        $tableGoodsAttrs = EGoodAttr::table();
        $tableGoodsCategories = EGoodCategory::table();
        $tableAttrsCategories = EAttrCategory::table();
        $tableReferences = EReference::table();
        $tableReferencesValues = EReferenceValue::table();
        
        $query =
            "SELECT " .
                "ra.*, rac.required, " .
                "rga.good_attr_id as ga_good_attr_id, rga.value_int as ga_value_int, rga.value_string as ga_value_string, rga.value_text as ga_value_text, " .
                "rr.name as r_name, rr.slug as r_slug, rr.type as r_type, " .
                "rrv.value_int as rv_value_int, rrv.value_string as rv_value_string, rrv.value_text as rv_value_text, " .
                "rrv2.value_int as rv2_value_int, rrv2.value_string as rv2_value_string, rrv2.value_text as rv2_value_text " .
            "FROM {$this->table()} ra " .
            "JOIN {$tableAttrsCategories} rac on ra.attr_id = rac.attr_id " .
            "LEFT JOIN {$tableGoodsCategories} rgc on rgc.category_id = rac.category_id OR rac.category_id IN ( " .
                "SELECT cat2.category_id " .
                "FROM ( " .
                    "SELECT @r AS _id, " .
                        "(SELECT @r := parent_category_id FROM rb_categories WHERE category_id = _id) AS parent_category_id, " .
                        "@l := @l + 1 AS lvl " .
                    "FROM (SELECT @r := rgc.category_id, @l := 0) vars, rb_categories h " .
                    "WHERE @r <> 0 " .
                ") cat1 " .
                "JOIN rb_categories cat2 ON cat1._id = cat2.category_id " .
                "ORDER BY cat1.lvl DESC " .
            ") " .
            "RIGHT JOIN {$tableGoods} rg on rgc.good_id = rg.good_id " .
            "LEFT JOIN {$tableGoodsAttrs} rga on rg.good_id = rga.good_id AND rga.attr_id = ra.attr_id " .
            "LEFT JOIN {$tableReferences} rr on ra.reference_id = rr.reference_id " .
            "LEFT JOIN {$tableReferencesValues} rrv on rr.reference_id = rrv.reference_id " .
                "AND rga.value_int = rrv.reference_value_id " .
            "LEFT JOIN rb_references_values rrv2 on rrv.reference_value_id = rrv2.value_int AND rr.type = 'ref' " .
            "WHERE rg.good_id = {$goodId} AND rgc.is_main = 1;";

        $result = $this->query($query)->toArray();

        return $result;
    }

    /**
     * @param int $categoryId
     * @return array
     * @throws \App\Settings\Exceptions\DatabaseException
     */
    public function getByCategoryId(int $categoryId): array
    {
        $tableAttrs = EAttr::table();
        $tableAttrsCategories = EAttrCategory::table();
        $tableCategories = ECategory::table();
        $query =
            "SELECT a.attr_id, a.name, a.slug, a.visible, a.deleted, ac.attr_category_id " .
            "FROM {$tableAttrs} a " .
            "JOIN {$tableAttrsCategories} ac ON ac.attr_id = a.attr_id " .
            "JOIN {$tableCategories} c on ac.category_id = c.category_id " .
            "WHERE c.category_id = {$categoryId};";
        $result = $this->query($query)->toArray();

        return $result;
    }

    /**
     * @param int $attrId
     * @return EAttr|null
     * @throws \App\Settings\Exceptions\DatabaseException
     */
    public function getByIdWithReference(int $attrId): ?EAttr
    {
        $tableAttrs = EAttr::table();
        $tableReferences = EReference::table();

        $query =
            "SELECT a.*, r.name as reference_name " .
            "FROM {$tableAttrs} a " .
            "LEFT JOIN {$tableReferences} r ON r.reference_id = a.reference_id " .
            "WHERE a.attr_id = {$attrId};";

        /** @var EAttr $attr */
        $attr = $this->query($query)->first();

        return $attr !== false ? $attr : null;
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
            "SELECT attr_id, name " .
            "FROM {$table} " .
            "WHERE (name LIKE '%{$text}%' " .
                "OR slug LIKE '%{$text}%' " .
                "OR description LIKE '%{$text}%') " .
                "AND visible = 1 " .
                "AND deleted = 0;";

        $result = $this->query($query)->toArray('attr_id', 'name');

        return $result;
    }
}