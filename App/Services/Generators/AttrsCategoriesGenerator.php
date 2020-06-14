<?php

namespace App\Services\Generators;

use App\Models\Entities\EAttr;
use App\Models\Entities\ECategory;
use App\Models\Entities\EReference;

/**
 * Class AttrsCategoriesGenerator
 * @package App\Services\Generators
 */
class AttrsCategoriesGenerator extends AbstractGenerator
{
    public function run()
    {
        $this->generateReferences();
        $this->generateAttrsCategories();
    }

    /**
     * generateReferences
     */
    private function generateReferences()
    {
        $references = [
            [
                'name' => 'Типы шлемов',
                'slug' => 'helmet_type',
                'type' => 'string',
                'visible' => 1,
                'values' => [
                    'Детский',
                    'Кросс/Эндуро',
                    'Открытый',
                    'Интегральный',
                    'Модульный',
                    'Универсальный',
                    'Внедорожный',
                    'Снегоходный',
                    'Дорожный',
                    'Спортивный',
                    'Туристический',
                ],
            ],
            [
                'name' => 'Материалы шлемов',
                'slug' => 'helmet_material',
                'type' => 'string',
                'visible' => 1,
                'values' => [
                    'Карбон',
                    'Поликарбонат',
                    'Термопластик',
                    'Композит',
                    'Стекловолокно',
                ],
            ],
            [
                'name' => 'Размеры шлемов',
                'slug' => 'helmet_size',
                'type' => 'string',
                'visible' => 1,
                'values' => [
                    'XS', 'S', 'M', 'L', 'XL', 'XXL', '3XL', 'XXS', '4XL', 'MS', 'ML', 'CS',
                ],
            ],
            [
                'name' => 'Цвета шлемов',
                'slug' => 'helmet_color',
                'type' => 'string',
                'visible' => 1,
                'values' => [
                    '2550b0',
                    '66f804',
                    '959594',
                    '000000',
                    'f8a505',
                    'ffffff',
                    'e9df3f',
                    '53301b',
                    'yyyqqq',
                    '17991d',
                    'd508a7',
                    'ca2d21',
                ],
            ],
            [
                'name' => 'Типы обуви',
                'slug' => 'shoes_type',
                'type' => 'string',
                'visible' => 1,
                'values' => [
                    'Женские',
                    'Кросс/Эндуро',
                    'Спортивные',
                    'Теристические',
                    'Мотокросовки',
                ],
            ],
            [
                'name' => 'Материалы обуви',
                'slug' => 'shoes_material',
                'type' => 'string',
                'visible' => 1,
                'values' => [
                    'Замша',
                    'Кожа',
                    'Текстиль',
                ],
            ],
            [
                'name' => 'Половая принадлежность',
                'slug' => 'gender',
                'type' => 'string',
                'visible' => 1,
                'values' => [
                    'Детские',
                    'Женские',
                    'Мужские',
                ],
            ],
            [
                'name' => 'Размеры обуви',
                'slug' => 'shoes_size',
                'type' => 'int',
                'visible' => 1,
                'values' => [
                    33, 34, 35, 36, 37, 38, 39, 40, 41, 42, 43, 44, 45, 46, 47, 48,
                ],
            ],
            [
                'name' => 'Цвета обуви',
                'slug' => 'shoes_color',
                'type' => 'string',
                'visible' => 1,
                'values' => [
                    '959594',
                    '17991d',
                    'yyyqqq',
                    '000000',
                    '53301b',
                    'ffffff',
                    '2550b0',
                    'ca2d21',
                    'f8a505',
                    '66f804',
                    'e9df3f',
                    'd508a7',
                ],
            ],
            [
                'name' => 'Материалы брюк',
                'slug' => 'pants_material',
                'type' => 'string',
                'visible' => 1,
                'values' => [
                    'Джинса',
                    'Перфорированная кожа',
                    'Кожа',
                    'Текстиль',
                ],
            ],
            [
                'name' => 'Размеры брюк',
                'slug' => 'pants_size',
                'type' => 'string',
                'visible' => 1,
                'values' => [
                    'XS', 'S', 'M', 'L', 'XL', 'XXL', '3XL', 'XXS', '4XL', 'MS', 'ML', 'CS',
                    22, 24, 26, 28, 30, 32, 34, 36, 38, 40, 42, 44, 46, 48, 50, 52, 54, 56, 58,
                ]
            ],
            [
                'name' => 'Цвета брюк',
                'slug' => 'pants_color',
                'type' => 'string',
                'visible' => 1,
                'values' => [
                    '959594',
                    '17991d',
                    'yyyqqq',
                    '000000',
                    '53301b',
                    'ffffff',
                    '2550b0',
                    'ca2d21',
                    'f8a505',
                    '66f804',
                    'e9df3f',
                    'd508a7',
                ],
            ],
        ];

        foreach ($references as $reference) {
            $referenceId = $this->createReference($reference);
            foreach ($reference['values'] as $value) {
                $this->createReferenceValue($referenceId, $reference['type'], $value);
            }
        }
    }

