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

        $options = array('code' => $storage->getCode());
        $founds = Utilities::toStorageCollection(
            $this->repository->findByParams($options, 1, 5000, array(["id", "asc"])));
        if ($founds) {
            foreach ($founds as $found) {
                if (($found) && ($found->__equals($storage))) {
                    throw new BusinessException('Storage already exists');
                }
            }
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

    public function findByDesignation(array $options, int $page, int $limit, array $sorts): ?array
    {

        $storages = Utilities::toStorageCollection(
            $this->repository->findByParams($options, $page, $limit, $sorts));

        if (!($storages)) {
            throw new EntityNotFoundException('Could not find any storage with the given parameters');
        }

        return $storages;
    }

    public function findByCode(array $options, int $page, int $limit, array $sorts): ?array
    {

        $storages = Utilities::toStorageCollection(
            $this->repository->findByParams($options, $page, $limit, $sorts));

        if (!($storages)) {
            throw new EntityNotFoundException('Could not find any storage with the given parameters');
        }

        return $storages;
    }

    public function findByDesignationAndCode(
        array $options,
        int $page,
        int $limit,
        array $sorts): ?array
    {

        $storages = Utilities::toStorageCollection(
            $this->repository->findByParams($options, $page, $limit, $sorts));

        if (!($storages)) {
            throw new EntityNotFoundException('Could not find any storage with the given parameters');
        }

        return $storages;
    }

    public function findAll(
        int $page,
        int $limit,
        array $sorts): ?array
    {

        $storages = Utilities::toStorageCollection($this->repository->findAll($page, $limit, $sorts));

        if (!($storages)) {
            throw new EntityNotFoundException('Could not find any storage');
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
            throw new BusinessException('Could not proceed with update');
        }

        return $updatedStorage;
    }

    public function delete(?int $id): ?bool
    {

        $storage = $this->findOne($id);

        return $this->repository->delete($storage->getId());
    }

    public function getStoragesExistance()
    {
        return $this->repository->getTotal();
    }

    public function add(?StoredProductInputModel $inputModel): ?StoredProduct
    {

        $product = $this->findProduct($inputModel->getProduct()->getId());

        $storage = $this->findOne($inputModel->getStorage()->getId());

        $storedProduct = new StoredProduct();
        $storedProduct->setProduct($product);
        $storedProduct->setStorage($storage);
        $storedProduct->setQuantity($inputModel->getQuantity());

        $found = Utilities::toStoredProduct(
            $this->storedProductRepository->findOne(
            $storage->getId(), $product->getId())
        );

        if (($found) && ($found->__equals($storedProduct))) {
            throw new BusinessException('This product has already been added to the given storage');
        }

        $storedProduct->setProduct($product);
        $storedProduct->setStorage($storage);

        return Utilities::toStoredProduct(
            $this->storedProductRepository->create($storedProduct));
    }

    public function viewProduct(?int $productId, ?int $storageId): ?StoredProduct
    {

        $storedProduct = Utilities::toStoredProduct(
            $this->storedProductRepository->findOne($productId, $storageId)
        );

        if (!($storedProduct)) {
            throw new EntityNotFoundException('Product not found on Storage');
        }

        return $storedProduct;
    }

    public function listAll(
        int $storageId,
        int $page,
        int $limit,
        array $sorts): ?array
    {

        $storage = $this->findOne($storageId);

        $storedProducts = Utilities::toStoredProductCollection(
            $this->storedProductRepository->findAll($storage->getId(), $page, $limit, $sorts)
        );

        if (!($storedProducts)) {
            throw new EntityNotFoundException('This storage is empty! 
            Could not find any product in this storage');
        }

        return $storedProducts;
    }

    public function listByQuantity(
        int $storageId,
        array $options,
        int $page,
        int $limit,
        array $sorts): ?array
    {

        $storage = $this->findOne($storageId);

        $storedProducts = Utilities::toStoredProductCollection(
            $this->storedProductRepository->findByParams(
            $storage->getId(), $options, $page, $limit, $sorts)
        );

        if (!($storedProducts)) {
            throw new EntityNotFoundException('Could not find any product on this storage 
            with the given parameters');
        }

        return $storedProducts;
    }

    public function edit(?StoredProductInputModel $inputModel): ?StoredProduct
    {

        $product = $this->findProduct($inputModel->getProduct()->getId());

        $storage = $this->findOne($inputModel->getStorage()->getId());

        $storedProduct = new StoredProduct();
        $storedProduct->setQuantity($inputModel->getQuantity());
        $storedProduct->setProduct($product);
        $storedProduct->setStorage($storage);


        $found = Utilities::toStoredProduct(
            $this->storedProductRepository->findOne(
            $storage->getId(), $product->getId()
        )
        );

        if (!($found)) {
            throw new EntityNotFoundException('This product is not stored in given storage.');
        }

        $found->setProduct($product);
        $found->setStorage($storage);
        $found->setQuantity($storedProduct->getQuantity());

        $updated = Utilities::toStoredProduct(
            $this->storedProductRepository->update($found)
        );

        if (!$updated) {
            throw new BusinessException("Could not proceed with update");
        }

        return $updated;
    }

    public function remove(int $storageId, int $productId): ?bool
    {

        $this->findOne($storageId);
        $this->findProduct($productId);

        $found = Utilities::toStoredProduct(
            $this->storedProductRepository->findOne($storageId, $productId)
        );

        if (!$found) {
            throw new EntityNotFoundException("This product is not stored in the given store");
        }

        return $this->storedProductRepository->deleteOne($storageId, $productId);
    }

    public function getStoredProductsExistance(int $id)
    {
        return $this->storedProductRepository->getTotal($id);
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

        $options = array('designation' => $storage->getDesignation());

        $foundByDescription = Utilities::toStorageCollection(
            $this->repository->findByParams($options, 1, 100, array(["id", "asc"])
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
