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

        $found = Utilities::toSupplier(
            $this->repository->findByVatNumber(
            $supplier->getVatNumber()
        )
        );

        if (($found) && ($found->__equals($supplier))) {
            throw new BusinessException('Supplier Already exists');
        }

        return Utilities::toSupplier($this->repository->create($supplier));
    }

    public function findOne(?int $id): Supplier
    {

        $supplier = Utilities::toSupplier($this->repository->findOne($id));

        if (!($supplier)) {
            throw new EntityNotFoundException('Supplier Not Found');
        }

        return $supplier;
    }

    public function findByVatNumber(?int $vatNumber): ?Supplier
    {

        $supplier = Utilities::toSupplier(
            $this->repository->findByVatNumber($vatNumber)
        );

        if (!($supplier)) {
            throw new EntityNotFoundException('Supplier Not Found');
        }

        return $supplier;
    }

    public function findByName(?string $name): ?Supplier
    {

        $supplier = Utilities::toSupplier($this->repository->findByName($name));

        if (!($supplier)) {
            throw new EntityNotFoundException('Supplier Not Found');
        }

        return $supplier;
    }

    public function findAll(): ?array
    {

        $suppliers = Utilities::toSupplierCollection(
            $this->repository->findAll()
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

        if ($created) {
            if ($created instanceof SupplierProduct) {
                $created->setProduct($this->findProduct($created->getProduct()->getId()));
                $created->setSupplier($this->findOne($created->getSupplier()->getId()));
            }
        }

        return $created;
    }

    public function findOneProduct(?int $productId, ?int $supplierId): ?SupplierProduct
    {

        $supplierProduct = Utilities::toSupplierProduct(
            $this->supplierProductRepository->
            findOne($productId, $supplierId)
        );

        if (!($supplierProduct)) {
            throw new EntityNotFoundException('Supplier does not supply this Product');
        }

        $product = $this->findProduct($supplierProduct->getProduct()->getId());
        $supplier = $this->findOne($supplierProduct->getSupplier()->getId());

        $supplierProduct->setProduct($product);
        $supplierProduct->setSupplier($supplier);

        return $supplierProduct;
    }

    public function listProducts(?int $supplierId): ?array
    {

        $supplier = $this->findOne($supplierId);

        $supplierProduct = new SupplierProduct();
        $supplierProduct->setSupplier($supplier);

        $supplierProducts = Utilities::toSupplierProductCollection(
            $this->supplierProductRepository->findBySupplier($supplierProduct)
        );

        if (!($supplierProducts)) {
            throw new EntityNotFoundException('Could not find any product of '
                . 'this Supplier');
        }

        foreach ($supplierProducts as $supplierProd) {
            if ($supplierProd instanceof SupplierProduct) {
                $supplierProd->setProduct(
                    $this->findProduct($supplierProd->getProduct()->getId())
                );
                $supplierProd->setSupplier(
                    $this->findOne($supplierProd->getSupplier()->getId())
                );
            }
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

        if ($updated) {
            if ($updated instanceof SupplierProduct) {
                $updated->setProduct(
                    $this->findProduct(
                    $updated->getProduct()->getId()
                )
                );

                $updated->setSupplier(
                    $this->findOne(
                    $updated->getSupplier()->getId()
                )
                );
            }
        }

        return $updated;
    }

    public function remove(?SupplierProductInputModel $inputModel): ?bool
    {

        $supplierProduct = new SupplierProduct();

        $product = new Product();
        $product->setId($inputModel->getProduct()->getId());

        $supplier = new Supplier();
        $supplier->setId($inputModel->getSupplier()->getId());

        $supplierProduct->setProduct($product);
        $supplierProduct->setSupplier($supplier);

        $found = Utilities::toSupplierProduct(
            $this->supplierProductRepository->findOne(
            $supplierProduct->getProduct()->
            getId(), $supplierProduct->getSupplier()->getId()
        )
        );

        return $this->supplierProductRepository->deleteOne(
            $found->getProduct()->getId(), $found->getSupplier()->getId());
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
