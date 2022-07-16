<?php

namespace app\api\controller;

use App\api\controller\BaseController;

use App\api\model\StorageInputModel;
use App\api\model\StorageIdInputModel;
use App\api\model\StoredProductInputModel;
use App\api\model\ProductIdInputModel;

use App\api\exceptionHandler\ApiExceptionHandler;

use App\core\shared\Utilities;

use App\domain\exception\BusinessException;
use App\domain\exception\ConnectionException;
use App\domain\exception\EntityNotFoundException;
use App\domain\exception\MYSQLTransactionException;

use App\domain\service\StorageService;

use Hateoas\HateoasBuilder;
use Hateoas\UrlGenerator\CallableUrlGenerator;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Pagerfanta;
use Hateoas\Representation\CollectionRepresentation;


class StorageController extends BaseController
{

    private $service;
    private $hateoas;

    public function __construct()
    {
        $this->service = new StorageService();

        $urlGenerator = new CallableUrlGenerator(function ($route, $parameters) {
            return $route . '?' . http_build_query($parameters);
        });

        $this->hateoas = HateoasBuilder::create()->setUrlGenerator(null, $urlGenerator)->build();

    }


    /**
     * @method void create()
     * 
     * "/v1/endpoints/storages/create" Endpoint 
     * 
     * Creates a new storage
     */
    public function create()
    {

        $errorMessage = '';

        try {

            $data = json_decode(file_get_contents("php://input"));

            if (!isset($data) || !isset($data->designation)) {
                throw new BusinessException('Please provide valid value for designation [Not Null and Not Blank].');
            }
            else {
                $designation = isset($data->designation) ? $this->clean($data->designation) : '';
            }

            if (!$designation || substr($designation, 0, 1) === ' ') {
                throw new BusinessException('Please provide valid value for designation [Not Null and Not Blank].');
            }

            $model = new StorageInputModel();
            $model->setDesignation($designation);

            $outputModel = $this->hateoas->serialize(
                Utilities::toStorageOutputModel($this->service->create($model)), 'json');

        }
        catch (ConnectionException $connectionException) {
            $errorMessage = ApiExceptionHandler::handleConnectionException($connectionException);
            $errorHeader = 'HTTT/1.1 500 Internal Server Error';
        }
        catch (BusinessException $businessException) {
            $errorMessage = ApiExceptionHandler::handleBusinessException($businessException);
            $errorHeader = 'HTTP/1.1 400 Bad Request';
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
     * "/v1/endpoints/Storages/{id}" Endpoint 
     * 
     * Get a storage by a given ID
     * 
     * You can either get a storage by its designation 
     * or code passing one of this variable as a query parameters
     */

    public function findOne($id = null)
    {
        $errorMessage = '';

        try {

            $id = filter_var($id, FILTER_SANITIZE_NUMBER_INT, array("flags" => FILTER_VALIDATE_INT));

            if ($id) {
                $outputModel = $this->hateoas->serialize(
                    Utilities::toStorageOutputModel($this->service->findOne($id)), 'json');
            }
            else {
                throw new BusinessException('Please provide valid value for id [Not Null and Not Blank or Greater than 0].');
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
        catch (BusinessException $businessException) {
            $errorMessage = ApiExceptionHandler::handleBusinessException($businessException);
            $errorHeader = 'HTTP/1.1 400 Bad Request';
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
     * "/v1/endpoints/storages" Endpoint 
     * 
     * Get a list of storages
     */

    public function find($page, $limit, $sorts)
    {
        $errorMessage = '';

        try {

            $adapter = new ArrayAdapter(
                Utilities::toStorageOutputCollectionModel($this->service->findAll($page, $limit, $sorts)));

            $totalQuantity = $this->service->getStoragesExistance();

            $pager = new Pagerfanta($adapter);

            $paginatedCollection = new \Hateoas\Representation\PaginatedRepresentation(
                new CollectionRepresentation($pager->getCurrentPageResults()),
                '/v1/endpoints/storages',
                array(),
                $page,
                $limit,
                ceil($totalQuantity / $limit),
                'page',
                'limit',
                false,
                $totalQuantity
                );

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
     * "/v1/endpoints/storages/{id}/update" Endpoint 
     * 
     * Updates a storage with the given ID
     */

    public function update(int $id)
    {
        $errorMessage = '';

        try {

            $data = json_decode(file_get_contents("php://input"));

            $id = isset($id) ? 
                filter_var($this->clean($id), FILTER_VALIDATE_INT) : 0;

            if (!isset($data) || !isset($data->designation)) {
                throw new BusinessException('Please provide valid value for designation [Not Null and Not Blank].');
            }
            else {
                $designation = isset($data->designation) ? $this->clean($data->designation) : '';
            }

            if (!$designation || substr($designation, 0, 1) === ' ') {
                throw new BusinessException('Please provide valid value for designation [Not Null and Not Blank].');
            }

            if (!$id || !$designation || substr($designation, 0, 1) === ' ') {
                throw new BusinessException('Please provide valid values for {id}, {designation}');
            }

            $model = new StorageInputModel();
            $model->setDesignation($designation);

            $outputModel = $this->hateoas->serialize(Utilities::toStorageOutputModel(
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
     * "/v1/endpoints/storages/{id}/delete" Endpoint 
     * 
     * Delete a storage with the given ID
     */

    public function delete($id)
    {
        $errorMessage = '';

        try {

            $id = ($id) ? filter_var($id, FILTER_SANITIZE_NUMBER_INT, array("flags" => FILTER_VALIDATE_INT)) : 0;

            if (!$id) {
                throw new BusinessException('Please provide valid value for id [Not Null and Not Blank or Greater than 0].');
            }

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
        catch (BusinessException $businessException) {
            $errorMessage = ApiExceptionHandler::handleBusinessException($businessException);
            $errorHeader = 'HTTP/1.1 400 Bad Request';
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
     * @method void add($storageId)
     * 
     * "/v1/endpoints/storages/{storageId}/products/add" Endpoint 
     * 
     * Adds a product to a storage with the given ID
     */
    public function add($storageId)
    {
        $errorMessage = '';

        try {

            $data = json_decode(file_get_contents("php://input"));

            $storageModel = new StorageIdInputModel();
            $storageModel->setId(filter_var($this->clean($storageId), FILTER_VALIDATE_INT));

            if (isset($data)) {
                $productId = isset($data->productId) ? filter_var($this->clean($data->productId), FILTER_VALIDATE_INT) : 0;
                $quantity = isset($data->quantity) ? filter_var($this->clean($data->quantity), FILTER_VALIDATE_FLOAT) : 0;

            }
            else {
                throw new BusinessException('Please provide valid values for productId and quantity [Not Null and Not Blank or Greater than 0].');
            }

            if (!$productId && !$quantity) {
                throw new BusinessException('Please provide valid values for productId and quantity [Not Null and Not Blank or Greater than 0].');
            }

            if (!$productId) {
                throw new BusinessException('Please provide valid value for productId [Not Null and Not Blank or Greater than 0]');
            }

            if (!$quantity) {
                throw new BusinessException('Please provide valid value for quantity [Not Null and Not Blank or Greater than 0]');
            }

            $productModel = new ProductIdInputModel();
            $productModel->setId($productId);

            $model = new StoredProductInputModel();
            $model->setStorage($storageModel);
            $model->setProduct($productModel);
            $model->setQuantity($quantity);

            $outputModel = $this->hateoas->serialize(Utilities::toStoredProductOutputModel(
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
     * @method void view($storageId, $productId)
     * 
     * "/v1/endpoints/storages/{storageId}/products/{productId}" Endpoint 
     * 
     * Adds a product to a storage with the given ID
     */
    public function view($storageId, $productId)
    {
        $errorMessage = '';

        try {

            $storageId = filter_var($storageId, FILTER_VALIDATE_INT) ? 
                filter_var($storageId, FILTER_VALIDATE_INT) : 0;

            $productId = filter_var($productId, FILTER_VALIDATE_INT) ? 
                filter_var($productId, FILTER_VALIDATE_INT) : 0;

            if ($productId && $storageId) {
                $outputModel = $this->hateoas->serialize(Utilities::toStoredProductOutputModel(
                    $this->service->viewProduct($productId, $storageId)), 'json');
            }
            else {
                throw new BusinessException('Please provide valid values for productId and storageId Not Null and Not Blank or Greater than 0].');
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
     * @method void list($storageId)
     * 
     * "/v1/endpoints/storages/{storageId}/products/" Endpoint 
     * 
     * Adds a product to a storage with the given ID
     */

    public function list($storageId, $page, $limit, $sorts)
    {

        $errorMessage = '';

        try {

            $id = filter_var($this->clean($storageId), FILTER_VALIDATE_INT);

            if (!$id) {
                throw new BusinessException('Please provide valid value for id [Not Null and Not Blank or Greater than 0].');
            }

            $adapter = new ArrayAdapter(Utilities::toStoredProductOutputCollectionModel(
                $this->service->listAll($id, $page, $limit, $sorts)
            ));

            $totalQuantity = $this->service->getStoredProductsExistance($id);

            $pager = new Pagerfanta($adapter);

            $paginatedCollection = new \Hateoas\Representation\PaginatedRepresentation(
                new CollectionRepresentation($pager->getCurrentPageResults()),
                '/v1/endpoints/storages' . $id . '/products',
                array(),
                $page,
                $limit,
                ceil($totalQuantity / $limit),
                'page',
                'limit',
                false,
                $totalQuantity
                );

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
        catch (BusinessException $businessException) {
            $errorMessage = ApiExceptionHandler::handleBusinessException($businessException);
            $errorHeader = 'HTTP/1.1 400 Bad Request';
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
     * @method void edit($storageId, $productId)
     * 
     * "/v1/endpoints/storages/{storageId}/products/{productId}/edit" Endpoint 
     * 
     * Updates a product from a storage with the given ID
     */

    public function edit($storageId, $productId)
    {

        $errorMessage = '';

        try {

            $data = json_decode(file_get_contents("php://input"));

            if (isset($data)) {
                $quantity = isset($data->quantity) ? filter_var($this->clean($data->quantity), FILTER_VALIDATE_FLOAT) : 0;

            }
            else {
                throw new BusinessException('Please provide valid value for quantity [Not Null and Not Blank or Greater than 0].');
            }

            if (!$quantity) {
                throw new BusinessException('Please provide valid value for quantity [Not Null and Not Blank or Greater than 0]');
            }

            $productModel = new ProductIdInputModel();
            $productModel->setId(filter_var($this->clean($productId), FILTER_VALIDATE_INT));

            $storageModel = new StorageIdInputModel();
            $storageModel->setId(filter_var($this->clean($storageId), FILTER_VALIDATE_INT));

            $model = new StoredProductInputModel();
            $model->setProduct($productModel);
            $model->setStorage($storageModel);
            $model->setQuantity($quantity);

            $outputModel = $this->hateoas->serialize(Utilities::toStoredProductOutputModel(
                $this->service->edit($model)), 'json');
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
     * @method void remove($storageId, $productId)
     * 
     * "/v1/endpoints/storages/{storageId}/products/{productId}/remove" Endpoint 
     * 
     * Removes a product from a storage with the given ID
     */

    public function remove($storageId, $productId)
    {

        $errorMessage = '';

        try {

            $productId = filter_var($this->clean($productId), FILTER_VALIDATE_INT);

            $storageId = filter_var($this->clean($storageId), FILTER_VALIDATE_INT);

            if (!$productId || !$storageId) {
                throw new BusinessException('Please provide valid values for productId and storageId [Not Null and Not Blank or Greater than 0].');
            }

            if ($this->service->remove($storageId, $productId)) {
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

    public function methodNotSupported($requestMethod)
    {
        $errorMessage = ApiExceptionHandler::handleMethodNotSupported('Method not supported', $requestMethod);
        $errorHeader = 'HTTP/1.1 405 Method Not Allowed';

        $this->sendOutput(
            json_encode(array(
            'error' => $errorMessage
        )), array(
            'Content-Type: application/json', $errorHeader
        )
        );
    }
}
