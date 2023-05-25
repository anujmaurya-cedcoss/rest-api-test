<?php

use Phalcon\Mvc\Micro;
use Phalcon\Di\FactoryDefault;
use Phalcon\Mvc\Collection\Manager;

define("BASE_PATH", (__DIR__));
require_once(BASE_PATH . '/vendor/autoload.php');


// Use Loader() to autoload our model
$container = new FactoryDefault();
$container->set(
    'mongo',
    function () {
        $mongo = new MongoDB\Client(
            'mongodb+srv://root:VajsFVXK36vxh4M6@cluster0.nwpyx9q.mongodb.net/?retryWrites=true&w=majority'
        );
        return $mongo->rest_api;
    },
    true
);
$container->set(
    'collectionManager',
    function () {
        return new Manager();
    }
);
$app = new Micro($container);
// Define the routes here

// Retrieves all products
$app->get(
    '/api/product',
    function () {
        $collection = $this->mongo->products;
        $productList = $collection->find();
        $data = [];

        foreach ($productList as $product) {
            $data[] = [
                'id' => $product['id'],
                'name' => $product['name'],
                'price' => $product['price'],
            ];
        }
        echo json_encode($data);
    }
);

// Searches for product with $name in their name
$app->get(
    '/api/product/search/{name}',
    function ($name) {
        $collection = $this->mongo->products;
        $productList = $collection->find(["name" => $name]);

        $data = [];

        foreach ($productList as $product) {
            $data[] = [
                'name' => $product['name'],
                'price' => $product['price'],
                'stock' => $product['stock'],
            ];
        }
        echo json_encode($data);
    }
);

// Retrieves products based on key
$app->get(
    '/api/product/{id:[0-9]+}',
    function ($id) {
        $collection = $this->mongo->products;
        $product = $collection->findOne(["id" => (int)$id]);
        echo json_encode($product);
    }
);

// Adds a new product
$app->post(
    '/api/product',
    function () use ($app) {
        $product = $app->request->getJsonRawBody();
        $collection = $this->mongo->products;
        $arr = [
            "id" => $product->id,
            "name" => $product->name,
            "price" => $product->price,
            "stock" => $product->stock
        ];
        $status = $collection->insertOne($arr);
        return var_dump($status);
    }
);

// update the product
$app->put(
    '/api/product/{id:[0-9]+}',
    function ($id) use ($app) {
        $product = $app->request->getJsonRawBody();
        $response = $this->mongo->products->updateOne(['id' => (int) $id], ['$set' => ['name' => $product->name]]);
        return $response;
    }
);

// Deletes robots based on primary key
$app->delete(
    '/api/product/{id:[0-9]+}',
    function ($id) use ($app) {
        $response = $this->mongo->products->deleteOne(["id" => (int) $id]);
        return $response;
    }
);

$app->handle($_SERVER['REQUEST_URI']);
