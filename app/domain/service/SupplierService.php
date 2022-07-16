<?php

namespace app\domain\service;

use App\api\model\SupplierInputModel;
use App\api\model\SupplierProductInputModel;

use App\core\shared\Utilities;

use App\domain\exception\BusinessException;
use app\domain\exception\ConnectionException;
use App\domain\exception\EntityNotFoundException;

use App\domain\model\Product;
use App\domain\model\Supplier;
use App\domain\model\SupplierProduct;

use App\domain\repository\ProductRepository;
use App\domain\repository\SupplierRepository;
use App\domain\repository\SupplierProductRepository;

class SupplierService
{

    private $repository;
    private $productRepository;
    private $supplierProductRepository;

    public function __construct()
    {
        try {
            $this->repository = new SupplierRepository();
            $this->productRepository = new ProductRepository();
            $this->supplierProductRepository = new SupplierProductRepository();
        }
        catch (ConnectionException $connectionException) {
            throw new ConnectionException($connectionException->getMessage());
        }
    }

    public function create(?SupplierInputModel $inputModel): ?Supplier
    {

        $supplier = new Supplier();
        $supplier->setName($inputModel->getName());
        $supplier->setVatNumber($inputModel->getVatNumber());

        $options = array('vatNumber' => $supplier->getVatNumber());

        $founds = Utilities::toSupplierCollection(
            $this->repository->findByParams(
            $options, 1, 5000, array(["id", "asc"])));

        if ($founds) {
            foreach ($founds as $found) {
                if (($found) && ($found->__equals($supplier))) {
                    throw new BusinessException('Supplier Already exists');
                }
            }
        }

        return Utilities::toSupplier($this->repository->create($supplier));
    }

    public function findOne(?int $id): ?Supplier
    {

        $supplier = Utilities::toSupplier($this->repository->findOne($id));

        if (!($supplier)) {
            throw new EntityNotFoundException('Supplier Not Found');
        }

        return $supplier;
    }

    public function findByVatNumber(
        array $options,
        int $page,
        int $limit,
        array $sorts): ?array
    {

        $suppliers = Utilities::toSupplierCollection(
            $this->repository->findByParams($options, $page, $limit, $sorts)
        );

        if (!($suppliers)) {
            throw new EntityNotFoundException('Supplier Not Found');
        }

        return $suppliers;
    }

    public function findByName(
        array $options,
        int $page,
        int $limit,
        array $sorts): ?array
    {

        $suppliers = Utilities::toSupplierCollection(
            $this->repository->findByParams($options, $page, $limit, $sorts)
        );

        if (!($suppliers)) {
            throw new EntityNotFoundException('Supplier Not Found');
        }

        return $suppliers;
    }

    public function findByNameAndVatNumber(
        array $options,
        int $page,
        int $limit,
        array $sorts): ?array
    {

        $suppliers = Utilities::toSupplierCollection($this->repository->findByParams($options, $page, $limit, $sorts));

        if (!($suppliers)) {
            throw new EntityNotFoundException('Supplier Not Found');
        }

        return $suppliers;
    }

    public function findAll(
        int $page,
        int $limit,
        array $sorts): ?array
    {

        $suppliers = Utilities::toSupplierCollection(
            $this->repository->findAll($page, $limit, $sorts)
        );

        if (!($suppliers)) {
            throw new EntityNotFoundException('Could not find any Supplier');
        }

        return $suppliers;
    }

    public function update(?int $id, ?SupplierInputModel $inputModel): ?Supplier
    {

        $found = $this->findOne($id);

        $found->setName($inputModel->getName());
        $found->setVatNumber($inputModel->getVatNumber());

        $supplier = Utilities::toSupplier(
            $this->repository->update($found)
        );

        if (!($supplier)) {
            throw new BusinessException('Could not proceed update');
        }

        return $supplier;
    }

    public function delete(?int $id): ?bool
    {

        $supplier = $this->findOne($id);

        return $this->repository->delete($supplier->getId());
    }

    public function getSuppliersExistance()
    {
        return $this->repository->getTotal();
    }

