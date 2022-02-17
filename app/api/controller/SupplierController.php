<?php

namespace app\api\controller;

use App\api\controller\BaseController;

use App\api\model\SupplierInputModel;
use App\api\model\SupplierIdInputModel;
use App\api\model\SupplierProductInputModel;
use App\api\model\ProductIdInputModel;

use App\api\exceptionHandler\ApiExceptionHandler;

use App\core\shared\Utilities;

use App\domain\exception\BusinessException;
use App\domain\exception\ConnectionException;
use App\domain\exception\EntityNotFoundException;
use App\domain\exception\MYSQLTransactionException;

use App\domain\service\SupplierService;

use Hateoas\HateoasBuilder;
use Hateoas\UrlGenerator\CallableUrlGenerator;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Pagerfanta;
use Hateoas\Representation\Factory\PagerfantaFactory;
use Hateoas\Configuration\Route;




class SupplierController extends BaseController
{

    private $service;
    private $hateoas;

    public function __construct()
    {
        $this->service = new SupplierService();

        $urlGenerator = new CallableUrlGenerator(function ($route, $parameters) {
            return $route . '?' . http_build_query($parameters);
        });

        $this->hateoas = HateoasBuilder::create()->setUrlGenerator(null, $urlGenerator)->build();

    }


    /**
     * @method void create()
     * 
     * "/v1/endpoints/suppliers/create" Endpoint 
     * 
     * Creates a new supplier
     */
    public function create()
    {

        $errorMessage = '';
        $requestMethod = filter_input(INPUT_SERVER, 'REQUEST_METHOD', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        if (strtoupper($requestMethod) == 'POST') {
            try {

                $data = json_decode(file_get_contents("php://input"));

                $name = $this->clean($data->name);
                $vatNumber = $this->clean($data->vatNumber);

                $model = new SupplierInputModel();
                $model->setName($name);
                $model->setVatNumber($vatNumber);

                $outputModel = $this->hateoas->serialize(
                    Utilities::toSupplierOutputModel($this->service->create($model)), 'json');

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
     * "/v1/endpoints/suppliers/1" Endpoint 
     * 
     * Returns a supplier by a given ID
     * 
     * You can either get a supplier by his name 
     * or vatNumber passing one of this variable as a query parameters
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
                        Utilities::toSupplierOutputModel($this->service->findOne($id)), 'json');
                }
                else {
                    $query = parse_url(filter_input(INPUT_SERVER, 'REQUEST_URI'), PHP_URL_QUERY);
                    $toArrayQuery = parse_str($query, $arrayQuery);

                    if (count($arrayQuery)) {
                        if (isset($arrayQuery['name'])) {
                            $name = filter_var($arrayQuery['name'], FILTER_SANITIZE_SPECIAL_CHARS);

                            $outputModel = $this->hateoas->serialize(
                                Utilities::toSupplierOutputModel($this->service->findByName($name)), 'json');

                        }
                        else if (isset($arrayQuery['vatNumber'])) {
                            $options = array("flags" => FILTER_VALIDATE_INT);
                            $vatNumber = filter_var($arrayQuery['vatNumber'], FILTER_SANITIZE_SPECIAL_CHARS, $options);

                            if ($vatNumber) {
                                $outputModel = $this->hateoas->serialize(
                                    Utilities::toSupplierOutputModel($this->service->findByVatNumber($vatNumber)), 'json');
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
     * "/v1/endpoints/suppliers" Endpoint 
     * 
     * Returns a list of suppliers
     */

    public function find()
    {
        $errorMessage = '';
        $requestMethod = filter_input(INPUT_SERVER, 'REQUEST_METHOD', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        if (strtoupper($requestMethod) == 'GET') {
            try {

                $adapter = new ArrayAdapter(
                    Utilities::toSupplierOutputCollectionModel($this->service->findAll()));

                $pager = new Pagerfanta($adapter);

                if ($adapter->getNbResults() > 10) {
                    $pager->setMaxPerPage(8);
                }
                else {

                    $pager->setMaxPerPage(4);
                }

                $pagerFantaFactory = new PagerfantaFactory();

                $paginatedCollection = $pagerFantaFactory->createRepresentation(
                    $pager, new Route('/v1/endpoints/suppliers/', array()));

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
     * "/v1/endpoints/suppliers/1/update" Endpoint 
     * 
     * Updates a supplier with the given ID
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

                $name = $this->clean($data->name);
                $vatNumber = $this->clean($data->vatNumber);

                $model = new SupplierInputModel();
                $model->setName($name);
                $model->setVatNumber($vatNumber);

                $outputModel = $this->hateoas->serialize(Utilities::toSupplierOutputModel(
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
     * "/v1/endpoints/suppliers/{id}/delete" Endpoint 
     * 
     * Delete a supplier with the given ID
     */

    public function delete($id)
    {
        $errorMessage = '';
        $requestMethod = filter_input(INPUT_SERVER, 'REQUEST_METHOD', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        if (strtoupper($requestMethod) == 'DELETE') {

            try {

                $id = ($id) ? filter_var($id, FILTER_SANITIZE_NUMBER_INT, array("flags" => FILTER_VALIDATE_INT)) : 0;

                $outputModel = json_encode(
                    array('deleted' =>
                    ($this->service->delete($id)) ? 'sucessfully' : 'failed'
                ), JSON_PRETTY_PRINT);
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
     * @method void add($supplierId)
     * 
     * "/v1/endpoints/suppliers/{id}/products/add" Endpoint 
     * 
     * Adds a product to a supplier with the given ID
     */
    public function add($supplierId)
    {
        $errorMessage = '';
        $requestMethod = filter_input(INPUT_SERVER, 'REQUEST_METHOD', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        if (strtoupper($requestMethod) == 'POST') {

            try {

                $data = json_decode(file_get_contents("php://input"));

                $supplierModel = new SupplierIdInputModel();
                $supplierModel->setId(filter_var($this->clean($supplierId)), FILTER_VALIDATE_INT);

                $productModel = new ProductIdInputModel();
                $productModel->setId($this->clean($data->productId));

                $price = filter_var($this->clean($data->price), FILTER_VALIDATE_FLOAT);

                $model = new SupplierProductInputModel();
                $model->setSupplier($supplierModel);
                $model->setProduct($productModel);
                $model->setPrice($price);

                $outputModel = $this->hateoas->serialize(Utilities::toSupplierOutputModel(
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
     * @method void view($supplierId, $productId)
     * 
     * "/v1/endpoints/suppliers/{supplierId}/products/{productId}" Endpoint 
     * 
     * Adds a product to a supplier with the given ID
     */
    public function view($supplierId, $productId)
    {
        $errorMessage = '';
        $requestMethod = filter_input(INPUT_SERVER, 'REQUEST_METHOD', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        if (strtoupper($requestMethod) == 'GET') {

            try {

                $supplierId = filter_var($supplierId, FILTER_VALIDATE_INT) ? 
                    filter_var($supplierId, FILTER_VALIDATE_INT) : 0;

                $productId = filter_var($productId, FILTER_VALIDATE_INT) ? 
                    filter_var($productId, FILTER_VALIDATE_INT) : 0;

                if ($productId && $supplierId) {
                    $outputModel = $this->hateoas->serialize(Utilities::toSupplierProductOutputModel(
                        $this->service->findOneProduct($productId, $supplierId)), 'json');
                }
                else {
                    $errorMessage = 'Provide valid parameters. Both parameters must be integer and greater than 0 {'
                        . $supplierId . ', ' . $productId . '}';
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
     * @method void list($supplierId)
     * 
     * "/v1/endpoints/suppliers/{id}/products/" Endpoint 
     * 
     * Adds a product to a supplier with the given ID
     */

    public function list($supplierId)
    {

        $errorMessage = '';
        $requestMethod = filter_input(INPUT_SERVER, 'REQUEST_METHOD', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        if (strtoupper($requestMethod) == 'GET') {
            try {

                $id = filter_var($this->clean($supplierId), FILTER_VALIDATE_INT);

                $adapter = new ArrayAdapter(Utilities::toSupplierProductOutputCollectionModel(
                    $this->service->listProducts($supplierId)
                ));

                $pager = new Pagerfanta($adapter);

                $factory = new PagerfantaFactory();

                $paginatedCollection = $factory->createRepresentation(
                    $pager, new Route('/v1/endpoints/suppliers/' . $supplierId . '/products/', array()));

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
     * @method void edit($supplierId, $product)
     * 
     * "/v1/endpoints/suppliers/{supplierId}/products/{productId}/edit" Endpoint 
     * 
     * Adds a product to a supplier with the given ID
     */

    public function edit($supplierId, $productId)
    {

        $errorMessage = '';
        $requestMethod = filter_input(INPUT_SERVER, 'REQUEST_METHOD', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        if (strtoupper($requestMethod) == 'PUT') {

            try {

                $data = json_decode(file_get_contents("php://input"));

                $productModel = new ProductIdInputModel();
                $productModel->setId(filter_var($this->clean($productId)), FILTER_VALIDATE_INT);

                $supplierModel = new SupplierIdInputModel();
                $supplierModel->setId(filter_var($this->clean($supplierId)), FILTER_VALIDATE_INT);

                $price = filter_var($this->clean($data->price), FILTER_VALIDATE_FLOAT);

                $model = new SupplierProductInputModel();
                $model->setProduct($productModel);
                $model->setSupplier($supplierModel);
                $model->setPrice($price);

                $outputModel = $this->hateoas->serialize(Utilities::toSupplierProductOutputModelselect * from supplier_products;(
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

}