    /**
     * @param array $reference
     * @return int
     */
    private function createReference(array $reference)
    {
        $referencesTable = EReference::$table;

        $referenceName = $reference['name'];
        $referenceSlug = $reference['slug'];
        $referenceType = $reference['type'];
        $referenceVisible = $reference['visible'];
        $referenceQuery =
            "INSERT INTO {$referencesTable}
                (name, slug, type, visible)
            VALUES ('$referenceName', '$referenceSlug', '$referenceType', $referenceVisible);";
        $this->db->exec($referenceQuery);

        return (int)$this->db->lastInsertId();
    }

    /**
     * @param int $referenceId
     * @param string $type
     * @param $value
     */
    private function createReferenceValue(int $referenceId, string $type, $value)
    {
        $referencesValuesable = 'rb_references_values';
        $visible = 1;
        $columnName = 'value_' . $type;
        switch ($type) {
            case 'int':
                $value = (int)$value;
                break;
            case 'string':
            case 'text':
                $value = (string)$value;
                break;
        }

        $referenceValueQuery =
            "INSERT INTO {$referencesValuesable}
                (reference_id, $columnName, visible)
            VALUES ($referenceId, " . ($type === 'int' ? $value : "'$value'") . ", $visible);";
        $this->db->exec($referenceValueQuery);
    }

    /**
     * generateAttrsCategories
     */
    private function generateAttrsCategories()
    {
        $categories = [
            [
                'name' => 'Мототовары',
                'slug' => 'goods',
                'description' => 'Мототовары',
                'parent_category_slug' => null,
                'visible' => 1,
                'attrs' => [
                    [
                        'name' => 'Производитель',
                        'slug' => 'manufacturer',
                        'description' => 'Производитель',
                        'type' => 'table',
                        'table_name' => 'rb_manufacturers',
                        'visible' => 1,
                    ],
                    [
                        'name' => 'Модель',
                        'slug' => 'model',
                        'description' => 'Модель',
                        'type' => 'string',
                        'visible' => 1,
                    ],
                ],
            ],
            [
                'name' => 'Шлемы',
                'slug' => 'helmet',
                'description' => 'Шлемы',
                'parent_category_slug' => 'goods',
                'visible' => 1,
                'attrs' => [
                    [
                        'name' => 'Тип',
                        'slug' => 'helmet_type',
                        'description' => 'Тип',
                        'type' => 'ref',
                        'reference_slug' => 'helmet_type',
                        'visible' => 1,
                    ],
                    [
                        'name' => 'Материал',
                        'slug' => 'helmet_material',
                        'description' => 'Материал',
                        'type' => 'ref',
                        'reference_slug' => 'helmet_material',
                        'visible' => 1,
                    ],
                    [
                        'name' => 'Вес, г',
                        'slug' => 'weight_g',
                        'description' => 'Вес, г',
                        'type' => 'int',
                        'visible' => 1,
                    ],
                    [
                        'name' => 'Размер',
                        'slug' => 'helmet_size',
                        'description' => 'Размер',
                        'type' => 'ref',
                        'reference_slug' => 'helmet_size',
                        'visible' => 1,
                    ],
                    [
                        'name' => 'Цвет',
                        'slug' => 'helmet_color',
                        'description' => 'Цвет',
                        'type' => 'ref',
                        'reference_slug' => 'helmet_color',
                        'visible' => 1,
                    ],
                ]
            ],
            [
                'name' => 'Обувь',
                'slug' => 'shoes',
                'description' => 'Обувь',
                'parent_category_slug' => 'goods',
                'visible' => 1,
                'attrs' => [
                    [
                        'name' => 'Тип',
                        'slug' => 'shoes_type',
                        'description' => 'Тип',
                        'type' => 'ref',
                        'reference_slug' => 'shoes_type',
                        'visible' => 1,
                    ],
                    [
                        'name' => 'Материал',
                        'slug' => 'shoes_material',
                        'description' => 'Материал',
                        'type' => 'ref',
                        'reference_slug' => 'shoes_material',
                        'visible' => 1,
                    ],
                    [
                        'name' => 'Пол',
                        'slug' => 'gender',
                        'description' => 'Пол',
                        'type' => 'ref',
                        'reference_slug' => 'gender',
                        'visible' => 1,
                    ],
                    [
                        'name' => 'Размер',
                        'slug' => 'shoes_size',
                        'description' => 'Размер',
                        'type' => 'ref',
                        'reference_slug' => 'shoes_size',
                        'visible' => 1,
                    ],
                    [
                        'name' => 'Цвет',
                        'slug' => 'shoes_color',
                        'description' => 'Цвет',
                        'type' => 'ref',
                        'reference_slug' => 'shoes_color',
                        'visible' => 1,
                    ],
                ],
            ],
            [
                'name' => 'Брюки',
                'slug' => 'pants',
                'description' => 'Брюки',
                'parent_category_slug' => 'goods',
                'visible' => 1,
                'attrs' => [
                    [
                        'name' => 'Материал',
                        'slug' => 'pants_material',
                        'description' => 'Материал',
                        'type' => 'ref',
                        'reference_slug' => 'pants_material',
                        'visible' => 1,
                    ],
                    [
                        'name' => 'Пол',
                        'slug' => 'gender',
                        'description' => 'Пол',
                        'type' => 'ref',
                        'reference_slug' => 'gender',
                        'visible' => 1,
                    ],
                    [
                        'name' => 'Размер',
                        'slug' => 'pants_size',
                        'description' => 'Размер',
                        'type' => 'ref',
                        'reference_slug' => 'pants_size',
                        'visible' => 1,
                    ],
                    [
                        'name' => 'Цвет',
                        'slug' => 'pants_color',
                        'description' => 'Цвет',
                        'type' => 'ref',
                        'reference_slug' => 'pants_color',
                        'visible' => 1,
                    ],
                ]
            ]
        ];

        foreach ($categories as $category) {
            $categoryId = $this->createCategory($category);
            foreach ($category['attrs'] as $attr) {
                $attrId = $this->createAttr($attr);
                $this->createAttrCategory($attrId, $categoryId);
            }
        }
    }

