<?php

namespace App\Controllers;

use App\Models\Entities\EUser;
use App\Models\Entities\Requests\EChangePasswordUser;
use App\Models\Entities\Requests\ELoginUser;
use App\Models\Entities\Requests\ERegistrationUser;
use App\Models\Entities\Responses\Response;
use App\Models\User;
use App\Services\UsersService;

/**
 * Class UsersController
 * @package App\Controllers
 */
class UsersController extends Controller
{
    /** @var User */
    protected $mainModel;

    /**
     * UsersController constructor.
     * @throws \App\Settings\Exceptions\DatabaseException
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Spot\Exception
     */
    public function __construct()
    {
        parent::__construct();
        $this->mainModel = User::getModel(EUser::class);
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'index' => !$this->isGuest,
            'view' => !$this->isGuest,
            'login' => $this->isGuest,
            'registration' => $this->isGuest,
            'confirmEmail' => true,
            'logout' => !$this->isGuest,
            'restorePassword' => !$this->isGuest,
            'changePassword' => !$this->isGuest,
            'edit' => true,
            'deleted' => true,
            'resendConfirmEmail' => !$this->isGuest,
        ];
    }

    /**
     * @throws \Twig\Error\Error
     */
    public function index()
    {
        $page = $this->request['page'] ?? 1;
        $users = $this->mainModel->getList($page);

        $response = new Response();
        $response->setModels($users['data']);
        $response->setView('users.index');

        $this->render($response, ['pager' => $users['pager']]);
    }

    /**
     * @throws \Twig\Error\Error
     */
    public function view()
    {
        $id = $this->request['id'];
        $user = $this->userModel->get($id);

        $response = new Response();
        if ($user instanceof EUser) {
            $response->setModel($user);
        } else {
            $response->setErrors(['Пользователя с текущим идентификатором не существует!']);
        }
        $response->setView('users.view');

        $this->render($response);
    }

    /**
     * @throws \App\Settings\Exceptions\DatabaseException
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Twig\Error\Error
     */
    public function login()
    {
        $request = null;
        if (isset($_POST['user'])) {
            $request = $_POST['user'];
            $request['remember'] = false;
            if (isset($request['remember']) && $request['remember'] === 'on')
            {
                $request['remember'] = true;
            }

            $loginDto = new ELoginUser();
            $loginDto->setEmail($request['email']);
            $loginDto->setPassword($request['password']);
            $loginDto->setRemember($request['remember']);

            $usersService = new UsersService();
            $response = $usersService->login($loginDto);

            if ($response->hasErrors() === false) {
                $this->redirect('/users/view/' . $response->getUserId(), $response->getMessages());
            }
        } else {
            $response = new Response();
        }
        $response->setView('users.login');

        $this->render($response, ['request' => $request]);
    }

    /**
     * logout
     */
    public function logout()
    {
        unset($_SESSION['id']);
        setcookie('email', '');
        setcookie('password', '');

        $this->redirect('/');
    }

    /**
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Twig\Error\Error
     */
    public function registration()
    {
        $request = null;
        if (isset($_POST['user'])) {
            $request = $_POST['user'];
            $registrationDto = new ERegistrationUser();
            $registrationDto->setEmail($request['email']);
            $registrationDto->setNewPassword($request['password']);
            $registrationDto->setRepeatPassword($request['repeat_password']);

            $response = $this->usersService->registration($registrationDto);
            if ($response->hasErrors() === false) {
                $this->redirect('/users/login', $response->getMessages());
            }
        } else {
            $response = new Response();
        }
        $response->setView('users.registration');

        $this->render($response, ['request' => $request]);
    }

    /**
     *
     */
    public function restorePassword()
    {

    }

    /**
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Twig\Error\Error
     */
    public function changePassword()
    {
        $request = null;
        if (isset($_POST['user'])) {
            $request = $_POST['user'];
            $changePasswordDto = new EChangePasswordUser();
            $changePasswordDto->setCurrentPassword($request['current_password']);
            $changePasswordDto->setNewPassword($request['new_password']);
            $changePasswordDto->setRepeatPassword($request['repeat_password']);

            $response = $this->usersService->changePassword($changePasswordDto);
            if ($response->hasErrors() === false) {
                $this->redirect('/users/view/' . $this->user->user_id, $response->getMessages());
            }
        } else {
            $user = $this->userModel->get($_SESSION['id']);
            $response = new Response();

            if ($user instanceof EUser) {
                $response->setModel($user);
            } else {
                $response->setErrors(['Пользователя с текущим идентификатором не существует!']);
            }
            $response->setView('users.view');
        }
        $response->setView('users.change_password');

        $this->render($response, ['request' => $request]);
    }

    /**
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Twig\Error\Error
     */
    public function confirmEmail()
    {
        $email = $this->request['email'];
        $code = $this->request['code'];

        $response = $this->usersService->confirmEmail($email, $code);
        $response->setView('users.confirm_email');

        $this->render($response);
    }

    /**
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     */
    public function resendConfirmEmail()
    {
        $response = $this->usersService->resendConfirmEmail($this->user->email);

        $this->redirect('/users/view/' . $this->user->user_id, $response->getMessages());
    }
}