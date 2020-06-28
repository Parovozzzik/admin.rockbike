<?php

namespace App\Controllers;

use App\Models\Category;
use App\Models\Entities\ECategory;
use App\Models\Entities\EGood;
use App\Models\Entities\EGoodAttr;
use App\Models\Entities\EGoodCategory;
use App\Models\Entities\Responses\Response;
use App\Models\Entities\Responses\ResponseMessage;
use App\Models\Good;
use App\Models\GoodAttr;
use App\Models\GoodCategory;

/**
 * Class GoodsCategoriesController
 * @package App\Controllers
 */
class GoodsCategoriesController extends Controller
{
    /** @var GoodCategory */
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
        $this->mainModel = GoodCategory::getModel(EGoodCategory::class);
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'index' => !$this->isGuest,
            'view' => !$this->isGuest,
            'create' => !$this->isGuest,
            'delete' => !$this->isGuest,
        ];
    }

    /**
     * @throws \App\Settings\Exceptions\DatabaseException
     * @throws \Twig\Error\Error
     */
    public function index()
    {
        $page = $this->request['page'] ?? 1;
        $goods = $this->mainModel->getList($page);

        $response = new Response();
        $response->setView('goodscategories.index');
        $response->setModels($goods['data']);

        $this->render($response, ['pager' => $goods['pager']]);
    }

    /**
     * @throws \App\Settings\Exceptions\DatabaseException
     * @throws \Twig\Error\Error
     */
    public function view()
    {
        $id = $this->request['id'];

        $goodCategory = $this->mainModel->getByIdWithRelations($id);
        $response = new Response();

        if ($goodCategory instanceof EGoodCategory) {
            $response->setModel($goodCategory);
        } else {
            $message = new ResponseMessage('Связи с указанным идентификатором не существует',
                ResponseMessage::STATUS_ERROR,
                ResponseMessage::ICON_ERROR);

            $this->redirect('/goods-categories', [$message]);
        }

        $response->setView('goodscategories.view');

        $this->render($response);
    }

    /**
     * @throws \App\Settings\Exceptions\DatabaseException
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Twig\Error\Error
     */
    public function create()
    {
        $response = new Response();

        $goodCategory = new EGoodCategory();
        $response->setModel($goodCategory);

        if (isset($_POST['good_category'])) {
            $request = $_POST['good_category'];
            $this->validate($goodCategory, $request, $response);
            if ($goodCategory->hasErrors() === false && $response->hasErrors() === false) {
                $this->save($goodCategory, $response);
            }
        } else {
            $request = $goodCategory->toArray();
        }

        if (isset($request['category_id']) && trim($request['category_id']) !== '') {
            $categoriesModel = Category::getModel(ECategory::class);
            $category = $categoriesModel->select('category_id, name')
                ->where(['category_id' => $request['category_id']])
                ->toArray('category_id', 'name');
        }
        if (isset($request['good_id']) && trim($request['good_id']) !== '') {
            $goodModel = Good::getModel(EGood::class);
            $good = $goodModel->select('good_id, name')
                ->where(['good_id' => $request['good_id']])
                ->toArray('good_id', 'name');
        }

        $response->setView('goodscategories.create');
        $this->render($response,
            [
                'request' => $request,
                'category' => $category ?? [],
                'good' => $good ?? [],
            ]
        );
    }

    /**
     * Deletion of AttrCategory
     *
     * @throws \App\Settings\Exceptions\DatabaseException
     */
    public function delete()
    {
        $id = $this->request['id'];
        /** @var EGoodCategory $goodCategory */
        $goodCategory = $this->mainModel->get($id);

        if ($goodCategory instanceof EGoodCategory) {
            if ((bool)$goodCategory->is_main === true) {
                $goodAttrModel = GoodAttr::getModel(EGoodAttr::class);
                $goodExists = $goodAttrModel->where(['good_id' => $goodCategory->good_id])->count() > 0;
                if ($goodExists === true) {
                    $message = new ResponseMessage(
                        'Ошибка удаления записи. Имеются связанные данные',
                        ResponseMessage::STATUS_ERROR,
                        ResponseMessage::ICON_ERROR);
                }
            } else {
                $this->mainModel->delete(['good_category_id' => $goodCategory->good_category_id]);
                $message = new ResponseMessage(
                    "Связь #{$goodCategory->good_category_id} успешно удалена",
                    ResponseMessage::STATUS_SUCCESS,
                    ResponseMessage::ICON_SUCCESS);
            }
        } else {
            $message = new ResponseMessage(
                'Связи с указанным идентификатором не существует',
                ResponseMessage::STATUS_ERROR,
                ResponseMessage::ICON_ERROR);
        }

        $this->redirect('/goods-categories', [$message]);
    }

    /**
     * @param EGoodCategory $goodCategory
     * @param Response $response
     */
    protected function save(EGoodCategory $goodCategory, Response &$response): void
    {
        try {
            $result = $this->mainModel->save($goodCategory);

            if ((bool)$result === true) {
                $message = new ResponseMessage(
                    'Запись успешно сохранена!',
                    ResponseMessage::STATUS_SUCCESS,
                    ResponseMessage::ICON_SUCCESS
                );
                $this->redirect('/goods-categories/view/' . $goodCategory->good_category_id, [$message]);
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
     * @param EGoodCategory $goodCategory
     * @param array $request
     * @param Response $response
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function validate(EGoodCategory $goodCategory, array &$request, Response $response): void
    {
        $goodCategory->data($request);

        if ($this->mainModel->validate($goodCategory) === false) {
            foreach ($goodCategory->errors() as $key => $errors) {
                foreach ($errors as $error) {
                    $response->addError($key, $error);
                }
            }
        }

        if ($response->hasErrors() === false && $goodCategory->isNew()) {
            $isExists = $this->mainModel->isExists($goodCategory->good_id, $goodCategory->category_id);
            if ($isExists === true) {
                $response->addError('good_id', 'Данная связь уже умеется');
                $response->addError('category_id', 'Данная связь уже умеется');
            } elseif((bool)$goodCategory->is_main === true) {
                $isMainExists = $this->mainModel->isMainExists($goodCategory->good_id);
                if ($isMainExists === true) {
                    $response->addError('is_main', 'Данные товар уже имеет основную категорию');
                }
            }
        }
    }
}