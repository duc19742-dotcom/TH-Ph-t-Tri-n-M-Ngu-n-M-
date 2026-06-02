<?php include 'app/views/shares/header.php'; ?>

<h1>Dang ky tai khoan</h1>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <ul>
            <?php foreach ($errors as $error): ?>
                <li><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form method="POST" action="/Account/save">
    <div class="form-group">
        <label>Username:</label>
        <input type="text" name="username" class="form-control">
    </div>

    <div class="form-group">
    <label>Email:</label>
    <input type="email" name="email" class="form-control">
    </div>

    <div class="form-group">
        <label>Ho ten:</label>
        <input type="text" name="fullname" class="form-control">
    </div>

    <div class="form-group">
        <label>Mat khau:</label>
        <input type="password" name="password" class="form-control">
    </div>

    <div class="form-group">
        <label>Nhap lai mat khau:</label>
        <input type="password" name="confirmpassword" class="form-control">
    </div>



    <button type="submit" class="btn btn-primary">Dang ky</button>
    <a href="/Account/login" class="btn btn-secondary">Dang nhap</a>
</form>

<?php include 'app/views/shares/footer.php'; ?>