    public function add(?SupplierProductInputModel $inputModel): ?SupplierProduct
    {

        $product = new Product();
        $product->setId($inputModel->getProduct()->getId());

        $supplier = new Supplier();
        $supplier->setId($inputModel->getSupplier()->getId());

        $supplierProduct = new SupplierProduct();
        $supplierProduct->setPrice($inputModel->getPrice());
        $supplierProduct->setProduct($product);
        $supplierProduct->setSupplier($supplier);

        $found = Utilities::toSupplierProduct(
            $this->supplierProductRepository->findOne(
            $supplierProduct->getProduct()->getId(), $supplierProduct->getSupplier()->getId())
        );

        if (($found) && ($found->__equals($supplierProduct))) {
            throw new BusinessException('Product already associated to this '
                . 'Supplier');
        }

        $prod = $this->findProduct($supplierProduct->getProduct()->getId());
        $sup = $this->findOne($supplierProduct->getSupplier()->getId());

        $supplierProduct->setProduct($prod);
        $supplierProduct->setSupplier($sup);

        $created = Utilities::toSupplierProduct(
            $this->supplierProductRepository->create($supplierProduct));

        return $created;
    }

    public function viewProduct(?int $productId, ?int $supplierId): ?SupplierProduct
    {

        $supplierProduct = Utilities::toSupplierProduct(
            $this->supplierProductRepository->
            findOne($productId, $supplierId)
        );

        if (!($supplierProduct)) {
            throw new EntityNotFoundException('Supplier does not supply this Product');
        }

        return $supplierProduct;
    }

    public function listAll(
        int $supplierId,
        int $page,
        int $limit,
        array $sorts): ?array
    {

        $supplier = $this->findOne($supplierId);

        $supplierProducts = Utilities::toSupplierProductCollection(
            $this->supplierProductRepository->findAll($supplier->getId(), $page, $limit, $sorts)
        );

        if (!($supplierProducts)) {
            throw new EntityNotFoundException('Could not find any product of '
                . 'this Supplier');
        }

        return $supplierProducts;
    }

    public function listByPrice(
        int $supplierId,
        array $options,
        int $page,
        int $limit,
        array $sorts): ?array
    {

        $supplier = $this->findOne($supplierId);

        $supplierProducts = Utilities::toSupplierProductCollection(
            $this->supplierProductRepository->findByParams($supplier->getId(), $options, $page, $limit, $sorts)
        );

        if (!($supplierProducts)) {
            throw new EntityNotFoundException('Could not find any product of '
                . 'this Supplier');
        }

        return $supplierProducts;
    }

    public function edit(?SupplierProductInputModel $inputModel): ?SupplierProduct
    {

        $product = new Product();
        $product->setId($inputModel->getProduct()->getId());

        $supplier = new Supplier();
        $supplier->setId($inputModel->getSupplier()->getId());

        $supplierProduct = new SupplierProduct();
        $supplierProduct->setProduct($product);
        $supplierProduct->setSupplier($supplier);
        $supplierProduct->setPrice($inputModel->getPrice());


        $found = Utilities::toSupplierProduct(
            $this->supplierProductRepository->findOne(
            $supplierProduct->getProduct()->getId(), $supplierProduct->
            getSupplier()->getId()
        )
        );

        if (!($found)) {
            throw new EntityNotFoundException('Product Not Associated to this Supplier');
        }

        $prod = $this->findProduct($supplierProduct->getProduct()->getId());
        $sup = $this->findOne($supplierProduct->getSupplier()->getId());

        $found->setProduct($prod);
        $found->setSupplier($sup);
        $found->setPrice($supplierProduct->getPrice());

        $updated = Utilities::toSupplierProduct(
            $this->supplierProductRepository->update($found)
        );

        return $updated;
    }

    public function remove(int $supplierId, int $productId): ?bool
    {

        $found = Utilities::toSupplierProduct(
            $this->supplierProductRepository->findOne(
            $supplierId, $productId)
        );

        if (!$found) {
            throw new EntityNotFoundException("This product is not associated with this Supplier");
        }

        return $this->supplierProductRepository->deleteOne(
            $found->getProduct()->getId(), $found->getSupplier()->getId());
    }

    public function getSupplierProductsExistance(int $id)
    {
        return $this->supplierProductRepository->getTotal($id);
    }

    private function findProduct(?int $id): Product
    {
        $product = Utilities::toProduct($this->productRepository->findOne($id));

        if (!($product)) {
            throw new EntityNotFoundException('Product Not Found');
        }

        return $product;
    }

}