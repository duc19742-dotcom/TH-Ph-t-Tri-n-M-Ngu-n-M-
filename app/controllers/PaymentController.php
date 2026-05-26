<?php

require_once('app/config/database.php');

class PaymentController
{
    private $db;
    private $momo;

    public function __construct()
    {
        $this->db = (new Database())->getConnection();
        $this->momo = require 'app/config/momo.php';
    }

    public function createMomoPayment()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }

        $cart = $_SESSION['cart'] ?? [];

        if (empty($cart)) {
            header('Location: /Product/cart');
            exit;
        }

        if (!$this->isMomoConfigured()) {
            echo 'Chua cau hinh thong tin MoMo va URL HTTPS public trong app/config/momo.php.';
            return;
        }

        $name = trim($_POST['name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');

        if ($name === '' || $phone === '' || $address === '') {
            echo 'Vui long nhap day du thong tin giao hang.';
            return;
        }

        $amount = 0;

        foreach ($cart as $item) {
            $amount += (int) $item['price'] * (int) $item['quantity'];
        }

        if ($amount < 1000) {
            echo 'MoMo yeu cau so tien thanh toan toi thieu 1.000 VND.';
            return;
        }

        $this->db->beginTransaction();

        try {
            $stmt = $this->db->prepare(
                "INSERT INTO orders
                (name, phone, address, total_amount, order_status, payment_method, payment_status)
                VALUES (:name, :phone, :address, :total_amount, 'pending', 'momo', 'pending')"
            );

            $stmt->execute([
                ':name' => $name,
                ':phone' => $phone,
                ':address' => $address,
                ':total_amount' => $amount
            ]);

            $orderDbId = $this->db->lastInsertId();

            $detailStmt = $this->db->prepare(
                "INSERT INTO order_details
                (order_id, product_id, quantity, price)
                VALUES (:order_id, :product_id, :quantity, :price)"
            );

            foreach ($cart as $productId => $item) {
                $detailStmt->execute([
                    ':order_id' => $orderDbId,
                    ':product_id' => $productId,
                    ':quantity' => $item['quantity'],
                    ':price' => $item['price']
                ]);
            }

            $momoOrderId = 'ORDER_' . $orderDbId . '_' . time();
            $requestId = 'REQ_' . bin2hex(random_bytes(8));

            $stmt = $this->db->prepare(
                "UPDATE orders
                 SET momo_order_id = :momo_order_id, momo_request_id = :momo_request_id
                 WHERE id = :id"
            );

            $stmt->execute([
                ':momo_order_id' => $momoOrderId,
                ':momo_request_id' => $requestId,
                ':id' => $orderDbId
            ]);

            $this->db->commit();
        } catch (Exception $exception) {
            $this->db->rollBack();
            echo 'Khong tao duoc don hang.';
            return;
        }

        $extraData = '';
        $orderInfo = 'Thanh toan don hang ' . $momoOrderId;
        $requestType = 'captureWallet';

        $rawSignature =
            'accessKey=' . $this->momo['access_key'] .
            '&amount=' . $amount .
            '&extraData=' . $extraData .
            '&ipnUrl=' . $this->momo['ipn_url'] .
            '&orderId=' . $momoOrderId .
            '&orderInfo=' . $orderInfo .
            '&partnerCode=' . $this->momo['partner_code'] .
            '&redirectUrl=' . $this->momo['redirect_url'] .
            '&requestId=' . $requestId .
            '&requestType=' . $requestType;

        $signature = hash_hmac(
            'sha256',
            $rawSignature,
            $this->momo['secret_key']
        );

        $payload = [
            'partnerCode' => $this->momo['partner_code'],
            'requestId' => $requestId,
            'amount' => $amount,
            'orderId' => $momoOrderId,
            'orderInfo' => $orderInfo,
            'redirectUrl' => $this->momo['redirect_url'],
            'ipnUrl' => $this->momo['ipn_url'],
            'requestType' => $requestType,
            'extraData' => $extraData,
            'lang' => 'vi',
            'signature' => $signature
        ];

        $response = $this->postJson($this->momo['endpoint'], $payload);

        if (($response['resultCode'] ?? -1) === 0 && !empty($response['payUrl'])) {
            header('Location: ' . $response['payUrl']);
            exit;
        }

        echo 'Khong khoi tao duoc thanh toan MoMo.';
    }

    private function postJson($url, array $payload)
    {
        $curl = curl_init($url);

        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_TIMEOUT => 30
        ]);

        $response = curl_exec($curl);
        curl_close($curl);

        return json_decode($response, true) ?? [];
    }

    private function isMomoConfigured()
    {
        return strpos($this->momo['partner_code'], 'YOUR_') !== 0
            && strpos($this->momo['access_key'], 'YOUR_') !== 0
            && strpos($this->momo['secret_key'], 'YOUR_') !== 0
            && strpos($this->momo['redirect_url'], 'your-public-domain.example') === false
            && strpos($this->momo['ipn_url'], 'your-public-domain.example') === false;
    }

    public function momoReturn()
{
    $orderId = $_GET['orderId'] ?? '';
    $resultCode = (int) ($_GET['resultCode'] ?? -1);

    $stmt = $this->db->prepare(
        "SELECT * FROM orders WHERE momo_order_id = :order_id"
    );

    $stmt->execute([':order_id' => $orderId]);
    $order = $stmt->fetch(PDO::FETCH_OBJ);

    include 'app/views/payment/result.php';
}

