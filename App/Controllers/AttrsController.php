<?php

namespace App\Controllers;

use App\Models\Attr;
use App\Models\AttrCategory;
use App\Models\Category;
use App\Models\Entities\EAttr;
use App\Models\Entities\EAttrCategory;
use App\Models\Entities\ECategory;
use App\Models\Entities\EGoodAttr;
use App\Models\Entities\EReference;
use App\Models\Entities\Responses\Response;
use App\Models\Entities\Responses\ResponseMessage;
use App\Models\GoodAttr;
use App\Models\Reference;

/**
 * Class AttrsController
 * @package App\Controllers
 */
class AttrsController extends Controller
{
    /** @var Attr */
    protected $mainModel;

    /**
     * AttrsController constructor.
     * @throws \App\Settings\Exceptions\DatabaseException
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Spot\Exception
     */
    public function __construct()
    {
        parent::__construct();
        $this->mainModel = Attr::getModel(EAttr::class);
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
        $attrs = $this->mainModel->getList($page, 10);

        $response = new Response();
        $response->setView('attrs.index');
        $response->setModels($attrs['data']);

        $this->render($response, ['pager' => $attrs['pager']]);
    }

    /**
     * @throws \App\Settings\Exceptions\DatabaseException
     * @throws \Twig\Error\Error
     */
    public function view()
    {
        $id = $this->request['id'];

        $attr = $this->mainModel->getByIdWithReference($id);
        $response = new Response();

        if ($attr instanceof EAttr) {
            $response->setModel($attr);
        } else {
            $message = new ResponseMessage('Атрибута с указанным идентификатором не существует',
                ResponseMessage::STATUS_ERROR,
                ResponseMessage::ICON_ERROR);

            $this->redirect('/attrs', [$message]);
        }
        $response->setView('attrs.view');

        /** @var Category $categoriesModel */
        $categoriesModel = Category::getModel(ECategory::class);
        $categories = $categoriesModel->getByAttrId($attr->attr_id);

        $this->render($response, [
            'categories' => $categories,
        ]);
    }

