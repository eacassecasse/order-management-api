<?php

namespace app\api\controller;

use App\api\controller\BaseController;

use App\api\model\UserInputModel;

use App\api\exceptionHandler\ApiExceptionHandler;

use App\core\shared\Utilities;

use App\domain\exception\BusinessException;
use App\domain\exception\ConnectionException;
use App\domain\exception\EntityNotFoundException;
use App\domain\exception\MYSQLTransactionException;

use App\domain\service\UserService;

use Hateoas\HateoasBuilder;
use Hateoas\UrlGenerator\CallableUrlGenerator;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Pagerfanta;
use Hateoas\Representation\Factory\PagerfantaFactory;
use Hateoas\Configuration\Route;

class UserController extends BaseController
{
    private $service;
    private $hateoas;

    public function __construct()
    {
        $this->service = new UserService();

        $urlGenerator = new CallableUrlGenerator(function ($route, $parameters) {
            return $route . '?' . http_build_query($parameters);
        });

        $this->hateoas = HateoasBuilder::create()->setUrlGenerator(null, $urlGenerator)->build();

    }


    /**
     * @method void create()
     * 
     * "/v1/endpoints/users/create" Endpoint 
     * 
     * Creates a new user
     */
    public function create()
    {

        $errorMessage = '';
        $requestMethod = filter_input(INPUT_SERVER, 'REQUEST_METHOD', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        if (strtoupper($requestMethod) == 'POST') {
            try {

                $data = json_decode(file_get_contents("php://input"));

                $email = filter_var($this->clean($data->email), FILTER_VALIDATE_EMAIL);
                $password = $this->clean($data->password);

                $model = new UserInputModel();
                $model->setEmail($email);
                $model->setPassword(password_hash($password, PASSWORD_DEFAULT));

                $outputModel = $this->hateoas->serialize(
                    Utilities::toUserOutputModel($this->service->create($model)), 'json');

            }
            catch (ConnectionException $connectionException) {
                $errorMessage = ApiExceptionHandler::handleConnectionException($connectionException);
                $errorHeader = 'HTTT/1.1 500 Internal Server Error';
            }
            catch (BusinessException $businessException) {
                $errorMessage = ApiExceptionHandler::handleBusinessException($businessException);
                $errorHeader = 'HTTP/1.1 400 Bad Request';
            }
        }
        else {
            $errorMessage = ApiExceptionHandler::handleMethodNotSupported('Method not supported', strtoupper($requestMethod));
            $errorHeader = 'HTTP/1.1 405 Method Not Allowed';
        }

        //Send Output
        if (!($errorMessage)) {
            $this->sendOutput($outputModel, array('Content-Type: application/json', 'HTTP/1.1 201 Created'));
        }
        else {
            $this->sendOutput(
                json_encode(
                array('error' => $errorMessage)
                , JSON_PRETTY_PRINT), array('Content-Type: application/json', $errorHeader));
        }
    }

    /**
     * @method void findOne($id)
     * 
     * "/v1/endpoints/users/{id}" Endpoint 
     * 
     * Get a user by a given ID
     * 
     * You can either get a user by his email
     * passing this variable as a query parameters
     */

    public function findOne($id = null)
    {
        $errorMessage = '';
        $requestMethod = filter_input(INPUT_SERVER, 'REQUEST_METHOD', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        if (strtoupper($requestMethod) == 'GET') {

            try {

                $id = filter_var($id, FILTER_VALIDATE_INT);

                if ($id) {

                    $outputModel = $this->hateoas->serialize(
                        Utilities::toUserOutputModel($this->service->findOne($id)), 'json');
                }
                else {
                    $query = parse_url(filter_input(INPUT_SERVER, 'REQUEST_URI'), PHP_URL_QUERY);
                    $toArrayQuery = parse_str($query, $arrayQuery);

                    if (count($arrayQuery)) {
                        if (isset($arrayQuery['email'])) {
                            $email = filter_var($arrayQuery['email'], FILTER_SANITIZE_SPECIAL_CHARS);

                            $outputModel = $this->hateoas->serialize(
                                Utilities::toUserOutputModel(
                                $this->service->findEmail($email)), 'json');

                        }
                    }
                    else {
                        $errorMessage = 'Provide a valid ID please!';
                        $errorHeader = 'HTTP/1.1 400 Bad Request';
                    }
                }
            }
            catch (ConnectionException $connectionException) {
                $errorMessage = ApiExceptionHandler::handleConnectionException($connectionException);
                $errorHeader = 'HTTP/1.1 500 Internal Server Error';
            }
            catch (MYSQLTransactionException $mysqlTransaction) {
                $errorMessage = ApiExceptionHandler::handleMYSQLTransactionException($mysqlTransaction);
                $errorHeader = 'HTTP/1.1 400 Bad Request';
            }
            catch (EntityNotFoundException $entityNotFoundException) {
                $errorMessage = ApiExceptionHandler::handleEntityNotFoundException($entityNotFoundException);
                $errorHeader = 'HTTP/1.1 404 Not Found';
            }
        }
        else {
            $errorMessage = ApiExceptionHandler::handleMethodNotSupported('Method not supported', strtoupper($requestMethod));
            $errorHeader = 'HTTP/1.1 405 Method Not Allowed';
        }

        //Send Output
        if (!($errorMessage)) {
            $this->sendOutput($outputModel, array('Content-Type: application/json', 'HTTP/1.1 200 OK'));
        }
        else {
            $this->sendOutput(
                json_encode(
                array('error' => $errorMessage)
                , JSON_PRETTY_PRINT), array('Content-Type: application/json', $errorHeader));
        }
    }

    /**
     * @method void find()
     * 
     * "/v1/endpoints/users" Endpoint 
     * 
     * Get a list of users
     */

    public function find()
    {
        $errorMessage = '';
        $requestMethod = filter_input(INPUT_SERVER, 'REQUEST_METHOD', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        if (strtoupper($requestMethod) == 'GET') {
            try {

                $adapter = new ArrayAdapter(
                    Utilities::toUserOutputCollectionModel($this->service->findAll()));

                $pager = new Pagerfanta($adapter);

                if ($adapter->getNbResults() > 10) {
                    $pager->setMaxPerPage(8);
                }
                else {

                    $pager->setMaxPerPage(4);
                }

                $pagerFantaFactory = new PagerfantaFactory();

                $paginatedCollection = $pagerFantaFactory->createRepresentation(
                    $pager, new Route('/v1/endpoints/users/', array()));

                $outputModel = $this->hateoas->serialize($paginatedCollection, 'json');
            }
            catch (ConnectionException $connectionException) {
                $errorMessage = ApiExceptionHandler::handleConnectionException($connectionException);
                $errorHeader = 'HTTP/1.1 500 Internal Server Error';
            }
            catch (MYSQLTransactionException $mysqlTransactionException) {
                $errorMessage = ApiExceptionHandler::handleMYSQLTransactionException($mysqlTransactionException);
                $errorHeader = 'HTTP/1.1 400 Bad Request';
            }
            catch (EntityNotFoundException $entityNotFoundException) {
                $errorMessage = ApiExceptionHandler::handleEntityNotFoundException($entityNotFoundException);
                $errorHeader = 'HTTP/1.1 404 Not Found';
            }
        }
        else {
            $errorMessage = ApiExceptionHandler::handleMethodNotSupported('Method not supported', strtoupper($requestMethod));
            $errorHeader = 'HTTP/1.1 405 Method Not Allowed';
        }

        //Send Output
        if (!($errorMessage)) {
            $this->sendOutput($outputModel, array('Content-Type: application/json', 'HTTP/1.1 200 OK'));
        }
        else {
            $this->sendOutput(
                json_encode(
                array('error' => $errorMessage)
                , JSON_PRETTY_PRINT), array('Content-Type: application/json', $errorHeader));
        }
    }

    /**
     * @method void update($id)
     * 
     * "/v1/endpoints/users/{id}/update" Endpoint 
     * 
     * Updates a user with the given ID
     */

    public function update(int $id)
    {
        $errorMessage = '';
        $requestMethod = filter_input(INPUT_SERVER, 'REQUEST_METHOD', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        if (strtoupper($requestMethod) == 'PUT') {
            try {

                $data = json_decode(file_get_contents("php://input"));

                $id = isset($id) ? 
                    filter_var($this->clean($id), FILTER_VALIDATE_INT) : 0;

                $email = $this->validEmail($this->clean($data->email));
                $password = $this->clean($data->password);

                $model = new UserInputModel();
                $model->setEmail($email);
                $model->setPassword(password_hash($password, PASSWORD_DEFAULT));

                $outputModel = $this->hateoas->serialize(Utilities::toUserOutputModel(
                    $this->service->update($id, $model)), 'json');
            }
            catch (ConnectionException $connectionException) {
                $errorMessage = ApiExceptionHandler::handleConnectionException($connectionException);
                $errorHeader = 'HTTP/1.1 500 Internal Server Error';
            }
            catch (MYSQLTransactionException $mysqlTransactionException) {
                $errorMessage = ApiExceptionHandler::handleMYSQLTransactionException($mysqlTransactionException);
                $errorHeader = 'HTTP/1.1 400 Bad Request';
            }
            catch (EntityNotFoundException $entityNotFoundException) {
                $errorMessage = ApiExceptionHandler::handleEntityNotFoundException($entityNotFoundException);
                $errorHeader = 'HTTP/1.1 404 Not Found';
            }
            catch (BusinessException $businessException) {
                $errorMessage = ApiExceptionHandler::handleBusinessException($businessException);
                $errorHeader = 'HTTP/1.1 400 Bad Request';
            }
        }
        else {
            $errorMessage = ApiExceptionHandler::handleMethodNotSupported('Method not supported', strtoupper($requestMethod));
            $errorHeader = 'HTTP/1.1 405 Method Not Allowed';
        }

        //Send Output
        if (!($errorMessage)) {
            $this->sendOutput($outputModel, array('Content-Type: application/json', 'HTTP/1.1 200 OK'));
        }
        else {
            $this->sendOutput(
                json_encode(
                array('error' => $errorMessage)
                , JSON_PRETTY_PRINT), array('Content-Type: application/json', $errorHeader));
        }
    }

    /**
     * @method void delete($id)
     * 
     * "/v1/endpoints/users/{id}/delete" Endpoint 
     * 
     * Delete a user with the given ID
     */

    public function delete($id)
    {
        $errorMessage = '';
        $requestMethod = filter_input(INPUT_SERVER, 'REQUEST_METHOD', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        if (strtoupper($requestMethod) == 'DELETE') {

            try {

                $id = ($id) ? filter_var($id, FILTER_SANITIZE_NUMBER_INT, array("flags" => FILTER_VALIDATE_INT)) : 0;

                if ($this->service->delete($id)) {
                    $outputModel = '';
                }
                else {
                    $errorMessage = 'Oops! Something went wrong';
                    $errorHeader = 'HTTP/1.1 500 Internal Server Error';
                }
            }
            catch (ConnectionException $connectionException) {
                $errorMessage = ApiExceptionHandler::handleConnectionException($connectionException);
                $errorHeader = 'HTTP/1.1 500 Internal Server Error';
            }
            catch (EntityNotFoundException $entityNotFoundException) {
                $errorMessage = ApiExceptionHandler::handleEntityNotFoundException($entityNotFoundException);
                $errorHeader = 'HTTP/1.1 404 Not Found';
            }
        }
        else {
            $errorMessage = ApiExceptionHandler::handleMethodNotSupported('Method not supported', strtoupper($requestMethod));
            $errorHeader = 'HTTP/1.1 405 Method Not Allowed';
        }

        //Send Output
        if (!($errorMessage)) {
            $this->sendOutput($outputModel, array('Content-Type: application/json', 'HTTP/1.1 204 No content'));
        }
        else {
            $this->sendOutput(
                json_encode(
                array('error' => $errorMessage)
                , JSON_PRETTY_PRINT), array('Content-Type: application/json', $errorHeader));
        }
    }
}
