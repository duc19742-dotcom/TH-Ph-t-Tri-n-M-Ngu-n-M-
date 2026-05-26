<?php include 'app/views/shares/header.php'; ?>

<h1>Ket qua thanh toan</h1>

<?php if (!$order): ?>
    <div class="alert alert-danger">Khong tim thay don hang.</div>
<?php elseif ($order->payment_status === 'paid'): ?>
    <?php unset($_SESSION['cart']); ?>

    <div class="alert alert-success">
        Thanh toan MoMo thanh cong.
    </div>

    <p>Ma don hang: <?php echo htmlspecialchars($order->momo_order_id, ENT_QUOTES, 'UTF-8'); ?></p>
    <p>So tien: <?php echo number_format($order->total_amount, 0, ',', '.'); ?> VND</p>
<?php else: ?>
    <div class="alert alert-warning">
        Giao dich chua duoc xac nhan thanh cong.
    </div>

    <p>Trang thai hien tai: <?php echo htmlspecialchars($order->payment_status, ENT_QUOTES, 'UTF-8'); ?></p>
<?php endif; ?>

<a href="/Product" class="btn btn-primary">Ve trang san pham</a>

<?php include 'app/views/shares/footer.php'; ?>