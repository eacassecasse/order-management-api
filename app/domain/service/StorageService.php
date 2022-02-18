<?php

namespace app\domain\service;

use App\api\model\StorageInputModel;
use App\api\model\StoredProductInputModel;

use App\core\shared\Utilities;

use App\domain\exception\BusinessException;
use app\domain\exception\ConnectionException;
use App\domain\exception\EntityNotFoundException;

use App\domain\model\Product;
use App\domain\model\Storage;
use App\domain\model\StoredProduct;


use App\domain\repository\ProductRepository;
use App\domain\repository\StorageRepository;
use App\domain\repository\StoredProductRepository;


class StorageService
{

    private $repository;
    private $productRepository;
    private $storedProductRepository;

    public function __construct()
    {
        try {
            $this->repository = new StorageRepository();
            $this->productRepository = new ProductRepository();
            $this->storedProductRepository = new StoredProductRepository();
        }
        catch (ConnectionException $connectionException) {
            throw new ConnectionException($connectionException->getMessage());
        }
    }

    public function create(?StorageInputModel $inputModel): ?Storage
    {

        $storage = new Storage();
        $storage->setDesignation($inputModel->getDesignation());
        $storage->setCode($this->createCode($storage));

        $found = Utilities::toStorage(
            $this->repository->findByCode($storage->getCode()));

        if (($found) && ($found->__equals($storage))) {
            throw new BusinessException('Storage already exists');
        }

        return Utilities::toStorage($this->repository->create($storage));
    }

    public function findOne(?int $id): Storage
    {

        $storage = Utilities::toStorage($this->repository->findOne($id));

        if (!($storage)) {
            throw new EntityNotFoundException('Storage Not Found');
        }

        return $storage;
    }

    public function findByDesignation(?string $designation): ?array
    {

        $storages = Utilities::toStorageCollection(
            $this->repository->findByDesignation($designation));

        if (!($storages)) {
            throw new EntityNotFoundException('Could not find any Storage');
        }

        return $storages;
    }

    public function findByCode(?string $code): ?Storage
    {

        $storage = Utilities::toStorage($this->repository->findByCode($code));

        if (!($storage)) {
            throw new EntityNotFoundException('Storage Not Found');
        }

        return $storage;
    }

    public function findAll(): ?array
    {

        $storages = Utilities::toStorageCollection($this->repository->findAll());

        if (!($storages)) {
            throw new EntityNotFoundException('Could not find any Storage');
        }

        return $storages;
    }

    public function update(?int $id, ?StorageInputModel $inputModel): ?Storage
    {

        $found = $this->findOne($id);

        if ($inputModel->getDesignation() !== $found->getDesignation()) {
            $found->setDesignation($inputModel->getDesignation());
            $found->setCode($this->createCode($found));
        }
        
        $updatedStorage = Utilities::toStorage(
            $this->repository->update($found));

        if (!($updatedStorage)) {
            throw new BusinessException('Could not proceed with update.');
        }

        return $updatedStorage;
    }

    public function delete(?int $id): ?bool
    {

        $storage = $this->findOne($id);

        return $this->repository->delete($storage->getId());
    }

    public function add(?StoredProductInputModel $inputModel): ?StoredProduct
    {

        $prod = new Product();
        $prod->setId($inputModel->getProduct()->getId());

        $stor = new Storage();
        $stor->setId($inputModel->getStorage()->getId());

        $storedProduct = new StoredProduct();
        $storedProduct->setProduct($prod);
        $storedProduct->setStorage($stor);
        $storedProduct->setQuantity($inputModel->getQuantity());

        $found = Utilities::toStoredProduct(
            $this->storedProductRepository->findOne(
            $storedProduct->getProduct()->getId(), $storedProduct->getStorage()->getId())
        );

        if (($found) && ($found->__equals($storedProduct))) {
            throw new BusinessException('Product already exists on this '
                . 'Storage');
        }

        $product = $this->findProduct($storedProduct->getProduct()->getId());
        $storage = $this->findOne($storedProduct->getStorage()->getId());

        $storedProduct->setProduct($product);
        $storedProduct->setStorage($storage);

        return Utilities::toStoredProduct(
            $this->storedProductRepository->create($storedProduct));
    }

