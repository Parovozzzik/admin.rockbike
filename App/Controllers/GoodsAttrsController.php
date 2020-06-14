<?php

namespace App\Controllers;

use App\Helpers\DbHelper;
use App\Models\Attr;
use App\Models\Category;
use App\Models\Entities\EAttr;
use App\Models\Entities\ECategory;
use App\Models\Entities\EGood;
use App\Models\Entities\EGoodAttr;
use App\Models\Entities\EGoodCategory;
use App\Models\Entities\EReference;
use App\Models\Entities\EReferenceValue;
use App\Models\Entities\Responses\Response;
use App\Models\Entities\Responses\ResponseMessage;
use App\Models\Good;
use App\Models\GoodAttr;
use App\Models\GoodCategory;
use App\Models\Reference;
use App\Models\ReferenceValue;

/**
 * Class GoodsAttrsController
 * @package App\Controllers
 */
class GoodsAttrsController extends Controller
{
    /** @var GoodAttr */
    protected $mainModel;

    /**
     * GoodsController constructor.
     * @throws \App\Settings\Exceptions\DatabaseException
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Spot\Exception
     */
    public function __construct()
    {
        parent::__construct();
        $this->mainModel = GoodAttr::getModel(EGoodAttr::class);
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'index' => !$this->isGuest,
            'view' => !$this->isGuest,
            'edit' => !$this->isGuest,
            'create' => !$this->isGuest,
            'delete' => !$this->isGuest,
        ];
    }

    /**
     * @throws \Twig\Error\Error
     */
    public function index()
    {
        $page = $this->request['page'] ?? 1;
        $goods = $this->mainModel->getList($page);

        $response = new Response();
        $response->setModels($goods['data']);

        $response->setView('goodsattrs.index');
        $this->render($response, ['pager' => $goods['pager']]);
    }

    /**
     * @throws \Twig\Error\Error
     */
    public function view()
    {
        $id = $this->request['id'];

        $goodAttr = $this->mainModel->getByIdWithReference($id);
        $response = new Response();

        if ($goodAttr instanceof EGoodAttr) {
            $response->setModel($goodAttr);
        } else {
            $message = new ResponseMessage(' Справочника с указанным идентификатором не существует',
                ResponseMessage::STATUS_ERROR,
                ResponseMessage::ICON_ERROR);

            $this->redirect('/goods-attrs', [$message]);
        }

        $response->setView('goodsattrs.view');
        $this->render($response);
    }

    /**
     * @throws \App\Settings\Exceptions\DatabaseException
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Spot\Exception
     * @throws \Twig\Error\Error
     */
    public function create()
    {
        $step = 1;
        $attrs = [];
        $referencesWithValues = [];
        $tables = [];
        $response = new Response();

        /** @var Attr $attrsModel */
        $attrsModel = Attr::getModel(EAttr::class);

        $goodCategory = new EGoodCategory();
        $response->setModel($goodCategory);

        if (isset($_POST['good_category'])) {
            $request = $_POST['good_category'];
            $goodId = (int)$request['good_id'];
            $categoryId = (int)$request['category_id'];

            /** @var GoodCategory $goodCategoryModel */
            $goodCategoryModel = GoodCategory::getModel(EGoodCategory::class);
            if ($goodCategoryModel->isExists($goodId, $categoryId)) {
                $step = 2;
                $goodCategory->data($request);
            } else {
                $this->validateGoodCategory($goodCategory, $request, $response);
                if ($goodCategory->hasErrors() === false && $response->hasErrors() === false) {
                    $result = $this->saveGoodCategory($goodCategory, $response);
                    if ($result === true) {
                        $step = 2;
                    }
                }
            }
        }

        if (isset($_POST['good_attrs'])) {
            $request = $_POST['good_attrs'];
            $goodId = (int)$request['good_id'];
            $categoryId = (int)$request['category_id'];

            /** @var GoodCategory $goodCategoryModel */
            $goodCategoryModel = GoodCategory::getModel(EGoodCategory::class);
            if (!$goodCategoryModel->isExists($goodId, $categoryId)) {
                $response->addError('good_id', 'Attributive model does not exists.');
                $response->addError('category_id', 'Attributive model does not exists.');
            } else {
                $attrsSlugs = array_values(array_diff_key(array_keys($request), ['good_id', 'category_id']));
                $attrs = $attrsModel->getByGoodId($goodId);

                $attrsSlugsDb = array_filter(array_column($attrs, 'slug'), function ($item) {
                    return $item !== null;
                });
                foreach ($attrsSlugs as $attrsSlug) {
                    if (!in_array($attrsSlug, $attrsSlugsDb)) {
                        $response->addError($attrsSlug, 'Attribute ' . $attrsSlug . ' does not exists.');
                    }
                }

                if (!$response->hasErrors()) {
                    foreach ($attrs as $attr) {
                        $conditions = ['good_id' => $goodId, 'attr_id' => $attr['attr_id']];
                        if ($this->mainModel->isRowExists($conditions)) {
                            $goodAttr = $this->mainModel->where($conditions)->first();
                        } else {
                            $goodAttr = new EGoodAttr();
                            $goodAttr->good_id = $goodId;
                            $goodAttr->attr_id = $attr['attr_id'];
                        }

                        switch ($attr['type']) {
                            case EAttr::ATTR_TYPE_INT:
                            case EAttr::ATTR_TYPE_REF:
                            case EAttr::ATTR_TYPE_TABLE:
                                $goodAttr->type = EAttr::ATTR_TYPE_INT;
                                $goodAttr->value_int = (int)$request[$attr['slug']];
                                break;
                            case EAttr::ATTR_TYPE_STRING:
                                $goodAttr->type = EAttr::ATTR_TYPE_STRING;
                                $goodAttr->value_string = (string)$request[$attr['slug']];
                                break;
                            case EAttr::ATTR_TYPE_TEXT:
                            case EAttr::ATTR_TYPE_JSON:
                                $goodAttr->type = EAttr::ATTR_TYPE_TEXT;
                                $goodAttr->value_string = (string)$request[$attr['slug']];
                                break;
                            case EAttr::ATTR_TYPE_FLOAT:
                                $goodAttr->type = EAttr::ATTR_TYPE_STRING;
                                $goodAttr->value_string = (string)round($request[$attr['slug']], $attr['round']);
                                break;
                        }

                        $this->validate($goodAttr, $attr, $response);

                        if ($goodAttr->hasErrors() === false && $response->hasErrors() === false) {
                            $this->save($goodAttr, $response);
                        }
                        $step = 2;
                    }

                    if ($response->hasErrors() === false) {
                        $message = new ResponseMessage("Все атрибуты товара #{$goodId} успешно сохранены!",
                            ResponseMessage::STATUS_SUCCESS,
                            ResponseMessage::ICON_SUCCESS);

                        $this->redirect('/goods-attrs', [$message]);
                    }
                } else {
                    $step = 2;
                }
            }
        } else {
            $request = $goodCategory->toArray();
        }

        if ($step === 1) {
            if (isset($request['good_id']) && trim($request['good_id']) !== '') {
                $goodModel = Good::getModel(EGood::class);
                $good = $goodModel->select('good_id, name')
                    ->where(['good_id' => $request['good_id']])
                    ->toArray('good_id', 'name');
            }
            if (isset($request['category_id']) && trim($request['category_id']) !== '') {
                $categoryModel = Category::getModel(ECategory::class);
                $category = $categoryModel->select('category_id, name')
                    ->where(['category_id' => $request['category_id']])
                    ->toArray('category_id', 'name');
            }
        }

        if ($step === 2) {
            list($attrs, $referencesWithValues, $tables) = $this->getDataSecondStep($goodId);
        }

        $response->setView('goodsattrs.create');

        $this->render($response,
            [
                'request' => $request,
                'attrs' => $attrs,
                'references' => $referencesWithValues,
                'tables' => $tables,
                'step' => $step,
                'good' => $good ?? [],
                'category' => $category ?? [],
            ]
        );
    }

    /**
     * @throws \App\Settings\Exceptions\DatabaseException
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Spot\Exception
     * @throws \Twig\Error\Error
     */
    public function edit()
    {
        $id = $this->request['id'];
        /** @var EGoodAttr $goodAttr */
        $goodAttr = $this->mainModel->get($id);
        $step = 1;

        $request['good_id'] = $goodAttr->good_id;

        /** @var Category $categoriesModel */
        $categoriesModel = Category::getModel(ECategory::class);
        $categories = $categoriesModel->getByGoodId($goodAttr->good_id, true);
        if (count($categories) === 1) {
            $categoryId = $categories[0]['category_id'];
            $request['category_id'] = $categoryId;
            $step = 2;
            list($attrs, $referencesWithValues, $tables) = $this->getDataSecondStep($request['good_id']);
        }

        $response = new Response();
        $response->setView('goodsattrs.create');

        $this->render($response,
            [
                'request' => $request,
                'attrs' => $attrs,
                'references' => $referencesWithValues,
                'tables' => $tables,
                'step' => $step,
            ]
        );
    }

    /**
     * Deletion of AttrCategory
     */
    public function delete()
    {
        $id = $this->request['id'];
        $goodAttr = $this->mainModel->get($id);

        if ($goodAttr instanceof EGoodAttr) {
            $this->mainModel->delete(['good_attr_id' => $goodAttr->good_attr_id]);
            $message = new ResponseMessage(
                "Связь #{$goodAttr->good_attr_id} успешно удалена",
                ResponseMessage::STATUS_SUCCESS,
                ResponseMessage::ICON_SUCCESS);
        } else {
            $message = new ResponseMessage(
                'Связи с указанным идентификатором не существует',
                ResponseMessage::STATUS_ERROR,
                ResponseMessage::ICON_ERROR);
        }

        $this->redirect('/goods-attrs', [$message]);
    }

    /**
     * @return array
     * @throws \App\Settings\Exceptions\DatabaseException
     */
    protected function getDataFirstStep(): array
    {
        $categoriesModel = Category::getModel(ECategory::class);
        $categories = $categoriesModel->select('category_id, name')
            ->where(['parent_category_id :not' => null])
            ->toArray('category_id', 'name');

        return $categories;
    }

    /**
     * @param int $goodId
     * @return array
     * @throws \App\Settings\Exceptions\DatabaseException
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Spot\Exception
     */
    protected function getDataSecondStep(int $goodId): array
    {
        /** @var Attr $attrsModel */
        $attrsModel = Attr::getModel(EAttr::class);
        $attrs = $attrsModel->getByGoodId($goodId);
        $referencesSlugs = array_filter(array_column($attrs, 'r_slug'), function ($item) {
            return $item !== null;
        });

        /** @var Reference $referencesModel */
        $referencesModel = Reference::getModel(EReference::class);
        $referencesWithValues = $referencesModel->getBySlugsWithValues($referencesSlugs);

        $attrsTables = array_filter($attrs, function ($attr) {
            return $attr['table_name'] !== null && trim($attr['table_name']) !== '';
        }, ARRAY_FILTER_USE_BOTH);

        $tables = [];
        foreach ($attrsTables as $attrsTable) {
            if (isset($attrsTable['ga_value_int']) && $attrsTable['ga_value_int'] !== null) {
                $table = getenv('DB_TABLE_PREFIX') . $attrsTable['table_name'];
                $primaryKey = DbHelper::getColumnNameByPrimaryKey($table);
                $valueInt = $attrsTable['ga_value_int'];
                $query = "SELECT name FROM {$table} WHERE {$primaryKey} = {$valueInt};";
                $value = $this->mainModel->connection()->executeQuery($query)->fetchColumn();
                $tables[$attrsTable['slug']] = [$attrsTable['ga_value_int'] => $value];
            }
        }

        return [$attrs, $referencesWithValues, $tables];
    }

    /**
     * @param EGoodCategory $goodCategory
     * @param Response $response
     * @return bool
     */
    protected function saveGoodCategory(EGoodCategory $goodCategory, Response $response): bool
    {
        try {
            /** @var GoodCategory $goodCategoryModel */
            $goodCategoryModel = GoodCategory::getModel(EGoodCategory::class);
            $result = $goodCategoryModel->save($goodCategory);

            if ((bool)$result === true) {
                $response->addMessage(
                    new ResponseMessage(
                        'Запись успешно сохранена!',
                        ResponseMessage::STATUS_SUCCESS,
                        ResponseMessage::ICON_SUCCESS)
                );

                return true;
            } else {
                $response->addMessage(
                    new ResponseMessage('Ошибка сохранения!',
                        ResponseMessage::STATUS_ERROR,
                        ResponseMessage::ICON_ERROR)
                );
            }
        } catch (\Exception $e) {
            $response->addMessage(
                new ResponseMessage('Ошибка сохранения! ' . $e->getMessage(),
                    ResponseMessage::STATUS_ERROR,
                    ResponseMessage::ICON_ERROR)
            );
        }

        return false;
    }

    /**
     * @param EGoodCategory $goodCategory
     * @param array $request
     * @param Response $response
     * @throws \App\Settings\Exceptions\DatabaseException
     * @throws \Doctrine\DBAL\DBALException
     */
    public function validateGoodCategory(EGoodCategory $goodCategory, array $request, Response $response): void
    {
        /** @var GoodCategory $goodCategoryModel */
        $goodCategoryModel = GoodCategory::getModel(EGoodCategory::class);

        $goodCategory->data($request);
        if ($goodCategoryModel->validate($goodCategory) === false) {
            foreach ($goodCategory->errors() as $key => $errors) {
                foreach ($errors as $error) {
                    $response->addError($key, $error);
                }
            }
        }
        if ($goodCategory->hasErrors() === false && $goodCategoryModel->isMainExists($goodCategory->good_id) === true) {
            $response->addError('good_id', 'У данного товара уже определена атрибутивная модель. ' .
                'Для изменение значений атрибутов выберите главную категорию.');
        }
    }

    /**
     * @param EGoodAttr $goodAttr
     * @param Response $response
     */
    protected function save(EGoodAttr $goodAttr, Response $response): void
    {
        try {
            $result = $this->mainModel->save($goodAttr);

            if ((bool)$result === true) {
                $message = new ResponseMessage(
                    'Запись успешно сохранена!',
                    ResponseMessage::STATUS_SUCCESS,
                    ResponseMessage::ICON_SUCCESS);
            } else {
                $message = new ResponseMessage('Ошибка сохранения!',
                    ResponseMessage::STATUS_ERROR,
                    ResponseMessage::ICON_ERROR);
            }
        } catch (\Exception $e) {
            $message = new ResponseMessage('Ошибка сохранения! ' . $e->getMessage(),
                ResponseMessage::STATUS_ERROR,
                ResponseMessage::ICON_ERROR);
        }
        $response->addMessage($message);
    }

    /**
     * @param EGoodAttr $goodAttr
     * @param array $attr
     * @param Response $response
     * @throws \App\Settings\Exceptions\DatabaseException
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Spot\Exception
     */
    public function validate(EGoodAttr $goodAttr, array $attr, Response $response): void
    {
        if ($this->mainModel->validate($goodAttr) === false) {
            foreach ($goodAttr->errors() as $key => $errors) {
                foreach ($errors as $error) {
                    $response->addError($key, $error);
                }
            }
        }

        if ((bool)$attr['required'] === true) {
            switch ($attr['type']) {
                case EAttr::ATTR_TYPE_INT:
                    if (!isset($goodAttr->value_int) || (int)trim($goodAttr->value_int) === 0) {
                        $response->addError($attr['slug'], 'Attribute ' . $attr['slug'] . ' is required.');
                    }
                    break;
                case EAttr::ATTR_TYPE_REF:
                    /** @var ReferenceValue $referenceValueModel */
                    $referenceValueModel = ReferenceValue::getModel(EReferenceValue::class);
                    $referenceValues = $referenceValueModel->getByReferenceId($attr['reference_id']);
                    $referenceValuesIds = array_column($referenceValues, 'reference_value_id');
                    if (!in_array($goodAttr->value_int, $referenceValuesIds)) {
                        $response->addError($attr['slug'], 'Attribute ' . $attr['slug'] . ' is required.');
                    }
                    break;
                case EAttr::ATTR_TYPE_TABLE:
                    $table = getenv('DB_TABLE_PREFIX') . $attr['table_name'];
                    if (DbHelper::tableExists($table) === false) {
                        $response->addError($attr['slug'], 'Table ' . $table . ' is required.');
                    }
                    $primaryKey = DbHelper::getColumnNameByPrimaryKey($table);
                    $valueInt = $goodAttr->value_int;
                    $query = "SELECT name FROM {$table} WHERE {$primaryKey} = {$valueInt};";
                    $value = $this->mainModel->connection()->executeQuery($query)->fetchColumn();

                    if ($value === false) {
                        $response->addError($attr['slug'], 'Attribute ' . $attr['slug'] . ' is required.');
                    }
                    break;
                case EAttr::ATTR_TYPE_STRING:
                case EAttr::ATTR_TYPE_TEXT:
                case EAttr::ATTR_TYPE_JSON:
                case EAttr::ATTR_TYPE_FLOAT:
                    if (!isset($goodAttr->value_string) || trim($goodAttr->value_string) === '') {
                        $response->addError($attr['slug'], 'Attribute ' . $attr['slug'] . ' is required.');
                    }
                    break;
            }
        }
    }
}