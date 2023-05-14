<?php

namespace App\domain\service;

use App\api\model\ProductInputModel;
use App\api\model\ValidityInputModel;

use App\core\shared\Utilities;

use App\domain\exception\ConnectionException;
use App\domain\exception\BusinessException;
use App\domain\exception\EntityNotFoundException;

use App\domain\exception\MYSQLTransactionException;
use App\domain\model\Product;
use App\domain\model\StoredProduct;
use App\domain\model\SupplierProduct;
use App\domain\model\Validity;

use App\domain\repository\ProductRepository;
use App\domain\repository\StoredProductRepository;
use App\domain\repository\SupplierProductRepository;
use App\domain\repository\ValidityRepository;
use app\domain\model\Supplier;
use app\domain\model\Storage;

class ProductService
{

    private ProductRepository $repository;
    private ValidityRepository $validityRepository;
    private SupplierProductRepository $supplierProductRepository;
    private StoredProductRepository $storedProductRepository;

    /**
     * @throws ConnectionException
     */
    public function __construct()
    {
        try {
            $this->repository = new ProductRepository();
            $this->validityRepository = new ValidityRepository();
            $this->supplierProductRepository = new SupplierProductRepository();
            $this->storedProductRepository = new StoredProductRepository();
        }
        catch (ConnectionException $ex) {
            throw new ConnectionException($ex->getMessage());
        }
    }

    /**
     * @throws BusinessException
     * @throws MYSQLTransactionException
     */
    public function create(?ProductInputModel $inputModel): ?Product
    {

        $product = new Product();
        $product->setDescription($inputModel->getDescription());
        $product->setUnit($inputModel->getUnit());
        $product->setLowestPrice(0.00);
        $product->setTotalQuantity(0.00);

        $options = array('description' => $inputModel->getDescription());

        $founds = Utilities::toProductCollection(
            $this->repository->findByParams($options, 1, 2, array(["id", "asc"]))
        );

        if ($founds) {
            foreach ($founds as $found) {
                if (($found) && ($found->__equals($product))) {
                    throw new BusinessException('Product already exists!');
                }
            }
        }

        return Utilities::toProduct($this->repository->create($product));
    }

    /**
     * @throws MYSQLTransactionException
     * @throws EntityNotFoundException
     */
    public function findOne(?int $id): ?Product
    {

        $product = Utilities::toProduct($this->repository->findOne($id));

        if (!($product)) {
            throw new EntityNotFoundException('Product Not Found');
        }

        return $product;
    }

    /**
     * @throws MYSQLTransactionException
     * @throws EntityNotFoundException
     */
    public function findByDescription(array $options, int $page, int $limit, array $sorts): ?array
    {

        $products = Utilities::toProductCollection(
            $this->repository->findByParams($options, $page, $limit, $sorts)
        );

        if (!($products)) {
            throw new EntityNotFoundException('Could not find any product with the given parameters');
        }

        return $products;
    }


    /**
     * @throws MYSQLTransactionException
     * @throws EntityNotFoundException
     */
    public function findByUnit(array $options, int $page, int $limit, array $sorts): ?array
    {

        $products = Utilities::toProductCollection(
            $this->repository->findByParams($options, $page, $limit, $sorts)
        );

        if (!($products)) {
            throw new EntityNotFoundException('Could not find any product with the given parameters');
        }

        return $products;
    }

    /**
     * @throws MYSQLTransactionException
     * @throws EntityNotFoundException
     */
    public function findByDescriptionAndUnit(array $options, int $page, int $limit, array $sorts): ?array
    {

        $products = Utilities::toProductCollection(
            $this->repository->findByParams($options, $page, $limit, $sorts)
        );

        if (!($products)) {
            throw new EntityNotFoundException('Could not find any product with the given parameters');
        }

        return $products;
    }

    /**
     * @throws MYSQLTransactionException
     * @throws EntityNotFoundException
     */
    public function findByLowestPrice(array $options, int $page, int $limit, array $sorts): ?array
    {

        $products = Utilities::toProductCollection(
            $this->repository->findByParams($options, $page, $limit, $sorts)
        );

        if (!($products)) {
            throw new EntityNotFoundException('Could not find any product with the given parameters');
        }

        return $products;
    }

