<?php

namespace App\Controllers;

use App\Models\Attr;
use App\Models\Entities\EAttr;
use App\Models\Entities\EReference;
use App\Models\Entities\EReferenceValue;
use App\Models\Entities\Responses\Response;
use App\Models\Entities\Responses\ResponseMessage;
use App\Models\Reference;
use App\Models\ReferenceValue;

/**
 * Class ReferencesController
 * @package App\Controllers
 */
class ReferencesController extends Controller
{
    /** @var Reference */
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
        $this->mainModel = Reference::getModel(EReference::class);
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
        $references = $this->mainModel->getList($page, 10);

        $response = new Response();
        $response->setModels($references['data']);

        $response->setView('references.index');
        $this->render($response, ['pager' => $references['pager']]);
    }

    /**
     * @throws \App\Settings\Exceptions\DatabaseException
     * @throws \Twig\Error\Error
     */
    public function view()
    {
        $id = $this->request['id'];

        $reference = $this->mainModel->getByIdWithParent($id);
        $response = new Response();

        if ($reference instanceof EReference) {
            $response->setModel($reference);
        } else {
            $message = new ResponseMessage('Справочника с указанным идентификатором не существует',
                ResponseMessage::STATUS_ERROR,
                ResponseMessage::ICON_ERROR);

            $this->redirect('/references', [$message]);
        }

        $types = EReference::REF_TYPES;

        /** @var ReferenceValue $referencesValuesModel */
        $referencesValuesModel = ReferenceValue::getModel(EReferenceValue::class);
        $values = $referencesValuesModel->getByReferenceId($reference->reference_id);

        $childReferences = $this->mainModel->where(['parent_reference_id' => $reference->reference_id]);

        $response->setView('references.view');
        $this->render($response, [
            'types' => $types,
            'values' => $values,
            'childs' => $childReferences,
        ]);
    }

    /**
     * @throws \App\Settings\Exceptions\DatabaseException
     * @throws \Twig\Error\Error
     */
    public function create()
    {
        $response = new Response();

        $reference = new EReference();
        $response->setModel($reference);

        if (isset($_POST['reference'])) {
            $request = $_POST['reference'];
            $this->validate($reference, $request, $response);
            if ($reference->hasErrors() === false) {
                $this->save($reference, $response);
            }
        } else {
            $request = $reference->toArray();
        }

        /** special processing for select input */
        $types = [];
        foreach (EReference::REF_TYPES as $type) {
            $types[$type] = $type;
        }

        if (isset($request['parent_reference_id']) && trim($request['parent_reference_id']) !== '') {
            $referenceModel = Reference::getModel(EReference::class);
            $reference = $referenceModel->select('reference_id, name')
                ->where(['reference_id' => $request['parent_reference_id']])
                ->toArray('reference_id', 'name');
        }

        $response->setView('references.create');
        $this->render($response,
            [
                'types' => $types,
                'request' => $request,
                'parent_reference' => $reference ?? [],
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
        $reference = $this->mainModel->get($id);

        $response = new Response();

        if ($reference instanceof EReference) {
            $response->setModel($reference);

            if (isset($_POST['reference'])) {
                $request = $_POST['reference'];
                $this->validate($reference, $request, $response);
                if ($response->hasErrors() === false) {
                    $this->save($reference, $response);
                }
            } else {
                $request = $reference->toArray();
            }
        } else {
            $message = new ResponseMessage('Справочника с указанным идентификатором не существует.',
                ResponseMessage::STATUS_ERROR,
                ResponseMessage::ICON_ERROR);

            $this->redirect('/references', [$message]);
        }

        /** special processing for select input */
        $types = [];
        foreach (EReference::REF_TYPES as $type) {
            $types[$type] = $type;
        }

        /** @var ReferenceValue $referencesValuesModel */
        $referencesValuesModel = ReferenceValue::getModel(EReferenceValue::class);
        $values = $referencesValuesModel->getByReferenceId($reference->reference_id);

        if (isset($request['parent_reference_id']) && trim($request['parent_reference_id']) !== '') {
            $referenceModel = Reference::getModel(EReference::class);
            $reference = $referenceModel->select('reference_id, name')
                ->where(['reference_id' => $request['parent_reference_id']])
                ->toArray('reference_id', 'name');
        }

        $response->setView('references.create');
        $this->render($response,
            [
                'types' => $types,
                'request' => $request,
                'values' => $values,
                'parent_reference' => $reference ?? [],
            ]
        );
    }

    /**
     * @throws \App\Settings\Exceptions\DatabaseException
     */
    public function delete()
    {
        $id = $this->request['id'];
        $reference = $this->mainModel->get($id);

        if ($reference instanceof EReference) {
            $referenceValueModel = ReferenceValue::getModel(EReferenceValue::class);
            $referenceExists = $referenceValueModel->where(['reference_id' => $reference->reference_id])->count() > 0;

            $attrModel = Attr::getModel(EAttr::class);
            $attrExists = $attrModel->where(['reference_slug' => $reference->slug])->count() > 0;

            if ($referenceExists === true || $attrExists === true) {
                $message = new ResponseMessage(
                    'Ошибка удаления справочника. Справочник имеет связанные данные',
                    ResponseMessage::STATUS_ERROR,
                    ResponseMessage::ICON_ERROR);
            } else {
                $this->mainModel->delete(['reference_id' => $reference->reference_id]);
                $message = new ResponseMessage(
                    "Справочник # {$reference->reference_id} успешно удален",
                    ResponseMessage::STATUS_SUCCESS,
                    ResponseMessage::ICON_SUCCESS);
            }
        } else {
            $message = new ResponseMessage(
                'Справочник с указанным идентификатором не существует',
                ResponseMessage::STATUS_ERROR,
                ResponseMessage::ICON_ERROR);
        }

        $this->redirect('/references', [$message]);
    }

    /**
     * @param EReference $reference
     * @param array $request
     * @param Response $response
     */
    protected function save(EReference $reference, Response &$response): void
    {
        try {
            $result = $this->mainModel->save($reference);
            if ((bool)$result === true) {
                $message = new ResponseMessage(
                    'Справочник успешно сохранен!',
                    ResponseMessage::STATUS_SUCCESS,
                    ResponseMessage::ICON_SUCCESS
                );
                $this->redirect('/references/view/' . $reference->reference_id, [$message]);
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
     * @param EReference $reference
     * @param array $request
     * @param Response $response
     */
    protected function validate(EReference $reference, array &$request, Response &$response): void
    {
        if (!in_array($request['type'], EReference::REF_TYPES)) {
            $referenceTypesString = implode(', ', EReference::REF_TYPES);
            $response->addError('type', "Поле type должно быть одним из значений: {$referenceTypesString}!");
        } else {
            if ($request['type'] === EReference::REF_TYPE_REF) {
                if (!isset($request['parent_reference_id']) || trim($request['parent_reference_id']) === '') {
                    $response->addError(
                        'parent_reference_id',
                        "В случае выбора типа справочника \"{$request['type']}\" " .
                        "поле parent_reference_id обязательно для заполнения.");
                } else {
                    $filter = ['reference_id' => $request['parent_reference_id']];
                    if (!$reference->isNew()) {
                        $filter = array_merge($filter, ['reference_id :not' => $reference->reference_id]);
                    }
                    $parentReference = $this->mainModel->where($filter)->first();
                    if (!$parentReference instanceof EReference) {
                        $response->addError(
                            'parent_reference_id',
                            "В соответствии с выбранным типом значений \"{$reference->type}\" для справочника " .
                            "поле parent_reference_id должно быть идектификатором записи родительского справочника.");
                    }
                }
            }
        }
        if (isset($request['parent_reference_id']) && trim($request['parent_reference_id'] === '')) {
            $request['parent_reference_id'] = null;
        }

        $reference->data($request);
        if ($this->mainModel->validate($reference) === false) {
            foreach ($reference->errors() as $key => $errors) {
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

        $references = $this->mainModel->getByText($text);

        return $references;
    }
}