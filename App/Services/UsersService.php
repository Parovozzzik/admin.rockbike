<?php

namespace App\Services;

use App\Helpers\Helper;
use App\Models\Entities\EUser;
use App\Models\Entities\Requests\EChangePasswordUser;
use App\Models\Entities\Requests\ELoginUser;
use App\Models\Entities\Requests\ERegistrationUser;
use App\Models\Entities\Responses\Response;
use App\Models\Entities\Responses\ResponseMessage;
use App\Models\User;

/**
 * Class UsersService
 * @package App\Services
 */
class UsersService
{
    /** @var User */
    protected $mainModel;

    /**
     * UsersService constructor.
     * @throws \App\Settings\Exceptions\DatabaseException
     */
    public function __construct()
    {
        $this->mainModel = User::getModel(EUser::class);
    }

    /**
     * @param ELoginUser $loginUser
     * @return Response
     * @throws \Doctrine\DBAL\DBALException
     */
    public function login(ELoginUser $loginUser): Response
    {
        $response = new Response();

        if (trim($loginUser->getEmail()) === '') {
            $response->addError('email', 'Поле Email обязательно для заполнения!');
        }
        if (trim($loginUser->getPassword()) === '') {
            $response->addError('password', 'Поле Password обязательно для заполнения!');
        }

        if ($response->hasErrors() === false) {
            if ($this->mainModel->existsByEmail($loginUser->getEmail())) {
                $user = $this->mainModel->getByEmail($loginUser->getEmail());

                if (Helper::passwordVerify($loginUser->getPassword(), $user->password)) {
                    $response->setUserId($user->user_id);
                    $response->setEmail($user->email);

                    $_SESSION['id'] = $user->user_id;
                    setcookie('email', $user->email, time() + 50000, '/');
                    setcookie('password', $user->password, time() + 50000, '/');

                    $response->addMessage(
                        new ResponseMessage(
                            'Авторизация прошла успешно!',
                            ResponseMessage::STATUS_SUCCESS,
                            ResponseMessage::ICON_SUCCESS
                        )
                    );
                } else {
                    $response->setErrors(['password' => 'Ошибка ввода логина или пароля!']);
                }
            } else {
                $response->setErrors(['email' => 'Данный пользователь не зарегистрирован!']);
            }
        }

        return $response;
    }

    /**
     * @return bool
     * @throws \Doctrine\DBAL\DBALException
     */
    public function isLogin(): bool
    {
        session_start();

        if (isset($_SESSION['id'])) {
            if (isset($_COOKIE['login']) && isset($_COOKIE['password'])) {
                setcookie('email', "", time() - 1, '/');
                setcookie('password', "", time() - 1, '/');
                setcookie('email', $_COOKIE['login'], time() + 50000, '/');
                setcookie('password', $_COOKIE['password'], time() + 50000, '/');

                return true;
            } else {
                /** @var EUser $user */
                $user = $this->mainModel->get($_SESSION['id']);
                if ($user instanceof EUser && $user->user_id === (int)$_SESSION['id']) {
                    setcookie('email', $user->email, time() + 50000, '/');
                    setcookie('password', $user->password, time() + 50000, '/');

                    return true;
                }
            }
        } else {
            if (isset($_COOKIE['email']) && isset($_COOKIE['password'])) {
                if ($this->mainModel->existsByEmail($_COOKIE['email'])) {
                    $user = $this->mainModel->getByEmail($_COOKIE['email']);
                    if (Helper::passwordVerify($_COOKIE['password'], $user->password)) {
                        $_SESSION['id'] = $user->user_id;

                        return true;
                    }
                }
            }
        }

        setcookie('email', '', time() - 360000, '/');
        setcookie('password', '', time() - 360000, '/');

        return false;
    }

    /**
     * @param EChangePasswordUser $changePasswordUser
     * @return Response
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Exception
     */
    public function changePassword(EChangePasswordUser $changePasswordUser): Response
    {
        $response = new Response();
        $currentPassword = trim($changePasswordUser->getCurrentPassword());

        $newPassword = trim($changePasswordUser->getNewPassword());
        $repeatPassword = trim($changePasswordUser->getRepeatPassword());

        /** @var EUser $user */
        $user = $this->mainModel->get($_SESSION['id']);

        if ($currentPassword === '') {
            $response->addError('current_password', 'Поле Current Password обязательно для заполнения!');
        } else {
            if (Helper::passwordVerify($currentPassword, $user->password) === false) {
                $response->addError('current_password', 'Введен неверный текущий пароль.');
            }
        }

        if ($newPassword === '') {
            $response->addError('new_password', 'Поле New Password обязательно для заполнения!');
        }
        if ($repeatPassword === '') {
            $response->addError('repeat_password', 'Поле Repeat Password обязательно для заполнения!');
        }

        if ($newPassword !== $repeatPassword) {
            $message = 'Введенные новые пароли должны совпадать!';
            $response->addError('repeat_password', $message);
            $response->addError('new_password', $message);
        }

        if ($response->hasErrors() === false) {
            $result = $this->mainModel->changePassword($user->user_id, $newPassword);
            if ($result === true) {
                $response->addMessage(
                    new ResponseMessage(
                        'Пароль успешно изменен!',
                        ResponseMessage::STATUS_SUCCESS,
                        ResponseMessage::ICON_SUCCESS
                    )
                );
            } else {
                $response->addMessage(
                    new ResponseMessage(
                        'Неизвестная ошибка изменения пароля. Обратитесь к администратору системы.',
                    ResponseMessage::STATUS_ERROR,
                    ResponseMessage::ICON_ERROR
                    )
                );
            }
        }

        return $response;
    }

