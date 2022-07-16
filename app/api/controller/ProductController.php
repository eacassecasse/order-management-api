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
use Hateoas\Representation\CollectionRepresentation;


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
     * "POST /v1/endpoints/products" Endpoint 
     * 
     * Creates a new product
     */
    public function create()
    {

        $errorMessage = '';

        try {

            $data = json_decode(file_get_contents("php://input"));

            if (isset($data)) {
                $description = isset($data->description) ? $this->clean($data->description) : '';
                $unit = isset($data->unit) ? $this->clean($data->unit) : '';
            }
            else {
                throw new BusinessException('Please provide valid values for description and unit [Not Null and Not Blank].');
            }

            if ((!$description && !$unit) ||
            (substr($description, 0, 1) === ' ' && !$unit) ||
            (!$description && substr($unit, 0, 1) === ' ') ||
            (substr($description, 0, 1) === ' ' && substr($unit, 0, 1) === ' ')) {
                throw new BusinessException('Please provide valid values for description and unit [Not Null and Not Blank].');
            }

            if (!$description || substr($description, 0, 1) === ' ') {
                throw new BusinessException('Please provide a valid value for description [Not Null and Not Blank].');
            }
            if (!$unit || substr($unit, 0, 1) === ' ') {
                throw new BusinessException('Please provide valid value for unit [Not Null and Not Blank].');
            }

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
     * "GET /v1/endpoints/products/{id}" Endpoint 
     * 
     * Get a product by a given ID
     */

    public function findOne($id)
    {
        $errorMessage = '';


        try {

            $id = filter_var($this->clean($id), FILTER_VALIDATE_INT);

            if ($id) {
                $outputModel = $this->hateoas->serialize(
                    Utilities::toProductOutputModel($this->service->findOne($id)), 'json');
            }
            else {
                throw new BusinessException("Please provide valid values for ID [Not Blank|Not Empty|Integer|Greater than 0]!");
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
     * @method void find($page, $limit, $sorts)
     * 
     * "GET /v1/endpoints/products" Endpoint 
     * 
     * Get a list of products
     */

    public function find($page, $limit, $sorts)
    {
        $errorMessage = '';

        try {

            $totalQuantity = $this->service->getProductExistance();

            $adapter = new ArrayAdapter(
                Utilities::toProductOutputCollectionModel($this->service->findAll($page, $limit, $sorts)));


            $pager = new Pagerfanta($adapter);

            $paginatedCollection = new \Hateoas\Representation\PaginatedRepresentation(
                new CollectionRepresentation($pager->getCurrentPageResults()),
                '/v1/endpoints/products',
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
     * "PUT /v1/endpoints/products/{id}" Endpoint 
     * 
     * Updates a product with the given ID
     */

    public function update(int $id)
    {
        $errorMessage = '';
        try {

            $data = json_decode(file_get_contents("php://input"));

            if ($id === 0) {
                throw new BusinessException('Please provided a valid value for id [Greater than 0 || Not Null]');
            }

            if (isset($data)) {
                $description = isset($data->description) ? $this->clean($data->description) : '';
                $unit = isset($data->unit) ? $this->clean($data->unit) : '';
            }
            else {
                throw new BusinessException('Please provide valid values for description and unit [Not Null and Not Blank].');
            }

            if ((!$description && !$unit) ||
            (substr($description, 0, 1) === ' ' && !$unit) ||
            (!$description && substr($unit, 0, 1) === ' ') ||
            (substr($description, 0, 1) === ' ' && substr($unit, 0, 1) === ' ')) {
                throw new BusinessException('Please provide valid values for description and unit [Not Null and Not Blank].');
            }

            if (!$description || substr($description, 0, 1) === ' ') {
                throw new BusinessException('Please provide a valid value for description [Not Null and Not Blank].');
            }
            if (!$unit || substr($unit, 0, 1) === ' ') {
                throw new BusinessException('Please provide valid value for unit [Not Null and Not Blank].');
            }

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
     * "DELETE /v1/endpoints/products/{id}/" Endpoint 
     * 
     * Delete a product with the given ID
     */

    public function delete($id)
    {
        $errorMessage = '';

        try {

            $id = ($id) ? filter_var($id, FILTER_SANITIZE_NUMBER_INT, array("flags" => FILTER_VALIDATE_INT)) : 0;

            if ($id === 0) {
                throw new BusinessException('Please provide a valid value for id [Greater than 0 and Not Null]');
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
     * @method void add($productId)
     * 
     * "POST /v1/endpoints/products/{productId}/validities/" Endpoint 
     * 
     * Adds a validity to a product with the given ID
     */
    public function add($productId)
    {
        $errorMessage = '';


        try {

            $data = json_decode(file_get_contents("php://input"));

            if ($productId === 0) {
                throw new BusinessException('Please provided a valid value for product id [Greater than 0 || Not Null]');
            }

            if (isset($data)) {
                $expirationDate = isset($data->expirationDate) ? $this->clean($data->expirationDate) : '';
                $quantity = isset($data->quantity) ? $this->clean($data->quantity) : '';
            }
            else {
                throw new BusinessException('Please provide valid values for expirationDate and quantity [Not Null and Not Blank or Greater than 0].');
            }

            if ((!$expirationDate && !$quantity) ||
            (substr($expirationDate, 0, 1) === ' ' && !$quantity) ||
            (!$expirationDate && substr($quantity, 0, 1) === ' ') ||
            (substr($expirationDate, 0, 1) === ' ' && substr($quantity, 0, 1) === ' ')) {
                throw new BusinessException('Please provide valid values for expirationDate and quantity [Not Null and Not Blank or Greater than 0].');
            }

            if (!$expirationDate || substr($expirationDate, 0, 1) === ' ') {
                throw new BusinessException('Please provide a valid value for expirationDate [Not Null and Not Blank].');
            }
            if (!$quantity || substr($quantity, 0, 1) === ' ') {
                throw new BusinessException('Please provide valid value for quantity [Not Null and Not Blank or Greater than 0].');
            }

            $productModel = new ProductIdInputModel();
            $productModel->setId(filter_var($this->clean($productId), FILTER_VALIDATE_INT));


            $expirationDate = $this->clean($data->expirationDate);
            $quantity = filter_var($this->clean($data->quantity), FILTER_VALIDATE_FLOAT);

            if (!$productModel->getId() || !$expirationDate || !$quantity || substr($expirationDate, 0, 1) === ' ') {
                throw new BusinessException("Provide valid values for {productId}, {expirationDate} and {quantity}");
            }

            $model = new ValidityInputModel();
            $model->setProduct($productModel);
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
     * "GET /v1/endpoints/products/{productId}/validities/{validitiesId}" Endpoint 
     * 
     * View a validity to a product with the given ID
     */
    public function view($productId, $validityId)
    {
        $errorMessage = '';


        try {

            $productId = filter_var($productId, FILTER_VALIDATE_INT) ? 
                filter_var($productId, FILTER_VALIDATE_INT) : 0;

            $validityId = filter_var($validityId, FILTER_VALIDATE_INT) ? 
                filter_var($validityId, FILTER_VALIDATE_INT) : 0;

            if ($productId === 0) {
                throw new BusinessException('Please provide valid values for productId [Not Null and Not Blank or Greater than 0]');
            }

            if ($validityId === 0) {
                throw new BusinessException('Please provide valid values for validityId [Not Null and Not Blank or Greater than 0]');
            }

            if ($productId === 0 && $validityId === 0) {
                throw new BusinessException('Please provide valid values for productId and validityId [Not Null and Not Blank or Greater than 0]');
            }

            $outputModel = $this->hateoas->serialize(Utilities::toValidityOutputModel(
                $this->service->viewValidity($productId, $validityId)), 'json');

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
     * @method void listValidities($productId, $page, $limit, $sorts)
     * 
     * "GET /v1/endpoints/products/{productId}/validities/" Endpoint 
     * 
     * Adds a validity to a product with the given ID
     */

    public function listValidities($productId, $page, $limit, $sorts)
    {

        $errorMessage = '';

        try {

            $id = filter_var($this->clean($productId), FILTER_VALIDATE_INT);

            $adapter = new ArrayAdapter(Utilities::toValidityOutputCollectionModel(
                $this->service->listAllValidities($id, $page, $limit, $sorts)
            ));

            $totalQuantity = $this->service->getValiditiesExistance($id);

            $pager = new Pagerfanta($adapter);

            $paginatedCollection = new \Hateoas\Representation\PaginatedRepresentation(
                new CollectionRepresentation($pager->getCurrentPageResults()),
                '/v1/endpoints/products/' . $id . '/validities',
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
     * "PUT /v1/endpoints/products/{productId}/validities/{validityId}" Endpoint 
     * 
     * Updates a validity from a product with the given ID
     */

    public function edit($productId, $validityId)
    {

        $errorMessage = '';

        try {

            $data = json_decode(file_get_contents("php://input"));

            $productModel = new ProductIdInputModel();
            $productModel->setId(filter_var($this->clean($productId), FILTER_VALIDATE_INT));

            $validityId = filter_var($this->clean($validityId), FILTER_VALIDATE_INT);

            if ($productId === 0) {
                throw new BusinessException('Please provided a valid value for product id [Greater than 0 || Not Null]');
            }

            if (isset($data)) {
                $expirationDate = isset($data->expirationDate) ? $this->clean($data->expirationDate) : '';
                $quantity = isset($data->quantity) ? filter_var($this->clean($data->quantity), FILTER_VALIDATE_FLOAT) : '';
            }
            else {
                throw new BusinessException('Please provide valid values for expirationDate and quantity [Not Null and Not Blank or Greater than 0].');
            }

            if ((!$expirationDate && !$quantity) ||
            (substr($expirationDate, 0, 1) === ' ' && !$quantity) ||
            (!$expirationDate && substr($quantity, 0, 1) === ' ') ||
            (substr($expirationDate, 0, 1) === ' ' && substr($quantity, 0, 1) === ' ')) {
                throw new BusinessException('Please provide valid values for expirationDate and quantity [Not Null and Not Blank or Greater than 0].');
            }

            if (!$expirationDate || substr($expirationDate, 0, 1) === ' ') {
                throw new BusinessException('Please provide a valid value for expirationDate [Not Null and Not Blank].');
            }
            if (!$quantity || substr($quantity, 0, 1) === ' ') {
                throw new BusinessException('Please provide valid value for quantity [Not Null and Not Blank or Greater than 0].');
            }

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
     * "DELETE /v1/endpoints/products/{productId}/validities/{validityId}" Endpoint 
     * 
     * Removes a validity from a product with the given ID
     */

    public function remove($productId, $validityId)
    {

        $errorMessage = '';

        try {

            $productId = filter_var($this->clean($productId), FILTER_VALIDATE_INT);
            $validityId = filter_var($this->clean($validityId), FILTER_VALIDATE_INT);

            if ($this->service->remove($productId, $validityId)) {
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

    /**
     * @method void viewSupplier($productId, $supplierId)
     * 
     * "GET /v1/endpoints/products/{productId}/suppliers/{supplierId}" Endpoint 
     * 
     * Get a supplier by its ID of the product with the given ID
     */

    public function viewSupplier($productId, $supplierId)
    {
        $errorMessage = '';

        try {

            $productId = filter_var($productId, FILTER_VALIDATE_INT) ? 
                filter_var($productId, FILTER_VALIDATE_INT) : 0;

            $supplierId = filter_var($supplierId, FILTER_VALIDATE_INT) ? 
                filter_var($supplierId, FILTER_VALIDATE_INT) : 0;

            if ($productId && $supplierId) {
                $outputModel = $this->hateoas->serialize(Utilities::toSupplierOutputModel(
                    $this->service->viewSupplier($productId, $supplierId)), 'json');
            }
            else {
                $errorMessage = 'Provide valid parameters. Both parameters must be integer and greater than 0 {'
                    . $productId . ', ' . $supplierId . '}';
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
     * @method void listSuppliers($productId, $page, $limit, $sorts)
     * 
     * "GET /v1/endpoints/products/{productId}/suppliers/" Endpoint 
     * 
     * Get a list of supplier of the product with the given ID
     */

    public function listSuppliers($productId, $page, $limit, $sorts)
    {

        $errorMessage = '';

        try {

            $id = filter_var($this->clean($productId), FILTER_VALIDATE_INT);

            $totalQuantity = $this->service->getSuppliersExistance($id);

            $adapter = new ArrayAdapter(Utilities::toSupplierOutputCollectionModel(
                $this->service->listSuppliers($id, $page, $limit, $sorts)
            ));

            $pager = new Pagerfanta($adapter);

            $paginatedCollection = new \Hateoas\Representation\PaginatedRepresentation(
                new CollectionRepresentation($pager->getCurrentPageResults()),
                '/v1/endpoints/products/' . $id . '/suppliers',
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
     * @method void listStorages($productId, $page, $limit, $sorts)
     * 
     * "GET /v1/endpoints/products/{productId}/storages/" Endpoint 
     * 
     * Get a list of storages of the product with the given ID
     */

    public function listStorages($productId, $page, $limit, $sorts)
    {

        $errorMessage = '';

        try {

            $id = filter_var($this->clean($productId), FILTER_VALIDATE_INT);

            $totalQuantity = $this->service->getStoragesExistance($id);

            $adapter = new ArrayAdapter(Utilities::toStorageOutputCollectionModel(
                $this->service->listStorages($id, $page, $limit, $sorts)
            ));

            $pager = new Pagerfanta($adapter);

            $paginatedCollection = new \Hateoas\Representation\PaginatedRepresentation(
                new CollectionRepresentation($pager->getCurrentPageResults()),
                '/v1/endpoints/products/' . $id . '/storages',
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
     * @method void viewStorage($productId, $storageId)
     * 
     * "/v1/endpoints/products/{productId}/storages/{storageId}" Endpoint 
     * 
     * Get a storage by its ID of the product with the given ID
     */

    public function viewStorage($productId, $storageId)
    {
        $errorMessage = '';

        try {

            $productId = filter_var($productId, FILTER_VALIDATE_INT) ? 
                filter_var($productId, FILTER_VALIDATE_INT) : 0;

            $storageId = filter_var($storageId, FILTER_VALIDATE_INT) ? 
                filter_var($storageId, FILTER_VALIDATE_INT) : 0;

            if ($productId && $storageId) {
                $outputModel = $this->hateoas->serialize(Utilities::toStorageOutputModel(
                    $this->service->viewStorage($productId, $storageId)), 'json');
            }
            else {
                $errorMessage = 'Provide valid parameters. Both parameters must be integer and greater than 0 {'
                    . $productId . ', ' . $storageId . '}';
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
