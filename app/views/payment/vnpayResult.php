<?php include 'app/views/shares/header.php'; ?>

<h1>K&#7871;t qu&#7843; thanh to&aacute;n VNPAY</h1>

<?php if (!$validSignature): ?>
    <div class="alert alert-danger">Ch&#7919; k&yacute; ph&#7843;n h&#7891;i kh&ocirc;ng h&#7907;p l&#7879;.</div>
<?php elseif (!$order || !$validAmount): ?>
    <div class="alert alert-danger">&#272;&#417;n h&agrave;ng ho&#7863;c s&#7889; ti&#7873;n kh&ocirc;ng h&#7907;p l&#7879;.</div>
<?php elseif ($paymentSuccess): ?>
    <?php unset($_SESSION['cart']); ?>
    <div class="alert alert-success">Thanh to&aacute;n VNPAY th&agrave;nh c&ocirc;ng.</div>
    <p>M&atilde; &#273;&#417;n h&agrave;ng: <?php echo htmlspecialchars($order->vnp_txn_ref, ENT_QUOTES, 'UTF-8'); ?></p>
    <p>S&#7889; ti&#7873;n: <?php echo number_format($order->total_amount, 0, ',', '.'); ?> VND</p>
<?php elseif ($responseCode === '00'): ?>
    <div class="alert alert-warning">
        VNPAY &#273;&atilde; tr&#7843; k&#7871;t qu&#7843; th&agrave;nh c&ocirc;ng, &#273;ang ch&#7901; IPN x&aacute;c nh&#7853;n &#273;&#417;n h&agrave;ng.
    </div>
<?php else: ?>
    <div class="alert alert-danger">Giao d&#7883;ch kh&ocirc;ng th&agrave;nh c&ocirc;ng.</div>
<?php endif; ?>

<a href="/Product" class="btn btn-primary">V&#7873; trang s&#7843;n ph&#7849;m</a>

<?php include 'app/views/shares/footer.php'; ?>