    /**
     * @throws MYSQLTransactionException
     * @throws EntityNotFoundException
     */
    public function findByUnitAndLowestPrice(array $options, int $page, int $limit, array $sorts): ?array
    {

        $products = Utilities::toProductCollection(
            $this->repository->findByParams($options, $page, $limit, $sorts)
        );

        if (!($products)) {
            throw new EntityNotFoundException('Could not find any product with the given parameters');
        }

        return $products;
    }

    /**
     * @throws MYSQLTransactionException
     * @throws EntityNotFoundException
     */
    public function findByDescriptionAndLowestPrice(
        array $options,
        int $page,
        int $limit,
        array $sorts): ?array
    {

        $products = Utilities::toProductCollection(
            $this->repository->findByParams($options, $page, $limit, $sorts)
        );

        if (!($products)) {
            throw new EntityNotFoundException('Could not find any product with the given parameters');
        }

        return $products;
    }

    /**
     * @throws MYSQLTransactionException
     * @throws EntityNotFoundException
     */
    public function findByDescriptionAndUnitAndLowestPrice(
        array $options,
        int $page,
        int $limit,
        array $sorts): ?array
    {

        $products = Utilities::toProductCollection(
            $this->repository->findByParams($options, $page, $limit, $sorts)
        );

        if (!($products)) {
            throw new EntityNotFoundException('Could not find any product with the given parameters');
        }

        return $products;
    }

    /**
     * @throws MYSQLTransactionException
     * @throws EntityNotFoundException
     */
    public function findByTotalQuantity(
        array $options,
        int $page,
        int $limit,
        array $sorts): ?array
    {

        $products = Utilities::toProductCollection(
            $this->repository->findByParams($options, $page, $limit, $sorts)
        );

        if (!($products)) {
            throw new EntityNotFoundException('Could not find any product with the given parameters');
        }

        return $products;
    }

    /**
     * @throws MYSQLTransactionException
     * @throws EntityNotFoundException
     */
    public function findByDescriptionAndTotalQuantity(
        array $options,
        int $page,
        int $limit,
        array $sorts): ?array
    {

        $products = Utilities::toProductCollection(
            $this->repository->findByParams($options, $page, $limit, $sorts)
        );

        if (!($products)) {
            throw new EntityNotFoundException('Could not find any product with the given parameters');
        }

        return $products;
    }

    /**
     * @throws MYSQLTransactionException
     * @throws EntityNotFoundException
     */
    public function findByUnitAndTotalQuantity(
        array $options,
        int $page,
        int $limit,
        array $sorts): ?array
    {

        $products = Utilities::toProductCollection(
            $this->repository->findByParams($options, $page, $limit, $sorts)
        );

        if (!($products)) {
            throw new EntityNotFoundException('Could not find any product with the given parameters');
        }

        return $products;
    }

    public function findByLowestPriceAndTotalQuantity(
        array $options,
        int $page,
        int $limit,
        array $sorts): ?array
    {

        $products = Utilities::toProductCollection(
            $this->repository->findByParams($options, $page, $limit, $sorts)
        );

        if (!($products)) {
            throw new EntityNotFoundException('Could not find any product with the given parameters');
        }

        return $products;
    }

    public function findByDescriptionAndUnitAndTotalQuantity(
        array $options,
        int $page,
        int $limit,
        array $sorts): ?array
    {

        $products = Utilities::toProductCollection(
            $this->repository->findByParams($options, $page, $limit, $sorts)
        );

        if (!($products)) {
            throw new EntityNotFoundException('Could not find any product with the given parameters');
        }

        return $products;
    }

    public function findByDescriptionAndLowestPriceAndTotalQuantity(
        array $options,
        int $page,
        int $limit,
        array $sorts): ?array
    {

        $products = Utilities::toProductCollection(
            $this->repository->findByParams($options, $page, $limit, $sorts)
        );

        if (!($products)) {
            throw new EntityNotFoundException('Could not find any product with the given parameters');
        }

        return $products;
    }

    public function findByUnitAndLowestPriceAndTotalQuantity(
        array $options,
        int $page,
        int $limit,
        array $sorts): ?array
    {

        $products = Utilities::toProductCollection(
            $this->repository->findByParams($options, $page, $limit, $sorts)
        );

        if (!($products)) {
            throw new EntityNotFoundException('Could not find any product with the given parameters');
        }

        return $products;
    }