    /**
     * @param ERegistrationUser $registrationUser
     * @return Response
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Exception
     */
    public function registration(ERegistrationUser $registrationUser): Response
    {
        $response = new Response();

        if (trim($registrationUser->getEmail()) === '') {
            $response->addError('email', 'Поле Email обязательно для заполнения!');
        }
        if (trim($registrationUser->getNewPassword()) === '') {
            $response->addError('password', 'Поле Password обязательно для заполнения!');
        } elseif (trim($registrationUser->getNewPassword()) !== trim($registrationUser->getRepeatPassword())) {
            $message = 'Введенные пароли должны совпадать!';
            $response->addError('repeat_password', $message);
            $response->addError('password', $message);
        }

        if ($response->hasErrors() === false && $this->mainModel->existsByEmail($registrationUser->getEmail()) === false) {
            $confirmCode = Helper::generateConfirmCode(12);
            $result = $this->mainModel->registration(
                $registrationUser->getEmail(),
                $registrationUser->getNewPassword(),
                $confirmCode
            );
            if ($result === true) {
                $response->addMessage(
                    new ResponseMessage(
                        'Учетная запись успешно зарегистрирована!',
                        ResponseMessage::STATUS_SUCCESS,
                        ResponseMessage::ICON_SUCCESS)
                );

                MailgunService::send(
                    $registrationUser->getEmail(),
                    $registrationUser->getEmail(),
                    'Учетная запись успешно зарегистрирована!',
                    '<a href="http://' . Helper::getFullDomain() . '/users/confirm-email/' . $registrationUser->getEmail() . '/' . $confirmCode . '">Confirm email</a>'
                );
            } else {
                $response->addMessage(
                    new ResponseMessage(
                        'Неизвестная ошибка регистрации пользователя. Обратитесь к администратору системы!',
                        ResponseMessage::STATUS_SUCCESS,
                        ResponseMessage::ICON_SUCCESS)
                );
            }

        } else {
            $response->addError('email', 'Данный пользователь уже зарегистрирован!');
        }
        $user = $this->mainModel->getByEmail($registrationUser->getEmail());
        $response->setUserId($user->user_id);
        $response->setEmail($user->email);

        return $response;
    }

    /**
     * @param string $email
     * @param string $code
     * @return Response
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Exception
     */
    public function confirmEmail(string $email, string $code): Response
    {
        $response = new Response();
        $user = $this->mainModel->getByEmail($email);
        if ($user instanceof EUser) {
            if ($user->email_confirm_code !== null && Helper::passwordVerify($code, $user->email_confirm_code)) {
                $result = $this->mainModel->confirmEmail($user->user_id);
                if ($result === true) {
                    $response->addMessage(
                        new ResponseMessage(
                            'Email успешно подтвержден!',
                            ResponseMessage::STATUS_SUCCESS,
                            ResponseMessage::ICON_SUCCESS
                        )
                    );
                } else {
                    $response->addMessage(
                        new ResponseMessage(
                            'Неизвестная ошибка подтверждения эл. почты. Обратитесь к администратору системы!',
                            ResponseMessage::STATUS_ERROR,
                            ResponseMessage::ICON_ERROR
                        )
                    );
                }

            } else {
                $response->setErrors(['Код подтверждения неверный или устарел!']);
            }
        } else {
            $response->setErrors(['Данный пользователь не зарегистрирован!']);
        }

        return $response;
    }

    /**
     * @param string $email
     * @return Response
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Exception
     */
    public function resendConfirmEmail(string $email): Response
    {
        $response = new Response();
        $user = $this->mainModel->getByEmail($email);
        if ($user instanceof EUser) {
            $confirmCode = Helper::generateConfirmCode(12);
            $result = $this->mainModel->resendConfirmEmail($user->user_id, $confirmCode);
            if ($result === true) {
                $response->addMessage(
                    new ResponseMessage(
                        'Email с новым кодом подтверждения успешно отправлен!',
                        ResponseMessage::STATUS_INFO,
                        ResponseMessage::ICON_INFO
                    )
                );

                MailgunService::send(
                    $email,
                    $email,
                    'Подтверждение регистрации!',
                    '<a href="http://' . Helper::getFullDomain() . '/users/confirm-email/' . $email . '/' . $confirmCode . '">Confirm email</a>'
                );
            } else {
                $response->addMessage(
                    new ResponseMessage(
                        'Неизвестная ошибка отправки письма-подтверждения. Обратитесь к администратору системы!',
                        ResponseMessage::STATUS_ERROR,
                        ResponseMessage::ICON_ERROR
                    )
                );
            }

        } else {
            $response->setErrors(['Данный пользователь не зарегистрирован!']);
        }

        return $response;
    }
}