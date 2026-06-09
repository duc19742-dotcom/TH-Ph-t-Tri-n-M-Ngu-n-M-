<?php include 'app/views/shares/header.php'; ?>

<h1>Them san pham moi</h1>

<form id="add-product-form">
    <div class="form-group">
        <label for="name">Ten san pham:</label>
        <input type="text" id="name" name="name" class="form-control" required>
    </div>

    <div class="form-group">
        <label for="description">Mo ta:</label>
        <textarea id="description" name="description" class="form-control" required></textarea>
    </div>

    <div class="form-group">
        <label for="price">Gia:</label>
        <input type="number" id="price" name="price" class="form-control" step="0.01" required>
    </div>

    <div class="form-group">
        <label for="category_id">Danh muc:</label>
        <select id="category_id" name="category_id" class="form-control" required></select>
    </div>

    <button type="submit" class="btn btn-primary">Them san pham</button>
</form>

<a href="/Product" class="btn btn-secondary mt-2">Quay lai danh sach san pham</a>

<script>
$(document).ready(function () {
    if (!getToken()) {
        alert('Vui long dang nhap');
        window.location.href = '/Account/login';
        return;
    }

    $.ajax({
        url: '/api/category',
        method: 'GET',
        dataType: 'json',
        success: function (categories) {
            const categorySelect = document.getElementById('category_id');
            categories.forEach(category => {
                const option = document.createElement('option');
                option.value = category.id;
                option.textContent = category.name;
                categorySelect.appendChild(option);
            });
        }
    });

    $('#add-product-form').on('submit', function (event) {
        event.preventDefault();

        const formData = new FormData(this);
        const jsonData = {};

        formData.forEach((value, key) => {
            jsonData[key] = value;
        });

        $.ajax({
            url: '/api/product',
            method: 'POST',
            headers: {
                Authorization: 'Bearer ' + getToken()
            },
            data: JSON.stringify(jsonData),
            contentType: 'application/json',
            dataType: 'json',
            success: function (data) {
                if (data.message === 'Product created successfully') {
                    window.location.href = '/Product';
                } else {
                    alert('Them san pham that bai');
                }
            },
            error: function () {
                alert('Them san pham that bai');
            }
        });
    });
});

function getToken() {
    return localStorage.getItem('jwtToken');
}
</script>

<?php include 'app/views/shares/footer.php'; ?>
