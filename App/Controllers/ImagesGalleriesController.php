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
 * Class ImagesGalleriesController
 * @package App\Controllers
 */
class ImagesGalleriesController extends Controller
{
    /** @var ImageGallery */
    protected $mainModel;
    /** @var Gallery */
    protected $galleryModel;

    /**
     * GoodsController constructor.
     * @throws \App\Settings\Exceptions\DatabaseException
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Spot\Exception
     */
    public function __construct()
    {
        parent::__construct();
        $this->mainModel = ImageGallery::getModel(EImageGallery::class);
        $this->galleryModel = Gallery::getModel(EGallery::class);
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
        $goods = $this->mainModel->getList($page, 25);

        $response = new Response();
        $response->setView('imagesgalleries.index');
        $response->setModels($goods['data']);

        $this->render($response, [
            'pager' => $goods['pager'],
            'storage_path' => ImagesService::STORAGE_PATH,
        ]);
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

        if ($attr instanceof EImageGallery) {
            $response->setModel($attr);
        } else {
            $message = new ResponseMessage('Записи с указанным идентификатором не существует.',
                ResponseMessage::STATUS_ERROR,
                ResponseMessage::ICON_ERROR);

            $this->redirect('/images-galleries', [$message]);
        }

        $response->setView('imagesgalleries.view');
        $this->render($response, [
            'storage_path' => ImagesService::STORAGE_PATH,
        ]);
    }

    /**
     * @throws \App\Settings\Exceptions\DatabaseException
     */
    public function delete()
    {
        $id = $this->request['id'];
        $imagegallery = $this->mainModel->get($id);

        if ($imagegallery instanceof EImageGallery) {
            $this->mainModel->delete(['image_gallery_id' => $imagegallery->image_gallery_id]);
            $message = new ResponseMessage(
                "Связь #{$imagegallery->image_gallery_id} успешно удалена",
                ResponseMessage::STATUS_SUCCESS,
                ResponseMessage::ICON_SUCCESS);
        } else {
            $message = new ResponseMessage(
                'Связи с указанным идентификатором не существует',
                ResponseMessage::STATUS_ERROR,
                ResponseMessage::ICON_ERROR);
        }

        $this->redirect('/images-galleries', [$message]);
    }

    /**
     * @throws \App\Settings\Exceptions\DatabaseException
     * @throws \Twig\Error\Error
     */
    public function create()
    {
        $step = 1;
        $images = [];
        $response = new Response();

        $gallery = new EGallery();
        $response->setModel($gallery);

        if (isset($_POST['image_gallery'])) {
            $request = $_POST['image_gallery'];
            $galleryId = (int)$request['gallery_id'];

            /** @var Gallery $galleryModel */
            $galleryModel = Gallery::getModel(EGallery::class);
            if ($galleryModel->where(['gallery_id' => $galleryId])) {
                $step = 2;
                $gallery->data($request);
            }
        } else {
            $request = $gallery->toArray();
        }

        if ($step === 2) {
            /** @var Image $imagesModel */
            $imagesModel = Image::getModel(EImage::class);
            $images = $imagesModel->getByGalleryId($gallery->gallery_id);
        }

        $response->setView('imagesgalleries.create');

        $this->render($response,
            [
                'request' => $request,
                'step' => $step,
                'gallery' => $gallery ?? [],
                'images' => $images ?? [],
            ]
        );
    }
}