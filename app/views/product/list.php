<?php include 'app/views/shares/header.php'; ?>

<h1>Danh sach san pham</h1>

<?php if (SessionHelper::isAdmin()): ?>
    <a href="/Product/add" class="btn btn-success mb-2">Them san pham moi</a>
<?php endif; ?>

<ul class="list-group" id="product-list"></ul>

<script>
$(document).ready(function () {
    if (!getToken()) {
        alert('Vui long dang nhap');
        window.location.href = '/Account/login';
        return;
    }

    loadProducts();
});

function getToken() {
    return localStorage.getItem('jwtToken');
}

function escapeHtml(value) {
    const div = document.createElement('div');
    div.textContent = value ?? '';
    return div.innerHTML;
}

function loadProducts() {
    $.ajax({
        url: '/api/product',
        method: 'GET',
        headers: {
            Authorization: 'Bearer ' + getToken()
        },
        dataType: 'json',
        success: function (products) {
            const productList = document.getElementById('product-list');
            productList.innerHTML = '';

            products.forEach(product => {
                const productItem = document.createElement('li');
                productItem.className = 'list-group-item';
                productItem.innerHTML = `
                    <h2>
                        <a href="/Product/show/${product.id}">${escapeHtml(product.name)}</a>
                    </h2>
                    <p>${escapeHtml(product.description)}</p>
                    <p>Gia: ${escapeHtml(product.price)} VND</p>
                    <p>Danh muc: ${escapeHtml(product.category_name || '')}</p>
                    <a href="/Product/addToCart/${product.id}" class="btn btn-primary">Them vao gio hang</a>
                    <?php if (SessionHelper::isAdmin()): ?>
                    <a href="/Product/edit/${product.id}" class="btn btn-warning">Sua</a>
                    <button type="button" class="btn btn-danger" onclick="deleteProduct(${product.id})">Xoa</button>
                    <?php endif; ?>
                `;
                productList.appendChild(productItem);
            });
        },
        error: function (xhr) {
            if (xhr.status === 401) {
                alert('Vui long dang nhap');
                window.location.href = '/Account/login';
                return;
            }

            document.getElementById('product-list').innerHTML =
                '<li class="list-group-item text-danger">Khong tai duoc danh sach san pham.</li>';
        }
    });
}

function deleteProduct(id) {
    if (!confirm('Ban co chac chan muon xoa san pham nay?')) {
        return;
    }

    $.ajax({
        url: `/api/product/${id}`,
        method: 'DELETE',
        headers: {
            Authorization: 'Bearer ' + getToken()
        },
        dataType: 'json',
        success: function (data) {
            if (data.message === 'Product deleted successfully') {
                loadProducts();
            } else {
                alert('Xoa san pham that bai');
            }
        }
    });
}
</script>

<?php include 'app/views/shares/footer.php'; ?>