    public function findByDescriptionAndUnitAndLowestPriceAndTotalQuantity(
        array $options,
        int $page,
        int $limit,
        array $sorts): ?array
    {

        $products = Utilities::toProductCollection(
            $this->repository->findByParams($options, $page, $limit, $sorts)
        );

        if (!($products)) {
            throw new EntityNotFoundException('Could not find any product with the given parameters');
        }

        return $products;
    }

    public function findAll(int $page, int $limit, array $sorts): ?array
    {

        $products = Utilities::toProductCollection($this->repository->findAll($page, $limit, $sorts));

        if (!($products)) {
            throw new EntityNotFoundException('Could not find any product');
        }

        return $products;
    }

    public function update(?int $id, ?ProductInputModel $inputModel): ?Product
    {

        $found = $this->findOne($id);

        $found->setDescription($inputModel->getDescription());
        $found->setUnit($inputModel->getUnit());

        $updateProd = Utilities::toProduct($this->repository->update($found));

        if (!$updateProd) {
            throw new BusinessException('Could not proceed with update');
        }

        return $updateProd;
    }

    public function delete(?int $id): ?bool
    {

        $product = $this->findOne($id);

        return $this->repository->delete($product->getId());
    }

    public function getProductExistance(): int
    {
        return $this->repository->getTotal();
    }

    public function add(?ValidityInputModel $inputModel): ?Validity
    {

        $product = $this->findOne($inputModel->getProduct()->getId());


        $datetime = new \Datetime($inputModel->getExpirationDate());
        $options = array("expiration_date" => $datetime->format('Y-m-d\TH:i:s'));

        $founds = Utilities::toValidityCollection(
            $this->validityRepository->findByParams($product->getId(),
            $options, 1, 5000, array(["validity_id", "ASC"]))
        );

        $validity = new Validity();
        $validity->setProduct($product);
        $validity->setExpirationDate($datetime);
        $validity->setQuantity($inputModel->getQuantity());

        if ($founds) {
            foreach ($founds as $found) {
                if (($found) && ($found->__equals($validity))) {
                    throw new BusinessException('This validity has already been added to the given product');
                }
            }
        }

        $created = Utilities::toValidity($this->validityRepository->create($validity));

        return $created;
    }

    public function viewValidity(?int $productId, ?int $validityId): ?Validity
    {

        $product = $this->findOne($productId);

        $validity = Utilities::toValidity(
            $this->validityRepository->findOne($product->getId(), $validityId)
        );

        if (!($validity)) {
            throw new EntityNotFoundException('Validity Not Found');
        }

        return $validity;
    }

