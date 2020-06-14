<?php

namespace App\Models;

use App\Models\Entities\ECategory;
use App\Models\Entities\EGood;
use App\Models\Entities\EGoodCategory;

/**
 * Class GoodCategory
 * @package App\Models
 */
class GoodCategory extends Model
{
    /** @var string */
    public $entityName = EGoodCategory::class;

    /**
     * @param int $goodId
     * @param int $categoryId
     * @return bool
     * @throws \Doctrine\DBAL\DBALException
     */
    public function isExists(int $goodId, int $categoryId): bool
    {
        return $this->isRowExists(
            [
                'good_id' => $goodId,
                'category_id' => $categoryId,
            ]
        );
    }

    /**
     * @param int $goodId
     * @return bool
     * @throws \Doctrine\DBAL\DBALException
     */
    public function isMainExists(int $goodId): bool
    {
        return $this->isRowExists(
            [
                'good_id' => $goodId,
                'is_main' => 1,
            ]
        );
    }

    /**
     * @param int $goodCategoryId
     * @return EGoodCategory|null
     * @throws \App\Settings\Exceptions\DatabaseException
     */
    public function getByIdWithRelations(int $goodCategoryId): ?EGoodCategory
    {
        $tableGoods = EGood::table();
        $tableCategories = ECategory::table();
        $tableGoodsCategories = $this->table();

        $query =
            "SELECT gc.*, c.name as category_name, g.name as good_name " .
            "FROM {$tableGoodsCategories} gc " .
            "JOIN {$tableCategories} c ON c.category_id = gc.category_id " .
            "JOIN {$tableGoods} g ON g.good_id = gc.good_id " .
            "WHERE gc.good_category_id = {$goodCategoryId};";
        /** @var EGoodCategory $goodCategory */
        $goodCategory = $this->query($query)->first();

        return $goodCategory !== false ? $goodCategory : null;
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
        $tableGoods = EGood::table();
        $tableCategories = ECategory::table();
        $tableGoodsCategories = $this->table();

        $queryCount =
            "SELECT COUNT(good_category_id) as count " .
            "FROM {$tableGoodsCategories};";
        $count = $this->query($queryCount)->toArray('count')[0];

        $query =
            "SELECT gc.*, c.name as category_name, g.name as good_name " .
            "FROM {$tableGoodsCategories} gc " .
            "JOIN {$tableCategories} c ON c.category_id = gc.category_id " .
            "JOIN {$tableGoods} g ON g.good_id = gc.good_id;";
        $data = $this->query($query);
        $pager = Model::buildPager($page, $count, $limit);

        return [
            'data' => $data->toArray(),
            'pager' => $pager
        ];
    }
}