    public function findOneProduct(?int $productId, ?int $storageId): ?StoredProduct
    {

        $storedProduct = Utilities::toStoredProduct(
            $this->storedProductRepository->findOne($productId, $storageId)
        );

        if (!($storedProduct)) {
            throw new EntityNotFoundException('Product not found on Storage');
        }

        $product = $this->findProduct($storedProduct->getProduct()->getId());
        $storage = $this->findOne($storedProduct->getStorage()->getId());

        $storedProduct->setProduct($product);
        $storedProduct->setStorage($storage);

        return $storedProduct;
    }

    public function listAll(?int $storageId): ?array
    {

        $storage = $this->findOne($storageId);

        $storedProduct = new StoredProduct();
        $storedProduct->setStorage($storage);

        $storedProducts = Utilities::toStoredProductCollection(
            $this->storedProductRepository->findByStorage($storedProduct)
        );

        if (!($storedProducts)) {
            throw new EntityNotFoundException('Could not find any product on '
                . 'this Storage');
        }

        return $storedProducts;
    }

    public function edit(?StoredProductInputModel $inputModel): ?StoredProduct
    {

        $prod = new Product();
        $prod->setId($inputModel->getProduct()->getId());

        $stor = new Storage();
        $stor->setId($inputModel->getStorage()->getId());

        $storedProduct = new StoredProduct();
        $storedProduct->setQuantity($inputModel->getQuantity());
        $storedProduct->setProduct($prod);
        $storedProduct->setStorage($stor);


        $found = Utilities::toStoredProduct(
            $this->storedProductRepository->findOne(
            $storedProduct->getProduct()->getId(), $storedProduct->
            getStorage()->getId()
        )
        );

        if (!($found)) {
            throw new EntityNotFoundException('Product Not Found on the Storage');
        }

        $product = $this->findProduct($storedProduct->getProduct()->getId());
        $storage = $this->findOne($storedProduct->getStorage()->getId());

        $found->setProduct($product);
        $found->setStorage($storage);
        $found->setQuantity($storedProduct->getQuantity());

        return Utilities::toStoredProduct(
            $this->storedProductRepository->update($found)
        );
    }

    public function remove(?StoredProductInputModel $inputModel): ?bool
    {

        $prod = new Product();
        $prod->setId($inputModel->getProduct()->getId());

        $stor = new Storage();
        $stor->setId($inputModel->getStorage()->getId());

        $storedProduct = new StoredProduct();
        $storedProduct->setQuantity($inputModel->getQuantity());
        $storedProduct->setProduct($prod);
        $storedProduct->setStorage($stor);

        $found = Utilities::toStoredProduct(
            $this->storedProductRepository->findOne(
            $storedProduct->getProduct()->
            getId(), $storedProduct->getStorage()->getId()
        )
        );

        return $this->storedProductRepository->deleteOne(
            $found->getProduct()->getId(), $found->getStorage()->getId());
    }

    private function findProduct(?int $id): ?Product
    {
        $product = Utilities::toProduct($this->productRepository->findOne($id));

        if (!($product)) {
            throw new EntityNotFoundException('Product Not Found');
        }

        return $product;
    }

    private function createCode(?Storage $storage): ?string
    {

        $foundByDescription = Utilities::toStorageCollection(
            $this->repository->findByDesignation($storage->getDesignation()
        )
        );

        if ($foundByDescription) {
            $lastStorage = $foundByDescription[count($foundByDescription) - 1];
            $storage_no = substr($lastStorage->getCode(), -1, 2);

            $noPart = '0' . ++$storage_no;

            $arr_designation = explode(' ', $storage->getDesignation());

            if (count($arr_designation) > 1) {
                $firstPart = substr($arr_designation[0], 0, 4);

                if ($arr_designation[1] != 'de') {
                    $secondPart = substr($arr_designation[1], 0, 3);
                }
                else {
                    $secondPart = substr($arr_designation[2], 0, 3);
                }

                return $firstPart . $secondPart . $noPart;
            }
            else if (count($arr_designation) == 1) {
                $firstPart = substr($arr_designation[0], 0, 6);

                return $firstPart . $noPart;
            }
        }
        else {
            $arr_designation = explode(' ', $storage->getDesignation());

            if (count($arr_designation) > 1) {
                $firstPart = substr($arr_designation[0], 0, 4);

                if ($arr_designation[1] != 'de') {
                    $secondPart = substr($arr_designation[1], 0, 3);
                }
                else {
                    $secondPart = substr($arr_designation[2], 0, 3);
                }

                return $firstPart . $secondPart . '01';
            }
            else if (count($arr_designation) == 1) {
                $firstPart = substr($arr_designation[0], 0, 6);

                return $firstPart . '01';
            }
        }
    }

}