    public function listValidityByExpirationDate(
        int $productId,
        array $options,
        int $page,
        int $limit,
        array $sorts): ?array
    {

        $validities = Utilities::toValidityCollection(
            $this->validityRepository->findByParams($productId,
            $options, $page, $limit, $sorts
        )
        );

        if (!($validities)) {
            throw new EntityNotFoundException('Could not find 
            any validity with the given parameters added to this product');
        }

        return $validities;
    }

    public function listValidityByQuantity(
        int $productId,
        array $options,
        int $page,
        int $limit,
        array $sorts): ?array
    {

        $validities = Utilities::toValidityCollection(
            $this->validityRepository->findByParams($productId,
            $options, $page, $limit, $sorts
        ));

        if (!($validities)) {
            throw new EntityNotFoundException('Could not find any validity 
            with the given parameters added to this product');
        }

        return $validities;
    }

    public function listValidityByExpirationDateAndQuantity(
        int $productId,
        array $options,
        int $page,
        int $limit,
        array $sorts): ?array
    {

        $validities = Utilities::toValidityCollection(
            $this->validityRepository->findByParams($productId,
            $options, $page, $limit, $sorts
        )
        );

        if (!($validities)) {
            throw new EntityNotFoundException('Could not find 
            any validity with the given parameters added to this product');
        }

        return $validities;
    }

    public function listAllValidities(
        int $productId,
        int $page,
        int $limit,
        array $sorts): ?array
    {

        $product = $this->findOne($productId);

        $validities = Utilities::toValidityCollection(
            $this->validityRepository->findAll($product->getId(), $page, $limit, $sorts)
        );

        if (!($validities)) {
            throw new EntityNotFoundException('Could not find 
            any validity added to this product');
        }

        return $validities;
    }

    public function edit(?int $validityId, ?ValidityInputModel $inputModel): ?Validity
    {
        $productId = $inputModel->getProduct()->getId();

        $found = $this->viewValidity($productId, $validityId);

        $found->setProduct(
            $this->findOne($productId)
        );
        $found->setExpirationDate(new \DateTime($inputModel->getExpirationDate()));
        $found->setQuantity($inputModel->getQuantity());

        $updated = Utilities::toValidity($this->validityRepository->update($found));

        if (!$updated) {
            throw new BusinessException("Was not possible to processed with update");
        }

        return $updated;
    }

    public function remove(?int $productId, ?int $validityId): ?bool
    {

        $found = $this->viewValidity($productId, $validityId);

        return $this->validityRepository->delete(
            $found->getId());
    }

    public function getValiditiesExistance(int $productId)
    {
        return $this->validityRepository->getTotal($productId);
    }

    public function listSuppliers(int $productId, int $page, int $limit, array $sorts): ?array
    {

        $product = $this->findOne($productId);

        $supplierProduct = new SupplierProduct();
        $supplierProduct->setProduct($product);

        $records = Utilities::toSupplierProductCollection(
            $this->supplierProductRepository->
            findByProduct($product->getId(), $page, $limit, $sorts)
        );

        if ($records) {
            $suppliers = array();

            foreach ($records as $record) {
                array_push($suppliers, $record->getSupplier());
            }
        }
        else {
            throw new EntityNotFoundException('Could not find 
            any supplier of the given product');
        }

        return $suppliers;
    }

    public function viewSupplier(?int $productId, ?int $supplierId): ?Supplier
    {

        $product = $this->findOne($productId);

        $supplierProducts = Utilities::toSupplierProductCollection(
            $this->supplierProductRepository->findByProduct(
            $product->getId(), 1, 5000, array(["supplier_id", "ASC"])
        )
        );

        if (!($supplierProducts)) {
            throw new EntityNotFoundException('This product is not supplied by this Supplier');
        }

        $supplier = null;

        foreach ($supplierProducts as $supplied) {
            if ($supplied instanceof SupplierProduct) {
                if ($supplied->getSupplier()->getId() === $supplierId) {
                    $supplier = $supplied->getSupplier();
                }
            }
        }

        if (!($supplier)) {
            throw new EntityNotFoundException('This product is not supplied by this Supplier');
        }

        return $supplier;
    }

    public function getSuppliersExistance(int $id): int
    {
        return $this->supplierProductRepository->getTotalByProduct($id);
    }

    public function listStorages(int $productId,
        int $page,
        int $limit,
        array $sorts): ?array
    {

        $product = $this->findOne($productId);

        $records = Utilities::toStoredProductCollection(
            $this->storedProductRepository->
            findByProduct($product->getId(), $page, $limit, $sorts)
        );

        if ($records) {
            $storages = array();

            foreach ($records as $record) {
                array_push($storages, $record->getStorage());
            }
        }
        else {
            throw new EntityNotFoundException('Could not find 
            any storage that contains this product');
        }

        return $storages;
    }

    public function viewStorage(int $productId, int $storageId): ?Storage
    {

        $product = $this->findOne($productId);

        $storedProducts = Utilities::toStoredProductCollection(
            $this->storedProductRepository->findByProduct(
            $product->getId(), 1, 5000, array(["storage_id", "ASC"]))
        );

        if (!($storedProducts)) {
            throw new EntityNotFoundException('This product is not stored on this Storage');
        }

        $storage = null;

        foreach ($storedProducts as $stored) {
            if ($stored instanceof StoredProduct) {
                if ($stored->getStorage()->getId() === $storageId) {
                    $storage = $stored->getStorage();
                }
            }
        }

        if (!($storage)) {
            throw new EntityNotFoundException('This product is not stored on this Storage');
        }

        return $storage;
    }

    public function getStoragesExistance(int $id): int
    {
        return $this->storedProductRepository->getTotalByProduct($id);
    }
}
