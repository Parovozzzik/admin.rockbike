<?php

namespace App\Controllers;

use App\Models\Attr;
use App\Models\Category;
use App\Models\Entities\EAttr;
use App\Models\Entities\ECategory;
use App\Models\Entities\EGood;
use App\Models\Entities\EReference;
use App\Models\Entities\Responses\Response;
use App\Models\Entities\Responses\ResponseMessage;
use App\Models\Good;
use App\Models\Reference;

/**
 * Class GoodsController
 * @package App\Controllers
 */
class GoodsController extends Controller
{
    /** @var Good */
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
        $this->mainModel = Good::getModel(EGood::class);
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
     * @throws \Twig\Error\Error
     */
    public function index()
    {
        $page = $this->request['page'] ?? 1;
        $goods = $this->mainModel->getList($page);

        $response = new Response();
        $response->setView('goods.index');
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

        $good = $this->mainModel->get($id);
        $response = new Response();

        if ($good instanceof EGood) {
            $response->setModel($good);
        } else {
            $response->setErrors(['Товара с текущим идентификатором не существует!']);
        }

        /** @var Category $categoriesModel */
        $categoriesModel = Category::getModel(ECategory::class);
        $categories = $categoriesModel->getByGoodId($good->good_id);

        /** @var Attr $attrsModel */
        $attrsModel = Attr::getModel(EAttr::class);
        $attrs = $attrsModel->getByGoodId($good->good_id);

        $referencesSlugs = array_filter(array_column($attrs, 'r_slug'), function ($item) {
            return $item !== null;
        });

        /** @var Reference $referencesModel */
        $referencesModel = Reference::getModel(EReference::class);
        $referencesWithValues = $referencesModel->getBySlugsWithValues($referencesSlugs);

        $response->setView('goods.view');

        $this->render($response, [
            'categories' => $categories,
            'attrs' => $attrs,
            'references' => $referencesWithValues
        ]);
    }

    /**
     * @throws \App\Settings\Exceptions\DatabaseException
     * @throws \Spot\Exception
     * @throws \Twig\Error\Error
     */
    public function edit()
    {
        $id = $this->request['id'];
        $good = $this->mainModel->get($id);

        $response = new Response();
        if ($good instanceof EGood) {
            $response->setModel($good);

            if (isset($_POST['good'])) {
                $request = $_POST['good'];
                $this->validate($good, $request, $response);
                if ($good->hasErrors() === false && $response->hasErrors() === false) {
                    $this->save($good, $response);
                }
            } else {
                $request = $good->toArray();
            }
        } else {
            $message = new ResponseMessage('Атрибута с указанным идентификатором не существует.',
                ResponseMessage::STATUS_ERROR,
                ResponseMessage::ICON_ERROR);

            $this->redirect('/attrs', [$message]);
        }

        /** @var Category $categoriesModel */
        $categoriesModel = Category::getModel(ECategory::class);
        $categories = $categoriesModel->getByGoodId($good->good_id);

        $response->setView('goods.create');

        $this->render($response, [
            'request' => $request,
            'categories' => $categories
        ]);
    }

    /**
     * @throws \Twig\Error\Error
     */
    public function create()
    {
        $response = new Response();

        $good = new EGood();
        $response->setModel($good);

        if (isset($_POST['good'])) {
            $request = $_POST['good'];
            $this->validate($good, $request, $response);
            if ($good->hasErrors() === false && $response->hasErrors() === false) {
                $this->save($good, $response);
            }
        } else {
            $request = $good->toArray();
        }

        $response->setView('goods.create');

        $this->render($response,
            [
                'request' => $request,
            ]
        );
    }

    /**
     * @param EGood $good
     * @param Response $response
     */
    protected function save(EGood $good, Response $response): void
    {
        try {
            $result = $this->mainModel->save($good);
            if ((bool)$result === true) {
                $message = new ResponseMessage(
                    'Товар успешно сохранен!',
                    ResponseMessage::STATUS_SUCCESS,
                    ResponseMessage::ICON_SUCCESS
                );
                $this->redirect('/goods/view/' . $good->good_id, [$message]);
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
     * @param EGood $good
     * @param array $request
     * @param Response $response
     */
    protected function validate(EGood $good, array &$request, Response $response): void
    {
        if (isset($request['price']) && trim($request['price'] === '')) {
            $request['price'] = null;
        }

        $good->data($request);

        if ($this->mainModel->validate($good) === false) {
            foreach ($good->errors() as $key => $errors) {
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

        $goods = $this->mainModel->getByText($text);

        return $goods;
    }
}