<?php

require_once 'app/config/database.php';
require_once 'app/models/ProductModel.php';
require_once 'app/utils/JWTHandler.php';

class ProductApiController
{
    private $productModel;
    private $jwtHandler;

    public function __construct()
    {
        $db = (new Database())->getConnection();
        $this->productModel = new ProductModel($db);
        $this->jwtHandler = new JWTHandler();
    }

    private function authenticate()
    {
        $headers = function_exists('apache_request_headers') ? apache_request_headers() : [];
        $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? $_SERVER['HTTP_AUTHORIZATION'] ?? '';

        if (!$authHeader || stripos($authHeader, 'Bearer ') !== 0) {
            return false;
        }

        $jwt = trim(substr($authHeader, 7));
        return $this->jwtHandler->decode($jwt) !== null;
    }

    private function requireAuth()
    {
        if ($this->authenticate()) {
            return true;
        }

        http_response_code(401);
        echo json_encode(['message' => 'Unauthorized']);
        return false;
    }

    public function index()
    {
        if (!$this->requireAuth()) {
            return;
        }

        echo json_encode($this->productModel->getProducts());
    }

    public function show($id)
    {
        if (!$this->requireAuth()) {
            return;
        }

        $product = $this->productModel->getProductById($id);

        if ($product) {
            echo json_encode($product);
            return;
        }

        http_response_code(404);
        echo json_encode(['message' => 'Product not found']);
    }

    public function store()
    {
        if (!$this->requireAuth()) {
            return;
        }

        $data = $this->getJsonInput();

        $result = $this->productModel->addProduct(
            $data['name'] ?? '',
            $data['description'] ?? '',
            $data['price'] ?? '',
            $data['category_id'] ?? null
        );

        if (is_array($result)) {
            http_response_code(400);
            echo json_encode(['errors' => $result]);
            return;
        }

        if ($result) {
            http_response_code(201);
            echo json_encode(['message' => 'Product created successfully']);
            return;
        }

        http_response_code(400);
        echo json_encode(['message' => 'Product creation failed']);
    }

    public function update($id)
    {
        if (!$this->requireAuth()) {
            return;
        }

        $data = $this->getJsonInput();

        $result = $this->productModel->updateProduct(
            $id,
            $data['name'] ?? '',
            $data['description'] ?? '',
            $data['price'] ?? '',
            $data['category_id'] ?? null
        );

        if (is_array($result)) {
            http_response_code(400);
            echo json_encode(['errors' => $result]);
            return;
        }

        if ($result) {
            echo json_encode(['message' => 'Product updated successfully']);
            return;
        }

        http_response_code(400);
        echo json_encode(['message' => 'Product update failed']);
    }

    public function destroy($id)
    {
        if (!$this->requireAuth()) {
            return;
        }

        if ($this->productModel->deleteProduct($id)) {
            echo json_encode(['message' => 'Product deleted successfully']);
            return;
        }

        http_response_code(400);
        echo json_encode(['message' => 'Product deletion failed']);
    }

    private function getJsonInput()
    {
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);

        return is_array($data) ? $data : [];
    }
}

?>
