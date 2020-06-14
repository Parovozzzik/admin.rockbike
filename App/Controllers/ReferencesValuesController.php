<?php

namespace App\Controllers;

use App\Models\Entities\EReference;
use App\Models\Entities\EReferenceValue;
use App\Models\Entities\Responses\Response;
use App\Models\Entities\Responses\ResponseMessage;
use App\Models\Reference;
use App\Models\ReferenceValue;

/**
 * Class ReferencesValuesController
 * @package App\Controllers
 */
class ReferencesValuesController extends Controller
{
    /** @var ReferenceValue */
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
        $this->mainModel = ReferenceValue::getModel(EReferenceValue::class);
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
     * @throws \App\Settings\Exceptions\DatabaseException
     * @throws \Twig\Error\Error
     */
    public function index()
    {
        $page = $this->request['page'] ?? 1;
        $references = $this->mainModel->getList($page, 10);

        $response = new Response();
        $response->setView('referencesvalues.index');
        $response->setModels($references['data']);

        $this->render($response, ['pager' => $references['pager']]);
    }

    /**
     * @throws \App\Settings\Exceptions\DatabaseException
     * @throws \Twig\Error\Error
     */
    public function view()
    {
        $id = $this->request['id'];

        $referenceValue = $this->mainModel->getByIdWithParent($id);
        $response = new Response();

        if ($referenceValue instanceof EReferenceValue) {
            $response->setModel($referenceValue);
        } else {
            $message = new ResponseMessage(' Справочника с указанным идентификатором не существует',
                ResponseMessage::STATUS_ERROR,
                ResponseMessage::ICON_ERROR);

            $this->redirect('/references-values', [$message]);
        }
        $response->setView('referencesvalues.view');

        $this->render($response);
    }

