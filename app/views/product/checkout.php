<?php include 'app/views/shares/header.php'; ?>

<?php
$total = 0;

foreach ($cart as $item) {
    $total += $item['price'] * $item['quantity'];
}
?>

<h1>Thanh toan</h1>

<div class="alert alert-info">
    Tong thanh toan:
    <strong><?php echo number_format($total, 0, ',', '.'); ?> VND</strong>
</div>

<form method="POST" action="/Vnpay/createPayment">
    <div class="form-group">
        <label for="name">Ho ten:</label>
        <input type="text" id="name" name="name" class="form-control" required>
    </div>

    <div class="form-group">
        <label for="phone">So dien thoai:</label>
        <input type="text" id="phone" name="phone" class="form-control" required>
    </div>

    <div class="form-group">
        <label for="address">Dia chi giao hang:</label>
        <textarea id="address" name="address" class="form-control" required></textarea>
    </div>

    <div class="form-group">
        <label>Phuong thuc thanh toan:</label>
        <div class="form-control">VNPAY</div>
    </div>

    <button type="submit" class="btn btn-primary">
        Thanh toan qua VNPAY
    </button>

    <a href="/Product/cart" class="btn btn-secondary">
        Quay lai gio hang
    </a>
</form>

<?php include 'app/views/shares/footer.php'; ?>
