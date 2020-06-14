<?php

namespace App\Controllers;

use App\Models\Attr;
use App\Models\AttrCategory;
use App\Models\Category;
use App\Models\Entities\EAttr;
use App\Models\Entities\EAttrCategory;
use App\Models\Entities\ECategory;
use App\Models\Entities\EGoodCategory;
use App\Models\Entities\Responses\Response;
use App\Models\Entities\Responses\ResponseMessage;
use App\Models\GoodCategory;

/**
 * Class CategoriesController
 * @package App\Controllers
 */
class CategoriesController extends Controller
{
    /** @var Category */
    protected $mainModel;

    /**
     * CategoriesController constructor.
     * @throws \App\Settings\Exceptions\DatabaseException
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Spot\Exception
     */
    public function __construct()
    {
        parent::__construct();
        $this->mainModel = Category::getModel(ECategory::class);
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
            'ajaxList' => !$this->isGuest,
        ];
    }

    /**
     * @throws \App\Settings\Exceptions\DatabaseException
     * @throws \Twig\Error\Error
     */
    public function index()
    {
        $page = $this->request['page'] ?? 1;
        $categories = $this->mainModel->getList($page, 10);

        $response = new Response();
        $response->setView('categories.index');
        $response->setModels($categories['data']);

        $this->render($response, ['pager' => $categories['pager']]);
    }

    /**
     * @throws \App\Settings\Exceptions\DatabaseException
     * @throws \Twig\Error\Error
     */
    public function view()
    {
        $id = $this->request['id'];

        $category = $this->mainModel->getByIdWithParent($id);
        $response = new Response();

        if ($category instanceof ECategory) {
            $response->setModel($category);
        } else {
            $message = new ResponseMessage('Атрибута с указанным идентификатором не существует.',
                ResponseMessage::STATUS_ERROR,
                ResponseMessage::ICON_ERROR);

            $this->redirect('/categories', [$message]);
        }

        /** @var Category $categoriesModel */
        $categoriesModel = Category::getModel(ECategory::class);
        $categories = $categoriesModel->getByAttrId($category->category_id);

        /** @var Attr $attrsModel */
        $attrsModel = Attr::getModel(EAttr::class);
        $attrs = $attrsModel->getByCategoryId($category->category_id);

        $response->setView('categories.view');
        $this->render($response, [
            'categories' => $categories,
            'attrs' => $attrs,
        ]);
    }

    /**
     * @throws \App\Settings\Exceptions\DatabaseException
     * @throws \Twig\Error\Error
     */
    public function create()
    {
        $response = new Response();

        $category = new ECategory();
        $response->setModel($category);

        if (isset($_POST['category'])) {
            $request = $_POST['category'];
            $this->validate($category, $request, $response);
            if ($category->hasErrors() === false && $response->hasErrors() === false) {
                $this->save($category, $response);
            }
        } else {
            $request = $category->toArray();
        }

        if (isset($request['parent_category_id']) && trim($request['parent_category_id']) !== '') {
            $categoryModel = Category::getModel(ECategory::class);
            $category = $categoryModel->select('category_id, name')
                ->where(['category_id' => $request['parent_category_id']])
                ->toArray('category_id', 'name');
        }

        $response->setView('categories.create');
        $this->render($response,
            [
                'request' => $request,
                'parent_category' => $category ?? [],
            ]
        );
    }

    /**
     * @throws \App\Settings\Exceptions\DatabaseException
     * @throws \Twig\Error\Error
     */
    public function edit()
    {
        $id = $this->request['id'];
        $category = $this->mainModel->get($id);

        $response = new Response();

        if ($category instanceof ECategory) {
            $response->setModel($category);

            if (isset($_POST['category'])) {
                $request = $_POST['category'];
                $this->validate($category, $request, $response);
                if ($category->hasErrors() === false && $response->hasErrors() === false) {
                    $this->save($category, $response);
                }
            } else {
                $request = $category->toArray();
            }
        } else {
            $message = new ResponseMessage('Категории с указанным идентификатором не существует.',
                ResponseMessage::STATUS_ERROR,
                ResponseMessage::ICON_ERROR);

            $this->redirect('/categories', [$message]);
        }

        /** @var Attr $attrsModel */
        $attrsModel = Attr::getModel(EAttr::class);
        $attrs = $attrsModel->getByCategoryId($category->category_id);

        if (isset($request['parent_category_id']) && trim($request['parent_category_id']) !== '') {
            $categoryModel = Category::getModel(ECategory::class);
            $category = $categoryModel->select('category_id, name')
                ->where(['category_id' => $request['parent_category_id']])
                ->toArray('category_id', 'name');
        }

        $response->setView('categories.create');
        $this->render($response,
            [
                'attrs' => $attrs,
                'request' => $request,
                'parent_category' => $category ?? [],
            ]
        );
    }

    /**
     * @throws \App\Settings\Exceptions\DatabaseException
     */
    public function delete()
    {
        $id = $this->request['id'];
        $category = $this->mainModel->get($id);

        if ($category instanceof ECategory) {
            $attrCategoryModel = AttrCategory::getModel(EAttrCategory::class);
            $attrExists = $attrCategoryModel->where(['category_id' => $category->category_id])->count() > 0;

            $goodCategoryModel = GoodCategory::getModel(EGoodCategory::class);
            $goodExists = $goodCategoryModel->where(['category_id' => $category->category_id])->count() > 0;

            $childCategoriesExists = $this->mainModel->where(['parent_category_id' => $category->category_id])->count() > 0;

            if ($attrExists === true || $goodExists === true || $childCategoriesExists) {
                $message = new ResponseMessage(
                    'Ошибка удаления категории. Категория имеет связанные данные',
                    ResponseMessage::STATUS_ERROR,
                    ResponseMessage::ICON_ERROR);
            } else {
                $this->mainModel->delete(['category_id' => $category->category_id]);
                $message = new ResponseMessage(
                    "Категория #{$category->category_id} успешно удалена",
                    ResponseMessage::STATUS_SUCCESS,
                    ResponseMessage::ICON_SUCCESS);
            }
        } else {
            $message = new ResponseMessage(
                'Категории с указанным идентификатором не существует',
                ResponseMessage::STATUS_ERROR,
                ResponseMessage::ICON_ERROR);
        }

        $this->redirect('/categories', [$message]);
    }

    /**
     * @param ECategory $category
     * @param Response $response
     */
    protected function save(ECategory $category, Response &$response): void
    {
        try {
            $result = $this->mainModel->save($category);

            if ((bool)$result === true) {
                $message = new ResponseMessage(
                    'Категория успешно сохранен!',
                    ResponseMessage::STATUS_SUCCESS,
                    ResponseMessage::ICON_SUCCESS
                );
                $this->redirect('/categories/view/' . $category->category_id, [$message]);
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
    }

    /**
     * @param ECategory $category
     * @param array $request
     * @param Response $response
     */
    protected function validate(ECategory $category, array &$request, Response &$response): void
    {
        if (isset($request['parent_category_id']) && trim($request['parent_category_id'] === '')) {
            $request['parent_category_id'] = null;
        }

        $category->data($request);

        if ($this->mainModel->validate($category) === false) {
            foreach ($category->errors() as $key => $errors) {
                foreach ($errors as $error) {
                    $response->addError($key, $error);
                }
            }
        }
    }

    /**
     * @return array
     * @throws \App\Settings\Exceptions\DatabaseException
     */
    public function ajaxList()
    {
        $text = $this->request['text'];
        if (!isset($this->request['value-exclude']) || trim($this->request['value-exclude']) === '') {
            $valueExclude = null;
        } else {
            $valueExclude = (int)$this->request['value-exclude'];
        }

        $categories = $this->mainModel->getByText($text, $valueExclude);

        return $categories;
    }
}