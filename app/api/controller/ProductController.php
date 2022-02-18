<?php

namespace app\api\controller;

use App\api\controller\BaseController;

use App\api\model\ProductInputModel;
use App\api\model\ProductIdInputModel;
use App\api\model\ValidityInputModel;

use App\api\exceptionHandler\ApiExceptionHandler;

use App\core\shared\Utilities;

use App\domain\exception\BusinessException;
use App\domain\exception\ConnectionException;
use App\domain\exception\EntityNotFoundException;
use App\domain\exception\MYSQLTransactionException;

use App\domain\service\ProductService;

use Hateoas\HateoasBuilder;
use Hateoas\UrlGenerator\CallableUrlGenerator;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Pagerfanta;
use Hateoas\Representation\Factory\PagerfantaFactory;
use Hateoas\Configuration\Route;

class ProductController extends BaseController
{
    private $service;
    private $hateoas;

    public function __construct()
    {
        $this->service = new ProductService();

        $urlGenerator = new CallableUrlGenerator(function ($route, $parameters) {
            return $route . '?' . http_build_query($parameters);
        });

        $this->hateoas = HateoasBuilder::create()->setUrlGenerator(null, $urlGenerator)->build();

    }


    /**
     * @method void create()
     * 
     * "/v1/endpoints/products/create" Endpoint 
     * 
     * Creates a new product
     */
    public function create()
    {

        $errorMessage = '';
        $requestMethod = filter_input(INPUT_SERVER, 'REQUEST_METHOD', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        if (strtoupper($requestMethod) == 'POST') {
            try {

                $data = json_decode(file_get_contents("php://input"));

                $description = $this->clean($data->description);
                $unit = $this->clean($data->unit);

                $model = new ProductInputModel();
                $model->setDescription($description);
                $model->setUnit($unit);

                $outputModel = $this->hateoas->serialize(
                    Utilities::toProductOutputModel($this->service->create($model)), 'json');

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
     * "/v1/endpoints/Products/{id}" Endpoint 
     * 
     * Get a product by a given ID
     * 
     * You can either get a product by its description 
     * or unit passing one of this variable as a query parameters
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
                        Utilities::toProductOutputModel($this->service->findOne($id)), 'json');
                }
                else {
                    $query = parse_url(filter_input(INPUT_SERVER, 'REQUEST_URI'), PHP_URL_QUERY);
                    $toArrayQuery = parse_str($query, $arrayQuery);

                    if (count($arrayQuery)) {
                        if (isset($arrayQuery['description'])) {
                            $description = filter_var($arrayQuery['description'], FILTER_SANITIZE_SPECIAL_CHARS);

                            $outputModel = $this->hateoas->serialize(
                                Utilities::toProductOutputModel(
                                $this->service->findByDescription($description)), 'json');

                        }
                        else if (isset($arrayQuery['unit'])) {
                            $options = array("flags" => FILTER_VALIDATE_INT);
                            $unit = filter_var($arrayQuery['unit'], FILTER_SANITIZE_SPECIAL_CHARS, $options);

                            if ($unit) {

                                $adapter = new ArrayAdapter(
                                    Utilities::toProductOutputCollectionModel($this->service->findByUnit($unit)));

                                $pager = new Pagerfanta($adapter);

                                if ($adapter->getNbResults() > 10) {
                                    $pager->setMaxPerPage(8);
                                }
                                else {

                                    $pager->setMaxPerPage(4);
                                }

                                $pagerFantaFactory = new PagerfantaFactory();

                                $paginatedCollection = $pagerFantaFactory->createRepresentation(
                                    $pager, new Route('/v1/endpoints/products/', array("unit"=>$unit)));

                                $outputModel = $this->hateoas->serialize($paginatedCollection, 'json');
                            }
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
     * "/v1/endpoints/products" Endpoint 
     * 
     * Get a list of products
     */

    public function find()
    {
        $errorMessage = '';
        $requestMethod = filter_input(INPUT_SERVER, 'REQUEST_METHOD', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        if (strtoupper($requestMethod) == 'GET') {
            try {

                $adapter = new ArrayAdapter(
                    Utilities::toProductOutputCollectionModel($this->service->findAll()));

                $pager = new Pagerfanta($adapter);

                if ($adapter->getNbResults() > 10) {
                    $pager->setMaxPerPage(8);
                }
                else {

                    $pager->setMaxPerPage(4);
                }

                $pagerFantaFactory = new PagerfantaFactory();

                $paginatedCollection = $pagerFantaFactory->createRepresentation(
                    $pager, new Route('/v1/endpoints/products/', array()));

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
     * "/v1/endpoints/products/{id}/update" Endpoint 
     * 
     * Updates a product with the given ID
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

                $description = $this->clean($data->description);
                $unit = $this->clean($data->unit);

                $model = new ProductInputModel();
                $model->setDescription($description);
                $model->setUnit($unit);

                $outputModel = $this->hateoas->serialize(Utilities::toProductOutputModel(
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
     * "/v1/endpoints/products/{id}/delete" Endpoint 
     * 
     * Delete a product with the given ID
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


    /**
     * @method void add($productId)
     * 
     * "/v1/endpoints/products/{productId}/validities/add" Endpoint 
     * 
     * Adds a validity to a product with the given ID
     */
    public function add($productId)
    {
        $errorMessage = '';
        $requestMethod = filter_input(INPUT_SERVER, 'REQUEST_METHOD', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        if (strtoupper($requestMethod) == 'POST') {

            try {

                $data = json_decode(file_get_contents("php://input"));

                $ProductModel = new ProductIdInputModel();
                $ProductModel->setId(filter_var($this->clean($productId)), FILTER_VALIDATE_INT);


                $expirationDate = $this->clean($data->expirationDate);
                $quantity = filter_var($this->clean($data->quantity), FILTER_VALIDATE_FLOAT);

                $model = new ValidityInputModel();
                $model->setProduct($ProductModel);
                $model->setExpirationDate($expirationDate);
                $model->setQuantity($quantity);

                $outputModel = $this->hateoas->serialize(Utilities::toValidityOutputModel(
                    $this->service->add($model)
                ), 'json');
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
            $this->sendOutput(
                $outputModel, array(
                'Content-Type: application/json', 'HTTP/1.1 201 Created')
            );
        }
        else {
            $this->sendOutput(
                json_encode(array(
                'error' => $errorMessage
            )), array(
                'Content-Type: application/json', $errorHeader
            )
            );
        }
    }

    /**
     * @method void view($productId, $validityId)
     * 
     * "/v1/endpoints/products/{productId}/validities/{validitiesId}" Endpoint 
     * 
     * View a validity to a product with the given ID
     */
    public function view($productId, $validityId)
    {
        $errorMessage = '';
        $requestMethod = filter_input(INPUT_SERVER, 'REQUEST_METHOD', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        if (strtoupper($requestMethod) == 'GET') {

            try {

                $productId = filter_var($productId, FILTER_VALIDATE_INT) ? 
                    filter_var($productId, FILTER_VALIDATE_INT) : 0;

                $validityId = filter_var($validityId, FILTER_VALIDATE_INT) ? 
                    filter_var($validityId, FILTER_VALIDATE_INT) : 0;

                if ($productId && $validityId) {
                    $outputModel = $this->hateoas->serialize(Utilities::toValidityOutputModel(
                        $this->service->findOneValidity($productId, $validityId)), 'json');
                }
                else {
                    $errorMessage = 'Provide valid parameters. Both parameters must be integer and greater than 0 {'
                        . $productId . ', ' . $validityId . '}';
                    $errorHeader = 'HTTP/1.1 400 Bad Request';
                }
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
            $this->sendOutput(
                $outputModel, array(
                'Content-Type: application/json', 'HTTP/1.1 200 OK')
            );
        }
        else {
            $this->sendOutput(
                json_encode(array(
                'error' => $errorMessage
            )), array(
                'Content-Type: application/json', $errorHeader
            )
            );
        }
    }

    /**
     * @method void listValidities($productId)
     * 
     * "/v1/endpoints/products/{productId}/validities/" Endpoint 
     * 
     * Adds a validity to a product with the given ID
     */

    public function listValidities($productId)
    {

        $errorMessage = '';
        $requestMethod = filter_input(INPUT_SERVER, 'REQUEST_METHOD', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        if (strtoupper($requestMethod) == 'GET') {
            try {

                $id = filter_var($this->clean($productId), FILTER_VALIDATE_INT);

                $adapter = new ArrayAdapter(Utilities::toValidityOutputCollectionModel(
                    $this->service->listValidities($productId)
                ));

                $pager = new Pagerfanta($adapter);

                $factory = new PagerfantaFactory();

                $paginatedCollection = $factory->createRepresentation(
                    $pager, new Route('/v1/endpoints/products/' . $productId . '/validities/', array()));

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
            $this->sendOutput(
                $outputModel, array(
                'Content-Type: application/json', 'HTTP/1.1 200 Ok')
            );
        }
        else {
            $this->sendOutput(json_encode(
                array(
                'error' => $errorMessage
            ), JSON_PRETTY_PRINT), array(
                'Content-Type: application/json', $errorHeader)
            );
        }
    }

    /**
     * @method void edit($productId, $validitiesId)
     * 
     * "/v1/endpoints/products/{productId}/validities/{validityId}/edit" Endpoint 
     * 
     * Updates a validity from a product with the given ID
     */

    public function edit($productId, $validityId)
    {

        $errorMessage = '';
        $requestMethod = filter_input(INPUT_SERVER, 'REQUEST_METHOD', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        if (strtoupper($requestMethod) == 'PUT') {

            try {

                $data = json_decode(file_get_contents("php://input"));

                $productModel = new ProductIdInputModel();
                $productModel->setId(filter_var($this->clean($productId), FILTER_VALIDATE_INT));

                $validityId = filter_var($this->clean($validityId), FILTER_VALIDATE_INT);

                $expirationDate = $this->clean($data->expirationDate);
                $quantity = filter_var($this->clean($data->quantity), FILTER_VALIDATE_FLOAT);

                $model = new ValidityInputModel();
                $model->setProduct($productModel);
                $model->setExpirationDate($expirationDate);
                $model->setQuantity($quantity);

                $outputModel = $this->hateoas->serialize(Utilities::toValidityOutputModel(
                    $this->service->edit($validityId, $model)), 'json');
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
            $this->sendOutput(
                $outputModel, array(
                'Content-Type: application/json', 'HTTP/1.1 200 OK')
            );
        }
        else {
            $this->sendOutput(
                json_encode(array(
                'error' => $errorMessage
            )), array(
                'Content-Type: application/json', $errorHeader
            )
            );
        }
    }

    /**
     * @method void remove($productId, $validityId)
     * 
     * "/v1/endpoints/products/{productId}/validities/{validityId}/remove" Endpoint 
     * 
     * Removes a validity from a product with the given ID
     */

    public function remove($productId, $validityId)
    {

        $errorMessage = '';
        $requestMethod = filter_input(INPUT_SERVER, 'REQUEST_METHOD', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        if (strtoupper($requestMethod) == 'DELETE') {

            try {

                $productId = filter_var($this->clean($productId), FILTER_VALIDATE_INT);
                $validityId = filter_var($this->clean($validityId), FILTER_VALIDATE_INT);

                if ($this->service->remove($validityId, $productId)) {
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
            $this->sendOutput(
                $outputModel, array(
                'Content-Type: application/json', 'HTTP/1.1 204 No Content')
            );
        }
        else {
            $this->sendOutput(
                json_encode(array(
                'error' => $errorMessage
            )), array(
                'Content-Type: application/json', $errorHeader
            )
            );
        }
    }


    /**
     * @method void listSuppliers($productId)
     * 
     * "/v1/endpoints/products/{productId}/suppliers/" Endpoint 
     * 
     * Get a list of supplier of the product with the given ID
     */

    public function listSuppliers($productId)
    {

        $errorMessage = '';
        $requestMethod = filter_input(INPUT_SERVER, 'REQUEST_METHOD', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        if (strtoupper($requestMethod) == 'GET') {
            try {

                $id = filter_var($this->clean($productId), FILTER_VALIDATE_INT);

                $adapter = new ArrayAdapter(Utilities::toSupplierOutputCollectionModel(
                    $this->service->listSuppliers($productId)
                ));

                $pager = new Pagerfanta($adapter);

                $factory = new PagerfantaFactory();

                $paginatedCollection = $factory->createRepresentation(
                    $pager, new Route('/v1/endpoints/products/' . $productId . '/suppliers/', array()));

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
            $this->sendOutput(
                $outputModel, array(
                'Content-Type: application/json', 'HTTP/1.1 200 Ok')
            );
        }
        else {
            $this->sendOutput(json_encode(
                array(
                'error' => $errorMessage
            ), JSON_PRETTY_PRINT), array(
                'Content-Type: application/json', $errorHeader)
            );
        }
    }


    /**
     * @method void listStorages($productId)
     * 
     * "/v1/endpoints/products/{productId}/storages/" Endpoint 
     * 
     * Get a list of storages of the product with the given ID
     */

    public function listStorages($productId)
    {

        $errorMessage = '';
        $requestMethod = filter_input(INPUT_SERVER, 'REQUEST_METHOD', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        if (strtoupper($requestMethod) == 'GET') {
            try {

                $id = filter_var($this->clean($productId), FILTER_VALIDATE_INT);

                $adapter = new ArrayAdapter(Utilities::toStorageOutputCollectionModel(
                    $this->service->listStorages($productId)
                ));

                $pager = new Pagerfanta($adapter);

                $factory = new PagerfantaFactory();

                $paginatedCollection = $factory->createRepresentation(
                    $pager, new Route('/v1/endpoints/products/' . $productId . '/suppliers/', array()));

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
            $this->sendOutput(
                $outputModel, array(
                'Content-Type: application/json', 'HTTP/1.1 200 Ok')
            );
        }
        else {
            $this->sendOutput(json_encode(
                array(
                'error' => $errorMessage
            ), JSON_PRETTY_PRINT), array(
                'Content-Type: application/json', $errorHeader)
            );
        }
    }

}