public function momoIpn()
{
    $data = json_decode(file_get_contents('php://input'), true) ?? [];

    $required = [
        'partnerCode', 'orderId', 'requestId', 'amount',
        'orderInfo', 'orderType', 'transId', 'resultCode',
        'message', 'payType', 'responseTime', 'extraData', 'signature'
    ];

    foreach ($required as $field) {
        if (!array_key_exists($field, $data)) {
            http_response_code(400);
            echo json_encode(['message' => 'Missing field']);
            return;
        }
    }

    $rawSignature =
        'accessKey=' . $this->momo['access_key'] .
        '&amount=' . $data['amount'] .
        '&extraData=' . $data['extraData'] .
        '&message=' . $data['message'] .
        '&orderId=' . $data['orderId'] .
        '&orderInfo=' . $data['orderInfo'] .
        '&orderType=' . $data['orderType'] .
        '&partnerCode=' . $data['partnerCode'] .
        '&payType=' . $data['payType'] .
        '&requestId=' . $data['requestId'] .
        '&responseTime=' . $data['responseTime'] .
        '&resultCode=' . $data['resultCode'] .
        '&transId=' . $data['transId'];

    $signature = hash_hmac(
        'sha256',
        $rawSignature,
        $this->momo['secret_key']
    );

    if (!hash_equals($signature, $data['signature'])) {
        http_response_code(400);
        echo json_encode(['message' => 'Invalid signature']);
        return;
    }

    $stmt = $this->db->prepare(
        "SELECT * FROM orders WHERE momo_order_id = :order_id"
    );

    $stmt->execute([':order_id' => $data['orderId']]);
    $order = $stmt->fetch(PDO::FETCH_OBJ);

    if (!$order || (int) $order->total_amount !== (int) $data['amount']) {
        http_response_code(400);
        echo json_encode(['message' => 'Invalid order']);
        return;
    }

    if ((int) $data['resultCode'] === 0 && $order->payment_status !== 'paid') {
        $stmt = $this->db->prepare(
            "UPDATE orders
             SET payment_status = 'paid',
                 order_status = 'confirmed',
                 momo_trans_id = :trans_id,
                 momo_result_code = :result_code,
                 paid_at = NOW()
             WHERE id = :id"
        );

        $stmt->execute([
            ':trans_id' => $data['transId'],
            ':result_code' => $data['resultCode'],
            ':id' => $order->id
        ]);
    }

    echo json_encode(['message' => 'Success']);
}
}
