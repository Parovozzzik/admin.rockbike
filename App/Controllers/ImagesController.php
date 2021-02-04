<?php

namespace App\Controllers;

use App\Models\Entities\EGallery;
use App\Models\Entities\EImage;
use App\Models\Entities\EImageGallery;
use App\Models\Entities\Responses\Response;
use App\Models\Entities\Responses\ResponseMessage;
use App\Models\Gallery;
use App\Models\Image;
use App\Models\ImageGallery;
use App\Services\ImagesService;

/**
 * Class ImagesController
 * @package App\Controllers
 */
class ImagesController extends Controller
{
    /** @var Image */
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
        $this->mainModel = Image::getModel(EImage::class);
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
        $images = $this->mainModel->getList($page, 10);

        $response = new Response();
        $response->setView('images.index');
        $response->setModels($images['data']);

        $this->render($response, [
            'storage_path' => ImagesService::STORAGE_PATH,
            'pager' => $images['pager']
        ]);
    }

    /**
     * @throws \App\Settings\Exceptions\DatabaseException
     * @throws \Twig\Error\Error
     */
    public function view()
    {
        $id = $this->request['id'];

        $image = $this->mainModel->get($id);
        $response = new Response();

        if ($image instanceof EImage) {
            $response->setModel($image);
        } else {
            $message = new ResponseMessage('Изображения с указанным идентификатором не существует',
                ResponseMessage::STATUS_ERROR,
                ResponseMessage::ICON_ERROR);

            $this->redirect('/images', [$message]);
        }
        $response->setView('images.view');

        /** @var Gallery $galleryModel */
        $galleryModel = Gallery::getModel(EGallery::class);
        $galleries = $galleryModel->getByImageId($id);

        $this->render($response, [
            'galleries' => $galleries,
            'storage_path' => ImagesService::STORAGE_PATH,
        ]);
    }

    /**
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Twig\Error\Error
     */
    public function create()
    {
        $response = new Response();

        $image = new EImage();
        $response->setModel($image);

        if (isset($_POST['image'])) {
            $request = $_POST['image'];
            $file = $_FILES['path'];
            if ($file['error'][0] === 0 && isset($file['name'][0])) {
                $request['path'] = $file['tmp_name'][0];
            }

            $this->validate($image, $request, $response);

            if ($image->hasErrors() === false && $response->hasErrors() === false) {
                $imageService = new ImagesService();
                $fileName = $imageService->upload($file, new \DateTime());

                if ($fileName !== null) {
                    $image->path = $fileName;
                    $this->save($image, $response);
                } else {
                    $response->addMessage(
                        new ResponseMessage(
                            'Image does not save.',
                            ResponseMessage::STATUS_ERROR,
                            ResponseMessage::ICON_ERROR
                        )
                    );
                }
            }
        } else {
            $request = $image->toArray();
        }

        $response->setView('images.create');
        $this->render($response,
            [
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
        $image = $this->mainModel->get($id);

        $response = new Response();

        if ($image instanceof EImage) {
            $response->setModel($image);

            if (isset($_POST['image'])) {
                $request = $_POST['image'];
                $file = $_FILES['path'];

                if ((int)$file['error'][0] !== 0) {
                    $request['path'] = $image->path;
                } else {
                    $request['path'] = $file['tmp_name'][0];
                }

                $this->validate($image, $request, $response);
                if ($image->hasErrors() === false && $response->hasErrors() === false) {
                    if ($image->isModified('path') === true) {
                        $imageService = new ImagesService();
                        $fileName = $imageService->upload($file, $image->created_at);
                        if ($fileName !== null) {
                            $image->path = $fileName;
                            $this->save($image, $response, true);
                        } else {
                            $response->addMessage(
                                new ResponseMessage(
                                    'Image does not save to storage.',
                                    ResponseMessage::STATUS_ERROR,
                                    ResponseMessage::ICON_ERROR
                                )
                            );
                        }
                    } else {
                        $this->save($image, $response);
                    }
                }
            } else {
                $request = $image->toArray();
            }
        } else {
            $message = new ResponseMessage('Изображения с указанным идентификатором не существует.',
                ResponseMessage::STATUS_ERROR,
                ResponseMessage::ICON_ERROR);

            $this->redirect('/images', [$message]);
        }

        /** @var Gallery $galleryModel */
        $galleryModel = Gallery::getModel(EGallery::class);
        $galleries = $galleryModel->getByImageId($id);

        $response->setView('images.create');
        $this->render($response,
            [
                'request' => $request,
                'galleries' => $galleries,
                'storage_path' => ImagesService::STORAGE_PATH,
            ]
        );
    }

    /**
     * @throws \App\Settings\Exceptions\DatabaseException
     */
    public function delete()
    {
        $id = $this->request['id'];
        $image = $this->mainModel->get($id);

        if ($image instanceof EImage) {
            $imageGalleryModel = ImageGallery::getModel(EImageGallery::class);
            $galleryExists = $imageGalleryModel->where(['image_id' => $image->image_id])->count() > 0;

            if ($galleryExists === true) {
                $message = new ResponseMessage(
                    "Ошибка удаления изображения #{$image->image_id}. Изображение имеет связанные данные",
                    ResponseMessage::STATUS_ERROR,
                    ResponseMessage::ICON_ERROR);
            } else {
                $this->mainModel->delete(['image_id' => $image->image_id]);
                $imageService = new ImagesService();
                $imageService->unlink($image->path, $image->created_at);
                $message = new ResponseMessage(
                    "Изображение #{$image->image_id} успешно удалено",
                    ResponseMessage::STATUS_SUCCESS,
                    ResponseMessage::ICON_SUCCESS);
            }
        } else {
            $message = new ResponseMessage(
                'Изображения с указанным идентификатором не существует',
                ResponseMessage::STATUS_ERROR,
                ResponseMessage::ICON_ERROR);
        }

        $this->redirect('/images', [$message]);
    }

    /**
     * @param EImage $image
     * @param array $request
     * @param Response $response
     */
    protected function validate(EImage $image, array &$request, Response $response): void
    {
        $image->data($request);

        if ($this->mainModel->validate($image) === false) {
            foreach ($image->errors() as $key => $errors) {
                foreach ($errors as $error) {
                    $response->addError($key, $error);
                }
            }
        }
    }

    /**
     * @param EImage $image
     * @param Response $response
     * @param bool $isNeedUnlinkFile
     */
    protected function save(EImage $image, Response $response, bool $isNeedUnlinkFile = false): void
    {
        try {
            $imagePath = $image->dataUnmodified('path');
            $result = $this->mainModel->save($image);
            if ((bool)$result === true) {

                if ($isNeedUnlinkFile === true) {
                    $imageService = new ImagesService();
                    $imageService->unlink($imagePath, $image->created_at);
                }

                $message = new ResponseMessage(
                    'Изображение успешно сохранено!',
                    ResponseMessage::STATUS_SUCCESS,
                    ResponseMessage::ICON_SUCCESS
                );

                $this->redirect('/images/view/' . $image->image_id, [$message]);
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
}