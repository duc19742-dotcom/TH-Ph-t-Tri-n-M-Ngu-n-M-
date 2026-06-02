<?php include 'app/views/shares/header.php'; ?>

<h1>Dang nhap</h1>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger">
        <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
    </div>
<?php endif; ?>

<form method="POST" action="/Account/checkLogin">
    <div class="form-group">
        <label>Username hoac email:</label>
        <input type="text" name="login" class="form-control">
    </div>

    <div class="form-group">
        <label>Mat khau:</label>
        <input type="password" name="password" class="form-control">
    </div>

    <button type="submit" class="btn btn-primary">Dang nhap</button>
    <a href="/Account/register" class="btn btn-secondary">Dang ky</a>

    <a href="/Account/google" class="btn btn-danger">
        Dang nhap bang Google
    </a>

    <a href="/Account/github" class="btn btn-dark">
        Dang nhap bang GitHub
    </a>
</form>

<?php include 'app/views/shares/footer.php'; ?>
