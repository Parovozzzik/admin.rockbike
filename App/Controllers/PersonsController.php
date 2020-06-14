<?php

namespace App\Controllers;

use App\Models\Entities\EPerson;
use App\Models\Entities\Responses\Response;
use App\Models\Person;

/**
 * Class PersonsController
 * @package App\Controllers
 */
class PersonsController extends Controller
{
    /** @var Person */
    protected $mainModel;

    /**
     * PersonsController constructor.
     * @throws \App\Settings\Exceptions\DatabaseException
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Spot\Exception
     */
    public function __construct()
    {
        parent::__construct();
        $this->mainModel = Person::getModel(EPerson::class);
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'index' => !$this->isGuest,
            'view' => !$this->isGuest,
            'my' => !$this->isGuest,
            'create' => !$this->isGuest,
            'edit' => !$this->isGuest,
        ];
    }

    /**
     * @throws \Twig\Error\Error
     */
    public function index()
    {
        $page = $this->request['page'] ?? 0;
        $persons = $this->mainModel->getList($page);

        $response = new Response();
        $response->setModels($persons['data']);
        $response->setView('persons.index');

        $this->render($response, ['pager' => $persons['pager']]);
    }

    /**
     * @throws \Twig\Error\Error
     */
    public function view()
    {
        $id = $this->request['id'];

        $person = $this->mainModel->get($id);
        $response = new Response();

        if ($person instanceof EPerson) {
            $response->setModel($person);
        } else {
            $response->setErrors(['Персоны с текущим идентификатором не существует!']);
        }
        $response->setView('persons.view');

        $this->render($response);
    }

    /**
     * @throws \App\Settings\Exceptions\DatabaseException
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Twig\Error\Error
     */
    public function my()
    {
        $person = $this->mainModel->getByUserId($this->user->user_id);

        if ($person instanceof EPerson) {
            $response = new Response();
            $response->setModel($person);
            $response->setView('persons.view');

            $this->render($response);
        } else {
            $this->redirect('/persons/create');
        }
    }

    /**
     * @throws \App\Settings\Exceptions\DatabaseException
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Twig\Error\Error
     */
    public function create()
    {
        $person = $this->mainModel->getByUserId($this->user->user_id);

        if ($person instanceof EPerson) {
            $this->redirect('/persons/my');
        }

        $request = [];
        $response = new Response();

        if (isset($_POST['person'])) {
            $request = $_POST['person'];
            $response = $this->save($person, $request);
        }

        $response->setView('persons.create');

        $this->render($response, ['request' => $request]);
    }

    /**
     * @throws \App\Settings\Exceptions\DatabaseException
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Twig\Error\Error
     */
    public function edit()
    {
        $person = $this->mainModel->getByUserId($this->user->user_id);

        if (!$person instanceof EPerson) {
            $this->redirect('/persons/create');
        }

        $response = new Response();
        if (isset($_POST['person'])) {
            $request = $_POST['person'];
            $response = $this->save($person, $request);
        } else {
            $request = $person->toArray();
        }

        $response->setView('persons.create');

        $this->render($response, ['request' => $request]);
    }

    /**
     * @param EPerson|null $person
     * @param array $request
     * @return Response
     * @throws \App\Settings\Exceptions\DatabaseException
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function save(?EPerson $person, array $request): Response
    {
        if ($person === null) {
            $person = new EPerson($request);
            $person->user_id = $this->user->user_id;
        } else {
            $person->data($request);
        }

        $response = $this->mainModel->saveByEPerson($person);

        if ($response->hasErrors() === false) {
            $this->redirect('/persons/my');
        }

        return $response;
    }
}