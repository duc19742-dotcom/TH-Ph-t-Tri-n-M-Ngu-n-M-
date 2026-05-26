<?php

require_once('app/config/database.php');
require_once('app/models/ProductModel.php');
require_once('app/models/CategoryModel.php');

class ProductController
{
    private $productModel;
    private $db;
    private $uploadDirectory;

    public function __construct()
    {
        $this->db = (new Database())->getConnection();
        $this->productModel = new ProductModel($this->db);
        $this->uploadDirectory = dirname(__DIR__, 2) . '/uploads/products';
    }

    public function index()
    {
        $products = $this->productModel->getProducts();
        include 'app/views/product/list.php';
    }

    public function show($id)
    {
        $product = $this->productModel->getProductById($id);

        if ($product) {
            include 'app/views/product/show.php';
        } else {
            echo 'Khong thay san pham.';
        }
    }

    public function add()
    {
        $categories = (new CategoryModel($this->db))->getCategories();
        include 'app/views/product/add.php';
    }

    public function save()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }

        $name = $_POST['name'] ?? '';
        $description = $_POST['description'] ?? '';
        $price = $_POST['price'] ?? '';
        $categoryId = $_POST['category_id'] ?? null;
        $upload = $this->uploadImage();

        if ($upload['error']) {
            $errors = ['image' => $upload['error']];
            $categories = (new CategoryModel($this->db))->getCategories();
            include 'app/views/product/add.php';
            return;
        }

        $result = $this->productModel->addProduct(
            $name,
            $description,
            $price,
            $categoryId,
            $upload['path']
        );

        if (is_array($result)) {
            $this->deleteImageFile($upload['path']);
            $errors = $result;
            $categories = (new CategoryModel($this->db))->getCategories();
            include 'app/views/product/add.php';
            return;
        }

        if ($result === true) {
            header('Location: /Product');
            return;
        }

        $this->deleteImageFile($upload['path']);
        echo 'Da xay ra loi khi them san pham.';
    }

    public function edit($id)
    {
        $product = $this->productModel->getProductById($id);
        $categories = (new CategoryModel($this->db))->getCategories();

        if ($product) {
            include 'app/views/product/edit.php';
        } else {
            echo 'Khong thay san pham.';
        }
    }

    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }

        $id = $_POST['id'] ?? null;
        $product = $this->productModel->getProductById($id);

        if (!$product) {
            echo 'Khong thay san pham.';
            return;
        }

        $upload = $this->uploadImage($product->image);

        if ($upload['error']) {
            $errors = ['image' => $upload['error']];
            $categories = (new CategoryModel($this->db))->getCategories();
            include 'app/views/product/edit.php';
            return;
        }

        $edited = $this->productModel->updateProduct(
            $id,
            $_POST['name'] ?? '',
            $_POST['description'] ?? '',
            $_POST['price'] ?? '',
            $_POST['category_id'] ?? null,
            $upload['path']
        );

        if ($edited) {
            if ($upload['new']) {
                $this->deleteImageFile($product->image);
            }

            header('Location: /Product');
            return;
        }

        if ($upload['new']) {
            $this->deleteImageFile($upload['path']);
        }

        echo 'Da xay ra loi khi luu san pham.';
    }

    public function delete($id)
    {
        $product = $this->productModel->getProductById($id);

        if ($this->productModel->deleteProduct($id)) {
            if ($product) {
                $this->deleteImageFile($product->image);
            }

            header('Location: /Product');
        } else {
            echo 'Da xay ra loi khi xoa san pham.';
        }
    }

    private function uploadImage($existingImage = null)
    {
        if (!isset($_FILES['image']) || $_FILES['image']['error'] === UPLOAD_ERR_NO_FILE) {
            return ['path' => $existingImage, 'new' => false, 'error' => null];
        }

        if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            return ['path' => $existingImage, 'new' => false, 'error' => 'Khong the tai hinh anh len.'];
        }

        if ($_FILES['image']['size'] > 2 * 1024 * 1024) {
            return ['path' => $existingImage, 'new' => false, 'error' => 'Hinh anh khong duoc vuot qua 2 MB.'];
        }

        $allowedTypes = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
        ];
        $mimeType = (new finfo(FILEINFO_MIME_TYPE))->file($_FILES['image']['tmp_name']);

        if (!isset($allowedTypes[$mimeType])) {
            return ['path' => $existingImage, 'new' => false, 'error' => 'Chi chap nhan anh JPG, PNG, GIF hoac WEBP.'];
        }

        if (!is_dir($this->uploadDirectory) && !mkdir($this->uploadDirectory, 0775, true)) {
            return ['path' => $existingImage, 'new' => false, 'error' => 'Khong tao duoc thu muc luu anh.'];
        }

        $fileName = bin2hex(random_bytes(16)) . '.' . $allowedTypes[$mimeType];
        $storedPath = 'uploads/products/' . $fileName;
        $destination = $this->uploadDirectory . '/' . $fileName;

        if (!move_uploaded_file($_FILES['image']['tmp_name'], $destination)) {
            return ['path' => $existingImage, 'new' => false, 'error' => 'Khong luu duoc hinh anh.'];
        }

        return ['path' => $storedPath, 'new' => true, 'error' => null];
    }

    private function deleteImageFile($imagePath)
    {
        if (empty($imagePath) || strpos($imagePath, 'uploads/products/') !== 0) {
            return;
        }

        $filePath = dirname(__DIR__, 2) . '/' . $imagePath;

        if (is_file($filePath)) {
            unlink($filePath);
        }
    }

    public function addToCart($id)
    {
        $product = $this->productModel->getProductById($id);

        if (!$product) {
            echo 'Khong tim thay san pham.';
            return;
        }

        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        if (isset($_SESSION['cart'][$id])) {
            $_SESSION['cart'][$id]['quantity']++;
        } else {
            $_SESSION['cart'][$id] = [
                'id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
                'image' => $product->image,
                'quantity' => 1
            ];
        }

        header('Location: /Product/cart');
        exit;
    }

    public function cart()
    {
        $cart = $_SESSION['cart'] ?? [];
        include 'app/views/product/cart.php';
    }

    public function updateCart()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }

        foreach ($_POST['quantity'] ?? [] as $id => $quantity) {
            $quantity = (int) $quantity;

            if ($quantity <= 0) {
                unset($_SESSION['cart'][$id]);
            } elseif (isset($_SESSION['cart'][$id])) {
                $_SESSION['cart'][$id]['quantity'] = $quantity;
            }
        }

        header('Location: /Product/cart');
        exit;
    }

    public function removeFromCart($id)
    {
        if (isset($_SESSION['cart'][$id])) {
            unset($_SESSION['cart'][$id]);
        }

        header('Location: /Product/cart');
        exit;
    }

    public function checkout()
    {
        $cart = $_SESSION['cart'] ?? [];

        if (empty($cart)) {
            header('Location: /Product/cart');
            exit;
        }

        include 'app/views/product/checkout.php';
    }
    }

?>
