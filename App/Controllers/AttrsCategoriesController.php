<?php

namespace App\Controllers;

use App\Models\Attr;
use App\Models\AttrCategory;
use App\Models\Category;
use App\Models\Entities\EAttr;
use App\Models\Entities\EAttrCategory;
use App\Models\Entities\ECategory;
use App\Models\Entities\Responses\Response;
use App\Models\Entities\Responses\ResponseMessage;

/**
 * Class AttrsCategoriesController
 * @package App\Controllers
 */
class AttrsCategoriesController extends Controller
{
    /** @var AttrCategory */
    protected $mainModel;

    /**
     * AttrsCategoriesController constructor.
     * @throws \App\Settings\Exceptions\DatabaseException
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Spot\Exception
     */
    public function __construct()
    {
        parent::__construct();
        $this->mainModel = AttrCategory::getModel(EAttrCategory::class);
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
        $attrsCategories = $this->mainModel->getList($page, 10);

        $response = new Response();
        $response->setView('attrscategories.index');
        $response->setModels($attrsCategories['data']);

        $this->render($response, ['pager' => $attrsCategories['pager']]);
    }

    /**
     * @throws \App\Settings\Exceptions\DatabaseException
     * @throws \Twig\Error\Error
     */
    public function view()
    {
        $id = $this->request['id'];

        $attr = $this->mainModel->getByIdWithRelations($id);
        $response = new Response();

        if ($attr instanceof EAttrCategory) {
            $response->setModel($attr);
        } else {
            $message = new ResponseMessage('Записи с указанным идентификатором не существует.',
                ResponseMessage::STATUS_ERROR,
                ResponseMessage::ICON_ERROR);

            $this->redirect('/attrs-categories', [$message]);
        }

        $response->setView('attrscategories.view');
        $this->render($response);
    }

    /**
     * @throws \App\Settings\Exceptions\DatabaseException
     * @throws \Twig\Error\Error
     */
    public function create()
    {
        $response = new Response();

        $attrCategory = new EAttrCategory();
        $response->setModel($attrCategory);

        if (isset($_POST['attr_category'])) {
            $request = $_POST['attr_category'];
            $this->validate($attrCategory, $request, $response);
            if ($attrCategory->hasErrors() === false && $response->hasErrors() === false) {
                $this->save($attrCategory, $response);
            }
        } else {
            $request = $attrCategory->toArray();
        }

        if (isset($request['category_id']) && trim($request['category_id']) !== '') {
            $categoriesModel = Category::getModel(ECategory::class);
            $category = $categoriesModel->select('category_id, name')
                ->where(['category_id' => $request['category_id']])
                ->toArray('category_id', 'name');
        }
        if (isset($request['attr_id']) && trim($request['attr_id']) !== '') {
            $attrModel = Attr::getModel(EAttr::class);
            $attr = $attrModel->select('attr_id, name')
                ->where(['attr_id' => $request['attr_id']])
                ->toArray('attr_id', 'name');
        }

        $response->setView('attrscategories.create');
        $this->render($response,
            [
                'request' => $request,
                'category' => $category ?? [],
                'attr' => $attr ?? [],
            ]
        );
    }

    /**
     * Deletion of AttrCategory
     */
    public function delete()
    {
        $id = $this->request['id'];
        $attrCategory = $this->mainModel->get($id);

        if ($attrCategory instanceof EAttrCategory) {
            $this->mainModel->delete(['attr_category_id' => $attrCategory->attr_category_id]);
            $message = new ResponseMessage(
                "Связь #{$attrCategory->attr_category_id} успешно удалена",
                ResponseMessage::STATUS_SUCCESS,
                ResponseMessage::ICON_SUCCESS);
        } else {
            $message = new ResponseMessage(
                'Связи с указанным идентификатором не существует',
                ResponseMessage::STATUS_ERROR,
                ResponseMessage::ICON_ERROR);
        }

        $this->redirect('/attrs-categories', [$message]);
    }

    /**
     * @param EAttrCategory $attrCategory
     * @param Response $response
     */
    protected function save(EAttrCategory $attrCategory, Response &$response): void
    {
        try {
            $result = $this->mainModel->save($attrCategory);

            if ((bool)$result === true) {
                $message = new ResponseMessage(
                    'Запись успешно сохранена!',
                    ResponseMessage::STATUS_SUCCESS,
                    ResponseMessage::ICON_SUCCESS
                );
                $this->redirect('/attrs-categories/view/' . $attrCategory->attr_category_id, [$message]);
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
     * @param EAttrCategory $attrCategory
     * @param array $request
     * @param Response $response
     */
    protected function validate(EAttrCategory $attrCategory, array &$request, Response &$response): void
    {
        $attrCategory->data($request);

        if ($this->mainModel->validate($attrCategory) === false) {
            foreach ($attrCategory->errors() as $key => $errors) {
                foreach ($errors as $error) {
                    $response->addError($key, $error);
                }
            }
        }
    }
}