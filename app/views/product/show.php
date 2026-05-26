<?php include 'app/views/shares/header.php'; ?>

<div class="card shadow-lg">
    <div class="card-header bg-primary text-white text-center">
        <h2 class="mb-0">Chi ti&#7871;t s&#7843;n ph&#7849;m</h2>
    </div>

    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <?php if (!empty($product->image)): ?>
                    <img
                        src="/<?php echo htmlspecialchars($product->image, ENT_QUOTES, 'UTF-8'); ?>"
                        class="img-fluid rounded"
                        alt="<?php echo htmlspecialchars($product->name, ENT_QUOTES, 'UTF-8'); ?>"
                    >
                <?php else: ?>
                    <div class="alert alert-light text-center">
                        Ch&#432;a c&oacute; h&igrave;nh &#7843;nh
                    </div>
                <?php endif; ?>
            </div>

            <div class="col-md-6">
                <h3 class="card-title text-dark font-weight-bold">
                    <?php echo htmlspecialchars($product->name, ENT_QUOTES, 'UTF-8'); ?>
                </h3>

                <p class="card-text">
                    <?php echo nl2br(htmlspecialchars($product->description, ENT_QUOTES, 'UTF-8')); ?>
                </p>

                <p class="text-danger font-weight-bold h4">
                    <?php echo number_format($product->price, 0, ',', '.'); ?> VND
                </p>

                <p>
                    <strong>Danh m&#7909;c:</strong>
                    <?php echo !empty($product->category_name)
                        ? htmlspecialchars($product->category_name, ENT_QUOTES, 'UTF-8')
                        : 'Chua co danh muc'; ?>
                </p>

                <div class="mt-4">
                    <a href="/Product/addToCart/<?php echo $product->id; ?>"
                       class="btn btn-success px-4">
                        Th&ecirc;m v&agrave;o gi&#7887; h&agrave;ng
                    </a>
                    <a href="/Product/edit/<?php echo $product->id; ?>"
                       class="btn btn-warning px-4 ml-2">
                        S&#7917;a
                    </a>
                    <a href="/Product" class="btn btn-secondary px-4 ml-2">
                        Quay l&#7841;i danh s&aacute;ch
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'app/views/shares/footer.php'; ?>