    /**
     * @throws \App\Settings\Exceptions\DatabaseException
     * @throws \Twig\Error\Error
     */
    public function create()
    {
        $response = new Response();

        $attr = new EAttr();
        $response->setModel($attr);

        if (isset($_POST['attr'])) {
            $request = $_POST['attr'];
            $this->validate($attr, $request, $response);
            if ($attr->hasErrors() === false && $response->hasErrors() === false) {
                $this->save($attr, $response);
            }
        } else {
            $request = $attr->toArray();
        }

        /** special processing for select input */
        $types = [];
        foreach (EAttr::ATTR_TYPES as $type) {
            $types[$type] = $type;
        }

        if (isset($request['reference_id']) && trim($request['reference_id']) !== '') {
            $referenceModel = Reference::getModel(EReference::class);
            $reference = $referenceModel->select('reference_id, name')
                ->where(['reference_id' => $request['reference_id']])
                ->toArray('reference_id', 'name');
        }

        $response->setView('attrs.create');
        $this->render($response,
            [
                'types' => $types,
                'request' => $request,
                'reference' => $reference ?? [],
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
        $attr = $this->mainModel->get($id);

        $response = new Response();

        if ($attr instanceof EAttr) {
            $response->setModel($attr);

            if (isset($_POST['attr'])) {
                $request = $_POST['attr'];
                $this->validate($attr, $request, $response);
                if ($attr->hasErrors() === false && $response->hasErrors() === false) {
                    $this->save($attr, $response);
                }
            } else {
                $request = $attr->toArray();
            }
        } else {
            $message = new ResponseMessage('Атрибута с указанным идентификатором не существует.',
                ResponseMessage::STATUS_ERROR,
                ResponseMessage::ICON_ERROR);

            $this->redirect('/attrs', [$message]);
        }

        /** special processing for select input */
        $types = [];
        foreach (EAttr::ATTR_TYPES as $type) {
            $types[$type] = $type;
        }

        /** @var Category $categoriesModel */
        $categoriesModel = Category::getModel(ECategory::class);
        $categories = $categoriesModel->getByAttrId($attr->attr_id);

        if (isset($request['reference_id']) && trim($request['reference_id']) !== '') {
            $referenceModel = Reference::getModel(EReference::class);
            $reference = $referenceModel->select('reference_id, name')
                ->where(['reference_id' => $request['reference_id']])
                ->toArray('reference_id', 'name');
        }

        $response->setView('attrs.create');
        $this->render($response,
            [
                'types' => $types,
                'categories' => $categories,
                'reference' => $reference ?? [],
                'request' => $request,
            ]
        );
    }

    /**
     * @throws \App\Settings\Exceptions\DatabaseException
     */
    public function delete()
    {
        $id = $this->request['id'];
        $attr = $this->mainModel->get($id);

        if ($attr instanceof EAttr) {
            $attrCategoryModel = AttrCategory::getModel(EAttrCategory::class);
            $categoryExists = $attrCategoryModel->where(['attr_id' => $attr->attr_id])->count() > 0;

            $goodAttrModel = GoodAttr::getModel(EGoodAttr::class);
            $goodExists = $goodAttrModel->where(['attr_id' => $attr->attr_id])->count() > 0;

            if ($categoryExists === true || $goodExists === true) {
                $message = new ResponseMessage(
                    'Ошибка удаления атрибута. Атрибут имеет связанные данные',
                    ResponseMessage::STATUS_ERROR,
                    ResponseMessage::ICON_ERROR);
            } else {
                $this->mainModel->delete(['attr_id' => $attr->attr_id]);
                $message = new ResponseMessage(
                    "Атрибут # {$attr->attr_id} успешно удален",
                    ResponseMessage::STATUS_SUCCESS,
                    ResponseMessage::ICON_SUCCESS);
            }
        } else {
            $message = new ResponseMessage(
                'Атрибут с указанным идентификатором не существует',
                ResponseMessage::STATUS_ERROR,
                ResponseMessage::ICON_ERROR);
        }

        $this->redirect('/attrs', [$message]);
    }

    /**
     * @param EAttr $attr
     * @param array $request
     * @param Response $response
     */
    protected function save(EAttr $attr, Response &$response): void
    {
        try {
            $result = $this->mainModel->save($attr);
            if ((bool)$result === true) {
                $message = new ResponseMessage(
                    'Атрибут успешно сохранен!',
                    ResponseMessage::STATUS_SUCCESS,
                    ResponseMessage::ICON_SUCCESS
                );
                $this->redirect('/attrs/view/' . $attr->attr_id, [$message]);
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
     * @param EAttr $attr
     * @param array $request
     * @param Response $response
     */
    protected function validate(EAttr $attr, array &$request, Response &$response): void
    {
        if (!in_array($request['type'], EAttr::ATTR_TYPES)) {
            $attrTypesString = implode(', ', EAttr::ATTR_TYPES);
            $response->addError('type', "Поле type должно быть одним из значений: {$attrTypesString}!");
        } else {
            switch ($request['type']) {
                case EAttr::ATTR_TYPE_REF:
                    if (!isset($request['reference_id']) || trim($request['reference_id']) === '') {
                        $response->addError('reference_id', 'Поле reference_id обязательно для заполнения!');
                    }
                    break;
                case EAttr::ATTR_TYPE_TABLE:
                    if (!isset($request['table_name']) || trim($request['table_name']) === '') {
                        $response->addError('table_name', 'Поле table_name обязательно для заполнения!');
                    }
                    break;
            }
            if ($request['type'] !== EAttr::ATTR_TYPE_REF) {
                $request['reference_id'] = null;
            }
        }
        if (isset($request['round']) && trim($request['round'] === '')) {
            $request['round'] = null;
        }

        $attr->data($request);

        if ($this->mainModel->validate($attr) === false) {
            foreach ($attr->errors() as $key => $errors) {
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

        $attrs = $this->mainModel->getByText($text);

        return $attrs;
    }
}