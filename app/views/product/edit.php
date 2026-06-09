<?php include 'app/views/shares/header.php'; ?>

<h1>Sua san pham</h1>

<form id="edit-product-form">
    <input type="hidden" id="id" name="id" value="<?php echo htmlspecialchars($product->id, ENT_QUOTES, 'UTF-8'); ?>">

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

    <button type="submit" class="btn btn-primary">Luu thay doi</button>
</form>

<a href="/Product" class="btn btn-secondary mt-2">Quay lai danh sach san pham</a>

<script>
$(document).ready(function () {
    if (!getToken()) {
        alert('Vui long dang nhap');
        window.location.href = '/Account/login';
        return;
    }

    const productId = <?php echo json_encode($product->id); ?>;
    const categorySelect = document.getElementById('category_id');

    $.ajax({
        url: `/api/product/${productId}`,
        method: 'GET',
        headers: {
            Authorization: 'Bearer ' + getToken()
        },
        dataType: 'json',
        success: function (product) {
            document.getElementById('id').value = product.id;
            document.getElementById('name').value = product.name;
            document.getElementById('description').value = product.description;
            document.getElementById('price').value = product.price;
            categorySelect.dataset.selected = product.category_id;
        }
    });

    $.ajax({
        url: '/api/category',
        method: 'GET',
        dataType: 'json',
        success: function (categories) {
            categories.forEach(category => {
                const option = document.createElement('option');
                option.value = category.id;
                option.textContent = category.name;
                categorySelect.appendChild(option);
            });

            if (categorySelect.dataset.selected) {
                categorySelect.value = categorySelect.dataset.selected;
            }
        }
    });

    $('#edit-product-form').on('submit', function (event) {
        event.preventDefault();

        const formData = new FormData(this);
        const jsonData = {};

        formData.forEach((value, key) => {
            jsonData[key] = value;
        });

        $.ajax({
            url: `/api/product/${jsonData.id}`,
            method: 'PUT',
            headers: {
                Authorization: 'Bearer ' + getToken()
            },
            data: JSON.stringify(jsonData),
            contentType: 'application/json',
            dataType: 'json',
            success: function (data) {
                if (data.message === 'Product updated successfully') {
                    window.location.href = '/Product';
                } else {
                    alert('Cap nhat san pham that bai');
                }
            },
            error: function () {
                alert('Cap nhat san pham that bai');
            }
        });
    });
});

function getToken() {
    return localStorage.getItem('jwtToken');
}
</script>

<?php include 'app/views/shares/footer.php'; ?>