    /**
     * @throws \App\Settings\Exceptions\DatabaseException
     * @throws \Twig\Error\Error
     */
    public function create()
    {
        $response = new Response();

        $referenceValue = new EReferenceValue();
        $response->setModel($referenceValue);

        if (isset($_POST['reference_value'])) {
            $request = $_POST['reference_value'];
            $this->validate($referenceValue, $request, $response);
            if ($referenceValue->hasErrors() === false && $response->hasErrors() === false) {
                $this->save($referenceValue, $response);
            }
        } else {
            $request = $referenceValue->toArray();
        }

        if (isset($request['reference_id']) && trim($request['reference_id']) !== '') {
            $referencesModel = Reference::getModel(EReference::class);
            $reference = $referencesModel->select('reference_id, name')
                ->where(['reference_id' => $request['reference_id']])
                ->toArray('reference_id', 'name');
        }

        $response->setView('referencesvalues.create');
        $this->render($response,
            [
                'reference' => $reference ?? [],
                'request' => $request,
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
        $referenceValue = $this->mainModel->get($id);

        $response = new Response();

        if ($referenceValue instanceof EReferenceValue) {
            $response->setModel($referenceValue);

            if (isset($_POST['reference_value'])) {
                $request = $_POST['reference_value'];
                $this->validate($referenceValue, $request, $response);
                if ($response->hasErrors() === false) {
                    $this->save($referenceValue, $response);
                }
            } else {
                $request = $referenceValue->toArray();
            }
        } else {
            $message = new ResponseMessage('Значения справочника с указанным идентификатором не существует.',
                ResponseMessage::STATUS_ERROR,
                ResponseMessage::ICON_ERROR);

            $this->redirect('/references-values', [$message]);
        }

        if (isset($request['reference_id']) && trim($request['reference_id']) !== '') {
            $referencesModel = Reference::getModel(EReference::class);
            $reference = $referencesModel->select('reference_id, name')
                ->where(['reference_id' => $request['reference_id']])
                ->toArray('reference_id', 'name');
        }

        $response->setView('referencesvalues.create');
        $this->render($response,
            [
                'request' => $request,
                'reference' => $reference ?? [],
            ]
        );
    }

    /**
     * @throws \App\Settings\Exceptions\DatabaseException
     */
    public function delete()
    {
        $id = $this->request['id'];
        $referenceValue = $this->mainModel->get($id);

        if ($referenceValue instanceof EReferenceValue) {
            if ($this->mainModel->isUsed($id) === true) {
                $message = new ResponseMessage(
                    'Ошибка удаления значения справочника. Значение справочника имеет связанные данные',
                    ResponseMessage::STATUS_ERROR,
                    ResponseMessage::ICON_ERROR);
            } else {
                $this->mainModel->delete(['reference_value_id' => $referenceValue->reference_value_id]);
                $message = new ResponseMessage(
                    "Значение справочника # {$referenceValue->reference_value_id} успешно удален",
                    ResponseMessage::STATUS_SUCCESS,
                    ResponseMessage::ICON_SUCCESS);
            }
        } else {
            $message = new ResponseMessage(
                'Значение справочника с указанным идентификатором не существует',
                ResponseMessage::STATUS_ERROR,
                ResponseMessage::ICON_ERROR);
        }

        $this->redirect('/references-values', [$message]);
    }

    /**
     * @param EReferenceValue $referenceValue
     * @param array $request
     * @param Response $response
     */
    protected function save(EReferenceValue $referenceValue, Response &$response): void
    {
        try {
            $result = $this->mainModel->save($referenceValue);
            if ((bool)$result === true) {
                $message = new ResponseMessage(
                    'Запись справочника успешно сохранена!',
                    ResponseMessage::STATUS_SUCCESS,
                    ResponseMessage::ICON_SUCCESS
                );
                $this->redirect('/references-values/view/' . $referenceValue->reference_value_id, [$message]);
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
     * @param EReferenceValue $referenceValue
     * @param array $request
     * @param Response $response
     * @throws \App\Settings\Exceptions\DatabaseException
     */
    protected function validate(EReferenceValue $referenceValue, array &$request, Response &$response): void
    {
        $referencesModel = Reference::getModel(EReference::class);
        /** @var EReference $reference */
        $reference = $referencesModel->get($request['reference_id']);
        if (!$reference instanceof EReference) {
            $response->addError('reference_id', "Справоника с ID {$request['reference_id']} не существует");
        } else {
            switch ($reference->type) {
                case EReference::REF_TYPE_INT:
                case EReference::REF_TYPE_STRING:
                case EReference::REF_TYPE_TEXT:
                    if (!isset($request['value_' . $reference->type]) || trim($request['value_' . $reference->type]) === '') {
                        $response->addError(
                            "value_{$reference->type}",
                            "В соответствии с типом значений \"{$reference->type}\" справочника \"{$reference->name}\" " .
                            "должно быть заполнено поле value_{$reference->type}.");
                    }
                    break;
                case EReference::REF_TYPE_REF:
                    if (!isset($request['value_int']) || trim($request['value_int']) === '') {
                        $response->addError(
                            'value_int',
                            "В соответствии с типом значений справочника {$reference->type} " .
                            "должно быть заполнено поле value_int.");
                    } else {
                        $parentReferenceValue = $this->mainModel->get($request['value_int']);
                        if (!$parentReferenceValue instanceof EReferenceValue) {
                            $response->addError(
                                'value_int',
                                "В соответствии с типом значений \"{$reference->type}\" справочника \"{$reference->name}\" " .
                                "поле value_int должно быть идектификатором записи родительского справочника.");
                        }
                    }
                    break;
            }
        }

        foreach (EReference::REF_TYPES as $type) {
            if (isset($request['value_' . $type]) && trim($request['value_' . $type] === '')) {
                $request['value_' . $type] = null;
            }
        }

        $referenceValue->data($request);

        if ($this->mainModel->validate($referenceValue) === false) {
            foreach ($referenceValue->errors() as $key => $errors) {
                foreach ($errors as $error) {
                    $response->addError($key, $error);
                }
            }
        }
    }
}