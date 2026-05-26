<?php include 'app/views/shares/header.php'; ?>

<h1>Gio hang</h1>

<?php if (empty($cart)): ?>
    <div class="alert alert-info">Gio hang dang trong.</div>
    <a href="/Product" class="btn btn-primary">Tiep tuc mua sam</a>
<?php else: ?>
    <?php $total = 0; ?>

    <form method="POST" action="/Product/updateCart">
        <table class="table">
            <thead>
                <tr>
                    <th>San pham</th>
                    <th>Hinh anh</th>
                    <th>Gia</th>
                    <th>So luong</th>
                    <th>Thanh tien</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cart as $id => $item): ?>
                    <?php
                    $subtotal = $item['price'] * $item['quantity'];
                    $total += $subtotal;
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td>
                            <?php if (!empty($item['image'])): ?>
                                <img src="/<?php echo htmlspecialchars($item['image'], ENT_QUOTES, 'UTF-8'); ?>"
                                     style="max-width: 70px;"
                                     alt="">
                            <?php endif; ?>
                        </td>
                        <td><?php echo number_format($item['price'], 0, ',', '.'); ?> VND</td>
                        <td>
                            <input type="number"
                                   name="quantity[<?php echo $id; ?>]"
                                   value="<?php echo $item['quantity']; ?>"
                                   min="1"
                                   class="form-control"
                                   style="width: 90px;">
                        </td>
                        <td><?php echo number_format($subtotal, 0, ',', '.'); ?> VND</td>
                        <td>
                            <a href="/Product/removeFromCart/<?php echo $id; ?>"
                               class="btn btn-danger">
                                Xoa
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <h4>Tong cong: <?php echo number_format($total, 0, ',', '.'); ?> VND</h4>

        <button type="submit" class="btn btn-secondary">Cap nhat gio hang</button>
        <a href="/Product/checkout" class="btn btn-success">Thanh toan</a>
    </form>
<?php endif; ?>

<?php include 'app/views/shares/footer.php'; ?>