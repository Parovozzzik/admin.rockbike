<?php

namespace App\Controllers;

use App\Models\Entities\Responses\Response;

/**
 * Class IndexController
 * @package App\Controllers
 */
class IndexController extends Controller
{
    /**
     * @return array
     */
    public function rules()
    {
        return [
            'index' => true,
        ];
    }

    /**
     * @throws \Twig\Error\Error
     */
    public function index()
    {
        $response = new Response();
        $response->setView('index.index');

        $this->render($response);
    }
}