<?php

require_once('app/config/database.php');

class VnpayController
{
    private $db;
    private $config;

    public function __construct()
    {
        $this->db = (new Database())->getConnection();
        $this->config = require 'app/config/vnpay.php';
        date_default_timezone_set('Asia/Ho_Chi_Minh');
    }

    public function createPayment()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }

        $cart = $_SESSION['cart'] ?? [];

        if (empty($cart)) {
            header('Location: /Product/cart');
            exit;
        }

        if (!$this->isConfigured()) {
            echo 'Chua cau hinh VNPAY. Hay nhap vnp_TmnCode va vnp_HashSecret vao app/config/vnpay.php.';
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

        if ($amount <= 0) {
            echo 'Tong tien thanh toan khong hop le.';
            return;
        }

        $this->db->beginTransaction();

        try {
            $stmt = $this->db->prepare(
                "INSERT INTO orders
                (name, phone, address, total_amount, order_status, payment_method, payment_status)
                VALUES (:name, :phone, :address, :amount, 'pending', 'vnpay', 'pending')"
            );
            $stmt->execute([
                ':name' => $name,
                ':phone' => $phone,
                ':address' => $address,
                ':amount' => $amount,
            ]);

            $orderId = $this->db->lastInsertId();
            $txnRef = 'ORDER' . $orderId . date('YmdHis');
            $detailStmt = $this->db->prepare(
                "INSERT INTO order_details (order_id, product_id, quantity, price)
                VALUES (:order_id, :product_id, :quantity, :price)"
            );

            foreach ($cart as $productId => $item) {
                $detailStmt->execute([
                    ':order_id' => $orderId,
                    ':product_id' => $productId,
                    ':quantity' => $item['quantity'],
                    ':price' => $item['price'],
                ]);
            }

            $stmt = $this->db->prepare(
                'UPDATE orders SET vnp_txn_ref = :txn_ref WHERE id = :id'
            );
            $stmt->execute([
                ':txn_ref' => $txnRef,
                ':id' => $orderId,
            ]);

            $this->db->commit();
        } catch (Exception $exception) {
            $this->db->rollBack();
            echo 'Khong tao duoc don hang.';
            return;
        }

        $params = [
            'vnp_Version' => '2.1.0',
            'vnp_Command' => 'pay',
            'vnp_TmnCode' => $this->config['tmn_code'],
            'vnp_Amount' => $amount * 100,
            'vnp_CurrCode' => 'VND',
            'vnp_TxnRef' => $txnRef,
            'vnp_OrderInfo' => 'Thanh toan don hang ' . $txnRef,
            'vnp_OrderType' => 'other',
            'vnp_Locale' => 'vn',
            'vnp_ReturnUrl' => $this->config['return_url'],
            'vnp_IpAddr' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
            'vnp_CreateDate' => date('YmdHis'),
            'vnp_ExpireDate' => date('YmdHis', strtotime('+15 minutes')),
        ];

        $hashData = $this->buildHashData($params);
        $params['vnp_SecureHash'] = hash_hmac('sha512', $hashData, $this->config['hash_secret']);

        header('Location: ' . $this->config['payment_url'] . '?' . http_build_query($params));
        exit;
    }

    public function returnUrl()
    {
        $data = $this->getVnpayParameters($_GET);
        $order = $this->findOrder($data['vnp_TxnRef'] ?? '');
        $validSignature = $this->verifySignature($data);
        $validAmount = $order
            && (int) $order->total_amount === (int) (($data['vnp_Amount'] ?? 0) / 100);
        $responseCode = $data['vnp_ResponseCode'] ?? '';

        $paymentSuccess = $validSignature && $validAmount
            && $responseCode === '00'
            && $order->payment_status === 'paid';

        include 'app/views/payment/vnpayResult.php';
    }

    public function ipn()
    {
        $data = $this->getVnpayParameters($_GET);

        if (!$this->verifySignature($data)) {
            $this->respondIpn('97', 'Invalid Signature');
            return;
        }

        $order = $this->findOrder($data['vnp_TxnRef'] ?? '');

        if (!$order) {
            $this->respondIpn('01', 'Order not found');
            return;
        }

        if ((int) $order->total_amount !== (int) (($data['vnp_Amount'] ?? 0) / 100)) {
            $this->respondIpn('04', 'Invalid amount');
            return;
        }

        if ($order->payment_status === 'paid') {
            $this->respondIpn('02', 'Order already confirmed');
            return;
        }

        if (($data['vnp_ResponseCode'] ?? '') === '00'
            && ($data['vnp_TransactionStatus'] ?? '') === '00') {
            $stmt = $this->db->prepare(
                "UPDATE orders SET
                    payment_status = 'paid',
                    order_status = 'confirmed',
                    vnp_transaction_no = :transaction_no,
                    vnp_response_code = :response_code,
                    vnp_bank_code = :bank_code,
                    vnp_pay_date = :pay_date,
                    paid_at = NOW()
                 WHERE id = :id AND payment_status = 'pending'"
            );
            $stmt->execute([
                ':transaction_no' => $data['vnp_TransactionNo'] ?? null,
                ':response_code' => $data['vnp_ResponseCode'],
                ':bank_code' => $data['vnp_BankCode'] ?? null,
                ':pay_date' => $data['vnp_PayDate'] ?? null,
                ':id' => $order->id,
            ]);
        }

        $this->respondIpn('00', 'Confirm Success');
    }

    private function isConfigured()
    {
        return $this->config['tmn_code'] !== 'VNP_TMN_CODE_CUA_BAN'
            && $this->config['hash_secret'] !== 'VNP_HASH_SECRET_CUA_BAN';
    }

    private function getVnpayParameters(array $source)
    {
        $data = [];

        foreach ($source as $key => $value) {
            if (strpos($key, 'vnp_') === 0) {
                $data[$key] = $value;
            }
        }

        return $data;
    }

    private function verifySignature(array $data)
    {
        $receivedHash = $data['vnp_SecureHash'] ?? '';
        unset($data['vnp_SecureHash'], $data['vnp_SecureHashType']);

        return $receivedHash !== ''
            && hash_equals(
                hash_hmac('sha512', $this->buildHashData($data), $this->config['hash_secret']),
                $receivedHash
            );
    }

    private function buildHashData(array $params)
    {
        ksort($params);
        $pairs = [];

        foreach ($params as $key => $value) {
            if ($value !== '' && $value !== null) {
                $pairs[] = urlencode($key) . '=' . urlencode($value);
            }
        }

        return implode('&', $pairs);
    }

    private function findOrder($txnRef)
    {
        $stmt = $this->db->prepare('SELECT * FROM orders WHERE vnp_txn_ref = :txn_ref');
        $stmt->execute([':txn_ref' => $txnRef]);

        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    private function respondIpn($code, $message)
    {
        header('Content-Type: application/json');
        echo json_encode(['RspCode' => $code, 'Message' => $message]);
    }
}

?>
