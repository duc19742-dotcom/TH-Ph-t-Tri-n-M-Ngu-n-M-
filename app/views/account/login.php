<?php include 'app/views/shares/header.php'; ?>

<h1>Dang nhap</h1>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger">
        <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
    </div>
<?php endif; ?>

<form id="login-form" method="POST" action="/Account/checkLogin">
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

<script>
$(document).ready(function () {
    $('#login-form').on('submit', function (event) {
        event.preventDefault();

        const data = {
            login: $('input[name="login"]').val(),
            password: $('input[name="password"]').val()
        };

        $.ajax({
            url: '/api/account',
            method: 'POST',
            data: JSON.stringify(data),
            contentType: 'application/json',
            dataType: 'json',
            success: function (response) {
                localStorage.setItem('jwtToken', response.token);
                window.location.href = '/Product';
            },
            error: function () {
                alert('Dang nhap that bai');
            }
        });
    });
});
</script>

<?php include 'app/views/shares/footer.php'; ?>
