<?php

namespace App\Controllers;

use App\Models\Entities\EGallery;
use App\Models\Entities\EGood;
use App\Models\Entities\EManufacturer;
use App\Models\Entities\Entity;
use App\Models\Entities\EUser;
use App\Models\Entities\Responses\Response;
use App\Models\Entities\Responses\ResponseMessage;
use App\Models\Gallery;
use App\Models\Good;
use App\Models\Manufacturer;
use App\Models\User;

/**
 * Class GalleriesController
 * @package App\Controllers
 */
class GalleriesController extends Controller
{
    /** @var Gallery */
    protected $mainModel;

    /**
     * GalleriesController constructor.
     * @throws \App\Settings\Exceptions\DatabaseException
     */
    public function __construct()
    {
        parent::__construct();
        $this->mainModel = Gallery::getModel(EGallery::class);
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
     * @throws \ReflectionException
     */
    public function index()
    {
        $page = $this->request['page'] ?? 1;
        $galleries = $this->mainModel->getList($page, 10);

        $response = new Response();
        $response->setView('galleries.index');
        $response->setModels($galleries['data']);

        $this->render($response, ['pager' => $galleries['pager']]);
    }

    /**
     * @throws \App\Settings\Exceptions\DatabaseException
     * @throws \Twig\Error\Error
     */
    public function view()
    {
        $id = $this->request['id'];

        $attr = $this->mainModel->get($id);
        $response = new Response();

        if ($attr instanceof EGallery) {
            $response->setModel($attr);
        } else {
            $message = new ResponseMessage('Атрибута с указанным идентификатором не существует',
                ResponseMessage::STATUS_ERROR,
                ResponseMessage::ICON_ERROR);

            $this->redirect('/galleries', [$message]);
        }
        $response->setView('galleries.view');

        /** special processing for select input */
        $types = [];
        foreach (EGallery::GALLERY_TYPES as $type) {
            $types[$type] = $type;
        }

        $this->render($response, [
            'types' => $types,
        ]);
    }

    /**
     * @throws \ReflectionException
     */
    public function create()
    {
        $response = new Response();

        $gallery = new EGallery();
        $response->setModel($gallery);

        if (isset($_POST['gallery'])) {
            $request = $_POST['gallery'];
            $this->validate($gallery, $request, $response);
            if ($gallery->hasErrors() === false && $response->hasErrors() === false) {
                $this->save($gallery, $response);
            }
        } else {
            $request = $gallery->toArray();
        }

        /** special processing for select input */
        $types = [];
        foreach (EGallery::GALLERY_TYPES as $type) {
            $types[$type] = $type;
        }

        $response->setView('galleries.create');
        $this->render($response,
            [
                'types' => $types,
                'request' => $request,
            ]
        );
    }

    /**
     * @throws \ReflectionException
     */
    public function edit()
    {
        $id = $this->request['id'];
        $gallery = $this->mainModel->get($id);

        $response = new Response();

        if ($gallery instanceof EGallery) {
            $response->setModel($gallery);

            if (isset($_POST['gallery'])) {
                $request = $_POST['gallery'];
                $this->validate($gallery, $request, $response);
                if ($gallery->hasErrors() === false && $response->hasErrors() === false) {
                    $this->save($gallery, $response);
                }
            } else {
                $request = $gallery->toArray();
            }
        } else {
            $message = new ResponseMessage('Галереи с указанным идентификатором не существует.',
                ResponseMessage::STATUS_ERROR,
                ResponseMessage::ICON_ERROR);

            $this->redirect('/galleries', [$message]);
        }

        /** special processing for select input */
        $types = [];
        foreach (EGallery::GALLERY_TYPES as $type) {
            $types[$type] = $type;
        }

        $response->setView('galleries.create');
        $this->render($response,
            [
                'types' => $types,
                'request' => $request,
            ]
        );
    }

    /**
     * @param EGallery $gallery
     * @param Response $response
     */
    protected function save(EGallery $gallery, Response $response): void
    {
        try {
            $result = $this->mainModel->save($gallery);
            if ((bool)$result === true) {
                $message = new ResponseMessage(
                    'Галерея успешно сохранена!',
                    ResponseMessage::STATUS_SUCCESS,
                    ResponseMessage::ICON_SUCCESS
                );
                $this->redirect('/galleries/view/' . $gallery->gallery_id, [$message]);
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
     * @param EGallery $gallery
     * @param array $request
     * @param Response $response
     * @throws \App\Settings\Exceptions\DatabaseException
     */
    protected function validate(EGallery $gallery, array &$request, Response $response): void
    {
        if (!in_array($request['type'], EGallery::GALLERY_TYPES)) {
            $attrTypesString = implode(', ', EGallery::GALLERY_TYPES);
            $response->addError('type', "Поле type должно быть одним из значений: {$attrTypesString}!");
        } else {
            if (!isset($request['parent_object_id']) || trim($request['parent_object_id']) === '') {
                $response->addError('parent_object_id', 'Поле parent_object_id обязательно для заполнения!');
            }
            $objectId = $request['parent_object_id'];
            switch ($request['type']) {
                case EGallery::GALLERY_TYPE_GOOD:
                    $model = Good::getModel(EGood::class);

                    $exists = $this->mainModel->where([
                        'parent_object_id' => $objectId,
                        'type' => EGallery::GALLERY_TYPE_GOOD,
                        'gallery_id !=' => $request['gallery_id']
                    ])->first();
                    if ($exists !== false) {
                        $response->addError('parent_object_id', 'У данного объекта галерея уже существует!');
                    }
                    break;
                case EGallery::GALLERY_TYPE_MODEL:
                    //TODO
                    //$model =
                    break;
                case EGallery::GALLERY_TYPE_MANUFACTURER:
                    $model = Manufacturer::getModel(EManufacturer::class);
                    break;
                case EGallery::GALLERY_TYPE_USER:
                    $model = User::getModel(EUser::class);
                    break;
            }
            if ($model->get($objectId) === false) {
                $response->addError('parent_object_id', 'Указанного объекта не существует!');
            }
        }
        if (isset($request['name']) && trim($request['name'] === '')) {
            $request['name'] = null;
        }
        if (isset($request['slug']) && trim($request['slug'] === '')) {
            $request['slug'] = null;
        }

        $gallery->data($request);

        if ($this->mainModel->validate($gallery) === false) {
            foreach ($gallery->errors() as $key => $errors) {
                foreach ($errors as $error) {
                    $response->addError($key, $error);
                }
            }
        }
    }
}