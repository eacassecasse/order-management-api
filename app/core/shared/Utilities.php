<?php

namespace app\core\shared;


use App\api\model\ProductOutputModel;
use App\api\model\StorageOutputModel;
use App\api\model\SupplierOutputModel;
use App\api\model\UserOutputModel;
use App\api\model\StoredProductOutputModel;
use App\api\model\SupplierProductOutputModel;
use App\api\model\ValidityOutputModel;

use App\domain\model\Product;
use App\domain\model\Storage;
use App\domain\model\Supplier;
use App\domain\model\User;
use App\domain\model\StoredProduct;
use App\domain\model\SupplierProduct;
use App\domain\model\Validity;


class Utilities
{

    //put your code here

    public static function toProductOutputModel(?Product $product)
    {

        $model = null;

        if ($product) {
            $model = new ProductOutputModel();

            $model->setId($product->getId());
            $model->setDescription($product->getDescription());
            $model->setUnit($product->getUnit());
            $model->setLowestPrice($product->getLowestPrice());
            $model->setTotalQuantity($product->getTotalQuantity());
        }

        return $model;
    }

    public static function toProductOutputCollectionModel($products)
    {

        $collectionModel = null;
        if ($products) {
            $collectionModel = array();

            foreach ($products as $product) {
                array_push($collectionModel, Utilities::toProductOutputModel($product));
            }
        }

        return $collectionModel;
    }

    public static function toStorageOutputModel(?Storage $storage)
    {

        $model = null;

        if ($storage) {
            $model = new StorageOutputModel();

            $model->setId($storage->getId());
            $model->setDesignation($storage->getDesignation());
            $model->setCode($storage->getCode());
        }

        return $model;
    }

    public static function toStorageOutputCollectionModel($storages)
    {

        $collectionModel = null;

        if ($storages) {
            $collectionModel = array();

            foreach ($storages as $storage) {
                array_push($collectionModel, Utilities::toStorageOutputModel($storage));
            }
        }

        return $collectionModel;
    }

    public static function toSupplierOutputModel(?Supplier $supplier)
    {

        $model = null;

        if ($supplier) {
            $model = new SupplierOutputModel();

            $model->setId($supplier->getId());
            $model->setName($supplier->getName());
            $model->setVatNumber($supplier->getVatNumber());
        }

        return $model;
    }

    public static function toSupplierOutputCollectionModel($suppliers)
    {

        $collectionModel = null;

        if ($suppliers) {
            $collectionModel = array();

            foreach ($suppliers as $supplier) {
                array_push($collectionModel, Utilities::toSupplierOutputModel($supplier));
            }
        }

        return $collectionModel;
    }

    public static function toUserOutputModel(?User $user)
    {

        $model = null;

        if ($user) {
            $model = new UserOutputModel();

            $model->setId($user->getId());
            $model->setEmail($user->getEmail());
            $model->setPassword($user->getPassword());
        }

        return $model;
    }

    public static function toUserOutputCollectionModel($users)
    {

        $collectionModel = null;

        if ($users) {
            $collectionModel = array();

            foreach ($users as $user) {
                array_push($collectionModel, Utilities::toUserOutputModel($user));
            }
        }

        return $collectionModel;
    }

    public static function toValidityOutputModel(?Validity $validity)
    {

        $model = null;

        if ($validity) {
            $model = new ValidityOutputModel();

            $model->setId($validity->getId());
            $model->setExpirationDate(
                Utilities::toVisibleDate($validity->getExpirationDate()->getTimestamp()
            )
            );
            $model->setQuantity($validity->getQuantity());
            $model->setProduct(Utilities::toProductOutputModel($validity->getProduct()));
        }

        return $model;
    }

    public static function toValidityOutputCollectionModel($validities)
    {

        $collectionModel = null;

        if ($validities) {
            $collectionModel = array();

            foreach ($validities as $validity) {
                array_push($collectionModel, Utilities::toValidityOutputModel($validity));
            }
        }

        return $collectionModel;
    }

