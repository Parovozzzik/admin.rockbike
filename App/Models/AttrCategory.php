<?php

namespace App\Models;

use App\Models\Entities\EAttr;
use App\Models\Entities\EAttrCategory;
use App\Models\Entities\ECategory;

class AttrCategory extends Model
{
    /** @var string */
    public $entityName = EAttrCategory::class;

    /**
     * @param int $attrCategoryId
     * @return EAttrCategory|null
     * @throws \App\Settings\Exceptions\DatabaseException
     */
    public function getByIdWithRelations(int $attrCategoryId): ?EAttrCategory
    {
        $tableAttrs = EAttr::table();
        $tableCategories = ECategory::table();
        $tableAttrsCategories = $this->table();

        $query =
            "SELECT ac.*, c.name as category_name, a.name as attr_name " .
            "FROM {$tableAttrsCategories} ac " .
            "JOIN {$tableCategories} c ON c.category_id = ac.category_id " .
            "JOIN {$tableAttrs} a ON a.attr_id = ac.attr_id " .
            "WHERE ac.attr_category_id = {$attrCategoryId};";
        /** @var EAttrCategory $attrCategory */
        $attrCategory = $this->query($query)->first();

        return $attrCategory !== false ? $attrCategory : null;
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

        $tableAttrsCategories = $this->table();
        $queryCount =
            "SELECT COUNT(attr_category_id) as count " .
            "FROM {$tableAttrsCategories};";
        $count = $this->query($queryCount)->toArray('count')[0];

        $tableAttrs = EAttr::table();
        $tableCategories = ECategory::table();
        $query =
            "SELECT ac.*, c.name as category_name, a.name as attr_name " .
            "FROM {$tableAttrsCategories} ac " .
            "JOIN {$tableCategories} c ON c.category_id = ac.category_id " .
            "JOIN {$tableAttrs} a ON a.attr_id = ac.attr_id " .
            "LIMIT {$limit} " .
            "OFFSET {$offset};";
        $data = $this->query($query);
        $pager = Model::buildPager($page, $count, $limit);

        return [
            'data' => $data->toArray(),
            'pager' => $pager
        ];
    }
}