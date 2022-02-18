<?php

namespace app\domain\service;

use App\api\model\ProductInputModel;
use App\api\model\ValidityInputModel;

use App\core\shared\Utilities;

use App\domain\exception\ConnectionException;
use App\domain\exception\BusinessException;
use App\domain\exception\EntityNotFoundException;

use App\domain\model\Product;
use App\domain\model\StoredProduct;
use App\domain\model\SupplierProduct;
use App\domain\model\Validity;

use App\domain\repository\ProductRepository;
use App\domain\repository\StorageRepository;
use App\domain\repository\SupplierRepository;
use App\domain\repository\StoredProductRepository;
use App\domain\repository\SupplierProductRepository;
use App\domain\repository\ValidityRepository;

class ProductService
{

    private $repository;
    private $validityRepository;
    private $supplierRepository;
    private $supplierProductRepository;
    private $storageRepository;
    private $storedProductRepository;

    public function __construct()
    {
        try {
            $this->repository = new ProductRepository();
            $this->validityRepository = new ValidityRepository();
            $this->supplierRepository = new SupplierRepository();
            $this->supplierProductRepository = new SupplierProductRepository();
            $this->storageRepository = new StorageRepository();
            $this->storedProductRepository = new StoredProductRepository();
        }
        catch (ConnectionException $ex) {
            throw new ConnectionException($ex->getMessage());
        }
    }

    public function create(?ProductInputModel $inputModel): ?Product
    {

        $product = new Product();
        $product->setDescription($inputModel->getDescription());
        $product->setUnit($inputModel->getUnit());
        $product->setLowestPrice(0.00);
        $product->setTotalQuantity(0.00);

        $found = Utilities::toProduct(
            $this->repository->findByDescription($product->getDescription())
        );

        if (($found) && ($found->__equals($product))) {
            throw new BusinessException('Product alredy exists!');
        }

        return Utilities::toProduct($this->repository->create($product));
    }

    public function findOne(?int $id): ?Product
    {

        $product = Utilities::toProduct($this->repository->findOne($id));

        if (!($product)) {
            throw new EntityNotFoundException('Product Not Found');
        }

        return $product;
    }

    public function findByDescription(?string $description): ?Product
    {

        $product = Utilities::toProduct(
            $this->repository->findByDescription($description)
        );

        if (!($product)) {
            throw new EntityNotFoundException('Product Not Found');
        }

        return $product;
    }

    public function findByUnit(?string $unit): ?array
    {

        $products = Utilities::toProductCollection(
            $this->repository->findByUnit($unit)
        );

        if (!($products)) {
            throw new EntityNotFoundException('Product Not Found');
        }

        return $products;
    }

    public function findAll(): ?array
    {

        $products = Utilities::toProductCollection($this->repository->findAll());

        if (!($products)) {
            throw new EntityNotFoundException('Could not find any Product');
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

    public function add(?ValidityInputModel $inputModel): ?Validity
    {

        $product = $this->findOne($inputModel->getProduct()->getId());

        $founds = Utilities::toValidityCollection(
            $this->validityRepository->findByExpirationDate(
            new \DateTime($inputModel->getExpirationDate())
        )
        );

        $validity = new Validity();
        $validity->setProduct($product);
        $validity->setExpirationDate(new \DateTime($inputModel->getExpirationDate()));
        $validity->setQuantity($inputModel->getQuantity());

        if ($founds) {
            foreach ($founds as $found) {
                if (($found) && ($found->__equals($validity))) {
                    throw new BusinessException('Validity already exists for this Product');
                }
            }
        }

        $created = Utilities::toValidity($this->validityRepository->create($validity));

        if ($created) {
            $created->setProduct(
                $this->findOne($validity->getProduct()->getId())
            );
        }

        return $created;
    }

    public function findOneValidity(?int $productId, ?int $validityId): ?Validity
    {

        $product = $this->findOne($productId);

        $validity = Utilities::toValidity(
            $this->validityRepository->findOne($validityId, $product->getId())
        );

        if (!($validity)) {
            throw new EntityNotFoundException('Validity Not Found');
        }

        $validity->setProduct($product);

        return $validity;
    }

    public function findValidityByExpirationDate(?int $productId, ?int $expirationDate): ?array
    {

        $validities = Utilities::toValidityCollection(
            $this->validityRepository->findByExpirationDate(
            new \Datetime($expirationDate)
        )
        );

        if (!($validities)) {
            throw new EntityNotFoundException('Could not find any validity with '
                . 'this expiration date');
        }

        foreach ($validities as $validity) {
            if ($validity instanceof Validity) {
                $validity->setProduct($this->findOne($productId));
            }
        }

        return $validities;
    }

    public function listValidities(?int $productId): ?array
    {

        $product = $this->findOne($productId);

        $validities = Utilities::toValidityCollection(
            $this->validityRepository->findByProduct($product)
        );

        if (!($validities)) {
            throw new EntityNotFoundException('Could not find any validity '
                . 'for this Product');
        }

        foreach ($validities as $validity) {
            if ($validity instanceof Validity) {
                $validity->setProduct(
                    $this->findOne($validity->getProduct()->getId())
                );
            }
        }

        return $validities;
    }

    public function edit(?int $validityId, ?ValidityInputModel $inputModel): ?Validity
    {

        $found = Utilities::toValidity(
            $this->validityRepository->findOne(
            $validityId, $inputModel->getProduct()->getId())
        );

        $found->setProduct(
            $this->findOne($inputModel->getProduct()->getId())
        );
        $found->setExpirationDate(new \DateTime($inputModel->getExpirationDate()));
        $found->setQuantity($inputModel->getQuantity());

        $updated = Utilities::toValidity($this->validityRepository->update($found));

        if ($updated) {
            $updated->setProduct($this->findOne($updated->getProduct()->getId()));
        }

        return $updated;
    }

    public function remove(?int $validityId, ?int $productId): ?bool
    {

        $product = $this->findOne($productId);

        $found = Utilities::toValidity(
            $this->validityRepository->findOne($validityId, $product->getId())
        );

        if (!($found)) {
            throw new EntityNotFoundException('Validity Not Found');
        }

        return $this->validityRepository->delete(
            $found->getId(), $product->getId());
    }

    public function listSuppliers(?int $productId): ?array
    {

        $product = $this->findOne($productId);

        $supplierProduct = new SupplierProduct();
        $supplierProduct->setProduct($product);

        $records = Utilities::toSupplierProductCollection(
            $this->supplierProductRepository->
            findByProduct($supplierProduct)
        );

        if ($records) {
            $suppliers = array();

            foreach ($records as $record) {
                array_push($suppliers, Utilities::toSupplier(
                    $this->supplierRepository->findOne(
                    $record->getSupplier()->getId()
                )
                )
                );
            }
        }

        return $suppliers;
    }

    public function listStorages(?int $productId): ?array
    {

        $product = $this->findOne($productId);

        $storedProduct = new StoredProduct();
        $storedProduct->setProduct($product);

        $records = Utilities::toStoredProductCollection(
            $this->storedProductRepository->
            findByProduct($storedProduct)
        );

        if ($records) {
            $storages = array();

            foreach ($records as $record) {
                array_push($storages, Utilities::toStorage(
                    $this->storageRepository->
                    findOne($record->getStorage()->getId()))
                );
            }
        }

        return $storages;
    }

}