    public static function toStoredProductOutputModel(?StoredProduct $storedProduct)
    {

        $model = null;

        if ($storedProduct) {
            $model = new StoredProductOutputModel();

            $model->setQuantity($storedProduct->getQuantity());
            $model->setProduct(
                Utilities::toProductOutputModel(
                $storedProduct->getProduct()
            )
            );
            $model->setStorage(Utilities::toStorageOutputModel(
                $storedProduct->getStorage()
            )
            );
        }

        return $model;
    }

    public static function toStoredProductOutputCollectionModel($storedProducts)
    {

        $collectionModel = null;

        if ($storedProducts) {
            $collectionModel = array();

            foreach ($storedProducts as $storedProduct) {
                array_push($collectionModel, Utilities::toStoredProductOutputModel($storedProduct));
            }
        }

        return $collectionModel;
    }

    public static function toSupplierProductOutputModel(?SupplierProduct $supplierProduct)
    {

        $model = null;

        if ($supplierProduct) {
            $model = new SupplierProductOutputModel();

            $model->setPrice($supplierProduct->getPrice());
            $model->setProduct(
                Utilities::toProductOutputModel(
                $supplierProduct->getProduct()
            )
            );
            $model->setSupplier(
                Utilities::toSupplierOutputModel(
                $supplierProduct->getSupplier()
            )
            );
        }

        return $model;
    }

    public static function toSupplierProductOutputCollectionModel($supplierProducts)
    {

        $collectionModel = null;

        if ($supplierProducts) {
            $collectionModel = array();

            foreach ($supplierProducts as $supplierProduct) {
                array_push($collectionModel, Utilities::toSupplierProductOutputModel($supplierProduct));
            }
        }

        return $collectionModel;
    }

    public static function toProduct($record)
    {

        $product = null;

        if ($record) {
            $product = new Product();

            $product->setId($record['id']);
            $product->setDescription($record['description']);
            $product->setUnit($record['measure_unit']);
            $product->setLowestPrice($record['lowest_price']);
            $product->setTotalQuantity($record['total_quantity']);
        }

        return $product;
    }

    public static function toProductCollection($records)
    {

        $products = null;

        if ($records) {
            $products = array();

            foreach ($records as $record) {
                array_push($products, Utilities::toProduct($record));
            }
        }

        return $products;
    }

    public static function toSupplier($record)
    {

        $supplier = null;

        if ($record) {
            $supplier = new Supplier();

            $supplier->setId($record['id']);
            $supplier->setName($record['name']);
            $supplier->setVatNumber($record['vatNumber']);
        }

        return $supplier;
    }

    public static function toSupplierCollection($records)
    {

        $suppliers = null;

        if ($records) {
            $suppliers = array();

            foreach ($records as $record) {
                array_push($suppliers, Utilities::toSupplier($record));
            }
        }

        return $suppliers;
    }

    public static function toStorage($record)
    {

        $storage = null;

        if ($record) {

            $storage = new Storage();

            $storage->setId($record['id']);
            $storage->setDesignation($record['designation']);
            $storage->setCode($record['code']);
        }

        return $storage;
    }

    public static function toStorageCollection($records)
    {

        $storages = null;

        if ($records) {
            $storages = array();

            foreach ($records as $record) {
                array_push($storages, Utilities::toStorage($record));
            }
        }

        return $storages;
    }

    public static function toValidity($record)
    {

        $validity = null;

        if ($record) {
            $validity = new Validity();

            $validity->setId($record['validity_id']);
            $validity->setQuantity($record['quantity']);

            $validity->setExpirationDate(
                Utilities::toPHPDatetime($record['expiration_date'])
            );

            $product = new Product();

            $product->setId($record['product_id']);
            $product->setDescription($record['description']);
            $product->setUnit($record['unit']);
            $product->setTotalQuantity((float)$record['total_quantity']);

            $validity->setProduct($product);
        }

        return $validity;
    }

