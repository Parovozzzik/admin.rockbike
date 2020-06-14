<?php

namespace App\Controllers;

use App\Models\Entities\EManufacturer;
use App\Models\Entities\Responses\Response;
use App\Models\Manufacturer;

/**
 * Class ManufacturersController
 * @package App\Controllers
 */
class ManufacturersController extends Controller
{
    /** @var Manufacturer */
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
        $this->mainModel = Manufacturer::getModel(EManufacturer::class);
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
     * @return array
     * @throws \App\Settings\Exceptions\DatabaseException
     */
    public function ajaxList()
    {
        $text = $this->request['text'];

        $manufacturers = $this->mainModel->getByText($text);

        return $manufacturers;
    }
}