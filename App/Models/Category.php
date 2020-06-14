<?php

namespace App\Models;

use App\Models\Entities\EAttr;
use App\Models\Entities\EAttrCategory;
use App\Models\Entities\ECategory;
use App\Models\Entities\EGood;
use App\Models\Entities\EGoodCategory;

/**
 * Class Category
 * @package App\Models
 */
class Category extends Model
{
    /** @var string */
    public $entityName = ECategory::class;

    /**
     * @param int $categoryId
     * @return ECategory|null
     * @throws \App\Settings\Exceptions\DatabaseException
     */
    public function getByIdWithParent(int $categoryId): ?ECategory
    {
        $tableCategories = $this->table();
        $query =
            "SELECT c.*, pc.name as parent_category_name " .
            "FROM {$tableCategories} c " .
            "LEFT JOIN {$tableCategories} pc ON pc.category_id = c.parent_category_id " .
            "WHERE c.category_id = {$categoryId};";
        /** @var ECategory $category */
        $category = $this->query($query)->first();

        return $category !== false ? $category : null;
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

        $tableCategories = $this->table();
        $queryCount =
            "SELECT COUNT(category_id) as count " .
            "FROM {$tableCategories};";
        $count = $this->query($queryCount)->toArray('count')[0];

        $query =
            "SELECT c.*, pc.name as parent_category_name " .
            "FROM {$tableCategories} c " .
            "LEFT JOIN {$tableCategories} pc ON pc.category_id = c.parent_category_id " .
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
     * @param int $goodId
     * @param bool|null $isMain
     * @return array
     * @throws \App\Settings\Exceptions\DatabaseException
     */
    public function getByGoodId(int $goodId, ?bool $isMain = null): array
    {
        $tableGoods = EGood::table();
        $tableGoodsCategories = EGoodCategory::table();

        $queryIsMain = '';
        if ($isMain !== null) {
            $queryIsMain = " AND gc.is_main = " . (int)$isMain;
        }

        $query =
            "SELECT c.*, gc.good_category_id, gc.is_main " .
            "FROM {$this->table()} c " .
            "JOIN {$tableGoodsCategories} gc ON gc.category_id = c.category_id " .
            "JOIN {$tableGoods} g ON gc.good_id = g.good_id " .
            "WHERE g.good_id = {$goodId} {$queryIsMain};";
        $result = $this->query($query)->toArray();

        return $result;
    }

    /**
     * @param int $attrId
     * @return array
     * @throws \App\Settings\Exceptions\DatabaseException
     */
    public function getByAttrId(int $attrId): array
    {
        $tableAttrs = EAttr::table();
        $tableAttrsCategories = EAttrCategory::table();
        $query =
            "SELECT c.*, ac.attr_category_id " .
            "FROM {$this->table()} c " .
            "JOIN {$tableAttrsCategories} ac ON ac.category_id = c.category_id " .
            "JOIN {$tableAttrs} g ON ac.attr_id = g.attr_id " .
            "WHERE g.attr_id = {$attrId};";
        $result = $this->query($query)->toArray();

        return $result;
    }

    /**
     * @param string $text
     * @param int|null $valueExclude
     * @return array
     * @throws \App\Settings\Exceptions\DatabaseException
     */
    public function getByText(string $text, ?int $valueExclude = null): array
    {
        $table = $this->table();
        $query =
            "SELECT category_id, name " .
            "FROM {$table} " .
            "WHERE (name LIKE '%{$text}%' " .
                "OR slug LIKE '%{$text}%' " .
                "OR description LIKE '%{$text}%') " .
                /** TODO: filter for good? Can we create relation for goods and parent categories? */
                //"AND parent_category_id IS NOT NULL " .
                ($valueExclude !== null ? "AND category_id != {$valueExclude} " : "") .
                "AND visible = 1 " .
                "AND deleted = 0;";

        $result = $this->query($query)->toArray('category_id', 'name');

        return $result;
    }
}