    public static function toValidityCollection($records)
    {

        $validities = null;

        if ($records) {
            $validities = array();

            foreach ($records as $record) {
                array_push($validities, Utilities::toValidity($record));
            }
        }

        return $validities;
    }

    public static function toStoredProduct($record)
    {

        $storedProduct = null;

        if ($record) {
            $storedProduct = new StoredProduct();

            $product = new Product();
            $product->setId($record['product_id']);
            $product->setDescription($record['description']);
            $product->setUnit($record['unit']);
            $product->setTotalQuantity((float)$record['total_quantity']);
            $product->setLowestPrice((float)$record['lowest_price']);

            $storage = new Storage();
            $storage->setId($record['storage_id']);
            $storage->setDesignation($record['designation']);
            $storage->setCode($record['code']);

            $storedProduct->setProduct($product);
            $storedProduct->setStorage($storage);
            $storedProduct->setQuantity($record['quantity']);
        }

        return $storedProduct;
    }

    public static function toStoredProductCollection($records)
    {

        $storedProducts = null;

        if ($records) {
            $storedProducts = array();

            foreach ($records as $record) {
                array_push($storedProducts, Utilities::toStoredProduct($record));
            }
        }

        return $storedProducts;
    }

    public static function toSupplierProduct($record)
    {

        $supplierProduct = null;

        if ($record) {
            $supplierProduct = new SupplierProduct();

            $product = new Product();
            $product->setId($record['product_id']);
            $product->setDescription($record['description']);
            $product->setUnit($record['unit']);
            $product->setLowestPrice((float)$record['lowest_price']);
            $product->setTotalQuantity((float)$record['total_quantity']);

            $supplier = new Supplier();
            $supplier->setId($record['supplier_id']);
            $supplier->setName($record['name']);
            $supplier->setVatNumber($record['vatNumber']);

            $supplierProduct->setProduct($product);
            $supplierProduct->setSupplier($supplier);
            $supplierProduct->setPrice($record['price']);
        }

        return $supplierProduct;
    }

    public static function toSupplierProductCollection($records)
    {

        $supplierProducts = null;

        if ($records) {
            $supplierProducts = array();

            foreach ($records as $record) {
                array_push(
                    $supplierProducts, Utilities::toSupplierProduct($record)
                );
            }
        }

        return $supplierProducts;
    }

    public static function toUser($record)
    {

        $user = null;

        if ($record) {
            $user = new User();

            $user->setId($record['id']);
            $user->setEmail($record['email']);
            $user->setPassword($record['password']);
        }

        return $user;
    }

    public static function toUserCollection($records)
    {
        $users = null;

        if ($records) {
            $users = array();

            foreach ($records as $record) {
                array_push($users, Utilities::toUser($record));
            }
        }

        return $users;
    }

    public static function toDatabaseDatetime(?\Datetime $datetime)
    {
        $mysqlDatetime = date('Y-m-d H:i:s', $datetime->getTimestamp());

        return $mysqlDatetime;
    }

    public static function toPHPDatetime(?string $mysqlDatetime)
    {

        $phpDatetime = new \DateTime($mysqlDatetime);

        return $phpDatetime;
    }

    public static function toVisibleDate($timestamp)
    {

        $date = date('d-m-Y', $timestamp);

        return $date;
    }

    public static function isDate($param)    {
        return self::isDateFormat($param, 'Y-m-d') ||
        self::isDateFormat($param, 'Y-m-d\TH:i') ||
        self::isDateFormat($param, 'Y-m-d\TH:i') ||
        self::isDateFormat($param, 'Y-m-d\TH:i:s') ||
        self::isDateFormat($param, 'y-m-d') ||
        self::isDateFormat($param, 'y-m-d\TH:i') ||
        self::isDateFormat($param, 'y-m-d\TH:i:s');    
    }    
            
    private static function isDateFormat($stringDate, $format)    {
        try {
            $datetime = new \DateTime($stringDate);

            return $datetime && $datetime->format($format) === $stringDate;

        }
        catch (\Exception $exception) {
            return false;
        }    
    }
}
