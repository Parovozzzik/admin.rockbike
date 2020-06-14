<?php

namespace App\Controllers;

use App\Helpers\Helper;
use App\Models\Entities\EUser;
use App\Models\Entities\Responses\Response;
use App\Models\Entities\Responses\ResponseMessage;
use App\Models\User;
use App\Services\UsersService;
use App\Settings\DB\Database;
use Twig\Environment;
use Twig\Error\Error;
use Twig\Loader\FilesystemLoader;

/**
 * Class Controller
 * @package App\Controllers
 */
class Controller
{
    /** @var string */
    const HEAD_LAYOUT = 'App/Views/Templates/layout.twig';

    /** @var EUser */
    protected $user;

    /** @var User */
    protected $userModel;

    /** @var UsersService */
    protected $usersService;

    /** @var bool */
    protected $isGuest = true;

    /** @var array */
    protected $request;

    /**
     * Controller constructor.
     * @throws \App\Settings\Exceptions\DatabaseException
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Spot\Exception
     */
    public function __construct()
    {
        $this->request = $_REQUEST;

        Database::addConnection(
            getenv('DB_URI'),
            [\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8']
        );

        $this->userModel = User::getModel(EUser::class);
        $this->usersService = new UsersService();
        if ($this->usersService->isLogin()) {
            $this->isGuest = false;
            $this->user = $this->userModel->get($_SESSION['id']);
        }
    }

    /**
     * @param Response $response
     * @param array $params
     * @throws Error
     */
    public function render(Response $response, array $params = [])
    {
        $loader = new FilesystemLoader('/');
        $twig = new Environment($loader);
        $path = Helper::getViewPath($response->getView());

        $user = null;
        if (isset($_SESSION['id'])) {
            $user = $this->userModel->get($_SESSION['id']);
        }
        $params = array_merge($params,
            [
                'uri' => $_SERVER['REQUEST_URI']
            ]
        );

        if (isset($_SESSION['flash_messages']) && is_array($_SESSION['flash_messages'])) {
            $messages = $_SESSION['flash_messages'];
            /** @var ResponseMessage $message */
            foreach ($messages as $message) {
                $response->addMessage(
                    new ResponseMessage($message->getMessage(),
                        $message->getStatus(),
                        $message->getIcon())
                );
            }

            unset($_SESSION['flash_messages']);
        }

        try {
            echo $twig->render(self::HEAD_LAYOUT,
                [
                    'user' => $user,
                    'data' => $response->toArray(),
                    'content' => $path,
                    'params' => $params,
                ]
            );
        } catch (Error $e) {
            throw $e;
        }
    }

    /**
     * @param string $path
     * @param array $messages
     */
    public function redirect(string $path, array $messages = []): void
    {
        if (count($messages) > 0) {
            $_SESSION['flash_messages'] = $messages;
        }

        header('Location: ' . $path);
        exit();
    }

    /**
     * @param string $message
     */
    public function addFlashMessage(ResponseMessage $message): void
    {
        $currentMessages = [];
        if (isset($_SESSION['flash_messages']) && is_array($_SESSION['flash_messages'])) {
            $currentMessages = $_SESSION['flash_messages'];
        }

        $currentMessages[] = $message;
        $_SESSION['flash_messages'] = $currentMessages;
    }
}