    /**
     * @param array $category
     * @return string
     */
    private function createCategory(array $category)
    {
        $categoriesTable = ECategory::$table;

        $categoryName = $category['name'];
        $categorySlug = $category['slug'];
        $categoryDesc = $category['description'];
        $categoryParentSlug = $category['parent_category_slug'];
        if ($categoryParentSlug !== null) {
            $categoryParentQuery =
                "SELECT category_id 
                FROM $categoriesTable
                WHERE slug = '$categoryParentSlug';";
            $categoryParentId = $this->db->query($categoryParentQuery)->fetchColumn();
        } else {
            $categoryParentId = 'NULL';
        }
        $categoryVisible = $category['visible'];

        $categoryQuery =
            "INSERT INTO $categoriesTable 
                (name, slug, description, parent_category_id, visible)
            VALUES ('$categoryName', '$categorySlug', '$categoryDesc', $categoryParentId, $categoryVisible);";
        $this->db->exec($categoryQuery);

        return (int)$this->db->lastInsertId();
    }

    /**
     * @param array $attr
     * @return string
     */
    private function createAttr(array $attr)
    {
        $attrsTable = EAttr::$table;
        $referenceTable = EReference::table();

        $attrName = $attr['name'];
        $attrSlug = $attr['slug'];
        $attrDesc = $attr['description'];
        $attrType = $attr['type'];
        $attrReferenceSlug = $attr['reference_slug'] ?? null;
        if ($attrType === 'ref' && $attrReferenceSlug !== null) {
            $referenceQuery =
                "SELECT reference_id 
                FROM {$referenceTable}
                WHERE slug = '{$attrReferenceSlug}';";
            $attrReferenceId = $this->db->query($referenceQuery)->fetchColumn();
        }
        $attrTableName = $attr['table_name'] ?? null;
        $attrVisible = $attr['visible'];

        $attrValues = "('$attrName', '$attrSlug', '$attrDesc', '$attrType', "  .
            ($attrReferenceSlug !== null ? $attrReferenceId : 'NULL') . ", " .
            ($attrTableName !== null ? "'$attrTableName'" : 'NULL') . ", " .
            "$attrVisible);";
        $attrQuery =
            "INSERT INTO $attrsTable
                (name, slug, description, type, reference_id, table_name, visible)
            VALUES $attrValues";
        $this->db->exec($attrQuery);

        return (int)$this->db->lastInsertId();

    }

    /**
     * @param int $attrId
     * @param int $categoryId
     */
    private function createAttrCategory(int $attrId, int $categoryId)
    {
        $attrsCategoriesTable = 'rb_attrs_categories';

        $attrsCategoriesQuery =
            "INSERT INTO $attrsCategoriesTable
                (attr_id, category_id)
            VALUES ($attrId, $categoryId);";

        $this->db->exec($attrsCategoriesQuery);
    }
}