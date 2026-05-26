<?php include 'app/views/shares/header.php'; ?> 
 
<h1>Thêm sản phẩm mới</h1> 

<?php if (!empty($errors)): ?> 
    <div class="alert alert-danger"> 
        <ul> 
            <?php foreach ($errors as $error): ?> 
                <li><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></li> 
            <?php endforeach; ?> 
        </ul> 
    </div> 
<?php endif; ?> 

<form method="POST" action="/Product/save" enctype="multipart/form-data"> 
    
    <div class="form-group"> 
        <label for="name">Tên sản phẩm:</label> 
        <input type="text" id="name" name="name" class="form-control" required> 
    </div> 

    <div class="form-group"> 
        <label for="description">Mô tả:</label> 
        <textarea id="description" name="description" class="form-control" required></textarea> 
    </div> 

    <div class="form-group"> 
        <label for="price">Giá:</label> 
        <input type="number" id="price" name="price" class="form-control" step="0.01" required> 
    </div> 

    <div class="form-group"> 
        <label for="category_id">Danh mục:</label> 
        
        <select id="category_id" name="category_id" class="form-control" required> 

            <?php foreach ($categories as $category): ?> 

                <option value="<?php echo $category->id; ?>">
                    <?php echo htmlspecialchars($category->name, ENT_QUOTES, 'UTF-8'); ?>
                </option> 

            <?php endforeach; ?> 

        </select> 
    </div> 

    <div class="form-group">
        <label for="image">H&igrave;nh &#7843;nh:</label>
        <input type="file" id="image" name="image" class="form-control-file" accept="image/jpeg,image/png,image/gif,image/webp">
        <small class="form-text text-muted">JPG, PNG, GIF ho&#7863;c WEBP; t&#7889;i &#273;a 2 MB.</small>
    </div>

    <button type="submit" class="btn btn-primary">
        Thêm sản phẩm
    </button> 

</form> 

<a href="/Product" class="btn btn-secondary mt-2">
    Quay lại danh sách sản phẩm
</a> 
 
<?php include 'app/views/shares/footer.php'; ?>
